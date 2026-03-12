<?php

/**
 * ---------------------------------------------------------------------
 *
 * ZENTRA - Gestionnaire Libre de Parc Informatique
 *
 * http://zentra-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
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

use Zentra\Asset\Asset_PeripheralAsset;
use Zentra\CalDAV\Contracts\CalDAVCompatibleItemInterface;
use Zentra\Socket;

/**
 * Relation constants between tables.
 *
 * This mapping is used for to detect links between objects.
 * For example, it is used to detect if items are associated to another item that
 * is going to be deleted, in order to prevent deletion or ask for user what to do with
 * linked items.
 *
 * Format is:
 * [
 *    'referenced_table_name' => [
 *       'linked_table_name_1' => 'foreign_key_1',
 *       'linked_table_name_2' => ['foreign_key_2', 'foreign_key_3'],
 *       'linked_table_name_2' => [['items_id', 'itemtype']],
 *    ]
 * ]
 * where:
 *  - 'referenced_table_name' is the name of a table having its id referenced in other tables,
 *  - 'linked_table_name_*' is the name of a table that have foreign keys referencing the table 'referenced_table_name',
 *  - 'foreign_key_*' is the name of the field that is a foreign key (can be ['items_id', 'itemtype']).
 *
 * /!\ "_" prefix is used to disable usage check on relations while deleting an item when they are
 *     handled by application.
 *     Applications handle specific usage check and links updates :
 *      - in `CommonDBTM::cleanRelationTable()` method,
 *      - in `$item::cleanDBonPurge()` method,
 *      - using `$forward_entity_to` values,
 *      - by `CommonTreeDropdown` logic for recursive keys.
 *     Relations will still be used to check ability to disable recursivity on an element.
 *
 * /!\ Table's names are in alphabetic order - Please respect it
 *
 * @var array $RELATION
 */
