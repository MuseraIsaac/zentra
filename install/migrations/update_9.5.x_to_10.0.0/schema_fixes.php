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
$default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

// Remove the `NOT NULL` flag of comment fields and fix collation
$tables = [
    'zentra_apiclients',
    'zentra_applianceenvironments',
    'zentra_appliances',
    'zentra_appliancetypes',
    'zentra_devicesimcards',
    'zentra_knowbaseitems_comments',
    'zentra_lines',
    'zentra_rulerightparameters',
    'zentra_ssovariables',
    'zentra_virtualmachinestates',
    'zentra_virtualmachinesystems',
    'zentra_virtualmachinetypes',
];
foreach ($tables as $table) {
    $migration->changeField($table, 'comment', 'comment', 'text');
}

// Add `DEFAULT CURRENT_TIMESTAMP` to some date fields
$tables = [
    'zentra_alerts',
    'zentra_crontasklogs',
    'zentra_notimportedemails',
];
foreach ($tables as $table) {
    $migration->changeField($table, 'date', 'date', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
}

// Fix charset for zentra_notimportedemails table
$migration->addPreQuery(
    sprintf(
        'ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s',
        $DB->quoteName('zentra_notimportedemails'),
        DBConnection::getDefaultCharset(),
        DBConnection::getDefaultCollation()
    )
);
// Put back `subject` type to text (charset convertion changed it from text to mediumtext)
$migration->changeField('zentra_notimportedemails', 'subject', 'subject', 'text', ['nodefault' => true]);

// Drop malformed keys
$malformed_keys = [
    'zentra_ipaddresses' => [
        'textual',
    ],
    'zentra_items_softwareversions' => [
        'is_deleted',
        'is_template',
    ],
    'zentra_registeredids' => [
        'item',
    ],
];
foreach ($malformed_keys as $table => $keys) {
    foreach ($keys as $key) {
        $migration->dropKey($table, $key);
        $migration->migrationOneTable($table);
    }
}

// Drop useless keys
$useless_keys = [
    'zentra_appliances_items' => [
        'appliances_id',
    ],
    'zentra_appliances_items_relations' => [
        'itemtype',
        'items_id',
    ],
    'zentra_certificates_items' => [
        'device',
    ],
    'zentra_changetemplatehiddenfields' => [
        'changetemplates_id',
    ],
    'zentra_changetemplatemandatoryfields' => [
        'changetemplates_id',
    ],
    'zentra_contracts_items' => [
        'FK_device',
    ],
    'zentra_dashboards_rights' => [
        'dashboards_dashboards_id',
    ],
    'zentra_domains_items' => [
        'domains_id',
        'FK_device',
    ],
    'zentra_dropdowntranslations' => [
        'typeid',
    ],
    'zentra_entities' => [
        'entities_id',
    ],
    'zentra_impactrelations' => [
        'source_asset',
    ],
    'zentra_ipaddresses_ipnetworks' => [
        'ipaddresses_id',
    ],
    'zentra_items_devicebatteries' => [
        'computers_id',
    ],
    'zentra_items_devicecases' => [
        'computers_id',
    ],
    'zentra_items_devicecontrols' => [
        'computers_id',
    ],
    'zentra_items_devicedrives' => [
        'computers_id',
    ],
    'zentra_items_devicefirmwares' => [
        'computers_id',
    ],
    'zentra_items_devicegenerics' => [
        'computers_id',
    ],
    'zentra_items_devicegraphiccards' => [
        'computers_id',
    ],
    'zentra_items_deviceharddrives' => [
        'computers_id',
    ],
    'zentra_items_devicememories' => [
        'computers_id',
    ],
    'zentra_items_devicemotherboards' => [
        'computers_id',
    ],
    'zentra_items_devicenetworkcards' => [
        'computers_id',
    ],
    'zentra_items_devicepcis' => [
        'computers_id',
    ],
    'zentra_items_devicepowersupplies' => [
        'computers_id',
    ],
    'zentra_items_deviceprocessors' => [
        'computers_id',
    ],
    'zentra_items_devicesensors' => [
        'computers_id',
    ],
    'zentra_items_devicesoundcards' => [
        'computers_id',
    ],
    'zentra_items_disks' => [
        'itemtype',
        'items_id',
    ],
    'zentra_items_operatingsystems' => [
        'items_id',
    ],
    'zentra_items_softwarelicenses' => [
        'itemtype',
        'items_id',
    ],
    'zentra_items_softwareversions' => [
        'item',
        'itemtype',
        'items_id',
    ],
    'zentra_itilfollowups' => [
        'itemtype',
        'item_id',
    ],
    'zentra_itilsolutions' => [
        'itemtype',
        'item_id',
    ],
    'zentra_knowbaseitemcategories' => [
        'entities_id',
    ],
    'zentra_knowbaseitems_items' => [
        'item',
        'itemtype',
        'item_id',
    ],
    'zentra_networknames' => [
        'name',
    ],
    'zentra_networkports' => [
        'on_device',
    ],
    'zentra_notifications_notificationtemplates' => [
        'notifications_id',
    ],
    'zentra_olalevels_tickets' => [
        'tickets_id',
    ],
    'zentra_problemtemplatehiddenfields' => [
        'problemtemplates_id',
    ],
    'zentra_problemtemplatemandatoryfields' => [
        'problemtemplates_id',
    ],
    'zentra_reservations' => [
        'reservationitems_id',
    ],
    'zentra_slalevels_tickets' => [
        'tickets_id',
    ],
    'zentra_tickettemplatehiddenfields' => [
        'tickettemplates_id',
    ],
    'zentra_tickettemplatemandatoryfields' => [
        'tickettemplates_id',
    ],
];
foreach ($useless_keys as $table => $keys) {
    foreach ($keys as $key) {
        $migration->dropKey($table, $key);
        $migration->migrationOneTable($table);
    }
}

// Add missing keys (based on tools:check_database_keys detection)
$missing_keys = [
    'zentra_apiclients' => [
        'entities_id',
        'is_recursive',
        'name',
    ],
    'zentra_appliances' => [
        'date_mod',
        'is_recursive',
    ],
    'zentra_appliancetypes' => [
        'is_recursive',
    ],
    'zentra_authldapreplicates' => [
        'name',
    ],
    'zentra_authldaps' => [
        'name',
    ],
    'zentra_authmails' => [
        'name',
    ],
    'zentra_blacklistedmailcontents' => [
        'name',
    ],
    'zentra_businesscriticities' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_calendarsegments' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_cartridgeitems' => [
        'is_recursive',
    ],
    'zentra_certificates' => [
        'is_recursive',
    ],
    'zentra_clusters' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_computerantiviruses' => [
        'manufacturers_id',
    ],
    'zentra_computervirtualmachines' => [
        'virtualmachinetypes_id',
    ],
    'zentra_configs' => [
        'name',
    ],
    'zentra_consumableitems' => [
        'is_recursive',
    ],
    'zentra_contacts' => [
        'is_recursive',
    ],
    'zentra_contracts' => [
        'is_recursive',
        'is_template',
    ],
    'zentra_crontasks' => [
        'name',
    ],
    'zentra_dashboards_dashboards' => [
        'name',
    ],
    'zentra_dashboards_rights' => [
        'item' => ['itemtype', 'items_id'],
    ],
    'zentra_datacenters' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_dcrooms' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_devicesensors' => [
        'devicesensormodels_id',
    ],
    'zentra_documents' => [
        'is_recursive',
    ],
    'zentra_documents_items' => [
        'entities_id',
        'is_recursive',
        'date_mod',
    ],
    'zentra_domainrecords' => [
        'is_recursive',
    ],
    'zentra_domainrecordtypes' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_domainrelations' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_domains' => [
        'is_recursive',
    ],
    'zentra_domaintypes' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_enclosures' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_entities' => [
        'authldaps_id',
        'calendars_id',
        'entities_id_software',
        'name',
    ],
    'zentra_fieldblacklists' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_fieldunicities' => [
        'entities_id',
        'is_active',
        'is_recursive',
        'name',
    ],
    'zentra_groups' => [
        'is_recursive',
    ],
    'zentra_groups_users' => [
        'is_dynamic',
    ],
    'zentra_holidays' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_impactcompounds' => [
        'name',
    ],
    'zentra_ipaddresses' => [
        'name',
    ],
    'zentra_ipnetworks' => [
        'ipnetworks_id',
        'is_recursive',
    ],
    'zentra_ipnetworks_vlans' => [
        'vlans_id',
    ],
    'zentra_items_devicebatteries' => [
        'locations_id',
        'states_id',
    ],
    'zentra_items_devicefirmwares' => [
        'locations_id',
        'states_id',
    ],
    'zentra_items_devicegenerics' => [
        'locations_id',
        'states_id',
    ],
    'zentra_items_devicesensors' => [
        'locations_id',
        'states_id',
    ],
    'zentra_items_kanbans' => [
        'users_id',
        'date_creation',
        'date_mod',
    ],
    'zentra_items_operatingsystems' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_items_softwareversions' => [
        'is_deleted',
        'is_deleted_item',
        'is_template_item',
    ],
    'zentra_itilsolutions' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_knowbaseitemcategories' => [
        'knowbaseitemcategories_id',
    ],
    'zentra_knowbaseitems_comments' => [
        'knowbaseitems_id',
        'parent_comment_id',
        'users_id',
        'date_creation',
        'date_mod',
    ],
    'zentra_knowbaseitems_items' => [
        'knowbaseitems_id',
        'date_creation',
        'date_mod',
    ],
    'zentra_knowbaseitems_revisions' => [
        'users_id',
    ],
    'zentra_knowbaseitemtranslations' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_lines' => [
        'is_deleted',
        'groups_id',
        'linetypes_id',
        'locations_id',
        'states_id',
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_links' => [
        'is_recursive',
        'name',
    ],
    'zentra_mailcollectors' => [
        'name',
    ],
    'zentra_manuallinks' => [
        'name',
    ],
    'zentra_monitors' => [
        'date_mod',
    ],
    'zentra_networkaliases' => [
        'fqdns_id',
    ],
    'zentra_networkequipments' => [
        'is_recursive',
    ],
    'zentra_networkports' => [
        'name',
    ],
    'zentra_networkportwifis' => [
        'networkportwifis_id',
    ],
    'zentra_objectlocks' => [
        'users_id',
    ],
    'zentra_olalevels' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_olas' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_operatingsystemeditions' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_operatingsystemkernels' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_operatingsystemkernelversions' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_passivedcequipments' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_pdumodels' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_pdus' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_pdus_plugs' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_pdus_racks' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_planningexternalevents' => [
        'name',
    ],
    'zentra_planningexternaleventtemplates' => [
        'name',
    ],
    'zentra_plugins' => [
        'name',
    ],
    'zentra_printers' => [
        'is_recursive',
    ],
    'zentra_profilerights' => [
        'name',
    ],
    'zentra_profiles' => [
        'name',
    ],
    'zentra_projects' => [
        'is_deleted',
    ],
    'zentra_queuednotifications' => [
        'notificationtemplates_id',
    ],
    'zentra_rackmodels' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_racks' => [
        'date_creation',
        'date_mod',
        'name',
    ],
    'zentra_recurrentchanges' => [
        'calendars_id',
        'name',
    ],
    'zentra_refusedequipments' => [
        'name',
    ],
    'zentra_registeredids' => [
        'item' => ['itemtype', 'items_id'],
    ],
    'zentra_remindertranslations' => [
        'date_creation',
        'date_mod',
    ],
    'zentra_reminders' => [
        'name',
    ],
    'zentra_rulerightparameters' => [
        'name',
    ],
    'zentra_rules' => [
        'name',
    ],
    'zentra_savedsearches' => [
        'name',
    ],
    'zentra_softwarecategories' => [
        'name',
    ],
    'zentra_softwarelicenses' => [
        'is_recursive',
        'softwarelicenses_id',
    ],
    'zentra_softwarelicensetypes' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_softwares' => [
        'is_recursive',
    ],
    'zentra_slalevels' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_slas' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_ssovariables' => [
        'name',
    ],
    'zentra_states' => [
        'entities_id',
        'is_recursive',
    ],
    'zentra_suppliers' => [
        'is_recursive',
    ],
    'zentra_ticketrecurrents' => [
        'calendars_id',
        'name',
    ],
    'zentra_tickets_tickets' => [
        'tickets_id_2',
    ],
    'zentra_transfers' => [
        'name',
    ],
    'zentra_users' => [
        'auths_id',
        'default_requesttypes_id',
    ],
    'zentra_virtualmachinestates' => [
        'name',
    ],
    'zentra_virtualmachinesystems' => [
        'name',
    ],
    'zentra_virtualmachinetypes' => [
        'name',
    ],
    'zentra_vlans' => [
        'is_recursive',
    ],
    'zentra_wifinetworks' => [
        'is_recursive',
    ],
];
foreach ($missing_keys as $table => $fields) {
    foreach ($fields as $key => $field) {
        $migration->addKey($table, $field, is_numeric($key) ? '' : $key);
    }
}

