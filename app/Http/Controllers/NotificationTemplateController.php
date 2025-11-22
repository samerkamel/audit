<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of notification templates.
     */
    public function index(Request $request)
    {
        $category = $request->get('category');

        $query = NotificationTemplate::query()->orderBy('category')->orderBy('name');

        if ($category) {
            $query->where('category', $category);
        }

        $templates = $query->get()->groupBy('category');
        $categories = NotificationTemplate::getCategories();

        return view('notification-templates.index', compact('templates', 'categories', 'category'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(NotificationTemplate $notificationTemplate)
    {
        $categories = NotificationTemplate::getCategories();
        $colors = NotificationTemplate::getColors();
        $icons = NotificationTemplate::getIcons();

        return view('notification-templates.edit', compact('notificationTemplate', 'categories', 'colors', 'icons'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'email_subject' => 'required|string|max:255',
            'email_body' => 'required|string',
            'notification_title' => 'required|string|max:255',
            'notification_message' => 'required|string',
            'notification_icon' => 'required|string|max:50',
            'notification_color' => 'required|in:primary,success,warning,danger,info',
            'send_email' => 'boolean',
            'send_database' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Handle checkbox values
        $validated['send_email'] = $request->boolean('send_email');
        $validated['send_database'] = $request->boolean('send_database');
        $validated['is_active'] = $request->boolean('is_active');

        $notificationTemplate->update($validated);

        return redirect()
            ->route('notification-templates.index')
            ->with('success', "Template '{$notificationTemplate->name}' updated successfully.");
    }

    /**
     * Toggle template active status.
     */
    public function toggleStatus(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->update([
            'is_active' => !$notificationTemplate->is_active,
        ]);

        $status = $notificationTemplate->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Template '{$notificationTemplate->name}' {$status} successfully.");
    }

    /**
     * Preview template with sample data.
     */
    public function preview(Request $request, NotificationTemplate $notificationTemplate)
    {
        // Generate sample data based on placeholders
        $sampleData = $this->generateSampleData($notificationTemplate);

        $preview = [
            'email_subject' => $notificationTemplate->renderEmailSubject($sampleData),
            'email_body' => $notificationTemplate->renderEmailBody($sampleData),
            'notification_title' => $notificationTemplate->renderNotificationTitle($sampleData),
            'notification_message' => $notificationTemplate->renderNotificationMessage($sampleData),
        ];

        return response()->json($preview);
    }

    /**
     * Reset template to default values.
     */
    public function reset(NotificationTemplate $notificationTemplate)
    {
        $defaults = $this->getDefaultTemplate($notificationTemplate->code);

        if ($defaults) {
            $notificationTemplate->update($defaults);

            return redirect()
                ->route('notification-templates.edit', $notificationTemplate)
                ->with('success', 'Template reset to default values.');
        }

        return redirect()
            ->back()
            ->with('error', 'Default template not found.');
    }

    /**
     * Generate sample data for preview.
     */
    protected function generateSampleData(NotificationTemplate $template): array
    {
        $placeholders = $template->available_placeholders ?? [];
        $sampleData = [];

        $defaults = [
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'car_number' => 'CAR-2025-001',
            'car_subject' => 'Equipment Calibration Issue',
            'car_priority' => 'High',
            'car_status' => 'Issued',
            'car_due_date' => date('Y-m-d', strtotime('+7 days')),
            'department_name' => 'Quality Assurance',
            'audit_number' => 'AUD-2025-001',
            'audit_date' => date('Y-m-d'),
            'audit_type' => 'Internal Audit',
            'document_number' => 'DOC-QMS-001',
            'document_title' => 'Quality Manual',
            'document_status' => 'Pending Review',
            'certificate_number' => 'CERT-ISO-2025-001',
            'certificate_standard' => 'ISO 9001:2015',
            'certificate_expiry' => date('Y-m-d', strtotime('+30 days')),
            'complaint_number' => 'COMP-2025-001',
            'complaint_subject' => 'Product Quality Issue',
            'complaint_status' => 'New',
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'action_url' => config('app.url') . '/sample-action',
        ];

        foreach ($placeholders as $placeholder) {
            $sampleData[$placeholder] = $defaults[$placeholder] ?? "[{$placeholder}]";
        }

        // Always include common placeholders
        $sampleData['user_name'] = $defaults['user_name'];
        $sampleData['app_name'] = $defaults['app_name'];

        return $sampleData;
    }

    /**
     * Get default template content by code.
     */
    protected function getDefaultTemplate(string $code): ?array
    {
        $defaults = [
            'car_issued' => [
                'email_subject' => 'CAR Issued: {car_number}',
                'email_body' => "Hello {user_name},\n\nA Corrective Action Request (CAR) has been issued to your department.\n\nCAR Number: {car_number}\nSubject: {car_subject}\nPriority: {car_priority}\n\nPlease review and submit your response as soon as possible.\n\nThank you.",
                'notification_title' => 'CAR Issued to Your Department',
                'notification_message' => 'CAR {car_number} has been issued: {car_subject}',
            ],
            'car_due' => [
                'email_subject' => 'CAR Due Reminder: {car_number}',
                'email_body' => "Hello {user_name},\n\nThis is a reminder that CAR {car_number} is due on {car_due_date}.\n\nPlease ensure your response is submitted before the deadline.\n\nThank you.",
                'notification_title' => 'CAR Due Soon',
                'notification_message' => 'CAR {car_number} is due on {car_due_date}',
            ],
            // Add more defaults as needed
        ];

        return $defaults[$code] ?? null;
    }
}
