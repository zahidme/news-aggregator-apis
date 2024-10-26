<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function it_can_fetch_articles_with_pagination()
    {
        Article::factory()->count(15)->create();
        $response = $this->getJson('/api/articles?page=1');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'current_page',
                     'data' => [
                         '*' => [
                             'id',
                             'title',
                             'content',
                             'author',
                             'source',
                             'category',
                             'published_at',
                         ],
                     ],
                     'first_page_url',
                     'last_page_url',
                     'next_page_url',
                     'prev_page_url',
                     'links',
                     'from',
                     'last_page',
                     'per_page',
                     'total',
                     'to',
                 ]);
    }

    public function it_can_retrieve_single_article()
    {
        $article = Article::factory()->create();
        $response = $this->getJson("/api/articles/{$article->id}");
        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $article->id,
                     'title' => $article->title,
                 ]);
    }
}
