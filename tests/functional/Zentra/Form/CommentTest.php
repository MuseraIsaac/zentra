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

use Zentra\Form\Comment;
use Zentra\Form\Condition\LogicOperator;
use Zentra\Form\Condition\Type;
use Zentra\Form\Condition\ValueOperator;
use Zentra\Form\Condition\VisibilityStrategy;
use Zentra\Form\QuestionType\QuestionTypeShortText;
use Zentra\Tests\DbTestCase;
use Zentra\Tests\FormBuilder;
use Zentra\Tests\FormTesterTrait;

final class CommentTest extends DbTestCase
{
    use FormTesterTrait;

    public function testConditionsDataAreCleanedWhenStrategyIsReset(): void
    {
        // Arrange: create a form with visibility conditions on a comment
        $builder = new FormBuilder();
        $builder->addQuestion("My question", QuestionTypeShortText::class);
        $builder->addComment("My comment");
        $builder->setCommentVisibility("My comment", VisibilityStrategy::VISIBLE_IF, [
            [
                'logic_operator' => LogicOperator::AND,
                'item_name'      => "My question",
                'item_type'      => Type::QUESTION,
                'value_operator' => ValueOperator::EQUALS,
                'value'          => "Yes",
            ],
        ]);
        $form = $this->createForm($builder);

        // Act: reset the comment's visibility strategy
        $comment_id = $this->getCommentId($form, "My comment");
        $comment = $this->updateItem(Comment::class, $comment_id, [
            'visibility_strategy' => VisibilityStrategy::ALWAYS_VISIBLE->value,
        ]);

        // Assert: the conditions should be deleted
        $this->assertEmpty($comment->getConfiguredConditionsData());
    }
}
