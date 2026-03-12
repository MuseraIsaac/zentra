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

require_once(__DIR__ . '/_check_webserver_config.php');

use Zentra\Event;
use Zentra\Exception\Http\BadRequestHttpException;

/**
 * @since 0.85
 */

$item = new Item_Project();

if (isset($_POST["add"])) {
    $item->check(-1, CREATE, $_POST);

    if ($item->add($_POST)) {
        Event::log(
            $_POST["projects_id"],
            "project",
            4,
            "maintain",
            //TRANS: %s is the user login
            sprintf(__('%s adds a link with an item'), $_SESSION["zentraname"])
        );
    }
    Html::back();
}

throw new BadRequestHttpException();
