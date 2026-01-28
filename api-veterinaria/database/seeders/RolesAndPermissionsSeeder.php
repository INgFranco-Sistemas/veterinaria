<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos base (los iremos ampliando por módulos)
        $permissions = [
            // Usuarios / Roles
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',

            // Veterinarios / Clientes / Mascotas
            'vets.view', 'vets.create', 'vets.update', 'vets.delete',
            'clients.view', 'clients.create', 'clients.update', 'clients.delete',
            'pets.view', 'pets.create', 'pets.update', 'pets.delete',

            // Servicios / Agenda
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.delete',
            'vaccines.view', 'vaccines.create', 'vaccines.update', 'vaccines.delete',
            'surgeries.view', 'surgeries.create', 'surgeries.update', 'surgeries.delete',

            // Pagos
            'payments.view', 'payments.create', 'payments.update', 'payments.delete',

            // KPIs
            'kpis.view',

            // Horarios / Slots
            'schedules.view', 'schedules.create', 'schedules.update', 'schedules.delete',
            'slots.view', 'slots.generate', 'slots.delete',

            // Reservas (citas)
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.cancel',

        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'api']);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $vet = Role::firstOrCreate(['name' => 'veterinario', 'guard_name' => 'api']);
        $reception = Role::firstOrCreate(['name' => 'recepcion', 'guard_name' => 'api']);
        $client = Role::firstOrCreate(['name' => 'cliente', 'guard_name' => 'api']);

        // Asignaciones
        $admin->syncPermissions(Permission::all());

        $vet->syncPermissions([
            'appointments.view', 'appointments.update',
            'vaccines.view', 'vaccines.create', 'vaccines.update',
            'surgeries.view', 'surgeries.create', 'surgeries.update',
            'pets.view',
            'kpis.view',
        ]);

        $reception->syncPermissions([
            'appointments.view', 'appointments.create', 'appointments.update',
            'clients.view', 'clients.create', 'clients.update',
            'pets.view', 'pets.create', 'pets.update',
            'payments.view', 'payments.create',
        ]);

        $client->syncPermissions([
            'appointments.view', 'appointments.create',
            'pets.view', 'pets.create', 'pets.update',
            'payments.view',
        ]);
    }
}
