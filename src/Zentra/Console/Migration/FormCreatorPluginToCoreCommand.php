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

namespace Zentra\Console\Migration;

use Zentra\Form\AccessControl\FormAccessControlManager;
use Zentra\Form\Migration\FormMigration;
use Zentra\Migration\AbstractPluginMigration;
use Override;
use Symfony\Component\Console\Input\InputOption;

class FormCreatorPluginToCoreCommand extends AbstractPluginMigrationCommand
{
    #[Override]
    public function getName(): string
    {
        return 'migration:formcreator_plugin_to_core';
    }

    #[Override]
    public function getDescription(): string
    {
        return sprintf(__('Migrate %s plugin data into ZENTRA core tables'), 'Formcreator');
    }

    #[Override]
    public function getMigration(): AbstractPluginMigration
    {
        return new FormMigration(
            $this->db,
            FormAccessControlManager::getInstance(),
            $this->input->getOption('form-id')
        );
    }

    #[Override]
    protected function configure()
    {
        parent::configure();

        $this->addOption(
            'form-id',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            __('Import only specific forms with the given IDs'),
            []
        );
    }
}
