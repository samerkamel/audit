<?php

namespace App\Exports;

use App\Models\CustomerComplaint;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComplaintsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = CustomerComplaint::with(['assignedTo', 'customer']);

        // Apply filters
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['priority']) && $this->filters['priority'] !== 'all') {
            $query->where('priority', $this->filters['priority']);
        }

        return $query->orderBy('complaint_number', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Complaint Number',
            'Customer Name',
            'Contact',
            'Priority',
            'Category',
            'Assigned To',
            'Received Date',
            'Due Date',
            'Status',
            'Subject',
            'Description',
        ];
    }

    public function map($complaint): array
    {
        return [
            $complaint->complaint_number,
            $complaint->customer_name,
            $complaint->customer_contact ?? 'N/A',
            ucfirst($complaint->priority),
            ucwords(str_replace('_', ' ', $complaint->category)),
            $complaint->assignedTo?->name ?? 'Unassigned',
            $complaint->received_date->format('Y-m-d'),
            $complaint->due_date?->format('Y-m-d') ?? 'N/A',
            ucfirst($complaint->status),
            $complaint->subject,
            $complaint->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF9800']]],
        ];
    }
}
