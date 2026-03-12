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

use Zentra\DBAL\QueryExpression;
use Zentra\DBAL\QuerySubQuery;

/**
 * @var array $ADDTODISPLAYPREF
 * @var DBmysql $DB
 * @var Migration $migration
 */

$default_charset = DBConnection::getDefaultCharset();
$default_collation = DBConnection::getDefaultCollation();
$default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

// Add assignable assets rights
$assignable_asset_rights = [
    'computer', 'monitor', 'software', 'networking', 'printer',
    'cartridge', 'consumable', 'phone', 'peripheral',
];
foreach ($assignable_asset_rights as $rightname) {
    $migration->addRight($rightname, READ_ASSIGNED, [$rightname => READ]);
    $migration->addRight($rightname, UPDATE_ASSIGNED, [$rightname => UPDATE]);
    $migration->addRight($rightname, READ_OWNED, [$rightname => READ]);
    $migration->addRight($rightname, UPDATE_OWNED, [$rightname => UPDATE]);
}

$assignable_itemtypes = [
    'Appliance' => [
        'table' => 'zentra_appliances',
        'rightname' => 'appliance',
    ],
    'Cable' => [
        'table' => 'zentra_cables',
        'rightname' => 'cable_management',
    ],
    'CartridgeItem' => [
        'table' => 'zentra_cartridgeitems',
        'rightname' => 'cartridge',
    ],
    'Certificate' => [
        'table' => 'zentra_certificates',
        'rightname' => 'certificate',
    ],
    'Cluster' => [
        'table' => 'zentra_clusters',
        'rightname' => 'cluster',
    ],
    'Computer' => [
        'table' => 'zentra_computers',
        'rightname' => 'computer',
    ],
    'ConsumableItem' => [
        'table' => 'zentra_consumableitems',
        'rightname' => 'consumable',
    ],
    'DatabaseInstance' => [
        'table' => 'zentra_databaseinstances',
        'rightname' => 'databaseinstance',
    ],
    'Domain' => [
        'table' => 'zentra_domains',
        'rightname' => 'domain',
    ],
    'DomainRecord' => [
        'table' => 'zentra_domainrecords',
        'rightname' => 'domain',
    ],
    'Enclosure' => [
        'table' => 'zentra_enclosures',
        'rightname' => 'datacenter',
    ],
    'Item_DeviceSimcard' => [
        'table' => 'zentra_items_devicesimcards',
        'rightname' => 'device',
    ],
    'Line' => [
        'table' => 'zentra_lines',
        'rightname' => 'line',
    ],
    'Monitor' => [
        'table' => 'zentra_monitors',
        'rightname' => 'monitor',
    ],
    'NetworkEquipment' => [
        'table' => 'zentra_networkequipments',
        'rightname' => 'networking',
    ],
    'PassiveDCEquipment' => [
        'table' => 'zentra_passivedcequipments',
        'rightname' => 'datacenter',
    ],
    'PDU' => [
        'table' => 'zentra_pdus',
        'rightname' => 'datacenter',
    ],
    'Peripheral' => [
        'table' => 'zentra_peripherals',
        'rightname' => 'peripheral',
    ],
    'Phone' => [
        'table' => 'zentra_phones',
        'rightname' => 'phone',
    ],
    'Printer' => [
        'table' => 'zentra_printers',
        'rightname' => 'printer',
    ],
    'Rack' => [
        'table' => 'zentra_racks',
        'rightname' => 'datacenter',
    ],
    'Software' => [
        'table' => 'zentra_softwares',
        'rightname' => 'software',
    ],
    'SoftwareLicense' => [
        'table' => 'zentra_softwarelicenses',
        'rightname' => 'license',
    ],
    'Unmanaged' => [
        'table' => 'zentra_unmanageds',
        'rightname' => 'unmanaged',
    ],
];

if (!$DB->tableExists('zentra_groups_items')) {
    $query = <<<SQL
        CREATE TABLE `zentra_groups_items` (
          `id` int unsigned NOT NULL AUTO_INCREMENT,
          `groups_id` int {$default_key_sign} NOT NULL DEFAULT '0',
          `itemtype` varchar(255) NOT NULL DEFAULT '',
          `items_id` int {$default_key_sign} NOT NULL DEFAULT '0',
          `type` tinyint NOT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unicity` (`groups_id`,`itemtype`,`items_id`, `type`),
          KEY `item` (`itemtype`, `items_id`),
          KEY `type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;
SQL;
    $DB->doQuery($query);
}

foreach ($assignable_itemtypes as $itemtype => $specs) {
    $itemtype_table     = $specs['table'];
    $itemtype_rightname = $specs['rightname'];

    $migration->addRight($itemtype_rightname, READ_ASSIGNED, [$itemtype_rightname => READ]);
    $migration->addRight($itemtype_rightname, UPDATE_ASSIGNED, [$itemtype_rightname => UPDATE]);

    // Add missing `users_id`/`users_id_tech` fields on assignable items
    $migration->addField($itemtype_table, 'users_id', 'fkey');
    $migration->addKey($itemtype_table, 'users_id');
    $migration->addField($itemtype_table, 'users_id_tech', 'fkey');
    $migration->addKey($itemtype_table, 'users_id_tech');

    // move groups to the new link table
    if ($DB->fieldExists($itemtype_table, 'groups_id')) {
        $DB->insert('zentra_groups_items', new QuerySubQuery([
            'SELECT' => [
                new QueryExpression('NULL', 'id'),
                'groups_id',
                new QueryExpression($DB::quoteValue($itemtype), 'itemtype'),
                'id AS items_id',
                new QueryExpression('1', 'type'),
            ],
            'FROM'   => $itemtype_table,
            'WHERE'  => [
                'groups_id' => ['>', 0],
            ],
        ]));
    }
    if ($DB->fieldExists($itemtype_table, 'groups_id_tech')) {
        $DB->insert('zentra_groups_items', new QuerySubQuery([
            'SELECT' => [
                new QueryExpression('NULL', 'id'),
                'groups_id_tech AS groups_id',
                new QueryExpression($DB::quoteValue($itemtype), 'itemtype'),
                'id AS items_id',
                new QueryExpression('2', 'type'),
            ],
            'FROM'   => $itemtype_table,
            'WHERE'  => [
                'groups_id_tech' => ['>', 0],
            ],
        ]));
    }

    $migration->dropKey($itemtype_table, 'groups_id');
    $migration->dropKey($itemtype_table, 'groups_id_tech');
    $migration->dropField($itemtype_table, 'groups_id');
    $migration->dropField($itemtype_table, 'groups_id_tech');
}
