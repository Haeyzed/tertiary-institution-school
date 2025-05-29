<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case TEACHER = 'teacher';
    case STUDENT = 'student';
    case PARENT = 'parent';

    /**
     * Get all role values as an array
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
     * Get human-readable name for the role
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
     * Get default permissions for this role
     *
     * @return array<string>
     */
    public function getDefaultPermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => ['*'], // All permissions
            self::ADMIN => [
                'view-dashboard-metrics',
                'view-user', 'create-user', 'edit-user', 'delete-user',
                'view-role', 'create-role', 'edit-role', 'delete-role',
                'view-permission', 'create-permission', 'edit-permission', 'delete-permission',
                'assign-role', 'revoke-role', 'assign-permission', 'revoke-permission',
                'view-all-students', 'view-all-staff', 'view-all-parents',
                'manage-settings', 'view-reports', 'export-data', 'import-data',
            ],
            self::STAFF => [
                'view-dashboard-metrics',
                'view-user', 'edit-user',
                'view-student', 'create-student', 'edit-student',
                'view-course', 'create-course', 'edit-course',
                'view-exam', 'create-exam', 'edit-exam',
                'view-result', 'create-result', 'edit-result',
                'view-fee', 'create-fee', 'edit-fee',
                'view-payment', 'process-payment', 'verify-payment',
                'view-reports', 'export-data',
            ],
            self::TEACHER => [
                'view-dashboard-metrics',
                'view-user', 'edit-user',
                'view-student', 'view-course',
                'view-assignment', 'create-assignment', 'edit-assignment', 'delete-assignment',
                'view-exam', 'create-exam', 'edit-exam',
                'view-result', 'create-result', 'edit-result',
                'view-announcement', 'create-announcement', 'edit-announcement', 'delete-announcement',
            ],
            self::STUDENT => [
                'view-user', 'edit-user',
                'view-course', 'view-assignment', 'view-exam', 'view-result',
                'view-fee', 'view-payment', 'process-payment',
                'view-announcement',
            ],
            self::PARENT => [
                'view-user', 'edit-user',
                'view-student', 'view-course', 'view-result',
                'view-fee', 'view-payment', 'process-payment',
                'view-announcement',
            ],
        };
    }
}
