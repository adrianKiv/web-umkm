<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 1],
            ['nama_role' => 'user']
        );

        DB::table('roles')->updateOrInsert(
            ['id' => 2],
            ['nama_role' => 'admin']
        );

        DB::table('roles')->updateOrInsert(
            ['id' => 3],
            ['nama_role' => 'super_admin']
        );
    }
}
