<x-filament-panels::page>

    <form wire:submit="save">

        {{ $this->settingsForm }}

        <div style="margin-top:16px">
            <x-filament::button type="submit" color="warning" icon="heroicon-o-check" size="lg">
                Save All Settings
            </x-filament::button>
        </div>

    </form>

    <x-filament-actions::modals />

</x-filament-panels::page>
