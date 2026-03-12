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

namespace tests\units\Zentra\Toolbox;

use Zentra\Tests\ZENTRATestCase;
use Zentra\Toolbox\DatabaseSchema;
use PHPUnit\Framework\Attributes\DataProvider;

class DatabaseSchemaTest extends ZENTRATestCase
{
    public static function versionsProvider(): iterable
    {
        foreach (['-dev', '-alpha', '-alpha1', '-alpha3', '-beta', '-beta2', '-rc', '-rc1', ''] as $suffix) {
            // Unavailable versions
            foreach (['0.72', '0.72.21', '0.78', '0.78.5'] as $version) {
                yield [
                    'version'  => $version . $suffix,
                    'expected' => null,
                ];
            }

            // Current version and all of its pre-releases should return the default schema file
            $current_version = preg_replace('/^(\d+\.\d+\.\d+)(-\w+)?/', '\1', ZENTRA_VERSION);
            yield [
                'version'  => $current_version . $suffix,
                'expected' => 'zentra-empty.sql',
            ];

            // Any other supported version and all its pre-releases should return their specific schema file
            foreach (['9.3.1', '9.4.4', '9.5.7', '10.0.1'] as $version) {
                yield [
                    'version'  => $version . $suffix,
                    'expected' => sprintf('zentra-%s-empty.sql', $version),
                ];
            }
        }
    }

    #[DataProvider('versionsProvider')]
    public function testGetEmptySchemaPath(string $version, ?string $expected): void
    {
        $instance = new DatabaseSchema();
        if ($expected !== null) {
            $expected = realpath(ZENTRA_ROOT) . '/install/mysql/' . $expected;
        }
        $this->assertEquals($expected, $instance->getEmptySchemaPath($version));
    }
}
