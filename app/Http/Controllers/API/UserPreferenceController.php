<?php

namespace App\Http\Controllers\API;

use App\Models\UserPreference;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class UserPreferenceController extends BaseController
{
    public function setPreferences(Request $request)
    {

        try {
            $validated = $request->validate([
                'preferred_sources' => 'nullable|array',
                'preferred_categories' => 'nullable|array',
                'preferred_authors' => 'nullable|array',
            ]);


            $user = auth()->user();

            $preferences = UserPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'preferred_sources' => json_encode($validated['preferred_sources']),
                    'preferred_categories' => json_encode($validated['preferred_categories']),
                    'preferred_authors' => json_encode($validated['preferred_authors']),
                ]
            );
           

            return response()->json($preferences, 200);

        } catch (Exception $e) {
            Log::error('Error setting user preferences', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to set preferences'], 500);
        }
    }

    public function getPreferences()
    {
        try {
            $preferences = UserPreference::where('user_id', auth()->id())->first();

            if (!$preferences) {
                return response()->json(['error' => 'No preferences found'], 404);
            }

            return response()->json($preferences, 200);

        } catch (Exception $e) {
            Log::error('Error retrieving user preferences', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to get preferences'], 500);
        }
    }

    public function personalizedFeed()
    {
        try {
            $preferences = UserPreference::where('user_id', auth()->id())->first();

            if (!$preferences) {
                return response()->json(['error' => 'No preferences found'], 404);
            }

            $query = Article::query();

            if ($preferences->preferred_sources) {
                $query->whereIn('source', json_decode($preferences->preferred_sources));
            }

            if ($preferences->preferred_categories) {
                $query->whereIn('category', json_decode($preferences->preferred_categories));
            }

            if ($preferences->preferred_authors) {
                $query->whereIn('author', json_decode($preferences->preferred_authors));
            }

            $articles = $query->paginate(10);
            return response()->json($articles, 200);

        } catch (Exception $e) {
            Log::error('Error fetching personalized feed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to fetch personalized feed'], 500);
        }
    }
}
