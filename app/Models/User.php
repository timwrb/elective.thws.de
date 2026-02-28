<?php

namespace App\Models;

use App\Services\SemesterService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $id
 * @property string|null $salutation
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property int|null $start_semester_id
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $full_name
 */
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;
    use InteractsWithMedia;
    use TwoFactorAuthenticatable;

    /** @var list<string> */
    protected $fillable = [
        'salutation',
        'name',
        'surname',
        'email',
        'start_semester_id',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn () => "$this->salutation $this->name $this->surname");
    }

    /** @return HasMany<UserSelection, $this> */
    public function selections(): HasMany
    {
        return $this->hasMany(UserSelection::class);
    }

    /** @return Collection<int, UserSelection> */
    public function selectionsForSemester(Semester $semester): Collection
    {
        return $this->selections()
            ->forSemester($semester)
            ->with('elective')
            ->get();
    }

    /** @return MorphToMany<Awpf, $this> */
    public function awpfSelections(): MorphToMany
    {
        return $this->morphedByMany(
            Awpf::class,
            'elective',
            'user_selections',
            'user_id',
            'elective_choice_id'
        )
            ->withPivot(['semester_id', 'parent_elective_choice_id', 'status', 'enrollment_type', 'id'])
            ->withTimestamps();
    }

    /** @return MorphToMany<Fwpm, $this> */
    public function fwpmSelections(): MorphToMany
    {
        return $this->morphedByMany(
            Fwpm::class,
            'elective',
            'user_selections',
            'user_id',
            'elective_choice_id'
        )
            ->withPivot(['semester_id', 'parent_elective_choice_id', 'status', 'enrollment_type', 'id'])
            ->withTimestamps();
    }

    /** @return MorphToMany<ResearchProject, $this> */
    public function researchProjectSelections(): MorphToMany
    {
        return $this->morphedByMany(
            ResearchProject::class,
            'elective',
            'user_selections',
            'user_id',
            'elective_choice_id'
        )
            ->withPivot(['semester_id', 'status', 'enrollment_type', 'id'])
            ->withTimestamps();
    }

    /** @return HasMany<ResearchProject, $this> */
    public function createdResearchProjects(): HasMany
    {
        return $this->hasMany(ResearchProject::class, 'creator_id');
    }

    /** @return BelongsTo<Semester, $this> */
    public function startSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'start_semester_id');
    }

    /**
     * Get the current active semester based on today's date.
     */
    public function getCurrentSemester(): ?Semester
    {
        return resolve(SemesterService::class)->getCurrentSemester();
    }

    /**
     * Calculate which semester number this user is in based on their start semester.
     *
     * Example: If user started in WS23/24 and current semester is WS25/26, returns 5.
     */
    public function getSemesterNumber(): ?int
    {
        return resolve(SemesterService::class)->calculateSemesterNumber($this);
    }

    public function canEnrollInResearchProject(ResearchProject $project, Semester $semester): bool
    {
        $existingEnrollment = $this->researchProjectSelections()
            ->wherePivot('semester_id', $semester->id)
            ->wherePivot('elective_choice_id', $project->id)
            ->wherePivotIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existingEnrollment) {
            return false;
        }

        return $project->hasCapacity($semester);
    }

    public function getAvatarUrl(): ?string
    {
        return $this->getMedia('avatars')->first()?->getUrl();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatars')
            ->useDisk('public')
            ->singleFile();
    }

    public function getInitials(): string
    {
        $initials = mb_strtoupper(mb_substr($this->name, 0, 1));

        if (filled($this->surname)) {
            $initials .= mb_strtoupper(mb_substr($this->surname, 0, 1));
        }

        return $initials;
    }

    public function initials(): string
    {
        return $this->getInitials();
    }

    /**
     * Get a consistent avatar background color based on the user's name.
     *
     * @return string Hex color code (e.g., '#3B82F6')
     */
    public function getAvatarColor(): string
    {
        $colors = [
            '#3B82F6', // blue-500
            '#10B981', // emerald-500
            '#8B5CF6', // violet-500
            '#F59E0B', // amber-500
            '#EF4444', // red-500
            '#EC4899', // pink-500
            '#06B6D4', // cyan-500
            '#84CC16', // lime-500
        ];

        $hash = crc32($this->name.$this->surname);

        return $colors[abs($hash) % count($colors)];
    }
}
