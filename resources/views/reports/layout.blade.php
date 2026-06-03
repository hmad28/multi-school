<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .letterhead { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 20px; }
        .school-name { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .muted { color: #4b5563; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .title { font-size: 16px; font-weight: bold; text-align: center; margin: 16px 0 6px; }
        .meta { margin: 8px 0; }
        .signature { width: 220px; float: right; text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="letterhead">
        <div class="school-name">{{ $school?->name ?? 'Platform Sekolah' }}</div>
        <div class="muted">{{ $school?->address ?? '-' }}</div>
        <div class="muted">Telp. {{ $school?->phone ?? '-' }} &middot; Email {{ $school?->email ?? '-' }}</div>
    </div>

    @yield('content')
</body>
</html>
