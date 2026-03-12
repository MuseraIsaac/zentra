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

namespace tests\units\Zentra\Form\Migration;

use Zentra\Form\AccessControl\FormAccessControlManager;
use Zentra\Form\Destination\AbstractCommonITILFormDestination;
use Zentra\Form\Destination\CommonITILField\AssigneeField;
use Zentra\Form\Destination\CommonITILField\AssigneeFieldConfig;
use Zentra\Form\Destination\CommonITILField\AssociatedItemsField;
use Zentra\Form\Destination\CommonITILField\AssociatedItemsFieldConfig;
use Zentra\Form\Destination\CommonITILField\AssociatedItemsFieldStrategy;
use Zentra\Form\Destination\CommonITILField\BackupPlanField;
use Zentra\Form\Destination\CommonITILField\CausesField;
use Zentra\Form\Destination\CommonITILField\CheckListField;
use Zentra\Form\Destination\CommonITILField\ContentField;
use Zentra\Form\Destination\CommonITILField\ControlsListField;
use Zentra\Form\Destination\CommonITILField\DeploymentPlanField;
use Zentra\Form\Destination\CommonITILField\EntityField;
use Zentra\Form\Destination\CommonITILField\EntityFieldConfig;
use Zentra\Form\Destination\CommonITILField\EntityFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ImpactsField;
use Zentra\Form\Destination\CommonITILField\ITILActorFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILCategoryField;
use Zentra\Form\Destination\CommonITILField\ITILCategoryFieldConfig;
use Zentra\Form\Destination\CommonITILField\ITILCategoryFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILFollowupField;
use Zentra\Form\Destination\CommonITILField\ITILFollowupFieldConfig;
use Zentra\Form\Destination\CommonITILField\ITILFollowupFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILTaskField;
use Zentra\Form\Destination\CommonITILField\ITILTaskFieldConfig;
use Zentra\Form\Destination\CommonITILField\ITILTaskFieldStrategy;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsField;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsFieldConfig;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsFieldStrategyConfig;
use Zentra\Form\Destination\CommonITILField\LocationField;
use Zentra\Form\Destination\CommonITILField\LocationFieldConfig;
use Zentra\Form\Destination\CommonITILField\LocationFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ObserverField;
use Zentra\Form\Destination\CommonITILField\ObserverFieldConfig;
use Zentra\Form\Destination\CommonITILField\OLATTOField;
use Zentra\Form\Destination\CommonITILField\OLATTOFieldConfig;
use Zentra\Form\Destination\CommonITILField\OLATTRField;
use Zentra\Form\Destination\CommonITILField\OLATTRFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequesterField;
use Zentra\Form\Destination\CommonITILField\RequesterFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequestSourceField;
use Zentra\Form\Destination\CommonITILField\RequestSourceFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequestSourceFieldStrategy;
use Zentra\Form\Destination\CommonITILField\RequestTypeField;
use Zentra\Form\Destination\CommonITILField\RequestTypeFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequestTypeFieldStrategy;
use Zentra\Form\Destination\CommonITILField\SimpleValueConfig;
use Zentra\Form\Destination\CommonITILField\SLATTOField;
use Zentra\Form\Destination\CommonITILField\SLATTOFieldConfig;
use Zentra\Form\Destination\CommonITILField\SLATTRField;
use Zentra\Form\Destination\CommonITILField\SLATTRFieldConfig;
use Zentra\Form\Destination\CommonITILField\SLMFieldStrategy;
use Zentra\Form\Destination\CommonITILField\StatusField;
use Zentra\Form\Destination\CommonITILField\SymptomsField;
use Zentra\Form\Destination\CommonITILField\TemplateField;
use Zentra\Form\Destination\CommonITILField\TemplateFieldConfig;
use Zentra\Form\Destination\CommonITILField\TemplateFieldStrategy;
use Zentra\Form\Destination\CommonITILField\TitleField;
use Zentra\Form\Destination\CommonITILField\UrgencyField;
use Zentra\Form\Destination\CommonITILField\UrgencyFieldConfig;
use Zentra\Form\Destination\CommonITILField\UrgencyFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ValidationField;
use Zentra\Form\Destination\CommonITILField\ValidationFieldConfig;
use Zentra\Form\Destination\CommonITILField\ValidationFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ValidationFieldStrategyConfig;
use Zentra\Form\Destination\FormDestinationChange;
use Zentra\Form\Destination\FormDestinationProblem;
use Zentra\Form\Destination\FormDestinationTicket;
use Zentra\Form\Form;
use Zentra\Form\Migration\FormMigration;
use Zentra\Migration\PluginMigrationResult;
use Zentra\Tests\DbTestCase;
use Zentra\Tests\FormTesterTrait;
use ZentraPlugin\Tester\Form\ExternalIDField;
use ZentraPlugin\Tester\Form\ExternalIDFieldConfig;
use ZentraPlugin\Tester\Form\ExternalIDFieldStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use Ticket;

