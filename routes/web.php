<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('/dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
        ->name('dashboard')
        ->lazy();

    Volt::route('/brands', 'brands.index')->name('brands');
    Volt::route('/calls', 'calls.index')->name('calls');
    Volt::route('/appeasement-reasons', 'reasons.index')->name('appeasement-reasons');
    Volt::route('/appeasements', 'appeasements.index')->name('appeasements');
    Volt::route('/locations', 'locations.index')->name('locations');
    Volt::route('/dispositions', 'dispositions.index')->name('dispositions');
    Volt::route('/collections', 'collections.index')->name('collections');
    Volt::route('/tags', 'tags.index')->name('tags');
    Volt::route('/appeasement-reasons', 'reasons.index')->name('appeasement-reasons');
    Volt::route('/appeasements', 'appeasements.index')->name('appeasements');
    Volt::route('/locations', 'locations.index')->name('locations');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Volt::route('/agents', 'agents.index')->name('agents.index');
    Volt::route('/agents/{agent}', 'agents.view')->name('agents.view');

    Volt::route('/settings/holidays', 'settings.holidays')->name('settings.holidays');

    Route::group(['prefix' => 'reports'], function () {
        Volt::route('/', 'reports.index')->name('reports.index');
        Volt::route('/service-level', 'reports.service-level')->name('reports.service-level');
        Volt::route('/agent-data', 'reports.agent-data.index')->name('reports.agent-data');
        Volt::route('/agents-overview', 'reports.agents-overview')->name('reports.agents-overview')->lazy();
        Volt::route('/never-shipped', 'reports.never-shipped')->name('reports.never-shipped');
        Volt::route('/appeasements-breakdown', 'reports.appeasements-breakdown')->name('reports.appeasements-breakdown');
        Volt::route('/in-store-returns-unlocks', 'reports.in-store-returns-unlocks')->name('reports.in-store-returns-unlocks');
        Volt::route('/best-sellers', 'reports.best-sellers')->name('reports.best-sellers');
        Volt::route('/reasons-for-contact', 'reports.reasons-for-contact')->name('reports.reasons-for-contact');
        Volt::route('/appeasements-per-month', 'reports.appeasements-per-month')->name('reports.appeasements-per-month');
    });
});

require __DIR__.'/auth.php';
