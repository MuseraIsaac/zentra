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

/**
 * @var DBmysql $DB
 * @var Migration $migration
 */
if (!$DB->fieldExists('zentra_users', '2fa')) {
    $migration->addField('zentra_users', '2fa', 'text');
}
if (!$DB->fieldExists('zentra_users', '2fa_unenforced')) {
    $migration->addField('zentra_users', '2fa_unenforced', 'bool');
}
if (!$DB->fieldExists('zentra_entities', '2fa_enforcement_strategy')) {
    $migration->addField('zentra_entities', '2fa_enforcement_strategy', 'tinyint NOT NULL DEFAULT -2');
    // Root entity should have this set to 0 by default
    $migration->addPostQuery(
        $DB->buildUpdate(
            'zentra_entities',
            ['2fa_enforcement_strategy' => 0],
            ['id' => 0]
        )
    );
}
if (!$DB->fieldExists('zentra_profiles', '2fa_enforced')) {
    $migration->addField('zentra_profiles', '2fa_enforced', 'bool');
}
if (!$DB->fieldExists('zentra_groups', '2fa_enforced')) {
    $migration->addField('zentra_groups', '2fa_enforced', 'bool');
}
$migration->addConfig([
    '2fa_enforced' => 0,
    '2fa_grace_date_start' => null,
    '2fa_grace_days' => 0,
    '2fa_suffix' => '',
]);
