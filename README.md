# win24 - Lottery Platform MVP

A full-stack lottery/raffle platform where users can create lotteries to sell items, and others buy tickets for a chance to win.

## Tech Stack

### Backend
- **Laravel 11** (PHP 8.2+)
- **Laravel Sanctum** for API authentication
- **MySQL 8** database

### Frontend
- **Vue 3** with Composition API
- **Vite** for build tooling
- **Vue Router** for navigation
- **Pinia** for state management
- **Tailwind CSS** for styling
- **Axios** for API requests

## Features

- User registration with email verification
- Create lotteries with images, pricing, and countdown
- Purchase tickets for lotteries
- Admin approval workflow for new lotteries
- Automatic winner selection when lottery ends
- User dashboard (my lotteries, my tickets)
- Admin panel (approve/reject lotteries, view all)
- Search and popular lotteries
- Real-time countdown timers (polling)

## Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8
- Git

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd backend
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Create environment file:
   ```bash
   cp .env.example .env
   ```

4. Configure your `.env` file:
   ```env
   APP_URL=http://localhost:8000
   FRONTEND_URL=http://localhost:3000
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=win24
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Create the database:
   ```bash
   mysql -u root -p -e "CREATE DATABASE win24"
   ```

7. Run migrations and seed:
   ```bash
   php artisan migrate --seed
   ```

8. Create storage symlink:
   ```bash
   php artisan storage:link
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm run dev
   ```

The frontend will be available at `http://localhost:3000`

### Default Admin Credentials

- **Email:** admin@win24.com
- **Password:** password

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login
- `POST /api/logout` - Logout (authenticated)
- `GET /api/user` - Get current user (authenticated)

### Lotteries (Public)
- `GET /api/lotteries` - List active lotteries
- `GET /api/lotteries/popular` - Popular lotteries
- `GET /api/lotteries/{id}` - Get lottery details

### Lotteries (Authenticated)
- `GET /api/user/lotteries` - User's created lotteries
- `POST /api/user/lotteries` - Create lottery
- `POST /api/user/lotteries/{id}` - Update lottery
- `DELETE /api/user/lotteries/{id}` - Delete lottery

### Tickets
- `POST /api/lotteries/{id}/tickets` - Purchase tickets
- `GET /api/user/tickets` - User's purchased tickets

### Admin
- `GET /api/admin/stats` - Dashboard statistics
- `GET /api/admin/lotteries` - All lotteries
- `PUT /api/admin/lotteries/{id}/approve` - Approve lottery
- `PUT /api/admin/lotteries/{id}/reject` - Reject lottery
- `PUT /api/admin/lotteries/{id}` - Edit lottery

## Scheduled Tasks

To run the winner selection automatically, set up a cron job:

```bash
* * * * * cd /path-to-your-project/backend && php artisan schedule:run >> /dev/null 2>&1
```

Or run manually:
```bash
php artisan lotteries:select-winners
```

## Security Features

- Laravel Sanctum API token authentication
- Email verification required for creating lotteries and purchasing tickets
- Rate limiting on API endpoints
- CORS configuration
- Input validation via Form Request classes
- SQL injection prevention via Eloquent ORM
- Secure image upload with MIME validation
- Audit logging for admin actions
- Cryptographically secure winner selection (`random_int()`)

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure proper database credentials
3. Set up HTTPS
4. Configure mail driver for email verification
5. Set up Redis for caching/sessions (optional)
6. Configure queue driver for background jobs (optional)
7. Run `npm run build` in frontend
8. Serve frontend static files via Nginx/Apache

## License

MIT

