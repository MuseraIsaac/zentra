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

use Zentra\Application\Environment;
use Zentra\Inventory\Conf;
use Zentra\Inventory\Request;
use Zentra\Kernel\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';

$kernel = new Kernel(Environment::TESTING->value);
$kernel->boot();

$conf = new Conf();
if ($conf->enabled_inventory != 1) {
    die("Inventory is disabled");
}

if (!isCommandLine()) {
    die('This script is only available from the command line');
}

$f = fopen('php://stdin', 'r');
$contents = '';
while ($line = fgets($f)) {
    $contents .= $line;
}
fclose($f);

try {
    $inventory_request = new Request();
    $inventory_request->handleRequest($contents);
} catch (Throwable $e) {
    $inventory_request->addError($e->getMessage());
}

$inventory_request->handleMessages();
