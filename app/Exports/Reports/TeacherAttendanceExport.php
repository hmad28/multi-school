<?php

namespace App\Exports\Reports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TeacherAttendanceExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIP', 'Nama Guru', 'Jabatan', 'Status', 'Catatan'];
    }

    public function map($row): array
    {
        return [
            $row->date?->toDateString(),
            $row->teacher?->nip,
            $row->teacher?->full_name,
            $row->teacher?->position,
            $row->status?->name,
            $row->note,
        ];
    }
}
