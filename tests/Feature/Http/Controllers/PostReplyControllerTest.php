<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Post;
use App\Models\PostReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PostReplyControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'create post replies']);
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo('create post replies');
    }

    /**
     * @test
     */
    public function it_stores_a_post_reply_and_redirects_with_status()
    {
        $user = User::factory()->create()->assignRole('user');
        $post = Post::factory()->create();
        $content = $this->faker->sentence;

        $response = $this
            ->from(route('posts.show', $post))
            ->actingAs($user)
            ->post(route('replies.store', $post), [
            'content' => $content,
        ]);

        $response->assertRedirect(route('posts.show', $post));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Postreply successfully created.');

        $this->assertDatabaseHas('post_replies', [
            'content' => $content,
            'user_id' => $user->id,
            'post_id' => $post->id,
            'parent_id' => null,
        ]);
    }

    /**
     * @test
     */
    public function it_stores_a_comment_to_a_post_reply_and_redirects_with_status()
    {
        $user = User::factory()->create()->assignRole('user');
        $post = Post::factory()->create();
        $postReply = PostReply::factory()->create();
        $content = $this->faker->sentence;

        $response = $this
            ->from(route('posts.show', $post->id))
            ->actingAs($user)
            ->post(route('replies.store', [$post, $postReply]), [
                'content' => $content,
            ]);

        $response->assertRedirect(route('posts.show', $post->id));
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'Comment successfully created.');

        $this->assertDatabaseHas('post_replies', [
            'content' => $content,
            'user_id' => $user->id,
            'post_id' => $post->id,
            'parent_id' => $postReply->id,
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_store_a_post_reply_with_no_content_provided()
    {
        $user = User::factory()->create()->assignRole('user');
        $post = Post::factory()->create();

        $response = $this
            ->from(route('posts.show', $post->id))
            ->actingAs($user)
            ->post(route('replies.store', $post), []);

        $this->assertDatabaseCount('post_replies', 0);
        $response->assertSessionHasErrors('content');
        $response->assertRedirect(route('posts.show', $post->id));
    }

    /**
     * @test
     */
    public function it_does_not_store_a_post_reply_when_the_user_is_not_authenticated()
    {
        $post = Post::factory()->create();
        $content = $this->faker->sentence;

        $response = $this
            ->from(route('posts.show', $post->id))
            ->post(route('replies.store', $post), [
                'content' => $content,
            ]);

        $this->assertDatabaseCount('post_replies', 0);

        $response->assertRedirect(route('home'));
    }
}
