<?php

declare(strict_types=1);

return [
    'form' => [
        'title' => 'Заголовок',
        'url' => 'URL',
        'linkable_type' => 'Тип',
        'linkable_id' => 'ID',
    ],
    'resource' => [
        'name' => [
            'label' => 'Название',
        ],
        'locations' => [
            'label' => 'Расположения',
            'empty' => 'Не назначено',
        ],
        'items' => [
            'label' => 'Элементы',
        ],
        'is_visible' => [
            'label' => 'Видимость',
            'visible' => 'Видимый',
            'hidden' => 'Скрытый',
        ],
    ],
    'actions' => [
        'add' => [
            'label' => 'Добавить в меню',
        ],
        'indent' => 'Сделать вложенным',
        'unindent' => 'Сделать на одном уровне',
        'locations' => [
            'label' => 'Расположения',
            'heading' => 'Управление расположениями',
            'description' => 'Выберите, какое меню отображается в каждом расположении.',
            'submit' => 'Обновить',
            'form' => [
                'location' => [
                    'label' => 'Расположение',
                ],
                'menu' => [
                    'label' => 'Назначенное меню',
                ],
            ],
            'empty' => [
                'heading' => 'Нет зарегистрированных расположений',
            ],
        ],
    ],
    'items' => [
        'expand' => 'Развернуть',
        'collapse' => 'Свернуть',
        'empty' => [
            'heading' => 'В этом меню нет элементов.',
        ],
    ],
    'custom_link' => 'Пользовательская ссылка',
    'custom_text' => 'Пользовательский текст',
    'open_in' => [
        'label' => 'Открыть в',
        'options' => [
            'self' => 'Этой вкладке',
            'blank' => 'Новой вкладке',
            'parent' => 'Родительской вкладке',
            'top' => 'Верхней вкладке',
        ],
    ],
    'notifications' => [
        'created' => [
            'title' => 'Ссылка создана',
        ],
        'locations' => [
            'title' => 'Расположения меню обновлены',
        ],
    ],
    'panel' => [
        'empty' => [
            'heading' => 'Ничего не найдено',
            'description' => 'В этом меню нет элементов.',
        ],
        'pagination' => [
            'previous' => 'Предыдущая',
            'next' => 'Следующая',
        ],
    ],
];
