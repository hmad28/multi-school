@extends('reports.layout')

@section('content')
<div class="title">Surat Panggilan Orang Tua/Wali</div>
<p>Kepada Yth. Orang Tua/Wali dari:</p>
<table>
    <tr><th>Nama Siswa</th><td>{{ $student->full_name }}</td></tr>
    <tr><th>NIS</th><td>{{ $student->nis }}</td></tr>
    <tr><th>Kelas</th><td>{{ $student->schoolClass?->name }}</td></tr>
    <tr><th>Total Poin Pelanggaran</th><td>{{ $totalPoints }}</td></tr>
</table>
<p>Dengan hormat, kami mengundang Bapak/Ibu untuk hadir ke sekolah guna membahas pembinaan siswa terkait catatan pelanggaran yang telah tervalidasi.</p>
<table>
    <thead><tr><th>Tanggal</th><th>Pelanggaran</th><th>Poin</th></tr></thead>
    <tbody>
        @forelse ($violations as $row)
            <tr><td>{{ $row->date->translatedFormat('d F Y') }}</td><td>{{ $row->violationType?->name }}</td><td>{{ $row->points_snapshot }}</td></tr>
        @empty
            <tr><td colspan="3">Tidak ada pelanggaran tervalidasi.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="signature">
    <div>{{ now()->translatedFormat('d F Y') }}</div>
    <div>Kepala Sekolah</div>
    <br><br><br>
    <strong>{{ $school?->principal_name ?? '-' }}</strong><br>
    <span>{{ $school?->principal_nip ? 'NIP. ' . $school->principal_nip : '' }}</span>
</div>
@endsection
