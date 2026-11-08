<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Concerns\HasDisplayCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasDisplayCode, HasFactory, Notifiable;

    public const DISPLAY_CODE_COLUMN = 'code';

    public const DISPLAY_CODE_PREFIX = 'USR-';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'date_of_birth',
        'join_date',
        'phone_number',
        'status',
        'role',
        'avatar',
        'position',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'join_date' => 'date',
            'password' => 'hashed',
            'role' => Role::class,
            'status' => UserStatus::class,
        ];
    }

    /** Public URL of the user's avatar, falling back to the default photo. */
    public function getAvatarUrlAttribute(): string
    {
        return asset('images/'.($this->avatar ?: 'photo_defaults.jpg'));
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return (bool) $this->role?->isAdmin();
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    /** Departments this user (a teacher) is head of. */
    public function headedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'head_user_id');
    }

    /**
     * Whether this user may see a student's personal records (report cards, attendance):
     * the student's own account. Guardians are added in the Communication module.
     */
    public function canViewStudent(Student $student): bool
    {
        return $student->user_id === $this->id;
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }
}
