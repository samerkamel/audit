<?php

namespace App\Exports;

use App\Models\Certificate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CertificatesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Certificate::with(['department', 'sector']);

        // Apply filters
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['type']) && $this->filters['type'] !== 'all') {
            $query->where('certificate_type', $this->filters['type']);
        }

        return $query->orderBy('expiry_date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Certificate Number',
            'Certificate Name',
            'Type',
            'Department',
            'Sector',
            'Issuing Authority',
            'Issue Date',
            'Expiry Date',
            'Days Until Expiry',
            'Status',
            'Scope',
        ];
    }

    public function map($certificate): array
    {
        $daysUntilExpiry = now()->diffInDays($certificate->expiry_date, false);

        return [
            $certificate->certificate_number,
            $certificate->certificate_name,
            ucwords(str_replace('_', ' ', $certificate->certificate_type)),
            $certificate->department?->name ?? 'N/A',
            $certificate->sector?->name ?? 'N/A',
            $certificate->issuing_authority,
            $certificate->issue_date->format('Y-m-d'),
            $certificate->expiry_date->format('Y-m-d'),
            $daysUntilExpiry > 0 ? $daysUntilExpiry . ' days' : abs($daysUntilExpiry) . ' days overdue',
            ucfirst($certificate->status),
            $certificate->scope ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2196F3']]],
        ];
    }
}
