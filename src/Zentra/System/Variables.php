<?php

/**
 * ---------------------------------------------------------------------
 *
 * ZENTRA - Gestionnaire Libre de Parc Informatique
 *
 * http://zentra-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
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

namespace Zentra\System;

/**
 * @since 9.5.4
 */
class Variables
{
    /**
     * Returns list of constants corresponding to directories that contains custom data.
     *
     * @return string[]
     */
    public static function getDataDirectoriesConstants(): array
    {
        return [
            'ZENTRA_CACHE_DIR',
            'ZENTRA_CRON_DIR',
            'ZENTRA_DOC_DIR',
            'ZENTRA_GRAPH_DIR',
            'ZENTRA_LOCK_DIR',
            'ZENTRA_LOG_DIR',
            'ZENTRA_PICTURE_DIR',
            'ZENTRA_PLUGIN_DOC_DIR',
            'ZENTRA_RSS_DIR',
            'ZENTRA_SESSION_DIR',
            'ZENTRA_TMP_DIR',
            'ZENTRA_UPLOAD_DIR',
        ];
    }

    /**
     * Returns list of directories that contains custom data.
     *
     * @return string[]
     */
    public static function getDataDirectories()
    {
        return array_map(
            fn(string $constant) => constant($constant),
            self::getDataDirectoriesConstants()
        );
    }
}
