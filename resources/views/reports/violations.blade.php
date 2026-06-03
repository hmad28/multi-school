@extends('reports.layout')

@section('content')
<div class="title">Laporan Pelanggaran Siswa</div>
<div class="meta">Periode: {{ \Carbon\Carbon::parse($data['from'])->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($data['to'])->translatedFormat('d F Y') }}</div>
<table>
    <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Status</th><th>Catatan</th></tr></thead>
    <tbody>
        @forelse ($rows as $row)
            <tr><td>{{ $row->date->translatedFormat('d F Y') }}</td><td>{{ $row->student?->full_name }}</td><td>{{ $row->student?->schoolClass?->name }}</td><td>{{ $row->violationType?->name }}</td><td>{{ $row->points_snapshot }}</td><td>{{ $row->status }}</td><td>{{ $row->note ?? '-' }}</td></tr>
        @empty
            <tr><td colspan="7">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
