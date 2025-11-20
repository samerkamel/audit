# RBAC - Roles & Permissions Matrix

## Role Definitions

### 1. **Super Administrator**
**User Type:** System Administrator
**Purpose:** Complete system access for technical configuration and maintenance

### 2. **Quality Manager**
**User Type:** Quality Team Leader
**Purpose:** Oversee all audit activities, approve reports and CARs

### 3. **Quality Engineer / Auditor**
**User Type:** Quality Team Member / Internal Auditor
**Purpose:** Conduct audits, create reports, issue CARs

### 4. **Sector Director**
**User Type:** Industrial Sector Director / Electronics Sector Director
**Purpose:** Oversee sector-level audit activities and approve responses

### 5. **Department Manager**
**User Type:** Department Head
**Purpose:** Respond to audits, CARs, and complaints for their department

### 6. **Management Representative**
**User Type:** Senior Management
**Purpose:** Strategic oversight, view reports and analytics

### 7. **External Auditor (Read-Only)**
**User Type:** External Auditor (if system access is later granted)
**Purpose:** View assigned audit documentation only

---

## Permission Modules

### Module 1: User Management
- `users.view`
- `users.create`
- `users.edit`
- `users.delete`
- `users.activate_deactivate`

### Module 2: Role & Permission Management
- `roles.view`
- `roles.create`
- `roles.edit`
- `roles.delete`
- `roles.assign_permissions`
- `permissions.view`

### Module 3: Organizational Structure
- `sectors.view`
- `sectors.create`
- `sectors.edit`
- `sectors.delete`
- `departments.view`
- `departments.create`
- `departments.edit`
- `departments.delete`

### Module 4: Auditor Management
- `auditors.view`
- `auditors.create`
- `auditors.edit`
- `auditors.delete`
- `auditors.assign_competencies`
- `auditors.assign_departments`
- `auditors.manage_certificates`
- `auditors.view_availability`
- `auditors.edit_availability`

### Module 5: Question Bank & Checklists
- `iso_standards.view`
- `iso_standards.create`
- `iso_standards.edit`
- `iso_standards.delete`
- `iso_clauses.view`
- `iso_clauses.create`
- `iso_clauses.edit`
- `iso_clauses.delete`
- `procedures.view`
- `procedures.create`
- `procedures.edit`
- `procedures.delete`
- `questions.view`
- `questions.create`
- `questions.edit`
- `questions.delete`
- `checklists.view`
- `checklists.create`
- `checklists.edit`
- `checklists.delete`
- `checklists.assign_departments`

### Module 6: Annual Audit Planning
- `annual_plans.view`
- `annual_plans.create`
- `annual_plans.edit`
- `annual_plans.delete`
- `annual_plans.approve`

### Module 7: Audit Execution
- `audits.view_all`
- `audits.view_own`
- `audits.view_department`
- `audits.create`
- `audits.edit`
- `audits.delete`
- `audits.schedule`
- `audits.reschedule`
- `audits.assign_auditors`
- `audits.conduct` (perform audit)
- `audits.add_findings`
- `audits.edit_findings`

### Module 8: Audit Reports
- `audit_reports.view_all`
- `audit_reports.view_own`
- `audit_reports.view_department`
- `audit_reports.create`
- `audit_reports.edit`
- `audit_reports.submit_for_approval`
- `audit_reports.approve`
- `audit_reports.send`
- `audit_reports.export`

### Module 9: CAR Management
- `cars.view_all`
- `cars.view_own_issued`
- `cars.view_department`
- `cars.create`
- `cars.edit`
- `cars.delete`
- `cars.submit_for_approval`
- `cars.approve`
- `cars.issue`
- `cars.respond`
- `cars.review_response`
- `cars.accept_response`
- `cars.reject_response`
- `cars.follow_up`
- `cars.close`
- `cars.export`

### Module 10: External Audits
- `external_audits.view`
- `external_audits.create`
- `external_audits.edit`
- `external_audits.delete`
- `external_audits.upload_documents`
- `external_audits.delete_documents`

