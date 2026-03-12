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

namespace tests\units;

use Zentra\Asset\Capacity;
use Zentra\Asset\Capacity\HasAntivirusCapacity;
use Zentra\Features\Clonable;
use Zentra\Tests\DbTestCase;
use ItemAntivirus;
use Toolbox;

class ItemAntivirusTest extends DbTestCase
{
    public function testRelatedItemHasTab()
    {
        global $CFG_ZENTRA;

        $this->initAssetDefinition(capacities: [new Capacity(name: HasAntivirusCapacity::class)]);

        $this->login(); // tab will be available only if corresponding right is available in the current session

        foreach ($CFG_ZENTRA['itemantivirus_types'] as $itemtype) {
            $item = $this->createItem(
                $itemtype,
                $this->getMinimalCreationInput($itemtype)
            );

            $tabs = $item->defineAllTabs();
            $this->assertArrayHasKey('ItemAntivirus$1', $tabs, $itemtype);
        }
    }

    public function testRelatedItemCloneRelations()
    {
        global $CFG_ZENTRA;

        $this->initAssetDefinition(capacities: [new Capacity(name: HasAntivirusCapacity::class)]);

        foreach ($CFG_ZENTRA['itemantivirus_types'] as $itemtype) {
            if (!Toolbox::hasTrait($itemtype, Clonable::class)) {
                continue;
            }

            $item = \getItemForItemtype($itemtype);
            $this->assertContains(ItemAntivirus::class, $item->getCloneRelations(), $itemtype);
        }
    }
}
