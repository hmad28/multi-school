@extends('reports.layout')

@section('content')
<div class="title">Laporan Absensi Guru</div>
<div class="meta">Periode: {{ \Carbon\Carbon::parse($data['from'])->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($data['to'])->translatedFormat('d F Y') }}</div>
<table>
    <thead><tr><th>Tanggal</th><th>Guru</th><th>NIP</th><th>Status</th><th>Catatan</th></tr></thead>
    <tbody>
        @forelse ($rows as $row)
            <tr><td>{{ $row->date->translatedFormat('d F Y') }}</td><td>{{ $row->teacher?->full_name }}</td><td>{{ $row->teacher?->nip ?? '-' }}</td><td>{{ $row->status?->name }}</td><td>{{ $row->note ?? '-' }}</td></tr>
        @empty
            <tr><td colspan="5">Tidak ada data.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
