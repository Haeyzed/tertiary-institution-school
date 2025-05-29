<?php

namespace App\Console\Commands;

use App\Services\ACLService;
use App\Services\RoleService;
use Exception;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SetupRolesAndPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:setup-roles-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up default roles and permissions for the application';

    /**
     * Execute the console command.
     */
    public function handle(ACLService $aclService, RoleService $roleService): int
    {
        $this->info('Setting up default roles and permissions...');

        try {
            // Create default roles and permissions
            $aclService->createDefaultRolesAndPermissions();

            $this->info('✅ Default roles and permissions created successfully!');

            // Display created roles
            $this->info("\nCreated Roles:");
            $roles = Role::all();
            foreach ($roles as $role) {
                $this->line("  - {$role->name}");
            }

            // Display created permissions count
            $permissionsCount = Permission::query()->count();
            $this->info("\nCreated {$permissionsCount} permissions");

            return CommandAlias::SUCCESS;
        } catch (Exception $e) {
            $this->error('Failed to set up roles and permissions: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
