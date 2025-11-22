# ISO 9001:2015 Audit Management System - API Documentation

## Overview

RESTful API for the ISO 9001:2015 Audit Management System with Laravel Sanctum authentication.

**Base URL**: `http://your-domain.com/api/v1`

**Current Version**: v1

## Authentication

The API uses Laravel Sanctum for token-based authentication.

### Login

**Endpoint**: `POST /api/v1/auth/login`

**Rate Limit**: 5 requests per minute per IP

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "mobile_app"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "admin",
      "department_id": 1,
      "sector_id": 2
    },
    "token": "1|abc123def456...",
    "token_type": "Bearer"
  }
}
```

### Logout

**Endpoint**: `POST /api/v1/auth/logout`

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get User Profile

**Endpoint**: `GET /api/v1/auth/profile`

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "admin",
      "phone": "+123456789",
      "job_title": "QA Manager",
      "employee_id": "EMP001",
      "department": {
        "id": 1,
        "name": "Quality Assurance",
        "code": "QA"
      },
      "sector": {
        "id": 2,
        "name": "Manufacturing",
        "code": "MFG"
      },
      "created_at": "2025-01-01 10:00:00",
      "updated_at": "2025-01-15 14:30:00"
    }
  }
}
```

## Rate Limiting

The API implements the following rate limits:

- **General API**: 60 requests per minute per user/IP
- **Authentication**: 5 requests per minute per IP
- **Read Operations**: 100 requests per minute per user/IP
- **Write Operations**: 30 requests per minute per user/IP

Rate limit headers are included in all responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `Retry-After`: Seconds until rate limit resets (when exceeded)

## External Audits API

### List Audits

**Endpoint**: `GET /api/v1/audits`

**Headers**: `Authorization: Bearer {token}`