final class TargetsMigrationTest extends DbTestCase
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

    public static function provideFormMigrationWithTargets(): iterable
    {
        yield 'Test form migration for targets' => [
            'form_name'             => 'Test form migration for targets',
            'expected_destinations' => [
                [
                    'itemtype'     => FormDestinationTicket::class,
                    'name'         => 'Test form migration for target ticket',
                    'is_mandatory' => false,
                    'fields'       => [
                        TitleField::getKey()           => new SimpleValueConfig(
                            'Test form migration for target ticket'
                        ),
                        TemplateField::getKey()        => new TemplateFieldConfig(
                            strategy: TemplateFieldStrategy::DEFAULT_TEMPLATE
                        ),
                        ITILCategoryField::getKey()    => new ITILCategoryFieldConfig(
                            strategy: ITILCategoryFieldStrategy::LAST_VALID_ANSWER
                        ),
                        EntityField::getKey()          => new EntityFieldConfig(
                            strategy: EntityFieldStrategy::FORM_FILLER
                        ),
                        LocationField::getKey()        => new LocationFieldConfig(
                            strategy: LocationFieldStrategy::FROM_TEMPLATE
                        ),
                        AssociatedItemsField::getKey() => new AssociatedItemsFieldConfig(
                            strategies: [AssociatedItemsFieldStrategy::LAST_VALID_ANSWER]
                        ),
                        ITILFollowupField::getKey()    => new ITILFollowupFieldConfig(
                            strategy: ITILFollowupFieldStrategy::NO_FOLLOWUP
                        ),
                        RequestSourceField::getKey()   => new RequestSourceFieldConfig(
                            strategy: RequestSourceFieldStrategy::FROM_TEMPLATE
                        ),
                        ValidationField::getKey()      => new ValidationFieldConfig([
                            new ValidationFieldStrategyConfig(
                                strategy: ValidationFieldStrategy::NO_VALIDATION
                            ),
                        ]),
                        ITILTaskField::getKey()        => new ITILTaskFieldConfig(
                            strategy: ITILTaskFieldStrategy::NO_TASK
                        ),
                        RequesterField::getKey()       => new RequesterFieldConfig(
                            strategies: [ITILActorFieldStrategy::FORM_FILLER]
                        ),
                        ObserverField::getKey()        => new ObserverFieldConfig(
                            strategies: [ITILActorFieldStrategy::FROM_TEMPLATE]
                        ),
                        AssigneeField::getKey()        => new AssigneeFieldConfig(
                            strategies: [ITILActorFieldStrategy::FROM_TEMPLATE]
                        ),
                        SLATTOField::getKey()          => new SLATTOFieldConfig(
                            strategy: SLMFieldStrategy::FROM_TEMPLATE
                        ),
                        SLATTRField::getKey()          => new SLATTRFieldConfig(
                            strategy: SLMFieldStrategy::FROM_TEMPLATE
                        ),
                        OLATTOField::getKey()          => new OLATTOFieldConfig(
                            strategy: SLMFieldStrategy::FROM_TEMPLATE
                        ),
                        OLATTRField::getKey()          => new OLATTRFieldConfig(
                            strategy: SLMFieldStrategy::FROM_TEMPLATE
                        ),
                        UrgencyField::getKey()        => new UrgencyFieldConfig(
                            strategy: UrgencyFieldStrategy::FROM_TEMPLATE
                        ),
                        RequestTypeField::getKey()    => new RequestTypeFieldConfig(
                            strategy: RequestTypeFieldStrategy::SPECIFIC_VALUE,
                            specific_request_type: Ticket::INCIDENT_TYPE
                        ),
                        StatusField::getKey()         => new SimpleValueConfig(
                            StatusField::DEFAULT_STATUS
                        ),
                        LinkedITILObjectsField::getKey() => new LinkedITILObjectsFieldConfig([
                            new LinkedITILObjectsFieldStrategyConfig(
                                strategy: null, // No specific strategy for linked ITIL objects
                            ),
                        ]),

                        // Tester plugin fields
                        ExternalIDField::getKey() => new ExternalIDFieldConfig(
                            strategy: ExternalIDFieldStrategy::NO_EXTERNAL_ID
                        ),
                    ],
                ],
                [
                    'itemtype' => FormDestinationChange::class,
                    'name'     => 'Test form migration for target change',
                    'fields'   => [
                        TitleField::getKey()           => new SimpleValueConfig(
                            'Test form migration for target change'
                        ),
                        TemplateField::getKey()        => new TemplateFieldConfig(
                            strategy: TemplateFieldStrategy::DEFAULT_TEMPLATE
                        ),
                        ITILCategoryField::getKey()    => new ITILCategoryFieldConfig(
                            strategy: ITILCategoryFieldStrategy::LAST_VALID_ANSWER
                        ),
                        EntityField::getKey()          => new EntityFieldConfig(
                            strategy: EntityFieldStrategy::FORM_FILLER
                        ),
                        LocationField::getKey()        => new LocationFieldConfig(
                            strategy: LocationFieldStrategy::LAST_VALID_ANSWER
                        ),
                        AssociatedItemsField::getKey() => new AssociatedItemsFieldConfig(
                            strategies: [AssociatedItemsFieldStrategy::LAST_VALID_ANSWER]
                        ),
                        ITILFollowupField::getKey()    => new ITILFollowupFieldConfig(
                            strategy: ITILFollowupFieldStrategy::NO_FOLLOWUP
                        ),
                        RequestSourceField::getKey()   => new RequestSourceFieldConfig(
                            strategy: RequestSourceFieldStrategy::FROM_TEMPLATE
                        ),
                        ValidationField::getKey()      => new ValidationFieldConfig([
                            new ValidationFieldStrategyConfig(
                                strategy: ValidationFieldStrategy::NO_VALIDATION
                            ),
                        ]),
                        ITILTaskField::getKey()        => new ITILTaskFieldConfig(
                            strategy: ITILTaskFieldStrategy::NO_TASK
                        ),
                        RequesterField::getKey()       => new RequesterFieldConfig(
                            strategies: [ITILActorFieldStrategy::FORM_FILLER]
                        ),
                        ObserverField::getKey()        => new ObserverFieldConfig(
                            strategies: [ITILActorFieldStrategy::FROM_TEMPLATE]
                        ),
                        AssigneeField::getKey()        => new AssigneeFieldConfig(
                            strategies: [ITILActorFieldStrategy::FROM_TEMPLATE]
                        ),
                        UrgencyField::getKey()        => new UrgencyFieldConfig(
                            strategy: UrgencyFieldStrategy::FROM_TEMPLATE
                        ),
                        LinkedITILObjectsField::getKey() => new LinkedITILObjectsFieldConfig([
                            new LinkedITILObjectsFieldStrategyConfig(
                                strategy: null, // No specific strategy for linked ITIL objects
                            ),
                        ]),
                        ImpactsField::getKey()        => new SimpleValueConfig(""),
                        ControlsListField::getKey()   => new SimpleValueConfig(""),
                        DeploymentPlanField::getKey() => new SimpleValueConfig(""),
                        BackupPlanField::getKey()     => new SimpleValueConfig(""),
                        CheckListField::getKey()      => new SimpleValueConfig(""),
                    ],
                ],
                [
                    'itemtype' => FormDestinationProblem::class,
                    'name'     => 'Test form migration for target problem',
                    'fields'   => [
                        TitleField::getKey()           => new SimpleValueConfig(
                            'Test form migration for target problem'
                        ),
                        TemplateField::getKey()        => new TemplateFieldConfig(
                            strategy: TemplateFieldStrategy::DEFAULT_TEMPLATE
                        ),
                        ITILCategoryField::getKey()    => new ITILCategoryFieldConfig(
                            strategy: ITILCategoryFieldStrategy::LAST_VALID_ANSWER
                        ),
                        EntityField::getKey()          => new EntityFieldConfig(
                            strategy: EntityFieldStrategy::FORM_FILLER
                        ),
                        LocationField::getKey()        => new LocationFieldConfig(
                            strategy: LocationFieldStrategy::LAST_VALID_ANSWER
                        ),
                        AssociatedItemsField::getKey() => new AssociatedItemsFieldConfig(
                            strategies: [AssociatedItemsFieldStrategy::LAST_VALID_ANSWER]
                        ),
                        ITILFollowupField::getKey()    => new ITILFollowupFieldConfig(
                            strategy: ITILFollowupFieldStrategy::NO_FOLLOWUP
                        ),
                        RequestSourceField::getKey()   => new RequestSourceFieldConfig(
                            strategy: RequestSourceFieldStrategy::FROM_TEMPLATE
                        ),
                        ValidationField::getKey()      => new ValidationFieldConfig([
                            new ValidationFieldStrategyConfig(
                                strategy: ValidationFieldStrategy::NO_VALIDATION
                            ),
                        ]),
                        ITILTaskField::getKey()        => new ITILTaskFieldConfig(
                            strategy: ITILTaskFieldStrategy::NO_TASK
                        ),
                        RequesterField::getKey()       => new RequesterFieldConfig(
                            strategies: [ITILActorFieldStrategy::FORM_FILLER]
                        ),
                        ObserverField::getKey()        => new ObserverFieldConfig(
                            strategies: [ITILActorFieldStrategy::FROM_TEMPLATE]
                        ),
                        AssigneeField::getKey()        => new AssigneeFieldConfig(
                            strategies: [ITILActorFieldStrategy::FROM_TEMPLATE]
                        ),
                        UrgencyField::getKey()        => new UrgencyFieldConfig(
                            strategy: UrgencyFieldStrategy::FROM_TEMPLATE
                        ),
                        LinkedITILObjectsField::getKey() => new LinkedITILObjectsFieldConfig([
                            new LinkedITILObjectsFieldStrategyConfig(
                                strategy: null, // No specific strategy for linked ITIL objects
                            ),
                        ]),
                        ImpactsField::getKey()  => new SimpleValueConfig(""),
                        CausesField::getKey()   => new SimpleValueConfig(""),
                        SymptomsField::getKey() => new SimpleValueConfig(""),
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('provideFormMigrationWithTargets')]
    public function testAllDestinationFieldsAreChecked($form_name, $expected_destinations): void
    {
        foreach ($expected_destinations as $expected_destination) {
            $destination = new $expected_destination['itemtype']();
            foreach ($destination->getConfigurableFields() as $field) {
                if ($field instanceof ContentField) {
                    continue;
                }

                $this->assertArrayHasKey(
                    $field::getKey(),
                    $expected_destination['fields']
                );
            }
        }
    }

    #[DataProvider('provideFormMigrationWithTargets')]
    public function testFormMigrationWithTargets($form_name, $expected_destinations): void
    {
        global $DB;

        $migration = new FormMigration($DB, FormAccessControlManager::getInstance());
        $this->setPrivateProperty($migration, 'result', new PluginMigrationResult());
        $this->assertTrue($this->callPrivateMethod($migration, 'processMigration'));

        /** @var Form $form */
        $form = getItemByTypeName(Form::class, $form_name);
        $destinations = $form->getDestinations();
        foreach ($destinations as $destination) {
            /** @var AbstractCommonITILFormDestination $itil_destination */
            $itil_destination = $destination->getConcreteDestinationItem();

            // Find the matching expected destination
            $expected_destination = current(array_filter(
                $expected_destinations,
                function ($expected_destination) use ($itil_destination, $destination) {
                    if ((new $expected_destination['itemtype']())->getTarget()::class !== $itil_destination->getTarget()::class) {
                        return false;
                    }

                    if ($expected_destination['name'] !== $destination->fields['name']) {
                        return false;
                    }

                    return true;
                }
            ));
            $this->assertNotFalse($expected_destination);

            // Check the fields of the destination
            if ($itil_destination instanceof AbstractCommonITILFormDestination) {
                foreach ($expected_destination['fields'] as $field_key => $expected_field) {
                    $field = $itil_destination->getConfigurableFieldByKey($field_key);

                    $this->assertEquals(
                        $expected_field->jsonSerialize(),
                        $field->getConfig(
                            $form,
                            $destination->getConfig()
                        )->jsonSerialize()
                    );
                }
            }
        }

        $this->assertCount(
            count($expected_destinations),
            $destinations,
            'The number of destinations is not the expected one'
        );
    }

    public function testMigrationOfChangesSpecificFields(): void
    {
        /** @var \DBmysql $DB */
        global $DB;

        // Arrange: create a form with a change target
        $form_id = $this->createSimpleFormcreatorForm("Form with change", [
            ['name' => 'Question 1', 'fieldtype' => 'text'],
            ['name' => 'Question 2', 'fieldtype' => 'text'],
            ['name' => 'Question 3', 'fieldtype' => 'text'],
            ['name' => 'Question 4', 'fieldtype' => 'text'],
            ['name' => 'Question 5', 'fieldtype' => 'text'],
        ]);
        $q1 = $this->getFormCreatorQuestionId("Question 1");
        $q2 = $this->getFormCreatorQuestionId("Question 2");
        $q3 = $this->getFormCreatorQuestionId("Question 3");
        $q4 = $this->getFormCreatorQuestionId("Question 4");
        $q5 = $this->getFormCreatorQuestionId("Question 5");
        $this->addChangeTargetToFromcreatorForm($form_id, [
            'name'               => 'My change',
            'target_name'        => 'My change',
            'content'            => "##FULLFORM##",
            'impactcontent'      => "##question_$q1##",
            'controlistcontent'  => "##question_$q2##",
            'rolloutplancontent' => "##question_$q3##",
            'backoutplancontent' => "##question_$q4##",
            'checklistcontent'   => "##question_$q5##",
        ]);

        // Act: execute migration
        $control_manager = FormAccessControlManager::getInstance();
        $migration = new FormMigration(
            db: $DB,
            formAccessControlManager: $control_manager,
            specificFormsIds: [$form_id],
        );
        $migration->execute();

        // Assert: a form destination should be populated with the specific fields
        $form = getItemByTypeName(Form::class, "Form with change");
        $destinations = $form->getDestinations();
        $this->assertCount(1, $destinations);

        $destination = current($destinations);
        $this->assertEquals(
            FormDestinationChange::class,
            $destination->fields['itemtype'],
        );

        $config = json_decode($destination->fields['config'], true);
        $this->assertStringContainsString(
            "#Question: Question 1",
            $config['zentra-form-destination-commonitilfield-impactsfield']['value'],
        );
        $this->assertStringContainsString(
            "#Question: Question 2",
            $config['zentra-form-destination-commonitilfield-controlslistfield']['value'],
        );
        $this->assertStringContainsString(
            "#Question: Question 3",
            $config['zentra-form-destination-commonitilfield-deploymentplanfield']['value'],
        );
        $this->assertStringContainsString(
            "#Question: Question 4",
            $config['zentra-form-destination-commonitilfield-backupplanfield']['value'],
        );
        $this->assertStringContainsString(
            "#Question: Question 5",
            $config['zentra-form-destination-commonitilfield-checklistfield']['value'],
        );
    }

    public function testMigrationOfProblemsSpecificFields(): void
    {
        /** @var \DBmysql $DB */
        global $DB;

        // Arrange: create a form with a problem target
        $form_id = $this->createSimpleFormcreatorForm("Form with problem", [
            ['name' => 'Question 1', 'fieldtype' => 'text'],
            ['name' => 'Question 2', 'fieldtype' => 'text'],
            ['name' => 'Question 3', 'fieldtype' => 'text'],
        ]);
        $q1 = $this->getFormCreatorQuestionId("Question 1");
        $q2 = $this->getFormCreatorQuestionId("Question 2");
        $q3 = $this->getFormCreatorQuestionId("Question 3");
        $this->addProblemTargetToFromcreatorForm($form_id, [
            'name'           => 'My problem',
            'target_name'    => 'My problem',
            'content'        => "##FULLFORM##",
            'impactcontent'  => "##question_$q1##",
            'causecontent'   => "##question_$q2##",
            'symptomcontent' => "##question_$q3##",
        ]);

        // Act: execute migration
        $control_manager = FormAccessControlManager::getInstance();
        $migration = new FormMigration(
            db: $DB,
            formAccessControlManager: $control_manager,
            specificFormsIds: [$form_id],
        );
        $migration->execute();

        // Assert: a form destination should be populated with the specific fields
        $form = getItemByTypeName(Form::class, "Form with problem");
        $destinations = $form->getDestinations();
        $this->assertCount(1, $destinations);

        $destination = current($destinations);
        $this->assertEquals(
            FormDestinationProblem::class,
            $destination->fields['itemtype'],
        );

        $config = json_decode($destination->fields['config'], true);
        $this->assertStringContainsString(
            "#Question: Question 1",
            $config['zentra-form-destination-commonitilfield-impactsfield']['value'],
        );
        $this->assertStringContainsString(
            "#Question: Question 2",
            $config['zentra-form-destination-commonitilfield-causesfield']['value'],
        );
        $this->assertStringContainsString(
            "#Question: Question 3",
            $config['zentra-form-destination-commonitilfield-symptomsfield']['value'],
        );
    }
}
