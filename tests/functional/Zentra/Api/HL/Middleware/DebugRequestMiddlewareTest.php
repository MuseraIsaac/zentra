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

namespace tests\units\Zentra\Api\HL\Middleware;

use Zentra\Api\HL\Middleware\DebugRequestMiddleware;
use Zentra\Api\HL\Middleware\MiddlewareInput;
use Zentra\Api\HL\Route;
use Zentra\Api\HL\RoutePath;
use Zentra\Http\Request;
use Zentra\Tests\DbTestCase;

class DebugRequestMiddlewareTest extends DbTestCase
{
    public function testDebugModeEnabled()
    {
        $middleware = new DebugRequestMiddleware();
        $input = new MiddlewareInput(
            new Request('GET', '/', [
                'X-Debug-Mode' => 'true',
            ]),
            new RoutePath('', '', '', ['GET'], 1, Route::SECURITY_AUTHENTICATED, ''),
            null
        );
        // User not authenticated, so should fail permission check
        $middleware->process(
            $input,
            function () {
                $this->assertEquals(\Session::NORMAL_MODE, $_SESSION['zentra_use_mode']);
            }
        );

        $this->login('tech', 'tech');
        // This user doesn't have permission to use debug mode
        $middleware->process(
            $input,
            function () {
                $this->assertEquals(\Session::NORMAL_MODE, $_SESSION['zentra_use_mode']);
            }
        );

        $this->login();
        // This user has permission to use debug mode
        $middleware->process(
            $input,
            function () {
                $this->assertEquals(\Session::DEBUG_MODE, $_SESSION['zentra_use_mode']);
            }
        );
    }
}
