<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'H', 'name' => 'Hadir', 'slug' => 'hadir', 'color' => 'bg-emerald-50 text-emerald-700', 'sort_order' => 1],
            ['code' => 'T', 'name' => 'Terlambat', 'slug' => 'terlambat', 'color' => 'bg-amber-50 text-amber-700', 'sort_order' => 2],
            ['code' => 'I', 'name' => 'Izin', 'slug' => 'izin', 'color' => 'bg-sky-50 text-sky-700', 'sort_order' => 3],
            ['code' => 'S', 'name' => 'Sakit', 'slug' => 'sakit', 'color' => 'bg-purple-50 text-purple-700', 'sort_order' => 4],
            ['code' => 'A', 'name' => 'Alpha', 'slug' => 'alpha', 'color' => 'bg-rose-50 text-rose-700', 'sort_order' => 5],
        ];

        foreach ($statuses as $status) {
            AttendanceStatus::query()->firstOrCreate(
                ['code' => $status['code']],
                $status,
            );
        }
    }
}
