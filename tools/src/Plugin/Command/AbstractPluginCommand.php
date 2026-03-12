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

namespace Zentra\Tools\Plugin\Command;

use Zentra\Tools\Command\AbstractCommand;
use Override;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command extending this class don't have a native way to interact with the ZENTRA core.
 * If you need to interact with it or its database, use the `Zentra\Console\AbstractCommand` instead.
 */
abstract class AbstractPluginCommand extends AbstractCommand
{
    #[Override]
    protected function isRequiringPluginOption(): bool
    {
        return true;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $plugin_name = $this->input->getOption('plugin');
        if (!$plugin_name) {
            throw new InvalidOptionException('The "--plugin" option is required.');
        }

        $root_dir = dirname(__DIR__, 4);
        $plugin_dir = $root_dir . '/plugins/' . $plugin_name;

        if (!is_dir($plugin_dir)) {
            throw new RuntimeException(
                sprintf('Plugin directory "%s" not found.', $plugin_dir)
            );
        }
    }
}
