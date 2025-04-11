<?php

namespace App\Enums;

enum TagType: string
{
    case TALKDESK = 'talkdesk';
    case GORGIAS_CHANNEL = 'gorgias-channel';
    case GORGIAS_BRAND = 'gorgias-brand';
    case GORGIAS_REASON = 'gorgias-reason';
    case PRODUCT_SIZE = 'product-size';
    case PRODUCT_COLOR = 'product-color';
    case PRODUCT_DELIMITER = 'product-delimiter';

    public function color()
    {
        return match ($this) {
            TagType::TALKDESK => 'purple',
            TagType::GORGIAS_CHANNEL, TagType::GORGIAS_BRAND, TagType::GORGIAS_REASON => 'blue',
            TagType::PRODUCT_SIZE, TagType::PRODUCT_COLOR, TagType::PRODUCT_DELIMITER => 'green',
        };
    }

    public function classes()
    {
        return match ($this) {
            TagType::TALKDESK => 'border border-purple-200 text-purple-700 !bg-zinc-800',
            TagType::GORGIAS_CHANNEL, TagType::GORGIAS_BRAND, TagType::GORGIAS_REASON => 'border border-blue-200 text-blue-700 !bg-zinc-800',
            TagType::PRODUCT_SIZE, TagType::PRODUCT_COLOR, TagType::PRODUCT_DELIMITER => 'border border-green-200 text-green-700 !bg-zinc-800',
        };
    }
}
