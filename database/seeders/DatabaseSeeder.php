<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user = User::factory()->create([
            'name'     => 'Heuder Rodrigues de Sena',
            'email'    => 'heuderdev@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        $defaultName = Str::upper(Str::uuid());

        $tenant = Tenant::create([
            'name' => 'Tenant-' . $defaultName,
            'slug' => 'slug-' . $defaultName,
        ]);

        $tenant->users()->attach($user->id, [
            'role'   => 'owner',
            'status' => 'ativo',
            'cargo'  => 'Administrador',
        ]);

        $user->update([
            'default_tenant_id' => $tenant->id,
        ]);

        $this->call([
            BancoFebrabanSeeder::class
        ]);
    }
}
