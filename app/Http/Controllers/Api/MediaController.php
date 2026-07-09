<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MediaController extends Controller
{
    public function show(Request $request): RedirectResponse
    {
        $path = (string) $request->query('path', '');
        $path = ltrim($path, '/');

        if (
            $path === '' ||
            ! (
                Str::startsWith($path, 'reports/') ||
                Str::startsWith($path, 'pet_photos/') ||
                Str::startsWith($path, 'vaccination_cards/')
            )
        ) {
            abort(404);
        }

        if (! Storage::disk('s3')->exists($path)) {
            abort(404);
        }

        return redirect()->away(
            Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(15))
        );
    }
}
