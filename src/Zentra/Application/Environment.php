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

namespace Zentra\Application;

use Psr\Log\LogLevel;
use UnexpectedValueException;

use function Safe\define;

enum Environment: string
{
    /**
     * Production environment.
     */
    case PRODUCTION = 'production';

    /**
     * Staging environment.
     * Suitable for pre-production servers and customer acceptance tests.
     */
    case STAGING = 'staging';

    /**
     * Testing environment.
     * Suitable for CI runners, quality control and internal acceptance tests.
     */
    case TESTING = 'testing';

    /**
     * E2E testing environment.
     * Suitable for CI runners and local test execution.
     */
    case E2E = 'e2e_testing';

    /**
     * Development environment.
     * Suitable for developer machines and development servers.
     */
    case DEVELOPMENT = 'development';

    public static function isSet(): bool
    {
        return defined('ZENTRA_ENVIRONMENT_TYPE');
    }

    /**
     * @return array
     */
    public static function getValues()
    {
        $values = [];
        foreach (self::cases() as $env) {
            $values[] = $env->value;
        }
        return $values;
    }

    public static function get(): self
    {
        // Read ZENTRA_ENVIRONMENT_TYPE if it exist
        if (defined('ZENTRA_ENVIRONMENT_TYPE')) {
            $value = ZENTRA_ENVIRONMENT_TYPE;
        } else {
            // In some rare case, the kernel may not be booted yet and thus we must
            // rely on global vars to find the env value.
            // If no value is given, we fallback to the production env.
            $value = $_ENV['ZENTRA_ENVIRONMENT_TYPE']
                ?? $_SERVER['ZENTRA_ENVIRONMENT_TYPE']
                ?? self::PRODUCTION->value
            ;
        }

        // Avoid a crash if an unexpected value is supplied.
        if (!is_string($value)) {
            $value = "";
        }

        // Try to load the given env, with a fallback to production.
        return self::tryFrom($value) ?? self::PRODUCTION->value;
    }

    public static function set(self $environment): void
    {
        define('ZENTRA_ENVIRONMENT_TYPE', $environment->value);
    }

