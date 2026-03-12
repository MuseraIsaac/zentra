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

use CommonITILObject_CommonITILObject;
use Zentra\DBAL\JsonFieldInterface;
use Zentra\Form\Destination\CommonITILField\AssigneeField;
use Zentra\Form\Destination\CommonITILField\AssigneeFieldConfig;
use Zentra\Form\Destination\CommonITILField\AssociatedItemsField;
use Zentra\Form\Destination\CommonITILField\AssociatedItemsFieldConfig;
use Zentra\Form\Destination\CommonITILField\AssociatedItemsFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ContentField;
use Zentra\Form\Destination\CommonITILField\EntityField;
use Zentra\Form\Destination\CommonITILField\EntityFieldConfig;
use Zentra\Form\Destination\CommonITILField\EntityFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILActorFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILCategoryField;
use Zentra\Form\Destination\CommonITILField\ITILCategoryFieldConfig;
use Zentra\Form\Destination\CommonITILField\ITILCategoryFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILFollowupField;
use Zentra\Form\Destination\CommonITILField\ITILFollowupFieldConfig;
use Zentra\Form\Destination\CommonITILField\ITILFollowupFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ITILTaskField;
use Zentra\Form\Destination\CommonITILField\ITILTaskFieldConfig;
use Zentra\Form\Destination\CommonITILField\ITILTaskFieldStrategy;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsField;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsFieldConfig;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsFieldStrategy;
use Zentra\Form\Destination\CommonITILField\LinkedITILObjectsFieldStrategyConfig;
use Zentra\Form\Destination\CommonITILField\LocationField;
use Zentra\Form\Destination\CommonITILField\LocationFieldConfig;
use Zentra\Form\Destination\CommonITILField\LocationFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ObserverField;
use Zentra\Form\Destination\CommonITILField\ObserverFieldConfig;
use Zentra\Form\Destination\CommonITILField\OLATTOField;
use Zentra\Form\Destination\CommonITILField\OLATTOFieldConfig;
use Zentra\Form\Destination\CommonITILField\OLATTRField;
use Zentra\Form\Destination\CommonITILField\OLATTRFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequesterField;
use Zentra\Form\Destination\CommonITILField\RequesterFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequestSourceField;
use Zentra\Form\Destination\CommonITILField\RequestSourceFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequestSourceFieldStrategy;
use Zentra\Form\Destination\CommonITILField\RequestTypeField;
use Zentra\Form\Destination\CommonITILField\RequestTypeFieldConfig;
use Zentra\Form\Destination\CommonITILField\RequestTypeFieldStrategy;
use Zentra\Form\Destination\CommonITILField\SimpleValueConfig;
use Zentra\Form\Destination\CommonITILField\SLATTOField;
use Zentra\Form\Destination\CommonITILField\SLATTOFieldConfig;
use Zentra\Form\Destination\CommonITILField\SLATTRFieldConfig;
use Zentra\Form\Destination\CommonITILField\SLMFieldStrategy;
use Zentra\Form\Destination\CommonITILField\TemplateField;
use Zentra\Form\Destination\CommonITILField\TemplateFieldConfig;
use Zentra\Form\Destination\CommonITILField\TemplateFieldStrategy;
use Zentra\Form\Destination\CommonITILField\TitleField;
use Zentra\Form\Destination\CommonITILField\UrgencyField;
use Zentra\Form\Destination\CommonITILField\UrgencyFieldConfig;
use Zentra\Form\Destination\CommonITILField\UrgencyFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ValidationField;
use Zentra\Form\Destination\CommonITILField\ValidationFieldConfig;
use Zentra\Form\Destination\CommonITILField\ValidationFieldStrategy;
use Zentra\Form\Destination\CommonITILField\ValidationFieldStrategyConfig;
use Zentra\Form\Destination\FormDestinationTicket;
use Zentra\Form\Export\Serializer\FormSerializer;
use Zentra\Form\Form;
use Zentra\Form\QuestionType\QuestionTypeActorsExtraDataConfig;
use Zentra\Form\QuestionType\QuestionTypeAssignee;
use Zentra\Form\QuestionType\QuestionTypeItem;
use Zentra\Form\QuestionType\QuestionTypeItemDropdown;
use Zentra\Form\QuestionType\QuestionTypeItemDropdownExtraDataConfig;
use Zentra\Form\QuestionType\QuestionTypeItemExtraDataConfig;
use Zentra\Form\QuestionType\QuestionTypeObserver;
use Zentra\Form\QuestionType\QuestionTypeRequester;
use Zentra\Form\QuestionType\QuestionTypeRequestType;
use Zentra\Form\QuestionType\QuestionTypeUrgency;
use Zentra\Form\QuestionType\QuestionTypeUserDevice;
use Zentra\Tests\DbTestCase;
use Zentra\Tests\FormBuilder;
use Zentra\Tests\FormTesterTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use SLM;
use Ticket;

