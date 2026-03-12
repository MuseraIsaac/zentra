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

namespace tests\units\Zentra\Form;

use Computer;
use Zentra\Form\Condition\LogicOperator;
use Zentra\Form\Condition\Type;
use Zentra\Form\Condition\ValidationStrategy;
use Zentra\Form\Condition\ValueOperator;
use Zentra\Form\Condition\VisibilityStrategy;
use Zentra\Form\Question;
use Zentra\Form\QuestionType\AbstractQuestionTypeShortAnswer;
use Zentra\Form\QuestionType\QuestionTypeEmail;
use Zentra\Form\QuestionType\QuestionTypeLongText;
use Zentra\Form\QuestionType\QuestionTypeNumber;
use Zentra\Form\QuestionType\QuestionTypeShortText;
use Zentra\Tests\DbTestCase;
use Zentra\Tests\FormBuilder;
use Zentra\Tests\FormTesterTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class QuestionTest extends DbTestCase
{
    use FormTesterTrait;

    public static function getQuestionTypeProvider(): iterable
    {
        // First set of tests: valid values
        $question = new Question();
        $question->fields = [
            'type' => QuestionTypeShortText::class,
        ];
        yield [$question, new QuestionTypeShortText()];

        $question = new Question();
        $question->fields = [
            'type' => QuestionTypeNumber::class,
        ];
        yield [$question, new QuestionTypeNumber()];

        $question = new Question();
        $question->fields = [
            'type' => QuestionTypeEmail::class,
        ];
        yield [$question, new QuestionTypeEmail()];

        $question = new Question();
        $question->fields = [
            'type' => QuestionTypeLongText::class,
        ];
        yield [$question, new QuestionTypeLongText()];

        // Second set: Invalid values
        $question = new Question();
        $question->fields = [
            'type' => "not a type",
        ];
        yield [$question, null];

        $question = new Question();
        $question->fields = [
            'type' => Computer::class,
        ];
        yield [$question, null];

        $question = new Question();
        $question->fields = [
            'type' => AbstractQuestionTypeShortAnswer::class,
        ];
        yield [$question, null];
    }

    public static function setDefaultValueFromParametersProvider(): iterable
    {
        yield 'Standard UUID without dots applies value' => [
            'uuid'           => 'abcd1234-ef56-7890-ab12-cd3456789012',
            'get'            => ['abcd1234-ef56-7890-ab12-cd3456789012' => 'my value'],
            'expected_value' => 'my value',
        ];

        yield 'UUID with dot: PHP replaces dots with underscores in GET params' => [
            'uuid'           => 'abcd.1234',
            'get'            => ['abcd_1234' => 'my value'],
            'expected_value' => 'my value',
        ];

        yield 'UUID not present in GET params does not change default value' => [
            'uuid'           => 'abcd1234',
            'get'            => ['other_param' => 'my value'],
            'expected_value' => 'initial value',
        ];
    }

    #[DataProvider('setDefaultValueFromParametersProvider')]
    public function testSetDefaultValueFromParameters(
        string $uuid,
        array $get,
        string $expected_value,
    ): void {
        $question = new Question();
        $question->fields = [
            'uuid'          => $uuid,
            'type'          => QuestionTypeShortText::class,
            'default_value' => 'initial value',
        ];

        $question->setDefaultValueFromParameters($get);

        $this->assertEquals($expected_value, $question->fields['default_value']);
    }

    #[DataProvider('getQuestionTypeProvider')]
    public function testGetQuestionType(Question $question, $expected): void
    {
        $type = $question->getQuestionType();
        $this->assertEquals($expected, $type);
    }

    public function testVisibilityConditionsDataAreCleanedWhenStrategyIsReset(): void
    {
        // Arrange: create a form with visibility conditions on a question
        $builder = new FormBuilder();
        $builder->addQuestion("My question", QuestionTypeShortText::class);
        $builder->addQuestion("My other question", QuestionTypeShortText::class);
        $builder->setQuestionVisibility(
            "My other question",
            VisibilityStrategy::VISIBLE_IF,
            [
                [
                    'logic_operator' => LogicOperator::AND,
                    'item_name'      => "My question",
                    'item_type'      => Type::QUESTION,
                    'value_operator' => ValueOperator::EQUALS,
                    'value'          => "Yes",
                ],
            ]
        );
        $form = $this->createForm($builder);

        // Act: reset the question's visibility strategy
        $question_id = $this->getQuestionId($form, "My other question");
        $question = $this->updateItem(Question::class, $question_id, [
            'visibility_strategy' => VisibilityStrategy::ALWAYS_VISIBLE->value,
        ]);

        // Assert: the conditions should be deleted
        $this->assertEmpty($question->getConfiguredConditionsData());
    }

    public function testValidationConditionsDataAreCleanedWhenStrategyIsReset(): void
    {
        // Arrange: create a form with visibility conditions on a question
        $builder = new FormBuilder();
        $builder->addQuestion("My question", QuestionTypeShortText::class);
        $builder->addQuestion("My other question", QuestionTypeShortText::class);
        $builder->setQuestionValidation(
            "My other question",
            ValidationStrategy::VALID_IF,
            [
                [
                    'logic_operator' => LogicOperator::AND,
                    'item_name'      => "My question",
                    'item_type'      => Type::QUESTION,
                    'value_operator' => ValueOperator::EQUALS,
                    'value'          => "Yes",
                ],
            ]
        );
        $form = $this->createForm($builder);

        // Act: reset the question's validation strategy
        $question_id = $this->getQuestionId($form, "My other question");
        $question = $this->updateItem(Question::class, $question_id, [
            'validation_strategy' => ValidationStrategy::NO_VALIDATION->value,
        ]);

        // Assert: the conditions should be deleted
        $this->assertEmpty($question->getConfiguredValidationConditionsData());
    }
}
