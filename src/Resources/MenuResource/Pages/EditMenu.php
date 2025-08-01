<?php

declare(strict_types=1);

namespace Datlechin\FilamentMenuBuilder\Resources\MenuResource\Pages;

use Datlechin\FilamentMenuBuilder\Concerns\HasLocationAction;
use Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\View as SchemaView;
use Datlechin\FilamentMenuBuilder\Actions\AddMenuItemAction;

class EditMenu extends EditRecord
{
    use HasLocationAction;

    public static function getResource(): string
    {
        return FilamentMenuBuilderPlugin::get()->getResource();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament-menu-builder::menu-builder.resource.name.label'))
                            ->required(),

                        Toggle::make('is_visible')
                            ->label(__('filament-menu-builder::menu-builder.resource.is_visible.label'))
                            ->default(true),
                    ]),

                // Menu Management Section
                SchemaView::make('filament-menu-builder::menu-management')
                    ->viewData([
                        'record' => $this->record,
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            AddMenuItemAction::make($this->record),
            DeleteAction::make(),
            $this->getLocationAction(),
        ];
    }
}
