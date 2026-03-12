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

use Zentra\Controller\MaintenanceController;
use Zentra\Kernel\KernelListenerTrait;
use Zentra\Kernel\ListenersPriority;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class CheckMaintenanceListener implements EventSubscriberInterface
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
        if (!$event->isMainRequest()) {
            // Do not check DB maintenance mode on sub-requests.
            return;
        }

        if (
            $this->isFrontEndAssetEndpoint($event->getRequest())
            || $this->isSymfonyProfilerEndpoint($event->getRequest())
        ) {
            // These resources should always be available.
            return;
        }

        global $CFG_ZENTRA;

        // Check maintenance mode
        if (!isset($CFG_ZENTRA["maintenance_mode"]) || !$CFG_ZENTRA["maintenance_mode"]) {
            return;
        }

        if ($event->getRequest()->query->get('skipMaintenance')) {
            $_SESSION["zentraskipMaintenance"] = 1;
            return;
        }

        if (isset($_SESSION["zentraskipMaintenance"]) && $_SESSION["zentraskipMaintenance"]) {
            return;
        }

        // Setting the `_controller` attribute will force Symfony to consider that routing was resolved already.
        // @see `\Symfony\Component\HttpKernel\EventListener\RouterListener::onKernelRequest()`
        $event->getRequest()->attributes->set('_controller', MaintenanceController::class);
    }
}
