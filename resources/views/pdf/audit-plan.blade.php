<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Plan - {{ $auditPlan->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #4a6cf7;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1a1a1a;
        }
        .header .subtitle {
            margin: 8px 0 0;
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #f4f4f4;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-left: 4px solid #4a6cf7;
            font-weight: bold;
            font-size: 14px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px 0;
            width: 180px;
            color: #555;
        }
        .info-value {
            display: table-cell;
            padding: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-primary { background-color: #4a6cf7; color: #fff; }
        .badge-success { background-color: #28a745; color: #fff; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-info { background-color: #17a2b8; color: #fff; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Audit Plan Report</h1>
        <div class="subtitle">{{ $auditPlan->title }}</div>
    </div>

    <div class="section">
        <div class="section-title">General Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Audit Type:</div>
                <div class="info-value">{{ $auditPlan->audit_type_label }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="badge badge-{{ $auditPlan->status_color }}">
                        {{ ucfirst(str_replace('_', ' ', $auditPlan->status)) }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Lead Auditor:</div>
                <div class="info-value">{{ $auditPlan->leadAuditor?->name ?? 'Not Assigned' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Created By:</div>
                <div class="info-value">{{ $auditPlan->creator?->name ?? '-' }}</div>
            </div>
            @if($auditPlan->actual_start_date)
            <div class="info-row">
                <div class="info-label">Actual Start Date:</div>
                <div class="info-value">{{ $auditPlan->actual_start_date->format('F d, Y') }}</div>
            </div>
            @endif
            @if($auditPlan->actual_end_date)
            <div class="info-row">
                <div class="info-label">Actual End Date:</div>
                <div class="info-value">{{ $auditPlan->actual_end_date->format('F d, Y') }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($auditPlan->scope)
    <div class="section">
        <div class="section-title">Scope</div>
        <p>{{ $auditPlan->scope }}</p>
    </div>
    @endif

    @if($auditPlan->objectives)
    <div class="section">
        <div class="section-title">Objectives</div>
        <p>{{ $auditPlan->objectives }}</p>
    </div>
    @endif

    @if($auditPlan->description)
    <div class="section">
        <div class="section-title">Description</div>
        <p>{{ $auditPlan->description }}</p>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Departments Included</div>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Planned Start</th>
                    <th>Planned End</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditPlan->departments as $department)
                <tr>
                    <td>{{ $department->name }}</td>
                    <td>{{ $department->pivot->planned_start_date ? \Carbon\Carbon::parse($department->pivot->planned_start_date)->format('M d, Y') : '-' }}</td>
                    <td>{{ $department->pivot->planned_end_date ? \Carbon\Carbon::parse($department->pivot->planned_end_date)->format('M d, Y') : '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $department->pivot->status ?? 'pending')) }}</td>
                    <td>{{ $department->pivot->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Generated on {{ $generatedAt->format('F d, Y H:i') }}</p>
        <p>Audit Management System</p>
    </div>
</body>
</html>
