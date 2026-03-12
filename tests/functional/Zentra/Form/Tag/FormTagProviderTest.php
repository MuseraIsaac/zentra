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

namespace tests\units\Zentra\Form\Tag;

use Zentra\Form\AnswersSet;
use Zentra\Form\Form;
use Zentra\Form\Tag\FormTagProvider;
use Zentra\Form\Tag\Tag;
use Zentra\Tests\DbTestCase;
use Zentra\Tests\FormBuilder;
use Zentra\Tests\FormTesterTrait;

final class FormTagProviderTest extends DbTestCase
{
    use FormTesterTrait;

    public function testGetTagsForEmptyForm(): void
    {
        $form = $this->createForm(new FormBuilder());
        $this->checkTestGetTags($form, [
            new Tag(
                label: 'Form name: Test form',
                value: $form->getId(),
                provider: new FormTagProvider(),
            ),
        ]);
    }

    public function testGetTagsForFormWithName(): void
    {
        $form = $this->createForm((new FormBuilder())->setName('My form'));
        $this->checkTestGetTags($form, [
            new Tag(
                label: 'Form name: My form',
                value: $form->getId(),
                provider: new FormTagProvider(),
            ),
        ]);
    }

    private function checkTestGetTags(Form $form, array $expected): void
    {
        $tagProvider = new FormTagProvider();
        $tags = $tagProvider->getTags($form);
        $this->assertEquals($expected, $tags);
    }

    public function testGetTagContentForValueUsingInvalidValue(): void
    {
        $this->checkGetTagContentForValue('not a valid form id', '');
    }

    public function testGetTagContentForValueUsingFormWithName(): void
    {
        $form = $this->createForm((new FormBuilder())->setName('My form'));
        $this->checkGetTagContentForValue(
            $form->getId(),
            'My form'
        );
    }

    private function checkGetTagContentForValue(
        string $value,
        string $expected_content
    ): void {
        $tag_provider = new FormTagProvider();

        $computed_content = $tag_provider->getTagContentForValue(
            $value,
            new AnswersSet(), // Answers don't (yet) matter for this provider.
        );
        $this->assertEquals($expected_content, $computed_content);
    }
}
