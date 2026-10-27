<?php

namespace Tests\Feature\GroupAdmin;

use App\Models\Candidate;
use App\Models\Country;
use App\Models\Group;
use App\Models\Religion;
use App\Models\User;

trait InteractsWithGroupAdmins
{
    protected function seedReferences(): void
    {
        Country::query()->create([
            'id' => 'PAK',
            'name' => 'Pakistan',
            'nationality' => 'Pakistani',
        ]);

        Religion::query()->create([
            'id' => 1,
            'name' => 'Islam',
        ]);
    }

    protected function admin(string $name = 'Sana'): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower($name).'@example.com',
            'role' => 'group_admin',
        ]);

        $user->groupAdmin()->create([
            'name' => $name,
            'phone' => '03001234567',
        ]);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function ownedGroup(User $admin, array $attributes = []): Group
    {
        $group = Group::query()->create([
            'name' => 'Doctors',
            'description' => 'Doctors only',
            ...$attributes,
        ]);

        $group->admins()->attach($admin->id, [
            'is_primary' => true,
            'approval_date' => now(),
            'approved_by' => $admin->id,
        ]);

        return $group;
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    protected function member(string $displayName, Group $group, array $profile = [], string $membership = 'active'): User
    {
        $user = User::factory()->create([
            'name' => $displayName,
            'email' => strtolower($displayName).'@example.com',
        ]);

        Candidate::factory()->create([
            'user_id' => $user->id,
            'display_name' => $displayName,
            'real_name' => $displayName.' Khan',
            ...$profile,
        ]);

        $group->candidates()->attach($user->id, [
            'status' => $membership,
        ]);

        return $user;
    }
}
