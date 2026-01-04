# Gym Management System - API Documentation

## Base URL

All endpoints are prefixed with `/api`.

- **Development**: `http://127.0.0.1:8000/api`
- **Production**: `https://your-domain.com/api`

---

## Authentication

This API uses token-based authentication (Laravel Sanctum style).

### Required Headers (Protected Routes)

```
Accept: application/json
Authorization: Bearer {token}
```

---

## Contents

1. [Authentication](#authentication)
2. [Users (Admin)](#users-admin)
3. [Profile](#profile)
4. [Messages (Admin ↔ Users)](#messages-admin--users)
5. [Attendance (QR / Scan)](#attendance-qr--scan)
6. [Dashboard](#dashboard)
7. [Dashboard Export](#dashboard-export)
8. [Blogs](#blogs)
9. [Subscriptions](#subscriptions)
10. [Pricing](#pricing)
11. [Trainer Bookings](#trainer-bookings)
12. [Trainer Module](#trainer-module)
13. [Captcha](#captcha)
14. [Errors](#errors)
15. [Endpoint Access Matrix](#endpoint-access-matrix)

---

## Authentication

### 1) Login

**POST** `/login`

**Body**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Success (200)**
```json
{
  "message": "Login successful",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "expires_at": "2026-01-02T12:00:00+00:00",
  "user": {
    "id": 1,
    "name": "John",
    "email": "user@example.com",
    "role": "administrator"
  }
}
```

---

### 2) Register (Admin creates users)

**POST** `/register`  ✅ Protected (admin)

**Notes**
- Creating role `administrator` is blocked and returns **403**.

**Body (fields validated by `RegisterRequest`)**
```json
{
  "name": "Member One",
  "email": "member1@example.com",
  "password": "password",
  "password_confirmation": "password",
  "role": "user"
}
```

**Success (201)**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 10,
    "name": "Member One",
    "email": "member1@example.com",
    "role": "user",
    "created_at": "2026-01-02T00:00:00.000000Z"
  }
}
```

---

### 3) Logout

**POST** `/logout` ✅ Protected

**Success (200)**
```json
{
  "message": "Logged out successfully"
}
```

---

## Users (Admin)

### 4) List Users

**GET** `/users` ✅ Protected (admin)

**Query Params**
- `deleted` (optional): `true|false`
  - If `true`, returns soft-deleted users.

**Example**
```
GET /api/users
GET /api/users?deleted=true
```

**Success (200)**
```json
{
  "message": "Users retrieved successfully",
  "total": 2,
  "users": [
    { "id": 1, "name": "John", "email": "john@example.com", "role": "user" }
  ]
}
```

---

### 5) Delete User (Soft delete)

**DELETE** `/users/{id}` ✅ Protected (admin)

**Success (200)**
```json
{
  "message": "User deleted successfully (soft delete). User can be restored.",
  "deleted_user": { "id": 5, "name": "User", "email": "u@example.com", "role": "user" },
  "deleted_at": "2026-01-02T10:00:00.000000Z"
}
```

---

### 6) Restore User

**POST** `/users/{id}/restore` ✅ Protected (admin)

**Success (200)**
```json
{
  "message": "User restored successfully",
  "user": {
    "id": 5,
    "name": "User",
    "email": "u@example.com",
    "role": "user",
    "deleted_at": null,
    "restored_at": "2026-01-02T10:05:00.000000Z"
  }
}
```

---

### 7) Send Password Reset Link (Admin)

**POST** `/users/forgot-password` ✅ Protected (admin)

**Body**
```json
{ "email": "user@example.com" }
```

**OR**
```json
{ "user_id": 5 }
```

**Success (Always 200)**
```json
{
  "message": "If the email address exists in our system, a password reset link has been sent."
}
```

---

## Profile

### 8) Current User

**GET** `/user` ✅ Protected

---

### 9) Update Profile

**PUT** `/user/profile` ✅ Protected  
**PATCH** `/user/profile` ✅ Protected

**Body (any combination)**
```json
{
  "name": "New Name",
  "email": "new@example.com",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

**Success (200)**
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "New Name",
    "email": "new@example.com",
    "role": "user",
    "email_verified_at": null,
    "updated_at": "2026-01-02T10:10:00.000000Z"
  }
}
```

If no changes:
```json
{
  "message": "No changes provided",
  "user": { "id": 1, "name": "John", "email": "john@example.com", "role": "user" }
}
```

---

## Messages (Admin ↔ Users)

> These endpoints are implemented in `Api\MessageController` and are designed for **admin chatting with users**.

### 10) List Conversations (Admin)

**GET** `/messages` ✅ Protected

**Success (200)**
```json
{
  "conversations": [
    {
      "user_id": 3,
      "user_name": "Trainer A",
      "user_email": "trainer@example.com",
      "user_role": "trainer",
      "preview": "Hi Admin",
      "updated_at": "2026-01-02T10:00:00.000000Z"
    }
  ]
}
```

---

### 11) Get Message Thread With User (Admin)

**GET** `/messages/{user}` ✅ Protected

**Success (200)**
```json
{
  "user": { "id": 3, "name": "Trainer A", "email": "trainer@example.com", "role": "trainer" },
  "messages": [
    {
      "id": 1,
      "body": "Hello",
      "created_at": "2026-01-02T09:00:00.000000Z",
      "is_admin": true,
      "sender_name": "Admin"
    }
  ]
}
```

**Note**
- Unread messages from that user to admin are marked as read (`read_at` set).

---

### 12) Send Message To User (Admin)

**POST** `/messages/{user}` ✅ Protected

**Body**
```json
{ "body": "Hello!" }
```

**Success (201)**
```json
{ "status": "sent" }
```

---

## Attendance (QR / Scan)

### 13) Attendance Users (for scanning UI)

**GET** `/attendance/users` ✅ Protected

**Success (200)**
```json
{
  "users": [
    { "id": 2, "name": "Member One", "role": "user" },
    { "id": 3, "name": "Trainer A", "role": "trainer" }
  ]
}
```

---

### 14) Get QR Links (User + Trainer)

**GET** `/attendance/qr` ✅ Protected

**Success (200)**
```json
{
  "user_qr": "http://127.0.0.1:8000/attendance/scan?type=user&token=XXXX",
  "trainer_qr": "http://127.0.0.1:8000/attendance/scan?type=trainer&token=YYYY"
}
```

---

### 15) Refresh QR (New tokens)

**POST** `/attendance/qr/refresh` ✅ Protected

**Success (200)**
```json
{
  "user_qr": "http://127.0.0.1:8000/attendance/scan?type=user&token=NEW_XXXX",
  "trainer_qr": "http://127.0.0.1:8000/attendance/scan?type=trainer&token=NEW_YYYY"
}
```

---

### 16) Scan Attendance (Admin selects user + type)

**POST** `/attendance/scan` ✅ Protected

**Body**
```json
{
  "user_id": 3,
  "qr_type": "trainer"
}
```

**Success (200)**
```json
{
  "message": "Scan recorded successfully.",
  "record": {
    "username": "Trainer A",
    "role": "trainer",
    "action": "check_in",
    "timestamp": "2026-01-02T10:20:00+00:00"
  }
}
```

**Validation notes**
- `qr_type` must be `user` or `trainer`
- `user_id` must exist and role must be `user` or `trainer`
- If user role != qr_type -> **422**

---

### 17) Scan Attendance From QR (User scans their own QR)

**POST** `/attendance/scan/qr` ✅ Protected

**Query Params (required)**
- `type`: `user|trainer`
- `token`: the current token in cache

**Example**
```
POST /api/attendance/scan/qr?type=user&token=XXXX
```

**Success (200)**
```json
{
  "message": "Check-in recorded successfully.",
  "record": {
    "username": "Member One",
    "role": "user",
    "action": "check_in",
    "timestamp": "2026-01-02T10:25:00+00:00"
  }
}
```

**Possible 422 errors**
- Invalid QR type
- Expired token
- Logged-in user role doesn't match QR type

---

### 18) Attendance Records

**GET** `/attendance/records` ✅ Protected

**Query Params (optional)**
- `username` (string)
- `start_date` (date)
- `end_date` (date)

**Example**
```
GET /api/attendance/records?username=John&start_date=2026-01-01&end_date=2026-01-02
```

**Success (200)**
```json
{
  "records": [
    {
      "username": "Trainer A",
      "role": "trainer",
      "action": "check_in",
      "timestamp": "2026-01-02T10:20:00+00:00",
      "total_check_in_days": 5
    }
  ]
}
```

---

### 19) Checked-In Summary (Today)

**GET** `/attendance/checked-in` ✅ Protected

**Success (200)**
```json
{
  "total_members": 20,
  "active_count": 4,
  "active_users": [
    { "id": 3, "name": "Trainer A", "role": "trainer", "action": "check_in", "timestamp": "..." }
  ]
}
```

---

## Dashboard

### 20) Attendance Report (Chart data)

**GET** `/dashboard/attendance-report` ✅ Protected

**Query Params**
- `period` (optional): `7days` (default), `1month`, `6months`

**Example**
```
GET /api/dashboard/attendance-report?period=1month
```

**Success (200)**
```json
{
  "period": "1month",
  "labels": ["Dec 04", "Dec 05"],
  "check_ins": [2, 3],
  "check_outs": [1, 2]
}
```

---

## Dashboard Export

### 21) Export Dashboard Report

**GET** `/dashboard/export/{format}` ✅ Protected

**Supported**
- `excel`  → returns `.xls` (HTML table with Excel content-type)
- `json`   → returns `.json`

**Examples**
```
GET /api/dashboard/export/excel
GET /api/dashboard/export/json
```

**Notes**
- Any other format results in **404**.

---

## Blogs

### 22) List Published Blog Posts

**GET** `/blogs`

**Success (200)**
```json
{
  "data": [
    {
      "id": 1,
      "title": "How to Build Muscle",
      "slug": "how-to-build-muscle",
      "summary": "...",
      "content": "...",
      "cover_image_url": "http://127.0.0.1:8000/storage/...",
      "published_at": "2026-01-01T00:00:00+00:00",
      "updated_at": "2026-01-02T00:00:00+00:00"
    }
  ]
}
```

---

### 23) Blog Details By Slug

**GET** `/blogs/{slug}`

**Example**
```
GET /api/blogs/how-to-build-muscle
```

**Success (200)**
```json
{
  "data": {
    "id": 1,
    "title": "How to Build Muscle",
    "slug": "how-to-build-muscle",
    "summary": "...",
    "content": "...",
    "cover_image_url": null,
    "published_at": "2026-01-01T00:00:00+00:00",
    "updated_at": "2026-01-02T00:00:00+00:00"
  }
}
```

---

## Subscriptions

### 24) List Subscriptions

**GET** `/subscriptions` ✅ Protected

**Success (200)**
```json
{
  "subscriptions": [
    {
      "id": 1,
      "member_name": "Member One",
      "plan_name": "Monthly Plan",
      "duration_days": 30,
      "price": 80000,
      "start_date": "2026-01-01",
      "end_date": "2026-01-31",
      "is_on_hold": false,
      "status": "Active"
    }
  ]
}
```

**Notes**
- If subscription is on hold, API calculates adjusted end date (adds hold days).

---

### 25) Subscription Options (Members + Plans)

**GET** `/subscriptions/options` ✅ Protected

**Success (200)**
```json
{
  "members": [
    { "id": 2, "name": "Member One", "email": "member@example.com" }
  ],
  "plans": [
    { "id": 1, "name": "Monthly", "duration_days": 30, "price": 80000 }
  ]
}
```

---

### 26) Create Subscription

**POST** `/subscriptions` ✅ Protected

**Body**
```json
{
  "member_id": 2,
  "membership_plan_id": 1,
  "start_date": "2026-01-02"
}
```

`start_date` is optional (defaults to today).

**Success (201)**
```json
{
  "message": "Subscription created successfully.",
  "subscription_id": 10
}
```

---

### 27) Hold Subscription

**POST** `/subscriptions/{subscription}/hold` ✅ Protected

**Success (200)**
```json
{ "message": "Subscription placed on hold." }
```

**422 cases**
- subscription expired
- already on hold

---

### 28) Resume Subscription

**POST** `/subscriptions/{subscription}/resume` ✅ Protected

**Success (200)**
```json
{ "message": "Subscription resumed." }
```

**Notes**
- End date is extended by number of hold days.

---

## Pricing

### 29) Update Monthly Subscription Price

**PUT** `/pricing/monthly` ✅ Protected

**Body**
```json
{ "monthly_subscription_price": 80000 }
```

**Success (200)**
```json
{
  "message": "Monthly subscription price updated.",
  "pricing": { "monthly_subscription_price": 80000 }
}
```

---

### 30) Update Quarterly Subscription Price

**PUT** `/pricing/quarterly` ✅ Protected

**Body**
```json
{ "quarterly_subscription_price": 240000 }
```

---

### 31) Update Annual Subscription Price

**PUT** `/pricing/annual` ✅ Protected

**Body**
```json
{ "annual_subscription_price": 960000 }
```

---

### 32) Update Trainer Price (per session)

**PUT** `/pricing/trainers/{user}` ✅ Protected

**Notes**
- `{user}` must be a `trainer` role, otherwise **404**.

**Body**
```json
{ "price_per_session": 30000 }
```

**Success (200)**
```json
{
  "message": "Session price updated for Trainer A.",
  "trainer": { "id": 3, "name": "Trainer A" },
  "pricing": { "price_per_session": 30000 }
}
```

---

## Trainer Bookings

### 33) List Bookings

**GET** `/trainer-bookings` ✅ Protected

**Success (200)**
```json
{
  "bookings": [
    {
      "id": 1,
      "member_id": 2,
      "member_name": "Member One",
      "trainer_id": 3,
      "trainer_name": "Trainer A",
      "session_datetime": "2026-01-02 16:00:00",
      "duration_minutes": 60,
      "sessions_count": 1,
      "price_per_session": 30000,
      "total_price": 30000,
      "status": "confirmed",
      "paid_status": "unpaid",
      "notes": null
    }
  ]
}
```

---

### 34) Create Booking

**POST** `/trainer-bookings` ✅ Protected

**Body**
```json
{
  "member_id": 2,
  "trainer_id": 3,
  "session_datetime": "2026-01-05 16:00:00",
  "duration_minutes": 60,
  "sessions_count": 2,
  "price_per_session": 30000,
  "status": "confirmed",
  "paid_status": "unpaid",
  "notes": "Bring towel"
}
```

**Notes**
- `price_per_session` is optional. If omitted, server uses trainer pricing or default (30,000).

**Success (201)**
```json
{
  "message": "Trainer booking created successfully.",
  "booking_id": 12
}
```

---

### 35) Mark Booking As Paid

**PATCH** `/trainer-bookings/{booking}/mark-paid` ✅ Protected

**Success (200)**
```json
{ "message": "Booking marked as paid." }
```

---

## Trainer Module

### 36) Trainer Home

**GET** `/trainer/home` ✅ Protected (trainer)

---

### 37) Trainer Check-in

**GET** `/trainer/check-in` ✅ Protected (trainer)

---

### 38) Trainer Scan From QR

**POST** `/trainer/check-in/scan` ✅ Protected (trainer)

---

### 39) Trainer Subscriptions

**GET** `/trainer/subscriptions` ✅ Protected (trainer)

---

### 40) Trainer Messages (Trainer ↔ Admin)

**GET** `/trainer/messages` ✅ Protected (trainer)

**Success (200)**
```json
{
  "admin": { "id": 1, "name": "Admin", "email": "admin@example.com" },
  "messages": [
    {
      "id": 1,
      "body": "Hello admin",
      "created_at": "2026-01-02T10:00:00+00:00",
      "is_trainer": true,
      "sender_name": "Trainer A"
    }
  ]
}
```

---

### 41) Trainer Send Message to Admin

**POST** `/trainer/messages` ✅ Protected (trainer)

**Body**
```json
{ "body": "I will be late 10 minutes." }
```

**Success (201)**
```json
{ "status": "sent" }
```

---

## Captcha

### 42) Captcha API

**GET** `/captcha/api/{config?}`

Example:
```
GET /captcha/api/default
```

### 43) Captcha Image (API)

**GET** `/captcha`

**Success (200)**
```json
{
  "captcha": "<img src=\"data:image/png;base64,...\" />"
}
```

---

### 44) Captcha Refresh (API)

**GET** `/captcha/refresh`

**Success (200)**
```json
{
  "captcha": "<img src=\"data:image/png;base64,...\" />"
}
```

---


---

## Errors

| Status | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthenticated (missing/invalid token) |
| 403 | Forbidden (role/permission) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error (check `storage/logs/laravel.log`) |

## Endpoint Access Matrix

**Legend**
- **Public**: No authentication required
- **Auth**: Any authenticated user (Bearer token required)
- **Admin**: Authenticated user with `administrator` role
- **Trainer**: Authenticated user with `trainer` role
- **User**: Authenticated user with `user` role

| Method | Endpoint | Access |
| --- | --- | --- |
| POST | `/login` | Public |
| POST | `/logout` | Auth |
| POST | `/register` | Admin |
| GET | `/version` | Public |
| GET | `/blogs` | Public |
| GET | `/blogs/{slug}` | Public |
| GET | `/captcha` | Public |
| GET | `/captcha/refresh` | Public |
| GET | `/captcha/api/{config?}` | Public |
| GET | `/user` | Auth |
| PUT | `/user/profile` | Auth |
| PATCH | `/user/profile` | Auth |
| GET | `/notifications` | Auth |
| POST | `/notifications/{notificationId}/read` | Auth |
| POST | `/notifications/read-all` | Auth |
| GET | `/my/messages` | Auth |
| POST | `/my/messages` | Auth |
| GET | `/users` | Admin |
| POST | `/users/forgot-password` | Admin |
| DELETE | `/users/{id}` | Admin |
| POST | `/users/{id}/restore` | Admin |
| PUT | `/pricing/monthly` | Admin |
| PUT | `/pricing/quarterly` | Admin |
| PUT | `/pricing/annual` | Admin |
| PUT | `/pricing/trainers/{user}` | Admin |
| GET | `/trainer-bookings` | Admin |
| POST | `/trainer-bookings` | Admin |
| PATCH | `/trainer-bookings/{booking}/mark-paid` | Admin |
| GET | `/subscriptions` | Admin |
| POST | `/subscriptions` | Admin |
| GET | `/subscriptions/options` | Admin |
| POST | `/subscriptions/{subscription}/hold` | Admin |
| POST | `/subscriptions/{subscription}/resume` | Admin |
| GET | `/attendance/users` | Admin |
| GET | `/attendance/qr` | Admin |
| GET | `/attendance/records` | Admin |
| GET | `/attendance/checked-in` | Admin |
| POST | `/attendance/scan` | Admin |
| POST | `/attendance/scan/qr` | Admin |
| POST | `/attendance/qr/refresh` | Admin |
| GET | `/dashboard/attendance-report` | Admin |
| GET | `/dashboard/export/{format}` | Admin |
| GET | `/messages` | Admin |
| GET | `/messages/{user}` | Admin |
| POST | `/messages/{user}` | Admin |
| GET | `/trainer/home` | Trainer |
| GET | `/trainer/check-in` | Trainer |
| POST | `/trainer/check-in/scan` | Trainer |
| GET | `/trainer/subscriptions` | Trainer |
| GET | `/trainer/messages` | Trainer |
| POST | `/trainer/messages` | Trainer |
| GET | `/user/home` | User |
| GET | `/user/check-in` | User |
| POST | `/user/check-in/scan` | User |
| GET | `/user/subscriptions` | User |
| GET | `/user/messages` | User |
| POST | `/user/messages` | User |


---

**Last Updated:** January 2026  
**API Version:** 1.0
