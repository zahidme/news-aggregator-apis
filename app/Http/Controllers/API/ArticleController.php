<?php

namespace App\Http\Controllers\API;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ArticleController extends BaseController
{
    /**
     * Fetch articles with pagination, search, and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $keyword = $request->input('keyword');
        $category = $request->input('category');
        $source = $request->input('source');
        $date = $request->input('date');

        $cacheKey = 'articles_' . md5(json_encode($request->all()));

        $articles = Cache::remember($cacheKey, 60, function () use ($keyword, $category, $source, $date) {
            $query = Article::query();
            if ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                      ->orWhere('content', 'like', '%' . $keyword . '%');
                });
            }

            if ($category) {
                $query->where('category', $category);
            }

            if ($source) {
                $query->where('source', $source);
            }

            if ($date) {
                $query->whereDate('published_at', $date);
            }

            return $query->orderBy('published_at', 'desc')->paginate(10);
        });

        return response()->json($articles);
    }
    public function show($id): JsonResponse
    {
        $cacheKey = 'article_' . $id;

        $article = Cache::remember($cacheKey, 60, function () use ($id) {
            return Article::findOrFail($id);
        });

        return response()->json($article);
    }

}
