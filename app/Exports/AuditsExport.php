<?php

namespace App\Exports;

use App\Models\Audit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Audit::with(['department', 'sector', 'auditor', 'findings']);

        // Apply filters
        if (isset($this->filters['type']) && $this->filters['type'] !== 'all') {
            $query->where('audit_type', $this->filters['type']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('scheduled_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Audit Number',
            'Type',
            'Department',
            'Sector',
            'Auditor',
            'Scheduled Date',
            'Completion Date',
            'Status',
            'Findings Count',
            'Total Score',
            'Objective',
        ];
    }

    public function map($audit): array
    {
        return [
            $audit->audit_number,
            ucwords(str_replace('_', ' ', $audit->audit_type)),
            $audit->department?->name ?? 'N/A',
            $audit->sector?->name ?? 'N/A',
            $audit->auditor?->name ?? 'N/A',
            $audit->scheduled_date?->format('Y-m-d'),
            $audit->completion_date?->format('Y-m-d') ?? 'Not completed',
            ucfirst($audit->status),
            $audit->findings->count(),
            $audit->total_score ?? 'N/A',
            $audit->objective,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4CAF50']]],
        ];
    }
}
