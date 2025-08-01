<?php
declare(strict_types=1);

namespace Datlechin\FilamentMenuBuilder\Actions;

use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Datlechin\FilamentMenuBuilder\Enums\LinkTarget;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use ReflectionClass;

class AddMenuItemAction
{
    protected Model $menu;

    public function __construct(Model $menu)
    {
        $this->menu = $menu;
    }

    public static function make(Model $menu): Action
    {
        $instance = new static($menu);
        return $instance->getAction();
    }

    protected function getAction(): Action
    {
        return Action::make('addMenuItem')
            ->label('Додати пункт меню')
            ->icon('heroicon-o-plus')
            ->color('success')
            ->modal()
            ->modalHeading('Додати новий пункт меню')
            ->modalWidth('md')
            ->form([
                Tabs::make('menu_item_type')
                    ->tabs([
                        Tab::make('model')
                            ->label('З моделі')
                            ->schema([
                                Select::make('linkable_type')
                                    ->label(__('filament-menu-builder::menu-builder.form.linkable_type'))
                                    ->options($this->getAvailableModels())
                                    ->searchable()
                                    ->required(fn (Get $get) => empty($get('custom_title')) && empty($get('custom_url')))
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('linkable_id', null);
                                        // Очищуємо власні поля
                                        $set('custom_title', null);
                                        $set('custom_url', null);
                                        // Очищуємо дані моделі
                                        $set('model_title', null);
                                        $set('model_url', null);
                                    }),

                                Select::make('linkable_id')
                                    ->label(__('filament-menu-builder::menu-builder.form.linkable_id'))
                                    ->options(fn (Get $get) => $this->getModelRecords($get('linkable_type')))
                                    ->searchable()
                                    ->required(fn (Get $get) => filled($get('linkable_type')))
                                    ->visible(fn (Get $get) => filled($get('linkable_type')))
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        if ($state && $get('linkable_type')) {
                                            $this->fillModelData($state, $get('linkable_type'), $set);
                                        }
                                    }),

                                // Приховані поля для внутрішнього збереження даних з моделі
                                Hidden::make('model_title'),
                                Hidden::make('model_url'),
                            ]),

                        Tab::make('custom')
                            ->label('Власне посилання')
                            ->schema([
                                TextInput::make('custom_title')
                                    ->label('Назва пункту меню')
                                    ->required(fn (Get $get) => empty($get('linkable_type')) || empty($get('linkable_id')))
                                    ->maxLength(255)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Якщо користувач вводить власну назву, очищуємо поля моделі
                                        if (!empty($state)) {
                                            $set('linkable_type', null);
                                            $set('linkable_id', null);
                                            $set('model_title', null);
                                            $set('model_url', null);
                                        }
                                    }),

                                TextInput::make('custom_url')
                                    ->label('URL')
                                    ->required(fn (Get $get) => empty($get('linkable_type')) || empty($get('linkable_id')))
                                    ->placeholder('/about або https://example.com')
                                    ->helperText('Введіть відносний шлях (/about) або повний URL (https://example.com)')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Якщо користувач вводить власний URL, очищуємо поля моделі
                                        if (!empty($state)) {
                                            $set('linkable_type', null);
                                            $set('linkable_id', null);
                                            $set('model_title', null);
                                            $set('model_url', null);
                                        }
                                    }),
                            ]),
                    ]),

                Select::make('target')
                    ->label(__('filament-menu-builder::menu-builder.open_in.label'))
                    ->options(LinkTarget::class)
                    ->default(LinkTarget::Self),
            ])
            ->action(function (array $data): void {
                // Кастомна валідація
                $hasModel = !empty($data['linkable_type']) && !empty($data['linkable_id']);
                $hasCustom = !empty($data['custom_title']) && !empty($data['custom_url']);

                if (!$hasModel && !$hasCustom) {
                    Notification::make()
                        ->title('Помилка валідації')
                        ->body('Оберіть модель або заповніть власне посилання')
                        ->danger()
                        ->send();
                    return;
                }

                $this->createMenuItem($data);
            });
    }

    protected function fillModelData($recordId, $modelClass, callable $set): void
    {
        if (!$recordId || !$modelClass || !class_exists($modelClass)) {
            return;
        }

        try {
            $record = $modelClass::find($recordId);
            if (!$record) {
                return;
            }

            // Визначаємо поле для назви
            $titleField = $this->getTitleField($record);
            $title = $record->{$titleField} ?? "ID: {$record->id}";

            // Генеруємо URL
            $url = null;
            if (method_exists($record, 'getMenuUrl')) {
                $url = $record->getMenuUrl();
            } elseif (method_exists($record, 'getUrl')) {
                $url = $record->getUrl();
            }

            $set('model_title', $title);
            $set('model_url', $url);

        } catch (\Exception $e) {
            // Ігноруємо помилки
        }
    }

    protected function getTitleField($model): string
    {
        $fillable = $model->getFillable();

        if (in_array('title', $fillable)) {
            return 'title';
        } elseif (in_array('name', $fillable)) {
            return 'name';
        } elseif (in_array('label', $fillable)) {
            return 'label';
        }

        return 'id';
    }

    protected function getAvailableModels(): array
    {
        $models = [];

        // Пошук моделей з трейтом HasMenuPanel у папці app/Models
        $modelsPath = app_path('Models');

        if (File::exists($modelsPath)) {
            $files = File::allFiles($modelsPath);

            foreach ($files as $file) {
                $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();

                if (class_exists($className)) {
                    try {
                        $reflection = new ReflectionClass($className);

                        // Перевіряємо чи використовує модель трейт HasMenuPanel
                        $traits = $reflection->getTraitNames();
                        if (in_array('Datlechin\\FilamentMenuBuilder\\Concerns\\HasMenuPanel', $traits)) {
                            $models[$className] = class_basename($className);
                        }
                    } catch (\Exception $e) {
                        // Ігноруємо помилки з моделями
                        continue;
                    }
                }
            }
        }

        return $models;
    }

    protected function getModelRecords(?string $modelClass): array
    {
        if (!$modelClass || !class_exists($modelClass)) {
            return [];
        }

        try {
            $model = new $modelClass;
            $titleField = $this->getTitleField($model);

            return $modelClass::limit(100)
                ->pluck($titleField, 'id')
                ->toArray();

        } catch (\Exception $e) {
            return [];
        }
    }

    protected function createMenuItem(array $data): void
    {
        try {
            // Валідація даних
            $this->validateMenuItemData($data);

            $menuItemModel = FilamentMenuBuilderPlugin::get()->getMenuItemModel();

            // Знаходимо максимальний порядок для нового елементу
            $maxOrder = $menuItemModel::where('menu_id', $this->menu->id)
                ->whereNull('parent_id')
                ->max('order') ?? 0;

            // Визначаємо джерело даних - з моделі чи власне введення
            $isFromModel = !empty($data['linkable_type']) && !empty($data['linkable_id']);

            if ($isFromModel) {
                // Використовуємо дані з моделі
                $title = $data['model_title'] ?? 'Без назви';
                $url = $data['model_url'] ?? null;
                $linkableType = $data['linkable_type'];
                $linkableId = $data['linkable_id'];
            } else {
                // Використовуємо власні дані
                $title = $data['custom_title'] ?? 'Без назви';
                $url = $data['custom_url'] ?? null;
                $linkableType = null;
                $linkableId = null;
            }

            // Підготовуємо дані для створення
            $menuItemData = [
                'menu_id' => $this->menu->id,
                'title' => $title,
                'url' => $url,
                'linkable_type' => $linkableType,
                'linkable_id' => $linkableId,
                'target' => $data['target'] instanceof LinkTarget
                    ? $data['target']->value
                    : (string) ($data['target'] ?? LinkTarget::Self->value),
                'order' => $maxOrder + 1,
                'parent_id' => null,
            ];

            $menuItemModel::create($menuItemData);

            Notification::make()
                ->title('Пункт меню додано успішно')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Помилка при додаванні пункту меню')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function validateMenuItemData(array $data): void
    {
        $isFromModel = !empty($data['linkable_type']) && !empty($data['linkable_id']);

        // Конвертуємо target enum в строку для валідації
        if (isset($data['target']) && $data['target'] instanceof LinkTarget) {
            $data['target'] = $data['target']->value;
        }

        $rules = [
            'target' => ['required', 'string', Rule::in(array_map(fn($case) => $case->value, LinkTarget::cases()))],
        ];

        if ($isFromModel) {
            $rules['linkable_type'] = ['required', 'string'];
            $rules['linkable_id'] = ['required'];
            $rules['model_title'] = ['required', 'string'];
        } else {
            $rules['custom_title'] = ['required', 'string'];
            $rules['custom_url'] = ['required', 'string'];
        }

        validator($data, $rules)->validate();
    }
}