// Add missing `date_creation` field on tables that already have `date_mod` field
$tables = [
    'zentra_apiclients',
    'zentra_appliances',
    'zentra_authmails',
    'zentra_transfers',
];
foreach ($tables as $table) {
    $migration->addField($table, 'date_creation', 'timestamp');
    $migration->addKey($table, 'date_creation');
}

// Add missing `date_mod` field on tables that already have `date_creation` field
$tables = [
    'zentra_lockedfields',
];
foreach ($tables as $table) {
    $migration->addField($table, 'date_mod', 'timestamp');
    $migration->addKey($table, 'date_mod');
}

// Rename `date` fields to `date_creation` when value is just a DB insert timestamp
$tables = [
    'zentra_knowbaseitems',
    'zentra_notepads',
    'zentra_projecttasks',
];
foreach ($tables as $table) {
    if ($DB->fieldExists($table, 'date', false)) {
        $migration->dropKey($table, 'date');
        $migration->migrationOneTable($table);
        $migration->changeField($table, 'date', 'date_creation', 'timestamp');
        $migration->addKey($table, 'date_creation');
    }
}
$migration->changeSearchOption(KnowbaseItem::class, 5, 121);
$migration->changeSearchOption(ProjectTask::class, 15, 121);

// Rename `zentra_objectlocks` `date_mod` to `date`
if ($DB->fieldExists('zentra_objectlocks', 'date_mod', false)) {
    $migration->dropKey('zentra_objectlocks', 'date_mod');
    $migration->migrationOneTable('zentra_objectlocks');
    $migration->changeField('zentra_objectlocks', 'date_mod', 'date', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    $migration->addKey('zentra_objectlocks', 'date');
}

// Rename `date_creation` to `date` when field refers to a valuable date and not just to db insert timestamps
$tables = [
    'zentra_knowbaseitems_revisions',
    'zentra_networkportconnectionlogs',
];
foreach ($tables as $table) {
    if ($DB->fieldExists($table, 'date_creation', false)) {
        $migration->dropKey($table, 'date_creation');
        $migration->migrationOneTable($table);
        $migration->changeField($table, 'date_creation', 'date', 'timestamp');
        $migration->addKey($table, 'date');
    }
}

// Replace -1 default values on entities_id foreign keys (visibility tables)
$tables = [
    'zentra_groups_knowbaseitems',
    'zentra_groups_reminders',
    'zentra_groups_rssfeeds',
    'zentra_knowbaseitems_profiles',
    'zentra_profiles_reminders',
    'zentra_profiles_rssfeeds',
];
foreach ($tables as $table) {
    $migration->addField($table, 'no_entity_restriction', 'boolean', ['update' => 0]);
    $migration->migrationOneTable($table); // Ensure 'no_entity_restriction' is created
    $DB->update(
        $table,
        ['entities_id' => 0, 'no_entity_restriction' => 1],
        ['entities_id' => -1]
    );
    $migration->changeField($table, 'entities_id', 'entities_id', "int {$default_key_sign} DEFAULT NULL");
    $migration->migrationOneTable($table); // Ensure 'entities_id' is nullable
    $DB->update(
        $table,
        ['entities_id' => 'NULL'],
        ['no_entity_restriction' => 1]
    );
}

// Replace -1 default values on zentra_rules.entities_id
$DB->update(
    'zentra_rules',
    ['entities_id' => 0],
    ['entities_id' => -1]
);

// Replace unused -1 default values on entities_id foreign keys
$tables = [
    'zentra_fieldunicities',
    'zentra_savedsearches',
];
foreach ($tables as $table) {
    $migration->changeField($table, 'entities_id', 'entities_id', "int {$default_key_sign} NOT NULL DEFAULT 0");
}

// Replace -1 default values on zentra_queuednotifications.items_id
$DB->update(
    'zentra_queuednotifications',
    ['items_id' => 0],
    ['items_id' => -1]
);
