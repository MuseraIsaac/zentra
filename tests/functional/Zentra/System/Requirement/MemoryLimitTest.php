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

namespace tests\units\Zentra\System\Requirement;

use Zentra\System\Requirement\MemoryLimit;
use Zentra\Tests\ZENTRATestCase;

class MemoryLimitTest extends ZENTRATestCase
{
    public function testCheckWithEnoughMemory()
    {
        $instance = new MemoryLimit(32 * 1024 * 1024);
        $this->assertTrue($instance->isValidated());
        $this->assertEquals(
            ['Allocated memory is sufficient.'],
            $instance->getValidationMessages()
        );
    }

    public function testCheckWithNotEnoughMemory()
    {
        $instance = new MemoryLimit(16 * 1024 * 1024 * 1024);
        $this->assertFalse($instance->isValidated());
        $this->assertEquals(
            [
                'Allocated memory: ' . \Toolbox::getSize(\Toolbox::getMemoryLimit()),
                'A minimum of 16 GiB is commonly required for ZENTRA.',
                'Try increasing the memory_limit parameter in the php.ini file.',
            ],
            $instance->getValidationMessages()
        );
    }
}
