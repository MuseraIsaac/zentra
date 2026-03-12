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

namespace Zentra\Helpdesk\Tile;

use CommonDBChild;
use Zentra\Form\AccessControl\FormAccessControlManager;
use Zentra\Form\AccessControl\FormAccessParameters;
use Zentra\Form\Form;
use Zentra\Session\SessionInfo;
use Html;
use Override;
use Ticket;

final class FormTile extends CommonDBChild implements TileInterface
{
    public static $rightname = 'config';
    public static $itemtype = Form::class;
    public static $items_id = 'forms_forms_id';

    private Form $form;

    #[Override]
    public function getWeight(): int
    {
        return 10;
    }

    #[Override]
    public function getLabel(): string
    {
        return Form::getTypeName(1);
    }

    #[Override]
    public static function canCreate(): bool
    {
        return self::canUpdate();
    }

    #[Override]
    public static function canPurge(): bool
    {
        return self::canUpdate();
    }

    #[Override]
    public function post_getFromDB(): void
    {
        $form = $this->getItem();
        if (!($form instanceof Form)) {
            throw new InvalidTileException("Unable to load linked form");
        } else {
            $this->form = $form;
        }
    }

    #[Override]
    public function getTitle(): string
    {
        return $this->form->getServiceCatalogItemTitle();
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->form->getServiceCatalogItemDescription();
    }

    #[Override]
    public function getIllustration(): string
    {
        return $this->form->getServiceCatalogItemIllustration();
    }

    #[Override]
    public function getTileUrl(): string
    {
        return Html::getPrefixedUrl('/Form/Render/' . $this->form->getID());
    }

    #[Override]
    public function isAvailable(SessionInfo $session_info): bool
    {
        $form_access_manager = FormAccessControlManager::getInstance();

        if (!$session_info->hasRight(Ticket::$rightname, CREATE)) {
            return false;
        }

        // Form must be active
        if (!$this->form->isActive()) {
            return false;
        }

        // Form must not be deleted
        if ($this->form->isDeleted()) {
            return false;
        }

        // Check that the form entity is visible
        if (!$this->form->isAccessibleFromEntities($session_info->getActiveEntitiesIds())) {
            return false;
        }

        // Check if the user can answer the form
        $form_access_params = new FormAccessParameters(session_info: $session_info);
        if (!$form_access_manager->canAnswerForm($this->form, $form_access_params)) {
            return false;
        }

        return true;
    }

    #[Override]
    public function getDatabaseId(): int
    {
        return $this->fields['id'];
    }

    #[Override]
    public function getConfigFieldsTemplate(): string
    {
        return "pages/admin/form_tile_config_fields.html.twig";
    }

    #[Override]
    public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                Item_Tile::class,
            ]
        );
    }

    public function getFormId(): int
    {
        return $this->fields['forms_forms_id'] ?? 0;
    }
}
