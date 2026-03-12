<?php

/**
 * ---------------------------------------------------------------------
 *
 * ZENTRA - Gestionnaire Libre de Parc Informatique
 *
 * http://zentra-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ZENTRA.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace Zentra\Tests;

use Zentra\DBAL\JsonFieldInterface;
use Zentra\Form\AccessControl\FormAccessControlManager;
use Zentra\Form\Destination\FormDestinationTicket;
use Zentra\Form\Form;
use Zentra\Form\Migration\FormMigration;
use Zentra\Migration\PluginMigrationResult;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class AbstractDestinationFieldTest extends DbTestCase
{
    use FormTesterTrait;

    public static function setUpBeforeClass(): void
    {
        global $DB;

        parent::setUpBeforeClass();

        // Clean up data in case execution was stopped before tearDownAfterClass
        // could run.
        $tables = $DB->listTables('zentra\_plugin\_formcreator\_%');
        foreach ($tables as $table) {
            $DB->dropTable($table['TABLE_NAME']);
        }

        $queries = $DB->getQueriesFromFile(sprintf('%s/tests/zentra-formcreator-migration-data.sql', ZENTRA_ROOT));
        foreach ($queries as $query) {
            $DB->doQuery($query);
        }
    }

    public static function tearDownAfterClass(): void
    {
        global $DB;

        $tables = $DB->listTables('zentra\_plugin\_formcreator\_%');
        foreach ($tables as $table) {
            $DB->dropTable($table['TABLE_NAME']);
        }

        $DB->clearSchemaCache();
        parent::tearDownAfterClass();
    }

    abstract public static function provideConvertFieldConfigFromFormCreator(): iterable;

    #[DataProvider('provideConvertFieldConfigFromFormCreator')]
    public function testConvertFieldConfigFromFormCreator(
        string $field_key,
        array $fields_to_set,
        callable|JsonFieldInterface $field_config
    ): void {
        global $DB;

        if (!empty($fields_to_set)) {
            // Compute some values
            foreach ($fields_to_set as $key => $value) {
                if (is_callable($value)) {
                    $fields_to_set[$key] = $value($this);
                }
            }

            // Update target fields
            $this->assertNotFalse($DB->update(
                'zentra_plugin_formcreator_targettickets',
                $fields_to_set,
                ['zentra_plugin_formcreator_forms.name' => 'Test form migration for targets'],
                [
                    'JOIN' => [
                        'zentra_plugin_formcreator_forms' => [
                            'ON' => [
                                'zentra_plugin_formcreator_targettickets' => 'plugin_formcreator_forms_id',
                                'zentra_plugin_formcreator_forms'         => 'id',
                            ],
                        ],
                    ],
                ]
            ));
        }

        // Run migration
        $migration = new FormMigration($DB, FormAccessControlManager::getInstance());
        $this->setPrivateProperty($migration, 'result', new PluginMigrationResult());
        $this->assertTrue($this->callPrivateMethod($migration, 'processMigration'));

        /** @var Form $form */
        $form = getItemByTypeName(Form::class, 'Test form migration for targets');
        $destination = current(array_filter(
            $form->getDestinations(),
            fn($destination) => $destination->getConcreteDestinationItem() instanceof FormDestinationTicket
        ));

        $this->assertNotFalse($destination);

        /** @var FormDestinationTicket $itil_destination */
        $itil_destination = $destination->getConcreteDestinationItem();
        $itil_destination->getConfigurableFieldByKey($field_key)
        ->getConfig($form, $destination->getConfig())
        ->jsonSerialize();
        $this->assertEquals(
            (is_callable($field_config) ? $field_config($migration, $form) : $field_config)->jsonSerialize(),
            $itil_destination->getConfigurableFieldByKey($field_key)
                ->getConfig($form, $destination->getConfig())
                ->jsonSerialize()
        );
    }
}
