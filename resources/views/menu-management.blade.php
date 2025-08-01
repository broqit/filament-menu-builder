@php
    $record = $this->record;
@endphp

<x-filament::section>
    <livewire:menu-builder-items :menu="$record" />
</x-filament::section>
