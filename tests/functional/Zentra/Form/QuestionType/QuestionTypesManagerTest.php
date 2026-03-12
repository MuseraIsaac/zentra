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

use Zentra\Form\QuestionType\QuestionTypeAssignee;
use Zentra\Form\QuestionType\QuestionTypeCategory;
use Zentra\Form\QuestionType\QuestionTypeCategoryInterface;
use Zentra\Form\QuestionType\QuestionTypeCheckbox;
use Zentra\Form\QuestionType\QuestionTypeDateTime;
use Zentra\Form\QuestionType\QuestionTypeDropdown;
use Zentra\Form\QuestionType\QuestionTypeEmail;
use Zentra\Form\QuestionType\QuestionTypeFile;
use Zentra\Form\QuestionType\QuestionTypeInterface;
use Zentra\Form\QuestionType\QuestionTypeItem;
use Zentra\Form\QuestionType\QuestionTypeItemDropdown;
use Zentra\Form\QuestionType\QuestionTypeLongText;
use Zentra\Form\QuestionType\QuestionTypeNumber;
use Zentra\Form\QuestionType\QuestionTypeObserver;
use Zentra\Form\QuestionType\QuestionTypeRadio;
use Zentra\Form\QuestionType\QuestionTypeRequester;
use Zentra\Form\QuestionType\QuestionTypeRequestType;
use Zentra\Form\QuestionType\QuestionTypeShortText;
use Zentra\Form\QuestionType\QuestionTypesManager;
use Zentra\Form\QuestionType\QuestionTypeUrgency;
use Zentra\Form\QuestionType\QuestionTypeUserDevice;
use Zentra\Tests\DbTestCase;
use ZentraPlugin\Tester\Form\QuestionTypeColor;
use ZentraPlugin\Tester\Form\QuestionTypeRange;
use ZentraPlugin\Tester\Form\TesterCategory;
use PHPUnit\Framework\Attributes\DataProvider;

final class QuestionTypesManagerTest extends DbTestCase
{
    /**
     * Test the getQuestionTypes method
     *
     * @return void
     */
    public function testGetDefaultTypeClass(): void
    {
        $manager = QuestionTypesManager::getInstance();
        $default_type = $manager->getDefaultTypeClass();
        $this->assertNotEmpty($default_type);

        // Ensure the default type is a valid question type
        $is_question_type = is_a($default_type, QuestionTypeInterface::class, true);
        $this->assertTrue($is_question_type);

        // Ensure the default type is not an abstract class
        $is_abstract = (new \ReflectionClass($default_type))->isAbstract();
        $this->assertFalse($is_abstract);

        // Ensure constructor is working
        $question_type_object = new $default_type();
        $this->assertNotNull($question_type_object);
    }

    /**
     * Test the getQuestionTypes method
     *
     * @return void
     */
    public function testGetCategories(): void
    {
        $manager = QuestionTypesManager::getInstance();
        $categories = $manager->getCategories();

        $expected_categories = [
            QuestionTypeCategory::SHORT_ANSWER,
            QuestionTypeCategory::LONG_ANSWER,
            QuestionTypeCategory::DATE_AND_TIME,
            QuestionTypeCategory::ACTORS,
            QuestionTypeCategory::URGENCY,
            QuestionTypeCategory::REQUEST_TYPE,
            QuestionTypeCategory::FILE,
            QuestionTypeCategory::RADIO,
            QuestionTypeCategory::CHECKBOX,
            QuestionTypeCategory::DROPDOWN,
            QuestionTypeCategory::ITEM,

            // Plugins categories should be detected too
            new TesterCategory(),
        ];

        // Manual array comparison, `isEqualTo`  doesn't seem to work properly
        // with an array of enums
        $this->assertCount(count($expected_categories), $categories);
        foreach ($categories as $i => $category) {
            $this->assertEquals(
                $expected_categories[$i],
                $category
            );
        }
    }

    public static function getTypesForCategoryProvider(): iterable
    {
        yield [
            'category' => QuestionTypeCategory::SHORT_ANSWER,
            'expected_types' => [
                (new QuestionTypeShortText())->getName(),
                (new QuestionTypeEmail())->getName(),
                (new QuestionTypeNumber())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::LONG_ANSWER,
            'expected_types' => [
                (new QuestionTypeLongText())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::DATE_AND_TIME,
            'expected_types' => [
                (new QuestionTypeDateTime())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::ACTORS,
            'expected_types' => [
                (new QuestionTypeRequester())->getName(),
                (new QuestionTypeObserver())->getName(),
                (new QuestionTypeAssignee())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::URGENCY,
            'expected_types' => [
                (new QuestionTypeUrgency())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::REQUEST_TYPE,
            'expected_types' => [
                (new QuestionTypeRequestType())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::FILE,
            'expected_types' => [
                (new QuestionTypeFile())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::RADIO,
            'expected_types' => [
                (new QuestionTypeRadio())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::CHECKBOX,
            'expected_types' => [
                (new QuestionTypeCheckbox())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::DROPDOWN,
            'expected_types' => [
                (new QuestionTypeDropdown())->getName(),
            ],
        ];

        yield [
            'category' => QuestionTypeCategory::ITEM,
            'expected_types' => [
                (new QuestionTypeItem())->getName(),
                (new QuestionTypeUserDevice())->getName(),
                (new QuestionTypeItemDropdown())->getName(),
            ],
        ];

        yield [
            'category' => new TesterCategory(),
            'expected_types' => [
                (new QuestionTypeRange())->getName(),
                (new QuestionTypeColor())->getName(),
            ],
        ];
    }

    #[DataProvider('getTypesForCategoryProvider')]
    public function testGetTypesForCategory(
        QuestionTypeCategoryInterface $category,
        array $expected_types
    ): void {
        $manager = QuestionTypesManager::getInstance();
        $types = $manager->getQuestionTypesDropdownValuesForCategory($category);
        $types = array_values($types); // Ignore keys

        $this->assertEquals($expected_types, $types);
    }

    /**
     * This test case ensure all categories are defined by the
     * testGetTypesForCategoryProvider provider.
     *
     * This prevent us from forgetting to update this provider when adding new
     * questions types
     *
     * @return void
     */
    public function testEnsureAllCategoriesAreTested(): void
    {
        $manager = QuestionTypesManager::getInstance();
        $provider_data = iterator_to_array($this->getTypesForCategoryProvider());

        $this->assertCount(
            count($manager->getCategories()),
            $provider_data,
            "All categories must be added to the `testGetTypesForCategoryProvider` provider"
        );
    }
}
