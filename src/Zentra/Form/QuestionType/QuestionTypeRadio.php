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

namespace Zentra\Form\QuestionType;

use Zentra\DBAL\JsonFieldInterface;
use Zentra\Form\Condition\ConditionHandler\SingleChoiceFromValuesConditionHandler;
use Zentra\Form\Condition\UsedAsCriteriaInterface;
use Zentra\Form\Question;
use InvalidArgumentException;
use Override;

final class QuestionTypeRadio extends AbstractQuestionTypeSelectable implements UsedAsCriteriaInterface
{
    #[Override]
    public function getInputType(?Question $question): string
    {
        return 'radio';
    }

    #[Override]
    public function getCategory(): QuestionTypeCategoryInterface
    {
        return QuestionTypeCategory::RADIO;
    }

    #[Override]
    protected function getExtraInputAttributes(): array
    {
        return ['data-zentra-form-radio-uncheckable' => ''];
    }

    #[Override]
    public function getConditionHandlers(
        ?JsonFieldInterface $question_config
    ): array {
        if (!$question_config instanceof QuestionTypeSelectableExtraDataConfig) {
            throw new InvalidArgumentException();
        }

        return array_merge(
            parent::getConditionHandlers($question_config),
            [new SingleChoiceFromValuesConditionHandler($question_config->getOptions())]
        );
    }
}
