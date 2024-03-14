<?php

namespace Database\Seeders;

use App\Actions\Jetstream\CreateTeam;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => env('ADMIN_EMAIL'),
            'is_admin' => true,
            'password' => bcrypt(env('ADMIN_PASSWORD'))]);

        (new CreateTeam)->create($user, [
            'name' => 'Admin Team',
        ]);
    }
}
