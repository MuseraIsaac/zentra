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

use Zentra\System\Requirement\LogsWriteAccess;
use Zentra\Tests\ZENTRATestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use org\bovigo\vfs\vfsStream;

class LogsWriteAccessTest extends ZENTRATestCase
{
    public function testCheckOnExistingWritableDir()
    {
        vfsStream::setup('root', 0o777, []);

        $logger = new Logger('test_log');
        $logger->pushHandler(new StreamHandler(vfsStream::url('root/test.log')));

        $instance = new LogsWriteAccess($logger);
        $this->assertTrue($instance->isValidated());
        $this->assertEquals(
            ['The log file has been created successfully.'],
            $instance->getValidationMessages()
        );
    }

    public function testCheckOnExistingProtectedDir()
    {
        vfsStream::setup('root', 0o555, []);

        $logger = new Logger('test_log');
        $logger->pushHandler(new StreamHandler(vfsStream::url('root/test.log')));

        $instance = new LogsWriteAccess($logger);
        $this->assertFalse($instance->isValidated());
        $this->assertEquals(
            ['The log file could not be created in ' . ZENTRA_LOG_DIR . '.'],
            $instance->getValidationMessages()
        );
    }
}