### Module 11: Customer Complaints
- `complaints.view_all`
- `complaints.view_department`
- `complaints.create`
- `complaints.edit`
- `complaints.delete`
- `complaints.assign_department`
- `complaints.respond`
- `complaints.review_response`
- `complaints.accept_response`
- `complaints.reject_response`
- `complaints.close`
- `complaints.create_car`
- `complaints.export`

### Module 12: Document Modifications
- `mod_requests.view_all`
- `mod_requests.view_own`
- `mod_requests.create`
- `mod_requests.edit`
- `mod_requests.approve`
- `mod_requests.reject`
- `mod_log.view`
- `mod_log.create`

### Module 13: Reports & Analytics
- `reports.view_audit_completion`
- `reports.view_car_log`
- `reports.view_department_performance`
- `reports.view_auditor_performance`
- `reports.view_compliance_trends`
- `reports.view_management_dashboard`
- `reports.export`

### Module 14: Notifications
- `notifications.view_own`
- `notifications.mark_read`
- `notifications.delete_own`

### Module 15: System Configuration
- `system.view_settings`
- `system.edit_settings`
- `system.view_activity_logs`
- `system.view_email_logs`
- `system.manage_email_templates`
- `system.backup_database`
- `system.restore_database`

---

## Permission Matrix

| Permission | Super Admin | Quality Manager | Quality Engineer | Sector Director | Dept Manager | Mgmt Rep | External Auditor |
|------------|-------------|-----------------|------------------|-----------------|--------------|----------|------------------|
| **USER MANAGEMENT** |
| users.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| users.create | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| users.edit | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| users.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| users.activate_deactivate | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **ROLE & PERMISSIONS** |
| roles.view | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| roles.create | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| roles.edit | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| roles.delete | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| roles.assign_permissions | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **ORGANIZATIONAL STRUCTURE** |
| sectors.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| sectors.create | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| sectors.edit | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| sectors.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| departments.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| departments.create | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| departments.edit | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| departments.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **AUDITOR MANAGEMENT** |
| auditors.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| auditors.create | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| auditors.edit | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| auditors.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| auditors.assign_competencies | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| auditors.assign_departments | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| auditors.manage_certificates | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| auditors.view_availability | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| auditors.edit_availability | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| **QUESTION BANK & CHECKLISTS** |
| iso_standards.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| iso_standards.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| iso_standards.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| iso_standards.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| iso_clauses.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| iso_clauses.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| iso_clauses.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| iso_clauses.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| procedures.view | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| procedures.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| procedures.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| procedures.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| questions.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| questions.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| questions.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| questions.delete | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| checklists.view | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| checklists.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| checklists.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| checklists.delete | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| checklists.assign_departments | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| **ANNUAL AUDIT PLANNING** |
| annual_plans.view | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| annual_plans.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| annual_plans.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| annual_plans.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| annual_plans.approve | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **AUDIT EXECUTION** |
| audits.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| audits.view_own | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| audits.view_department | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| audits.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| audits.edit | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| audits.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| audits.schedule | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| audits.reschedule | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| audits.assign_auditors | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| audits.conduct | ✓ | ✓ | ✓ (assigned) | ✗ | ✗ | ✗ | ✗ |
| audits.add_findings | ✓ | ✓ | ✓ (assigned) | ✗ | ✗ | ✗ | ✗ |
| audits.edit_findings | ✓ | ✓ | ✓ (assigned) | ✗ | ✗ | ✗ | ✗ |
| **AUDIT REPORTS** |
| audit_reports.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| audit_reports.view_own | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| audit_reports.view_department | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| audit_reports.create | ✓ | ✓ | ✓ (assigned) | ✗ | ✗ | ✗ | ✗ |
| audit_reports.edit | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| audit_reports.submit_for_approval | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| audit_reports.approve | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| audit_reports.send | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| audit_reports.export | ✓ | ✓ | ✓ (own) | ✓ (dept) | ✓ (dept) | ✓ | ✗ |
| **CAR MANAGEMENT** |
| cars.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| cars.view_own_issued | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| cars.view_department | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| cars.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| cars.edit | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| cars.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| cars.submit_for_approval | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| cars.approve | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| cars.issue | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| cars.respond | ✓ | ✓ | ✗ | ✓ (dept) | ✓ (dept) | ✗ | ✗ |
| cars.review_response | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| cars.accept_response | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| cars.reject_response | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| cars.follow_up | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| cars.close | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| cars.export | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| **EXTERNAL AUDITS** |
| external_audits.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| external_audits.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| external_audits.edit | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| external_audits.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| external_audits.upload_documents | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| external_audits.delete_documents | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| **CUSTOMER COMPLAINTS** |
| complaints.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| complaints.view_department | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| complaints.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.edit | ✓ | ✓ | ✓ (own) | ✗ | ✗ | ✗ | ✗ |
| complaints.delete | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| complaints.assign_department | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.respond | ✓ | ✓ | ✗ | ✓ (dept) | ✓ (dept) | ✗ | ✗ |
| complaints.review_response | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.accept_response | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.reject_response | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.close | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.create_car | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| complaints.export | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| **DOCUMENT MODIFICATIONS** |
| mod_requests.view_all | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| mod_requests.view_own | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| mod_requests.create | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| mod_requests.edit | ✓ | ✓ | ✓ (own) | ✓ (own) | ✓ (own) | ✗ | ✗ |
| mod_requests.approve | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| mod_requests.reject | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| mod_log.view | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✗ |
| mod_log.create | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| **REPORTS & ANALYTICS** |
| reports.view_audit_completion | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| reports.view_car_log | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| reports.view_department_performance | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| reports.view_auditor_performance | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| reports.view_compliance_trends | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| reports.view_management_dashboard | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| reports.export | ✓ | ✓ | ✓ | ✓ | ✓ (dept) | ✓ | ✗ |
| **NOTIFICATIONS** |
| notifications.view_own | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| notifications.mark_read | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| notifications.delete_own | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| **SYSTEM CONFIGURATION** |
| system.view_settings | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| system.edit_settings | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| system.view_activity_logs | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| system.view_email_logs | ✓ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| system.manage_email_templates | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| system.backup_database | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |
| system.restore_database | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ | ✗ |

