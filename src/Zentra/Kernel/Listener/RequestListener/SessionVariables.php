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

namespace Zentra\Kernel\Listener\RequestListener;

use Zentra\Kernel\KernelListenerTrait;
use Zentra\Kernel\ListenersPriority;
use Zentra\Toolbox\URL;
use Session;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class SessionVariables implements EventSubscriberInterface
{
    use KernelListenerTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', ListenersPriority::REQUEST_LISTENERS_PRIORITIES[self::class]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        global $ZENTRA_CACHE;

        if (!$event->isMainRequest()) {
            // Do not change sessionv ariables on sub-requests.
            return;
        }

        if (!$this->isDatabaseUsable()) {
            // Do not try to load data from the database if it is not available.
            return;
        }

        $request = $event->getRequest();

        // Manage force tab
        if ($request->query->has('forcetab')) {
            $itemtype = URL::extractItemtypeFromUrlPath($request->getPathInfo());
            if ($itemtype !== null) {
                Session::setActiveTab($itemtype, $request->get('forcetab'));
            }
        }

        // Manage tabs
        if ($request->get('zentra_tab') && $request->get('itemtype')) {
            Session::setActiveTab($request->get('itemtype'), $request->get('zentra_tab'));
        }

        // Override list-limit if choosen
        if ($request->get('zentralist_limit')) {
            $_SESSION['zentralist_limit'] = $request->get('zentralist_limit');
        }

        // Manage forced profile / entity.
        // This feature permits to craft custom links from an external app/notification that forces a specific profile
        // and/or a specific entity to be loaded.
        // see #10074
        $forced_profile = $request->get('force_profile');
        $forced_entity  = $request->get('force_entity');
        $check_entities = true;
        if (
            $forced_profile !== null
            && ($_SESSION['zentraactiveprofile']['id'] ?? -1) != $forced_profile
            && isset($_SESSION['zentraprofiles'][$forced_profile])
        ) {
            Session::changeProfile($forced_profile);
        }
        if (
            $forced_entity !== null
            && ($_SESSION["zentraactive_entity"] ?? -1) != $forced_entity
        ) {
            Session::changeActiveEntities($forced_entity, true);
            $check_entities = false;
        }

        // Reload entities if necessary.
        if ($check_entities && Session::shouldReloadActiveEntities()) {
            Session::changeActiveEntities(
                $_SESSION["zentraactive_entity"],
                $_SESSION["zentraactive_entity_recursive"]
            );
        }

        // The user's current groups are stored in his session
        // If there was any change regarding groups membership and/or configuration, we
        // need to reset the data stored in his session
        if (
            isset($_SESSION['zentragroups'])
            && (
                !isset($_SESSION['zentragroups_cache_date'])
                || $_SESSION['zentragroups_cache_date'] < $ZENTRA_CACHE->get('last_group_change')
            )
        ) {
            Session::loadGroups();
        }
    }
}
