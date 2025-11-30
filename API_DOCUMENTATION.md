# 📚 Floou Backend API Documentation

## Base URL
```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

## Authentication
API menggunakan **Laravel Sanctum** untuk authentication. Setelah login/register, gunakan token yang diterima di header setiap request:

```http
Authorization: Bearer {your-token-here}
```

---

## 📑 Table of Contents
1. [Authentication](#authentication-endpoints)
2. [Plants (Tanaman)](#plants-endpoints)
3. [Categories](#categories-endpoints)
4. [Plant Types](#plant-types-endpoints)
5. [Orders](#orders-endpoints)
6. [Reviews](#reviews-endpoints)
7. [Notifications](#notifications-endpoints)
8. [Admin - User Management](#admin-user-management)
9. [Admin - Dashboard](#admin-dashboard)

---

## 🔐 Authentication Endpoints

### 1. Register
**Endpoint:** `POST /register`  
**Auth Required:** No

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890",  // optional
  "address": "Jl. Example No. 123"  // optional
}
```

**Success Response (201):**
```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "phone": "081234567890",
    "address": "Jl. Example No. 123",
    "created_at": "2025-11-30T10:00:00.000000Z",
    "updated_at": "2025-11-30T10:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz..."
}
```

**Error Response (422):**
```json
{
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

---

### 2. Login
**Endpoint:** `POST /login`  
**Auth Required:** No

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "phone": "081234567890",
    "address": "Jl. Example No. 123"
  },
  "token": "2|abcdefghijklmnopqrstuvwxyz..."
}
```

**Error Response (401):**
```json
{
  "message": "Invalid credentials"
}
```

---

### 3. Logout
**Endpoint:** `POST /logout`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "message": "Logout successful"
}
```

---

### 4. Get Current User
**Endpoint:** `GET /me`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "phone": "081234567890",
    "address": "Jl. Example No. 123"
  }
}
```

---

## 🌱 Plants Endpoints

### 1. Get All Plants (Public)
**Endpoint:** `GET /plants`  
**Auth Required:** No

**Query Parameters:**
- `search` (string): Search by name or description
- `category_id` (integer): Filter by category
- `plant_type_id` (integer): Filter by plant type
- `min_price` (number): Minimum price
- `max_price` (number): Maximum price
- `in_stock` (1 or 0): Filter only available plants
- `sort_by` (string): `name`, `price`, `created_at`, `stock`
- `sort_order` (string): `asc` or `desc`
- `per_page` (integer): Items per page (default: 12)

**Example Request:**
```
GET /plants?search=monstera&category_id=1&in_stock=1&sort_by=price&sort_order=asc&per_page=10
```

**Success Response (200):**
```json
{
  "plants": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Monstera Deliciosa",
        "slug": "monstera-deliciosa",
        "description": "Tanaman hias populer dengan daun besar berlubang",
        "price": 150000,
        "stock": 50,
        "image": "plants/monstera.jpg",
        "image_url": "http://localhost:8000/storage/plants/monstera.jpg",
        "category_id": 1,
        "plant_type_id": 2,
        "average_rating": 4.5,
        "reviews_count": 12,
        "created_at": "2025-11-30T10:00:00.000000Z",
        "updated_at": "2025-11-30T10:00:00.000000Z",
        "category": {
          "id": 1,
          "name": "Tanaman Hias",
          "slug": "tanaman-hias"
        },
        "plant_type": {
          "id": 2,
          "name": "Indoor",
          "slug": "indoor"
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/plants?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "http://localhost:8000/api/plants?page=5",
    "next_page_url": "http://localhost:8000/api/plants?page=2",
    "path": "http://localhost:8000/api/plants",
    "per_page": 12,
    "prev_page_url": null,
    "to": 12,
    "total": 57
  }
}
```

---

### 2. Get Single Plant (Public)
**Endpoint:** `GET /plants/{slug}`  
**Auth Required:** No

**Success Response (200):**
```json
{
  "plant": {
    "id": 1,
    "name": "Monstera Deliciosa",
    "slug": "monstera-deliciosa",
    "description": "Tanaman hias populer dengan daun besar berlubang",
    "price": 150000,
    "stock": 50,
    "image": "plants/monstera.jpg",
    "image_url": "http://localhost:8000/storage/plants/monstera.jpg",
    "category_id": 1,
    "plant_type_id": 2,
    "average_rating": 4.5,
    "reviews_count": 12,
    "category": {
      "id": 1,
      "name": "Tanaman Hias",
      "slug": "tanaman-hias"
    },
    "plant_type": {
      "id": 2,
      "name": "Indoor",
      "slug": "indoor"
    },
    "reviews": [
      {
        "id": 1,
        "user_id": 2,
        "plant_id": 1,
        "order_id": 5,
        "rating": 5,
        "comment": "Tanaman sampai dengan selamat, sangat bagus!",
        "created_at": "2025-11-30T10:00:00.000000Z",
        "user": {
          "id": 2,
          "name": "Jane Doe"
        }
      }
    ]
  }
}
```

