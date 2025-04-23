# CartMate Backend

CartMate is a modern e-commerce platform that helps users track and manage their shopping carts across different online stores. This repository contains the backend API implementation built with Laravel.

## Features

### Authentication & Authorization
- User registration and login with email verification
- JWT-based authentication using Laravel Sanctum
- Role-based access control (Admin and User roles)
- Password reset functionality with OTP verification

### Product Management
- Product listing and details retrieval
- Product scraping from external sources
- Product search and filtering
- Product categories and tags

### Cart Management
- Add products to cart
- View cart contents
- Update cart quantities
- Remove items from cart
- Cart persistence across sessions

### API Documentation
- OpenAPI/Swagger documentation for all endpoints
- Detailed request/response schemas
- Authentication requirements
- Example payloads

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `POST /api/auth/verify-email` - Verify email address
- `POST /api/auth/reset-password` - Request password reset
- `POST /api/auth/reset-password/verify` - Verify reset password OTP

### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get product details
- `GET /api/products/trending` - Get trending products based on cart additions
- `POST /api/products` - Create new product (Admin only)
- `PUT /api/products/{id}` - Update product (Admin only)
- `DELETE /api/products/{id}` - Delete product (Admin only)
- `POST /api/products/scrape` - Scrape product details (Admin only)

### Cart
- `POST /api/cart/add` - Add product to cart
- `GET /api/cart` - Get user's cart items

## Technical Stack

- **Framework**: Laravel 10.x
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **API Documentation**: L5 Swagger
- **Email**: Laravel Mail with markdown templates
- **Testing**: PHPUnit

## Getting Started

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy `.env.example` to `.env` and configure your environment variables
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```
6. Start the development server:
   ```bash
   php artisan serve
   ```

## API Documentation

The API documentation is available at `/api/documentation` when running the application. This provides a Swagger UI interface to explore and test all available endpoints.

## Testing

Run the test suite:
```bash
php artisan test
```

## Contributing

Please read our contributing guidelines before submitting pull requests.
Please read our contributing guidelines before submitting pull requests.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
This project is licensed under the MIT License - see the LICENSE file for details.
