<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // CAR Notifications
            [
                'code' => 'car_issued',
                'name' => 'CAR Issued',
                'category' => 'car',
                'description' => 'Sent when a new CAR is issued to a department',
                'email_subject' => 'CAR Issued: {car_number}',
                'email_body' => "Hello {user_name},\n\nA Corrective Action Request (CAR) has been issued to your department.\n\nCAR Number: {car_number}\nSubject: {car_subject}\nPriority: {car_priority}\n\nPlease review and submit your response as soon as possible.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Issued to Your Department',
                'notification_message' => 'CAR {car_number} has been issued: {car_subject}',
                'notification_icon' => 'tabler-alert-circle',
                'notification_color' => 'warning',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'car_priority', 'car_due_date', 'department_name', 'action_url', 'app_name'],
            ],
            [
                'code' => 'car_approval_required',
                'name' => 'CAR Approval Required',
                'category' => 'car',
                'description' => 'Sent when a CAR requires approval',
                'email_subject' => 'Approval Required: CAR {car_number}',
                'email_body' => "Hello {user_name},\n\nCAR {car_number} requires your approval.\n\nSubject: {car_subject}\nPriority: {car_priority}\n\nPlease review and approve or reject this CAR.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Approval Required',
                'notification_message' => 'CAR {car_number} requires your approval',
                'notification_icon' => 'tabler-clipboard-check',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'car_priority', 'action_url', 'app_name'],
            ],
            [
                'code' => 'car_rejected',
                'name' => 'CAR Rejected',
                'category' => 'car',
                'description' => 'Sent when a CAR response is rejected',
                'email_subject' => 'CAR Response Rejected: {car_number}',
                'email_body' => "Hello {user_name},\n\nYour response to CAR {car_number} has been rejected.\n\nRejection Reason: {rejection_reason}\n\nPlease review and resubmit your response.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Response Rejected',
                'notification_message' => 'Your response to CAR {car_number} has been rejected',
                'notification_icon' => 'tabler-x',
                'notification_color' => 'danger',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'rejection_reason', 'action_url', 'app_name'],
            ],
            [
                'code' => 'car_closed',
                'name' => 'CAR Closed',
                'category' => 'car',
                'description' => 'Sent when a CAR is closed',
                'email_subject' => 'CAR Closed: {car_number}',
                'email_body' => "Hello {user_name},\n\nCAR {car_number} has been closed successfully.\n\nSubject: {car_subject}\n\nThank you for your cooperation.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Closed',
                'notification_message' => 'CAR {car_number} has been closed',
                'notification_icon' => 'tabler-check',
                'notification_color' => 'success',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'action_url', 'app_name'],
            ],
            [
                'code' => 'car_due',
                'name' => 'CAR Due Reminder',
                'category' => 'car',
                'description' => 'Sent when a CAR response is due soon',
                'email_subject' => 'Reminder: CAR {car_number} Due on {car_due_date}',
                'email_body' => "Hello {user_name},\n\nThis is a reminder that CAR {car_number} is due on {car_due_date}.\n\nSubject: {car_subject}\nPriority: {car_priority}\n\nPlease ensure your response is submitted before the deadline.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Due Soon',
                'notification_message' => 'CAR {car_number} is due on {car_due_date}',
                'notification_icon' => 'tabler-clock',
                'notification_color' => 'warning',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'car_priority', 'car_due_date', 'action_url', 'app_name'],
            ],

            // Audit Notifications
            [
                'code' => 'audit_scheduled',
                'name' => 'Audit Scheduled',
                'category' => 'audit',
                'description' => 'Sent when a new audit is scheduled',
                'email_subject' => 'Audit Scheduled: {audit_number}',
                'email_body' => "Hello {user_name},\n\nA new audit has been scheduled.\n\nAudit Number: {audit_number}\nType: {audit_type}\nDate: {audit_date}\nDepartment: {department_name}\n\nPlease prepare the necessary documentation.\n\nBest regards,\n{app_name}",
                'notification_title' => 'New Audit Scheduled',
                'notification_message' => 'Audit {audit_number} scheduled for {audit_date}',
                'notification_icon' => 'tabler-calendar',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'audit_number', 'audit_type', 'audit_date', 'department_name', 'action_url', 'app_name'],
            ],

            // External Audit Notifications
            [
                'code' => 'external_audit_scheduled',
                'name' => 'External Audit Scheduled',
                'category' => 'external_audit',
                'description' => 'Sent when an external audit is scheduled',
                'email_subject' => 'External Audit Scheduled: {audit_number}',
                'email_body' => "Hello {user_name},\n\nAn external audit has been scheduled.\n\nAudit Number: {audit_number}\nCertification Body: {certification_body}\nStandard: {standard}\nScheduled Date: {audit_date}\n\nPlease coordinate with the audit team.\n\nBest regards,\n{app_name}",
                'notification_title' => 'External Audit Scheduled',
                'notification_message' => 'External audit {audit_number} scheduled for {audit_date}',
                'notification_icon' => 'tabler-file-certificate',
                'notification_color' => 'primary',
                'available_placeholders' => ['user_name', 'audit_number', 'certification_body', 'standard', 'audit_date', 'action_url', 'app_name'],
            ],
            [
                'code' => 'external_audit_started',
                'name' => 'External Audit Started',
                'category' => 'external_audit',
                'description' => 'Sent when an external audit begins',
                'email_subject' => 'External Audit Started: {audit_number}',
                'email_body' => "Hello {user_name},\n\nExternal audit {audit_number} has started.\n\nPlease ensure all necessary personnel are available.\n\nBest regards,\n{app_name}",
                'notification_title' => 'External Audit Started',
                'notification_message' => 'External audit {audit_number} has started',
                'notification_icon' => 'tabler-player-play',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'audit_number', 'action_url', 'app_name'],
            ],
            [
                'code' => 'external_audit_completed',
                'name' => 'External Audit Completed',
                'category' => 'external_audit',
                'description' => 'Sent when an external audit is completed',
                'email_subject' => 'External Audit Completed: {audit_number}',
                'email_body' => "Hello {user_name},\n\nExternal audit {audit_number} has been completed.\n\nResult: {audit_result}\nMajor NCRs: {major_ncrs}\nMinor NCRs: {minor_ncrs}\n\nPlease review the audit findings.\n\nBest regards,\n{app_name}",
                'notification_title' => 'External Audit Completed',
                'notification_message' => 'External audit {audit_number} completed with result: {audit_result}',
                'notification_icon' => 'tabler-check',
                'notification_color' => 'success',
                'available_placeholders' => ['user_name', 'audit_number', 'audit_result', 'major_ncrs', 'minor_ncrs', 'action_url', 'app_name'],
            ],

            // Certificate Notifications
            [
                'code' => 'certificate_expiry',
                'name' => 'Certificate Expiring Soon',
                'category' => 'certificate',
                'description' => 'Sent when a certificate is about to expire',
                'email_subject' => 'Certificate Expiring: {certificate_number}',
                'email_body' => "Hello {user_name},\n\nThe following certificate is expiring soon:\n\nCertificate: {certificate_number}\nStandard: {certificate_standard}\nExpiry Date: {certificate_expiry}\n\nPlease initiate the renewal process.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Certificate Expiring Soon',
                'notification_message' => 'Certificate {certificate_number} expires on {certificate_expiry}',
                'notification_icon' => 'tabler-award',
                'notification_color' => 'warning',
                'available_placeholders' => ['user_name', 'certificate_number', 'certificate_standard', 'certificate_expiry', 'action_url', 'app_name'],
            ],
            [
                'code' => 'certificate_status_changed',
                'name' => 'Certificate Status Changed',
                'category' => 'certificate',
                'description' => 'Sent when a certificate status changes',
                'email_subject' => 'Certificate Status Update: {certificate_number}',
                'email_body' => "Hello {user_name},\n\nThe status of certificate {certificate_number} has been updated.\n\nNew Status: {certificate_status}\n\nPlease review the certificate details.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Certificate Status Changed',
                'notification_message' => 'Certificate {certificate_number} status changed to {certificate_status}',
                'notification_icon' => 'tabler-refresh',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'certificate_number', 'certificate_status', 'action_url', 'app_name'],
            ],

            // Document Notifications
            [
                'code' => 'document_review',
                'name' => 'Document Review Required',
                'category' => 'document',
                'description' => 'Sent when a document needs review',
                'email_subject' => 'Document Review Required: {document_number}',
                'email_body' => "Hello {user_name},\n\nThe following document requires your review:\n\nDocument: {document_number}\nTitle: {document_title}\n\nPlease review and provide your feedback.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Document Review Required',
                'notification_message' => 'Document {document_number} requires your review',
                'notification_icon' => 'tabler-file-search',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'document_number', 'document_title', 'action_url', 'app_name'],
            ],
            [
                'code' => 'document_status_changed',
                'name' => 'Document Status Changed',
                'category' => 'document',
                'description' => 'Sent when a document status changes',
                'email_subject' => 'Document Status Update: {document_number}',
                'email_body' => "Hello {user_name},\n\nThe status of document {document_number} has been updated.\n\nTitle: {document_title}\nNew Status: {document_status}\n\nPlease review the document.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Document Status Changed',
                'notification_message' => 'Document {document_number} status changed to {document_status}',
                'notification_icon' => 'tabler-file-text',
                'notification_color' => 'primary',
                'available_placeholders' => ['user_name', 'document_number', 'document_title', 'document_status', 'action_url', 'app_name'],
            ],

            // Complaint Notifications
            [
                'code' => 'complaint_assigned',
                'name' => 'Complaint Assigned',
                'category' => 'complaint',
                'description' => 'Sent when a complaint is assigned to someone',
                'email_subject' => 'Complaint Assigned: {complaint_number}',
                'email_body' => "Hello {user_name},\n\nA customer complaint has been assigned to you.\n\nComplaint: {complaint_number}\nSubject: {complaint_subject}\n\nPlease investigate and respond as soon as possible.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Complaint Assigned to You',
                'notification_message' => 'Complaint {complaint_number} has been assigned to you',
                'notification_icon' => 'tabler-message-report',
                'notification_color' => 'warning',
                'available_placeholders' => ['user_name', 'complaint_number', 'complaint_subject', 'complaint_status', 'action_url', 'app_name'],
            ],

            // Audit Invitation Reminders
            [
                'code' => 'audit_invitation_2_weeks',
                'name' => 'Audit Invitation - 2 Weeks',
                'category' => 'audit',
                'description' => 'Sent 2 weeks before an audit to invite participants',
                'email_subject' => 'Audit Invitation: {audit_number} - 2 Weeks Notice',
                'email_body' => "Hello {user_name},\n\nThis is an invitation to participate in an upcoming audit.\n\nAudit Details:\n- Audit Number: {audit_number}\n- Type: {audit_type}\n- Scheduled Date: {audit_date}\n- Department: {department_name}\n- Lead Auditor: {lead_auditor}\n\nYou have 2 weeks to prepare the required documentation and ensure your team is ready.\n\nRequired Documentation:\n1. Process procedures and work instructions\n2. Records and evidence of implementation\n3. Previous audit findings and corrective actions\n4. Training records (if applicable)\n\nPlease confirm your availability and prepare accordingly.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Audit Invitation - 2 Weeks Notice',
                'notification_message' => 'Audit {audit_number} scheduled in 2 weeks. Please prepare required documentation.',
                'notification_icon' => 'tabler-calendar-event',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'audit_number', 'audit_type', 'audit_date', 'department_name', 'lead_auditor', 'action_url', 'app_name'],
            ],
            [
                'code' => 'audit_invitation_1_week',
                'name' => 'Audit Invitation - 1 Week',
                'category' => 'audit',
                'description' => 'Sent 1 week before an audit as a reminder',
                'email_subject' => 'Reminder: Audit {audit_number} - 1 Week Away',
                'email_body' => "Hello {user_name},\n\nThis is a reminder that your scheduled audit is in ONE WEEK.\n\nAudit Details:\n- Audit Number: {audit_number}\n- Type: {audit_type}\n- Scheduled Date: {audit_date}\n- Department: {department_name}\n- Lead Auditor: {lead_auditor}\n\nFinal Preparation Checklist:\n- [ ] All requested documentation is ready\n- [ ] Process owners are briefed\n- [ ] Meeting room/location is confirmed\n- [ ] Previous corrective actions are closed\n\nPlease ensure all preparations are complete.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Audit Reminder - 1 Week Away',
                'notification_message' => 'Audit {audit_number} is scheduled in 1 week. Final preparations required.',
                'notification_icon' => 'tabler-calendar-time',
                'notification_color' => 'warning',
                'available_placeholders' => ['user_name', 'audit_number', 'audit_type', 'audit_date', 'department_name', 'lead_auditor', 'action_url', 'app_name'],
            ],

            // Audit Report Notification
            [
                'code' => 'audit_report_issued',
                'name' => 'Audit Report Issued',
                'category' => 'audit',
                'description' => 'Sent when an audit report is completed and issued',
                'email_subject' => 'Audit Report Issued: {audit_number}',
                'email_body' => "Hello {user_name},\n\nThe audit report for {audit_number} has been issued.\n\nAudit Summary:\n- Audit Number: {audit_number}\n- Department: {department_name}\n- Audit Date: {audit_date}\n- Lead Auditor: {lead_auditor}\n\nFindings Summary:\n- Compliant Items: {compliant_count}\n- Observations: {observation_count}\n- Non-Conformances: {nc_count}\n\nPlease review the audit report and address any findings within the specified timeframe.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Audit Report Issued',
                'notification_message' => 'Audit report for {audit_number} has been issued. Review findings and take action.',
                'notification_icon' => 'tabler-report',
                'notification_color' => 'primary',
                'available_placeholders' => ['user_name', 'audit_number', 'audit_type', 'audit_date', 'department_name', 'lead_auditor', 'compliant_count', 'observation_count', 'nc_count', 'action_url', 'app_name'],
            ],

            // CAR Reminder
            [
                'code' => 'car_reminder',
                'name' => 'CAR Response Reminder',
                'category' => 'car',
                'description' => 'Sent as a reminder when CAR response is pending',
                'email_subject' => 'Reminder: CAR {car_number} Response Required',
                'email_body' => "Hello {user_name},\n\nThis is a reminder that CAR {car_number} requires your response.\n\nCAR Details:\n- CAR Number: {car_number}\n- Subject: {car_subject}\n- Priority: {car_priority}\n- Due Date: {car_due_date}\n- Days Remaining: {days_remaining}\n\nPlease submit your response including:\n1. Root cause analysis\n2. Immediate correction taken\n3. Corrective action to prevent recurrence\n4. Target completion dates\n\nFailure to respond may result in escalation to senior management.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Response Reminder',
                'notification_message' => 'CAR {car_number} response is pending. {days_remaining} days remaining.',
                'notification_icon' => 'tabler-bell-ringing',
                'notification_color' => 'warning',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'car_priority', 'car_due_date', 'days_remaining', 'action_url', 'app_name'],
            ],

            // CAR Response Accepted
            [
                'code' => 'car_response_accepted',
                'name' => 'CAR Response Accepted',
                'category' => 'car',
                'description' => 'Sent when a CAR response is accepted by quality team',
                'email_subject' => 'CAR Response Accepted: {car_number}',
                'email_body' => "Hello {user_name},\n\nGreat news! Your response to CAR {car_number} has been accepted.\n\nCAR Details:\n- CAR Number: {car_number}\n- Subject: {car_subject}\n\nAccepted Actions:\n- Correction Target Date: {correction_target_date}\n- Corrective Action Target Date: {corrective_action_target_date}\n\nNext Steps:\n1. Implement the correction by {correction_target_date}\n2. Complete the corrective action by {corrective_action_target_date}\n3. Provide evidence of implementation\n4. Await effectiveness review\n\nThank you for your timely response.\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Response Accepted',
                'notification_message' => 'Your response to CAR {car_number} has been accepted. Proceed with implementation.',
                'notification_icon' => 'tabler-circle-check',
                'notification_color' => 'success',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'correction_target_date', 'corrective_action_target_date', 'action_url', 'app_name'],
            ],

            // CAR Response Rejected
            [
                'code' => 'car_response_rejected',
                'name' => 'CAR Response Rejected',
                'category' => 'car',
                'description' => 'Sent when a CAR response is rejected and needs revision',
                'email_subject' => 'Action Required: CAR {car_number} Response Rejected',
                'email_body' => "Hello {user_name},\n\nYour response to CAR {car_number} has been reviewed and requires revision.\n\nCAR Details:\n- CAR Number: {car_number}\n- Subject: {car_subject}\n- Priority: {car_priority}\n\nRejection Feedback:\n{rejection_reason}\n\nPlease address the feedback and resubmit your response as soon as possible.\n\nCommon reasons for rejection:\n- Root cause analysis is incomplete or incorrect\n- Corrective action does not address the root cause\n- Target dates are not realistic\n- Missing evidence or supporting documentation\n\nBest regards,\n{app_name}",
                'notification_title' => 'CAR Response Rejected - Revision Required',
                'notification_message' => 'Your response to CAR {car_number} was rejected. Please review feedback and resubmit.',
                'notification_icon' => 'tabler-alert-circle',
                'notification_color' => 'danger',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'car_priority', 'rejection_reason', 'action_url', 'app_name'],
            ],

            // CAR Escalation
            [
                'code' => 'car_escalation',
                'name' => 'CAR Escalation',
                'category' => 'car',
                'description' => 'Sent when a CAR is escalated due to non-response',
                'email_subject' => '[ESCALATION] CAR {car_number} - No Response for {days_overdue} Days',
                'email_body' => "Dear {user_name},\n\n**ESCALATION NOTICE**\n\nThis is an escalation notice for CAR {car_number}.\n\nCAR Details:\n- CAR Number: {car_number}\n- Subject: {car_subject}\n- Priority: {car_priority}\n- Responsible Department: {department_name}\n- Issue Date: {issued_date}\n- Working Days Without Response: {days_overdue}\n\nReason for Escalation:\nThe responsible department has not submitted a response within the expected timeframe.\n\nRecommended Action:\nPlease follow up with the responsible department to ensure immediate action is taken.\n\nThis matter requires your urgent attention.\n\nBest regards,\n{app_name}",
                'notification_title' => 'ESCALATION: CAR No Response',
                'notification_message' => 'ESCALATION: CAR {car_number} has had no response for {days_overdue} working days.',
                'notification_icon' => 'tabler-alert-triangle',
                'notification_color' => 'danger',
                'available_placeholders' => ['user_name', 'car_number', 'car_subject', 'car_priority', 'department_name', 'issued_date', 'days_overdue', 'action_url', 'app_name'],
            ],

            // Improvement Opportunity Notifications
            [
                'code' => 'io_issued',
                'name' => 'Improvement Opportunity Issued',
                'category' => 'improvement_opportunity',
                'description' => 'Sent when an improvement opportunity is issued to a department',
                'email_subject' => 'Improvement Opportunity Issued: {io_number}',
                'email_body' => "Hello {user_name},\n\nAn Improvement Opportunity has been issued to your department.\n\nIO Details:\n- IO Number: {io_number}\n- Subject: {io_subject}\n- Priority: {io_priority}\n\nDescription:\n{io_description}\n\nSuggested Improvement:\n{io_suggestion}\n\nPlease review and submit your improvement plan.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Improvement Opportunity Issued',
                'notification_message' => 'IO {io_number} has been issued to your department.',
                'notification_icon' => 'tabler-bulb',
                'notification_color' => 'info',
                'available_placeholders' => ['user_name', 'io_number', 'io_subject', 'io_priority', 'io_description', 'io_suggestion', 'department_name', 'action_url', 'app_name'],
            ],
            [
                'code' => 'io_closed',
                'name' => 'Improvement Opportunity Closed',
                'category' => 'improvement_opportunity',
                'description' => 'Sent when an improvement opportunity is closed',
                'email_subject' => 'Improvement Opportunity Closed: {io_number}',
                'email_body' => "Hello {user_name},\n\nImprovement Opportunity {io_number} has been successfully closed.\n\nIO Details:\n- IO Number: {io_number}\n- Subject: {io_subject}\n\nThank you for your contribution to continuous improvement.\n\nBest regards,\n{app_name}",
                'notification_title' => 'Improvement Opportunity Closed',
                'notification_message' => 'IO {io_number} has been closed successfully.',
                'notification_icon' => 'tabler-circle-check',
                'notification_color' => 'success',
                'available_placeholders' => ['user_name', 'io_number', 'io_subject', 'action_url', 'app_name'],
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                ['code' => $template['code']],
                $template
            );
        }

        $this->command->info('Notification templates seeded successfully!');
    }
}
