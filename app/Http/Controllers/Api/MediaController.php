<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function show(Request $request): BinaryFileResponse|StreamedResponse
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
            $stream = Storage::disk('s3')->readStream($path);

            if ($stream === false) {
                abort(404);
            }

            $mimeType = Storage::disk('s3')->mimeType($path) ?: 'application/octet-stream';

            return response()->stream(function () use ($stream): void {
                fpassthru($stream);

                if (is_resource($stream)) {
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                'Cache-Control' => 'public, max-age=900',
            ]);
        }

        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        abort(404);
    }
}
