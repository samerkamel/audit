<?php

namespace App\Services;

use App\Models\AuditPlan;
use App\Models\Car;
use App\Models\Certificate;
use App\Models\Document;
use App\Models\CustomerComplaint;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class PdfExportService
{
    /**
     * Generate PDF for an audit plan.
     */
    public function generateAuditPlanPdf(AuditPlan $auditPlan): \Barryvdh\DomPDF\PDF
    {
        $auditPlan->load([
            'departments',
            'leadAuditor',
            'creator',
            'checklistGroups.auditQuestions',
        ]);

        $data = [
            'auditPlan' => $auditPlan,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.audit-plan', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Generate PDF for a CAR.
     */
    public function generateCarPdf(Car $car): \Barryvdh\DomPDF\PDF
    {
        $car->load([
            'fromDepartment',
            'toDepartment',
            'issuedBy',
            'approvedBy',
            'responses.respondedBy',
            'responses.reviewedBy',
            'followUps.followedUpBy',
        ]);

        $data = [
            'car' => $car,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.car', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Generate PDF for a certificate.
     */
    public function generateCertificatePdf(Certificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'certificate' => $certificate,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.certificate', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Generate PDF for a document.
     */
    public function generateDocumentPdf(Document $document): \Barryvdh\DomPDF\PDF
    {
        $data = [
            'document' => $document,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.document', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Generate PDF for a complaint.
     */
    public function generateComplaintPdf(CustomerComplaint $complaint): \Barryvdh\DomPDF\PDF
    {
        $complaint->load([
            'department',
            'assignedTo',
            'createdBy',
        ]);

        $data = [
            'complaint' => $complaint,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.complaint', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Generate audit schedule PDF (used for email attachments).
     */
    public function generateAuditSchedulePdf(AuditPlan $auditPlan): \Barryvdh\DomPDF\PDF
    {
        $auditPlan->load([
            'departments',
            'leadAuditor',
        ]);

        // Build schedule data
        $scheduleData = [];
        foreach ($auditPlan->departments as $department) {
            $scheduleData[] = [
                'department' => $department->name,
                'planned_start' => $department->pivot->planned_start_date,
                'planned_end' => $department->pivot->planned_end_date,
                'status' => $department->pivot->status,
            ];
        }

        $data = [
            'auditPlan' => $auditPlan,
            'schedule' => $scheduleData,
            'generatedAt' => now(),
        ];

        return Pdf::loadView('pdf.audit-schedule', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);
    }

    /**
     * Generate and return PDF content as string (for email attachments).
     */
    public function getAuditPlanPdfContent(AuditPlan $auditPlan): string
    {
        return $this->generateAuditPlanPdf($auditPlan)->output();
    }

    /**
     * Generate and return CAR PDF content as string (for email attachments).
     */
    public function getCarPdfContent(Car $car): string
    {
        return $this->generateCarPdf($car)->output();
    }

    /**
     * Get filename for audit plan PDF.
     */
    public function getAuditPlanFilename(AuditPlan $auditPlan): string
    {
        $slug = str_replace(' ', '_', strtolower($auditPlan->title));
        return "audit_plan_{$auditPlan->id}_{$slug}.pdf";
    }

    /**
     * Get filename for CAR PDF.
     */
    public function getCarFilename(Car $car): string
    {
        return "car_{$car->car_number}.pdf";
    }
}
