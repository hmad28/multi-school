@extends('reports.layout')

@section('content')
<div class="title">Laporan Absensi Siswa</div>
<div class="meta">Periode: {{ \Carbon\Carbon::parse($data['from'])->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($data['to'])->translatedFormat('d F Y') }}</div>
<table>
    <thead><tr><th>Tanggal</th><th>Siswa</th><th>NIS</th><th>Kelas</th><th>Status</th><th>Catatan</th></tr></thead>
    <tbody>
        @forelse ($rows as $row)
            <tr><td>{{ $row->date->translatedFormat('d F Y') }}</td><td>{{ $row->student?->full_name }}</td><td>{{ $row->student?->nis }}</td><td>{{ $row->student?->schoolClass?->name }}</td><td>{{ $row->status?->name }}</td><td>{{ $row->note ?? '-' }}</td></tr>
        @empty
            <tr><td colspan="6">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
