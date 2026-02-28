<?php

namespace App\Enums;

enum ExamType: string
{
    case Written = 'written';
    case Oral = 'oral';
    case Portfolio = 'portfolio';

    public function getLabel(): string
    {
        return match ($this) {
            self::Written => __('Written Exam'),
            self::Oral => __('Oral Exam'),
            self::Portfolio => __('Portfolio Assessment'),
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Written => 'heroicon-o-pencil-square',
            self::Oral => 'heroicon-o-chat-bubble-left-right',
            self::Portfolio => 'heroicon-o-folder-open',
        };
    }

    public function getShortLabel(): string
    {
        return match ($this) {
            self::Written => __('Written'),
            self::Oral => __('Oral'),
            self::Portfolio => __('Portfolio'),
        };
    }

    public static function fromGermanType(string $germanType): self
    {
        // Handle combined types (e.g., "Kolloquium#Praktische Studienleistung")
        // Take the first type if multiple exist
        $firstType = explode('#', $germanType)[0];
        $trimmedType = trim($firstType);

        $translatedType = __($trimmedType);

        return match ($translatedType) {
            'Written Exam', 'Schriftliche Prüfung' => self::Written,
            'Oral Exam', 'Mündliche Prüfung', 'Kolloquium' => self::Oral,
            'Portfolio Assessment', 'Portfolio', 'Praktische Studienleistung' => self::Portfolio,
            default => self::Written, // Default fallback
        };
    }
}
