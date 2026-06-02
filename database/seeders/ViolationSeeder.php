<?php

namespace Database\Seeders;

use App\Models\CharacterPointType;
use App\Models\ViolationThreshold;
use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class ViolationSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['category' => 'ringan', 'name' => 'Terlambat masuk kelas', 'points' => 5, 'status' => 'active', 'sort_order' => 1],
            ['category' => 'ringan', 'name' => 'Tidak membawa buku', 'points' => 5, 'status' => 'active', 'sort_order' => 2],
            ['category' => 'ringan', 'name' => 'Seragam tidak rapi', 'points' => 5, 'status' => 'active', 'sort_order' => 3],
            ['category' => 'sedang', 'name' => 'Membuang sampah sembarangan', 'points' => 10, 'status' => 'active', 'sort_order' => 4],
            ['category' => 'sedang', 'name' => 'Tidak mengerjakan PR', 'points' => 10, 'status' => 'active', 'sort_order' => 5],
            ['category' => 'sedang', 'name' => 'Berkata kurang sopan', 'points' => 15, 'status' => 'active', 'sort_order' => 6],
            ['category' => 'berat', 'name' => 'Membolos', 'points' => 25, 'status' => 'active', 'sort_order' => 7],
            ['category' => 'berat', 'name' => 'Berkelahi', 'points' => 50, 'status' => 'active', 'sort_order' => 8],
            ['category' => 'berat', 'name' => 'Merokok di lingkungan sekolah', 'points' => 50, 'status' => 'active', 'sort_order' => 9],
        ])->each(fn (array $data) => ViolationType::query()->create($data));

        collect([
            ['points' => 25, 'label' => 'Peringatan Lisan', 'sort_order' => 1],
            ['points' => 50, 'label' => 'Peringatan Tertulis', 'sort_order' => 2],
            ['points' => 75, 'label' => 'Panggilan Orang Tua', 'sort_order' => 3],
            ['points' => 100, 'label' => 'Skorsing', 'sort_order' => 4],
        ])->each(fn (array $data) => ViolationThreshold::query()->create($data));

        collect([
            ['category' => 'akhlak', 'name' => 'Jujur', 'points' => 5, 'status' => 'active', 'sort_order' => 1],
            ['category' => 'akhlak', 'name' => 'Santun', 'points' => 5, 'status' => 'active', 'sort_order' => 2],
            ['category' => 'ibadah', 'name' => 'Tepat waktu salat', 'points' => 10, 'status' => 'active', 'sort_order' => 3],
            ['category' => 'ibadah', 'name' => 'Mengaji rutin', 'points' => 10, 'status' => 'active', 'sort_order' => 4],
            ['category' => 'sosial', 'name' => 'Membantu teman', 'points' => 5, 'status' => 'active', 'sort_order' => 5],
            ['category' => 'sosial', 'name' => 'Kerja bakti', 'points' => 5, 'status' => 'active', 'sort_order' => 6],
            ['category' => 'kedisiplinan', 'name' => 'Datang tepat waktu', 'points' => 5, 'status' => 'active', 'sort_order' => 7],
            ['category' => 'akademik', 'name' => 'Prestasi kelas', 'points' => 15, 'status' => 'active', 'sort_order' => 8],
            ['category' => 'akademik', 'name' => 'Juara lomba', 'points' => 25, 'status' => 'active', 'sort_order' => 9],
        ])->each(fn (array $data) => CharacterPointType::query()->create($data));
    }
}
