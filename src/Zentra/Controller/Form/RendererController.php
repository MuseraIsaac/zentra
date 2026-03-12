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

namespace Zentra\Controller\Form;

use Zentra\Controller\AbstractController;
use Zentra\Controller\Form\Utils\CanCheckAccessPolicies;
use Zentra\Exception\Http\BadRequestHttpException;
use Zentra\Exception\Http\NotFoundHttpException;
use Zentra\Form\Condition\Engine;
use Zentra\Form\Condition\EngineInput;
use Zentra\Form\Form;
use Zentra\Form\ServiceCatalog\ServiceCatalog;
use Zentra\Http\Firewall;
use Zentra\Security\Attribute\SecurityStrategy;
use Html;
use Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RendererController extends AbstractController
{
    use CanCheckAccessPolicies;

    private string $interface;

    public function __construct()
    {
        $this->interface = Session::getCurrentInterface();
    }

    #[SecurityStrategy(Firewall::STRATEGY_NO_CHECK)] // Some forms can be accessed anonymously
    #[Route(
        "/Form/Render/{id}",
        name: "zentra_form_render",
        methods: "GET",
        requirements: ['id' => '\d+'],
    )]
    public function __invoke(Request $request): Response
    {
        $is_unauthenticated_user = !Session::isAuthenticated();

        $form = $this->loadTargetForm($request);
        $this->checkFormAccessPolicies($form, $request);

        $my_tickets_criteria = [
            "criteria" => [
                [
                    "field" => 12, // Status
                    "searchtype" => "equals",
                    "value" => "notold", // Not solved
                ],
            ],
        ];
        if ($this->interface == 'central') {
            $my_tickets_criteria["criteria"][] = [
                "link" => "AND",
                "field" => 4, // Requester
                "searchtype" => "equals",
                "value" => 'myself',
            ];
        }

        // Compute the initial visibility of the form items
        $engine = new Engine($form, EngineInput::fromForm($form));
        $visibility_engine_output = $engine->computeVisibility();

        // Insert altcha for public forms
        if ($is_unauthenticated_user) {
            Html::requireJs('altcha');
        }

        return $this->render('pages/form_renderer.html.twig', [
            'title' => $form->fields['name'],
            'menu' => ['helpdesk', ServiceCatalog::getType()],
            'form' => $form,
            'unauthenticated_user' => $is_unauthenticated_user,
            'my_tickets_url_param' => http_build_query($my_tickets_criteria),
            'visibility_engine_output' => $visibility_engine_output,
            'params' => $request->query->all(),
        ]);
    }

    private function loadTargetForm(Request $request): Form
    {
        $forms_id = (int) $request->get("id");
        if (!$forms_id) {
            throw new BadRequestHttpException();
        }

        $form = Form::getById($forms_id);
        if (!$form instanceof Form) {
            throw new NotFoundHttpException();
        }

        return $form;
    }
}
