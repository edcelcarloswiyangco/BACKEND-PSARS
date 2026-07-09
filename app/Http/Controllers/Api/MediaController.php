<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    public function show(Request $request): BinaryFileResponse|RedirectResponse
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

        if (Storage::disk('s3')->exists($path)) {
            return redirect()->away(
                Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(15))
            );
        }

        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        abort(404);
    }
}
