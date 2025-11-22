# Comprehensive Testing Strategy
## Laravel ISO 9001:2015 Audit Management System

**Version:** 1.0
**Date:** November 22, 2025
**Status:** Planning Phase

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Testing Categories](#testing-categories)
3. [Unit Tests - Models](#unit-tests---models)
4. [Unit Tests - Services](#unit-tests---services)
5. [Feature Tests - Controllers](#feature-tests---controllers)
6. [API Tests](#api-tests)
7. [Integration Tests](#integration-tests)
8. [E2E Tests](#e2e-tests)
9. [Test Priority Matrix](#test-priority-matrix)
10. [Implementation Roadmap](#implementation-roadmap)

---

## Executive Summary

This document outlines the testing strategy for the Audit Management System covering:
- **17 Models** with business logic, relationships, and accessors
- **18 Controllers** (Web + API)
- **4 Number Generation Systems** (critical business logic)
- **3 Complex Workflows** (CAR, Document, Audit)

### Testing Goals
- **Unit Test Coverage:** 80%+ for models and services
- **Feature Test Coverage:** 100% for critical paths
- **API Test Coverage:** 100% for all endpoints
- **E2E Test Coverage:** Core user journeys (already at 100% with 45 tests)

---

## Testing Categories

| Category | Scope | Tools | Priority |
|----------|-------|-------|----------|
| Unit Tests | Models, Helpers, Services | PHPUnit | P0 |
| Feature Tests | HTTP Controllers, Forms | PHPUnit, Laravel HTTP | P0 |
| API Tests | REST API Endpoints | PHPUnit, Sanctum | P1 |
| Integration Tests | Workflows, Multi-model | PHPUnit | P1 |
| E2E Tests | Full User Journeys | Playwright | P2 (Done) |

---

## Unit Tests - Models

### 1. Car Model Tests
**File:** `tests/Unit/Models/CarTest.php`

#### 1.1 Number Generation
| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_generate_car_number_format` | Format matches CYY### pattern | P0 |
| `test_generate_car_number_increments` | Sequential increment works | P0 |
| `test_generate_car_number_includes_soft_deleted` | Soft-deleted records considered | P0 |
| `test_generate_car_number_handles_duplicates` | Fallback mechanism works | P1 |
| `test_generate_car_number_year_changes` | New year resets counter | P1 |

#### 1.2 Relationships
| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_car_belongs_to_from_department` | fromDepartment relationship | P1 |
| `test_car_belongs_to_to_department` | toDepartment relationship | P1 |
| `test_car_belongs_to_issued_by` | issuedBy relationship | P1 |
| `test_car_has_many_responses` | responses relationship | P0 |
| `test_car_has_many_follow_ups` | followUps relationship | P0 |
| `test_car_has_latest_response` | latestResponse scope | P1 |
| `test_car_morphs_to_source` | Polymorphic source | P1 |

#### 1.3 Scopes
| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_scope_by_status` | Filter by status | P1 |
| `test_scope_by_department` | Filter by to_department_id | P1 |
| `test_scope_by_source_type` | Filter by source_type | P2 |
| `test_scope_overdue` | Complex overdue logic | P0 |

#### 1.4 Accessors
| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_status_color_attribute` | All 8 status colors | P2 |
| `test_priority_color_attribute` | All 4 priority colors | P2 |

---

### 2. CarResponse Model Tests
**File:** `tests/Unit/Models/CarResponseTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_is_correction_overdue_returns_true` | When target passed, no actual | P0 |
| `test_is_correction_overdue_returns_false` | When actual date set | P0 |
| `test_is_corrective_action_overdue` | Same logic for CA | P0 |
| `test_is_complete_when_both_dates_set` | Both actual dates present | P0 |
| `test_is_complete_when_dates_missing` | Missing dates returns false | P0 |
| `test_response_status_color_attribute` | All 4 status colors | P2 |

---

### 3. CarFollowUp Model Tests
**File:** `tests/Unit/Models/CarFollowUpTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_belongs_to_car` | car relationship | P1 |
| `test_belongs_to_followed_up_by` | followedUpBy relationship | P1 |
| `test_follow_up_status_color` | 3 status colors | P2 |
| `test_follow_up_type_label` | Type label formatting | P2 |

---

### 4. AuditPlan Model Tests
**File:** `tests/Unit/Models/AuditPlanTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_is_overdue_returns_false_when_completed` | Completed plans not overdue | P0 |
| `test_is_overdue_returns_false_when_cancelled` | Cancelled plans not overdue | P0 |
| `test_is_overdue_returns_true_when_end_date_passed` | Overdue detection works | P0 |
| `test_is_overdue_excludes_completed_departments` | Only active departments | P1 |
| `test_checklist_groups_for_department` | Scoped relationship | P1 |
| `test_departments_pivot_data` | Pivot attributes loaded | P1 |
| `test_status_color_attribute` | 5 status colors | P2 |
| `test_audit_type_label_attribute` | 7 audit type labels | P2 |
| `test_scope_active` | is_active filter | P2 |
| `test_scope_by_status` | status filter | P2 |
| `test_scope_by_department` | Department relationship filter | P2 |

---

### 5. AuditQuestion Model Tests
**File:** `tests/Unit/Models/AuditQuestionTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_checklist_groups_relationship` | BelongsToMany with pivot | P1 |
| `test_scope_active` | is_active filter | P2 |
| `test_scope_by_category` | category filter | P2 |
| `test_category_color_attribute` | 6 category colors | P2 |
| `test_question_accessor_alias` | Maps to question_text | P2 |
| `test_question_mutator` | Sets question_text | P2 |

---

### 6. AuditResponse Model Tests
**File:** `tests/Unit/Models/AuditResponseTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_belongs_to_audit_plan` | auditPlan relationship | P1 |
| `test_belongs_to_department` | department relationship | P1 |
| `test_belongs_to_checklist_group` | checklistGroup relationship | P1 |
| `test_belongs_to_audit_question` | auditQuestion relationship | P1 |
| `test_response_color_complied` | Green for complied | P2 |
| `test_response_color_not_complied` | Red for not_complied | P2 |
| `test_response_label_attribute` | Display labels | P2 |

---

### 7. Department Model Tests
**File:** `tests/Unit/Models/DepartmentTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_belongs_to_sector` | sector relationship | P1 |
| `test_belongs_to_manager` | manager relationship | P1 |
| `test_has_many_users` | users relationship | P1 |
| `test_active_users_relationship` | Filtered users | P1 |
| `test_scope_active` | is_active filter | P2 |
| `test_scope_by_sector` | sector_id filter | P2 |
| `test_soft_deletes` | Trash and restore | P1 |

---

### 8. Sector Model Tests
**File:** `tests/Unit/Models/SectorTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_belongs_to_director` | director relationship | P1 |
| `test_has_many_departments` | departments relationship | P1 |
| `test_has_many_users` | users relationship | P1 |
| `test_scope_active` | is_active filter | P2 |
| `test_soft_deletes` | Trash and restore | P1 |

---

### 9. User Model Tests
**File:** `tests/Unit/Models/UserTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_has_role_single` | Check single role | P0 |
| `test_has_role_multiple` | Check multiple roles | P0 |
| `test_has_permission` | Permission through role | P0 |
| `test_has_any_permission` | Any of permissions | P1 |
| `test_has_all_permissions` | All permissions | P1 |
| `test_assign_role` | Add role to user | P0 |
| `test_remove_role` | Remove role from user | P1 |
| `test_sync_roles` | Replace all roles | P1 |
| `test_update_last_login` | Timestamp update | P2 |
| `test_belongs_to_department` | department relationship | P1 |
| `test_belongs_to_sector` | sector relationship | P1 |
| `test_roles_relationship` | BelongsToMany roles | P0 |
| `test_soft_deletes` | Trash and restore | P1 |

---

### 10. Role Model Tests
**File:** `tests/Unit/Models/RoleTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_has_permission` | Check permission by name | P0 |
| `test_give_permission` | Add permission to role | P0 |
| `test_revoke_permission` | Remove permission | P1 |
| `test_users_relationship` | BelongsToMany users | P1 |
| `test_permissions_relationship` | BelongsToMany permissions | P1 |

---

### 11. CheckListGroup Model Tests
**File:** `tests/Unit/Models/CheckListGroupTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_audit_questions_relationship` | BelongsToMany with pivot | P1 |
| `test_scope_active` | is_active filter | P2 |
| `test_scope_by_department` | department filter | P2 |
| `test_soft_deletes` | Trash and restore | P1 |

---

### 12. CustomerComplaint Model Tests
**File:** `tests/Unit/Models/CustomerComplaintTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_generate_complaint_number_format` | COMP-YY-#### format | P0 |
| `test_generate_complaint_number_increments` | Sequential increment | P0 |
| `test_generate_complaint_number_includes_soft_deleted` | Soft-deleted considered | P0 |
| `test_is_overdue_returns_false_when_closed` | Closed not overdue | P0 |
| `test_is_overdue_returns_true_when_past_response_date` | Overdue detection | P0 |
| `test_can_generate_car` | CAR generation eligibility | P0 |
| `test_can_be_closed` | Closure eligibility | P0 |
| `test_status_color_attribute` | 6 status colors | P2 |
| `test_priority_color_attribute` | 4 priority colors | P2 |
| `test_severity_color_attribute` | 3 severity colors | P2 |
| `test_category_label_attribute` | 7 category labels | P2 |

---

### 13. Certificate Model Tests
**File:** `tests/Unit/Models/CertificateTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_is_expired_returns_true` | Past expiry date | P0 |
| `test_is_expired_returns_false` | Future expiry date | P0 |
| `test_is_expiring_soon_within_90_days` | 90-day threshold | P0 |
| `test_is_expiring_soon_outside_90_days` | Not expiring soon | P0 |
| `test_is_valid` | Valid certificate check | P0 |
| `test_update_status_sets_expired` | Auto-expire logic | P0 |
| `test_update_status_sets_expiring_soon` | Auto-warning logic | P0 |
| `test_days_until_expiry_attribute` | Days calculation | P1 |
| `test_validity_period_in_years_attribute` | Years calculation | P2 |
| `test_status_color_attribute` | 5 status colors | P2 |
| `test_certificate_type_label` | 3 type labels | P2 |

---

### 14. ExternalAudit Model Tests
**File:** `tests/Unit/Models/ExternalAuditTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_generate_audit_number_format` | EXT-YY-#### format | P0 |
| `test_generate_audit_number_increments` | Sequential increment | P0 |
| `test_generate_audit_number_includes_soft_deleted` | Soft-deleted considered | P0 |
| `test_is_overdue_returns_false_when_completed` | Completed not overdue | P0 |
| `test_is_overdue_returns_true_when_past_end_date` | Overdue detection | P0 |
| `test_is_upcoming` | Upcoming detection (30 days) | P1 |
| `test_can_start` | Start eligibility | P1 |
| `test_can_complete` | Complete eligibility | P1 |
| `test_can_generate_certificate` | Certificate eligibility | P0 |
| `test_total_findings_attribute` | Sum calculation | P1 |
| `test_duration_in_days_attribute` | Duration calculation | P2 |
| `test_status_color_attribute` | 4 status colors | P2 |
| `test_result_color_attribute` | 4 result colors | P2 |
| `test_audit_type_label_attribute` | 5 type labels | P2 |

---

### 15. Document Model Tests
**File:** `tests/Unit/Models/DocumentTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_generate_document_number_quality_manual` | QM prefix | P0 |
| `test_generate_document_number_procedure` | PROC prefix | P0 |
| `test_generate_document_number_work_instruction` | WI prefix | P0 |
| `test_generate_document_number_form` | FORM prefix | P0 |
| `test_generate_document_number_record` | REC prefix | P0 |
| `test_generate_document_number_external` | EXT prefix | P0 |
| `test_generate_document_number_default` | DOC prefix | P0 |
| `test_increment_version_major` | Major version bump | P0 |
| `test_increment_version_minor` | Minor version bump | P0 |
| `test_is_draft` | Draft status check | P1 |
| `test_is_effective` | Effective status check | P1 |
| `test_is_obsolete` | Obsolete status check | P1 |
| `test_can_be_edited` | Edit eligibility | P0 |
| `test_can_be_reviewed` | Review eligibility | P0 |
| `test_can_be_approved` | Approval eligibility | P0 |
| `test_needs_review` | Review needed check | P0 |
| `test_submit_for_review` | Workflow transition | P0 |
| `test_review_workflow` | Review transition | P0 |
| `test_approve_workflow` | Approval transition | P0 |
| `test_make_effective` | Effective transition | P0 |
| `test_make_obsolete` | Obsolete transition | P1 |
| `test_archive` | Archive transition | P1 |
| `test_file_size_formatted_attribute` | Size formatting | P2 |
| `test_days_until_review_attribute` | Days calculation | P2 |

---

### 16. Notification Model Tests
**File:** `tests/Unit/Models/NotificationTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_mark_as_read` | Sets read_at timestamp | P1 |
| `test_mark_as_unread` | Clears read_at | P1 |
| `test_is_read` | Read status check | P1 |
| `test_is_unread` | Unread status check | P1 |
| `test_scope_unread` | Unread filter | P2 |
| `test_scope_read` | Read filter | P2 |
| `test_notifiable_morph` | Polymorphic relationship | P2 |

---

## Unit Tests - Services

### Number Generation Service Tests
**File:** `tests/Unit/Services/NumberGeneratorTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_concurrent_car_number_generation` | Race condition handling | P0 |
| `test_concurrent_complaint_number_generation` | Race condition handling | P0 |
| `test_concurrent_audit_number_generation` | Race condition handling | P0 |
| `test_concurrent_document_number_generation` | Race condition handling | P0 |
| `test_year_rollover_handling` | Year boundary | P1 |

---

## Feature Tests - Controllers

### 1. CarController Feature Tests
**File:** `tests/Feature/CarControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_index_displays_cars_list` | List view renders | P0 |
| `test_create_displays_form` | Create form renders | P1 |
| `test_store_creates_car` | Store validation passes | P0 |
| `test_store_validates_required_fields` | Validation errors | P0 |
| `test_show_displays_car` | Show view renders | P1 |
| `test_edit_displays_form` | Edit form renders | P1 |
| `test_update_modifies_car` | Update works | P0 |
| `test_destroy_soft_deletes_car` | Soft delete works | P0 |
| `test_approve_car` | Approval workflow | P0 |
| `test_close_car` | Closure workflow | P0 |
| `test_unauthorized_user_cannot_create` | Auth required | P0 |

### 2. CarResponseController Feature Tests
**File:** `tests/Feature/CarResponseControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_store_creates_response` | Response creation | P0 |
| `test_accept_response` | Accept workflow | P0 |
| `test_reject_response` | Reject workflow | P0 |
| `test_only_to_department_can_respond` | Department restriction | P0 |

### 3. AuditPlanController Feature Tests
**File:** `tests/Feature/AuditPlanControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_index_displays_plans` | List view | P0 |
| `test_store_creates_plan_with_departments` | Complex creation | P0 |
| `test_store_attaches_checklist_groups` | Pivot attachment | P0 |
| `test_update_syncs_departments` | Sync logic | P0 |

### 4. ExternalAuditController Feature Tests
**File:** `tests/Feature/ExternalAuditControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_index_displays_audits` | List view | P0 |
| `test_store_creates_audit` | Creation | P0 |
| `test_start_audit` | Start workflow | P0 |
| `test_complete_audit` | Complete workflow | P0 |
| `test_cancel_audit` | Cancel workflow | P1 |

### 5. CertificateController Feature Tests
**File:** `tests/Feature/CertificateControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_index_displays_certificates` | List view | P0 |
| `test_store_creates_certificate` | Creation | P0 |
| `test_certificate_linked_to_audit` | Audit linkage | P0 |

### 6. ComplaintController Feature Tests
**File:** `tests/Feature/ComplaintControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_index_displays_complaints` | List view | P0 |
| `test_store_creates_complaint` | Creation | P0 |
| `test_generate_car_from_complaint` | CAR generation | P0 |
| `test_close_complaint` | Closure workflow | P0 |

### 7. DocumentController Feature Tests
**File:** `tests/Feature/DocumentControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_index_displays_documents` | List view | P0 |
| `test_store_creates_document` | Creation | P0 |
| `test_submit_for_review` | Review workflow | P0 |
| `test_approve_document` | Approval workflow | P0 |
| `test_make_effective` | Effective workflow | P0 |
| `test_version_increment_on_edit` | Versioning | P0 |

### 8. DashboardController Feature Tests
**File:** `tests/Feature/DashboardControllerTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_dashboard_displays_statistics` | Stats calculation | P0 |
| `test_dashboard_recent_activities` | Activity list | P1 |
| `test_dashboard_charts_data` | Chart data format | P2 |

---

## API Tests

### Existing API Test Files
Location: `tests/Feature/Api/`

| File | Coverage | Status |
|------|----------|--------|
| `AuthControllerTest.php` | Login, Logout, Token | Exists |
| `CarControllerTest.php` | CAR CRUD | Exists |
| `CertificateControllerTest.php` | Certificate CRUD | Exists |
| `ComplaintControllerTest.php` | Complaint CRUD | Exists |
| `DocumentControllerTest.php` | Document CRUD | Exists |
| `ExternalAuditControllerTest.php` | Audit CRUD | Exists |

### Missing API Tests to Create

| Controller | Tests Needed | Priority |
|------------|--------------|----------|
| SectorApiController | CRUD + List | P1 |
| DepartmentApiController | CRUD + List | P1 |
| UserApiController | CRUD + Roles | P1 |
| AuditPlanApiController | CRUD + Departments | P1 |
| AuditQuestionApiController | CRUD | P2 |
| CheckListGroupApiController | CRUD | P2 |
| NotificationApiController | List + Mark Read | P2 |

---

## Integration Tests

### 1. CAR Workflow Integration Tests
**File:** `tests/Integration/CarWorkflowTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_full_car_lifecycle` | Create → Response → Follow-up → Close | P0 |
| `test_car_from_audit_finding` | Audit Response → CAR | P0 |
| `test_car_from_complaint` | Complaint → CAR | P0 |
| `test_car_rejection_resubmit` | Reject → Edit → Resubmit | P1 |

### 2. Document Workflow Integration Tests
**File:** `tests/Integration/DocumentWorkflowTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_full_document_lifecycle` | Draft → Review → Approve → Effective | P0 |
| `test_document_supersedes_previous` | Supersedes relationship | P0 |
| `test_document_version_history` | Version tracking | P1 |

### 3. Audit Execution Integration Tests
**File:** `tests/Integration/AuditExecutionTest.php`

| Test Case | Description | Priority |
|-----------|-------------|----------|
| `test_audit_plan_execution` | Plan → Execute → Complete | P0 |
| `test_audit_creates_findings` | Non-compliance → CAR | P0 |
| `test_audit_generates_certificate` | Pass → Certificate | P0 |

---

## E2E Tests

### Current Status
**File:** `tests/comprehensive-e2e.cjs`
- **Total Tests:** 45
- **Pass Rate:** 100%
- **Coverage:** All CRUD operations for major modules

### E2E Test Coverage

| Module | Tests | Status |
|--------|-------|--------|
| Authentication | 3 | ✅ |
| Dashboard | 2 | ✅ |
| Sectors | 5 | ✅ |
| Departments | 5 | ✅ |
| Users | 5 | ✅ |
| External Audits | 5 | ✅ |
| CARs | 5 | ✅ |
| Audit Plans | 5 | ✅ |
| Audit Questions | 5 | ✅ |
| CheckList Groups | 5 | ✅ |

### E2E Tests to Add

| Test Case | Description | Priority |
|-----------|-------------|----------|
| CAR Response Workflow | Submit, review, accept/reject | P1 |
| Document Approval Flow | Draft → Effective | P1 |
| Complaint to CAR Flow | Create complaint → Generate CAR | P1 |
| Certificate Generation | Complete audit → Generate cert | P2 |

---

## Test Priority Matrix

### P0 - Critical (Must Have)
- Number generation uniqueness
- Authentication/Authorization
- CRUD operations for core models
- Workflow state transitions
- Data integrity (relationships)

### P1 - High (Should Have)
- All relationship tests
- Scope/filter tests
- API endpoint coverage
- Integration workflows

### P2 - Medium (Nice to Have)
- Accessor/color attribute tests
- Edge case handling
- Performance tests
- Concurrent access tests

---

## Implementation Roadmap

### Phase 1: Foundation (Week 1)
- [ ] Set up test database configuration
- [ ] Create base test case classes
- [ ] Create model factories for all models
- [ ] Implement P0 number generation tests

### Phase 2: Model Unit Tests (Week 2)
- [ ] Car, CarResponse, CarFollowUp tests
- [ ] AuditPlan, AuditQuestion, AuditResponse tests
- [ ] User, Role, Permission tests
- [ ] Department, Sector tests

### Phase 3: Extended Model Tests (Week 3)
- [ ] ExternalAudit, Certificate tests
- [ ] CustomerComplaint tests
- [ ] Document tests
- [ ] CheckListGroup tests

### Phase 4: Feature Tests (Week 4)
- [ ] Web controller tests
- [ ] Form validation tests
- [ ] Authorization tests

### Phase 5: Integration & API (Week 5)
- [ ] Workflow integration tests
- [ ] Missing API tests
- [ ] Cross-module tests

### Phase 6: Polish (Week 6)
- [ ] P2 accessor tests
- [ ] Edge case coverage
- [ ] Test documentation
- [ ] CI/CD integration

---

## Test Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/Models/CarTest.php

# Run tests with coverage
php artisan test --coverage

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run E2E tests
node tests/comprehensive-e2e.cjs

# Run with verbose output
php artisan test -v
```

---

## Appendix: Test File Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── CarTest.php
│   │   ├── CarResponseTest.php
│   │   ├── CarFollowUpTest.php
│   │   ├── AuditPlanTest.php
│   │   ├── AuditQuestionTest.php
│   │   ├── AuditResponseTest.php
│   │   ├── DepartmentTest.php
│   │   ├── SectorTest.php
│   │   ├── UserTest.php
│   │   ├── RoleTest.php
│   │   ├── PermissionTest.php
│   │   ├── CheckListGroupTest.php
│   │   ├── CustomerComplaintTest.php
│   │   ├── CertificateTest.php
│   │   ├── ExternalAuditTest.php
│   │   ├── DocumentTest.php
│   │   └── NotificationTest.php
│   └── Services/
│       └── NumberGeneratorTest.php
├── Feature/
│   ├── CarControllerTest.php
│   ├── CarResponseControllerTest.php
│   ├── AuditPlanControllerTest.php
│   ├── ExternalAuditControllerTest.php
│   ├── CertificateControllerTest.php
│   ├── ComplaintControllerTest.php
│   ├── DocumentControllerTest.php
│   ├── DashboardControllerTest.php
│   └── Api/
│       ├── AuthControllerTest.php
│       ├── CarControllerTest.php
│       ├── CertificateControllerTest.php
│       ├── ComplaintControllerTest.php
│       ├── DocumentControllerTest.php
│       └── ExternalAuditControllerTest.php
├── Integration/
│   ├── CarWorkflowTest.php
│   ├── DocumentWorkflowTest.php
│   └── AuditExecutionTest.php
└── comprehensive-e2e.cjs
```
