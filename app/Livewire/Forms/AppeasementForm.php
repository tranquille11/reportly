<?php

namespace App\Livewire\Forms;

use App\Enums\AppeasementStatus;
use App\Enums\TagType;
use App\Models\Appeasement;
use App\Models\AppeasementReason;
use App\Models\Location;
use App\Services\AppeasementService;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Tags\Tag;

class AppeasementForm extends Form
{
    public ?Appeasement $appeasement;

    #[Validate('required')]
    public $order_number;

    #[Validate('required')]
    public $note;

    public function setAppeasement(Appeasement $appeasement)
    {
        $this->appeasement = $appeasement;
        $this->order_number = $appeasement->order_number;
        $this->note = $appeasement->note;
    }

    public function update()
    {
        $this->validate(attributes: ['order_number', 'note']);

        $this->appeasement->order_number = $this->order_number;
        $this->appeasement->note = strtoupper($this->note);

        $service = new AppeasementService(
            appeasement: $this->appeasement,
            reasons: AppeasementReason::all(),
            locations: Location::all(),
            sizes: Tag::getWithType(TagType::PRODUCT_SIZE->value)->pluck('name')->toArray(),
            colors: Tag::getWithType(TagType::PRODUCT_COLOR->value)->pluck('name')->toArray(),
            delimiters: Tag::getWithType(TagType::PRODUCT_DELIMITER->value)->pluck('name')->toArray(),
        );

        $appeasement = $service->process()->appeasement();
        $appeasement->save();

        Flux::modal('edit-appeasement')->close();

        if ($appeasement->status === AppeasementStatus::FAILED) {
            Flux::toast(heading: 'Appeasement has failed processing.', text: $appeasement->status_message, variant: 'danger');
        } else {
            Flux::toast('Appeasement was successfully processed.', variant: 'success');
        }
    }
}
