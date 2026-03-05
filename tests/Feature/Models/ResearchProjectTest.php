<?php

use App\Enums\EnrollmentStatus;
use App\Enums\EnrollmentType;
use App\Models\ResearchProject;
use App\Models\Semester;
use App\Models\User;
use App\Models\UserSelection;

it('generates an invite token and saves it', function (): void {
    $project = ResearchProject::factory()->create(['invite_token' => null]);

    expect($project->invite_token)->toBeNull();

    $project->generateInviteToken();

    expect($project->fresh()->invite_token)->toHaveLength(32);
});

it('generates a new token on regeneration', function (): void {
    $project = ResearchProject::factory()->withInviteToken()->create();
    $old = $project->invite_token;

    $project->generateInviteToken();

    expect($project->fresh()->invite_token)->not->toBe($old);
});

it('detects an enrolled member', function (): void {
    $user = User::factory()->create();
    $semester = Semester::factory()->create();
    $project = ResearchProject::factory()->create();

    UserSelection::factory()->create([
        'user_id' => $user->id,
        'semester_id' => $semester->id,
        'elective_type' => ResearchProject::class,
        'elective_choice_id' => $project->id,
        'enrollment_type' => EnrollmentType::Direct,
        'status' => EnrollmentStatus::Confirmed,
    ]);

    expect($project->isUserMember($user, $semester))->toBeTrue();
});

it('returns false for non-member', function (): void {
    $user = User::factory()->create();
    $semester = Semester::factory()->create();
    $project = ResearchProject::factory()->create();

    expect($project->isUserMember($user, $semester))->toBeFalse();
});
