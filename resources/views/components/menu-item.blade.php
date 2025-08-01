@props([
    'item',
])

@php
    /** @var \Datlechin\FilamentMenuBuilder\Models\MenuItem $item */

    $hasChildren = $item->children->isNotEmpty();
@endphp

<li
    wire:key="{{ $item->getKey() }}"
    data-sortable-item="{{ $item->getKey() }}"
    style="display: flex; flex-direction: column; gap: 20px"
    x-data="{ open: $persist(true).as('menu-item-' + {{ $item->getKey() }}) }"
>
    <div class="fi-section fi-header fi-align-justify fi-ta-cell">
        <div class="fi-header fi-align-justif">
            <div class="fi-ta-actions">
                {{ $this->reorderAction }}
                @if ($hasChildren)
                    <x-filament::icon-button
                        icon="heroicon-o-chevron-right"
                        x-on:click="open = !open"
                        x-bind:title="open ? '{{ trans('filament-menu-builder::menu-builder.items.collapse') }}' : '{{ trans('filament-menu-builder::menu-builder.items.expand') }}'"
                        color="gray"
                        class="transition duration-200 ease-in-out"
                        x-bind:class="{ 'rotate-90': open }"
                        size="sm"
                        style="display: flex;"
                    />
                @endif
                @if (\Datlechin\FilamentMenuBuilder\FilamentMenuBuilderPlugin::get()->isIndentActionsEnabled())
                    {{ ($this->unindentAction)(['id' => $item->getKey()]) }}
                    {{ ($this->indentAction)(['id' => $item->getKey()]) }}
                @endif
            </div>

            <div class="fi-section-header-heading">
                {{ $item->title }}
            </div>

            <div class="hidden overflow-hidden text-sm text-gray-500 sm:block dark:text-gray-400 whitespace-nowrap text-ellipsis">
                {{ $item->url }}
            </div>
        </div>
        <div class="fi-ta-actions">
            <x-filament::badge :color="$item->type === 'internal' ? 'primary' : 'gray'" class="hidden sm:block">
                {{ $item->type }}
            </x-filament::badge>

            @once
                <script type="text/javascript">
                    // Забезпечуємо наявність функції menuBuilder глобально
                    if (typeof window.menuBuilder === 'undefined') {
                        console.log('Creating menuBuilder function as fallback');
                        window.menuBuilder = function(config) {
                            return {
                                parentId: config.parentId,
                                sortable: null,
                                init: function() {
                                    console.log('MenuBuilder init called for parentId:', this.parentId);
                                    if (typeof Sortable !== 'undefined') {
                                        var self = this;
                                        var draggableSelector = '[data-sortable-item]';
                                        var handleSelector = '[data-sortable-handle]';
                                        var dataIdAttr = 'data-sortable-item';
                                        this.sortable = new Sortable(this.$el, {
                                            group: 'nested',
                                            draggable: draggableSelector,
                                            handle: handleSelector,
                                            animation: 300,
                                            ghostClass: 'fi-sortable-ghost',
                                            dataIdAttr: dataIdAttr,
                                            onSort: function() {
                                                console.log('Sortable onSort triggered');
                                                self.$wire.reorder(
                                                    self.sortable.toArray(),
                                                    self.parentId === 0 ? null : self.parentId
                                                );
                                            }
                                        });
                                    } else {
                                        console.error('Sortable library not found');
                                    }
                                }
                            };
                        };
                    } else {
                        console.log('menuBuilder function already exists');
                    }
                </script>
            @endonce
            {{ ($this->editAction)(['id' => $item->getKey(), 'title' => $item->title]) }}
            {{ ($this->deleteAction)(['id' => $item->getKey(), 'title' => $item->title]) }}
        </div>
    </div>

    <ul
        x-collapse
        x-show="open"
        wire:key="{{ $item->getKey() }}.children"
        x-data="menuBuilder({ parentId: {{ $item->getKey()  }} })"
        class="mt-2 space-y-2 ms-4"
        style="margin-left: 16px;"
    >
        @foreach ($item->children as $child)
            <x-filament-menu-builder::menu-item :item="$child" />
        @endforeach
    </ul>
</li>
