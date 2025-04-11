<?php

namespace App\Services;

use App\Enums\AppeasementStatus;
use App\Models\Appeasement;
use App\Models\AppeasementReason;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AppeasementService
{
    protected Location $location;

    protected AppeasementReason $reason;

    protected array $noteParts = [];

    public function __construct(
        protected Appeasement $appeasement,
        protected Collection $reasons,
        protected Collection $locations,
        protected $sizes,
        protected $colors,
        protected $delimiters,
    ) {}

    public function process()
    {
        if (! $this->determineReason()) {
            $this->appeasement->status = AppeasementStatus::FAILED;

            return $this;
        }

        if ($this->reason->has_location) {
            if (! $this->determineLocation()) {
                $this->appeasement->status = AppeasementStatus::FAILED;

                return $this;
            }
        }

        if ($this->reason->has_product) {
            if (! $this->determineProducts()) {
                $this->appeasement->status = AppeasementStatus::FAILED;

                return $this;
            }
        }

        $this->appeasement->status = AppeasementStatus::PROCESSED;
        $this->appeasement->status_message = null;

        return $this;
    }

    public function appeasement()
    {
        return $this->appeasement;
    }

    protected function determineReason(): bool
    {
        foreach (explode('-', str_replace('–', '-', $this->appeasement->note)) as $value) {
            $this->noteParts[] = trim($value);
        }

        $reason = $this->reasons->first(fn ($reason) => (
            $this->noteParts[0] === $reason->shorthand) ||
            $reason->shorthand == 'RECURATE' && str_starts_with($this->appeasement->note, 'RECURATE')
        );

        if (! $reason) {
            $this->appeasement->status_message = 'Reason does not exist';

            return false;
        }

        $this->reason = $reason;
        $this->appeasement->reason_id = $reason->id;

        return true;
    }

    protected function determineLocation(): bool
    {
        if (count($this->noteParts) == 1) {
            $this->appeasement->status_message = 'Product and/or location are missing.';

            return false;
        }

        $location = $this->locations->first(fn ($location) => $this->noteParts[1] == $location->number);

        if (! $location) {
            $this->appeasement->status_message = 'Location does not exist.';

            return false;
        }

        $location = $location->parent ?? $location;
        $this->appeasement->location_id = $location->id;

        return true;
    }

    protected function determineProducts(): bool
    {
        if (count($this->noteParts) < 3) {
            $this->appeasement->status_message = 'Percentage and/or product/location are missing.';

            return false;
        }

        $count = count($this->noteParts);

        if ($count > 3) {
            for ($i = 3; $i < $count; $i++) {
                $this->noteParts[2] .= '-'.$this->noteParts[$i];
                unset($this->noteParts[$i]);
            }
        }

        foreach ($this->delimiters as $delimiter) {

            $products = explode($delimiter, $this->noteParts[2]);

            if (count($products) > 1) {
                break;
            }
        }

        if (count($products) == 1) {
            $dashes = Str::substrCount($this->noteParts[2], '-');

            if ($dashes > 0) {
                $products = explode('-', $this->noteParts[2]);
                $countProducts = count($products);
                $i = 0;

                while ($i < $countProducts) {
                    if (! isset($products[$i + 1])) {
                        break;
                    }
                    if (strlen($products[$i + 1]) <= 6) {
                        if (! isset($products[$i + 1])) {
                            break;
                        }
                        $products[$i] .= ' '.$products[$i + 1];

                        unset($products[$i + 1]);
                        $i = $i + 2;

                        continue;
                    }

                    $i = $i + 1;
                }
            }

        }

        foreach ($products as $key => &$product) {
            if (count($products) > 1) {

                if (
                    isset($products[$key + 1]) &&
                    Str::endsWith($product, $this->colors) &&
                    Str::startsWith($products[$key + 1], $this->colors)
                ) {
                    $product = $product.'/'.$products[$key + 1];
                    unset($products[$key + 1]);
                }
            }

            $product = trim($product);
        }

        foreach ($products as $p) {
            $string = str('/');

            foreach ($this->sizes as $size) {

                $string = $string->append("$size+$|");
            }

            $string = $string->replaceLast('|', '')->append('/')->toString();

            $comparison = preg_match($string, $p);

            if (! $comparison && $this->reason->has_size) {
                $this->appeasement->status_message = "Reason [{$this->reason->name}] should have product with size.";

                return false;
            }

            if ($comparison && ! $this->reason->has_size) {
                $this->appeasement->status_message = "Reason [{$this->reason->name}] should have product without size.";

                return false;
            }
        }

        $this->appeasement->products = $products;

        return true;
    }
}
