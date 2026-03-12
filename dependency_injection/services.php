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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zentra\DependencyInjection\PublicService;
use Zentra\Error\ErrorHandler;

return static function (ContainerConfigurator $container): void {
    $projectDir = dirname(__DIR__);
    $parameters = $container->parameters();
    $services = $container->services();

    // Default secret, just in case
    $parameters->set('zentra.default_secret', bin2hex(random_bytes(32)));
    $parameters->set('env(APP_SECRET_FILE)', $projectDir . '/config/zentracrypt.key');
    $parameters->set('kernel.secret', env('default:zentra.default_secret:file:APP_SECRET_FILE'));

    // Prevent low level errors (e.g. warning) to be converted to exception in dev environment
    $parameters->set('debug.error_handler.throw_at', ErrorHandler::FATAL_ERRORS);

    $services = $services
        ->defaults()
            ->autowire()
            ->autoconfigure()
        ->instanceof(PublicService::class)->public()
    ;

    $services->load('Zentra\Controller\\', $projectDir . '/src/Zentra/Controller');
    $services->load('Zentra\Http\\', $projectDir . '/src/Zentra/Http');
    $services->load('Zentra\Kernel\\Listener\\', $projectDir . '/src/Zentra/Kernel/Listener');
    $services->load('Zentra\DependencyInjection\\', $projectDir . '/src/Zentra/DependencyInjection');
    $services->load('Zentra\Progress\\', $projectDir . '/src/Zentra/Progress')
        ->exclude([
            $projectDir . '/src/Zentra/Progress/ConsoleProgressIndicator.php',
            $projectDir . '/src/Zentra/Progress/StoredProgressIndicator.php',
        ]);
    $services->load(
        'Zentra\Form\Condition\\',
        $projectDir . '/src/Zentra/Form/Condition/*Manager.php'
    );
    $services->load(
        'Zentra\UI\\',
        $projectDir . '/src/Zentra/UI/*Manager.php'
    );

    // Prevent Symfony to register its own default logger.
    // @see \Symfony\Component\HttpKernel\DependencyInjection\LoggerPass
    $services->set('logger')->synthetic();
};
