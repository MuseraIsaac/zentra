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

/** Rename 'name' criteria in dictionnaries */
//move criteria 'name' to 'os_name' for 'RuleDictionnaryOperatingSystem'
//move criteria 'name' to 'os_version' for 'RuleDictionnaryOperatingSystemVersion'
//move criteria 'name' to 'os_edition' for 'RuleDictionnaryOperatingSystemEdition'
//move criteria 'name' to 'arch_name' for 'RuleDictionnaryOperatingSystemArchitecture'
//move criteria 'name' to 'servicepack_name' for 'RuleDictionnaryOperatingSystemServicePack'

$subType = [
    'servicepack_name' => 'RuleDictionnaryOperatingSystemServicePack',
    'os_edition' => 'RuleDictionnaryOperatingSystemEdition',
    'arch_name' => 'RuleDictionnaryOperatingSystemArchitecture',
    'os_version' => 'RuleDictionnaryOperatingSystemVersion',
    'os_name' => 'RuleDictionnaryOperatingSystem',
];

//Get all zentra_rulecriteria with 'name' criteria for OS Dictionnary
$result = $DB->request(
    [
        'SELECT'    => [
            'zentra_rulecriterias.id AS criteria_id',
            'zentra_rulecriterias.criteria',
            'zentra_rules.sub_type',
        ],
        'FROM'      => 'zentra_rulecriterias',
        'LEFT JOIN' => [
            'zentra_rules' => [
                'FKEY' => [
                    'zentra_rulecriterias'   => 'rules_id',
                    'zentra_rules'            => 'id',
                ],
            ],
        ],
        'WHERE'     => [
            'zentra_rulecriterias.criteria'      => 'name',
            'zentra_rules.sub_type' => array_values($subType),
        ],
    ]
);

//foreach criteria, change 'name' key to desired
foreach ($result as $data) {
    $migration->addPostQuery(
        $DB->buildUpdate(
            'zentra_rulecriterias',
            [
                'criteria' => array_search($data['sub_type'], $subType),
            ],
            [
                'id' => $data['criteria_id'],
            ]
        )
    );
}
/** /Rename 'name' criteria in dictionnaries */

/** Init 'initialized_rules_collections' config */
$migration->addConfig(['initialized_rules_collections' => '[]']);
/** /Init 'initialized_rules_collections' config */

/** Fix 'contact' rule criteria */
$migration->addPostQuery(
    $DB->buildUpdate(
        'zentra_rulecriterias',
        [
            'pattern' => $DB->escape('/(.*)[,|\/]/'),
        ],
        [
            'id' => 19,
            'pattern' => '/(.*)[,|/]/',
        ]
    )
);
/** /Fix 'contact' rule criteria */
