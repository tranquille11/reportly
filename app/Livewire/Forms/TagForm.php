<?php

namespace App\Livewire\Forms;

use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Tags\Tag;

class TagForm extends Form
{
    #[Validate('required')]
    public $name;

    public $type;

    public function create()
    {
        $this->validate();

        $tag = Tag::findOrCreate($this->name, $this->type);

        Flux::modals()->close();
        Flux::toast("Tag [{$tag->name}] has been created.", variant: 'success');

        $this->reset();
    }
}
