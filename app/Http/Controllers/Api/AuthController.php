<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const CODE_EXPIRY_MINUTES = 10;

    public function checkRegistrationEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        if (User::query()->where('email', $validated['email'])->exists()) {
            return response()->json([
                'message' => 'This email is already claimed or used.',
            ], 422);
        }

        return response()->json([
            'message' => 'This email is available.',
        ]);
    }

    public function requestRegistrationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/', 'confirmed'],
            'contact_number' => ['required', 'regex:/^63\d{10}$/'],
            'address' => ['required', 'string', 'max:255'],
        ], [
            'email.unique' => 'This email is already claimed or used.',
        ]);

        $code = $this->generateCode();

        DB::table('auth_codes')->updateOrInsert(
            [
                'email' => $validated['email'],
                'purpose' => 'register',
            ],
            [
                'code_hash' => Hash::make($code),
                'payload' => json_encode([
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'contact_number' => $validated['contact_number'],
                    'address' => $validated['address'],
                ]),
                'expires_at' => now()->addMinutes(self::CODE_EXPIRY_MINUTES),
                'used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->sendCodeMail(
            $validated['email'],
            'Verify your PSARS account',
            $code,
            'registration verification'
        );

        return response()->json([
            'message' => 'Verification code sent to your email. Enter it to complete registration.',
        ], 202);
    }

    public function verifyRegistrationCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = DB::table('auth_codes')
            ->where('email', $validated['email'])
            ->where('purpose', 'register')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $pending || ! Hash::check($validated['code'], $pending->code_hash)) {
            return response()->json([
                'message' => 'Invalid or expired verification code.',
            ], 422);
        }

        $payload = json_decode($pending->payload ?? '[]', true);

        if (! is_array($payload)) {
            return response()->json([
                'message' => 'Verification data is missing. Please request a new code.',
            ], 422);
        }

        if (User::query()->where('email', $validated['email'])->exists()) {
            DB::table('auth_codes')
                ->where('id', $pending->id)
                ->update(['used_at' => now(), 'updated_at' => now()]);

            return response()->json([
                'message' => 'This email is already registered. Please login.',
            ], 422);
        }

        $user = User::createWithRegistrationCode([
            'name' => $payload['full_name'],
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'password' => $payload['password'],
            'contact_number' => $payload['contact_number'],
            'address' => $payload['address'],
            'email_verified_at' => now(),
        ]);

        DB::table('auth_codes')
            ->where('id', $pending->id)
            ->update(['used_at' => now(), 'updated_at' => now()]);

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

        if (! $user->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
            ], 422);
        }

        return response()->json($this->issueTokenResponse($user));
    }

    public function requestPasswordResetCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
        ]);

        $code = $this->generateCode();

        DB::table('auth_codes')->updateOrInsert(
            [
                'email' => $validated['email'],
                'purpose' => 'reset_password',
            ],
            [
                'code_hash' => Hash::make($code),
                'payload' => null,
                'expires_at' => now()->addMinutes(self::CODE_EXPIRY_MINUTES),
                'used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->sendCodeMail(
            $validated['email'],
            'Reset your PSARS password',
            $code,
            'password reset'
        );

        return response()->json([
            'message' => 'Password reset code sent to your email.',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/', 'confirmed'],
        ]);

        $resetCode = DB::table('auth_codes')
            ->where('email', $validated['email'])
            ->where('purpose', 'reset_password')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $resetCode || ! Hash::check($validated['code'], $resetCode->code_hash)) {
            return response()->json([
                'message' => 'Invalid or expired reset code.',
            ], 422);
        }

        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'Account not found.',
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        DB::table('auth_codes')
            ->where('id', $resetCode->id)
            ->update(['used_at' => now(), 'updated_at' => now()]);

        return response()->json([
            'message' => 'Password reset successfully. You can now login.',
        ]);
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

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendCodeMail(string $email, string $subject, string $code, string $context): void
    {
        Mail::raw(
            "Your PSARS {$context} code is: {$code}\n\nThis code expires in " . self::CODE_EXPIRY_MINUTES . ' minutes.',
            function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            }
        );
    }

    private function serializeUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'registration_code' => $user->registration_code,
            'full_name' => $user->full_name,
            'name' => $user->full_name,
            'email' => $user->email,
            'contact_number' => $user->contact_number,
            'address' => $user->address,
            'is_admin' => false,
        ];
    }
}