<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificates Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2196F3;
        }
        .header h1 {
            color: #2196F3;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        .filters {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filters strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2196F3;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-active {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-expiring_soon {
            color: #FF9800;
            font-weight: bold;
        }
        .status-expired {
            color: #F44336;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Certificates Status Report</h1>
        <p>ISO 9001:2015 Certificate Management</p>
        <p>Generated on: {{ $generatedAt->format('F d, Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(isset($filters['status']) && $filters['status'] !== 'all')
            Status: {{ ucfirst($filters['status']) }} |
        @endif
        @if(isset($filters['type']) && $filters['type'] !== 'all')
            Type: {{ ucwords(str_replace('_', ' ', $filters['type'])) }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Certificate Number</th>
                <th>Certificate Name</th>
                <th>Type</th>
                <th>Department</th>
                <th>Sector</th>
                <th>Issuing Authority</th>
                <th>Issue Date</th>
                <th>Expiry Date</th>
                <th>Days Until Expiry</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($certificates as $certificate)
            @php
                $daysUntilExpiry = now()->diffInDays($certificate->expiry_date, false);
            @endphp
            <tr>
                <td>{{ $certificate->certificate_number }}</td>
                <td>{{ $certificate->certificate_name }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $certificate->certificate_type)) }}</td>
                <td>{{ $certificate->department?->name ?? 'N/A' }}</td>
                <td>{{ $certificate->sector?->name ?? 'N/A' }}</td>
                <td>{{ $certificate->issuing_authority }}</td>
                <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                <td>{{ $certificate->expiry_date->format('Y-m-d') }}</td>
                <td>
                    @if($daysUntilExpiry > 0)
                        {{ $daysUntilExpiry }} days
                    @else
                        <span style="color: #F44336; font-weight: bold;">{{ abs($daysUntilExpiry) }} days overdue</span>
                    @endif
                </td>
                <td class="status-{{ $certificate->status }}">{{ ucfirst(str_replace('_', ' ', $certificate->status)) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                    No certificates found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Page 1 | Total Certificates: {{ $certificates->count() }} | Confidential - ISO 9001:2015 QMS</p>
    </div>
</body>
</html>