---

### 3. Create Plant (Admin Only)
**Endpoint:** `POST /plants`  
**Auth Required:** Yes (Admin)  
**Content-Type:** `multipart/form-data`

**Request Body:**
```
name: Monstera Deliciosa
description: Tanaman hias populer dengan daun besar berlubang
price: 150000
stock: 50
category_id: 1
plant_type_id: 2
image: [file]  // optional, max 2MB (jpeg, png, jpg, gif)
```

**Success Response (201):**
```json
{
  "message": "Plant created successfully",
  "plant": {
    "id": 1,
    "name": "Monstera Deliciosa",
    "slug": "monstera-deliciosa",
    "description": "Tanaman hias populer dengan daun besar berlubang",
    "price": 150000,
    "stock": 50,
    "image": "plants/abc123.jpg",
    "image_url": "http://localhost:8000/storage/plants/abc123.jpg",
    "category": { ... },
    "plant_type": { ... }
  }
}
```

---

### 4. Update Plant (Admin Only)
**Endpoint:** `POST /plants/{id}`  
**Auth Required:** Yes (Admin)  
**Content-Type:** `multipart/form-data`

**Note:** Gunakan POST dengan `_method=PUT` untuk update dengan file upload

**Request Body:**
```
name: Monstera Deliciosa Updated  // optional
description: Updated description  // optional
price: 175000  // optional
stock: 45  // optional
category_id: 1  // optional
plant_type_id: 2  // optional
image: [file]  // optional, will replace old image
```

**Success Response (200):**
```json
{
  "message": "Plant updated successfully",
  "plant": { ... }
}
```

---

### 5. Delete Plant (Admin Only)
**Endpoint:** `DELETE /plants/{id}`  
**Auth Required:** Yes (Admin)

**Success Response (200):**
```json
{
  "message": "Plant deleted successfully"
}
```

**Error Response (400):**
```json
{
  "message": "Cannot delete plant with existing orders. Consider setting stock to 0 instead."
}
```

---

## 📂 Categories Endpoints

### 1. Get All Categories (Public)
**Endpoint:** `GET /categories`  
**Auth Required:** No

**Success Response (200):**
```json
{
  "categories": [
    {
      "id": 1,
      "name": "Tanaman Hias",
      "slug": "tanaman-hias",
      "plants_count": 25,
      "created_at": "2025-11-30T10:00:00.000000Z",
      "updated_at": "2025-11-30T10:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Tanaman Obat",
      "slug": "tanaman-obat",
      "plants_count": 15
    }
  ]
}
```

---

### 2. Create Category (Admin Only)
**Endpoint:** `POST /categories`  
**Auth Required:** Yes (Admin)

**Request Body:**
```json
{
  "name": "Tanaman Hias"
}
```

**Success Response (201):**
```json
{
  "message": "Category created successfully",
  "category": {
    "id": 1,
    "name": "Tanaman Hias",
    "slug": "tanaman-hias"
  }
}
```

---

### 3. Update Category (Admin Only)
**Endpoint:** `PUT /categories/{id}`  
**Auth Required:** Yes (Admin)

**Request Body:**
```json
{
  "name": "Tanaman Hias Updated"
}
```

---

### 4. Delete Category (Admin Only)
**Endpoint:** `DELETE /categories/{id}`  
**Auth Required:** Yes (Admin)

**Error Response (400):**
```json
{
  "message": "Cannot delete category with existing plants"
}
```

---

## 🏷️ Plant Types Endpoints

### 1. Get All Plant Types (Public)
**Endpoint:** `GET /plant-types`  
**Auth Required:** No

**Success Response (200):**
```json
{
  "plant_types": [
    {
      "id": 1,
      "name": "Indoor",
      "slug": "indoor",
      "plants_count": 30
    },
    {
      "id": 2,
      "name": "Outdoor",
      "slug": "outdoor",
      "plants_count": 20
    }
  ]
}
```

---

