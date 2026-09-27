<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CLIENTE = 'cliente';

    public const ROLE_COCINA = 'cocina';

    public const ROLE_DELIVERY = 'delivery';

    public const ROLE_CAJERO = 'cajero';

    public const ROLE_MESERO = 'mesero';

    public const ROLE_GERENTE = 'gerente';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sucursal_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function allowedRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_CLIENTE,
            self::ROLE_COCINA,
            self::ROLE_DELIVERY,
            self::ROLE_CAJERO,
            self::ROLE_MESERO,
            self::ROLE_GERENTE,
        ];
    }

    public static function roleRequiresBranch(string $role): bool
    {
        return in_array($role, [
            self::ROLE_COCINA,
            self::ROLE_DELIVERY,
            self::ROLE_CAJERO,
            self::ROLE_MESERO,
            self::ROLE_GERENTE,
        ], true);
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
