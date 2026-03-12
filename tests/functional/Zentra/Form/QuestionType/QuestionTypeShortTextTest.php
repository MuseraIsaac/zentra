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

namespace tests\units\Zentra\Form\QuestionType;

use Zentra\Form\QuestionType\QuestionTypeShortText;
use Zentra\Tests\DbTestCase;
use Zentra\Tests\FormBuilder;
use Zentra\Tests\FormTesterTrait;

final class QuestionTypeShortTextTest extends DbTestCase
{
    use FormTesterTrait;

    public function testShortTextAnswerIsDisplayedInTicketDescription(): void
    {
        $builder = new FormBuilder();
        $builder->addQuestion("First name", QuestionTypeShortText::class);
        $form = $this->createForm($builder);

        $ticket = $this->sendFormAndGetCreatedTicket($form, [
            "First name" => "John",
        ]);

        $this->assertStringContainsString(
            "1) First name: John",
            strip_tags($ticket->fields['content']),
        );
    }
}
