<?php

namespace App\Livewire\Forms;

use App\Models\Agent;
use App\Rules\AgentsRoleRule;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;

class AgentForm extends Form
{
    public ?Agent $agent;

    #[Validate('required|min:3')]
    public string $name = '';

    #[Validate('required|min:3')]
    public string $stage_name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate(['required', new AgentsRoleRule])]
    public string $role = '';

    #[Validate([
        'settings' => 'required',
        'settings.gorgias_user_id' => 'numeric|nullable|min:1',
    ])]
    public array $settings = ['gorgias_user_id' => null];

    public function setAgent(Agent $agent): void
    {
        $this->agent = $agent;

        $this->name = $agent->name;
        $this->stage_name = $agent->stage_name;
        $this->email = $agent->email;
        $this->role = $agent->role->value;
        $this->settings = $agent->settings;
    }

    public function create()
    {
        $this->validate();

        $agent = Agent::create($this->except(['agent']));

        Flux::toast("Agent {$agent->name} has been created.", variant: 'success');
        Flux::modal('create-agent')->close();

        $this->reset();

        return $agent;
    }

    public function save()
    {
        $this->validate();

        $this->agent->update($this->except(['agent']));

        Flux::toast('Agent has been successfully edited.', variant: 'success');

    }
}
