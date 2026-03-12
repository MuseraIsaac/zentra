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

use Zentra\System\Requirement\PhpVersion;
use Zentra\Tests\ZENTRATestCase;

class PhpVersionTest extends ZENTRATestCase
{
    public function testCheckWithUpToDateVersion()
    {
        $instance = new PhpVersion(ZENTRA_MIN_PHP, ZENTRA_MAX_PHP);
        $this->assertTrue($instance->isValidated());
        $this->assertEquals(
            ['PHP version (' . PHP_VERSION . ') is supported.'],
            $instance->getValidationMessages()
        );
    }

    public function testCheckOutdatedVersion()
    {
        $instance = new PhpVersion('20.7', '20.8');
        $this->assertFalse($instance->isValidated());
        $this->assertEquals(
            [
                'PHP version must be between 20.7 and 20.8.',
            ],
            $instance->getValidationMessages()
        );
    }
}
