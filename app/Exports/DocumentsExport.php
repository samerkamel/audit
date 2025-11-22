<?php

namespace App\Exports;

use App\Models\Document;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocumentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Document::with(['category', 'department', 'sector', 'owner']);

        // Apply filters
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['category_id']) && $this->filters['category_id'] !== 'all') {
            $query->where('category_id', $this->filters['category_id']);
        }

        return $query->orderBy('document_number', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Document Number',
            'Title',
            'Category',
            'Revision',
            'Department',
            'Sector',
            'Owner',
            'Issue Date',
            'Review Date',
            'Status',
            'Description',
        ];
    }

    public function map($document): array
    {
        return [
            $document->document_number,
            $document->title,
            $document->category?->name ?? 'N/A',
            $document->revision ?? '0',
            $document->department?->name ?? 'N/A',
            $document->sector?->name ?? 'N/A',
            $document->owner?->name ?? 'N/A',
            $document->issue_date?->format('Y-m-d') ?? 'N/A',
            $document->review_date?->format('Y-m-d') ?? 'N/A',
            ucfirst($document->status),
            $document->description ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '9C27B0']]],
        ];
    }
}