$RELATION = [

    'zentra_agents' => [
        'zentra_refusedequipments' => 'agents_id',
        'zentra_rulematchedlogs'   => 'agents_id',
        'zentra_unmanageds'        => 'agents_id',
    ],

    'zentra_agenttypes' => [
        '_zentra_agents' => 'agenttypes_id',
    ],

    'zentra_applianceenvironments' => [
        'zentra_appliances' => 'applianceenvironments_id',
    ],

    'zentra_appliances'     => [
        '_zentra_appliances_items' => 'appliances_id',
    ],

    'zentra_appliances_items' => [
        '_zentra_appliances_items_relations' => 'appliances_items_id',
    ],

    'zentra_appliancetypes' => [
        'zentra_appliances' => 'appliancetypes_id',
    ],

    'zentra_assets_assetdefinitions' => [
        '_zentra_assets_assets' => 'assets_assetdefinitions_id',
        '_zentra_assets_assetmodels' => 'assets_assetdefinitions_id',
        '_zentra_assets_assettypes' => 'assets_assetdefinitions_id',
        '_zentra_assets_customfielddefinitions' => 'assets_assetdefinitions_id',
    ],

    'zentra_assets_assetmodels' => [
        'zentra_assets_assets' => 'assets_assetmodels_id',
    ],

    'zentra_assets_assettypes' => [
        'zentra_assets_assets' => 'assets_assettypes_id',
    ],

    'zentra_databaseinstancetypes' => [
        'zentra_databaseinstances' => 'databaseinstancetypes_id',
    ],

    'zentra_authldaps' => [
        'zentra_authldapreplicates' => 'authldaps_id',
        'zentra_entities'           => 'authldaps_id',
        'zentra_users'              => 'auths_id',
    ],

    'zentra_authmails' => [
        'zentra_users' => 'auths_id',
    ],

    'zentra_autoupdatesystems' => [
        'zentra_clusters'          => 'autoupdatesystems_id',
        'zentra_computers'         => 'autoupdatesystems_id',
        'zentra_databaseinstances' => 'autoupdatesystems_id',
        'zentra_monitors'          => 'autoupdatesystems_id',
        'zentra_networkequipments' => 'autoupdatesystems_id',
        'zentra_peripherals'       => 'autoupdatesystems_id',
        'zentra_phones'            => 'autoupdatesystems_id',
        'zentra_printers'          => 'autoupdatesystems_id',
        'zentra_refusedequipments' => 'autoupdatesystems_id',
        'zentra_unmanageds'        => 'autoupdatesystems_id',
        'zentra_assets_assets'     => 'autoupdatesystems_id',
    ],

    'zentra_budgets' => [
        'zentra_changecosts'   => 'budgets_id',
        'zentra_contractcosts' => 'budgets_id',
        'zentra_infocoms'      => 'budgets_id',
        'zentra_problemcosts'  => 'budgets_id',
        'zentra_projectcosts'  => 'budgets_id',
        'zentra_ticketcosts'   => 'budgets_id',
    ],

    'zentra_budgettypes' => [
        'zentra_budgets' => 'budgettypes_id',
    ],

    'zentra_businesscriticities' => [
        '_zentra_businesscriticities' => 'businesscriticities_id',
        'zentra_infocoms'             => 'businesscriticities_id',
    ],

    'zentra_cablestrands' => [
        'zentra_cables' => 'cablestrands_id',
    ],

    'zentra_cabletypes' => [
        'zentra_cables' => 'cabletypes_id',
    ],

    'zentra_calendars' => [
        '_zentra_calendars_holidays' => 'calendars_id',
        '_zentra_calendarsegments'   => 'calendars_id',
        'zentra_entities'            => 'calendars_id',
        'zentra_olas'                => 'calendars_id',
        'zentra_slas'                => 'calendars_id',
        'zentra_slms'                => 'calendars_id',
        'zentra_recurrentchanges'    => 'calendars_id',
        'zentra_ticketrecurrents'    => 'calendars_id',
        'zentra_pendingreasons'     => 'calendars_id',
    ],

    'zentra_cartridgeitems' => [
        '_zentra_cartridgeitems_printermodels' => 'cartridgeitems_id',
        '_zentra_cartridges'                   => 'cartridgeitems_id',
    ],

    'zentra_cartridgeitemtypes' => [
        'zentra_cartridgeitems' => 'cartridgeitemtypes_id',
    ],

    'zentra_certificates' => [
        '_zentra_certificates_items' => 'certificates_id',
    ],

    'zentra_certificatetypes' => [
        'zentra_certificates' => 'certificatetypes_id',
    ],

    'zentra_changes' => [
        '_zentra_changecosts'           => 'changes_id',
        '_zentra_changes_changes'       => [
            'changes_id_1',
            'changes_id_2',
        ],
        '_zentra_changes_groups'        => 'changes_id',
        '_zentra_changes_items'         => 'changes_id',
        '_zentra_changes_problems'      => 'changes_id',
        '_zentra_changes_suppliers'     => 'changes_id',
        '_zentra_changes_tickets'       => 'changes_id',
        '_zentra_changes_users'         => 'changes_id',
        '_zentra_changesatisfactions'   => 'changes_id',
        '_zentra_changetasks'           => 'changes_id',
        '_zentra_changevalidations'     => 'changes_id',
        '_zentra_itils_projects'        => [['items_id', 'itemtype']],
        '_zentra_itilfollowups'         => [['items_id', 'itemtype']],
        '_zentra_itils_validationsteps' => [['items_id', 'itemtype']],
        '_zentra_itilsolutions'         => [['items_id', 'itemtype']],
    ],

    'zentra_changetemplates' => [
        'zentra_entities'                        => 'changetemplates_id',
        'zentra_itilcategories'                  => [
            'changetemplates_id',
        ],
        'zentra_changes'                         => 'changetemplates_id',
        '_zentra_changetemplatehiddenfields'     => 'changetemplates_id',
        '_zentra_changetemplatemandatoryfields'  => 'changetemplates_id',
        '_zentra_changetemplatepredefinedfields' => 'changetemplates_id',
        '_zentra_changetemplatereadonlyfields'   => 'changetemplates_id',
        'zentra_profiles'                        => 'changetemplates_id',
        'zentra_recurrentchanges'                => 'changetemplates_id',
    ],

    'zentra_clusters' => [
        '_zentra_items_clusters' => 'clusters_id',
    ],

    'zentra_clustertypes' => [
        'zentra_clusters' => 'clustertypes_id',
    ],

    'zentra_computermodels' => [
        'zentra_computers' => 'computermodels_id',
    ],

    'zentra_computers' => [
        'zentra_networknames' => [['items_id', 'itemtype']], // FIXME Find a list that can be used to declare this polymorphic relation
    ],

    'zentra_computertypes' => [
        'zentra_computers' => 'computertypes_id',
    ],

    'zentra_consumableitems' => [
        '_zentra_consumables' => 'consumableitems_id',
    ],

    'zentra_consumableitemtypes' => [
        'zentra_consumableitems' => 'consumableitemtypes_id',
    ],

    'zentra_contacts' => [
        '_zentra_contacts_suppliers' => 'contacts_id',
    ],

    'zentra_contacttypes' => [
        'zentra_contacts' => 'contacttypes_id',
    ],

    'zentra_contracts' => [
        '_zentra_contractcosts'       => 'contracts_id',
        '_zentra_contracts_items'     => 'contracts_id',
        '_zentra_contracts_suppliers' => 'contracts_id',
        'zentra_entities'             => 'contracts_id_default',
        '_zentra_tickets_contracts'   => 'contracts_id',
        '_zentra_contracts_users'    => 'contracts_id',
    ],

    'zentra_contracttypes' => [
        'zentra_contracts' => 'contracttypes_id',
    ],

    'zentra_crontasklogs' => [
        '_zentra_crontasklogs' => 'crontasklogs_id',
    ],

    'zentra_crontasks' => [
        '_zentra_crontasklogs' => 'crontasks_id',
    ],

    'zentra_dashboards_dashboards' => [
        '_zentra_dashboards_filters' => 'dashboards_dashboards_id',
        '_zentra_dashboards_items'   => 'dashboards_dashboards_id',
        '_zentra_dashboards_rights'  => 'dashboards_dashboards_id',
    ],

    'zentra_databaseinstancecategories' => [
        'zentra_databaseinstances' => 'databaseinstancecategories_id',
    ],

    'zentra_databaseinstances' => [
        'zentra_databases' => 'databaseinstances_id',
    ],

    'zentra_datacenters' => [
        'zentra_dcrooms' => 'datacenters_id',
    ],

    'zentra_dcrooms' => [
        'zentra_racks' => 'dcrooms_id',
    ],

    'zentra_devicebatteries' => [
        'zentra_items_devicebatteries' => 'devicebatteries_id',
    ],

    'zentra_devicebatterymodels' => [
        'zentra_devicebatteries' => 'devicebatterymodels_id',
    ],

    'zentra_devicecameramodels' => [
        'zentra_devicecameras' => 'devicecameramodels_id',
    ],

    'zentra_devicecameras' => [
        'zentra_items_devicecameras' => 'devicecameras_id',
    ],

    'zentra_devicebatterytypes' => [
        'zentra_devicebatteries' => 'devicebatterytypes_id',
    ],

    'zentra_devicecasemodels' => [
        'zentra_devicecases' => 'devicecasemodels_id',
    ],

    'zentra_devicecases' => [
        'zentra_items_devicecases' => 'devicecases_id',
    ],

    'zentra_devicecasetypes' => [
        'zentra_devicecases' => 'devicecasetypes_id',
    ],

    'zentra_devicecontrolmodels' => [
        'zentra_devicecontrols' => 'devicecontrolmodels_id',
    ],

    'zentra_devicecontrols' => [
        'zentra_items_devicecontrols' => 'devicecontrols_id',
    ],

    'zentra_devicedrivemodels' => [
        'zentra_devicedrives' => 'devicedrivemodels_id',
    ],

    'zentra_devicedrives' => [
        'zentra_items_devicedrives' => 'devicedrives_id',
    ],

    'zentra_devicefirmwaremodels' => [
        'zentra_devicefirmwares' => 'devicefirmwaremodels_id',
    ],

    'zentra_devicefirmwares' => [
        'zentra_items_devicefirmwares' => 'devicefirmwares_id',
    ],

    'zentra_devicefirmwaretypes' => [
        'zentra_devicefirmwares' => 'devicefirmwaretypes_id',
    ],

    'zentra_devicegenericmodels' => [
        'zentra_devicegenerics' => 'devicegenericmodels_id',
    ],

    'zentra_devicegenerics' => [
        'zentra_items_devicegenerics' => 'devicegenerics_id',
    ],

    'zentra_devicegenerictypes' => [
        'zentra_devicegenerics' => 'devicegenerictypes_id',
    ],

    'zentra_devicegraphiccardmodels' => [
        'zentra_devicegraphiccards' => 'devicegraphiccardmodels_id',
    ],

    'zentra_devicegraphiccards' => [
        'zentra_items_devicegraphiccards' => 'devicegraphiccards_id',
    ],

    'zentra_deviceharddrivemodels' => [
        'zentra_deviceharddrives' => 'deviceharddrivemodels_id',
    ],

    'zentra_deviceharddrivetypes' => [
        'zentra_deviceharddrives' => 'deviceharddrivetypes_id',
    ],

    'zentra_deviceharddrives' => [
        'zentra_items_deviceharddrives' => 'deviceharddrives_id',
    ],

    'zentra_devicememories' => [
        'zentra_items_devicememories' => 'devicememories_id',
    ],

    'zentra_devicememorymodels' => [
        'zentra_devicememories' => 'devicememorymodels_id',
    ],

    'zentra_devicememorytypes' => [
        'zentra_devicememories' => 'devicememorytypes_id',
    ],

    'zentra_devicemotherboardmodels' => [
        'zentra_devicemotherboards' => 'devicemotherboardmodels_id',
    ],

    'zentra_devicemotherboards' => [
        'zentra_items_devicemotherboards' => 'devicemotherboards_id',
    ],

    'zentra_devicenetworkcardmodels' => [
        'zentra_devicenetworkcards' => 'devicenetworkcardmodels_id',
        'zentra_devicepcis'         => 'devicenetworkcardmodels_id', // FIXME This field should probably removed
    ],

    'zentra_devicenetworkcards' => [
        'zentra_items_devicenetworkcards' => 'devicenetworkcards_id',
    ],

    'zentra_devicepcimodels' => [
        'zentra_devicepcis' => 'devicepcimodels_id',
    ],

    'zentra_devicepcis' => [
        'zentra_items_devicepcis' => 'devicepcis_id',
    ],

    'zentra_devicepowersupplies' => [
        'zentra_items_devicepowersupplies' => 'devicepowersupplies_id',
    ],

    'zentra_devicepowersupplymodels' => [
        'zentra_devicepowersupplies' => 'devicepowersupplymodels_id',
    ],

    'zentra_deviceprocessormodels' => [
        'zentra_deviceprocessors' => 'deviceprocessormodels_id',
    ],

    'zentra_deviceprocessors' => [
        'zentra_items_deviceprocessors' => 'deviceprocessors_id',
    ],

    'zentra_devicesensormodels' => [
        'zentra_devicesensors' => 'devicesensormodels_id',
    ],

    'zentra_devicesensors' => [
        'zentra_items_devicesensors' => 'devicesensors_id',
    ],

    'zentra_devicesensortypes' => [
        'zentra_devicesensors' => 'devicesensortypes_id',
    ],

    'zentra_devicesimcards' => [
        'zentra_items_devicesimcards' => 'devicesimcards_id',
    ],

    'zentra_devicesimcardtypes' => [
        'zentra_devicesimcards' => 'devicesimcardtypes_id',
    ],

    'zentra_devicesoundcardmodels' => [
        'zentra_devicesoundcards' => 'devicesoundcardmodels_id',
    ],

    'zentra_devicesoundcards' => [
        'zentra_items_devicesoundcards' => 'devicesoundcards_id',
    ],

    'zentra_documentcategories' => [
        'zentra_documentcategories' => 'documentcategories_id',
        'zentra_documents'          => 'documentcategories_id',
    ],

    'zentra_documents' => [
        '_zentra_documents_items' => 'documents_id',
    ],

    'zentra_domainrelations' => [
        'zentra_domains_items' => 'domainrelations_id',
    ],

    'zentra_domains'    => [
        '_zentra_domainrecords' => 'domains_id',
        '_zentra_domains_items' => 'domains_id',
    ],

    'zentra_domaintypes' => [
        'zentra_domains'  => 'domaintypes_id',
    ],

    'zentra_domainrecordtypes'    => [
        'zentra_domainrecords'  => 'domainrecordtypes_id',
    ],

    'zentra_dropdowns_dropdowndefinitions' => [
        '_zentra_dropdowns_dropdowns' => 'dropdowns_dropdowndefinitions_id',
    ],

    'zentra_dropdowns_dropdowns' => [
        'zentra_dropdowns_dropdowns' => 'dropdowns_dropdowns_id',
    ],

    'zentra_enclosuremodels' => [
        'zentra_enclosures' => 'enclosuremodels_id',
    ],

    'zentra_enclosures' => [
        '_zentra_items_enclosures' => 'enclosures_id',
    ],

    'zentra_entities' => [
        'zentra_agents'                      => 'entities_id',
        'zentra_apiclients'                  => 'entities_id',
        'zentra_appliances'                  => 'entities_id',
        'zentra_appliancetypes'              => 'entities_id',
        'zentra_assets_assets'               => 'entities_id',
        'zentra_budgets'                     => 'entities_id',
        'zentra_businesscriticities'         => 'entities_id',
        'zentra_cables'                      => 'entities_id',
        'zentra_calendars'                   => 'entities_id',
        '_zentra_calendarsegments'           => 'entities_id',
        'zentra_cartridgeitems'              => 'entities_id',
        '_zentra_cartridges'                 => 'entities_id',
        'zentra_certificates'                => 'entities_id',
        'zentra_certificatetypes'            => 'entities_id',
        '_zentra_changecosts'                => 'entities_id',
        'zentra_changes'                     => 'entities_id',
        'zentra_changetemplates'             => 'entities_id',
        '_zentra_changevalidations'          => 'entities_id',
        'zentra_clusters'                    => 'entities_id',
        'zentra_clustertypes'                => 'entities_id',
        'zentra_computers'                   => 'entities_id',
        'zentra_dropdowns_dropdowns'         => 'entities_id',
        'zentra_consumableitems'             => 'entities_id',
        '_zentra_consumables'                => 'entities_id',
        'zentra_contacts'                    => 'entities_id',
        '_zentra_contractcosts'              => 'entities_id',
        'zentra_contracts'                   => 'entities_id',
        'zentra_databaseinstances'           => 'entities_id',
        '_zentra_databases'                  => 'entities_id', // forwarded by Database
        'zentra_datacenters'                 => 'entities_id',
        'zentra_dcrooms'                     => 'entities_id',
        'zentra_devicebatteries'             => 'entities_id',
        'zentra_devicecameras'               => 'entities_id',
        'zentra_devicecases'                 => 'entities_id',
        'zentra_devicecontrols'              => 'entities_id',
        'zentra_devicedrives'                => 'entities_id',
        'zentra_devicefirmwares'             => 'entities_id',
        'zentra_devicegenerics'              => 'entities_id',
        'zentra_devicegraphiccards'          => 'entities_id',
        'zentra_deviceharddrives'            => 'entities_id',
        'zentra_devicememories'              => 'entities_id',
        'zentra_devicemotherboards'          => 'entities_id',
        'zentra_devicenetworkcards'          => 'entities_id',
        'zentra_devicepcis'                  => 'entities_id',
        'zentra_devicepowersupplies'         => 'entities_id',
        'zentra_deviceprocessors'            => 'entities_id',
        'zentra_devicesensors'               => 'entities_id',
        'zentra_devicesimcards'              => 'entities_id',
        'zentra_devicesoundcards'            => 'entities_id',
        'zentra_documents'                   => 'entities_id',
        '_zentra_documents_items'            => 'entities_id',
        'zentra_domainrelations'             => 'entities_id',
        'zentra_domainrecords'               => 'entities_id',
        'zentra_domainrecordtypes'           => 'entities_id',
        'zentra_domains'                     => 'entities_id',
        'zentra_domaintypes'                 => 'entities_id',
        'zentra_enclosures'                  => 'entities_id',
        '_zentra_entities'                   => 'entities_id',
        'zentra_entities'                    => 'entities_id_software',
        '_zentra_entities_knowbaseitems'     => 'entities_id',
        '_zentra_entities_reminders'         => 'entities_id',
        '_zentra_entities_rssfeeds'          => 'entities_id',
        'zentra_fieldblacklists'             => 'entities_id',
        'zentra_fieldunicities'              => 'entities_id',
        'zentra_forms_forms'                 => 'entities_id',
        'zentra_forms_answerssets'           => 'entities_id',
        'zentra_fqdns'                       => 'entities_id',
        'zentra_groups'                      => 'entities_id',
        'zentra_groups_knowbaseitems'        => 'entities_id',
        'zentra_groups_reminders'            => 'entities_id',
        'zentra_groups_rssfeeds'             => 'entities_id',
        'zentra_holidays'                    => 'entities_id',
        'zentra_imageformats'                => 'entities_id',
        'zentra_imageresolutions'            => 'entities_id',
        '_zentra_infocoms'                   => 'entities_id',
        'zentra_ipaddresses'                 => 'entities_id',
        'zentra_ipnetworks'                  => 'entities_id',
        '_zentra_items_devicebatteries'      => 'entities_id',
        '_zentra_items_devicecases'          => 'entities_id',
        '_zentra_items_devicecameras'        => 'entities_id', // forwarded by DeviceCamera
        '_zentra_items_devicecontrols'       => 'entities_id',
        '_zentra_items_devicedrives'         => 'entities_id',
        '_zentra_items_devicefirmwares'      => 'entities_id',
        '_zentra_items_devicegenerics'       => 'entities_id',
        '_zentra_items_devicegraphiccards'   => 'entities_id',
        '_zentra_items_deviceharddrives'     => 'entities_id',
        '_zentra_items_devicememories'       => 'entities_id',
        '_zentra_items_devicemotherboards'   => 'entities_id',
        '_zentra_items_devicenetworkcards'   => 'entities_id',
        '_zentra_items_devicepcis'           => 'entities_id',
        '_zentra_items_devicepowersupplies'  => 'entities_id',
        '_zentra_items_deviceprocessors'     => 'entities_id',
        '_zentra_items_devicesensors'        => 'entities_id',
        '_zentra_items_devicesimcards'       => 'entities_id',
        '_zentra_items_devicesoundcards'     => 'entities_id',
        '_zentra_items_disks'                => 'entities_id',
        '_zentra_items_operatingsystems'     => 'entities_id',
        '_zentra_items_softwareversions'     => 'entities_id',
        '_zentra_itemvirtualmachines'        => 'entities_id',
        'zentra_itilcategories'              => 'entities_id',
        'zentra_itilfollowuptemplates'       => 'entities_id',
        'zentra_itilvalidationtemplates'     => 'entities_id',
        'zentra_knowbaseitemcategories'      => 'entities_id',
        'zentra_knowbaseitems'               => 'entities_id',
        'zentra_knowbaseitems_profiles'      => 'entities_id',
        'zentra_lineoperators'               => 'entities_id',
        'zentra_lines'                       => 'entities_id',
        'zentra_links'                       => 'entities_id',
        'zentra_locations'                   => 'entities_id',
        'zentra_monitors'                    => 'entities_id',
        '_zentra_networkaliases'             => 'entities_id',
        'zentra_networkequipments'           => 'entities_id',
        'zentra_networknames'                => 'entities_id',
        '_zentra_networkports'               => 'entities_id',
        'zentra_networkporttypes'            => 'entities_id',
        'zentra_notifications'               => 'entities_id',
        '_zentra_olalevels'                  => 'entities_id',
        '_zentra_olas'                       => 'entities_id',
        'zentra_passivedcequipments'         => 'entities_id',
        'zentra_pcivendors'                  => 'entities_id',
        'zentra_pdus'                        => 'entities_id',
        'zentra_pdutypes'                    => 'entities_id',
        'zentra_pendingreasons'              => 'entities_id',
        'zentra_peripherals'                 => 'entities_id',
        'zentra_phones'                      => 'entities_id',
        'zentra_planningexternalevents'      => 'entities_id',
        'zentra_planningexternaleventtemplates' => 'entities_id',
        'zentra_printers'                    => 'entities_id',
        '_zentra_problemcosts'               => 'entities_id',
        'zentra_problems'                    => 'entities_id',
        'zentra_problemtemplates'            => 'entities_id',
        'zentra_profiles_reminders'          => 'entities_id',
        'zentra_profiles_rssfeeds'           => 'entities_id',
        '_zentra_profiles_users'             => 'entities_id',
        '_zentra_projectcosts'               => 'entities_id',
        'zentra_projects'                    => 'entities_id',
        '_zentra_projecttasks'               => 'entities_id',
        'zentra_projecttasktemplates'        => 'entities_id',
        'zentra_queuednotifications'         => 'entities_id',
        'zentra_racks'                       => 'entities_id',
        'zentra_racktypes'                   => 'entities_id',
        'zentra_recurrentchanges'            => 'entities_id',
        'zentra_refusedequipments'           => 'entities_id',
        '_zentra_reservationitems'           => 'entities_id',
        'zentra_rules'                       => 'entities_id',
        'zentra_savedsearches'               => 'entities_id',
        '_zentra_slalevels'                  => 'entities_id',
        '_zentra_slas'                       => 'entities_id',
        'zentra_slms'                        => 'entities_id',
        'zentra_softwarelicenses'            => 'entities_id',
        'zentra_softwarelicensetypes'        => 'entities_id',
        'zentra_softwares'                   => 'entities_id',
        '_zentra_softwareversions'           => 'entities_id',
        'zentra_solutiontemplates'           => 'entities_id',
        'zentra_solutiontypes'               => 'entities_id',
        'zentra_states'                      => 'entities_id',
        'zentra_suppliers'                   => 'entities_id',
        'zentra_taskcategories'              => 'entities_id',
        'zentra_tasktemplates'               => 'entities_id',
        '_zentra_ticketcosts'                => 'entities_id',
        'zentra_ticketrecurrents'            => 'entities_id',
        'zentra_tickets'                     => 'entities_id',
        'zentra_tickettemplates'             => 'entities_id',
        '_zentra_ticketvalidations'          => 'entities_id',
        'zentra_unmanageds'                  => 'entities_id',
        'zentra_usbvendors'                  => 'entities_id',
        'zentra_users'                       => 'entities_id',
        'zentra_vlans'                       => 'entities_id',
        'zentra_wifinetworks'                => 'entities_id',
        'zentra_webhooks'                    => 'entities_id',
        'zentra_queuedwebhooks'              => 'entities_id',
    ],

    'zentra_filesystems' => [
        'zentra_items_disks' => 'filesystems_id',
    ],

    'zentra_forms_answerssets' => [
        "_zentra_forms_destinations_answerssets_formdestinationitems" => "forms_answerssets_id",
    ],

    'zentra_forms_categories' => [
        'zentra_forms_categories' => 'forms_categories_id',
        'zentra_forms_forms' => 'forms_categories_id',
        'zentra_knowbaseitems' => 'forms_categories_id',
    ],

    'zentra_forms_forms' => [
        "_zentra_forms_accesscontrols_formaccesscontrols" => "forms_forms_id",
        "_zentra_forms_answerssets"                       => "forms_forms_id",
        "_zentra_forms_destinations_formdestinations"     => "forms_forms_id",
        "_zentra_forms_sections"                          => "forms_forms_id",
        "_zentra_helpdesks_tiles_formtiles"               => "forms_forms_id",
    ],

    'zentra_forms_sections' => [
        "_zentra_forms_questions" => "forms_sections_id",
        "_zentra_forms_comments" => "forms_sections_id",
    ],

    'zentra_fqdns' => [
        'zentra_networkaliases' => 'fqdns_id',
        'zentra_networknames'   => 'fqdns_id',
    ],

    'zentra_groups' => [
        '_zentra_changes_groups'       => 'groups_id',
        'zentra_changetasks'           => 'groups_id_tech',
        'zentra_groups'                => 'groups_id',
        '_zentra_groups_items'         => 'groups_id',
        '_zentra_groups_knowbaseitems' => 'groups_id',
        '_zentra_groups_problems'      => 'groups_id',
        '_zentra_groups_reminders'     => 'groups_id',
        '_zentra_groups_rssfeeds'      => 'groups_id',
        '_zentra_groups_tickets'       => 'groups_id',
        '_zentra_groups_users'         => 'groups_id',
        'zentra_itilcategories'        => 'groups_id',
        'zentra_planningexternalevents' => 'groups_id',
        'zentra_problemtasks'           => 'groups_id_tech',
        'zentra_projects'               => 'groups_id',
        'zentra_tasktemplates'          => 'groups_id_tech',
        'zentra_tickettasks'            => 'groups_id_tech',
        'zentra_users'                  => 'groups_id',
        'zentra_itilvalidationtemplates_targets' => 'groups_id',
    ],

    'zentra_holidays' => [
        '_zentra_calendars_holidays' => 'holidays_id',
    ],

    'zentra_imageformats' => [
        '_zentra_items_devicecameras_imageformats' => 'imageformats_id',
    ],

    'zentra_imageresolutions' => [
        '_zentra_items_devicecameras_imageresolutions' => 'imageresolutions_id',
    ],

    'zentra_impactcontexts' => [
        'zentra_impactitems' => 'impactcontexts_id',
    ],

    'zentra_interfacetypes' => [
        'zentra_devicecontrols'     => 'interfacetypes_id',
        'zentra_devicedrives'       => 'interfacetypes_id',
        'zentra_devicegraphiccards' => 'interfacetypes_id',
        'zentra_deviceharddrives'   => 'interfacetypes_id',
    ],

    'zentra_ipaddresses' => [
        '_zentra_ipaddresses_ipnetworks' => 'ipaddresses_id',
    ],

    'zentra_ipnetworks' => [
        '_zentra_ipaddresses_ipnetworks' => 'ipnetworks_id',
        'zentra_networknames'            => 'ipnetworks_id',
        'zentra_ipnetworks'              => 'ipnetworks_id',
        '_zentra_ipnetworks_vlans'       => 'ipnetworks_id',
    ],

    'zentra_items_devicecameras' => [
        '_zentra_items_devicecameras_imageformats' => 'items_devicecameras_id',
        '_zentra_items_devicecameras_imageresolutions' => 'items_devicecameras_id',
    ],

    'zentra_items_devicenetworkcards' => [
        'zentra_networkportethernets'     => 'items_devicenetworkcards_id',
        'zentra_networkportfiberchannels' => 'items_devicenetworkcards_id',
        'zentra_networkportwifis'         => 'items_devicenetworkcards_id',
    ],

    'zentra_itilcategories' => [
        'zentra_changes'        => 'itilcategories_id',
        'zentra_itilcategories' => 'itilcategories_id',
        'zentra_problems'       => 'itilcategories_id',
        'zentra_tickets'        => 'itilcategories_id',
    ],

    'zentra_itilfollowups' => [
        'zentra_itilsolutions' => 'itilfollowups_id',
    ],

    'zentra_itilfollowuptemplates' => [
        'zentra_pendingreasons' => 'itilfollowuptemplates_id',
    ],

    'zentra_itilvalidationtemplates' => [
        '_zentra_itilvalidationtemplates_targets' => 'itilvalidationtemplates_id',
        'zentra_changevalidations' => 'itilvalidationtemplates_id',
        'zentra_ticketvalidations' => 'itilvalidationtemplates_id',
    ],

    'zentra_itils_validationsteps' => [
        'zentra_ticketvalidations' => 'itils_validationsteps_id',
        'zentra_changevalidations' => 'itils_validationsteps_id',
    ],

    'zentra_knowbaseitemcategories' => [
        'zentra_itilcategories'            => 'knowbaseitemcategories_id',
        'zentra_knowbaseitemcategories'    => 'knowbaseitemcategories_id',
        '_zentra_knowbaseitems_knowbaseitemcategories' => 'knowbaseitemcategories_id',
        'zentra_taskcategories'            => 'knowbaseitemcategories_id',
    ],

    'zentra_knowbaseitems' => [
        '_zentra_entities_knowbaseitems'   => 'knowbaseitems_id',
        '_zentra_groups_knowbaseitems'     => 'knowbaseitems_id',
        '_zentra_knowbaseitems_comments'   => 'knowbaseitems_id',
        '_zentra_knowbaseitems_items'      => 'knowbaseitems_id',
        '_zentra_knowbaseitems_profiles'   => 'knowbaseitems_id',
        '_zentra_knowbaseitems_revisions'  => 'knowbaseitems_id',
        '_zentra_knowbaseitems_users'      => 'knowbaseitems_id',
        '_zentra_knowbaseitemtranslations' => 'knowbaseitems_id',
        '_zentra_knowbaseitems_knowbaseitemcategories' => 'knowbaseitems_id',
    ],

    'zentra_knowbaseitems_comments' => [
        'zentra_knowbaseitems_comments' => 'parent_comment_id',
    ],

    'zentra_lineoperators' => [
        'zentra_lines' => 'lineoperators_id',
    ],

    'zentra_lines' => [
        'zentra_items_devicesimcards' => 'lines_id',
        '_zentra_items_lines' => 'lines_id',
    ],

    'zentra_linetypes' => [
        'zentra_lines' => 'linetypes_id',
    ],

    'zentra_links' => [
        '_zentra_links_itemtypes' => 'links_id',
    ],

    'zentra_locations' => [
        'zentra_appliances'                => 'locations_id',
        'zentra_assets_assets'             => 'locations_id',
        'zentra_budgets'                   => 'locations_id',
        'zentra_cartridgeitems'            => 'locations_id',
        'zentra_certificates'              => 'locations_id',
        'zentra_changes'                   => 'locations_id',
        'zentra_computers'                 => 'locations_id',
        'zentra_contracts'                 => 'locations_id',
        'zentra_consumableitems'           => 'locations_id',
        'zentra_databaseinstances'         => 'locations_id',
        'zentra_datacenters'               => 'locations_id',
        'zentra_dcrooms'                   => 'locations_id',
        'zentra_devicegenerics'            => 'locations_id',
        'zentra_devicesensors'             => 'locations_id',
        'zentra_enclosures'                => 'locations_id',
        'zentra_items_devicebatteries'     => 'locations_id',
        'zentra_items_devicecameras'       => 'locations_id',
        'zentra_items_devicecases'         => 'locations_id',
        'zentra_items_devicecontrols'      => 'locations_id',
        'zentra_items_devicedrives'        => 'locations_id',
        'zentra_items_devicefirmwares'     => 'locations_id',
        'zentra_items_devicegenerics'      => 'locations_id',
        'zentra_items_devicegraphiccards'  => 'locations_id',
        'zentra_items_deviceharddrives'    => 'locations_id',
        'zentra_items_devicememories'      => 'locations_id',
        'zentra_items_devicemotherboards'  => 'locations_id',
        'zentra_items_devicenetworkcards'  => 'locations_id',
        'zentra_items_devicepcis'          => 'locations_id',
        'zentra_items_devicepowersupplies' => 'locations_id',
        'zentra_items_deviceprocessors'    => 'locations_id',
        'zentra_items_devicesensors'       => 'locations_id',
        'zentra_items_devicesimcards'      => 'locations_id',
        'zentra_items_devicesoundcards'    => 'locations_id',
        'zentra_lines'                     => 'locations_id',
        'zentra_locations'                 => 'locations_id',
        'zentra_monitors'                  => 'locations_id',
        'zentra_networkequipments'         => 'locations_id',
        'zentra_passivedcequipments'       => 'locations_id',
        'zentra_pdus'                      => 'locations_id',
        'zentra_peripherals'               => 'locations_id',
        'zentra_phones'                    => 'locations_id',
        'zentra_printers'                  => 'locations_id',
        'zentra_problems'                  => 'locations_id',
        'zentra_racks'                     => 'locations_id',
        'zentra_sockets'                   => 'locations_id',
        'zentra_softwarelicenses'          => 'locations_id',
        'zentra_softwares'                 => 'locations_id',
        'zentra_tickets'                   => 'locations_id',
        'zentra_unmanageds'                => 'locations_id',
        'zentra_users'                     => 'locations_id',
    ],

    'zentra_mailcollectors' => [
        'zentra_notimportedemails' => 'mailcollectors_id',
    ],

    'zentra_manufacturers' => [
        'zentra_appliances'          => 'manufacturers_id',
        'zentra_assets_assets'       => 'manufacturers_id',
        'zentra_cartridgeitems'      => 'manufacturers_id',
        'zentra_certificates'        => 'manufacturers_id',
        'zentra_itemantiviruses'    => 'manufacturers_id',
        'zentra_computers'           => 'manufacturers_id',
        'zentra_consumableitems'     => 'manufacturers_id',
        'zentra_databaseinstances'   => 'manufacturers_id',
        'zentra_devicebatteries'     => 'manufacturers_id',
        'zentra_devicecameras'       => 'manufacturers_id',
        'zentra_devicecases'         => 'manufacturers_id',
        'zentra_devicecontrols'      => 'manufacturers_id',
        'zentra_devicedrives'        => 'manufacturers_id',
        'zentra_devicefirmwares'     => 'manufacturers_id',
        'zentra_devicegenerics'      => 'manufacturers_id',
        'zentra_devicegraphiccards'  => 'manufacturers_id',
        'zentra_deviceharddrives'    => 'manufacturers_id',
        'zentra_devicememories'      => 'manufacturers_id',
        'zentra_devicemotherboards'  => 'manufacturers_id',
        'zentra_devicenetworkcards'  => 'manufacturers_id',
        'zentra_devicepcis'          => 'manufacturers_id',
        'zentra_devicepowersupplies' => 'manufacturers_id',
        'zentra_deviceprocessors'    => 'manufacturers_id',
        'zentra_devicesensors'       => 'manufacturers_id',
        'zentra_devicesimcards'      => 'manufacturers_id',
        'zentra_devicesoundcards'    => 'manufacturers_id',
        'zentra_enclosures'          => 'manufacturers_id',
        'zentra_monitors'            => 'manufacturers_id',
        'zentra_networkequipments'   => 'manufacturers_id',
        'zentra_passivedcequipments' => 'manufacturers_id',
        'zentra_pdus'                => 'manufacturers_id',
        'zentra_peripherals'         => 'manufacturers_id',
        'zentra_phones'              => 'manufacturers_id',
        'zentra_printers'            => 'manufacturers_id',
        'zentra_racks'               => 'manufacturers_id',
        'zentra_softwarelicenses'    => 'manufacturers_id',
        'zentra_softwares'           => 'manufacturers_id',
        'zentra_unmanageds'          => 'manufacturers_id',
    ],

    'zentra_monitormodels' => [
        'zentra_monitors' => 'monitormodels_id',
    ],

    'zentra_monitortypes' => [
        'zentra_monitors' => 'monitortypes_id',
    ],

    'zentra_networkequipmentmodels' => [
        'zentra_networkequipments' => 'networkequipmentmodels_id',
    ],

    'zentra_networkequipmenttypes' => [
        'zentra_networkequipments' => 'networkequipmenttypes_id',
    ],

    'zentra_networknames' => [
        '_zentra_networkaliases' => 'networknames_id',
    ],

    'zentra_networkportfiberchanneltypes' => [
        'zentra_networkportfiberchannels' => 'networkportfiberchanneltypes_id',
    ],

    'zentra_networkports' => [
        '_zentra_networkportaggregates'     => 'networkports_id',
        '_zentra_networkportaliases'        => 'networkports_id',
        'zentra_networkportaliases'         => 'networkports_id_alias',
        '_zentra_networkportconnectionlogs'  => [
            'networkports_id_destination',
            'networkports_id_source',
        ],
        '_zentra_networkportdialups'        => 'networkports_id',
        '_zentra_networkportethernets'      => 'networkports_id',
        '_zentra_networkportfiberchannels'  => 'networkports_id',
        '_zentra_networkportlocals'         => 'networkports_id',
        '_zentra_networkportmetrics'        => 'networkports_id',
        '_zentra_networkports_networkports' => [
            'networkports_id_1',
            'networkports_id_2',
        ],
        '_zentra_networkports_vlans'        => 'networkports_id',
        '_zentra_networkportwifis'          => 'networkports_id',
        'zentra_sockets'                    => 'networkports_id',
    ],

    'zentra_networkportwifis' => [
        'zentra_networkportwifis' => 'networkportwifis_id',
    ],

    'zentra_networks' => [
        'zentra_computers'         => 'networks_id',
        'zentra_networkequipments' => 'networks_id',
        'zentra_printers'          => 'networks_id',
        'zentra_unmanageds'        => 'networks_id',
    ],

    'zentra_notifications' => [
        '_zentra_notifications_notificationtemplates' => 'notifications_id',
        '_zentra_notificationtargets'                 => 'notifications_id',
    ],

    'zentra_notificationtemplates' => [
        '_zentra_notifications_notificationtemplates' => 'notificationtemplates_id',
        '_zentra_notificationtemplatetranslations'    => 'notificationtemplates_id',
        '_zentra_queuednotifications'                 => 'notificationtemplates_id',
    ],

    'zentra_olalevels' => [
        '_zentra_olalevelactions'   => 'olalevels_id',
        '_zentra_olalevelcriterias' => 'olalevels_id',
        '_zentra_olalevels_tickets' => 'olalevels_id',
        'zentra_tickets'            => 'olalevels_id_ttr',
    ],

    'zentra_olas' => [
        'zentra_olalevels' => 'olas_id',
        'zentra_tickets'   => [
            'olas_id_ttr',
            'olas_id_tto',
        ],
    ],

    'zentra_operatingsystemarchitectures' => [
        'zentra_items_operatingsystems' => 'operatingsystemarchitectures_id',
    ],

    'zentra_operatingsystemeditions' => [
        'zentra_items_operatingsystems' => 'operatingsystemeditions_id',
    ],

    'zentra_operatingsystemkernels' => [
        'zentra_operatingsystemkernelversions' => 'operatingsystemkernels_id',
    ],

    'zentra_operatingsystemkernelversions' => [
        'zentra_items_operatingsystems' => 'operatingsystemkernelversions_id',
    ],

    'zentra_operatingsystems' => [
        'zentra_items_operatingsystems' => 'operatingsystems_id',
        'zentra_softwareversions'        => 'operatingsystems_id',
    ],

    'zentra_operatingsystemservicepacks' => [
        'zentra_items_operatingsystems' => 'operatingsystemservicepacks_id',
    ],

    'zentra_operatingsystemversions' => [
        'zentra_items_operatingsystems' => 'operatingsystemversions_id',
    ],

    'zentra_passivedcequipmentmodels' => [
        'zentra_passivedcequipments' => 'passivedcequipmentmodels_id',
    ],

    'zentra_passivedcequipmenttypes' => [
        'zentra_passivedcequipments' => 'passivedcequipmenttypes_id',
    ],

    'zentra_pendingreasons' => [
        '_zentra_pendingreasons_items' => 'pendingreasons_id',
        'zentra_itilreminders' => 'pendingreasons_id',
        'zentra_itilfollowuptemplates' => 'pendingreasons_id',
        'zentra_tasktemplates' => 'pendingreasons_id',
    ],

    'zentra_pdumodels' => [
        'zentra_pdus' => 'pdumodels_id',
    ],

    'zentra_pdus' => [
        '_zentra_pdus_racks' => 'pdus_id',
    ],

    'zentra_pdutypes' => [
        'zentra_pdus' => 'pdutypes_id',
    ],

    'zentra_peripheralmodels' => [
        'zentra_peripherals' => 'peripheralmodels_id',
    ],

    'zentra_peripheraltypes' => [
        'zentra_peripherals' => 'peripheraltypes_id',
    ],

    'zentra_phonemodels' => [
        'zentra_phones' => 'phonemodels_id',
    ],

    'zentra_phonepowersupplies' => [
        'zentra_phones' => 'phonepowersupplies_id',
    ],

    'zentra_phonetypes' => [
        'zentra_phones' => 'phonetypes_id',
    ],

    'zentra_planningeventcategories' => [
        'zentra_planningexternalevents' => 'planningeventcategories_id',
        'zentra_planningexternaleventtemplates' => 'planningeventcategories_id',
    ],

    'zentra_planningexternaleventtemplates' => [
        'zentra_planningexternalevents' => 'planningexternaleventtemplates_id',
    ],

    'zentra_plugs' => [
        '_zentra_items_plugs' => 'plugs_id',
    ],

    'zentra_printermodels' => [
        '_zentra_cartridgeitems_printermodels' => 'printermodels_id',
        'zentra_printers'                      => 'printermodels_id',
    ],

    'zentra_printers' => [
        '_zentra_cartridges'              => 'printers_id',
        '_zentra_printerlogs'             => ['items_id', 'itemtype'],
        '_zentra_printers_cartridgeinfos' => 'printers_id',
    ],

    'zentra_printertypes' => [
        'zentra_printers' => 'printertypes_id',
    ],

    'zentra_problems' => [
        '_zentra_changes_problems'   => 'problems_id',
        '_zentra_groups_problems'    => 'problems_id',
        '_zentra_items_problems'     => 'problems_id',
        '_zentra_itils_projects'     => [['items_id', 'itemtype']],
        '_zentra_itilfollowups'      => [['items_id', 'itemtype']],
        '_zentra_itilsolutions'      => [['items_id', 'itemtype']],
        '_zentra_problemcosts'       => 'problems_id',
        '_zentra_problems_problems'  => [
            'problems_id_1',
            'problems_id_2',
        ],
        '_zentra_problems_suppliers' => 'problems_id',
        '_zentra_problems_tickets'   => 'problems_id',
        '_zentra_problems_users'     => 'problems_id',
        '_zentra_problemtasks'       => 'problems_id',
    ],

    'zentra_problemtemplates' => [
        'zentra_entities'                         => 'problemtemplates_id',
        'zentra_itilcategories'                   => [
            'problemtemplates_id',
        ],
        'zentra_problems'                         => 'problemtemplates_id',
        '_zentra_problemtemplatehiddenfields'     => 'problemtemplates_id',
        '_zentra_problemtemplatemandatoryfields'  => 'problemtemplates_id',
        '_zentra_problemtemplatepredefinedfields' => 'problemtemplates_id',
        '_zentra_problemtemplatereadonlyfields'   => 'problemtemplates_id',
        'zentra_profiles'                         => 'problemtemplates_id',
    ],

    'zentra_profiles' => [
        '_zentra_knowbaseitems_profiles'         => 'profiles_id',
        '_zentra_profilerights'                  => 'profiles_id',
        '_zentra_profiles_reminders'             => 'profiles_id',
        '_zentra_profiles_rssfeeds'              => 'profiles_id',
        '_zentra_profiles_users'                 => 'profiles_id',
        'zentra_users'                           => 'profiles_id',
    ],

    'zentra_projects' => [
        '_zentra_itils_projects'      => 'projects_id',
        '_zentra_items_projects'      => 'projects_id',
        '_zentra_projectcosts'        => 'projects_id',
        'zentra_projects'             => 'projects_id',
        '_zentra_projecttasks'        => 'projects_id',
        'zentra_projecttasktemplates' => 'projects_id',
        '_zentra_projectteams'        => 'projects_id',
    ],

    'zentra_projectstates' => [
        'zentra_projects'             => 'projectstates_id',
        'zentra_projecttasks'         => 'projectstates_id',
        'zentra_projecttasktemplates' => 'projectstates_id',
    ],

    'zentra_projecttasks' => [
        '_zentra_projecttasklinks'     => [
            'projecttasks_id_source',
            'projecttasks_id_target',
        ],
        'zentra_projecttasks'          => 'projecttasks_id',
        '_zentra_projecttasks_tickets' => 'projecttasks_id',
        '_zentra_projecttaskteams'     => 'projecttasks_id',
        'zentra_projecttasktemplates'  => 'projecttasks_id',
    ],

    'zentra_projecttasktemplates' => [
        'zentra_projecttasks' => 'projecttasktemplates_id',
    ],

    'zentra_projecttasktypes' => [
        'zentra_projecttasks'         => 'projecttasktypes_id',
        'zentra_projecttasktemplates' => 'projecttasktypes_id',
    ],

    'zentra_projecttypes' => [
        'zentra_projects' => 'projecttypes_id',
    ],

    'zentra_rackmodels' => [
        'zentra_racks' => 'rackmodels_id',
    ],

    'zentra_racks' => [
        '_zentra_items_racks' => 'racks_id',
        '_zentra_pdus_racks'  => 'racks_id',
    ],

    'zentra_racktypes' => [
        'zentra_racks' => 'racktypes_id',
    ],

    'zentra_reminders' => [
        '_zentra_entities_reminders'   => 'reminders_id',
        '_zentra_groups_reminders'     => 'reminders_id',
        '_zentra_profiles_reminders'   => 'reminders_id',
        '_zentra_remindertranslations' => 'reminders_id',
        '_zentra_reminders_users'      => 'reminders_id',
    ],

    'zentra_requesttypes' => [
        'zentra_itilfollowups'         => 'requesttypes_id',
        'zentra_itilfollowuptemplates' => 'requesttypes_id',
        'zentra_tickets'               => 'requesttypes_id',
        'zentra_users'                 => 'default_requesttypes_id',
    ],

    'zentra_reservationitems' => [
        '_zentra_reservations' => 'reservationitems_id',
    ],

    'zentra_rssfeeds' => [
        '_zentra_entities_rssfeeds' => 'rssfeeds_id',
        '_zentra_groups_rssfeeds'   => 'rssfeeds_id',
        '_zentra_profiles_rssfeeds' => 'rssfeeds_id',
        '_zentra_rssfeeds_users'    => 'rssfeeds_id',
    ],

    'zentra_rules' => [
        'zentra_refusedequipments' => 'rules_id',
        '_zentra_ruleactions'      => 'rules_id',
        '_zentra_rulecriterias'    => 'rules_id',
        'zentra_rulematchedlogs'   => 'rules_id',
    ],

    'zentra_savedsearches' => [
        '_zentra_savedsearches_alerts' => 'savedsearches_id',
        '_zentra_savedsearches_users'  => 'savedsearches_id',
    ],

    'zentra_slalevels' => [
        '_zentra_slalevelactions'   => 'slalevels_id',
        '_zentra_slalevelcriterias' => 'slalevels_id',
        '_zentra_slalevels_tickets' => 'slalevels_id',
        'zentra_tickets'            => 'slalevels_id_ttr',
    ],

    'zentra_slas' => [
        'zentra_slalevels' => 'slas_id',
        'zentra_tickets'   => [
            'slas_id_ttr',
            'slas_id_tto',
        ],
    ],

    'zentra_slms' => [
        '_zentra_olas' => 'slms_id',
        '_zentra_slas' => 'slms_id',
    ],

    'zentra_snmpcredentials' => [
        'zentra_networkequipments' => 'snmpcredentials_id',
        'zentra_printers'          => 'snmpcredentials_id',
        'zentra_unmanageds'        => 'snmpcredentials_id',
    ],

    'zentra_socketmodels' => [
        'zentra_cables' => [
            'socketmodels_id_endpoint_a',
            'socketmodels_id_endpoint_b',
        ],
        'zentra_sockets' => 'socketmodels_id',
    ],

    'zentra_sockets' => [
        'zentra_cables' => [
            'sockets_id_endpoint_a',
            'sockets_id_endpoint_b',
        ],
    ],

    'zentra_softwarecategories' => [
        '_zentra_softwarecategories' => 'softwarecategories_id',
        'zentra_softwares'           => 'softwarecategories_id',
    ],

    'zentra_softwarelicenses' => [
        '_zentra_items_softwarelicenses'     => 'softwarelicenses_id',
        '_zentra_softwarelicenses'           => 'softwarelicenses_id',
        '_zentra_softwarelicenses_users'             => 'softwarelicenses_id',
    ],

    'zentra_softwarelicensetypes' => [
        'zentra_softwarelicenses'      => 'softwarelicensetypes_id',
        '_zentra_softwarelicensetypes' => 'softwarelicensetypes_id',
    ],

    'zentra_softwares' => [
        '_zentra_softwarelicenses' => 'softwares_id',
        'zentra_softwares'         => 'softwares_id',
        '_zentra_softwareversions' => 'softwares_id',
    ],

    'zentra_softwareversions' => [
        '_zentra_items_softwareversions'     => 'softwareversions_id',
        'zentra_softwarelicenses'            => [
            'softwareversions_id_buy',
            'softwareversions_id_use',
        ],
    ],

    'zentra_solutiontemplates' => [
        'zentra_pendingreasons' => 'solutiontemplates_id',
    ],

    'zentra_solutiontypes' => [
        'zentra_itilsolutions'     => 'solutiontypes_id',
        'zentra_solutiontemplates' => 'solutiontypes_id',
    ],

    'zentra_states' => [
        'zentra_appliances'                => 'states_id',
        'zentra_assets_assets'             => 'states_id',
        'zentra_cables'                    => 'states_id',
        'zentra_certificates'              => 'states_id',
        'zentra_clusters'                  => 'states_id',
        'zentra_computers'                 => 'states_id',
        'zentra_contracts'                 => 'states_id',
        'zentra_databaseinstances'         => 'states_id',
        'zentra_enclosures'                => 'states_id',
        'zentra_items_devicebatteries'     => 'states_id',
        'zentra_items_devicecameras'       => 'states_id',
        'zentra_items_devicecases'         => 'states_id',
        'zentra_items_devicecontrols'      => 'states_id',
        'zentra_items_devicedrives'        => 'states_id',
        'zentra_items_devicefirmwares'     => 'states_id',
        'zentra_items_devicegenerics'      => 'states_id',
        'zentra_items_devicegraphiccards'  => 'states_id',
        'zentra_items_deviceharddrives'    => 'states_id',
        'zentra_items_devicememories'      => 'states_id',
        'zentra_items_devicemotherboards'  => 'states_id',
        'zentra_items_devicenetworkcards'  => 'states_id',
        'zentra_items_devicepcis'          => 'states_id',
        'zentra_items_devicepowersupplies' => 'states_id',
        'zentra_items_deviceprocessors'    => 'states_id',
        'zentra_items_devicesensors'       => 'states_id',
        'zentra_items_devicesimcards'      => 'states_id',
        'zentra_items_devicesoundcards'    => 'states_id',
        'zentra_lines'                     => 'states_id',
        'zentra_monitors'                  => 'states_id',
        'zentra_networkequipments'         => 'states_id',
        'zentra_passivedcequipments'       => 'states_id',
        'zentra_pdus'                      => 'states_id',
        'zentra_peripherals'               => 'states_id',
        'zentra_phones'                    => 'states_id',
        'zentra_printers'                  => 'states_id',
        'zentra_racks'                     => 'states_id',
        'zentra_softwarelicenses'          => 'states_id',
        'zentra_softwareversions'          => 'states_id',
        'zentra_states'                    => 'states_id',
        'zentra_unmanageds'                => 'states_id',
    ],

    'zentra_suppliers' => [
        '_zentra_changes_suppliers'   => 'suppliers_id',
        '_zentra_contacts_suppliers'  => 'suppliers_id',
        '_zentra_contracts_suppliers' => 'suppliers_id',
        'zentra_infocoms'             => 'suppliers_id',
        '_zentra_problems_suppliers'  => 'suppliers_id',
        '_zentra_suppliers_tickets'   => 'suppliers_id',
    ],

    'zentra_suppliertypes' => [
        'zentra_suppliers' => 'suppliertypes_id',
    ],

    'zentra_taskcategories' => [
        'zentra_changetasks'    => 'taskcategories_id',
        'zentra_problemtasks'   => 'taskcategories_id',
        'zentra_taskcategories' => 'taskcategories_id',
        'zentra_tasktemplates'  => 'taskcategories_id',
        'zentra_tickettasks'    => 'taskcategories_id',
    ],

    'zentra_tasktemplates' => [
        'zentra_changetasks'  => 'tasktemplates_id',
        'zentra_problemtasks' => 'tasktemplates_id',
        'zentra_tickettasks'  => 'tasktemplates_id',
    ],

    'zentra_ticketrecurrents' => [
        '_zentra_items_ticketrecurrents' => 'ticketrecurrents_id',
    ],

    'zentra_tickets' => [
        '_zentra_changes_tickets'       => 'tickets_id',
        'zentra_documents'              => 'tickets_id',
        '_zentra_groups_tickets'        => 'tickets_id',
        '_zentra_items_tickets'         => 'tickets_id',
        '_zentra_itils_projects'        => [['items_id', 'itemtype']],
        '_zentra_itilfollowups'         => [['items_id', 'itemtype']],
        '_zentra_itils_validationsteps' => [['items_id', 'itemtype']],
        '_zentra_itilsolutions'         => [['items_id', 'itemtype']],
        '_zentra_olalevels_tickets'     => 'tickets_id',
        '_zentra_problems_tickets'      => 'tickets_id',
        '_zentra_projecttasks_tickets'  => 'tickets_id',
        '_zentra_slalevels_tickets'     => 'tickets_id',
        '_zentra_suppliers_tickets'     => 'tickets_id',
        '_zentra_ticketcosts'           => 'tickets_id',
        '_zentra_tickets_contracts'     => 'tickets_id',
        '_zentra_tickets_tickets'       => [
            'tickets_id_1',
            'tickets_id_2',
        ],
        '_zentra_tickets_users'         => 'tickets_id',
        '_zentra_ticketsatisfactions'   => 'tickets_id',
        '_zentra_tickettasks'           => 'tickets_id',
        '_zentra_ticketvalidations'     => 'tickets_id',
    ],

    'zentra_tickettemplates' => [
        'zentra_entities'                        => 'tickettemplates_id',
        'zentra_itilcategories'                  => [
            'tickettemplates_id_incident',
            'tickettemplates_id_demand',
        ],
        'zentra_profiles'                        => 'tickettemplates_id',
        'zentra_tickets'                         => 'tickettemplates_id',
        'zentra_ticketrecurrents'                => 'tickettemplates_id',
        '_zentra_tickettemplatehiddenfields'     => 'tickettemplates_id',
        '_zentra_tickettemplatemandatoryfields'  => 'tickettemplates_id',
        '_zentra_tickettemplatepredefinedfields' => 'tickettemplates_id',
        '_zentra_tickettemplatereadonlyfields'   => 'tickettemplates_id',
    ],

    'zentra_transfers' => [
        'zentra_entities' => 'transfers_id',
    ],

    'zentra_usercategories' => [
        'zentra_users' => 'usercategories_id',
    ],

    'zentra_users' => [
        'zentra_appliances'             => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_assets_assets'            => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_cables'                   => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_cartridgeitems'           => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_certificates'             => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_changes'                  => [
            'users_id_recipient',
            'users_id_lastupdater',
        ],
        '_zentra_changes_users'           => 'users_id',
        'zentra_changetasks'              => [
            'users_id',
            'users_id_editor',
            'users_id_tech',
        ],
        'zentra_changevalidations'        => [
            'users_id',
            'users_id_validate',
        ],
        'zentra_clusters'                 => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_computers'                => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_consumableitems'          => [
            'users_id_tech',
            'users_id',
        ],
        '_zentra_dashboards_dashboards'   => 'users_id',
        'zentra_dashboards_filters'       => 'users_id',
        'zentra_databaseinstances'        => [
            'users_id_tech',
            'users_id',
        ],
        '_zentra_displaypreferences'      => 'users_id',
        'zentra_domains'                  => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_domainrecords'            => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_documents'                => 'users_id',
        'zentra_documents_items'          => 'users_id',
        'zentra_enclosures'               => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_forms_answerssets'        => 'users_id',
        '_zentra_groups_users'            => 'users_id',
        'zentra_items_devicesimcards'     => [
            'users_id_tech',
            'users_id',
        ],
        '_zentra_items_kanbans'           => 'users_id',
        'zentra_itilcategories'           => 'users_id',
        'zentra_itilfollowups'            => [
            'users_id',
            'users_id_editor',
        ],
        'zentra_itilsolutions'            => [
            'users_id_approval',
            'users_id_editor',
            'users_id',
        ],
        'zentra_knowbaseitems'            => 'users_id',
        'zentra_knowbaseitems_comments'   => 'users_id',
        'zentra_knowbaseitems_revisions'  => 'users_id',
        '_zentra_knowbaseitems_users'     => 'users_id',
        'zentra_knowbaseitemtranslations' => 'users_id',
        'zentra_lines'                    => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_monitors'                 => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_networkequipments'        => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_notepads'                 => [
            'users_id',
            'users_id_lastupdater',
        ],
        'zentra_notimportedemails'        => 'users_id',
        '_zentra_objectlocks'             => 'users_id',
        'zentra_passivedcequipments'      => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_pdus'                     => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_peripherals'              => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_phones'                   => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_planningexternalevents'   => 'users_id',
        'zentra_planningrecalls'          => 'users_id',
        'zentra_printers'                 => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_problems'                 => [
            'users_id_recipient',
            'users_id_lastupdater',
        ],
        '_zentra_problems_users'          => 'users_id',
        'zentra_problemtasks'             => [
            'users_id',
            'users_id_editor',
            'users_id_tech',
        ],
        '_zentra_profiles_users'          => 'users_id',
        'zentra_projects'                 => 'users_id',
        'zentra_projecttasks'             => 'users_id',
        'zentra_projecttasktemplates'     => 'users_id',
        'zentra_racks'                    => [
            'users_id_tech',
            'users_id',
        ],
        '_zentra_reminders'               => 'users_id',
        '_zentra_reminders_users'         => 'users_id',
        '_zentra_remindertranslations'    => 'users_id',
        'zentra_reservations'             => 'users_id',
        'zentra_rssfeeds'                 => 'users_id',
        '_zentra_rssfeeds_users'          => 'users_id',
        '_zentra_savedsearches'           => 'users_id',
        '_zentra_savedsearches_users'     => 'users_id',
        'zentra_softwarelicenses'         => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_softwares'                => [
            'users_id_tech',
            'users_id',
        ],
        'zentra_tasktemplates'            => 'users_id_tech',
        'zentra_tickets'                  => [
            'users_id_recipient',
            'users_id_lastupdater',
        ],
        '_zentra_tickets_users'           => 'users_id',
        'zentra_tickettasks'              => [
            'users_id',
            'users_id_editor',
            'users_id_tech',
        ],
        'zentra_ticketvalidations'        => [
            'users_id',
            'users_id_validate',
        ],
        'zentra_unmanageds'               => [
            'users_id_tech',
            'users_id',
        ],
        '_zentra_useremails'              => 'users_id',
        'zentra_users'                    => 'users_id_supervisor',
        '_zentra_validatorsubstitutes'     => [
            'users_id',
            'users_id_substitute',
        ],
        '_zentra_softwarelicenses_users'          => 'users_id',
        '_zentra_contracts_users'         => 'users_id',
    ],

    'zentra_usertitles' => [
        'zentra_contacts' => 'usertitles_id',
        'zentra_users'    => 'usertitles_id',
    ],

    'zentra_validationsteps' => [
        'zentra_itilvalidationtemplates' => 'validationsteps_id',
        'zentra_itils_validationsteps' => 'validationsteps_id',
    ],

    'zentra_virtualmachinestates' => [
        'zentra_itemvirtualmachines' => 'virtualmachinestates_id',
    ],

    'zentra_virtualmachinesystems' => [
        'zentra_itemvirtualmachines' => 'virtualmachinesystems_id',
    ],

    'zentra_virtualmachinetypes' => [
        'zentra_itemvirtualmachines' => 'virtualmachinetypes_id',
    ],

    'zentra_vlans' => [
        '_zentra_ipnetworks_vlans'   => 'vlans_id',
        '_zentra_networkports_vlans' => 'vlans_id',
    ],

    'zentra_wifinetworks' => [
        'zentra_networkportwifis' => 'wifinetworks_id',
    ],
    'zentra_webhooks' => [
        '_zentra_queuedwebhooks' => 'webhooks_id',
    ],

    'zentra_webhookcategories' => [
        'zentra_webhookcategories'    => 'webhookcategories_id',
        'zentra_webhooks'             => 'webhookcategories_id',
    ],

];

