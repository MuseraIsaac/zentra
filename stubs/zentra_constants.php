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

// This file contains stubs for ZENTRA constants.
// Please try to keep them alphabetically ordered.
// Keep in sync with the dynamicConstantNames config option in the PHPStan config file

// Wrap in a function to be sure to never declare any variable in the global scope.
(static function () {
    $random_val = static fn(array $values) => $values[array_rand($values)];

    // Directories constants
    define('ZENTRA_CACHE_DIR', dirname(__FILE__, 2) . '/files/_cache');
    define('ZENTRA_CONFIG_DIR', dirname(__FILE__, 2) . '/config');
    define('ZENTRA_CRON_DIR', dirname(__FILE__, 2) . '/files/_cron');
    define('ZENTRA_DOC_DIR', dirname(__FILE__, 2) . '/files');
    define('ZENTRA_GRAPH_DIR', dirname(__FILE__, 2) . '/files/_graphs');
    define('ZENTRA_INVENTORY_DIR', dirname(__FILE__, 2) . '/files/_inventories');
    define('ZENTRA_LOCAL_I18N_DIR', dirname(__FILE__, 2) . '/files/_locales');
    define('ZENTRA_LOCK_DIR', dirname(__FILE__, 2) . '/files/_lock');
    define('ZENTRA_LOG_DIR', dirname(__FILE__, 2) . '/files/_log');
    define('ZENTRA_MARKETPLACE_DIR', dirname(__FILE__, 2) . '/marketplace');
    define('ZENTRA_PICTURE_DIR', dirname(__FILE__, 2) . '/files/_pictures');
    define('ZENTRA_PLUGIN_DOC_DIR', dirname(__FILE__, 2) . '/files/_plugins');
    define('ZENTRA_RSS_DIR', dirname(__FILE__, 2) . '/files/_rss');
    define('ZENTRA_SESSION_DIR', dirname(__FILE__, 2) . '/files/_sessions');
    define('ZENTRA_THEMES_DIR', dirname(__FILE__, 2) . '/files/_themes');
    define('ZENTRA_TMP_DIR', dirname(__FILE__, 2) . '/files/_tmp');
    define('ZENTRA_UPLOAD_DIR', dirname(__FILE__, 2) . '/files/_uploads');
    define('ZENTRA_VAR_DIR', dirname(__FILE__, 2) . '/files');

    // Optionnal constants
    if ($random_val([false, true]) === true) {
        define('ZENTRA_FORCE_MAIL', 'example@zentra-project.org');
    }

    // Other constants
    define('ZENTRA_AJAX_DASHBOARD', $random_val([false, true]));
    define('ZENTRA_ALLOW_IFRAME_IN_RICH_TEXT', $random_val([false, true]));
    define('ZENTRA_CALDAV_IMPORT_STATE', $random_val([0, 1, 2]));
    define('ZENTRA_CENTRAL_WARNINGS', $random_val([false, true]));
    define('ZENTRA_DOCUMENTATION_ROOT_URL', 'https://links.zentra-project.org');
    define('ZENTRA_DISABLE_ONLY_FULL_GROUP_BY_SQL_MODE', $random_val([false, true]));
    define('ZENTRA_DISALLOWED_UPLOADS_PATTERN', $random_val(['', '/\.(php\d*|phar)$/i']));
    define('ZENTRA_ENVIRONMENT_TYPE', $random_val(['development', 'testing', 'staging', 'production']));
    define('ZENTRA_INSTALL_MODE', $random_val(['GIT', 'TARBALL']));
    define('ZENTRA_LOG_LVL', $random_val(['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug']));
    define('ZENTRA_MARKETPLACE_ALLOW_OVERRIDE', $random_val([false, true]));
    define('ZENTRA_MARKETPLACE_ENABLE', $random_val([0, 1, 2, 3]));
    define('ZENTRA_MARKETPLACE_MANUAL_DOWNLOADS', $random_val([false, true]));
    define('ZENTRA_MARKETPLACE_PLUGINS_API_URI', 'https://services.zentra-network.com/api/marketplace/');
    define('ZENTRA_MARKETPLACE_PRERELEASES', $random_val([false, true]));
    define('ZENTRA_NETWORK_API_URL', 'https://services.zentra-network.com/api');
    define('ZENTRA_NETWORK_REGISTRATION_API_URL', 'https://services.zentra-network.com/api/registration/');
    define('ZENTRA_NETWORK_MAIL', 'zentra@teclib.com');
    define('ZENTRA_NETWORK_SERVICES', 'https://services.zentra-network.com');
    define('ZENTRA_PLUGINS_DIRECTORIES', [dirname(__FILE__, 2) . '/plugins', dirname(__FILE__, 2) . '/marketplace']);
    define('ZENTRA_SERVERSIDE_URL_ALLOWLIST', $random_val([[], ['/^.*$/']]));
    define('ZENTRA_SKIP_UPDATES', $random_val([false, true]));
    define('ZENTRA_STRICT_ENV', $random_val([false, true]));
    define('ZENTRA_SYSTEM_CRON', $random_val([false, true]));
    define('ZENTRA_TELEMETRY_URI', 'https://telemetry.zentra-project.org');
    define('ZENTRA_TEXT_MAXSIZE', $random_val([1000, 2000, 3000, 4000]));
    define('ZENTRA_USER_AGENT_EXTRA_COMMENTS', $random_val(['', 'app-version:5']));
    define('ZENTRA_WEBHOOK_ALLOW_RESPONSE_SAVING', $random_val([false, true]));
    define('ZENTRA_WEBHOOK_CRA_MANDATORY', $random_val([false, true]));
})();
