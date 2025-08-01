@php
    $plugin = \Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin::get();
    $record = $this->record;
@endphp

<div class="fi-sc fi-sc-has-gap fi-grid lg:fi-grid-cols" style="margin-bottom: 30px; --cols-lg: repeat(3, minmax(0, 1fr)); --cols-default: repeat(1, minmax(0, 1fr));" wire:ignore>
    <div class="fi-grid fi-grid-col fi-sc-has-gap lg:fi-grid-col-span" style="gap:20px;--col-span-default: span 1 / span 1; --col-span-lg: span 1 / span 1;">
        @foreach ($plugin->getMenuPanels() as $menuPanel)
            <livewire:menu-builder-panel :menu="$record" :menuPanel="$menuPanel" />
        @endforeach

        @if ($plugin->isShowCustomLinkPanel())
            <livewire:create-custom-link :menu="$record" />
        @endif

        @if ($plugin->isShowCustomTextPanel())
            <livewire:create-custom-text :menu="$record" />
        @endif
    </div>
    <div class="fi-grid-col lg:fi-grid-col-span" style="--col-span-default: span 1 / span 1; --col-span-lg: span 2 / span 2;">
        <x-filament::section>
            <livewire:menu-builder-items :menu="$record" />
        </x-filament::section>
    </div>
</div>
