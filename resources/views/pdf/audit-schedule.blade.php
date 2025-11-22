<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Schedule - {{ $auditPlan->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1a1a1a;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4a6cf7;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-in_progress { background-color: #17a2b8; color: #fff; }
        .status-completed { background-color: #28a745; color: #fff; }
        .status-deferred { background-color: #6c757d; color: #fff; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Audit Schedule</h1>
        <p>{{ $auditPlan->title }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Audit Type:</span>
            {{ $auditPlan->audit_type_label }}
        </div>
        <div class="info-row">
            <span class="info-label">Lead Auditor:</span>
            {{ $auditPlan->leadAuditor?->name ?? 'Not Assigned' }}
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            {{ ucfirst(str_replace('_', ' ', $auditPlan->status)) }}
        </div>
        @if($auditPlan->scope)
        <div class="info-row">
            <span class="info-label">Scope:</span>
            {{ $auditPlan->scope }}
        </div>
        @endif
        @if($auditPlan->objectives)
        <div class="info-row">
            <span class="info-label">Objectives:</span>
            {{ $auditPlan->objectives }}
        </div>
        @endif
    </div>

    <h3>Department Schedule</h3>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Planned Start</th>
                <th>Planned End</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule as $item)
            <tr>
                <td>{{ $item['department'] }}</td>
                <td>{{ $item['planned_start'] ? \Carbon\Carbon::parse($item['planned_start'])->format('M d, Y') : '-' }}</td>
                <td>{{ $item['planned_end'] ? \Carbon\Carbon::parse($item['planned_end'])->format('M d, Y') : '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $item['status'] }}">
                        {{ ucfirst(str_replace('_', ' ', $item['status'] ?? 'pending')) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('F d, Y H:i') }} | Audit Management System</p>
    </div>
</body>
</html>
