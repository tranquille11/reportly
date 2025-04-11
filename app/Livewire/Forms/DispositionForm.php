<?php

namespace App\Livewire\Forms;

use App\Enums\TagType;
use App\Models\Disposition;
use Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DispositionForm extends Form
{
    public ?Disposition $disposition;

    #[Validate('required|min:3')]
    public string $name = '';

    #[Validate('required')]
    public array $tags = [];

    public function setDisposition(Disposition $disposition)
    {
        $this->disposition = $disposition;
        $this->name = $disposition->name;

        $this->tags = $disposition->tags->pluck('name')->toArray();
    }

    public function create()
    {
        $this->validate(attributes: ['name', 'tags']);
        $disposition = Disposition::create($this->only('name'));

        $disposition->syncTagsWithType($this->tags, TagType::GORGIAS_REASON->value);

        Flux::toast("Disposition [{$disposition->name}] was created.", variant: 'success');
        Flux::modal('create-disposition')->close();

        $this->reset();
    }

    public function save()
    {
        $this->validate(attributes: ['name', 'tags']);

        $this->disposition->update($this->only('name'));
        $this->disposition->syncTagsWithType($this->tags, TagType::GORGIAS_REASON->value);

        Flux::toast("Disposition [{$this->disposition->name}] was edited.", variant: 'success');
        Flux::modal('edit-disposition')->close();

        $this->reset();
    }
}