$add_mapping_entry = static function (string $source_table, string $target_table_key, string|array $relation_fields) use (&$RELATION) {
    if (!array_key_exists($source_table, $RELATION)) {
        $RELATION[$source_table] = [];
    }
    if (!array_key_exists($target_table_key, $RELATION[$source_table])) {
        $RELATION[$source_table][$target_table_key] = [];
    }
    if (!is_array($RELATION[$source_table][$target_table_key])) {
        $RELATION[$source_table][$target_table_key] = [$RELATION[$source_table][$target_table_key]];
    }

    if (!in_array($relation_fields, $RELATION[$source_table][$target_table_key], true)) {
        $RELATION[$source_table][$target_table_key][] = $relation_fields;
    }
};

// Add polymorphic relations based on configuration.
global $CFG_ZENTRA;
$specifically_managed_types = [
    Agent::class, // FIXME Agent should be a CommonDBChild with $mustBeAttached=true
    Consumable::class, // Consumables are handled manually to redefine `date_out` to `null`
    DatabaseInstance::class, // FIXME DatabaseInstance should be a CommonDBChild with $mustBeAttached=true
    Item_Cluster::class, // FIXME $mustBeAttached_1 and $mustBeAttached_2 should probably be set to true
    Item_Enclosure::class, // FIXME $mustBeAttached_1 and $mustBeAttached_2 should probably be set to true
    Item_Rack::class, // FIXME $mustBeAttached_1 and $mustBeAttached_2 should probably be set to true
];
$polymorphic_types_mapping = [
    Agent::class                   => $CFG_ZENTRA['agent_types'],
    Appliance_Item::class          => $CFG_ZENTRA['appliance_types'],
    Appliance_Item_Relation::class => $CFG_ZENTRA['appliance_relation_types'],
    Certificate_Item::class        => $CFG_ZENTRA['certificate_types'],
    Change_Item::class             => $CFG_ZENTRA['ticket_types'],
    Consumable::class              => $CFG_ZENTRA['consumables_types'],
    Contract_Item::class           => $CFG_ZENTRA['contract_types'],
    DatabaseInstance::class        => $CFG_ZENTRA['databaseinstance_types'],
    Document_Item::class           => Document::getItemtypesThatCanHave(),
    Domain_Item::class             => $CFG_ZENTRA['domain_types'],
    Infocom::class                 => Infocom::getItemtypesThatCanHave(),
    Item_Cluster::class            => $CFG_ZENTRA['cluster_types'],
    Item_Disk::class               => $CFG_ZENTRA['disk_types'],
    Item_Enclosure::class          => $CFG_ZENTRA['rackable_types'],
    Item_Kanban::class             => $CFG_ZENTRA['kanban_types'],
    Item_OperatingSystem::class    => $CFG_ZENTRA['operatingsystem_types'],
    Item_Problem::class            => $CFG_ZENTRA['ticket_types'],
    Item_Project::class            => $CFG_ZENTRA['project_asset_types'],
    Item_Rack::class               => $CFG_ZENTRA['rackable_types'],
    Item_SoftwareLicense::class    => $CFG_ZENTRA['software_types'],
    Item_SoftwareVersion::class    => $CFG_ZENTRA['software_types'],
    Item_Ticket::class             => $CFG_ZENTRA['ticket_types'],
    ItemAntivirus::class           => $CFG_ZENTRA['itemantivirus_types'],
    ItemVirtualMachine::class      => $CFG_ZENTRA['itemvirtualmachines_types'],
    KnowbaseItem_Item::class       => $CFG_ZENTRA['kb_types'],
    NetworkPort::class             => $CFG_ZENTRA['networkport_types'],
    ReservationItem::class         => $CFG_ZENTRA['reservation_types'],
    Socket::class                  => $CFG_ZENTRA['socket_types'],
    Item_Plug::class               => $CFG_ZENTRA['plug_types'],
];
foreach (Item_Devices::getDeviceTypes() as $itemdevice_itemtype) {
    $source_itemtypes = $itemdevice_itemtype::itemAffinity();
    if (in_array('*', $source_itemtypes)) {
        $source_itemtypes = $CFG_ZENTRA['itemdevices_types'];
    }
    $polymorphic_types_mapping[$itemdevice_itemtype] = $source_itemtypes;
    $specifically_managed_types[] = $itemdevice_itemtype; // Item_Devices is handled manually to take care of `keep_devices` option
}
$polymorphic_types_mapping[VObject::class] = [];
foreach ($CFG_ZENTRA['planning_types'] as $planning_itemtype) {
    if (is_a($planning_itemtype, CalDAVCompatibleItemInterface::class, true)) {
        $polymorphic_types_mapping[VObject::class][] = $planning_itemtype;
    }
}

