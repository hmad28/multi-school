<?php

namespace App\Actions\Catalog;

use App\Models\CharacterPointType;
use App\Models\School;
use App\Models\ViolationThreshold;
use App\Models\ViolationType;
use Illuminate\Support\Facades\DB;

class SeedDefaultCatalogAction
{
    /**
     * Seed the default disciplinary & character-point catalog for a school.
     * Catalogs are per-tenant; every new school starts from this template.
     */
    public function execute(School $school): void
    {
        DB::transaction(function () use ($school): void {
            foreach ($this->violationTypes() as $data) {
                ViolationType::query()->create(['school_id' => $school->id] + $data);
            }

            foreach ($this->violationThresholds() as $data) {
                ViolationThreshold::query()->create(['school_id' => $school->id] + $data);
            }

            foreach ($this->characterPointTypes() as $data) {
                CharacterPointType::query()->create(['school_id' => $school->id] + $data);
            }
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function violationTypes(): array
    {
        return [
            ['category' => 'ringan', 'name' => 'Terlambat masuk kelas', 'points' => 5, 'status' => 'active', 'sort_order' => 1],
            ['category' => 'ringan', 'name' => 'Tidak membawa buku', 'points' => 5, 'status' => 'active', 'sort_order' => 2],
            ['category' => 'ringan', 'name' => 'Seragam tidak rapi', 'points' => 5, 'status' => 'active', 'sort_order' => 3],
            ['category' => 'sedang', 'name' => 'Membuang sampah sembarangan', 'points' => 10, 'status' => 'active', 'sort_order' => 4],
            ['category' => 'sedang', 'name' => 'Tidak mengerjakan PR', 'points' => 10, 'status' => 'active', 'sort_order' => 5],
            ['category' => 'sedang', 'name' => 'Berkata kurang sopan', 'points' => 15, 'status' => 'active', 'sort_order' => 6],
            ['category' => 'berat', 'name' => 'Membolos', 'points' => 25, 'status' => 'active', 'sort_order' => 7],
            ['category' => 'berat', 'name' => 'Berkelahi', 'points' => 50, 'status' => 'active', 'sort_order' => 8],
            ['category' => 'berat', 'name' => 'Merokok di lingkungan sekolah', 'points' => 50, 'status' => 'active', 'sort_order' => 9],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function violationThresholds(): array
    {
        return [
            ['points' => 25, 'label' => 'Peringatan Lisan', 'sort_order' => 1],
            ['points' => 50, 'label' => 'Peringatan Tertulis', 'sort_order' => 2],
            ['points' => 75, 'label' => 'Panggilan Orang Tua', 'sort_order' => 3],
            ['points' => 100, 'label' => 'Skorsing', 'sort_order' => 4],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function characterPointTypes(): array
    {
        return [
            ['category' => 'akhlak', 'name' => 'Jujur', 'points' => 5, 'status' => 'active', 'sort_order' => 1],
            ['category' => 'akhlak', 'name' => 'Santun', 'points' => 5, 'status' => 'active', 'sort_order' => 2],
            ['category' => 'ibadah', 'name' => 'Tepat waktu salat', 'points' => 10, 'status' => 'active', 'sort_order' => 3],
            ['category' => 'ibadah', 'name' => 'Mengaji rutin', 'points' => 10, 'status' => 'active', 'sort_order' => 4],
            ['category' => 'sosial', 'name' => 'Membantu teman', 'points' => 5, 'status' => 'active', 'sort_order' => 5],
            ['category' => 'sosial', 'name' => 'Kerja bakti', 'points' => 5, 'status' => 'active', 'sort_order' => 6],
            ['category' => 'kedisiplinan', 'name' => 'Datang tepat waktu', 'points' => 5, 'status' => 'active', 'sort_order' => 7],
            ['category' => 'akademik', 'name' => 'Prestasi kelas', 'points' => 15, 'status' => 'active', 'sort_order' => 8],
            ['category' => 'akademik', 'name' => 'Juara lomba', 'points' => 25, 'status' => 'active', 'sort_order' => 9],
        ];
    }
}
