<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $appends = [
        'registration_code',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'full_name',
        'email',
        'email_verified_at',
        'password',
        'contact_number',
        'address',
        'registration_year',
        'registration_sequence',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'registration_year' => 'integer',
            'registration_sequence' => 'integer',
            'password' => 'hashed',
        ];
    }

    public function getRegistrationCodeAttribute(): string
    {
        $year = $this->registration_year ?? $this->created_at?->format('y') ?? '00';
        $sequence = $this->registration_sequence ?? $this->id;

        return sprintf('%02d-%05d', ((int) $year) % 100, (int) $sequence);
    }

    public static function createWithRegistrationCode(array $attributes): self
    {
        return DB::transaction(function () use ($attributes) {
            $year = (int) now()->format('Y');
            $nextSequence = (int) (static::query()
                ->where('registration_year', $year)
                ->max('registration_sequence') ?? 0) + 1;

            $attributes['registration_year'] = $year;
            $attributes['registration_sequence'] = $nextSequence;

            return static::query()->create($attributes);
        });
    }
}
