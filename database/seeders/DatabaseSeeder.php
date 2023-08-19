<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        Admin::insert([
            'name' => 'Satria Mandala',
            'gender' => 'Laki - laki',
            'phone' => '082343526675',
            'email' => 'satriamandala@gmail.com',
            'password' => Hash::make('satria123')
        ]);

        // Doctor
        DB::table('doctors')->insert([
            'name' => 'Mohamad Lutfi',
            'gender' => 'Laki - laki',
            'specialization' => 'Dokter Kulit',
            'phone' => '082312324456',
            'email' => 'mohamadlutfi@gmail.com',
            'password' => Hash::make('lutfi123')
        ]);
        DB::table('doctors')->insert([
            'name' => 'Diandra Asyahtri',
            'gender' => 'Perempuan',
            'specialization' => 'Dokter Kulit',
            'phone' => '085667589990',
            'email' => 'diandraasyahtri@gmail.com',
            'password' => Hash::make('diandra123')
        ]);

        // Patient
        DB::table('patients')->insert([
            'name' => 'Hikmal Afandi',
            'gender' => 'Laki - laki',
            'phone' => '085623234567',
            'email' => 'hikmalafandi@gmail.com',
            'password' => Hash::make('hikmal123')
        ]);
        DB::table('patients')->insert([
            'name' => 'Zidan Rizky Wijaya',
            'gender' => 'Laki - laki',
            'phone' => '085645233334',
            'email' => 'zidanwijaya@gmail.com',
            'password' => Hash::make('zidan123')
        ]);
    }
}
