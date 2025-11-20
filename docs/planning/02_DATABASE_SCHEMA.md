# Database Schema - Audit & Compliance Management System

## Entity Relationship Overview

### Core Entities
1. Users & Authentication
2. Organizational Structure
3. Auditor Management
4. Question Bank & Checklists
5. Audit Management
6. CAR Management
7. External Audits
8. Customer Complaints
9. Document Modifications
10. System Configuration

---

## Detailed Table Specifications

### 1. USER MANAGEMENT

#### `users`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
email               VARCHAR(255) UNIQUE NOT NULL
password            VARCHAR(255) NOT NULL
department_id       BIGINT UNSIGNED NULLABLE FOREIGN KEY -> departments(id)
sector_id           BIGINT UNSIGNED NULLABLE FOREIGN KEY -> sectors(id)
phone               VARCHAR(50) NULLABLE
mobile              VARCHAR(50) NULLABLE
is_active           BOOLEAN DEFAULT 1
language            ENUM('en', 'ar') DEFAULT 'en'
email_verified_at   TIMESTAMP NULLABLE
remember_token      VARCHAR(100) NULLABLE
last_login_at       TIMESTAMP NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE (soft delete)

INDEXES:
  - email
  - department_id
  - sector_id
  - is_active
```

#### `roles`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(100) NOT NULL UNIQUE
display_name        VARCHAR(255) NOT NULL
description         TEXT NULLABLE
is_system_role      BOOLEAN DEFAULT 0
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - name
```

#### `permissions`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(100) NOT NULL UNIQUE
display_name        VARCHAR(255) NOT NULL
module              VARCHAR(100) NOT NULL
description         TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - name
  - module
```

#### `role_user` (pivot)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id             BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id) ON DELETE CASCADE
role_id             BIGINT UNSIGNED NOT NULL FOREIGN KEY -> roles(id) ON DELETE CASCADE
created_at          TIMESTAMP

UNIQUE KEY (user_id, role_id)
INDEXES:
  - user_id
  - role_id
```

#### `permission_role` (pivot)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
role_id             BIGINT UNSIGNED NOT NULL FOREIGN KEY -> roles(id) ON DELETE CASCADE
permission_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> permissions(id) ON DELETE CASCADE
created_at          TIMESTAMP

UNIQUE KEY (role_id, permission_id)
INDEXES:
  - role_id
  - permission_id
```

---

### 2. ORGANIZATIONAL STRUCTURE

#### `sectors`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
name_ar             VARCHAR(255) NOT NULL
code                VARCHAR(50) UNIQUE NOT NULL
director_id         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
description         TEXT NULLABLE
is_active           BOOLEAN DEFAULT 1
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE

INDEXES:
  - code
  - director_id
  - is_active
```

#### `departments`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
sector_id           BIGINT UNSIGNED NOT NULL FOREIGN KEY -> sectors(id)
name                VARCHAR(255) NOT NULL
name_ar             VARCHAR(255) NOT NULL
code                VARCHAR(50) UNIQUE NOT NULL
manager_id          BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
email               VARCHAR(255) NULLABLE
phone               VARCHAR(50) NULLABLE
description         TEXT NULLABLE
is_active           BOOLEAN DEFAULT 1
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE

INDEXES:
  - sector_id
  - code
  - manager_id
  - is_active
```

---

### 3. AUDITOR MANAGEMENT

#### `auditors`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id             BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id) ON DELETE CASCADE
employee_code       VARCHAR(50) UNIQUE NULLABLE
bio                 TEXT NULLABLE
specializations     TEXT NULLABLE (JSON)
is_lead_auditor     BOOLEAN DEFAULT 0
is_active           BOOLEAN DEFAULT 1
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE KEY (user_id)
INDEXES:
  - user_id
  - employee_code
  - is_active
```

#### `auditor_certificates`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
auditor_id          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> auditors(id) ON DELETE CASCADE
certificate_name    VARCHAR(255) NOT NULL
issuing_authority   VARCHAR(255) NULLABLE
certificate_number  VARCHAR(100) NULLABLE
issue_date          DATE NULLABLE
expiry_date         DATE NULLABLE
attachment_path     VARCHAR(500) NULLABLE
notes               TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - auditor_id
  - expiry_date