### 2. Create Plant Type (Admin Only)
**Endpoint:** `POST /plant-types`  
**Auth Required:** Yes (Admin)

**Request Body:**
```json
{
  "name": "Indoor"
}
```

---

### 3. Update Plant Type (Admin Only)
**Endpoint:** `PUT /plant-types/{id}`  
**Auth Required:** Yes (Admin)

---

### 4. Delete Plant Type (Admin Only)
**Endpoint:** `DELETE /plant-types/{id}`  
**Auth Required:** Yes (Admin)

---

## 🛒 Orders Endpoints

### 1. Get User Orders
**Endpoint:** `GET /orders`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "orders": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "invoice_number": "INV-20251130-0001",
        "recipient_name": "John Doe",
        "recipient_phone": "081234567890",
        "total_price": 300000,
        "shipping_cost": 20000,
        "grand_total": 320000,
        "status": "pending",
        "shipping_address": "Jl. Example No. 123, Jakarta",
        "shipping_method": "standard",
        "notes": "Tolong kirim pagi",
        "completed_at": null,
        "canceled_at": null,
        "cancel_reason": null,
        "created_at": "2025-11-30T10:00:00.000000Z",
        "order_details": [
          {
            "id": 1,
            "order_id": 1,
            "plant_id": 1,
            "quantity": 2,
            "price": 150000,
            "plant": {
              "id": 1,
              "name": "Monstera Deliciosa",
              "image_url": "..."
            }
          }
        ]
      }
    ],
    "per_page": 10,
    "total": 5
  }
}
```

---

### 2. Create Order
**Endpoint:** `POST /orders`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "items": [
    {
      "plant_id": 1,
      "quantity": 2
    },
    {
      "plant_id": 3,
      "quantity": 1
    }
  ],
  "recipient_name": "John Doe",
  "recipient_phone": "081234567890",
  "shipping_address": "Jl. Example No. 123, Jakarta Selatan",
  "shipping_method": "standard",  // "standard" or "express"
  "notes": "Tolong kirim pagi"  // optional
}
```

**Success Response (201):**
```json
{
  "order": {
    "id": 1,
    "invoice_number": "INV-20251130-0001",
    "user_id": 1,
    "recipient_name": "John Doe",
    "recipient_phone": "081234567890",
    "total_price": 300000,
    "shipping_cost": 20000,
    "grand_total": 320000,
    "status": "pending",
    "order_details": [ ... ]
  }
}
```

**Notes:**
- `shipping_method`:
  - `standard`: Rp 20.000
  - `express`: Rp 50.000
- Stock akan berkurang otomatis
- Notification akan dibuat otomatis

**Error Response (400):**
```json
{
  "message": "Insufficient stock for Monstera Deliciosa. Available: 10"
}
```

---

### 3. Get Single Order
**Endpoint:** `GET /orders/{id}`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "order": { ... }
}
```

---

### 4. Cancel Order
**Endpoint:** `POST /orders/{id}/cancel`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "reason": "Salah beli produk"
}
```

**Success Response (200):**
```json
{
  "message": "Order canceled successfully",
  "order": {
    "id": 1,
    "status": "canceled",
    "canceled_at": "2025-11-30T10:30:00.000000Z",
    "cancel_reason": "Salah beli produk"
  }
}
```

**Notes:**
- Hanya order dengan status `pending` atau `confirmed` yang bisa dibatalkan
- Stock akan dikembalikan otomatis
- Notification akan dibuat otomatis

**Error Response (400):**
```json
{
  "message": "This order cannot be canceled. Only pending or confirmed orders can be canceled."
}
```

---

