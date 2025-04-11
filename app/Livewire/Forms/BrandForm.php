<?php

namespace App\Livewire\Forms;

use App\Enums\TagType;
use App\Models\Brand;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;

class BrandForm extends Form
{
    public ?Brand $brand;

    #[Validate('required|min:3')]
    public string $name = '';

    #[Validate('required')]
    public string $shorthand = '';

    #[Validate('required')]
    public array $tags = [];

    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;
        $this->name = $brand->name;
        $this->shorthand = $brand->shorthand;
        $this->tags = $brand->tags->pluck('name')->toArray();
    }

    public function create()
    {
        $this->validate(attributes: ['name', 'shorthand', 'tags']);

        $brand = Brand::create($this->only(['name', 'shorthand']));
        $brand->syncTagsWithType($this->tags, TagType::TALKDESK->value);

        $this->reset();

        Flux::modals()->close();
        Flux::toast('Brand has been created', variant: 'success');
    }

    public function save()
    {
        $this->validate(attributes: ['name', 'shorthand', 'tags']);

        $this->brand->update($this->only(['name', 'shorthand']));
        $this->brand->syncTagsWithType($this->tags, TagType::TALKDESK->value);

        $this->reset();

        Flux::modals()->close();
        Flux::toast('Brand has been saved', variant: 'success');
    }

    public function delete()
    {
        $this->brand->delete();

        Flux::modal('delete-brand')->close();
        Flux::toast("Brand [{$this->brand->name}] has been deleted.", variant: 'success');

        $this->reset();
    }

    public function restore()
    {
        $this->brand->restore();

        Flux::modal('enable-brand')->close();
        Flux::toast("Brand [{$this->brand->name}] has been restored.", variant: 'success');

        $this->reset();
    }
}
