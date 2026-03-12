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

namespace tests\units\Zentra\Kernel\Listener\PostBootListener;

use Zentra\Http\SessionManager;
use Zentra\Kernel\Listener\PostBootListener\SessionStart;
use Zentra\Tests\ZENTRATestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Session;

class SessionStartTest extends ZENTRATestCase
{
    public static function bootContextProvider(): iterable
    {
        yield [
            'php_sapi'     => 'cli',
            'is_stateless' => true,
            'use_cookies'  => 1, // PHP default value, we do not change this in CLI context
        ];

        yield [
            'php_sapi'     => 'cli',
            'is_stateless' => false, // it should not affect the CLI context
            'use_cookies'  => 1, // PHP default value, we do not change this in CLI context
        ];

        foreach (['apache', 'apache2handler', 'cgi-fcgi', 'fpm-fcgi'] as $php_sapi) {
            /*
             * Cannot be tested as it would force a call to `ini_set('session.use_cookies')` that is impossible in the PHPUnit context
             * due to output already sent at this moment.
             * It will be almost impossible to do it a different way unless all the direct $_SESSION usages are removed from ZENTRA.
            yield [
                'php_sapi'     => $php_sapi,
                'is_stateless' => true,
                'use_cookies'  => 0,
            ];
            */
            yield [
                'php_sapi'     => $php_sapi,
                'is_stateless' => false,
                'use_cookies'  => 1,
            ];
        }
    }

    #[DataProvider('bootContextProvider')]
    public function testOnPostBootInitSessionVar(string $php_sapi, bool $is_stateless, int $use_cookies): void
    {
        global $CFG_ZENTRA;

        // Prepare
        $_SESSION = []; // remove all sessions variables
        $custom_font_value = 'whatever-string-value';
        $CFG_ZENTRA['pdffont'] = $custom_font_value;

        $session_manager = $this->createMock(SessionManager::class);
        $session_manager->method('isResourceStateless')->willReturn($is_stateless);

        $instance = new SessionStart($session_manager, ZENTRA_ROOT, php_sapi: $php_sapi);

        // Act
        $instance->onPostBoot();

        // Assert
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $_SESSION['zentra_currenttime']);
        $this->assertEquals(Session::NORMAL_MODE, $_SESSION['zentra_use_mode']);
        $this->assertEquals([], $_SESSION['MESSAGE_AFTER_REDIRECT']);

        foreach ($CFG_ZENTRA['user_pref_field'] as $key) {
            $this->assertArrayHasKey('zentra' . $key, $_SESSION);
        }
        $this->assertEquals($custom_font_value, $_SESSION['zentrapdffont']);

        $this->assertEquals($use_cookies, ini_get('session.use_cookies'));
    }
}