### 5. Get Invoice
**Endpoint:** `GET /orders/{id}/invoice`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "invoice": {
    "id": 1,
    "invoice_number": "INV-20251130-0001",
    "user_id": 1,
    "recipient_name": "John Doe",
    "recipient_phone": "081234567890",
    "total_price": 300000,
    "shipping_cost": 20000,
    "grand_total": 320000,
    "status": "completed",
    "shipping_address": "Jl. Example No. 123, Jakarta",
    "shipping_method": "standard",
    "order_details": [ ... ],
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

---

### 6. Update Order Status (Admin Only)
**Endpoint:** `PATCH /orders/{id}/status`  
**Auth Required:** Yes (Admin)

**Request Body:**
```json
{
  "status": "confirmed"  // "confirmed", "completed", or "canceled"
}
```

**Success Response (200):**
```json
{
  "message": "Order status updated successfully",
  "order": { ... }
}
```

**Order Status Flow:**
1. `pending` → Order baru dibuat
2. `confirmed` → Admin konfirmasi order
3. `completed` → Order selesai (customer bisa review)
4. `canceled` → Order dibatalkan

---

## ⭐ Reviews Endpoints

### 1. Get Plant Reviews (Public)
**Endpoint:** `GET /plants/{plant_id}/reviews`  
**Auth Required:** No

**Query Parameters:**
- `page` (integer): Page number

**Success Response (200):**
```json
{
  "reviews": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 2,
        "plant_id": 1,
        "order_id": 5,
        "rating": 5,
        "comment": "Tanaman sampai dengan selamat, sangat bagus!",
        "created_at": "2025-11-30T10:00:00.000000Z",
        "user": {
          "id": 2,
          "name": "Jane Doe"
        }
      }
    ],
    "per_page": 10,
    "total": 12
  }
}
```

---

### 2. Create Review
**Endpoint:** `POST /reviews`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "plant_id": 1,
  "order_id": 5,
  "rating": 5,
  "comment": "Tanaman sampai dengan selamat, sangat bagus!"
}
```

**Success Response (201):**
```json
{
  "message": "Review submitted successfully",
  "review": {
    "id": 1,
    "user_id": 2,
    "plant_id": 1,
    "order_id": 5,
    "rating": 5,
    "comment": "Tanaman sampai dengan selamat, sangat bagus!",
    "user": {
      "id": 2,
      "name": "Jane Doe"
    }
  }
}
```

**Requirements:**
- Order harus milik user yang sedang login
- Order status harus `completed`
- User harus membeli plant tersebut di order ini
- User belum pernah review plant ini untuk order ini

**Error Responses:**
```json
// Order tidak ditemukan atau bukan milik user
{
  "message": "Order not found or does not belong to you."
}

// Order belum completed
{
  "message": "You can only review completed orders."
}

// Plant tidak ada di order
{
  "message": "You did not purchase this plant in this order."
}

// Sudah pernah review
{
  "message": "You have already reviewed this plant for this order."
}
```

---

### 3. Update Review
**Endpoint:** `PUT /reviews/{id}`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "rating": 4,
  "comment": "Update: Tanamannya tumbuh dengan baik!"
}
```

**Success Response (200):**
```json
{
  "message": "Review updated successfully",
  "review": { ... }
}
```

---

### 4. Delete Review
**Endpoint:** `DELETE /reviews/{id}`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "message": "Review deleted successfully"
}
```

---

### 5. Get My Reviews
**Endpoint:** `GET /my-reviews`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "reviews": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 2,
        "plant_id": 1,
        "order_id": 5,
        "rating": 5,
        "comment": "Tanaman sampai dengan selamat, sangat bagus!",
        "created_at": "2025-11-30T10:00:00.000000Z",
        "plant": {
          "id": 1,
          "name": "Monstera Deliciosa",
          "image": "plants/monstera.jpg"
        },
        "order": {
          "id": 5
        }
      }
    ],
    "per_page": 10
  }
}
```

---

## 🔔 Notifications Endpoints

### 1. Get Notifications
**Endpoint:** `GET /notifications`  
**Auth Required:** Yes

**Query Parameters:**
- `per_page` (integer): Items per page (default: 20)

**Success Response (200):**
```json
{
  "notifications": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "message": "Your order INV-20251130-0001 has been placed and is waiting for confirmation.",
        "type": "order_placed",
        "is_read": false,
        "created_at": "2025-11-30T10:00:00.000000Z"
      },
      {
        "id": 2,
        "user_id": 1,
        "message": "Your order INV-20251130-0001 has been confirmed and is being processed.",
        "type": "order_status_changed",
        "is_read": true,
        "created_at": "2025-11-30T09:00:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 15
  },
  "unread_count": 5
}
```

**Notification Types:**
- `order_placed`: Order baru dibuat
- `order_status_changed`: Status order berubah
- `order_canceled`: Order dibatalkan

---

### 2. Mark as Read
**Endpoint:** `PATCH /notifications/{id}/read`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "message": "Notification marked as read",
  "notification": { ... }
}
```

---

### 3. Mark All as Read
**Endpoint:** `PATCH /notifications/read-all`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "message": "All notifications marked as read",
  "updated_count": 5
}
```

---

