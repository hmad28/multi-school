<?php

namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CharacterPointExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jenis Poin', 'Kategori', 'Poin', 'Dicatat Oleh', 'Catatan'];
    }

    public function map($row): array
    {
        return [
            $row->date?->toDateString(),
            $row->student?->nis,
            $row->student?->name,
            $row->student?->schoolClass?->display_name,
            $row->characterPointType?->name,
            $row->category_snapshot,
            $row->points_snapshot,
            $row->recorder?->name,
            $row->note,
        ];
    }
}
