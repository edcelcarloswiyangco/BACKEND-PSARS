<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:60'],
            'middle_name' => ['nullable', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'suffix' => ['nullable', 'string', 'in:Jr.,Sr.,II,III,IV'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:12',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^()_+\-\[\]{};:,.<>]).{12,}$/',
            ],
            'country_code' => ['required', 'string', 'regex:/^\+\d{1,4}$/', 'max:8'],
            'phone_number' => ['required', 'string', 'regex:/^\d{7,15}$/'],
            'house_number' => ['nullable', 'string', 'max:30'],
            'building_name' => ['nullable', 'string', 'max:120'],
            'street_name' => ['required', 'string', 'max:120'],
            'barangay' => ['required', 'string', 'max:120'],
            'city_municipality' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'zip_code' => ['required', 'string', 'regex:/^\d{4,10}$/'],
        ]);

        $fullName = $this->composeFullName($validated);
        $contactNumber = $this->composeContactNumber($validated);
        $address = $this->composeAddress($validated);

        $user = User::query()->create([
            'name' => $fullName,
            'full_name' => $fullName,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country_code' => $validated['country_code'],
            'contact_number' => $contactNumber,
            'house_number' => $validated['house_number'] ?? null,
            'building_name' => $validated['building_name'] ?? null,
            'street_name' => $validated['street_name'],
            'barangay' => $validated['barangay'],
            'city_municipality' => $validated['city_municipality'],
            'province' => $validated['province'],
            'zip_code' => $validated['zip_code'],
            'address' => $address,
        ]);

        return response()->json($this->issueTokenResponse($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 422);
        }

        return response()->json($this->issueTokenResponse($user));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->serializeUser($request->user()),
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'contact_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        $user->contact_number = $validated['contact_number'];
        $user->address = $validated['address'];
        $user->save();

        return response()->json([
            'data' => $this->serializeUser($user),
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var ApiToken|null $token */
        $token = $request->attributes->get('api_token');

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function issueTokenResponse(User $user): array
    {
        $plainTextToken = Str::random(64);

        ApiToken::query()->create([
            'user_id' => $user->id,
            'name' => 'mobile-app',
            'token_hash' => hash('sha256', $plainTextToken),
            'last_used_at' => now(),
        ]);

        return [
            'token_type' => 'Bearer',
            'token' => $plainTextToken,
            'data' => $this->serializeUser($user),
        ];
    }

    private function serializeUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'name' => $user->full_name,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'suffix' => $user->suffix,
            'email' => $user->email,
            'country_code' => $user->country_code,
            'contact_number' => $user->contact_number,
            'phone_number' => $user->contact_number,
            'house_number' => $user->house_number,
            'building_name' => $user->building_name,
            'street_name' => $user->street_name,
            'barangay' => $user->barangay,
            'city_municipality' => $user->city_municipality,
            'province' => $user->province,
            'zip_code' => $user->zip_code,
            'address' => $user->address,
            'is_admin' => false,
        ];
    }

    private function composeFullName(array $validated): string
    {
        $parts = [
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
            $validated['suffix'] ?? null,
        ];

        return implode(' ', array_values(array_filter($parts, fn ($part) => filled($part))));
    }

    private function composeContactNumber(array $validated): string
    {
        return trim($validated['country_code'] . ' ' . $validated['phone_number']);
    }

    private function composeAddress(array $validated): string
    {
        $parts = [
            $validated['house_number'] ?? null,
            $validated['building_name'] ?? null,
            $validated['street_name'],
            $validated['barangay'],
            $validated['city_municipality'],
            $validated['province'],
            $validated['zip_code'],
        ];

        return implode(', ', array_values(array_filter($parts, fn ($part) => filled($part))));
    }
}