### 4. Delete Notification
**Endpoint:** `DELETE /notifications/{id}`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "message": "Notification deleted successfully"
}
```

---

### 5. Delete All Notifications
**Endpoint:** `DELETE /notifications`  
**Auth Required:** Yes

**Success Response (200):**
```json
{
  "message": "All notifications deleted",
  "deleted_count": 10
}
```

---

## 👥 Admin - User Management

### 1. Get All Users (Admin Only)
**Endpoint:** `GET /admin/users`  
**Auth Required:** Yes (Admin)

**Query Parameters:**
- `search` (string): Search by name or email
- `role` (string): Filter by role (`admin` or `user`)
- `per_page` (integer): Items per page

**Success Response (200):**
```json
{
  "users": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "user",
        "phone": "081234567890",
        "address": "Jl. Example No. 123",
        "orders_count": 5,
        "created_at": "2025-11-30T10:00:00.000000Z"
      }
    ],
    "per_page": 10,
    "total": 50
  }
}
```

---

### 2. Get Single User (Admin Only)
**Endpoint:** `GET /admin/users/{id}`  
**Auth Required:** Yes (Admin)

**Success Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "phone": "081234567890",
    "address": "Jl. Example No. 123",
    "created_at": "2025-11-30T10:00:00.000000Z",
    "orders": [ ... ],
    "notifications": [ ... ]
  }
}
```

---

### 3. Update User (Admin Only)
**Endpoint:** `PUT /admin/users/{id}`  
**Auth Required:** Yes (Admin)

**Request Body:**
```json
{
  "name": "John Doe Updated",  // optional
  "email": "john.updated@example.com",  // optional
  "role": "admin",  // optional: "admin" or "user"
  "phone": "081234567890",  // optional
  "address": "New address",  // optional
  "password": "newpassword123"  // optional
}
```

**Success Response (200):**
```json
{
  "message": "User updated successfully",
  "user": { ... }
}
```

---

### 4. Delete User (Admin Only)
**Endpoint:** `DELETE /admin/users/{id}`  
**Auth Required:** Yes (Admin)

**Success Response (200):**
```json
{
  "message": "User deleted successfully"
}
```

**Error Response (400):**
```json
{
  "message": "Cannot delete user with existing orders"
}
```

---

## 📊 Admin - Dashboard

### Get Dashboard Statistics (Admin Only)
**Endpoint:** `GET /dashboard`  
**Auth Required:** Yes (Admin)

**Success Response (200):**
```json
{
  "statistics": {
    "total_users": 150,
    "total_plants": 75,
    "total_orders": 250,
    "order_status_counts": {
      "pending": 10,
      "confirmed": 15,
      "completed": 200,
      "canceled": 25
    }
  },
  "low_stock_plants": [
    {
      "id": 5,
      "name": "Tanaman Low Stock",
      "stock": 3,
      "price": 50000
    }
  ],
  "recent_orders": [
    {
      "id": 1,
      "invoice_number": "INV-20251130-0001",
      "user": { ... },
      "total_price": 300000,
      "status": "pending",
      "created_at": "2025-11-30T10:00:00.000000Z"
    }
  ],
  "sales_stats": [
    {
      "date": "2025-11-30",
      "total_sales": 5000000
    },
    {
      "date": "2025-11-29",
      "total_sales": 4500000
    }
  ]
}
```

---

## 📋 Admin - Order Management

### 1. Get All Orders (Admin Only)
**Endpoint:** `GET /admin/orders`  
**Auth Required:** Yes (Admin)

**Query Parameters:**
- `status` (string): Filter by status
- `user_id` (integer): Filter by user
- `date_from` (date): Filter from date (YYYY-MM-DD)
- `date_to` (date): Filter to date (YYYY-MM-DD)
- `search` (string): Search by invoice number, user name, or email
- `per_page` (integer): Items per page

**Example:**
```
GET /admin/orders?status=pending&date_from=2025-11-01&date_to=2025-11-30&search=INV-20251130
```

**Success Response (200):**
```json
{
  "orders": {
    "current_page": 1,
    "data": [ ... ],
    "per_page": 10,
    "total": 100
  }
}
```

---

### 2. Update Order Details (Admin Only)
**Endpoint:** `PUT /admin/orders/{id}`  
**Auth Required:** Yes (Admin)

**Request Body:**
```json
{
  "recipient_name": "John Doe Updated",  // optional
  "recipient_phone": "081234567890",  // optional
  "shipping_address": "New address",  // optional
  "shipping_method": "express",  // optional: "standard" or "express"
  "notes": "Updated notes"  // optional
}
```

**Success Response (200):**
```json
{
  "message": "Order details updated successfully",
  "order": { ... }
}
```