    public static function validate(): void
    {
        // Store valid environments keys
        $allowed_keys = self::getValues();

        // Validate ZENTRA_ENVIRONMENT_TYPE if it exists.
        if (!in_array(ZENTRA_ENVIRONMENT_TYPE, $allowed_keys)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Invalid ZENTRA_ENVIRONMENT_TYPE constant value `%s`. Allowed values are: `%s`',
                    ZENTRA_ENVIRONMENT_TYPE,
                    implode('`, `', $allowed_keys)
                )
            );
        }
    }

    /**
     * See SystemConfigurator::computeConstants() for all available values that
     * can be overridden.
     */
    public function getConstantsOverride(string $root_dir): array
    {
        $test_token = getenv('TEST_TOKEN');

        return match ($this) {
            default => [],
            self::TESTING     => [
                'ZENTRA_CONFIG_DIR'               => $root_dir . '/tests/config',
                'ZENTRA_VAR_DIR'                  => $root_dir . '/tests/files' . (($test_token !== false && $test_token !== '' && $test_token > 1) ? "-$test_token" : ''),
                'ZENTRA_LOG_LVL'                  => LogLevel::DEBUG,
                'ZENTRA_STRICT_ENV'               => true,
                'ZENTRA_SERVERSIDE_URL_ALLOWLIST' => [
                    // Based on https://github.com/symfony/symfony/blob/7.3/src/Symfony/Component/Validator/Constraints/UrlValidator.php
                    '~^
                        (http|https|feed)://                                                # protocol
                        (
                            (?:
                                (?:xn--[a-z0-9-]++\.)*+xn--[a-z0-9-]++                      # a domain name using punycode
                                    |
                                (?:[\pL\pN\pS\pM\-\_]++\.)+[\pL\pN\pM]++                    # a multi-level domain name
                                    |
                                [a-z0-9\-\_]++                                              # a single-level domain name
                            )\.?
                                |                                                           # or
                            \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                              # an IP address
                                |                                                           # or
                            \[
                                (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                            \]                                                              # an IPv6 address
                        )
                        (?:/ (?:[\pL\pN\pS\pM\-._\~!$&\'()*+,;=:@]|%[0-9A-Fa-f]{2})* )*     # a path
                        (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%[0-9A-Fa-f]{2})* )?    # a query (optional)
                    $~ixuD',

                    // calendar mockups
                    '/^file:\/\/.*\.ics$/',
                ],
                'ZENTRA_MARKETPLACE_DIR'          => $root_dir . '/tests/fixtures/marketplace',
                'ZENTRA_PLUGINS_DIRECTORIES'      => [
                    $root_dir . '/plugins',
                    '{ZENTRA_MARKETPLACE_DIR}',
                    $root_dir . '/tests/fixtures/plugins',
                ],
            ],
            self::DEVELOPMENT => [
                'ZENTRA_LOG_LVL'                       => LogLevel::DEBUG,
                'ZENTRA_STRICT_ENV'                    => true,
                'ZENTRA_WEBHOOK_ALLOW_RESPONSE_SAVING' => '1',
            ],
            self::E2E => [
                'ZENTRA_CONFIG_DIR'          => $root_dir . '/tests/e2e/zentra_config',
                'ZENTRA_VAR_DIR'             => $root_dir . '/tests/e2e/zentra_files',
                'ZENTRA_LOG_LVL'             => LogLevel::DEBUG,
                'ZENTRA_STRICT_ENV'          => true,
                'ZENTRA_PLUGINS_DIRECTORIES' => [
                    $root_dir . '/tests/fixtures/plugins',
                ],
            ],
        };
    }

    /**
     * Will the files of this environment change ?
     * This may affect which cache we decide to set (twig, http cache on the
     * generated css and locale, ...)
     */
    public function shouldExpectResourcesToChange(string $root_dir = ZENTRA_ROOT): bool
    {
        // Only production/staging environment are considered as environments
        // where resources are not supposed to change.
        // In other environments, we must watch for changes.
        if (
            $this === self::TESTING
            || $this === self::DEVELOPMENT
            || $this === self::E2E
        ) {
            return true;
        }

        // If ZENTRA is install direcly by cloning the git repository, then it is preferable to check
        // resources state.
        if (is_dir($root_dir . '/.git')) {
            return true;
        }

        return false;
    }

    /**
     * Should the HTTP response contains extra headers to force the caching on the browser side ?
     */
    public function shouldForceExtraBrowserCache(): bool
    {
        // Prevent intensive caching on dev env.
        return match ($this) {
            default           => true,
            self::DEVELOPMENT => false,
        };
    }

    public function shouldSetupTesterPlugin(): bool
    {
        // Specific for tests, should never be enabled anywhere else.
        return match ($this) {
            default           => false,
            self::TESTING     => true,
        };
    }

    public function shouldEnableExtraDevAndDebugTools(): bool
    {
        // Specific for dev, should never be enabled anywhere else.
        return match ($this) {
            default           => false,
            self::DEVELOPMENT => true,
        };
    }

    public function shouldAddExtraE2EDataDuringInstallation(): bool
    {
        return
            $this->shouldAddExtraCypressDataDuringInstallation()
            || $this->shouldAddExtraPlaywrightDataDuringInstallation()
        ;
    }

    public function shouldAddExtraCypressDataDuringInstallation(): bool
    {
        // Note: this will be removed when we switch to playwright.
        return match ($this) {
            default       => false,
            self::TESTING => true,
        };
    }

    public function shouldAddExtraPlaywrightDataDuringInstallation(): bool
    {
        // Note: this is a temporary method, it should be replaced by a proper
        // seeder system.
        return match ($this) {
            default   => false,
            self::E2E => true,
        };
    }
}
