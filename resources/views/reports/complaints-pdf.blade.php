<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Complaints Report</title>
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
            border-bottom: 2px solid #FF9800;
        }
        .header h1 {
            color: #FF9800;
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
            background-color: #FF9800;
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
        .priority-high {
            color: #F44336;
            font-weight: bold;
        }
        .priority-medium {
            color: #FF9800;
            font-weight: bold;
        }
        .priority-low {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-open {
            color: #2196F3;
            font-weight: bold;
        }
        .status-investigating {
            color: #FF9800;
            font-weight: bold;
        }
        .status-resolved {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-closed {
            color: #9E9E9E;
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
        <h1>Customer Complaints Report</h1>
        <p>ISO 9001:2015 Customer Complaint Management</p>
        <p>Generated on: {{ $generatedAt->format('F d, Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(isset($filters['status']) && $filters['status'] !== 'all')
            Status: {{ ucfirst($filters['status']) }} |
        @endif
        @if(isset($filters['priority']) && $filters['priority'] !== 'all')
            Priority: {{ ucfirst($filters['priority']) }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Complaint Number</th>
                <th>Customer Name</th>
                <th>Contact</th>
                <th>Priority</th>
                <th>Category</th>
                <th>Subject</th>
                <th>Assigned To</th>
                <th>Received Date</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($complaints as $complaint)
            <tr>
                <td>{{ $complaint->complaint_number }}</td>
                <td>{{ $complaint->customer_name }}</td>
                <td>{{ $complaint->customer_contact ?? 'N/A' }}</td>
                <td class="priority-{{ $complaint->priority }}">{{ ucfirst($complaint->priority) }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $complaint->category)) }}</td>
                <td style="max-width: 150px;">{{ \Illuminate\Support\Str::limit($complaint->subject, 100) }}</td>
                <td>{{ $complaint->assignedTo?->name ?? 'Unassigned' }}</td>
                <td>{{ $complaint->received_date->format('Y-m-d') }}</td>
                <td>{{ $complaint->due_date?->format('Y-m-d') ?? 'N/A' }}</td>
                <td class="status-{{ $complaint->status }}">{{ ucfirst($complaint->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                    No complaints found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Page 1 | Total Complaints: {{ $complaints->count() }} | Confidential - ISO 9001:2015 QMS</p>
    </div>
</body>
</html>
