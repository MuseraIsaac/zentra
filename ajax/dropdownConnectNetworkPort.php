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

/**
 * @since 0.84
 */

use Zentra\DBAL\QueryExpression;

global $DB;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight("networking", UPDATE);

// Make a select box
if (
    class_exists($_POST["itemtype"])
    && isset($_POST["item"])
) {
    $table = getTableForItemType($_POST["itemtype"]);

    $joins = [];
    $name_field = new QueryExpression("'' AS " . $DB->quoteName('socketname'));

    if ($_POST['instantiation_type'] == 'NetworkPortEthernet') {
        $name_field = 'zentra_sockets.name AS socketname';
        $joins = [
            'zentra_networkportethernets'   => [
                'ON'  => [
                    'zentra_networkportethernets'   => 'id',
                    'zentra_networkports'           => 'id',
                ],
            ],
            'zentra_sockets'              => [
                'ON'  => [
                    'zentra_networkports'   => 'id',
                    'zentra_sockets'        => 'networkports_id',
                ],
            ],
        ];
    }

    $criteria = [
        'SELECT'    => [
            'zentra_networkports_networkports.id AS wid',
            'zentra_networkports.id AS did',
            "$table.name AS cname",
            'zentra_networkports.name AS nname',
            $name_field,
        ],
        'DISTINCT'  => true,
        'FROM'      => $table,
        'LEFT JOIN' => [
            'zentra_networkports'  => [
                'ON'  => [
                    'zentra_networkports'  => 'items_id',
                    $table               => 'id', [
                        'AND' => [
                            'zentra_networkports.items_id'           => $_POST['item'],
                            'zentra_networkports.itemtype'           => $_POST["itemtype"],
                            'zentra_networkports.instantiation_type' => $_POST['instantiation_type'],
                        ],
                    ],
                ],
            ],
            'zentra_networkports_networkports' => [
                'ON'  => [
                    'zentra_networkports_networkports' => 'networkports_id_1',
                    'zentra_networkports'              => 'id', [
                        'OR'  => [
                            'zentra_networkports_networkports.networkports_id_2' => new QueryExpression($DB->quoteName('zentra_networkports.id')),
                        ],
                    ],
                ],
            ],
        ] + $joins,
        'WHERE'     => [
            'zentra_networkports_networkports.id' => null,
            'NOT'                               => ['zentra_networkports.id' => null],
            'zentra_networkports.id'              => ['<>', $_POST['networkports_id']],
            "$table.is_deleted"                 => 0,
            "$table.is_template"                => 0,
        ],
        'ORDERBY'   => 'zentra_networkports.id',
    ];
    $iterator = $DB->request($criteria);

    echo "<br>";

    $values = [];
    foreach ($iterator as $data) {
        // Device name + port name
        $output = $output_long = $data['cname'];

        if (!empty($data['nname'])) {
            $output      = sprintf(__('%1$s - %2$s'), $output, $data['nname']);
            //TRANS: %1$s is device name, %2$s is port name
            $output_long = sprintf(__('%1$s - The port %2$s'), $output_long, $data['nname']);
        }

        // display netpoint (which will be copied)
        if (!empty($data['socketname'])) {
            $output      = sprintf(__('%1$s - %2$s'), $output, $data['socketname']);
            //TRANS: %1$s is a string (device name - port name...), %2$s is network outlet name
            $output_long = sprintf(__('%1$s - Network outlet %2$s'), $output_long, $data['socketname']);
        }
        $ID = $data['did'];

        if ($_SESSION["zentrais_ids_visible"] || empty($output) || empty($output_long)) {
            $output      = sprintf(__('%1$s (%2$s)'), $output, $ID);
            $output_long = sprintf(__('%1$s (%2$s)'), $output_long, $ID);
        }
        $values[$ID] = $output_long;
    }
    Dropdown::showFromArray($_POST['myname'], $values, ['display_emptychoice' => true]);
}
