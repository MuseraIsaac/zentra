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

namespace tests\units\Zentra\System\Requirement;

use Zentra\System\Requirement\DatabaseTablesEngine;
use Zentra\Tests\ZENTRATestCase;

final class DatabaseTablesEngineTest extends ZENTRATestCase
{
    public function testTablesWithIncorrectEngineAreFound(): void
    {
        /** @var \DBmysql $db */
        global $DB;

        // Arrange: create some MyIsam tables
        $this->getDbHandle()->query(<<<SQL
            CREATE TABLE `zentra_tmp_testTablesWithIncorrectEngineAreFound1` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=MyIsam CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
        SQL);
        $this->getDbHandle()->query(<<<SQL
            CREATE TABLE `zentra_tmp_testTablesWithIncorrectEngineAreFound2` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
            ) ENGINE=MyIsam CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
        SQL);

        // Act: run table engine requirement
        $table_engine_requirement = new DatabaseTablesEngine($DB);
        $is_validated = $table_engine_requirement->isValidated();
        $messages = $table_engine_requirement->getValidationMessages();

        // Clean created tables
        $DB->doQuery("DROP TABLE zentra_tmp_testTablesWithIncorrectEngineAreFound1");
        $DB->doQuery("DROP TABLE zentra_tmp_testTablesWithIncorrectEngineAreFound2");

        // Assert: validation should fail with 2 invalid tables
        $this->assertFalse($is_validated);
        $this->assertEquals([
            'The database contains 2 table(s) using the unsupported MyISAM engine. Please run the "php bin/console migration:myisam_to_innodb" command to migrate them to the InnoDB engine.',
        ], $messages);
    }

    public function testThereAreNoErrorsOnDefaultInstallation(): void
    {
        /** @var \DBmysql $db */
        global $DB;

        // Act: run table engine requirement
        $table_engine_requirement = new DatabaseTablesEngine($DB);
        $is_validated = $table_engine_requirement->isValidated();
        $messages = $table_engine_requirement->getValidationMessages();

        // Assert: there should be no errors
        $this->assertTrue($is_validated);
        $this->assertEmpty($messages);
    }
}
