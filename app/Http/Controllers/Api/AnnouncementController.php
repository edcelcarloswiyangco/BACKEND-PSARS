<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends Controller
{
    public function index(): JsonResponse
    {
        $announcements = Announcement::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->latest('id')
            ->get();

        return response()->json([
            'data' => $announcements->map(function (Announcement $announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'description' => $announcement->description,
                    'image_path' => $announcement->image_path,
                    'is_published' => $announcement->is_published,
                    'published_at' => optional($announcement->published_at)->toIso8601String(),
                    'created_at' => optional($announcement->created_at)->toIso8601String(),
                ];
            }),
        ]);
    }
}