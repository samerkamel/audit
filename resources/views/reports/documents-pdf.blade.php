<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document Register</title>
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
            border-bottom: 2px solid #9C27B0;
        }
        .header h1 {
            color: #9C27B0;
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
            background-color: #9C27B0;
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
        .status-draft {
            color: #9E9E9E;
            font-weight: bold;
        }
        .status-active {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-under_review {
            color: #FF9800;
            font-weight: bold;
        }
        .status-obsolete {
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
        <h1>Document Register</h1>
        <p>ISO 9001:2015 Document Control</p>
        <p>Generated on: {{ $generatedAt->format('F d, Y H:i:s') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <strong>Filters Applied:</strong>
        @if(isset($filters['status']) && $filters['status'] !== 'all')
            Status: {{ ucfirst($filters['status']) }} |
        @endif
        @if(isset($filters['category_id']) && $filters['category_id'] !== 'all')
            Category ID: {{ $filters['category_id'] }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Document Number</th>
                <th>Title</th>
                <th>Category</th>
                <th>Revision</th>
                <th>Department</th>
                <th>Sector</th>
                <th>Owner</th>
                <th>Issue Date</th>
                <th>Review Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
            <tr>
                <td>{{ $document->document_number }}</td>
                <td style="max-width: 150px;">{{ \Illuminate\Support\Str::limit($document->title, 80) }}</td>
                <td>{{ $document->category?->name ?? 'N/A' }}</td>
                <td>{{ $document->revision ?? '0' }}</td>
                <td>{{ $document->department?->name ?? 'N/A' }}</td>
                <td>{{ $document->sector?->name ?? 'N/A' }}</td>
                <td>{{ $document->owner?->name ?? 'N/A' }}</td>
                <td>{{ $document->issue_date?->format('Y-m-d') ?? 'N/A' }}</td>
                <td>{{ $document->review_date?->format('Y-m-d') ?? 'N/A' }}</td>
                <td class="status-{{ $document->status }}">{{ ucfirst(str_replace('_', ' ', $document->status)) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px; color: #999;">
                    No documents found matching the selected filters.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Page 1 | Total Documents: {{ $documents->count() }} | Confidential - ISO 9001:2015 QMS</p>
    </div>
</body>
</html>
