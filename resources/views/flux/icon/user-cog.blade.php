{{-- Credit: Lucide (https://lucide.dev) --}}

@props([
    'variant' => 'outline',
])

@php
if ($variant === 'solid') {
    throw new \Exception('The "solid" variant is not supported in Lucide.');
}

$classes = Flux::classes('shrink-0')
    ->add(match($variant) {
        'outline' => '[:where(&)]:size-6',
        'solid' => '[:where(&)]:size-6',
        'mini' => '[:where(&)]:size-5',
        'micro' => '[:where(&)]:size-4',
    });

$strokeWidth = match ($variant) {
    'outline' => 2,
    'mini' => 2.25,
    'micro' => 2.5,
};
@endphp

<svg
    {{ $attributes->class($classes) }}
    data-flux-icon
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    data-slot="icon"
>
  <circle cx="18" cy="15" r="3" />
  <circle cx="9" cy="7" r="4" />
  <path d="M10 15H6a4 4 0 0 0-4 4v2" />
  <path d="m21.7 16.4-.9-.3" />
  <path d="m15.2 13.9-.9-.3" />
  <path d="m16.6 18.7.3-.9" />
  <path d="m19.1 12.2.3-.9" />
  <path d="m19.6 18.7-.4-1" />
  <path d="m16.8 12.3-.4-1" />
  <path d="m14.3 16.6 1-.4" />
  <path d="m20.7 13.8 1-.4" />
</svg>
