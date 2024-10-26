<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function it_can_store_an_article()
    {
        $articleData = [
            'title' => 'AI in Healthcare',
            'content' => 'The impact of AI on healthcare...',
            'author' => 'John Doe',
            'source' => 'Health News',
            'category' => 'Health',
            'published_at' => now(),
        ];

        $article = Article::create($articleData);

        $this->assertDatabaseHas('articles', [
            'title' => 'AI in Healthcare',
            'author' => 'John Doe',
        ]);
    }

}