---

## Permission Scoping Rules

### Department-Scoped Permissions
Users with department-scoped permissions can only access data related to their assigned department:
- `audits.view_department`
- `audit_reports.view_department`
- `cars.view_department`
- `complaints.view_department`
- `procedures.view` (for Dept Manager)
- `checklists.view` (for Dept Manager)

### Own-Resource Permissions
Users can only access/modify resources they created:
- `audits.view_own`
- `audits.edit` (own)
- `audit_reports.view_own`
- `audit_reports.edit` (own)
- `cars.view_own_issued`
- `cars.edit` (own)
- `auditors.edit` (own profile only)

### Conditional Permissions
Some permissions are conditional based on audit assignments:
- `audits.conduct` (assigned auditor)
- `audits.add_findings` (assigned auditor)
- `audit_reports.create` (assigned auditor)

---

## Escalation Rules

### CAR Escalation
When a CAR is overdue or has no response:
1. **1 week overdue** → Email reminder to Department Manager + Sector Director (CC)
2. **2 weeks overdue** → Email reminder with Sector CEO in CC
3. **3 weeks overdue** → Escalation notification to Quality Manager + Management Representative

### Audit Report Approval Escalation
When audit reports are not approved:
1. **2 days pending** → Reminder to Quality Manager
2. **5 days pending** → Escalation to Management Representative

### Certificate Renewal Reminders
1. **90 days before expiry** → Reminder to Auditor + Quality Manager
2. **30 days before expiry** → Warning to Auditor + Quality Manager
3. **7 days before expiry** → Critical alert to Auditor + Quality Manager + Management Representative

---

## Data Access Summary

| Role | Access Level | Data Scope |
|------|-------------|------------|
| Super Administrator | Full | All data |
| Quality Manager | Full (Quality Module) | All quality data |
| Quality Engineer | Operational | Own + assigned audits |
| Sector Director | Read (Sector) | Sector departments |
| Department Manager | Read/Respond (Dept) | Own department only |
| Management Representative | Read-Only (Strategic) | All data (no modifications) |
| External Auditor | Read-Only (Assigned) | Assigned audit only |

---

**Total Permissions:** 150+
**Version:** 1.0
**Last Updated:** January 2025
