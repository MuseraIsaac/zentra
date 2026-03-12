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

global $DB;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight("software", UPDATE);

if ($_POST['softwares_id'] > 0) {
    if (!isset($_POST['value'])) {
        $_POST['value'] = 0;
    }

    $where = [];
    if (isset($_POST['used'])) {
        $used = $_POST['used'];
        if (count($used)) {
            $where = ['NOT' => ['zentra_softwareversions.id' => $used]];
        }
    }
    // Make a select box
    $iterator = $DB->request([
        'SELECT'    => ['zentra_softwareversions.*', 'zentra_states.name AS sname'],
        'DISTINCT'  => true,
        'FROM'      => 'zentra_softwareversions',
        'LEFT JOIN' => [
            'zentra_states'  => [
                'ON'  => [
                    'zentra_softwareversions' => 'states_id',
                    'zentra_states'           => 'id',
                ],
            ],
        ],
        'WHERE'     => ['zentra_softwareversions.softwares_id' => $_POST['softwares_id']] + $where,
    ]);
    $number = count($iterator);

    $values = [];
    foreach ($iterator as $data) {
        $ID = $data['id'];
        $output = $data['name'];

        if (empty($output) || $_SESSION['zentrais_ids_visible']) {
            $output = sprintf(__('%1$s (%2$s)'), $output, $ID);
        }
        if (!empty($data['sname'])) {
            $output = sprintf(__('%1$s - %2$s'), $output, $data['sname']);
        }
        $values[$ID] = $output;
    }

    Dropdown::showFromArray($_POST['myname'], $values, ['display_emptychoice' => true]);
}