foreach ($polymorphic_types_mapping as $target_itemtype => $source_itemtypes) {
    foreach ($source_itemtypes as $source_itemtype) {
        $target_table_key_prefix = '';
        if (
            in_array($target_itemtype, $specifically_managed_types)
            || (
                is_a($target_itemtype, CommonDBChild::class, true)
                && $target_itemtype::$itemtype === 'itemtype'
                && $target_itemtype::$items_id === 'items_id'
                && $target_itemtype::$mustBeAttached === true
            )
            || (
                is_a($target_itemtype, CommonDBRelation::class, true)
                && (
                    (
                        $target_itemtype::$itemtype_1 === 'itemtype'
                        && $target_itemtype::$items_id_1 === 'items_id'
                        && $target_itemtype::$mustBeAttached_1 === true
                    )
                    || (
                        $target_itemtype::$itemtype_2 === 'itemtype'
                        && $target_itemtype::$items_id_2 === 'items_id'
                        && $target_itemtype::$mustBeAttached_2 === true
                    )
                )
            )
        ) {
            // If item must be attached, target table key has to be prefixed by "_"
            // to be ignored by `CommonDBTM::cleanRelationData()`. Indeed, without usage of this prefix,
            // related item will be preserved with its foreign key defined to 0, making it an unwanted orphaned item.
            $target_table_key_prefix = '_';
        }
        /** @var class-string<CommonDBTM> $target_itemtype */
        $target_table_key = $target_table_key_prefix . $target_itemtype::getTable();
        $source_table     = $source_itemtype::getTable();

        $add_mapping_entry($source_table, $target_table_key, ['items_id', 'itemtype']);
    }
}

