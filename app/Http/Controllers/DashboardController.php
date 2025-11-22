<?php

namespace App\Http\Controllers;

use App\Models\AuditPlan;
use App\Models\AuditResponse;
use App\Models\Car;
use App\Models\CustomerComplaint;
use App\Models\ExternalAudit;
use App\Models\Certificate;
use App\Models\Document;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Overall Statistics
        $stats = [
            'total_audit_plans' => AuditPlan::count(),
            'active_audit_plans' => AuditPlan::where('status', 'in_progress')->count(),
            'completed_audits' => AuditPlan::where('status', 'completed')->count(),
            'pending_audits' => AuditPlan::where('status', 'scheduled')->count(),

            'total_cars' => Car::count(),
            'open_cars' => Car::whereIn('status', ['issued', 'in_progress'])->count(),
            'closed_cars' => Car::where('status', 'closed')->count(),
            'overdue_cars' => 0, // No due_date field in cars table

            'total_complaints' => CustomerComplaint::count(),
            'open_complaints' => CustomerComplaint::where('status', 'open')->count(),
            'unresolved_complaints' => CustomerComplaint::whereIn('status', ['open', 'in_progress'])->count(),

            'total_external_audits' => ExternalAudit::count(),
            'scheduled_external_audits' => ExternalAudit::where('status', 'scheduled')->count(),
            'upcoming_external_audits' => ExternalAudit::where('status', 'scheduled')
                ->where('scheduled_start_date', '>=', now())
                ->where('scheduled_start_date', '<=', now()->addMonth())
                ->count(),

            'total_certificates' => Certificate::count(),
            'valid_certificates' => Certificate::where('status', 'valid')->count(),
            'expiring_certificates' => Certificate::where('status', 'expiring_soon')->count(),
            'expired_certificates' => Certificate::where('status', 'expired')->count(),

            'total_documents' => Document::count(),
            'effective_documents' => Document::where('status', 'effective')->count(),
            'pending_review_documents' => Document::where('status', 'pending_review')->count(),
            'pending_approval_documents' => Document::where('status', 'pending_approval')->count(),
            'documents_needing_review' => Document::where('status', 'effective')
                ->whereDate('next_review_date', '<=', now())
                ->count(),
        ];

        // Recent Activities - Latest 10 items across all modules
        $recentActivities = collect();

        // Recent Audit Plans
        $recentAudits = AuditPlan::with('leadAuditor')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($audit) {
                return [
                    'type' => 'audit',
                    'icon' => 'clipboard-check',
                    'color' => 'primary',
                    'title' => $audit->title ?? 'Audit',
                    'description' => $audit->status === 'completed' ? 'Completed' : 'Scheduled',
                    'date' => $audit->updated_at,
                    'url' => route('audit-plans.show', $audit),
                ];
            });

        // Recent CARs
        $recentCars = Car::with('toDepartment')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($car) {
                return [
                    'type' => 'car',
                    'icon' => 'alert-circle',
                    'color' => 'danger',
                    'title' => $car->car_number ?? 'CAR',
                    'description' => $car->subject ?? $car->ncr_description,
                    'date' => $car->updated_at,
                    'url' => route('cars.show', $car),
                ];
            });

        // Recent Complaints
        $recentComplaints = CustomerComplaint::with('customer', 'assignedTo')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($complaint) {
                return [
                    'type' => 'complaint',
                    'icon' => 'message-report',
                    'color' => 'warning',
                    'title' => $complaint->complaint_number,
                    'description' => $complaint->subject,
                    'date' => $complaint->updated_at,
                    'url' => route('complaints.show', $complaint),
                ];
            });

        // Recent Documents
        $recentDocuments = Document::with('owner')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($doc) {
                return [
                    'type' => 'document',
                    'icon' => 'file-text',
                    'color' => 'info',
                    'title' => $doc->document_number,
                    'description' => $doc->title,
                    'date' => $doc->updated_at,
                    'url' => route('documents.show', $doc),
                ];
            });

        $recentActivities = $recentActivities
            ->concat($recentAudits)
            ->concat($recentCars)
            ->concat($recentComplaints)
            ->concat($recentDocuments)
            ->sortByDesc('date')
            ->take(10);

        // Audit Completion Trends (last 6 months)
        $auditTrends = AuditPlan::selectRaw('DATE_FORMAT(updated_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month');

        // CAR Status Distribution
        $carStatusDistribution = Car::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Complaint Priority Distribution
        $complaintPriorityDistribution = CustomerComplaint::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        // Document Status Distribution
        $documentStatusDistribution = Document::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Upcoming External Audits (next 3 months)
        $upcomingExternalAudits = ExternalAudit::where('status', 'scheduled')
            ->where('scheduled_start_date', '>=', now())
            ->where('scheduled_start_date', '<=', now()->addMonths(3))
            ->orderBy('scheduled_start_date')
            ->take(5)
            ->get();

        // Certificates Expiring Soon (next 6 months)
        $expiringCertificates = Certificate::where('status', 'valid')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addMonths(6))
            ->orderBy('expiry_date')
            ->take(5)
            ->get();

        // Documents Needing Review
        $documentsNeedingReview = Document::where('status', 'effective')
            ->whereDate('next_review_date', '<=', now())
            ->orderBy('next_review_date')
            ->take(5)
            ->get();

        // Recent CARs (instead of overdue since no due_date column)
        $overdueCars = Car::where('status', '!=', 'closed')
            ->orderBy('issued_date', 'desc')
            ->take(5)
            ->get();

        // Department Performance (count users)
        $departmentPerformance = Department::withCount('users')->get();

        return view('dashboard.index', compact(
            'stats',
            'recentActivities',
            'auditTrends',
            'carStatusDistribution',
            'complaintPriorityDistribution',
            'documentStatusDistribution',
            'upcomingExternalAudits',
            'expiringCertificates',
            'documentsNeedingReview',
            'overdueCars',
            'departmentPerformance'
        ));
    }
}
