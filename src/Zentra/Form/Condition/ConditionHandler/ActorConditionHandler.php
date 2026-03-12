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

namespace Zentra\Form\Condition\ConditionHandler;

use Zentra\Form\Condition\ConditionData;
use Zentra\Form\Condition\ValueOperator;
use Zentra\Form\QuestionType\AbstractQuestionTypeActors;
use Zentra\Form\QuestionType\QuestionTypeActorsExtraDataConfig;
use Override;

class ActorConditionHandler implements ConditionHandlerInterface
{
    use ArrayConditionHandlerTrait;

    public function __construct(
        private AbstractQuestionTypeActors $question_type,
        private QuestionTypeActorsExtraDataConfig $extra_data_config,
    ) {}

    #[Override]
    public function getSupportedValueOperators(): array
    {
        return $this->getSupportedArrayValueOperators();
    }

    #[Override]
    public function getTemplate(): string
    {
        return '/pages/admin/form/condition_handler_templates/actor.html.twig';
    }

    #[Override]
    public function getTemplateParameters(ConditionData $condition): array
    {
        return [
            'multiple'       => $this->extra_data_config->isMultipleActors(),
            'allowed_actors' => $this->question_type->getAllowedActorTypes(),
        ];
    }

    #[Override]
    public function applyValueOperator(
        mixed $a,
        ValueOperator $operator,
        mixed $b,
    ): bool {
        return $this->applyArrayValueOperator($a, $operator, $b);
    }
}
