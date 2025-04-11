<x-layouts.app.sidebar :title="$title ?? null">
    
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>

@persist('toast')
<flux:toast position="top right"/>
@endpersist

@filepondScripts