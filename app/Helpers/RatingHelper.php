<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class RatingHelper
{
    public static function getCalculatedRating(): Collection
    {
        $sort = request()->get('sort');
        $calculatedRating = QueryBuilder::for(User::class)
            ->defaultSort('name')
            ->allowedSorts($sort ?? 'name')
            ->whereHas('readChapters')
            ->orWhereHas('completedExercises')
            ->withCount('readChapters')
            ->withCount('completedExercises')
            ->limit(100)
            ->get()
            ->map(fn($user) => [
                'user' => $user,
                'points' => $user['read_chapters_count'] + $user['completed_exercises_count'] * 3,
            ])
            ->when(empty($sort), function ($collection) {
                return $collection->sortByDesc('points');
            })
            ->values()
            ->keyBy(fn($ratingPosition, $index) => $index + 1);

        return $calculatedRating;
    }

    public static function getParameterFromSorting(string $column, ?string $state): array
    {
        return match ($state) {
            null => ['sort' => $column],
            $column => ['sort' => "-{$column}"],
            default => [],
        };
    }
}
