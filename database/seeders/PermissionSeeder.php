<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Module 1: User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'module' => 'User Management', 'description' => 'View user list and details'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'module' => 'User Management', 'description' => 'Create new user accounts'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'module' => 'User Management', 'description' => 'Edit existing users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module' => 'User Management', 'description' => 'Delete user accounts'],
            ['name' => 'users.activate_deactivate', 'display_name' => 'Activate/Deactivate Users', 'module' => 'User Management', 'description' => 'Enable or disable user accounts'],

            // Module 2: Role & Permission Management
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'module' => 'Role Management', 'description' => 'View roles list'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'module' => 'Role Management', 'description' => 'Create new roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'module' => 'Role Management', 'description' => 'Edit existing roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'module' => 'Role Management', 'description' => 'Delete roles'],
            ['name' => 'roles.assign_permissions', 'display_name' => 'Assign Permissions', 'module' => 'Role Management', 'description' => 'Assign permissions to roles'],
            ['name' => 'permissions.view', 'display_name' => 'View Permissions', 'module' => 'Role Management', 'description' => 'View permissions list'],

            // Module 3: Organizational Structure
            ['name' => 'sectors.view', 'display_name' => 'View Sectors', 'module' => 'Organization', 'description' => 'View sectors'],
            ['name' => 'sectors.create', 'display_name' => 'Create Sectors', 'module' => 'Organization', 'description' => 'Create new sectors'],
            ['name' => 'sectors.edit', 'display_name' => 'Edit Sectors', 'module' => 'Organization', 'description' => 'Edit sectors'],
            ['name' => 'sectors.delete', 'display_name' => 'Delete Sectors', 'module' => 'Organization', 'description' => 'Delete sectors'],
            ['name' => 'departments.view', 'display_name' => 'View Departments', 'module' => 'Organization', 'description' => 'View departments'],
            ['name' => 'departments.create', 'display_name' => 'Create Departments', 'module' => 'Organization', 'description' => 'Create new departments'],
            ['name' => 'departments.edit', 'display_name' => 'Edit Departments', 'module' => 'Organization', 'description' => 'Edit departments'],
            ['name' => 'departments.delete', 'display_name' => 'Delete Departments', 'module' => 'Organization', 'description' => 'Delete departments'],

            // Module 4: Auditor Management
            ['name' => 'auditors.view', 'display_name' => 'View Auditors', 'module' => 'Auditor Management', 'description' => 'View auditor list'],
            ['name' => 'auditors.create', 'display_name' => 'Create Auditors', 'module' => 'Auditor Management', 'description' => 'Create new auditors'],
            ['name' => 'auditors.edit', 'display_name' => 'Edit Auditors', 'module' => 'Auditor Management', 'description' => 'Edit auditor profiles'],
            ['name' => 'auditors.delete', 'display_name' => 'Delete Auditors', 'module' => 'Auditor Management', 'description' => 'Delete auditors'],
            ['name' => 'auditors.assign_competencies', 'display_name' => 'Assign Competencies', 'module' => 'Auditor Management', 'description' => 'Manage auditor competencies'],
            ['name' => 'auditors.assign_departments', 'display_name' => 'Assign Departments', 'module' => 'Auditor Management', 'description' => 'Assign auditors to departments'],
            ['name' => 'auditors.manage_certificates', 'display_name' => 'Manage Certificates', 'module' => 'Auditor Management', 'description' => 'Manage auditor certificates'],
            ['name' => 'auditors.view_availability', 'display_name' => 'View Availability', 'module' => 'Auditor Management', 'description' => 'View auditor availability'],
            ['name' => 'auditors.edit_availability', 'display_name' => 'Edit Availability', 'module' => 'Auditor Management', 'description' => 'Edit auditor availability'],

            // Module 5: Question Bank & Checklists
            ['name' => 'iso_standards.view', 'display_name' => 'View ISO Standards', 'module' => 'Question Bank', 'description' => 'View ISO standards'],
            ['name' => 'iso_standards.create', 'display_name' => 'Create ISO Standards', 'module' => 'Question Bank', 'description' => 'Create ISO standards'],
            ['name' => 'iso_standards.edit', 'display_name' => 'Edit ISO Standards', 'module' => 'Question Bank', 'description' => 'Edit ISO standards'],
            ['name' => 'iso_standards.delete', 'display_name' => 'Delete ISO Standards', 'module' => 'Question Bank', 'description' => 'Delete ISO standards'],
            ['name' => 'iso_clauses.view', 'display_name' => 'View ISO Clauses', 'module' => 'Question Bank', 'description' => 'View ISO clauses'],
            ['name' => 'iso_clauses.create', 'display_name' => 'Create ISO Clauses', 'module' => 'Question Bank', 'description' => 'Create ISO clauses'],
            ['name' => 'iso_clauses.edit', 'display_name' => 'Edit ISO Clauses', 'module' => 'Question Bank', 'description' => 'Edit ISO clauses'],
            ['name' => 'iso_clauses.delete', 'display_name' => 'Delete ISO Clauses', 'module' => 'Question Bank', 'description' => 'Delete ISO clauses'],
            ['name' => 'procedures.view', 'display_name' => 'View Procedures', 'module' => 'Question Bank', 'description' => 'View procedures'],
            ['name' => 'procedures.create', 'display_name' => 'Create Procedures', 'module' => 'Question Bank', 'description' => 'Create procedures'],
            ['name' => 'procedures.edit', 'display_name' => 'Edit Procedures', 'module' => 'Question Bank', 'description' => 'Edit procedures'],
            ['name' => 'procedures.delete', 'display_name' => 'Delete Procedures', 'module' => 'Question Bank', 'description' => 'Delete procedures'],
            ['name' => 'questions.view', 'display_name' => 'View Questions', 'module' => 'Question Bank', 'description' => 'View questions'],
            ['name' => 'questions.create', 'display_name' => 'Create Questions', 'module' => 'Question Bank', 'description' => 'Create questions'],
            ['name' => 'questions.edit', 'display_name' => 'Edit Questions', 'module' => 'Question Bank', 'description' => 'Edit questions'],
            ['name' => 'questions.delete', 'display_name' => 'Delete Questions', 'module' => 'Question Bank', 'description' => 'Delete questions'],
            ['name' => 'checklists.view', 'display_name' => 'View Checklists', 'module' => 'Question Bank', 'description' => 'View checklists'],
            ['name' => 'checklists.create', 'display_name' => 'Create Checklists', 'module' => 'Question Bank', 'description' => 'Create checklists'],
            ['name' => 'checklists.edit', 'display_name' => 'Edit Checklists', 'module' => 'Question Bank', 'description' => 'Edit checklists'],
            ['name' => 'checklists.delete', 'display_name' => 'Delete Checklists', 'module' => 'Question Bank', 'description' => 'Delete checklists'],
            ['name' => 'checklists.assign_departments', 'display_name' => 'Assign Checklists to Departments', 'module' => 'Question Bank', 'description' => 'Assign checklists to departments'],

            // Module 6: Annual Audit Planning
            ['name' => 'annual_plans.view', 'display_name' => 'View Annual Plans', 'module' => 'Audit Planning', 'description' => 'View annual audit plans'],
            ['name' => 'annual_plans.create', 'display_name' => 'Create Annual Plans', 'module' => 'Audit Planning', 'description' => 'Create annual audit plans'],
            ['name' => 'annual_plans.edit', 'display_name' => 'Edit Annual Plans', 'module' => 'Audit Planning', 'description' => 'Edit annual audit plans'],
            ['name' => 'annual_plans.delete', 'display_name' => 'Delete Annual Plans', 'module' => 'Audit Planning', 'description' => 'Delete annual audit plans'],
            ['name' => 'annual_plans.approve', 'display_name' => 'Approve Annual Plans', 'module' => 'Audit Planning', 'description' => 'Approve annual audit plans'],

            // Module 7: Audit Execution
            ['name' => 'audits.view_all', 'display_name' => 'View All Audits', 'module' => 'Audit Execution', 'description' => 'View all audits system-wide'],
            ['name' => 'audits.view_own', 'display_name' => 'View Own Audits', 'module' => 'Audit Execution', 'description' => 'View own audits'],
            ['name' => 'audits.view_department', 'display_name' => 'View Department Audits', 'module' => 'Audit Execution', 'description' => 'View department audits'],
            ['name' => 'audits.create', 'display_name' => 'Create Audits', 'module' => 'Audit Execution', 'description' => 'Create new audits'],
            ['name' => 'audits.edit', 'display_name' => 'Edit Audits', 'module' => 'Audit Execution', 'description' => 'Edit audits'],
            ['name' => 'audits.delete', 'display_name' => 'Delete Audits', 'module' => 'Audit Execution', 'description' => 'Delete audits'],
            ['name' => 'audits.schedule', 'display_name' => 'Schedule Audits', 'module' => 'Audit Execution', 'description' => 'Schedule audits'],
            ['name' => 'audits.reschedule', 'display_name' => 'Reschedule Audits', 'module' => 'Audit Execution', 'description' => 'Reschedule audits'],
            ['name' => 'audits.assign_auditors', 'display_name' => 'Assign Auditors', 'module' => 'Audit Execution', 'description' => 'Assign auditors to audits'],
            ['name' => 'audits.conduct', 'display_name' => 'Conduct Audits', 'module' => 'Audit Execution', 'description' => 'Perform audits'],
            ['name' => 'audits.add_findings', 'display_name' => 'Add Findings', 'module' => 'Audit Execution', 'description' => 'Add audit findings'],
            ['name' => 'audits.edit_findings', 'display_name' => 'Edit Findings', 'module' => 'Audit Execution', 'description' => 'Edit audit findings'],

            // Module 8: Audit Reports
            ['name' => 'audit_reports.view_all', 'display_name' => 'View All Reports', 'module' => 'Audit Reports', 'description' => 'View all audit reports'],
            ['name' => 'audit_reports.view_own', 'display_name' => 'View Own Reports', 'module' => 'Audit Reports', 'description' => 'View own audit reports'],
            ['name' => 'audit_reports.view_department', 'display_name' => 'View Department Reports', 'module' => 'Audit Reports', 'description' => 'View department audit reports'],
            ['name' => 'audit_reports.create', 'display_name' => 'Create Reports', 'module' => 'Audit Reports', 'description' => 'Create audit reports'],
            ['name' => 'audit_reports.edit', 'display_name' => 'Edit Reports', 'module' => 'Audit Reports', 'description' => 'Edit audit reports'],
            ['name' => 'audit_reports.submit_for_approval', 'display_name' => 'Submit for Approval', 'module' => 'Audit Reports', 'description' => 'Submit reports for approval'],
            ['name' => 'audit_reports.approve', 'display_name' => 'Approve Reports', 'module' => 'Audit Reports', 'description' => 'Approve audit reports'],
            ['name' => 'audit_reports.send', 'display_name' => 'Send Reports', 'module' => 'Audit Reports', 'description' => 'Send audit reports'],
            ['name' => 'audit_reports.export', 'display_name' => 'Export Reports', 'module' => 'Audit Reports', 'description' => 'Export audit reports'],

            // Module 9: CAR Management
            ['name' => 'cars.view_all', 'display_name' => 'View All CARs', 'module' => 'CAR Management', 'description' => 'View all CARs'],
            ['name' => 'cars.view_own_issued', 'display_name' => 'View Own Issued CARs', 'module' => 'CAR Management', 'description' => 'View own issued CARs'],
            ['name' => 'cars.view_department', 'display_name' => 'View Department CARs', 'module' => 'CAR Management', 'description' => 'View department CARs'],
            ['name' => 'cars.create', 'display_name' => 'Create CARs', 'module' => 'CAR Management', 'description' => 'Create CARs'],
            ['name' => 'cars.edit', 'display_name' => 'Edit CARs', 'module' => 'CAR Management', 'description' => 'Edit CARs'],
            ['name' => 'cars.delete', 'display_name' => 'Delete CARs', 'module' => 'CAR Management', 'description' => 'Delete CARs'],
            ['name' => 'cars.submit_for_approval', 'display_name' => 'Submit for Approval', 'module' => 'CAR Management', 'description' => 'Submit CARs for approval'],
            ['name' => 'cars.approve', 'display_name' => 'Approve CARs', 'module' => 'CAR Management', 'description' => 'Approve CARs'],
            ['name' => 'cars.issue', 'display_name' => 'Issue CARs', 'module' => 'CAR Management', 'description' => 'Issue CARs'],
            ['name' => 'cars.respond', 'display_name' => 'Respond to CARs', 'module' => 'CAR Management', 'description' => 'Respond to CARs'],
            ['name' => 'cars.review_response', 'display_name' => 'Review Responses', 'module' => 'CAR Management', 'description' => 'Review CAR responses'],
            ['name' => 'cars.accept_response', 'display_name' => 'Accept Responses', 'module' => 'CAR Management', 'description' => 'Accept CAR responses'],
            ['name' => 'cars.reject_response', 'display_name' => 'Reject Responses', 'module' => 'CAR Management', 'description' => 'Reject CAR responses'],
            ['name' => 'cars.follow_up', 'display_name' => 'Follow Up CARs', 'module' => 'CAR Management', 'description' => 'Follow up on CARs'],
            ['name' => 'cars.close', 'display_name' => 'Close CARs', 'module' => 'CAR Management', 'description' => 'Close CARs'],
            ['name' => 'cars.export', 'display_name' => 'Export CARs', 'module' => 'CAR Management', 'description' => 'Export CAR data'],

            // Module 10: External Audits
            ['name' => 'external_audits.view', 'display_name' => 'View External Audits', 'module' => 'External Audits', 'description' => 'View external audits'],
            ['name' => 'external_audits.create', 'display_name' => 'Create External Audits', 'module' => 'External Audits', 'description' => 'Create external audits'],
            ['name' => 'external_audits.edit', 'display_name' => 'Edit External Audits', 'module' => 'External Audits', 'description' => 'Edit external audits'],
            ['name' => 'external_audits.delete', 'display_name' => 'Delete External Audits', 'module' => 'External Audits', 'description' => 'Delete external audits'],
            ['name' => 'external_audits.upload_documents', 'display_name' => 'Upload Documents', 'module' => 'External Audits', 'description' => 'Upload external audit documents'],
            ['name' => 'external_audits.delete_documents', 'display_name' => 'Delete Documents', 'module' => 'External Audits', 'description' => 'Delete external audit documents'],

            // Module 11: Customer Complaints
            ['name' => 'complaints.view_all', 'display_name' => 'View All Complaints', 'module' => 'Complaints', 'description' => 'View all complaints'],
            ['name' => 'complaints.view_department', 'display_name' => 'View Department Complaints', 'module' => 'Complaints', 'description' => 'View department complaints'],
            ['name' => 'complaints.create', 'display_name' => 'Create Complaints', 'module' => 'Complaints', 'description' => 'Create complaints'],
            ['name' => 'complaints.edit', 'display_name' => 'Edit Complaints', 'module' => 'Complaints', 'description' => 'Edit complaints'],
            ['name' => 'complaints.delete', 'display_name' => 'Delete Complaints', 'module' => 'Complaints', 'description' => 'Delete complaints'],
            ['name' => 'complaints.assign_department', 'display_name' => 'Assign Department', 'module' => 'Complaints', 'description' => 'Assign complaints to departments'],
            ['name' => 'complaints.respond', 'display_name' => 'Respond to Complaints', 'module' => 'Complaints', 'description' => 'Respond to complaints'],
            ['name' => 'complaints.review_response', 'display_name' => 'Review Responses', 'module' => 'Complaints', 'description' => 'Review complaint responses'],
            ['name' => 'complaints.accept_response', 'display_name' => 'Accept Responses', 'module' => 'Complaints', 'description' => 'Accept complaint responses'],
            ['name' => 'complaints.reject_response', 'display_name' => 'Reject Responses', 'module' => 'Complaints', 'description' => 'Reject complaint responses'],
            ['name' => 'complaints.close', 'display_name' => 'Close Complaints', 'module' => 'Complaints', 'description' => 'Close complaints'],
            ['name' => 'complaints.create_car', 'display_name' => 'Create CAR from Complaint', 'module' => 'Complaints', 'description' => 'Create CAR from complaint'],
            ['name' => 'complaints.export', 'display_name' => 'Export Complaints', 'module' => 'Complaints', 'description' => 'Export complaint data'],

            // Module 12: Document Modifications
            ['name' => 'mod_requests.view_all', 'display_name' => 'View All Modification Requests', 'module' => 'Document Modifications', 'description' => 'View all modification requests'],
            ['name' => 'mod_requests.view_own', 'display_name' => 'View Own Modification Requests', 'module' => 'Document Modifications', 'description' => 'View own modification requests'],
            ['name' => 'mod_requests.create', 'display_name' => 'Create Modification Requests', 'module' => 'Document Modifications', 'description' => 'Create modification requests'],
            ['name' => 'mod_requests.edit', 'display_name' => 'Edit Modification Requests', 'module' => 'Document Modifications', 'description' => 'Edit modification requests'],
            ['name' => 'mod_requests.approve', 'display_name' => 'Approve Modification Requests', 'module' => 'Document Modifications', 'description' => 'Approve modification requests'],
            ['name' => 'mod_requests.reject', 'display_name' => 'Reject Modification Requests', 'module' => 'Document Modifications', 'description' => 'Reject modification requests'],
            ['name' => 'mod_log.view', 'display_name' => 'View Modification Log', 'module' => 'Document Modifications', 'description' => 'View modification log'],
            ['name' => 'mod_log.create', 'display_name' => 'Create Modification Log', 'module' => 'Document Modifications', 'description' => 'Create modification log entries'],

            // Module 13: Reports & Analytics
            ['name' => 'reports.view_audit_completion', 'display_name' => 'View Audit Completion', 'module' => 'Reports & Analytics', 'description' => 'View audit completion reports'],
            ['name' => 'reports.view_car_log', 'display_name' => 'View CAR Log', 'module' => 'Reports & Analytics', 'description' => 'View CAR log reports'],
            ['name' => 'reports.view_department_performance', 'display_name' => 'View Department Performance', 'module' => 'Reports & Analytics', 'description' => 'View department performance reports'],
            ['name' => 'reports.view_auditor_performance', 'display_name' => 'View Auditor Performance', 'module' => 'Reports & Analytics', 'description' => 'View auditor performance reports'],
            ['name' => 'reports.view_compliance_trends', 'display_name' => 'View Compliance Trends', 'module' => 'Reports & Analytics', 'description' => 'View compliance trend reports'],
            ['name' => 'reports.view_management_dashboard', 'display_name' => 'View Management Dashboard', 'module' => 'Reports & Analytics', 'description' => 'View management dashboard'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'module' => 'Reports & Analytics', 'description' => 'Export reports'],

            // Module 14: Notifications
            ['name' => 'notifications.view_own', 'display_name' => 'View Own Notifications', 'module' => 'Notifications', 'description' => 'View own notifications'],
            ['name' => 'notifications.mark_read', 'display_name' => 'Mark as Read', 'module' => 'Notifications', 'description' => 'Mark notifications as read'],
            ['name' => 'notifications.delete_own', 'display_name' => 'Delete Own Notifications', 'module' => 'Notifications', 'description' => 'Delete own notifications'],

            // Module 15: System Configuration
            ['name' => 'system.view_settings', 'display_name' => 'View Settings', 'module' => 'System Configuration', 'description' => 'View system settings'],
            ['name' => 'system.edit_settings', 'display_name' => 'Edit Settings', 'module' => 'System Configuration', 'description' => 'Edit system settings'],
            ['name' => 'system.view_activity_logs', 'display_name' => 'View Activity Logs', 'module' => 'System Configuration', 'description' => 'View activity logs'],
            ['name' => 'system.view_email_logs', 'display_name' => 'View Email Logs', 'module' => 'System Configuration', 'description' => 'View email logs'],
            ['name' => 'system.manage_email_templates', 'display_name' => 'Manage Email Templates', 'module' => 'System Configuration', 'description' => 'Manage email templates'],
            ['name' => 'system.backup_database', 'display_name' => 'Backup Database', 'module' => 'System Configuration', 'description' => 'Backup database'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }
    }
}