use function PHPUnit\Framework\assertEquals;

final class FormSerializerDestinationTest extends DbTestCase
{
    use FormTesterTrait;

    private static FormSerializer $serializer;

    public static function setUpBeforeClass(): void
    {
        self::$serializer = new FormSerializer();
        parent::setUpBeforeClass();
    }

    public static function getFieldDestinationsWithSpecificValueProvider(): iterable
    {
        yield 'TitleField' => [
            'field_key' => TitleField::getKey(),
            'config'    => new SimpleValueConfig(value: 'My title'),
        ];

        yield 'ContentField' => [
            'field_key' => ContentField::getKey(),
            'config'    => new SimpleValueConfig(value: 'My content'),
        ];

        yield 'TemplateField' => [
            'field_key'        => TemplateField::getKey(),
            'config_fn'        => fn($created_items) => new TemplateFieldConfig(
                strategy: TemplateFieldStrategy::SPECIFIC_TEMPLATE,
                specific_template_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \TicketTemplate::class,
                    'input'    => [
                        'name'        => 'My ticket template',
                    ],
                ],
            ],
        ];

        yield 'ITILCategoryField' => [
            'field_key'        => ITILCategoryField::getKey(),
            'config_fn'        => fn($created_items) => new ITILCategoryFieldConfig(
                strategy: ITILCategoryFieldStrategy::SPECIFIC_VALUE,
                specific_itilcategory_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \ITILCategory::class,
                    'input'    => [
                        'name'        => 'My ITIL category',
                    ],
                ],
            ],
        ];

        yield 'EntityField' => [
            'field_key'        => EntityField::getKey(),
            'config_fn'        => fn($created_items) => new EntityFieldConfig(
                strategy: EntityFieldStrategy::SPECIFIC_VALUE,
                specific_entity_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \Entity::class,
                    'input'    => [
                        'name' => 'My entity',
                    ],
                ],
            ],
        ];

        yield 'LocationField' => [
            'field_key'        => LocationField::getKey(),
            'config_fn'        => fn($created_items) => new LocationFieldConfig(
                strategy: LocationFieldStrategy::SPECIFIC_VALUE,
                specific_location_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \Location::class,
                    'input'    => [
                        'name' => 'My location',
                    ],
                ],
            ],
        ];

        yield 'AssociatedItemsField' => [
            'field_key'        => AssociatedItemsField::getKey(),
            'config_fn'        => fn($created_items) => new AssociatedItemsFieldConfig(
                strategies: [AssociatedItemsFieldStrategy::SPECIFIC_VALUES],
                specific_associated_items: [
                    \Computer::class => [$created_items[0]->getId()],
                    \Monitor::class  => [$created_items[1]->getId()],
                ]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \Computer::class,
                    'input'    => [
                        'name' => 'My computer',
                    ],
                ],
                [
                    'itemtype' => \Monitor::class,
                    'input'    => [
                        'name' => 'My monitor',
                    ],
                ],
            ],
            'keys_to_ignore' => [],
        ];

        yield 'ITILFollowupField' => [
            'field_key'        => ITILFollowupField::getKey(),
            'config_fn'        => fn($created_items) => new ITILFollowupFieldConfig(
                strategy: ITILFollowupFieldStrategy::SPECIFIC_VALUES,
                specific_itilfollowuptemplates_ids: [$created_items[0]->getId()]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \ITILFollowupTemplate::class,
                    'input'    => [
                        'name' => 'My ITIL followup template',
                    ],
                ],
            ],
        ];

        yield 'RequestSourceField' => [
            'field_key'        => RequestSourceField::getKey(),
            'config_fn'        => fn($created_items) => new RequestSourceFieldConfig(
                strategy: RequestSourceFieldStrategy::SPECIFIC_VALUE,
                specific_request_source: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \RequestType::class,
                    'input'    => [
                        'name' => 'My request source',
                    ],
                ],
            ],
        ];

        yield 'ValidationField' => [
            'field_key'        => ValidationField::getKey(),
            'config_fn'        => fn($created_items) => new ValidationFieldConfig([
                new ValidationFieldStrategyConfig(
                    strategy: ValidationFieldStrategy::SPECIFIC_ACTORS,
                    specific_actors: [
                        \User::class => [$created_items[0]->getId()],
                        \Group::class => [$created_items[1]->getId()],
                    ]
                ),
            ]),
            'items_to_create'  => [
                [
                    'itemtype' => \User::class,
                    'input'    => [
                        'name' => 'My user',
                    ],
                ],
                [
                    'itemtype' => \Group::class,
                    'input'    => [
                        'name' => 'My group',
                    ],
                ],
            ],
            'check_fn' => function ($imported_destinations, $created_items) {
                assertEquals(
                    (new ValidationFieldConfig([
                        new ValidationFieldStrategyConfig(
                            strategy: ValidationFieldStrategy::SPECIFIC_ACTORS,
                            specific_actors: [
                                \User::class => [$created_items[0]->getId()],
                                \Group::class => [$created_items[1]->getId()],
                            ]
                        ),
                    ]))->jsonSerialize(),
                    end($imported_destinations)->getConfig()[ValidationField::getKey()]
                );
            },
        ];

        yield 'ITILTaskField' => [
            'field_key' => ITILTaskField::getKey(),
            'config_fn' => fn($created_items) => new ITILTaskFieldConfig(
                strategy: ITILTaskFieldStrategy::SPECIFIC_VALUES,
                specific_itiltasktemplates_ids: [$created_items[0]->getId()]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \TaskTemplate::class,
                    'input'    => [
                        'name' => 'My ITIL task',
                    ],
                ],
            ],
        ];

        yield 'RequesterField' => [
            'field_key' => RequesterField::getKey(),
            'config_fn' => fn($created_items) => new RequesterFieldConfig(
                strategies: [ITILActorFieldStrategy::SPECIFIC_VALUES],
                specific_itilactors_ids: [
                    getForeignKeyFieldForItemType(\User::class) . '-' . $created_items[0]->getId(),
                    getForeignKeyFieldForItemType(\Group::class) . '-' . $created_items[1]->getId(),
                    getForeignKeyFieldForItemType(\Supplier::class) . '-' . $created_items[2]->getId(),
                ]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \User::class,
                    'input'    => [
                        'name' => 'My user',
                    ],
                ],
                [
                    'itemtype' => \Group::class,
                    'input'    => [
                        'name' => 'My group',
                    ],
                ],
                [
                    'itemtype' => \Supplier::class,
                    'input'    => [
                        'name' => 'My supplier',
                    ],
                ],
            ],
            'check_fn' => function ($imported_destinations, $created_items) {
                assertEquals(
                    (new RequesterFieldConfig(
                        strategies: [ITILActorFieldStrategy::SPECIFIC_VALUES],
                        specific_itilactors_ids: [
                            \User::class => [$created_items[0]->getId()],
                            \Group::class => [$created_items[1]->getId()],
                            \Supplier::class => [$created_items[2]->getId()],
                        ]
                    ))->jsonSerialize(),
                    end($imported_destinations)->getConfig()[RequesterField::getKey()]
                );
            },
        ];

        yield 'ObserverField' => [
            'field_key' => ObserverField::getKey(),
            'config_fn' => fn($created_items) => new ObserverFieldConfig(
                strategies: [ITILActorFieldStrategy::SPECIFIC_VALUES],
                specific_itilactors_ids: [
                    getForeignKeyFieldForItemType(\User::class) . '-' . $created_items[0]->getId(),
                    getForeignKeyFieldForItemType(\Group::class) . '-' . $created_items[1]->getId(),
                ]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \User::class,
                    'input'    => [
                        'name' => 'My user',
                    ],
                ],
                [
                    'itemtype' => \Group::class,
                    'input'    => [
                        'name' => 'My group',
                    ],
                ],
            ],
            'check_fn' => function ($imported_destinations, $created_items) {
                assertEquals(
                    (new ObserverFieldConfig(
                        strategies: [ITILActorFieldStrategy::SPECIFIC_VALUES],
                        specific_itilactors_ids: [
                            \User::class => [$created_items[0]->getId()],
                            \Group::class => [$created_items[1]->getId()],
                        ]
                    ))->jsonSerialize(),
                    end($imported_destinations)->getConfig()[ObserverField::getKey()]
                );
            },
        ];

        yield 'AssigneeField' => [
            'field_key' => AssigneeField::getKey(),
            'config_fn' => fn($created_items) => new AssigneeFieldConfig(
                strategies: [ITILActorFieldStrategy::SPECIFIC_VALUES],
                specific_itilactors_ids: [
                    getForeignKeyFieldForItemType(\User::class) . '-' . $created_items[0]->getId(),
                    getForeignKeyFieldForItemType(\Group::class) . '-' . $created_items[1]->getId(),
                ]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \User::class,
                    'input'    => [
                        'name' => 'My user',
                    ],
                ],
                [
                    'itemtype' => \Group::class,
                    'input'    => [
                        'name' => 'My group',
                    ],
                ],
            ],
            'check_fn' => function ($imported_destinations, $created_items) {
                assertEquals(
                    (new AssigneeFieldConfig(
                        strategies: [ITILActorFieldStrategy::SPECIFIC_VALUES],
                        specific_itilactors_ids: [
                            \User::class => [$created_items[0]->getId()],
                            \Group::class => [$created_items[1]->getId()],
                        ]
                    ))->jsonSerialize(),
                    end($imported_destinations)->getConfig()[AssigneeField::getKey()]
                );
            },
        ];

        yield 'SLATTOField' => [
            'field_key' => SLATTOField::getKey(),
            'config_fn' => fn($created_items) => new SLATTOFieldConfig(
                strategy: SLMFieldStrategy::SPECIFIC_VALUE,
                specific_slm_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \SLA::class,
                    'input'    => [
                        'name'            => 'My SLA',
                        'type'            => SLM::TTO,
                        'number_time'     => 4,
                        'definition_time' => 'hour',
                    ],
                ],
            ],
        ];

        yield 'SLATTRField' => [
            'field_key' => SLATTOField::getKey(),
            'config_fn' => fn($created_items) => new SLATTRFieldConfig(
                strategy: SLMFieldStrategy::SPECIFIC_VALUE,
                specific_slm_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \SLA::class,
                    'input'    => [
                        'name'            => 'My SLA',
                        'type'            => SLM::TTR,
                        'number_time'     => 4,
                        'definition_time' => 'hour',
                    ],
                ],
            ],
        ];

        yield 'OLATTRField' => [
            'field_key' => OLATTRField::getKey(),
            'config_fn' => fn($created_items) => new OLATTRFieldConfig(
                strategy: SLMFieldStrategy::SPECIFIC_VALUE,
                specific_slm_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \OLA::class,
                    'input'    => [
                        'name'            => 'My OLA',
                        'type'            => SLM::TTR,
                        'number_time'     => 4,
                        'definition_time' => 'hour',
                    ],
                ],
            ],
        ];

        yield 'OLATTOField' => [
            'field_key' => OLATTOField::getKey(),
            'config_fn' => fn($created_items) => new OLATTOFieldConfig(
                strategy: SLMFieldStrategy::SPECIFIC_VALUE,
                specific_slm_id: $created_items[0]->getId()
            ),
            'items_to_create'  => [
                [
                    'itemtype' => \OLA::class,
                    'input'    => [
                        'name'            => 'My OLA',
                        'type'            => SLM::TTO,
                        'number_time'     => 4,
                        'definition_time' => 'hour',
                    ],
                ],
            ],
        ];

        yield 'UrgencyField' => [
            'field_key' => UrgencyField::getKey(),
            'config' => new UrgencyFieldConfig(
                strategy: UrgencyFieldStrategy::SPECIFIC_VALUE,
                specific_urgency_value: 5
            ),
        ];

        yield 'RequestTypeField' => [
            'field_key' => RequestTypeField::getKey(),
            'config' => new RequestTypeFieldConfig(
                strategy: RequestTypeFieldStrategy::SPECIFIC_VALUE,
                specific_request_type: Ticket::DEMAND_TYPE
            ),
        ];

        yield 'LinkedITILObjectsField' => [
            'field_key' => LinkedITILObjectsField::getKey(),
            'config_fn' => fn($created_items) => new LinkedITILObjectsFieldConfig(
                strategy_configs: [
                    new LinkedITILObjectsFieldStrategyConfig(
                        strategy: LinkedITILObjectsFieldStrategy::SPECIFIC_VALUES,
                        specific_itilobject: [
                            [
                                'itemtype' => Ticket::class,
                                'id'      => $created_items[0]->getId(),
                            ],
                        ]
                    ),
                ]
            ),
            'items_to_create'  => [
                [
                    'itemtype' => Ticket::class,
                    'input'    => [
                        'name'    => 'My ticket',
                        'content' => 'My ticket content',
                    ],
                ],
            ],
        ];
    }

    #[DataProvider('getFieldDestinationsWithSpecificValueProvider')]
    public function testImportAndExportForDestinationFieldWithSpecificValueStrategy(
        string $field_key,
        ?JsonFieldInterface $config = null,
        ?callable $config_fn = null,
        array $items_to_create = [],
        ?callable $check_fn = null,
        array $keys_to_ignore = []
    ) {
        $this->login();

        $created_items = [];
        foreach ($items_to_create as $item) {
            if ((new $item['itemtype']())->isEntityAssign()) {
                $item['input']['entities_id'] = $this->getTestRootEntity(only_id: true);
            }

            $created_items[] = $this->createItem(
                $item['itemtype'],
                $item['input']
            );
        }

        if ($config === null) {
            $config = $config_fn($created_items);
        }

        $imported_form = $this->checkImportAndExport(
            $field_key,
            $config
        );
        $imported_destinations = $imported_form->getDestinations();

        if ($check_fn === null) {
            $this->assertEquals(
                array_diff_key(
                    $config->jsonSerialize(),
                    array_flip($keys_to_ignore)
                ),
                end($imported_destinations)->getConfig()[$field_key]
            );
        } else {
            $check_fn($imported_destinations, $created_items);
        }
    }

    public static function getFieldDestinationsWithSpecificQuestionProvider(): iterable
    {
        yield 'ITILCategoryField' => [
            'field_key' => ITILCategoryField::getKey(),
            'config_fn'    => fn($questions) => new ITILCategoryFieldConfig(
                strategy: ITILCategoryFieldStrategy::SPECIFIC_ANSWER,
                specific_question_id: current($questions)->getId(),
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeItemDropdown::class,
                    'extra_data' => json_encode((new QuestionTypeItemDropdownExtraDataConfig(
                        itemtype: \ITILCategory::class
                    ))),
                ],
            ],
        ];

        yield 'EntityField' => [
            'field_key' => EntityField::getKey(),
            'config_fn'    => fn($questions) => new EntityFieldConfig(
                strategy: EntityFieldStrategy::SPECIFIC_ANSWER,
                specific_question_id: current($questions)->getId(),
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeItem::class,
                    'extra_data' => json_encode((new QuestionTypeItemExtraDataConfig(
                        itemtype: \Entity::class
                    ))->jsonSerialize()),
                ],
            ],
        ];

        yield 'LocationField' => [
            'field_key' => LocationField::getKey(),
            'config_fn'    => fn($questions) => new LocationFieldConfig(
                strategy: LocationFieldStrategy::SPECIFIC_ANSWER,
                specific_question_id: current($questions)->getId(),
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeItemDropdown::class,
                    'extra_data' => json_encode((new QuestionTypeItemDropdownExtraDataConfig(
                        itemtype: \Location::class
                    ))->jsonSerialize()),
                ],
            ],
        ];

        yield 'AssociatedItemsField' => [
            'field_key' => AssociatedItemsField::getKey(),
            'config_fn'    => fn($questions) => new AssociatedItemsFieldConfig(
                strategies: [AssociatedItemsFieldStrategy::SPECIFIC_ANSWERS],
                specific_question_ids: [
                    current($questions)->getId(),
                    next($questions)->getId(),
                ],
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeItem::class,
                    'extra_data' => json_encode((new QuestionTypeItemExtraDataConfig(
                        itemtype: \Computer::class
                    ))->jsonSerialize()),
                ],
                [
                    'type' => QuestionTypeUserDevice::class,
                ],
            ],
            'question_id_key' => 'specific_question_ids',
        ];

        yield 'ValidationField' => [
            'field_key' => ValidationField::getKey(),
            'config_fn'    => fn($questions) => new ValidationFieldConfig([
                new ValidationFieldStrategyConfig(
                    strategy: ValidationFieldStrategy::SPECIFIC_ANSWERS,
                    specific_question_ids: [
                        current($questions)->getId(),
                        next($questions)->getId(),
                        next($questions)->getId(),
                        next($questions)->getId(),
                    ],
                ),
            ]),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeItem::class,
                    'extra_data' => json_encode((new QuestionTypeItemExtraDataConfig(
                        itemtype: \User::class
                    ))->jsonSerialize()),
                ],
                [
                    'type' => QuestionTypeItem::class,
                    'extra_data' => json_encode((new QuestionTypeItemExtraDataConfig(
                        itemtype: \Group::class
                    ))->jsonSerialize()),
                ],
                [
                    'type' => QuestionTypeObserver::class,
                ],
                [
                    'type' => QuestionTypeAssignee::class,
                ],
            ],
            'question_id_key' => 'specific_question_ids',
            'question_id_extractor' => function (array $config) {
                // Extract question IDs from the first strategy config that has them
                foreach ($config['strategy_configs'] as $strategy_config) {
                    if (isset($strategy_config['specific_question_ids'])) {
                        return $strategy_config['specific_question_ids'];
                    }
                }
                return [];
            },
        ];

        yield 'RequesterField' => [
            'field_key' => RequesterField::getKey(),
            'config_fn' => fn($questions) => new RequesterFieldConfig(
                strategies: [ITILActorFieldStrategy::SPECIFIC_ANSWERS],
                specific_question_ids: [
                    current($questions)->getId(),
                    next($questions)->getId(),
                ],
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeRequester::class,
                ],
                [
                    'type' => QuestionTypeRequester::class,
                    'extra_data' => json_encode((new QuestionTypeActorsExtraDataConfig(
                        is_multiple_actors: true
                    ))->jsonSerialize()),
                ],
            ],
            'question_id_key' => 'specific_question_ids',
        ];

        yield 'ObserverField' => [
            'field_key' => ObserverField::getKey(),
            'config_fn' => fn($questions) => new ObserverFieldConfig(
                strategies: [ITILActorFieldStrategy::SPECIFIC_ANSWERS],
                specific_question_ids: [
                    current($questions)->getId(),
                    next($questions)->getId(),
                ],
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeObserver::class,
                ],
                [
                    'type' => QuestionTypeObserver::class,
                    'extra_data' => json_encode((new QuestionTypeActorsExtraDataConfig(
                        is_multiple_actors: true
                    ))->jsonSerialize()),
                ],
            ],
            'question_id_key' => 'specific_question_ids',
        ];

        yield 'AssigneeField' => [
            'field_key' => AssigneeField::getKey(),
            'config_fn' => fn($questions) => new AssigneeFieldConfig(
                strategies: [ITILActorFieldStrategy::SPECIFIC_ANSWERS],
                specific_question_ids: [
                    current($questions)->getId(),
                    next($questions)->getId(),
                ],
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeAssignee::class,
                ],
                [
                    'type' => QuestionTypeAssignee::class,
                    'extra_data' => json_encode((new QuestionTypeActorsExtraDataConfig(
                        is_multiple_actors: true
                    ))->jsonSerialize()),
                ],
            ],
            'question_id_key' => 'specific_question_ids',
        ];

        yield 'UrgencyField' => [
            'field_key' => UrgencyField::getKey(),
            'config_fn'    => fn($questions) => new UrgencyFieldConfig(
                strategy: UrgencyFieldStrategy::SPECIFIC_ANSWER,
                specific_question_id: current($questions)->getId(),
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeUrgency::class,
                ],
            ],
        ];

        yield 'RequestTypeField' => [
            'field_key' => RequestTypeField::getKey(),
            'config_fn'    => fn($questions) => new RequestTypeFieldConfig(
                strategy: RequestTypeFieldStrategy::SPECIFIC_ANSWER,
                specific_question_id: current($questions)->getId(),
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeRequestType::class,
                ],
            ],
        ];

        yield 'LinkedITILObjectsField' => [
            'field_key' => LinkedITILObjectsField::getKey(),
            'config_fn'    => fn($questions) => new LinkedITILObjectsFieldConfig(
                strategy_configs: [
                    new LinkedITILObjectsFieldStrategyConfig(
                        strategy: LinkedITILObjectsFieldStrategy::SPECIFIC_ANSWERS,
                        linktype: CommonITILObject_CommonITILObject::LINK_TO,
                        specific_question_ids: [current($questions)->getId()],
                    ),
                ]
            ),
            'questions_to_create' => [
                [
                    'type' => QuestionTypeItem::class,
                    'extra_data' => json_encode((new QuestionTypeItemExtraDataConfig(
                        itemtype: Ticket::class
                    ))->jsonSerialize()),
                ],
            ],
            'question_id_key' => 'specific_question_ids',
            'question_id_extractor' => function (array $config) {
                // Extract question IDs from the first strategy config that has them
                foreach ($config['strategy_configs'] as $strategy_config) {
                    if (isset($strategy_config['specific_question_ids'])) {
                        return $strategy_config['specific_question_ids'];
                    }
                }
                return [];
            },
        ];
    }

    #[DataProvider('getFieldDestinationsWithSpecificQuestionProvider')]
    public function testImportAndExportForDestinationFieldWithSpecificQuestionStrategy(
        string $field_key,
        ?JsonFieldInterface $config = null,
        ?callable $config_fn = null,
        array $questions_to_create = [],
        string $question_id_key = 'specific_question_id',
        ?callable $question_id_extractor = null
    ) {
        $this->login();

        $question_names = [];
        $form_builder = $this->getFormBuilderWithTicketDestination();
        foreach ($questions_to_create as $question_data) {
            $question_names[] = sprintf('%s - %s', $question_data['type'], count($question_names));
            $form_builder->addQuestion(
                $question_names[count($question_names) - 1],
                $question_data['type'],
                $question_data['default_value'] ?? '',
                $question_data['extra_data'] ?? ''
            );
        }
        $form = $this->createForm($form_builder);

        if ($config === null) {
            $config = $config_fn($form->getQuestions());
        }

        $imported_form = $this->checkImportAndExport(
            $field_key,
            $config,
            $form
        );
        $imported_destinations = $imported_form->getDestinations();

        // Extract question IDs from original and imported configs using the appropriate strategy
        if ($question_id_extractor !== null) {
            $original_question_ids = $question_id_extractor($config->jsonSerialize());
            $imported_question_ids = $question_id_extractor(end($imported_destinations)->getConfig()[$field_key]);
        } else {
            $original_question_ids = $config->jsonSerialize()[$question_id_key];
            $imported_question_ids = end($imported_destinations)->getConfig()[$field_key][$question_id_key];
        }

        $this->assertNotEquals($original_question_ids, $imported_question_ids);

        if (is_array($original_question_ids)) {
            foreach ($original_question_ids as $key => $question_id) {
                // Retrieve the question id from the imported form
                $this->assertEquals(
                    $form->getQuestions()[$question_id]->getName(),
                    $imported_form->getQuestions()[$imported_question_ids[$key]]->getName()
                );
            }
        } else {
            $this->assertEquals(
                $form->getQuestions()[$original_question_ids]->getName(),
                $imported_form->getQuestions()[$imported_question_ids]->getName()
            );
        }
    }

    public static function getFieldDestinationsWithSpecificDestinationProvider(): iterable
    {
        yield 'LinkedITILObjectsField' => [
            'field_key' => LinkedITILObjectsField::getKey(),
            'config_fn'    => fn($destinations) => new LinkedITILObjectsFieldConfig(
                strategy_configs: [
                    new LinkedITILObjectsFieldStrategyConfig(
                        strategy: LinkedITILObjectsFieldStrategy::SPECIFIC_DESTINATIONS,
                        linktype: CommonITILObject_CommonITILObject::LINK_TO,
                        specific_destination_ids: [current($destinations)->getId()],
                    ),
                ]
            ),
            'destination_to_create' => [
                [
                    'type' => FormDestinationTicket::class,
                    'name' => 'My ticket destination',
                ],
            ],
            'destination_id_key' => 'specific_question_ids',
            'destination_id_extractor' => function (array $config) {
                // Extract question IDs from the first strategy config that has them
                foreach ($config['strategy_configs'] as $strategy_config) {
                    if (isset($strategy_config['specific_destination_ids'])) {
                        return $strategy_config['specific_destination_ids'];
                    }
                }
                return [];
            },
        ];
    }

    #[DataProvider('getFieldDestinationsWithSpecificDestinationProvider')]
    public function testImportAndExportForDestinationFieldWithSpecificDestinationStrategy(
        string $field_key,
        ?JsonFieldInterface $config                = null,
        ?callable           $config_fn             = null,
        array               $destination_to_create = [],
        string              $destination_id_key    = 'specific_destination_id',
        ?callable           $destination_id_extractor = null
    ) {
        $this->login();

        $form_builder = $this->getFormBuilderWithTicketDestination();
        foreach ($destination_to_create as $destination_data) {
            $form_builder->addDestination(
                $destination_data['type'],
                $destination_data['name']
            );
        }
        $form = $this->createForm($form_builder);

        if ($config === null) {
            $config = $config_fn($form->getDestinations());
        }

        $imported_form = $this->checkImportAndExport(
            $field_key,
            $config,
            $form,
            2 // We expect 2 destinations after import (the original one + the one created for the test
        );
        $imported_destinations = $imported_form->getDestinations();

        // Extract question IDs from original and imported configs using the appropriate strategy
        if ($destination_id_extractor !== null) {
            $original_destination_ids = $destination_id_extractor($config->jsonSerialize());
            $imported_destination_ids = $destination_id_extractor(end($imported_destinations)->getConfig()[$field_key]);
        } else {
            $original_destination_ids = $config->jsonSerialize()[$destination_id_key];
            $imported_destination_ids = end($imported_destinations)->getConfig()[$field_key][$destination_id_key];
        }

        $this->assertNotEquals($original_destination_ids, $imported_destination_ids);

        if (is_array($original_destination_ids)) {
            foreach ($original_destination_ids as $key => $destination_id) {
                // Retrieve the destination id from the imported form
                $this->assertEquals(
                    $form->getDestinations()[$destination_id]->getName(),
                    $imported_form->getDestinations()[$imported_destination_ids[$key]]->getName()
                );
            }
        } else {
            $this->assertEquals(
                $form->getDestinations()[$original_destination_ids]->getName(),
                $imported_form->getDestinations()[$imported_destination_ids]->getName()
            );
        }
    }

    /**
     * Check the import and export process for a given key
     *
     * @param string $key
     * @param JsonFieldInterface $config
     * @param string[] $keys_to_skip
     * @param Form|null $form
     * @return Form The imported form
     */
    private function checkImportAndExport(
        string $key,
        JsonFieldInterface $config,
        ?Form $form = null,
        int $destinations_count = 1
    ): Form {
        if ($form === null) {
            $form = $this->createForm($this->getFormBuilderWithTicketDestination());
        }

        // Insert config into the second destination
        $destinations = $form->getDestinations();
        $destination = end($destinations);
        $this->updateItem(
            $destination::getType(),
            $destination->getId(),
            ['config' => [$key => $config->jsonSerialize()]],
            ["config"],
        );

        // Export and import process
        $imported_form = $this->exportAndImportForm($form);
        $imported_destinations = $imported_form->getDestinations();

        $this->assertCount($destinations_count, $imported_destinations);

        return $imported_form;
    }

    private function getFormBuilderWithTicketDestination(): FormBuilder
    {
        $builder = new FormBuilder();
        return $builder;
    }
}
