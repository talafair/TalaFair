<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserAvatarTest extends TestCase
{
    public function test_users_without_an_uploaded_avatar_use_the_static_silhouette(): void
    {
        $user = User::factory()->make(['avatar_path' => null]);

        $this->assertSame(asset('images/default-avatar.svg'), $user->avatar_url);
        $this->assertStringNotContainsString('talafair-logo', $user->avatar_url);
    }

    public function test_uploaded_avatar_urls_are_preserved(): void
    {
        $user = User::factory()->make(['avatar_path' => 'avatars/profile.png']);

        $this->assertStringContainsString('avatars/profile.png', $user->avatar_url);
    }
}
