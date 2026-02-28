<?php

namespace App\Models;

use App\Enums\DegreeField;
use App\Enums\DegreeLevel;
use Database\Factories\StudyProgramFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name_german
 * @property string $name_english
 * @property DegreeLevel $degree_level
 * @property DegreeField $degree_field
 * @property bool $is_dual
 * @property int|null $base_program_id
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class StudyProgram extends Model
{
    /** @use HasFactory<StudyProgramFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'degree_level' => DegreeLevel::class,
            'degree_field' => DegreeField::class,
            'is_dual' => 'boolean',
            'active' => 'boolean',
        ];
    }

    /** @return BelongsTo<StudyProgram, $this> */
    public function baseProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'base_program_id');
    }

    /** @return HasMany<StudyProgram, $this> */
    public function dualVariants(): HasMany
    {
        return $this->hasMany(StudyProgram::class, 'base_program_id');
    }

    /** @return BelongsToMany<Fwpm, $this> */
    public function fwpms(): BelongsToMany
    {
        return $this->belongsToMany(Fwpm::class, 'fwpm_study_program')
            ->withPivot('approval_status')
            ->withTimestamps();
    }
}
