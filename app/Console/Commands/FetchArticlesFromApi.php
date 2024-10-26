<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class FetchArticlesFromApi extends Command
{
    protected $signature = 'articles:fetch';
    protected $description = 'Fetch articles from external news APIs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sources = [
            'https://newsapi.org/v2/top-headlines?sources=bbc-news&apiKey=e974b669850a4348917567e9f3d2f68f',
            'https://newsapi.org/v2/top-headlines?sources=cnn&apiKey=e974b669850a4348917567e9f3d2f68f',
            'https://newsapi.org/v2/top-headlines?sources=the-verge&apiKey=e974b669850a4348917567e9f3d2f68f',
        ];

        foreach ($sources as $source) {
            try {
                $this->fetchAndStoreArticles($source);
                $this->info('Articles fetched successfully from: ' . $source);
                Log::info('Articles fetched successfully from: ' . $source);
            } catch (Exception $e) {
                $this->error('Error fetching articles from: ' . $source);
                Log::error('Error fetching articles from: ' . $source, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    public function fetchAndStoreArticles($source)
    {
        $client = new Client();

        try {
            $response = $client->get($source);
            $articles = json_decode($response->getBody()->getContents(), true)['articles'];

            foreach ($articles as $articleData) {
                $publishedAt = isset($articleData['publishedAt'])
                ? Carbon::parse($articleData['publishedAt'])->format('Y-m-d H:i:s')
                : null;

                Article::updateOrCreate(
                    ['title' => $articleData['title']],
                    [
                        'content' => $articleData['content'],
                        'author' => $articleData['author'] ?? null,
                        'source' => $articleData['source']['name'],
                        'category' => $articleData['category'] ?? 'General',
                        'published_at' => $publishedAt,
                    ]
                );
            }

            Log::info('Articles stored successfully from source: ' . $source);

        } catch (Exception $e) {
            Log::error('Error processing articles from source: ' . $source, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
