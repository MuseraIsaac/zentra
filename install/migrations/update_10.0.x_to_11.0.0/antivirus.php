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
 * @var Migration $migration
 * @var DBmysql $DB
 */
$default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

if ($DB->tableExists('zentra_computerantiviruses')) {
    $migration->renameTable('zentra_computerantiviruses', 'zentra_itemantiviruses');
}

if (!$DB->fieldExists('zentra_itemantiviruses', 'itemtype')) {
    $migration->addField(
        'zentra_itemantiviruses',
        'itemtype',
        'string',
        [
            'after'  => 'id',
            'update' => $DB->quoteValue('Computer'), // Defines value for all existing elements
        ]
    );
    $migration->migrationOneTable('zentra_itemantiviruses');
}

if (!$DB->fieldExists('zentra_itemantiviruses', 'items_id')) {
    $migration->dropKey('zentra_itemantiviruses', 'computers_id');
    $migration->changeField(
        'zentra_itemantiviruses',
        'computers_id',
        'items_id',
        "int {$default_key_sign} NOT NULL DEFAULT '0'",
        [
            'after' => 'itemtype',
        ]
    );
    $migration->migrationOneTable('zentra_itemantiviruses');
}

$migration->addKey('zentra_itemantiviruses', ['itemtype', 'items_id'], 'item');
