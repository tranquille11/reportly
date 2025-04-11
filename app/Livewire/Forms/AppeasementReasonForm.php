<?php

namespace App\Livewire\Forms;

use App\Models\AppeasementReason;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AppeasementReasonForm extends Form
{
    public ?AppeasementReason $reason;

    #[Validate('required|min:3')]
    public $name;

    #[Validate('required')]
    public $shorthand;

    #[Validate('required')]
    public $has_percentage = false;

    #[Validate('required')]
    public $has_location = false;

    #[Validate('required')]
    public $has_product = false;

    #[Validate('required')]
    public $has_size = false;

    public function setReason(AppeasementReason $reason)
    {
        $this->reason = $reason;
        $this->name = $reason->name;
        $this->shorthand = $reason->shorthand;
        $this->has_percentage = $reason->has_percentage;
        $this->has_product = $reason->has_product;
        $this->has_location = $reason->has_location;
        $this->has_size = $reason->has_size;
    }

    public function create()
    {
        $this->validate();

        AppeasementReason::create($this->except('reason'));

        Flux::modals()->close();
        Flux::toast(text: 'Appeasement reason has been created.', variant: 'success');

        $this->reset();
    }

    public function update()
    {
        $this->validate();

        $this->reason->update($this->except('reason'));

        Flux::modals()->close();
        Flux::toast('Appeasement reason has been edited.', variant: 'success');

    }

    public function delete()
    {
        $this->reason->delete();

        Flux::modals()->close();
        Flux::toast('Appeasement reason has been deleted.', variant: 'success');

    }

    public function resetAll()
    {
        $this->reset(['reason', 'name', 'shorthand', 'has_percentage', 'has_location', 'has_product', 'has_size']);
    }
}
