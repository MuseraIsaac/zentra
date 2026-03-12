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

use Zentra\Form\Condition\ValueOperator;
use Zentra\Form\QuestionType\AbstractQuestionTypeActors;
use Zentra\Form\QuestionType\QuestionTypeActorsExtraDataConfig;
use Zentra\Form\QuestionType\QuestionTypeAssignee;
use Zentra\Form\QuestionType\QuestionTypeObserver;
use Zentra\Form\QuestionType\QuestionTypeRequester;
use Zentra\Tests\AbstractConditionHandlerTest;

final class ActorConditionHandlerTest extends AbstractConditionHandlerTest
{
    public static function getConditionHandler(): ConditionHandlerInterface
    {
        return new ActorConditionHandler(
            new QuestionTypeRequester(),
            new QuestionTypeActorsExtraDataConfig()
        );
    }

    public static function conditionHandlerProvider(): iterable
    {
        /** @var class-string<AbstractQuestionTypeActors>[] $types */
        $types = [
            QuestionTypeRequester::class,
            QuestionTypeObserver::class,
            QuestionTypeAssignee::class,
        ];
        $extra_data = new QuestionTypeActorsExtraDataConfig(
            is_multiple_actors: true
        );

        foreach ($types as $type) {
            $allowed_actor_types = (new $type())->getAllowedActorTypes();
            foreach ($allowed_actor_types as $actor_type) {
                // Test actor answers with the EQUALS operator
                yield "Equals check - case 1 for $type with $actor_type (same actors)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Equals check - case 2 for $type with $actor_type (different order)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Equals check - case 3 for $type with $actor_type (missing actor)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Equals check - case 4 for $type with $actor_type (extra actor)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Equals check - case 5 for $type with $actor_type (completely different)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-4', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];

                // Test actor answers with the NOT_EQUALS operator
                yield "Not equals check - case 1 for $type with $actor_type (same actors)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not equals check - case 2 for $type with $actor_type (different order)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not equals check - case 3 for $type with $actor_type (missing actor)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not equals check - case 4 for $type with $actor_type (extra actor)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not equals check - case 5 for $type with $actor_type (completely different)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_EQUALS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-4', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];

                // Test actor answers with the CONTAINS operator
                yield "Contains check - case 1 for $type with $actor_type (same actors)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Contains check - case 2 for $type with $actor_type (partial match)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Contains check - case 3 for $type with $actor_type (extra actors)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Contains check - case 4 for $type with $actor_type (no match)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-4', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Contains check - case 5 for $type with $actor_type (empty submission)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];

                // Test actor answers with the NOT_CONTAINS operator
                yield "Not contains check - case 1 for $type with $actor_type (same actors)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not contains check - case 2 for $type with $actor_type (partial match)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not contains check - case 3 for $type with $actor_type (extra actors)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => false,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not contains check - case 4 for $type with $actor_type (no match)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [
                        sprintf('%s-3', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-4', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
                yield "Not contains check - case 5 for $type with $actor_type (empty submission)" => [
                    'question_type'       => $type,
                    'condition_operator'  => ValueOperator::NOT_CONTAINS,
                    'condition_value'     => [
                        sprintf('%s-1', getForeignKeyFieldForItemType($actor_type)),
                        sprintf('%s-2', getForeignKeyFieldForItemType($actor_type)),
                    ],
                    'submitted_answer'    => [],
                    'expected_result'     => true,
                    'question_extra_data' => $extra_data,
                ];
            }
        }
    }
}
