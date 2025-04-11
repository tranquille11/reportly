<?php

namespace App\Enums;

enum ImportHistoryStatus: string
{
    case COMPLETED = 'completed';
    case PROCESSING = 'processing';
    case FAILED = 'failed';

    public function color()
    {
        return match ($this) {
            ImportHistoryStatus::COMPLETED => 'green',
            ImportHistoryStatus::PROCESSING => 'yellow',
            ImportHistoryStatus::FAILED => 'red',
        };
    }
}
