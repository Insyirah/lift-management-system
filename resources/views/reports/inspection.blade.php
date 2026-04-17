<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lift Inspection Report - {{ $reportNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.5;
        }

        /* ─── Page Layout ─── */
        .page { padding: 20px 30px; }

        /* ─── Header ─── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .company-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            border-radius: 4px;
        }
        .company-logo-placeholder {
            width: 64px;
            height: 64px;
            background: #1d4ed8;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 20px;
            letter-spacing: 1px;
        }
        .company-info h1 { font-size: 16px; font-weight: 700; color: #1d4ed8; }
        .company-info p { font-size: 9px; color: #555; margin-top: 1px; }

        .header-right { text-align: right; }
        .report-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            background: #1d4ed8;
            padding: 5px 12px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .report-meta { font-size: 9px; color: #555; margin-top: 6px; }
        .report-meta strong { color: #1a1a2e; }

        /* ─── Overall Result Badge ─── */
        .result-banner {
            text-align: center;
            padding: 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }
        .result-banner.pass { background: #dcfce7; color: #166534; border: 2px solid #16a34a; }
        .result-banner.fail { background: #fee2e2; color: #991b1b; border: 2px solid #dc2626; }

        /* ─── Section Titles ─── */
        .section-title {
            font-size: 10px;
            font-weight: 700;
            color: #fff;
            background: #1d4ed8;
            padding: 4px 10px;
            border-radius: 3px;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ─── Info Grids ─── */
        .info-grid {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }
        .info-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 10px;
        }
        .info-row { display: flex; margin-bottom: 3px; }
        .info-label { width: 38%; font-weight: 600; color: #475569; font-size: 9px; }
        .info-value { width: 62%; color: #1a1a2e; font-size: 9px; }

        /* ─── Checklist Table ─── */
        .checklist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9px;
        }
        .checklist-table thead tr {
            background: #1d4ed8;
            color: #fff;
        }
        .checklist-table thead th {
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        .checklist-table tbody tr:nth-child(even) { background: #f8fafc; }
        .checklist-table tbody tr:nth-child(odd)  { background: #fff; }
        .checklist-table tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .checklist-table .category-header td {
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 700;
            font-size: 9px;
            padding: 4px 8px;
        }

        /* ─── Result Pills ─── */
        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .pill-pass { background: #dcfce7; color: #166534; }
        .pill-fail { background: #fee2e2; color: #991b1b; }
        .pill-na   { background: #f1f5f9; color: #64748b; }

        /* ─── Photos ─── */
        .photo-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .photo-item { width: 120px; }
        .photo-item img { width: 120px; height: 85px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; }
        .photo-caption { font-size: 8px; color: #64748b; margin-top: 2px; text-align: center; }

        /* ─── Summary Stats ─── */
        .stats-row { display: flex; gap: 8px; margin-bottom: 14px; }
        .stat-box {
            flex: 1;
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
        }
        .stat-box .stat-number { font-size: 20px; font-weight: 700; }
        .stat-box .stat-label  { font-size: 8px; color: #64748b; margin-top: 2px; }
        .stat-box.total  { background: #eff6ff; } .stat-box.total .stat-number  { color: #1d4ed8; }
        .stat-box.passed { background: #f0fdf4; } .stat-box.passed .stat-number { color: #16a34a; }
        .stat-box.failed { background: #fef2f2; } .stat-box.failed .stat-number { color: #dc2626; }
        .stat-box.na     { background: #f8fafc; } .stat-box.na .stat-number     { color: #64748b; }

        /* ─── Signature Section ─── */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }
        .sig-block { text-align: center; width: 45%; }
        .sig-image { height: 50px; margin: 0 auto 6px; display: block; }
        .sig-line  { border-top: 1px solid #1a1a2e; padding-top: 4px; font-size: 9px; }
        .sig-name  { font-weight: 700; font-size: 10px; }
        .sig-title { color: #64748b; font-size: 8px; }

        /* ─── Footer ─── */
        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #94a3b8;
        }

        /* ─── Notes ─── */
        .notes-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 14px;
            font-size: 9px;
            color: #92400e;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ═══════════════════════════════════════ HEADER ═══════════════════════════════════════ --}}
    @php
        $company      = $inspection->lift->building->organisation->company;
        $organisation = $inspection->lift->building->organisation;
        $building     = $inspection->lift->building;
        $lift         = $inspection->lift;
        $inspector    = $inspection->inspector;
        $results      = $inspection->results->load('item');

        $totalItems  = $results->count();
        $passCount   = $results->where('result', 'pass')->count();
        $failCount   = $results->where('result', 'fail')->count();
        $naCount     = $results->where('result', 'na')->count();
        $overallPass = $failCount === 0 && $totalItems > 0;

        $grouped = $results->groupBy('item.category');
        $photos  = $results->filter(fn($r) => !empty($r->photo_path));
    @endphp

    <div class="header">
        <div class="header-left">
            @if($company->logo_path && file_exists(storage_path('app/public/' . $company->logo_path)))
                <img src="{{ storage_path('app/public/' . $company->logo_path) }}" class="company-logo" alt="Logo">
            @else
                <div class="company-logo-placeholder">
                    {{ strtoupper(substr($company->name, 0, 2)) }}
                </div>
            @endif
            <div class="company-info">
                <h1>{{ $company->name }}</h1>
                <p>Reg. No: {{ $company->registration_no }}</p>
                <p>{{ $company->address }}</p>
                <p>{{ $company->phone }} &nbsp;|&nbsp; {{ $company->email }}</p>
            </div>
        </div>

        <div class="header-right">
            <div class="report-title">LIFT INSPECTION REPORT</div>
            <div class="report-meta">
                <div><strong>Report No:</strong> {{ $reportNumber }}</div>
                <div><strong>Generated:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                <div><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $inspection->inspection_type)) }}</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ OVERALL RESULT BANNER ═══════════════════════════ --}}
    <div class="result-banner {{ $overallPass ? 'pass' : 'fail' }}">
        OVERALL INSPECTION RESULT: {{ $overallPass ? 'PASS ✓' : 'FAIL ✗' }}
    </div>

    {{-- ═══════════════════════════ SUMMARY STATS ═══════════════════════════ --}}
    <div class="stats-row">
        <div class="stat-box total">
            <div class="stat-number">{{ $totalItems }}</div>
            <div class="stat-label">Total Items</div>
        </div>
        <div class="stat-box passed">
            <div class="stat-number">{{ $passCount }}</div>
            <div class="stat-label">Passed</div>
        </div>
        <div class="stat-box failed">
            <div class="stat-number">{{ $failCount }}</div>
            <div class="stat-label">Failed</div>
        </div>
        <div class="stat-box na">
            <div class="stat-number">{{ $naCount }}</div>
            <div class="stat-label">Not Applicable</div>
        </div>
    </div>

    {{-- ═══════════════════════════ ORGANISATION + BUILDING INFO ═══════════════════════════ --}}
    <div class="section-title">Client &amp; Building Information</div>
    <div class="info-grid">
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Organisation</span>
                <span class="info-value">{{ $organisation->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Reg. No.</span>
                <span class="info-value">{{ $organisation->registration_no ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Address</span>
                <span class="info-value">{{ $organisation->address }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Person</span>
                <span class="info-value">{{ $organisation->contact_person }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Phone</span>
                <span class="info-value">{{ $organisation->contact_phone }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $organisation->email ?? '—' }}</span>
            </div>
        </div>

        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Building</span>
                <span class="info-value">{{ $building->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Address</span>
                <span class="info-value">{{ $building->address }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">No. of Floors</span>
                <span class="info-value">{{ $building->number_of_floors }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Year Built</span>
                <span class="info-value">{{ $building->year_built ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ LIFT + INSPECTION INFO ═══════════════════════════ --}}
    <div class="section-title">Lift &amp; Inspection Details</div>
    <div class="info-grid">
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Lift Code</span>
                <span class="info-value">{{ $lift->lift_code }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Type</span>
                <span class="info-value">{{ ucfirst($lift->lift_type) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Brand / Model</span>
                <span class="info-value">{{ $lift->brand }} / {{ $lift->model }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Serial Number</span>
                <span class="info-value">{{ $lift->serial_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Capacity</span>
                <span class="info-value">{{ $lift->capacity ? $lift->capacity . ' kg' : '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Installation Date</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($lift->installation_date)->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $lift->status)) }}</span>
            </div>
        </div>

        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Inspection Date</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($inspection->inspection_date)->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Inspection Type</span>
                <span class="info-value">{{ ucfirst(str_replace('_', ' ', $inspection->inspection_type)) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Next Due Date</span>
                <span class="info-value">
                    {{ $inspection->next_due_date ? \Carbon\Carbon::parse($inspection->next_due_date)->format('d M Y') : '—' }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Inspector</span>
                <span class="info-value">{{ $inspector->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cert. Number</span>
                <span class="info-value">{{ $inspector->cert_number ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cert. Expiry</span>
                <span class="info-value">
                    {{ $inspector->cert_expiry ? \Carbon\Carbon::parse($inspector->cert_expiry)->format('d M Y') : '—' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ NOTES ═══════════════════════════ --}}
    @if($inspection->notes)
    <div class="section-title">Inspection Notes</div>
    <div class="notes-box">{{ $inspection->notes }}</div>
    @endif

    {{-- ═══════════════════════════ CHECKLIST TABLE ═══════════════════════════ --}}
    <div class="section-title">Inspection Checklist Results</div>

    <table class="checklist-table">
        <thead>
            <tr>
                <th style="width:4%">#</th>
                <th style="width:38%">Inspection Item</th>
                <th style="width:12%">Result</th>
                <th style="width:46%">Remark</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNum = 1; @endphp
            @foreach($grouped as $category => $items)
            <tr class="category-header">
                <td colspan="4">{{ $category }}</td>
            </tr>
            @foreach($items as $result)
            <tr>
                <td>{{ $rowNum++ }}</td>
                <td>{{ $result->item->name }}</td>
                <td>
                    @if($result->result === 'pass')
                        <span class="pill pill-pass">Pass</span>
                    @elseif($result->result === 'fail')
                        <span class="pill pill-fail">Fail</span>
                    @elseif($result->result === 'na')
                        <span class="pill pill-na">N/A</span>
                    @else
                        <span class="pill pill-na">—</span>
                    @endif
                </td>
                <td>{{ $result->remark ?? '—' }}</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- ═══════════════════════════ INSPECTION PHOTOS ═══════════════════════════ --}}
    @if($photos->count())
    <div class="section-title">Inspection Photos</div>
    <div class="photo-grid">
        @foreach($photos as $photo)
        @php $path = storage_path('app/public/' . $photo->photo_path); @endphp
        @if(file_exists($path))
        <div class="photo-item">
            <img src="{{ $path }}" alt="Inspection photo">
            <div class="photo-caption">{{ $photo->item->name }}</div>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    {{-- ═══════════════════════════ SIGNATURE ═══════════════════════════ --}}
    <div class="signature-section">
        <div class="sig-block">
            @if($inspector->signature_path && file_exists(storage_path('app/public/' . $inspector->signature_path)))
                <img src="{{ storage_path('app/public/' . $inspector->signature_path) }}"
                     class="sig-image" alt="Inspector Signature">
            @else
                <div style="height:50px; border-bottom:1px dashed #94a3b8; margin-bottom:6px;"></div>
            @endif
            <div class="sig-line">
                <div class="sig-name">{{ $inspector->name }}</div>
                <div class="sig-title">
                    Authorised Inspector
                    @if($inspector->cert_number)
                        &nbsp;|&nbsp; Cert: {{ $inspector->cert_number }}
                    @endif
                </div>
            </div>
        </div>

        <div class="sig-block">
            <div style="height:50px; margin-bottom:6px;"></div>
            <div class="sig-line">
                <div class="sig-name">Client Representative</div>
                <div class="sig-title">{{ $organisation->contact_person }} &nbsp;|&nbsp; {{ $organisation->name }}</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ FOOTER ═══════════════════════════ --}}
    <div class="footer">
        <span>{{ $company->name }} &nbsp;|&nbsp; {{ $company->phone }} &nbsp;|&nbsp; {{ $company->email }}</span>
        <span>Report No: {{ $reportNumber }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y') }}</span>
    </div>

</div>
</body>
</html>
