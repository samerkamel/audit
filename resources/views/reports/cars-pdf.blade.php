<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CARs Report</title>
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
            border-bottom: 2px solid #FF5722;
        }
        .header h1 {
            color: #FF5722;
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
            background-color: #FF5722;
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
        .status-open {
            color: #FF9800;
            font-weight: bold;
        }
        .status-in_progress {
            color: #2196F3;
            font-weight: bold;
        }
        .status-completed {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-verified {
            color: #9C27B0;
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
        <h1>Corrective Action Requests (CARs) Report</h1>
        <p>ISO 9001:2015 Quality Management System</p>
        <p>Generated on: {{ $generatedAt->format('F d, Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(isset($filters['status']) && $filters['status'] !== 'all')
            Status: {{ ucfirst($filters['status']) }} |
        @endif
        @if(isset($filters['source']) && $filters['source'] !== 'all')
            Source: {{ ucwords(str_replace('_', ' ', $filters['source'])) }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>CAR Number</th>
                <th>Source</th>
                <th>Department</th>
                <th>Sector</th>
                <th>Responsible</th>
                <th>Finding</th>
                <th>Root Cause</th>
                <th>Corrective Action</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Effectiveness</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cars as $car)
            <tr>
                <td>{{ $car->car_number }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $car->source)) }}</td>
                <td>{{ $car->department?->name ?? 'N/A' }}</td>
                <td>{{ $car->sector?->name ?? 'N/A' }}</td>
                <td>{{ $car->responsiblePerson?->name ?? 'N/A' }}</td>
                <td style="max-width: 150px;">{{ \Illuminate\Support\Str::limit($car->description, 100) }}</td>
                <td style="max-width: 120px;">{{ \Illuminate\Support\Str::limit($car->root_cause ?? 'Pending', 80) }}</td>
                <td style="max-width: 120px;">{{ \Illuminate\Support\Str::limit($car->corrective_action ?? 'Pending', 80) }}</td>
                <td>{{ $car->due_date?->format('Y-m-d') }}</td>
                <td class="status-{{ $car->status }}">{{ ucfirst($car->status) }}</td>
                <td>{{ $car->effectiveness_check ? ucfirst($car->effectiveness_check) : 'Pending' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center; padding: 20px; color: #999;">
                    No CARs found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Page 1 | Total CARs: {{ $cars->count() }} | Confidential - ISO 9001:2015 QMS</p>
    </div>
</body>
</html>