**Notes:**
- Jika `shipping_method` diubah, `shipping_cost` akan diupdate otomatis
- `grand_total` akan recalculated

---

### 3. Delete Order (Admin Only)
**Endpoint:** `DELETE /admin/orders/{id}`  
**Auth Required:** Yes (Admin)

**Success Response (200):**
```json
{
  "message": "Order deleted successfully"
}
```

**Notes:**
- Stock akan dikembalikan jika order belum canceled
- Order details akan dihapus otomatis

---

## 🔒 Authorization

### User Roles
1. **Admin**
   - Full access ke semua endpoints
   - Dapat manage users, plants, categories, plant types, orders
   - Dapat melihat dashboard statistics

2. **User**
   - Dapat browse plants dan categories (public)
   - Dapat membuat dan melihat orders milik sendiri
   - Dapat membuat dan manage reviews milik sendiri
   - Dapat melihat dan manage notifications milik sendiri

---

## ⚠️ Error Codes

| Code | Description |
|------|-------------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error - Server error |

---

## 📝 Common Error Response Format

### Validation Error (422)
```json
{
  "errors": {
    "email": [
      "The email field is required.",
      "The email must be a valid email address."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### Authentication Error (401)
```json
{
  "message": "Unauthenticated."
}
```

### Authorization Error (403)
```json
{
  "message": "Access denied. Admin privileges required."
}
```

### Server Error (500)
```json
{
  "message": "An error occurred while processing your request.",
  "error": "Detailed error message (only in debug mode)"
}
```

---

## 🎯 Tips for Frontend Development

### 1. Token Management
```javascript
// Save token after login/register
localStorage.setItem('token', response.data.token);

// Set Authorization header for all requests
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Remove token on logout
localStorage.removeItem('token');
```

### 2. Handle Pagination
```javascript
// Pagination data available in all list endpoints
const { 
  current_page, 
  last_page, 
  per_page, 
  total,
  next_page_url,
  prev_page_url 
} = response.data.plants;
```

### 3. Image URLs
- Semua image URL sudah full URL, langsung bisa digunakan di `<img src={plant.image_url}>`
- Jika `image_url` null, gunakan placeholder image

### 4. Price Formatting
```javascript
// Backend mengirim price dalam Rupiah (integer)
// Format untuk display:
const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price);
};

// Example: 150000 → "Rp 150.000"
```

### 5. Order Status Display
```javascript
const statusLabels = {
  pending: 'Menunggu Konfirmasi',
  confirmed: 'Dikonfirmasi',
  completed: 'Selesai',
  canceled: 'Dibatalkan'
};

const statusColors = {
  pending: 'yellow',
  confirmed: 'blue',
  completed: 'green',
  canceled: 'red'
};
```

### 6. Real-time Notifications
Untuk real-time notifications, pertimbangkan:
- Polling: Request `/notifications` setiap 30-60 detik
- WebSocket (future enhancement)

### 7. File Upload
```javascript
// Untuk upload image (plants)
const formData = new FormData();
formData.append('name', 'Monstera Deliciosa');
formData.append('description', 'Description...');
formData.append('price', 150000);
formData.append('stock', 50);
formData.append('category_id', 1);
formData.append('plant_type_id', 2);
formData.append('image', fileInput.files[0]);

axios.post('/api/plants', formData, {
  headers: {
    'Content-Type': 'multipart/form-data'
  }
});
```

---

## 🚀 Getting Started

### 1. Base Setup
```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add token to all requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle errors globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Redirect to login
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 2. Example Usage
```javascript
// Login
const login = async (email, password) => {
  try {
    const response = await api.post('/login', { email, password });
    localStorage.setItem('token', response.data.token);
    return response.data;
  } catch (error) {
    console.error('Login failed:', error.response?.data);
    throw error;
  }
};

// Get plants with filters
const getPlants = async (filters = {}) => {
  try {
    const response = await api.get('/plants', { params: filters });
    return response.data.plants;
  } catch (error) {
    console.error('Failed to fetch plants:', error);
    throw error;
  }
};

// Create order
const createOrder = async (orderData) => {
  try {
    const response = await api.post('/orders', orderData);
    return response.data.order;
  } catch (error) {
    console.error('Failed to create order:', error.response?.data);
    throw error;
  }
};
```

---

## 📞 Support

Jika ada pertanyaan atau issue, silakan hubungi backend team atau buat issue di repository.

---

**Last Updated:** 30 November 2025  
**API Version:** 1.0.0  
**Backend:** Laravel 11 + Sanctum
