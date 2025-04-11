<?php

use Spatie\Tags\Tag;

use function Livewire\Volt\{computed, state, usesPagination};

usesPagination();

state(['search' => ''])->url(as: 'q', except: '');

$tags = computed(fn () => Tag::search(['name'], $this->search)->latest()->paginate(20));

?>

<div class="space-y-4">
    <div class="flex items-center w-full gap-2 mb-6">

        <flux:heading size="xl" class="!font-extrabold"> Tags </flux:heading>

        <flux:spacer/>

        <flux:modal.trigger name="bulk-management">
            <flux:button icon="inbox-stack" variant="ghost" class="font-extrabold" size="sm">Bulk management</flux:button>
        </flux:modal.trigger>

        <flux:modal.trigger name="import-tags">
            <flux:button icon="arrow-up-tray" variant="filled" class="!font-extrabold" size="sm">Bulk import</flux:button>
        </flux:modal.trigger>

        <flux:separator vertical />

        <flux:modal.trigger name="create-tag">
            <flux:button variant="primary" size="sm">Create tag</flux:button>
        </flux:modal.trigger>

    </div>

    <flux:card class="py-3">
        <flux:text> {{ $this->tags->count() }} tags</flux:text>
    </flux:card>

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-4">Name</flux:table.column>
            <flux:table.column class="!px-4">Type</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->tags as $tag)
                <flux:table.row wire:key="tag-{{ $tag->id }}" class="hover:bg-zinc-700">
                    <flux:table.cell class="!px-4">
                        <flux:badge 
                            size="sm" 
                            color="{{ App\Enums\TagType::tryFrom($tag->type)?->color() }}"
                            class="{{ App\Enums\TagType::tryFrom($tag->type)?->classes() }}"> {{$tag->name}} </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="!px-4 text-sm">
                        {{Str::of($tag->type)->headline()->snake()}}
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$this->tags" class="border-none py-4 px-6"/>


    <livewire:tags.create @created="$refresh" />

    <livewire:tags.import @imported="$refresh" />

    <livewire:modals.bulk-management-modal historyType="tags" :key="'history-' . rand(1, 1000)" />

</div>