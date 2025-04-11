<?php

namespace App\Rules;

use App\Models\Brand;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AppeasementFileNameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $brands = Brand::all();

        foreach ($value as $file) {
            $name = strtolower($file->getClientOriginalName());
            $brand = $brands->filter(function ($brand) use ($name) {
                return str_starts_with($name, strtolower($brand->shorthand));
            })->first();

            if (! $brand) {
                $fail("File [$name] does not contain valid brand.");
            }
        }
    }
}
