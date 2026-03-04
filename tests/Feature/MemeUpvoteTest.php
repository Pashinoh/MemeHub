<?php

namespace Tests\Feature;

use App\Models\Meme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemeUpvoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_upvote_updates_score_and_creates_notification_for_owner(): void
    {
        $owner = User::factory()->create();
        $upvoter = User::factory()->create();

        $meme = Meme::create([
            'user_id' => $owner->id,
            'title' => 'Test Meme',
            'slug' => 'test-meme-' . uniqid(),
            'image_path' => 'memes/test.jpg',
            'score' => 0,
        ]);

        $response = $this
            ->actingAs($upvoter)
            ->postJson(route('memes.upvote', $meme), [
                'upvote_state' => '1',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'has_upvoted' => true,
                'score' => 1,
            ]);

        $this->assertDatabaseHas('meme_upvotes', [
            'meme_id' => $meme->id,
            'user_id' => $upvoter->id,
        ]);

        $this->assertSame(1, $meme->fresh()->score);
        $this->assertCount(1, $owner->fresh()->notifications);
    }

    public function test_upvote_state_endpoint_is_idempotent_and_can_unvote(): void
    {
        $owner = User::factory()->create();
        $upvoter = User::factory()->create();

        $meme = Meme::create([
            'user_id' => $owner->id,
            'title' => 'Another Meme',
            'slug' => 'another-meme-' . uniqid(),
            'image_path' => 'memes/test2.jpg',
            'score' => 0,
        ]);

        $this->actingAs($upvoter)->postJson(route('memes.upvote', $meme), [
            'upvote_state' => '1',
        ])->assertOk()->assertJson([
            'has_upvoted' => true,
            'score' => 1,
        ]);

        $this->actingAs($upvoter)->postJson(route('memes.upvote', $meme), [
            'upvote_state' => '1',
        ])->assertOk()->assertJson([
            'has_upvoted' => true,
            'score' => 1,
        ]);

        $this->assertDatabaseCount('meme_upvotes', 1);

        $this->actingAs($upvoter)->postJson(route('memes.upvote', $meme), [
            'upvote_state' => '0',
        ])->assertOk()->assertJson([
            'has_upvoted' => false,
            'score' => 0,
        ]);

        $this->assertDatabaseMissing('meme_upvotes', [
            'meme_id' => $meme->id,
            'user_id' => $upvoter->id,
        ]);

        $this->assertSame(0, $meme->fresh()->score);
    }
}
