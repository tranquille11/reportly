<?php

namespace App\Enums;

enum AgentRole: string
{
    case REPRESENTATIVE = 'representative';
    case SOCIAL_MEDIA = 'social media';
    case RESEARCHER = 'researcher';
    case SUPERVSIOR = 'supervisor';
    case MANAGER = 'manager';

    public function color()
    {
        return match ($this) {
            AgentRole::REPRESENTATIVE, AgentRole::SOCIAL_MEDIA => 'orange',
            AgentRole::RESEARCHER => 'green',
            AgentRole::SUPERVSIOR => 'red',
            AgentRole::MANAGER => 'zinc',
        };
    }
}
