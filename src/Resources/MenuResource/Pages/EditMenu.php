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
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\View as SchemaView;

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
                        'menuPanels' => FilamentMenuBuilderPlugin::get()->getMenuPanels(),
                        'showCustomLinkPanel' => FilamentMenuBuilderPlugin::get()->isShowCustomLinkPanel(),
                        'showCustomTextPanel' => FilamentMenuBuilderPlugin::get()->isShowCustomTextPanel(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            $this->getLocationAction(),
        ];
    }
}
