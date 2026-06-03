<?php

namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ViolationExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Jenis', 'Kategori', 'Poin', 'Status', 'Catatan'];
    }

    public function map($row): array
    {
        return [
            $row->date?->toDateString(),
            $row->student?->nis,
            $row->student?->name,
            $row->student?->schoolClass?->display_name,
            $row->violationType?->name,
            $row->category_snapshot,
            $row->points_snapshot,
            $row->status,
            $row->note,
        ];
    }
}
