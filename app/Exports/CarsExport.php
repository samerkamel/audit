<?php

namespace App\Exports;

use App\Models\Car;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CarsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Car::with(['department', 'sector', 'responsiblePerson', 'audit']);

        // Apply filters
        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['source']) && $this->filters['source'] !== 'all') {
            $query->where('source', $this->filters['source']);
        }

        return $query->orderBy('car_number', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'CAR Number',
            'Source',
            'Department',
            'Sector',
            'Responsible Person',
            'Finding',
            'Root Cause',
            'Corrective Action',
            'Due Date',
            'Status',
            'Effectiveness',
        ];
    }

    public function map($car): array
    {
        return [
            $car->car_number,
            ucwords(str_replace('_', ' ', $car->source)),
            $car->department?->name ?? 'N/A',
            $car->sector?->name ?? 'N/A',
            $car->responsiblePerson?->name ?? 'N/A',
            $car->description,
            $car->root_cause ?? 'Pending',
            $car->corrective_action ?? 'Pending',
            $car->due_date?->format('Y-m-d'),
            ucfirst($car->status),
            $car->effectiveness_check ? ucfirst($car->effectiveness_check) : 'Pending',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF5722']]],
        ];
    }
}
