<?php

namespace App\Livewire\Forms;

use App\Models\Location;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LocationForm extends Form
{
    public ?Location $location;

    #[Validate('required|min:3')]
    public $name;

    #[Validate('required|numeric')]
    public $number;

    #[Validate('required')]
    public $type = 'store';

    public $parent_id = null;

    public function setLocation(Location $location)
    {
        $this->location = $location;
        $this->name = $location->name;
        $this->number = $location->number;
        $this->type = $location->type;
        $this->parent_id = $location->parent_id;
    }

    public function create()
    {
        $this->validate();

        Location::create($this->except('location'));

        Flux::modals()->close();
        Flux::toast('Location has been created.', variant: 'success');

    }

    public function update()
    {
        $this->validate();

        $this->location->update($this->except('location'));

        Flux::modals()->close();
        Flux::toast('Location has been edited.', variant: 'success');
    }

    public function delete()
    {
        $this->location->delete();

        Flux::modals()->close();
        Flux::toast('Location has been deleted.');
    }
}
