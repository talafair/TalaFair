<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_bell_shows_unread_count_and_opening_notification_marks_it_read(): void
    {
        $user = User::factory()->create();
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'title' => 'Account update',
            'body' => 'Your account was updated.',
        ]);

        UserNotification::insert(collect(range(1, 9))->map(fn () => [
            'user_id' => $user->id,
            'title' => 'Another update',
            'body' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('9+', false);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $notification = UserNotification::create([
            'user_id' => $owner->id,
            'title' => 'Private update',
        ]);

        $this->actingAs($otherUser)
            ->get(route('notifications.open', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }
}
