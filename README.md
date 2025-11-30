# 🌿 Floou Backend API

Backend API untuk aplikasi e-commerce tanaman hias **Floou**, dibangun dengan Laravel 11 dan Laravel Sanctum untuk authentication.

## 📋 Features

- ✅ **Authentication & Authorization** - Register, Login, Logout dengan Laravel Sanctum
- ✅ **Plant Management** - CRUD tanaman dengan kategori dan tipe
- ✅ **Order System** - Create, view, cancel orders dengan invoice otomatis
- ✅ **Review System** - Customer dapat memberikan rating dan review
- ✅ **Notification System** - Real-time notification untuk order updates
- ✅ **Admin Dashboard** - Statistics dan management untuk admin
- ✅ **Image Upload** - Upload dan manage gambar tanaman
- ✅ **Shipping Options** - Standard dan Express shipping
- ✅ **Stock Management** - Automatic stock updates

## 🛠️ Tech Stack

- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **Storage**: Local Storage (configurable to S3)
- **PHP Version**: 8.2+

## 📦 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL
- Node.js & NPM (optional, untuk asset compilation)

### Setup Steps

1. **Clone Repository**
```bash
git clone https://github.com/rafienajwan/floou.git
cd floou-backend
```

2. **Install Dependencies**
```bash
composer install
```

3. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure Database**
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=floou_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Run Migrations & Seeders**
```bash
php artisan migrate --seed
```

6. **Create Storage Link**
```bash
php artisan storage:link
```

7. **Start Development Server**
```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## 👤 Default Accounts

Setelah menjalankan seeder, Anda dapat login dengan:

### Admin Account
- **Email**: admin@floou.com
- **Password**: password123

### Customer Account
- **Email**: customer@example.com
- **Password**: password123

## 📚 API Documentation

Dokumentasi lengkap API tersedia di:
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Dokumentasi lengkap semua endpoints
- **[Floou_API.postman_collection.json](Floou_API.postman_collection.json)** - Postman Collection untuk testing

### Quick Links
- Base URL: `http://localhost:8000/api`
- Authentication: Bearer Token (Laravel Sanctum)

### Main Endpoints

#### Public Endpoints
```
GET    /plants                 - Get all plants with filters
GET    /plants/{id}            - Get single plant detail
GET    /categories             - Get all categories
GET    /plant-types            - Get all plant types
GET    /plants/{id}/reviews    - Get plant reviews
```

#### Authentication
```
POST   /register               - Register new user
POST   /login                  - Login user
POST   /logout                 - Logout user
GET    /me                     - Get current user
```

#### Orders (Requires Auth)
```
GET    /orders                 - Get user orders
POST   /orders                 - Create new order
GET    /orders/{id}            - Get order detail
POST   /orders/{id}/cancel     - Cancel order
GET    /orders/{id}/invoice    - Get order invoice
```

#### Reviews (Requires Auth)
```
POST   /reviews                - Create review
PUT    /reviews/{id}           - Update review
DELETE /reviews/{id}           - Delete review
GET    /my-reviews             - Get user's reviews
```

#### Admin Endpoints (Requires Admin Role)
```
GET    /dashboard              - Get dashboard statistics
POST   /plants                 - Create plant
PUT    /plants/{id}            - Update plant
DELETE /plants/{id}            - Delete plant
PATCH  /orders/{id}/status     - Update order status
GET    /admin/users            - Get all users
GET    /admin/orders           - Get all orders with filters
```

## 🗂️ Project Structure

```
floou-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # API Controllers
│   │   ├── Middleware/       # Custom Middleware
│   │   └── Resources/        # API Resources
│   └── Models/               # Eloquent Models
├── config/                   # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── routes/
│   └── api.php              # API routes
├── storage/
│   └── app/public/          # Public storage (images)
└── public/
    └── storage/             # Symlink to storage
```

## 🔐 Security

- **Authentication**: Laravel Sanctum dengan token expiration 30 hari
- **CORS**: Configured untuk frontend compatibility
- **Validation**: Input validation pada semua endpoints
- **Authorization**: Role-based access control (Admin/User)
- **CSRF Protection**: Disabled untuk API routes

## 🚀 Deployment

### Production Checklist

1. **Environment**
```bash
APP_ENV=production
APP_DEBUG=false
```

2. **Optimize**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

3. **Database**
```bash
php artisan migrate --force
```

4. **Storage**
- Ensure `storage` and `bootstrap/cache` directories are writable
- Configure cloud storage (S3) if needed

5. **Security**
- Update `SANCTUM_STATEFUL_DOMAINS` in `.env`
- Configure `allowed_origins` in `config/cors.php`
- Set strong `APP_KEY`

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter TestName
```

## 📝 Database Schema

### Main Tables
- **users** - User accounts (admin & customers)
- **plants** - Plant products
- **categories** - Plant categories
- **plant_types** - Plant types (Indoor/Outdoor)
- **orders** - Customer orders
- **order_details** - Order items
- **reviews** - Product reviews
- **notifications** - User notifications

### Relationships
```
User -> Orders (1:N)
User -> Reviews (1:N)
User -> Notifications (1:N)
Plant -> Category (N:1)
Plant -> PlantType (N:1)
Plant -> Reviews (1:N)
Order -> OrderDetails (1:N)
Order -> Reviews (1:N)
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Developer

- **Repository**: [github.com/rafienajwan/floou](https://github.com/rafienajwan/floou)
- **Branch**: backend

## 📞 Support

Jika ada pertanyaan atau issue, silakan buat issue di GitHub repository atau hubungi backend team.

---

**Last Updated**: 30 November 2025  
**Version**: 1.0.0  
**Laravel Version**: 11.x

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
