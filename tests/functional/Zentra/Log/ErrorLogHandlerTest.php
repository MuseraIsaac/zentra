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

namespace tests\units\Zentra\Log;

use Zentra\Log\ErrorLogHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class ErrorLogHandlerTest extends TestCase
{
    private Logger $logger;

    protected function setUp(): void
    {
        $this->logger = new Logger('zentra_test');
        $this->logger->pushHandler(new ErrorLogHandler());

        assert(!file_exists($this->getDefaultFilePath()) || unlink($this->getDefaultFilePath()));

        parent::setUp();
    }


    public function test_Log_LogsAreWrittenInLogFile(): void
    {
        $this->logger->error('This is an error message');

        $this->assertFileExists($this->getDefaultFilePath());
    }

    public function test_Log_SeeExpectedContentsInLogFile(): void
    {
        $message = 'This is a test error';
        $this->logger->error($message);

        $this->assertStringContainsString($message, file_get_contents($this->getDefaultFilePath()));
    }

    public function test_Log_FilterRootPathInLogFile(): void
    {
        $PHPLOGGER = new Logger('zentra_test');
        $PHPLOGGER->pushHandler(new ErrorLogHandler());

        $message = 'This is a test error at ' . ZENTRA_ROOT;
        $contextInfo = 'some path ' . ZENTRA_ROOT;
        $this->logger->error($message, ['context' => $contextInfo]);

        $this->assertStringNotContainsString(ZENTRA_ROOT, file_get_contents($this->getDefaultFilePath()));
    }

    /**
     * value hardcoded in \Zentra\Log\ErrorLogHandler::__construct()
     */
    private function getDefaultFilePath(): string
    {
        return ZENTRA_LOG_DIR . "/php-errors.log";
    }
}
