<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Permission as PermissionType;
use App\Enums\Role as RoleType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\info;
use function Laravel\Prompts\outro;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'beacon:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Beacon';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        DB::transaction(callback: function () {
            info('Installing Beacon');

            if (Role::count() === 0) {
                info('Creating permissions and roles');

                collect(PermissionType::values())->each(function (string $permission) {
                    return Permission::create(['name' => $permission]);
                });

                Role::create(['name' => RoleType::OWNER()])->givePermissionTo(Permission::all());

                Role::create(['name' => RoleType::ADMIN()])->givePermissionTo(
                    Permission::all()
                        ->filter(fn (Permission $permission) => $permission->name !== PermissionType::BILLING->value)
                );

                Role::create(['name' => RoleType::DEVELOPER()])->givePermissionTo([
                    PermissionType::FEATURE_FLAGS(),
                    PermissionType::FEATURE_TYPES(),
                    PermissionType::FEATURE_FLAG_STATUS(),
                    PermissionType::APPLICATIONS(),
                    PermissionType::ENVIRONMENTS(),
                    PermissionType::TAGS(),
                ]);

                Role::create(['name' => RoleType::BILLER()])->givePermissionTo([PermissionType::BILLING()]);
            }

            outro('Beacon installed successfully');
        });
    }
}
