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

namespace Zentra\Controller\Form;

use Zentra\Controller\AbstractController;
use Zentra\Exception\Http\AccessDeniedHttpException;
use Zentra\Exception\Http\BadRequestHttpException;
use Zentra\Exception\Http\NotFoundHttpException;
use Zentra\Form\AccessControl\FormAccessControlManager;
use Zentra\Form\AccessControl\FormAccessParameters;
use Zentra\Form\Dropdown\FormActorsDropdown;
use Zentra\Form\Form;
use Zentra\Http\Firewall;
use Zentra\Security\Attribute\SecurityStrategy;
use Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuestionActorsDropdownController extends AbstractController
{
    #[Route(
        "/Form/Question/ActorsDropdown",
        name: "zentra_form_question_actors_dropdown_value",
        methods: "POST"
    )]
    #[SecurityStrategy(Firewall::STRATEGY_AUTHENTICATED)]
    public function __invoke(Request $request): Response
    {
        $this->checkFormAccessPolicies($request);

        $options = [
            'allowed_types'    => $request->request->all('allowed_types'),
            'right_for_users'  => $request->request->getString('right_for_users', 'all'),
            'group_conditions' => $request->request->all('group_conditions'),
            'page'             => $request->request->getInt('page', 1),
            'page_size'        => $request->request->getInt('page_limit', -1),
        ];

        return new JsonResponse(
            FormActorsDropdown::fetchValues(
                $request->request->getString('searchText'),
                $options
            )
        );
    }

    private function loadTargetForm(Request $request): Form
    {
        $forms_id = (int) $request->request->getInt('form_id');
        if (!$forms_id) {
            throw new BadRequestHttpException();
        }

        $form = Form::getById($forms_id);
        if (!$form instanceof Form) {
            throw new NotFoundHttpException();
        }

        return $form;
    }

    private function checkFormAccessPolicies(Request $request): void
    {
        $form_access_manager = FormAccessControlManager::getInstance();

        if (!Session::haveRight(Form::$rightname, READ)) {
            $form = $this->loadTargetForm($request);

            // Load current user session info and URL parameters.
            $parameters = new FormAccessParameters(
                session_info: Session::getCurrentSessionInfo(),
                url_parameters: $request->query->all(),
            );

            if (!$form_access_manager->canAnswerForm($form, $parameters)) {
                throw new AccessDeniedHttpException();
            }
        }
    }
}
