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

namespace Zentra\Form\Destination;

use Change;
use CommonITILObject;
use Zentra\Form\Destination\CommonITILField\BackupPlanField;
use Zentra\Form\Destination\CommonITILField\CheckListField;
use Zentra\Form\Destination\CommonITILField\ControlsListField;
use Zentra\Form\Destination\CommonITILField\DeploymentPlanField;
use Zentra\Form\Destination\CommonITILField\ImpactsField;
use Override;

final class FormDestinationChange extends AbstractCommonITILFormDestination
{
    #[Override]
    public function getTarget(): CommonITILObject
    {
        return new Change();
    }

    #[Override]
    public function getWeight(): int
    {
        return 20;
    }

    #[Override]
    protected function defineConfigurableFields(): array
    {
        return array_merge(parent::defineConfigurableFields(), [
            new ImpactsField(),
            new ControlsListField(),
            new DeploymentPlanField(),
            new BackupPlanField(),
            new CheckListField(),
        ]);
    }
}
