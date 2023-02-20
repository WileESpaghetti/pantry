<?php

namespace Tests\Feature;

use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pantry\Bookmark;
use Pantry\Repositories\TagRepository;
use Pantry\Tag;
use Pantry\User;
use Psr\Log\NullLogger;
use Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ?TagRepository $repo;
    private ?User $user;

    // FIXME not used
    protected function afterRefreshingDatabase()
    {
        $this->user = null;
        $this->repo = null;

        $this->user = User::factory()->create();
        $this->repo = new TagRepository(new NullLogger(), $this->app->make(DatabaseManager::class), $this->user);
    }


    /** @test */
    public function test_create(): void
    {
        $user = User::factory()->create();
        $repo = new TagRepository(new NullLogger(), $this->app->make(DatabaseManager::class), $user);

        $tagData = ['name' => 'example'];

        $tag = $repo->createForUser($user, $tagData);

        $this->assertDatabaseCount('tags', 1);
        $this->assertNotNull($tag);
        $this->assertEquals($tagData['name'], $tag->name);
    }

    /** @test */
    public function test_update(): void
    {
        $user = User::factory()->create();
        $repo = new TagRepository(new NullLogger(), $this->app->make(DatabaseManager::class), $user);

        $tag = Tag::factory()->create();
        $this->assertDatabaseCount('tags', 1);

        $updateData = ['name' => 'example'];
        $this->assertNotEquals($updateData['name'], $tag->name);

        $updatedTag = $repo->update($tag, $updateData);

        $this->assertNotNull($updatedTag);
        $this->assertNotEquals($tag, $updatedTag);
        $this->assertEquals($updateData['name'], $updatedTag->name);
        $this->assertDatabaseCount('tags', 1);

    }

    /** @test */
    public function test_delete(): void
    {
        $user = User::factory()->create();
        $repo = new TagRepository(new NullLogger(), $this->app->make(DatabaseManager::class), $user);

        $bookmark = Bookmark::factory()->create();
        $tag = Tag::factory()->create();
        $bookmark->tags()->save($tag);
        $bookmark = $bookmark->fresh();
        $tag = $tag->fresh();
        $this->assertCount(1, $bookmark->tags);
        $this->assertCount(1, $tag->bookmarks);

        $wasDeleted = $repo->delete($tag);
        $bookmark = $bookmark->fresh();

        $this->assertTrue($wasDeleted);
        $this->assertDatabaseCount('tags', 0);
        $this->assertCount(0, $bookmark->tags);
    }
}
