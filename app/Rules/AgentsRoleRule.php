<?php

namespace App\Rules;

use App\Enums\AgentRole;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AgentsRoleRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $roles = [];

        foreach (AgentRole::cases() as $case) {
            $roles[] = $case->value;
        }
        if (! in_array($value, $roles)) {
            $fail("{$value} is not a valid role.");
        }
    }
}