// IPAddress specific case
// mainitems_id/mainitemtype are mainly a copy of item related to source NetworkPort
foreach ($CFG_ZENTRA['networkport_types'] as $source_itemtype) {
    $target_table_key = IPAddress::getTable();
    $source_table     = $source_itemtype::getTable();

    $add_mapping_entry($source_table, $target_table_key, ['mainitems_id', 'mainitemtype']);
}

// Asset_PeripheralAsset specific case
foreach ($CFG_ZENTRA['directconnect_types'] as $directconnect_itemtype) {
    $target_table_key = Asset_PeripheralAsset::getTable();
    $source_table     = $directconnect_itemtype::getTable();

    $add_mapping_entry($source_table, $target_table_key, ['itemtype_peripheral', 'items_id_peripheral']);
}
foreach (Asset_PeripheralAsset::getPeripheralHostItemtypes() as $peripheralhost_itemtype) {
    $target_table_key = Asset_PeripheralAsset::getTable();
    $source_table     = $peripheralhost_itemtype::getTable();

    $add_mapping_entry($source_table, $target_table_key, ['itemtype_asset', 'items_id_asset']);
}

// Multiple groups assignments
$assignable_itemtypes = $CFG_ZENTRA['assignable_types'];
foreach ($assignable_itemtypes as $assignable_itemtype) {
    $source_table_key = $assignable_itemtype::getTable();

    $add_mapping_entry($source_table_key, '_zentra_groups_items', ['itemtype', 'items_id']);
}