```

#### `auditor_competencies`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
auditor_id          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> auditors(id) ON DELETE CASCADE
competency_name     VARCHAR(255) NOT NULL
proficiency_level   ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate'
notes               TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - auditor_id
```

#### `auditor_department_recommendations` (pivot)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
auditor_id          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> auditors(id) ON DELETE CASCADE
department_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id) ON DELETE CASCADE
recommendation_level ENUM('primary', 'secondary') DEFAULT 'primary'
created_at          TIMESTAMP

UNIQUE KEY (auditor_id, department_id)
INDEXES:
  - auditor_id
  - department_id
```

#### `auditor_availability`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
auditor_id          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> auditors(id) ON DELETE CASCADE
date                DATE NOT NULL
is_available        BOOLEAN DEFAULT 1
reason              VARCHAR(255) NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE KEY (auditor_id, date)
INDEXES:
  - auditor_id
  - date
```

---

### 4. QUESTION BANK & CHECKLISTS

#### `iso_standards`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
code                VARCHAR(50) UNIQUE NOT NULL
version             VARCHAR(50) NULLABLE
description         TEXT NULLABLE
is_active           BOOLEAN DEFAULT 1
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - code
  - is_active
```

#### `iso_clauses`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
iso_standard_id     BIGINT UNSIGNED NOT NULL FOREIGN KEY -> iso_standards(id) ON DELETE CASCADE
clause_number       VARCHAR(50) NOT NULL
title               VARCHAR(500) NOT NULL
title_ar            VARCHAR(500) NOT NULL
description         TEXT NULLABLE
parent_clause_id    BIGINT UNSIGNED NULLABLE FOREIGN KEY -> iso_clauses(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - iso_standard_id
  - clause_number
  - parent_clause_id
```

#### `procedures`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
department_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
code                VARCHAR(50) UNIQUE NOT NULL
name                VARCHAR(500) NOT NULL
name_ar             VARCHAR(500) NOT NULL
version             VARCHAR(20) NULLABLE
description         TEXT NULLABLE
is_active           BOOLEAN DEFAULT 1
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - department_id
  - code
  - is_active
```

#### `questions`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
question_text       TEXT NOT NULL
question_text_ar    TEXT NOT NULL
iso_clause_id       BIGINT UNSIGNED NULLABLE FOREIGN KEY -> iso_clauses(id)
procedure_id        BIGINT UNSIGNED NULLABLE FOREIGN KEY -> procedures(id)
category            VARCHAR(100) NULLABLE
tags                TEXT NULLABLE (JSON)
is_active           BOOLEAN DEFAULT 1
created_by          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - iso_clause_id
  - procedure_id
  - is_active
  - created_by
```

#### `checklists`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
name_ar             VARCHAR(255) NOT NULL
code                VARCHAR(50) UNIQUE NOT NULL
description         TEXT NULLABLE
version             VARCHAR(20) DEFAULT '1.0'
is_template         BOOLEAN DEFAULT 0
is_active           BOOLEAN DEFAULT 1
created_by          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - code
  - is_template
  - is_active
  - created_by
```

#### `checklist_questions` (pivot with order)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
checklist_id        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> checklists(id) ON DELETE CASCADE
question_id         BIGINT UNSIGNED NOT NULL FOREIGN KEY -> questions(id) ON DELETE CASCADE
order_index         INT NOT NULL
is_mandatory        BOOLEAN DEFAULT 1
created_at          TIMESTAMP

UNIQUE KEY (checklist_id, question_id)
INDEXES:
  - checklist_id
  - question_id
  - order_index
```

#### `checklist_department` (pivot)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
checklist_id        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> checklists(id) ON DELETE CASCADE
department_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id) ON DELETE CASCADE
created_at          TIMESTAMP

UNIQUE KEY (checklist_id, department_id)
INDEXES:
  - checklist_id
  - department_id
