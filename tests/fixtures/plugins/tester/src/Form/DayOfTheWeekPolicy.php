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

namespace ZentraPlugin\Tester\Form;

use Zentra\Application\View\TemplateRenderer;
use Zentra\DBAL\JsonFieldInterface;
use Zentra\Form\AccessControl\AccessVote;
use Zentra\Form\AccessControl\ControlType\ControlTypeInterface;
use Zentra\Form\AccessControl\FormAccessControl;
use Zentra\Form\AccessControl\FormAccessParameters;
use Zentra\Form\Export\Context\DatabaseMapper;
use Zentra\Form\Export\Serializer\DynamicExportDataField;
use Zentra\Form\Form;
use InvalidArgumentException;
use Override;
use Session;

final class DayOfTheWeekPolicy implements ControlTypeInterface
{
    #[Override]
    public function getLabel(): string
    {
        return __("Restrict access to a specific day of the week");
    }

    #[Override]
    public function getIcon(): string
    {
        return "ti ti-calendar";
    }

    #[Override]
    public function getWeight(): int
    {
        return 30;
    }

    #[Override]
    public function getConfig(): JsonFieldInterface
    {
        return new DayOfTheWeekPolicyConfig();
    }

    #[Override]
    public function createConfigFromUserInput(array $input): JsonFieldInterface
    {
        return DayOfTheWeekPolicyConfig::jsonDeserialize([
            'day_of_the_week' => $input['_day_of_the_week'],
        ]);
    }

    #[Override]
    public function allowUnauthenticated(JsonFieldInterface $config): bool
    {
        return false;
    }

    #[Override]
    public function canAnswer(
        Form $form,
        JsonFieldInterface $config,
        FormAccessParameters $parameters
    ): AccessVote {
        if (!($config instanceof DayOfTheWeekPolicyConfig)) {
            throw new InvalidArgumentException();
        }

        $date = Session::getCurrentDate();
        $day = date('l', strtotime($date));

        return $day === $config->getDay() ? AccessVote::Abstain : AccessVote::Deny;
    }

    #[Override]
    public function getWarnings(Form $form): array
    {
        return [];
    }

    #[Override]
    public function renderConfigForm(FormAccessControl $access_control): string
    {
        $config = $access_control->getConfig();
        if (!$config instanceof DayOfTheWeekPolicyConfig) {
            throw new InvalidArgumentException();
        }

        $twig = TemplateRenderer::getInstance();
        return $twig->render("@tester/day_of_the_week_policy.html.twig", [
            'input_name' => $access_control->getNormalizedInputName('_day_of_the_week'),
            'value'      => $config->getDay(),
            'label'      => __("Day"),
        ]);
    }

    #[Override]
    public function exportDynamicConfig(
        JsonFieldInterface $config
    ): DynamicExportDataField {
        return new DynamicExportDataField($config, []);
    }

    #[Override]
    public static function prepareDynamicConfigDataForImport(
        array $config,
        DatabaseMapper $mapper,
    ): array {
        return $config;
    }
}
