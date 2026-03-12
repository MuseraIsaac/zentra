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

final class UpdateDestinationController extends AbstractController
{
    #[Route("/Form/{form_id}/Destination/{destination_id}/Update", name: "zentra_form_destination_update", methods: "POST")]
    public function __invoke(Request $request, int $form_id, int $destination_id): Response
    {
        $destination = new FormDestination();
        $input = array_merge(
            $request->request->all(),
            [
                'id'                       => $destination_id,
                Form::getForeignKeyField() => $form_id,
            ]
        );

        // Right check
        if (!$destination->can($destination_id, UPDATE, $input)) {
            throw new AccessDeniedHttpException();
        }

        // Update destination item
        if (!$destination->update($input)) {
            throw new BadRequestHttpException('Failed to update destination item');
        }

        // Save the ID to reopen the correct accordion item
        $_SESSION['active_destination'] = $destination_id;

        return new RedirectResponse(Form::getFormURLWithID($form_id));
    }
}
