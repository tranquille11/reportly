<?php

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Support\Carbon;

use function Livewire\Volt\rules;
use function Livewire\Volt\state;

state([
    'holidays' => fn () => Setting::where('key', 'holidays')->first()->value,
    'minDate' => fn () => Carbon::createFromDate(now()->year, 1, 1)->format('Y-m-d'),
    'maxDate' => fn () => Carbon::createFromDate(now()->year, 12, 31)->format('Y-m-d'),
]);

rules([
    'holidays' => 'required',
    'holidays.*.date' => 'required|date'
]);

$save = function () {
    Setting::where('key', 'holidays')->update(['value' => $this->holidays]);

    Flux::toast(variant: 'success', text: 'Holidays have been saved.');
};
?>

<section class="w-full">

    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Settings') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your app settings') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <x-settings.layout-app-settings :heading="__('Customer Service Holidays')" :subheading="__('Update the days off for call center')">
                
        @foreach($holidays as $key => $holiday)
        <flux:date-picker 
            wire:model="holidays.{{ $key }}.date"
            min="{{ $minDate }}"
            max="{{ $maxDate }}"
            label="{{ $holiday['name'] }}"
            locale="en-US"
        />
        @endforeach

        <flux:button wire:click="save" variant="primary">{{ __('Save') }}</flux:button>

    </x-settings.layout-app-settings>
</section>
