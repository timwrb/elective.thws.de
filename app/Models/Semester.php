<?php

namespace App\Models;

use App\Enums\Season;
use Database\Factories\SemesterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $year
 * @property Season $season
 */
class Semester extends Model
{
    /** @use HasFactory<SemesterFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'season' => Season::class,
        ];
    }

    public function getLabel(): string
    {
        $yearShort = substr((string) $this->year, -2);

        if ($this->season === Season::Winter) {
            $nextYearShort = substr((string) ($this->year + 1), -2);

            return "{$this->season->value}$yearShort/{$nextYearShort}";
        }

        return "{$this->season->value}$yearShort";
    }
}
