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

namespace Zentra\Controller\Form\Destination;

use Zentra\Controller\AbstractController;
use Zentra\Exception\Http\AccessDeniedHttpException;
use Zentra\Exception\Http\BadRequestHttpException;
use Zentra\Form\Destination\FormDestination;
use Zentra\Form\Form;
use Zentra\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AddDestinationController extends AbstractController
{
    #[Route("/Form/{form_id}/Destination/Add", name: "zentra_form_destination_add", methods: "POST")]
    public function __invoke(Request $request, int $form_id): Response
    {
        $destination = new FormDestination();
        $input = array_merge($request->request->all(), [Form::getForeignKeyField() => $form_id]);

        // Right check
        if (!$destination->can(-1, CREATE, $input)) {
            throw new AccessDeniedHttpException();
        }

        // If we are duplicating an item, load the full data.
        if (isset($input['id'])) {
            $original = FormDestination::getById($input['id']);
            $input = $original->fields;
            unset($input['id']); // Id must be removed to prevent a clone from triggering.
        }

        $input['_do_not_clone'] = true;

        // Create destination item
        $id = $destination->add($input);
        if (!$id) {
            throw new BadRequestHttpException();
        }

        // Save the ID to reopen the correct accordion item
        $_SESSION['active_destination'] = $id;

        return new RedirectResponse(Form::getFormURLWithID($form_id));
    }
}
