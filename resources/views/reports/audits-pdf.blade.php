<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audits Report</title>
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
            border-bottom: 2px solid #4CAF50;
        }
        .header h1 {
            color: #4CAF50;
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
            background-color: #4CAF50;
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
        .status-scheduled {
            color: #2196F3;
            font-weight: bold;
        }
        .status-in_progress {
            color: #FF9800;
            font-weight: bold;
        }
        .status-completed {
            color: #4CAF50;
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
        <h1>Audits Report</h1>
        <p>ISO 9001:2015 Internal Audit Management System</p>
        <p>Generated on: {{ $generatedAt->format('F d, Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(isset($filters['type']) && $filters['type'] !== 'all')
            Type: {{ ucwords(str_replace('_', ' ', $filters['type'])) }} |
        @endif
        @if(isset($filters['status']) && $filters['status'] !== 'all')
            Status: {{ ucfirst($filters['status']) }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Audit Number</th>
                <th>Type</th>
                <th>Department</th>
                <th>Sector</th>
                <th>Auditor</th>
                <th>Scheduled Date</th>
                <th>Completion Date</th>
                <th>Status</th>
                <th>Findings</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($audits as $audit)
            <tr>
                <td>{{ $audit->audit_number }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $audit->audit_type)) }}</td>
                <td>{{ $audit->department?->name ?? 'N/A' }}</td>
                <td>{{ $audit->sector?->name ?? 'N/A' }}</td>
                <td>{{ $audit->auditor?->name ?? 'N/A' }}</td>
                <td>{{ $audit->scheduled_date?->format('Y-m-d') }}</td>
                <td>{{ $audit->completion_date?->format('Y-m-d') ?? 'Not completed' }}</td>
                <td class="status-{{ $audit->status }}">{{ ucfirst($audit->status) }}</td>
                <td>{{ $audit->findings->count() }}</td>
                <td>{{ $audit->total_score ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                    No audits found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Page 1 | Total Audits: {{ $audits->count() }} | Confidential - ISO 9001:2015 QMS</p>
    </div>
</body>
</html>
