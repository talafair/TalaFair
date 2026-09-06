<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_user_accepts_each_registration_category_and_saves_it(): void
    {
        $official = $this->managementOfficial();

        foreach (array_keys(User::CATEGORIES) as $index => $category) {
            $user = $this->actingAs($official)->post(route('users.store'), $this->userPayload($category, $index));

            $user->assertRedirect(route('users.index'));
            $this->assertDatabaseHas('users', [
                'username' => "managed-{$category}-{$index}",
                'role' => $category,
            ]);
        }
    }

    public function test_add_user_rejects_an_invalid_category(): void
    {
        $official = $this->managementOfficial();

        $this->actingAs($official)
            ->from(route('users.create'))
            ->post(route('users.store'), $this->userPayload('not-a-category'))
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['username' => 'managed-not-a-category']);
    }

    public function test_edit_user_preserves_existing_data_and_updates_category(): void
    {
        $official = $this->managementOfficial();
        $user = User::factory()->create([
            'role' => 'resident',
            'email' => 'keep-this@example.test',
            'username' => 'keep-this-user',
        ]);

        $this->actingAs($official)
            ->get(route('users.edit', $user))
            ->assertOk()
            ->assertSee('value="guest"', false)
            ->assertSee('Category', false);

        $payload = $this->userPayload('guest', 20);
        $payload['email'] = $user->email;
        $payload['username'] = $user->username;
        $payload['first_name'] = 'Updated';

        $this->actingAs($official)
            ->put(route('users.update', $user), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'guest',
            'email' => 'keep-this@example.test',
            'username' => 'keep-this-user',
            'first_name' => 'Updated',
        ]);
    }

    public function test_registration_uses_the_same_category_options(): void
    {
        $this->get(route('register', ['role' => 'resident']))
            ->assertOk()
            ->assertSee('value="resident"', false)
            ->assertSee('name="role"', false);

        $this->assertSame(['resident', 'guest', 'official'], array_keys(User::CATEGORIES));
    }

    private function managementOfficial(): User
    {
        return User::factory()->create([
            'role' => 'official',
            'official_group' => 'barangay_council',
            'official_position' => 'kagawad',
        ]);
    }

    private function userPayload(string $category, int $index = 0): array
    {
        return [
            'role' => $category,
            'official_group' => $category === 'official' ? 'barangay_council' : '',
            'official_position' => $category === 'official' ? 'kagawad' : '',
            'first_name' => 'Managed',
            'middle_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'female',
            'birthdate' => '1990-01-01',
            'contact_number' => '09170000000',
            'is_student' => '0',
            'occupation' => 'Tester',
            'house_no' => '10',
            'street' => 'Main Street',
            'zone' => '1',
            'is_head_of_family' => '1',
            'head_of_family_id' => '',
            'head_of_family_name' => '',
            'username' => "managed-{$category}-{$index}",
            'email' => "managed-{$category}-{$index}@example.test",
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];
    }
}
