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

/**
 * @var DBmysql $DB
 */

//Fix possible duplicates, see https://github.com/zentra-project/zentra/issues/22235
$DB->doQuery(
    "
  DELETE FROM zentra_printerlogs WHERE id IN (
    SELECT id FROM (
      SELECT a.id FROM zentra_printerlogs AS a
      JOIN(
          SELECT items_id, date
          FROM zentra_printerlogs
          GROUP BY items_id, date
          HAVING COUNT(items_id) > 1
      ) AS b
      WHERE a.items_id = b.items_id AND a.date = b.date AND a.itemtype = ''
    ) AS temp
  )"
);

//add missing itemtype
$DB->update('zentra_printerlogs', ['itemtype' => 'Printer'], ['itemtype' => '']);
