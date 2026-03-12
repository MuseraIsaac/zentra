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

namespace Zentra\Api\HL\Controller;

use Zentra\Api\HL\Doc as Doc;
use Zentra\Api\HL\GraphQL;
use Zentra\Api\HL\GraphQLGenerator;
use Zentra\Api\HL\Route;
use Zentra\Api\HL\RouteVersion;
use Zentra\Http\JSONResponse;
use Zentra\Http\Request;
use Zentra\Http\Response;

#[Route(path: '/GraphQL', priority: 1, tags: ['GraphQL'])]
final class GraphQLController extends AbstractController
{
    #[Route(path: '/', methods: ['POST'], security_level: Route::SECURITY_AUTHENTICATED, scopes: ['graphql'])]
    #[RouteVersion(introduced: '2.0')]
    #[Doc\Route(description: 'GraphQL API')]
    public function index(Request $request): Response
    {
        return new JSONResponse(GraphQL::processRequest($request));
    }

    #[Route(path: '/Schema', methods: ['GET'], security_level: Route::SECURITY_AUTHENTICATED, scopes: ['graphql'])]
    #[RouteVersion(introduced: '2.0')]
    #[Doc\Route(description: 'GraphQL API Schema')]
    public function getSchema(Request $request): Response
    {
        $graphql_generator = new GraphQLGenerator($this->getAPIVersion($request));
        return new Response(200, [], $graphql_generator->getSchema());
    }
}
