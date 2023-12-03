<?php
namespace App\Constants;

class UserRolesKeys
{
    /**
     * Dostęp administracyjny
     * @var string
     */
    public const ADMIN = 'ROLE_ADMIN';

    /**
     * Zwykły użytkownik.
     * @var string
     */
    public const USER = "ROLE_USER";

    /**
     * Użytkownik darczyńca.
     * @var string
     */
    public const DONOR = "ROLE_DONOR";


    public const ALL_ROLES = [
        self::ADMIN,
        self::USER,
        self::DONOR,
    ];

    public const ROLE_DESCRIPTIONS = [
        self::ADMIN => "Administrator",
        self::USER => "Użytkownik",
        self::DONOR => "Darczyńca",
    ];
}