```

---

### 5. AUDIT MANAGEMENT

#### `annual_audit_plans`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
year                YEAR NOT NULL
name                VARCHAR(255) NOT NULL
name_ar             VARCHAR(255) NOT NULL
status              ENUM('draft', 'approved', 'in_progress', 'completed') DEFAULT 'draft'
approved_by         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
approved_at         TIMESTAMP NULLABLE
created_by          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - year
  - status
  - created_by
  - approved_by
```

#### `audits`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
annual_plan_id      BIGINT UNSIGNED NULLABLE FOREIGN KEY -> annual_audit_plans(id)
audit_number        VARCHAR(50) UNIQUE NOT NULL (e.g., R25001)
audit_type          ENUM('internal', 'external') NOT NULL
department_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
procedure_id        BIGINT UNSIGNED NULLABLE FOREIGN KEY -> procedures(id)
checklist_id        BIGINT UNSIGNED NULLABLE FOREIGN KEY -> checklists(id)
scheduled_date      DATE NULLABLE
scheduled_start_time TIME NULLABLE
scheduled_duration  INT NULLABLE (minutes)
actual_date         DATE NULLABLE
actual_start_time   TIME NULLABLE
actual_end_time     TIME NULLABLE
priority            ENUM('urgent', 'high', 'medium', 'low') DEFAULT 'medium'
status              ENUM('planned', 'scheduled', 'rescheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned'
reschedule_reason   TEXT NULLABLE
notes               TEXT NULLABLE
created_by          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - annual_plan_id
  - audit_number
  - audit_type
  - department_id
  - procedure_id
  - scheduled_date
  - status
  - created_by
```

#### `audit_auditors` (pivot)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
audit_id            BIGINT UNSIGNED NOT NULL FOREIGN KEY -> audits(id) ON DELETE CASCADE
auditor_id          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> auditors(id) ON DELETE CASCADE
is_lead             BOOLEAN DEFAULT 0
created_at          TIMESTAMP

UNIQUE KEY (audit_id, auditor_id)
INDEXES:
  - audit_id
  - auditor_id
```

#### `audit_attendees`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
audit_id            BIGINT UNSIGNED NOT NULL FOREIGN KEY -> audits(id) ON DELETE CASCADE
attendee_name       VARCHAR(255) NOT NULL
attendee_title      VARCHAR(255) NULLABLE
department_id       BIGINT UNSIGNED NULLABLE FOREIGN KEY -> departments(id)
attendee_type       ENUM('auditor', 'auditee', 'observer') DEFAULT 'auditee'
created_at          TIMESTAMP

INDEXES:
  - audit_id
  - department_id
```

#### `audit_reports`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
audit_id            BIGINT UNSIGNED NOT NULL FOREIGN KEY -> audits(id) ON DELETE CASCADE
report_number       VARCHAR(50) UNIQUE NOT NULL (matches audit_number)
status              ENUM('draft', 'pending_approval', 'approved', 'sent') DEFAULT 'draft'
summary             TEXT NULLABLE
recommendations     TEXT NULLABLE
approved_by         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
approved_at         TIMESTAMP NULLABLE
sent_at             TIMESTAMP NULLABLE
created_by          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE KEY (audit_id)
INDEXES:
  - audit_id
  - report_number
  - status
  - created_by
```

#### `audit_findings`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
audit_report_id     BIGINT UNSIGNED NOT NULL FOREIGN KEY -> audit_reports(id) ON DELETE CASCADE
question_id         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> questions(id)
custom_question_text TEXT NULLABLE (for ad-hoc questions added during audit)
custom_question_text_ar TEXT NULLABLE
iso_clause_id       BIGINT UNSIGNED NULLABLE FOREIGN KEY -> iso_clauses(id)
procedure_clause    VARCHAR(255) NULLABLE
evidence            TEXT NULLABLE
compliance_status   ENUM('complied', 'complied_with_observation', 'not_complied_major', 'not_complied_minor', 'skipped') NOT NULL
order_index         INT NOT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - audit_report_id
  - question_id
  - iso_clause_id
  - compliance_status
  - order_index
```

---

### 6. CAR MANAGEMENT

#### `cars` (Corrective Action Requests)
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
car_number          VARCHAR(50) UNIQUE NOT NULL (e.g., C25001)
source_type         ENUM('internal_audit', 'external_audit', 'customer_complaint', 'process_performance', 'other') NOT NULL
source_id           BIGINT UNSIGNED NULLABLE (polymorphic - audit_finding_id, complaint_id, etc.)
audit_finding_id    BIGINT UNSIGNED NULLABLE FOREIGN KEY -> audit_findings(id)
customer_complaint_id BIGINT UNSIGNED NULLABLE FOREIGN KEY -> customer_complaints(id)
from_department_id  BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
to_department_id    BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
issued_date         DATE NOT NULL
subject             VARCHAR(500) NOT NULL
ncr_description     TEXT NOT NULL (Non-Conformance Report description)
clarification       TEXT NULLABLE (by quality team)
status              ENUM('draft', 'pending_approval', 'issued', 'in_progress', 'pending_review', 'rejected_to_be_edited', 'closed', 'late') DEFAULT 'draft'
priority            ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium'
issued_by           BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
approved_by         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
approved_at         TIMESTAMP NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - car_number
  - source_type
  - audit_finding_id
  - customer_complaint_id
  - from_department_id
  - to_department_id
  - status
  - issued_by
```

#### `car_responses`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
car_id              BIGINT UNSIGNED NOT NULL FOREIGN KEY -> cars(id) ON DELETE CASCADE
root_cause          TEXT NOT NULL
correction          TEXT NOT NULL (short-term action)
correction_target_date DATE NOT NULL
correction_actual_date DATE NULLABLE
corrective_action   TEXT NOT NULL (long-term action)
corrective_action_target_date DATE NOT NULL
corrective_action_actual_date DATE NULLABLE
attachments         TEXT NULLABLE (JSON array of file paths)
response_status     ENUM('pending', 'submitted', 'accepted', 'rejected') DEFAULT 'pending'
responded_by        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
responded_at        TIMESTAMP NULLABLE
reviewed_by         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
reviewed_at         TIMESTAMP NULLABLE
rejection_reason    TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - car_id
  - response_status
  - responded_by
  - reviewed_by
```

#### `car_follow_ups`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
car_id              BIGINT UNSIGNED NOT NULL FOREIGN KEY -> cars(id) ON DELETE CASCADE
follow_up_type      ENUM('correction', 'corrective_action') NOT NULL
follow_up_status    ENUM('accepted', 'not_accepted', 'pending') DEFAULT 'pending'
follow_up_notes     TEXT NULLABLE
followed_up_by      BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
followed_up_at      TIMESTAMP NOT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - car_id
  - follow_up_type
  - follow_up_status
  - followed_up_by
```

---

### 7. EXTERNAL AUDITS

#### `external_audits`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
audit_number        VARCHAR(50) UNIQUE NOT NULL (e.g., EA25001)
audit_body          VARCHAR(255) NOT NULL (certification body name)
iso_standard_id     BIGINT UNSIGNED NULLABLE FOREIGN KEY -> iso_standards(id)
audit_type          ENUM('certification', 'surveillance', 'recertification') NOT NULL
scheduled_date      DATE NULLABLE
actual_date         DATE NULLABLE
certificate_number  VARCHAR(100) NULLABLE
certificate_issue_date DATE NULLABLE
certificate_expiry_date DATE NULLABLE
status              ENUM('planned', 'in_progress', 'completed', 'certified', 'expired') DEFAULT 'planned'
notes               TEXT NULLABLE
created_by          BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - audit_number
  - iso_standard_id
  - scheduled_date
  - certificate_expiry_date
  - status
  - created_by
```

#### `external_audit_documents`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
external_audit_id   BIGINT UNSIGNED NOT NULL FOREIGN KEY -> external_audits(id) ON DELETE CASCADE
document_type       ENUM('audit_report', 'certificate', 'findings', 'other') NOT NULL
file_name           VARCHAR(255) NOT NULL
file_path           VARCHAR(500) NOT NULL
file_size           INT NULLABLE (bytes)
uploaded_by         BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
uploaded_at         TIMESTAMP NOT NULL
notes               TEXT NULLABLE
created_at          TIMESTAMP

INDEXES:
  - external_audit_id
  - document_type
  - uploaded_by
```

---

### 8. CUSTOMER COMPLAINTS

#### `customer_complaints`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
complaint_number    VARCHAR(50) UNIQUE NOT NULL (e.g., CC25001)
customer_name       VARCHAR(255) NOT NULL
customer_code       VARCHAR(100) NULLABLE
customer_branch     VARCHAR(255) NULLABLE
customer_phone      VARCHAR(50) NULLABLE
customer_mobile     VARCHAR(50) NULLABLE
customer_fax        VARCHAR(50) NULLABLE
product_name        VARCHAR(255) NULLABLE
complaint_content   TEXT NOT NULL
assigned_department_id BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
status              ENUM('received', 'in_progress', 'pending_review', 'rejected_to_be_edited', 'resolved', 'closed') DEFAULT 'received'
priority            ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium'
received_by         BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
received_date       DATE NOT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - complaint_number
  - assigned_department_id
  - status
  - received_by
  - received_date
```

#### `complaint_responses`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
complaint_id        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> customer_complaints(id) ON DELETE CASCADE
root_cause          TEXT NOT NULL
short_term_action   TEXT NOT NULL
short_term_target_date DATE NOT NULL
short_term_actual_date DATE NULLABLE
long_term_action    TEXT NOT NULL
long_term_target_date DATE NOT NULL
long_term_actual_date DATE NULLABLE
response_status     ENUM('pending', 'submitted', 'accepted', 'rejected') DEFAULT 'pending'
responded_by        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
responded_at        TIMESTAMP NULLABLE
reviewed_by         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
reviewed_at         TIMESTAMP NULLABLE
rejection_reason    TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - complaint_id
  - response_status
  - responded_by
  - reviewed_by
```

#### `complaint_attachments`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
complaint_id        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> customer_complaints(id) ON DELETE CASCADE
file_name           VARCHAR(255) NOT NULL
file_path           VARCHAR(500) NOT NULL
file_type           VARCHAR(50) NULLABLE
file_size           INT NULLABLE (bytes)
uploaded_by         BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
uploaded_at         TIMESTAMP NOT NULL
created_at          TIMESTAMP

INDEXES:
  - complaint_id
  - uploaded_by
```

---

### 9. DOCUMENT MODIFICATIONS

#### `modification_requests`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
request_number      VARCHAR(50) UNIQUE NOT NULL (e.g., M25001)
document_type       ENUM('procedure', 'form', 'checklist', 'other') NOT NULL
document_code       VARCHAR(50) NOT NULL
document_name       VARCHAR(500) NOT NULL
current_version     VARCHAR(20) NULLABLE
modification_type   ENUM('modification', 'addition', 'cancellation') NOT NULL
reason              TEXT NOT NULL
suggestions         TEXT NULLABLE
status              ENUM('draft', 'pending_approval', 'approved', 'rejected', 'implemented') DEFAULT 'draft'
requested_by        BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
department_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
approved_by         BIGINT UNSIGNED NULLABLE FOREIGN KEY -> users(id)
approved_at         TIMESTAMP NULLABLE
implementation_date DATE NULLABLE
rejection_reason    TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - request_number
  - document_code
  - status
  - requested_by
  - department_id
```

#### `modification_log`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
modification_request_id BIGINT UNSIGNED NULLABLE FOREIGN KEY -> modification_requests(id)
document_code       VARCHAR(50) NOT NULL
document_name       VARCHAR(500) NOT NULL
previous_version    VARCHAR(20) NULLABLE
new_version         VARCHAR(20) NOT NULL
department_id       BIGINT UNSIGNED NOT NULL FOREIGN KEY -> departments(id)
implementation_date DATE NOT NULL
notes               TEXT NULLABLE
logged_by           BIGINT UNSIGNED NOT NULL FOREIGN KEY -> users(id)
created_at          TIMESTAMP

INDEXES:
  - modification_request_id
  - document_code
  - implementation_date
  - department_id
```

---

### 10. NOTIFICATIONS & SYSTEM

#### `notifications`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
type                VARCHAR(100) NOT NULL
notifiable_type     VARCHAR(100) NOT NULL (polymorphic)
notifiable_id       BIGINT UNSIGNED NOT NULL
data                TEXT NOT NULL (JSON)
read_at             TIMESTAMP NULLABLE
created_at          TIMESTAMP

INDEXES:
  - notifiable_type
  - notifiable_id
  - read_at
  - created_at
```

#### `email_logs`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
email_type          VARCHAR(100) NOT NULL
recipient_email     VARCHAR(255) NOT NULL
recipient_name      VARCHAR(255) NULLABLE
cc_emails           TEXT NULLABLE (JSON)
subject             VARCHAR(500) NOT NULL
body                TEXT NOT NULL
attachments         TEXT NULLABLE (JSON)
sent_at             TIMESTAMP NULLABLE
status              ENUM('pending', 'sent', 'failed') DEFAULT 'pending'
error_message       TEXT NULLABLE
related_type        VARCHAR(100) NULLABLE (polymorphic)
related_id          BIGINT UNSIGNED NULLABLE
created_at          TIMESTAMP

INDEXES:
  - email_type
  - recipient_email
  - status
  - sent_at
  - related_type
  - related_id
```

#### `system_settings`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
key                 VARCHAR(100) UNIQUE NOT NULL
value               TEXT NULLABLE
data_type           ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string'
category            VARCHAR(100) NOT NULL
description         TEXT NULLABLE
is_public           BOOLEAN DEFAULT 0
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEXES:
  - key
  - category
```

#### `activity_logs`
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
log_name            VARCHAR(100) NULLABLE
description         TEXT NOT NULL
subject_type        VARCHAR(100) NULLABLE (polymorphic)
subject_id          BIGINT UNSIGNED NULLABLE
causer_type         VARCHAR(100) NULLABLE (polymorphic - User)
causer_id           BIGINT UNSIGNED NULLABLE
properties          TEXT NULLABLE (JSON - old & new values)
ip_address          VARCHAR(45) NULLABLE
user_agent          TEXT NULLABLE
created_at          TIMESTAMP

INDEXES:
  - log_name
  - subject_type
  - subject_id
  - causer_type
  - causer_id
  - created_at
```

---

## Database Relationships Summary

### One-to-Many Relationships
- sectors → departments
- departments → users
- sectors → users
- departments → procedures
- auditors → auditor_certificates
- auditors → auditor_competencies
- checklists → checklist_questions
- audits → audit_findings
- audit_reports → audit_findings
- cars → car_responses
- cars → car_follow_ups
- customer_complaints → complaint_responses
- customer_complaints → complaint_attachments
- external_audits → external_audit_documents

### Many-to-Many Relationships
- users ↔ roles (via role_user)
- roles ↔ permissions (via permission_role)
- auditors ↔ departments (via auditor_department_recommendations)
- checklists ↔ departments (via checklist_department)
- checklists ↔ questions (via checklist_questions)
- audits ↔ auditors (via audit_auditors)

### Polymorphic Relationships
- cars → source (audit_finding, customer_complaint, etc.)
- notifications → notifiable (User, Department, etc.)
- email_logs → related (Audit, CAR, Complaint, etc.)
- activity_logs → subject (any model)
- activity_logs → causer (User)

---

## Indexing Strategy

### Primary Indexes
- All `id` columns (primary keys)
- All `_number` columns (unique constraints)
- All `code` columns (unique constraints)

### Foreign Key Indexes
- All foreign key columns automatically indexed

### Search Optimization Indexes
- `users`: email, department_id, sector_id
- `audits`: audit_number, status, scheduled_date
- `cars`: car_number, status, issued_date
- `audit_findings`: compliance_status
- `email_logs`: sent_at, status
- `activity_logs`: created_at

### Composite Indexes (if needed for performance)
- `audits`: (department_id, scheduled_date, status)
- `cars`: (to_department_id, status)
- `email_logs`: (email_type, status, sent_at)

---

**Total Tables:** 48
**Version:** 1.0
**Last Updated:** January 2025
