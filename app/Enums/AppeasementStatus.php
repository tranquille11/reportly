<?php

namespace App\Enums;

enum AppeasementStatus: string
{
    case PROCESSED = 'processed';
    case FAILED = 'failed';

    public function color()
    {
        return match ($this) {
            AppeasementStatus::PROCESSED => 'green',
            AppeasementStatus::FAILED => 'red',
        };
    }
}