**Query Parameters**:
- `per_page` (optional, default: 15): Results per page
- `status` (optional): Filter by status (scheduled, in_progress, completed, cancelled)
- `audit_type` (optional): Filter by type (initial, surveillance, recertification, special)

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "audit_number": "EA-2025-001",
        "audit_type": "surveillance",
        "standard": "ISO 9001:2015",
        "certification_body": "BSI",
        "lead_auditor_name": "Jane Smith",
        "scheduled_start_date": "2025-02-15",
        "scheduled_end_date": "2025-02-17",
        "status": "scheduled",
        "result": "pending",
        "department": {...},
        "sector": {...}
      }
    ],
    "per_page": 15,
    "total": 45
  }
}
```

### Create Audit

**Endpoint**: `POST /api/v1/audits`

**Headers**: `Authorization: Bearer {token}`

**Request Body**:
```json
{
  "audit_number": "EA-2025-002",
  "audit_type": "surveillance",
  "standard": "ISO 9001:2015",
  "certification_body": "BSI",
  "lead_auditor_name": "Jane Smith",
  "lead_auditor_email": "jane@bsi.com",
  "scheduled_start_date": "2025-03-01",
  "scheduled_end_date": "2025-03-03",
  "scope": "Manufacturing processes and quality management",
  "department_id": 1,
  "sector_id": 2
}
```

### Get Audit Statistics

**Endpoint**: `GET /api/v1/audits/statistics`

**Headers**: `Authorization: Bearer {token}`

**Response** (200 OK):
```json
{
  "success": true,
  "data": {
    "total": 45,
    "scheduled": 12,
    "in_progress": 3,
    "completed": 28,
    "passed": 25,
    "with_certificate": 22,
    "upcoming": 5
  }
}
```

## CARs (Corrective Action Requests) API

### List CARs

**Endpoint**: `GET /api/v1/cars`

**Query Parameters**:
- `per_page` (optional, default: 15)
- `status` (optional): open, in_progress, pending_verification, closed, cancelled
- `priority` (optional): low, medium, high, critical

### Create CAR

**Endpoint**: `POST /api/v1/cars`

**Request Body**:
```json
{
  "car_number": "CAR-2025-001",
  "subject": "Non-conformance in welding process",
  "description": "Welding parameters not within specified range",
  "source": "internal_audit",
  "priority": "high",
  "issued_date": "2025-01-20",
  "due_date": "2025-02-20",
  "assigned_to": 5,
  "department_id": 2,
  "sector_id": 3
}
```

### Get CAR Statistics

**Endpoint**: `GET /api/v1/cars/statistics`

## Certificates API

### List Certificates

**Endpoint**: `GET /api/v1/certificates`

**Query Parameters**:
- `per_page` (optional, default: 15)
- `status` (optional): active, expiring_soon, expired, suspended, revoked
- `certificate_type` (optional): iso_certification, accreditation, license, other

### Get Expiring Certificates

**Endpoint**: `GET /api/v1/certificates/expiring`

**Query Parameters**:
- `days` (optional, default: 30): Days until expiry

### Create Certificate

**Endpoint**: `POST /api/v1/certificates`

**Request Body**:
```json
{
  "certificate_number": "CERT-ISO-2025-001",
  "certificate_name": "ISO 9001:2015 Certification",
  "certificate_type": "iso_certification",
  "issuing_authority": "BSI",
  "issue_date": "2025-01-15",
  "expiry_date": "2028-01-15",
  "scope": "Design, manufacture and supply of automotive components",
  "department_id": 1,
  "audit_id": 5
}
```

## Documents API

### List Documents

**Endpoint**: `GET /api/v1/documents`

**Query Parameters**:
- `per_page` (optional, default: 15)
- `status` (optional): draft, active, under_review, obsolete, archived
- `category_id` (optional): Filter by document category ID

### Get Documents Due for Review

**Endpoint**: `GET /api/v1/documents/due-for-review`

### Create Document

**Endpoint**: `POST /api/v1/documents`

**Request Body**:
```json
{
  "document_number": "QMS-PR-001",
  "title": "Quality Management Procedure",
  "category_id": 2,
  "description": "Procedure for quality management system",
  "revision": "2.0",
  "issue_date": "2025-01-10",
  "review_date": "2026-01-10",
  "owner_id": 3,
  "department_id": 1
}
```

## Complaints API

### List Complaints

**Endpoint**: `GET /api/v1/complaints`

**Query Parameters**:
- `per_page` (optional, default: 15)
- `status` (optional): new, investigating, action_required, resolved, closed, rejected
- `severity` (optional): low, medium, high, critical
- `category` (optional): product_quality, service_quality, delivery, documentation, other

### Get Unresolved Complaints

**Endpoint**: `GET /api/v1/complaints/unresolved`

### Create Complaint

**Endpoint**: `POST /api/v1/complaints`

**Request Body**:
```json
{
  "complaint_number": "COMP-2025-001",
  "subject": "Product defect in batch 123",
  "description": "Customer reported defects in product finish",
  "customer_name": "ABC Manufacturing Ltd",
  "customer_email": "contact@abc.com",
  "customer_phone": "+1234567890",
  "category": "product_quality",
  "severity": "high",
  "complaint_date": "2025-01-18",
  "assigned_to": 4,
  "department_id": 2
}
```

## Common Response Formats

### Success Response
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### HTTP Status Codes

- `200 OK`: Successful GET, PUT, PATCH requests
- `201 Created`: Successful POST request
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Missing or invalid authentication token
- `403 Forbidden`: Authenticated but not authorized for this action
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

## CRUD Operations

All resource endpoints follow RESTful conventions:

- `GET /api/v1/{resource}` - List resources (with pagination and filtering)
- `POST /api/v1/{resource}` - Create new resource
- `GET /api/v1/{resource}/{id}` - Get specific resource
- `PUT/PATCH /api/v1/{resource}/{id}` - Update resource
- `DELETE /api/v1/{resource}/{id}` - Delete resource
- `GET /api/v1/{resource}/statistics` - Get resource statistics

## Best Practices

1. **Always include the Authorization header** with your Sanctum token
2. **Handle rate limits gracefully** by checking response headers
3. **Use pagination** for list endpoints to improve performance
4. **Validate data client-side** before sending to reduce API calls
5. **Handle errors appropriately** based on HTTP status codes
6. **Cache responses** where appropriate to reduce API calls
7. **Use filters and search** to reduce data transfer

## Example Usage (cURL)

### Login
```bash
curl -X POST https://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "curl_client"
  }'
```

### List Audits
```bash
curl -X GET "https://your-domain.com/api/v1/audits?per_page=10&status=scheduled" \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

### Create CAR
```bash
curl -X POST https://your-domain.com/api/v1/cars \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Content-Type: application/json" \
  -d '{
    "car_number": "CAR-2025-001",
    "subject": "Non-conformance found",
    "description": "Details of the non-conformance",
    "source": "internal_audit",
    "priority": "high",
    "issued_date": "2025-01-20",
    "due_date": "2025-02-20",
    "assigned_to": 5
  }'
```

## Health Check

**Endpoint**: `GET /api/health`

**No authentication required**

**Response** (200 OK):
```json
{
  "success": true,
  "message": "API is running",
  "version": "v1",
  "timestamp": "2025-01-20T10:30:00+00:00"
}
```

## Support

For API support or to report issues, please contact the development team.
