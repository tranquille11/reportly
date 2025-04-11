<?php

namespace App\Livewire\Forms;

use App\Models\Collection;
use Flux;
use Illuminate\Database\UniqueConstraintViolationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CollectionForm extends Form
{
    public ?Collection $collection;

    #[Validate('required|min:3')]
    public ?string $name = '';

    #[Validate('required|integer')]
    public ?int $minimum_quantity;

    #[Validate('required|integer')]
    public ?int $threshold;

    #[Validate('required')]
    public ?array $categories = [];

    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
        $this->name = $collection->name;
        $this->minimum_quantity = $collection->minimum_quantity;
        $this->threshold = $collection->threshold;

        $this->categories = $collection->categories->pluck('id')->toArray();
    }

    public function create()
    {
        $this->validate(attributes: ['name', 'minimum_quantity', 'threshold', 'categories']);

        try {
            $collection = Collection::create($this->only(['name', 'minimum_quantity', 'threshold']));
        } catch (UniqueConstraintViolationException) {
            Flux::toast('Collection name already exists.', variant: 'danger');

            return;
        }

        $collection->categories()->sync($this->categories);

        $this->reset();

        Flux::toast("Collection [{$collection->name}] was created.", variant: 'success');
        Flux::modal('create-collection')->close();
    }

    public function save()
    {
        $this->validate();

        try {
            $this->collection->update($this->only(['name', 'minimum_quantity', 'threshold']));
        } catch (UniqueConstraintViolationException) {
            Flux::toast('Collection name already exists.', variant: 'danger');

            return;
        }

        $this->collection->categories()->sync($this->categories);

        Flux::toast("Collection [{$this->collection->name}] was edited.", variant: 'success');
        Flux::modal('edit-collection')->close();
    }

    public function delete()
    {
        $this->collection->delete();

        Flux::modal('delete-collection')->close();
        Flux::toast("Collection [{$this->collection->name}] has been deleted.", variant: 'success');

        $this->reset();
    }
}
