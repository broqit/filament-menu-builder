<x-filament-panels::page @class([
    'fi-resource-edit-record-page',
    'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    'fi-resource-record-' . $record->getKey(),
])>
    @capture($form)
    <x-filament-schemas::form id="form" :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
                              wire:submit="save">
        {{ $this->form }}

        <x-filament-schemas::actions :actions="$this->getCachedFormActions()" :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-schemas::form>
    @endcapture

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if (!$hasCombinedRelationManagerTabsWithContent || !count($relationManagers))
        {{ $form() }}
    @endif

    @if (count($relationManagers))
        <x-filament-menu-builder::relation-managers :active-locale="isset($activeLocale) ? $activeLocale : null" :active-manager="$this->activeRelationManager ??
            ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))" :content-tab-label="$this->getContentTabLabel()"
                                                    :content-tab-icon="$this->getContentTabIcon()" :content-tab-position="$this->getContentTabPosition()" :managers="$relationManagers" :owner-record="$record" :page-class="static::class">
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    {{ $form() }}
                </x-slot>
            @endif
        </x-filament-menu-builder::relation-managers>
    @endif

    <div class="grid grid-cols-12 gap-4" wire:ignore>
        <div class="flex flex-col col-span-12 gap-4 sm:col-span-4">
            @foreach (\Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin::get()->getMenuPanels() as $menuPanel)
                <livewire:menu-builder-panel :menu="$record" :menuPanel="$menuPanel" />
            @endforeach

            @if (\Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin::get()->isShowCustomLinkPanel())
                <livewire:create-custom-link :menu="$record" />
            @endif

            @if (\Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin::get()->isShowCustomTextPanel())
                <livewire:create-custom-text :menu="$record" />
            @endif
        </div>
        <div class="col-span-12 sm:col-span-8">
            <x-filament::section>
                <livewire:menu-builder-items :menu="$record" />
            </x-filament::section>
        </div>
    </div>

    <x-filament-panels::unsaved-action-changes-alert />
</x-filament-panels::page>
