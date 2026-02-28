<?php

namespace App\Enums;

enum ElectiveStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Published => __('Published'),
            self::Closed => __('Closed'),
            self::Archived => __('Archived'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Closed => 'warning',
            self::Archived => 'info',
        };
    }
}
