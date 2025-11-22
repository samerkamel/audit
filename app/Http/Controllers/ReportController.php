<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Car;
use App\Models\Certificate;
use App\Models\CustomerComplaint;
use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditsExport;
use App\Exports\CarsExport;
use App\Exports\CertificatesExport;
use App\Exports\DocumentsExport;
use App\Exports\ComplaintsExport;

class ReportController extends Controller
{
    /**
     * Export audits to PDF
     */
    public function auditsPdf(Request $request)
    {
        $query = Audit::with(['department', 'sector', 'auditor', 'findings']);

        // Apply filters if provided
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('audit_type', $request->type);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $audits = $query->orderBy('scheduled_date', 'desc')->get();

        $pdf = Pdf::loadView('reports.audits-pdf', [
            'audits' => $audits,
            'filters' => $request->all(),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('audits-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export audits to Excel
     */
    public function auditsExcel(Request $request)
    {
        return Excel::download(
            new AuditsExport($request->all()),
            'audits-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export CARs to PDF
     */
    public function carsPdf(Request $request)
    {
        $query = Car::with(['department', 'sector', 'responsiblePerson', 'audit']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        $cars = $query->orderBy('car_number', 'desc')->get();

        $pdf = Pdf::loadView('reports.cars-pdf', [
            'cars' => $cars,
            'filters' => $request->all(),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('cars-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export CARs to Excel
     */
    public function carsExcel(Request $request)
    {
        return Excel::download(
            new CarsExport($request->all()),
            'cars-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export certificates to PDF
     */
    public function certificatesPdf(Request $request)
    {
        $query = Certificate::with(['department', 'sector']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('certificate_type', $request->type);
        }

        $certificates = $query->orderBy('expiry_date', 'asc')->get();

        $pdf = Pdf::loadView('reports.certificates-pdf', [
            'certificates' => $certificates,
            'filters' => $request->all(),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('certificates-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export certificates to Excel
     */
    public function certificatesExcel(Request $request)
    {
        return Excel::download(
            new CertificatesExport($request->all()),
            'certificates-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export documents to PDF
     */
    public function documentsPdf(Request $request)
    {
        $query = Document::with(['category', 'department', 'sector', 'owner']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        $documents = $query->orderBy('document_number', 'desc')->get();

        $pdf = Pdf::loadView('reports.documents-pdf', [
            'documents' => $documents,
            'filters' => $request->all(),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('documents-register-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export documents to Excel
     */
    public function documentsExcel(Request $request)
    {
        return Excel::download(
            new DocumentsExport($request->all()),
            'documents-register-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export complaints to PDF
     */
    public function complaintsPdf(Request $request)
    {
        $query = CustomerComplaint::with(['assignedTo', 'customer']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        $complaints = $query->orderBy('complaint_number', 'desc')->get();

        $pdf = Pdf::loadView('reports.complaints-pdf', [
            'complaints' => $complaints,
            'filters' => $request->all(),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('complaints-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export complaints to Excel
     */
    public function complaintsExcel(Request $request)
    {
        return Excel::download(
            new ComplaintsExport($request->all()),
            'complaints-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
