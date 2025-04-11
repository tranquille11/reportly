<?php

namespace App\Enums;

enum ImportHistoryType: string
{
    case CALLS = 'calls';
    case APPEASEMENTS = 'appeasements';
    case AGENTS = 'agents';
    case REASONS = 'reasons';
    case DISPOSITIONS = 'dispositions';
    case SHIFTS = 'shifts';
    case TAGS = 'tags';

}
