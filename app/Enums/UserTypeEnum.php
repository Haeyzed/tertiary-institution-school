<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case TEACHER = 'teacher';
    case STUDENT = 'student';
    case PARENT = 'parent';

    /**
     * Get all user type values as an array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum values with their labels.
     *
     * @return array
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $enum) {
            $carry[$enum->value] = $enum->label();
            return $carry;
        }, []);
    }

    /**
     * Get human-readable name for the user type
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN => 'Administrator',
            self::STAFF => 'Staff Member',
            self::TEACHER => 'Teacher',
            self::STUDENT => 'Student',
            self::PARENT => 'Parent',
        };
    }

    /**
     * Get the corresponding role for this user type
     *
     * @return string
     */
    public function getDefaultRole(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => RoleEnum::SUPER_ADMIN->value,
            self::ADMIN => RoleEnum::ADMIN->value,
            self::STAFF => RoleEnum::STAFF->value,
            self::TEACHER => RoleEnum::TEACHER->value,
            self::STUDENT => RoleEnum::STUDENT->value,
            self::PARENT => RoleEnum::PARENT->value,
        };
    }
}
