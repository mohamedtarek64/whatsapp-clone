# 📱 WhatsApp Clone - Advanced Real-Time Chat Application

A robust, full-featured WhatsApp clone built with **Laravel**, **Livewire**, and **Alpine.js**. This application replicates the core functionality and user experience of WhatsApp, featuring real-time messaging, media sharing, status updates (stories), and a modern dark mode interface.

![Project Banner](https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg)

---

## ✨ Features

### 💬 Messaging & Real-Time Interaction
*   **Real-time Chat:** Instant messaging powered by Pusher/WebSockets.
*   **Message Types:** Support for **Text**, **Images**, **Files**, and **Voice Notes**.
*   **Message Actions:**
    *   Reply to specific messages.
    *   Delete messages (For Me / For Everyone).
    *   **React** to messages with emojis.
    *   **Star/Unstar** important messages.
*   **Typing Indicators:** Real-time "User is typing..." status.
*   **Read Receipts:** Single tick (sent), Double tick (delivered), Blue tick (read).

### 📖 Stories (Status) System
*   **Create Stories:** Post text, image, or video status updates.
*   **Expiry:** Stories automatically expire after 24 hours.
*   **View Tracking:** See who viewed your stories.
*   **Interactive UI:** Horizontal scroll view with circular previews, similar to native WhatsApp.

### 👥 Group Management & Admin Tools
*   **Create Groups:** Add multiple contacts to a conversation.
*   **Admin System:**
    *   Promote/Demote Admins.
    *   Add/Remove participants.
    *   Group Info editing.
*   **Group Permissions:** Only admins can perform sensitive actions.

### ⚙️ Settings & Customization
*   **Dark Mode:** Fully supported system-wide dark theme (Database Persisted).
*   **Instant Navigation:** Client-side routing for settings tabs using **Alpine.js** (Zero-latency).
*   **Profile Customization:**
    *   Update Profile Picture (with preview).
    *   Change Display Name.
    *   Manage Privacy Settings.
*   **Wallpaper:** Set custom chat backgrounds.

### 🔍 Utility
*   **Global Search:** Search through chats and messages.
*   **Chat Management:**
    *   **Pin** chats to the top.
    *   **Archive** chats to hide them.
    *   **Mute** notifications for specific chats.
    *   **Block** users.
    *   **Clear Chat** history.

---

## 🛠️ Technology Stack

*   **Backend:** [Laravel 9.x](https://laravel.com)
*   **Frontend Framework:** [Livewire 2.x](https://livewire.laravel.com)
*   **Interactivity:** [Alpine.js 3.x](https://alpinejs.dev)
*   **Styling:** [Tailwind CSS 3.x](https://tailwindcss.com)
*   **Real-Time:** [Pusher](https://pusher.com) / [Laravel Echo](https://laravel.com/docs/echo)
*   **Database:** MySQL 5.7+
*   **Authentication:** [Sanctum](https://laravel.com/docs/sanctum)
*   **Authorization:** [Gates & Policies](https://laravel.com/docs/authorization)

---

## 📋 Documentation

- **[Installation & Setup Guide](./INSTALLATION.md)** - Complete setup instructions
- **[Architecture & Code Structure](./ARCHITECTURE.md)** - Design patterns and models
- **[Contributing Guidelines](./CONTRIBUTING.md)** - How to contribute
- **[API Documentation](#-api-endpoints)** - API endpoint reference

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0.2+
- MySQL 5.7+
- Node.js 14+
- Composer

### Installation
```bash
# 1. Clone repository
git clone https://github.com/mohamedtarek64/whatsapp-clone.git
cd whatsapp-clone

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
# DB_DATABASE=whatsapp_clone
# DB_USERNAME=root

# 5. Run migrations
php artisan migrate

# 6. Link storage
php artisan storage:link

# 7. Build assets
npm run build

# 8. Start server
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## Continuous Integration

This repository includes a GitHub Actions workflow that runs the test suite on push and pull requests. The workflow is defined in `.github/workflows/phpunit.yml` and runs `composer install`, migrations and `phpunit` using an in-memory SQLite database.

Ensure your tests can run with an in-memory SQLite DB or update the workflow accordingly.


## 🔌 API Endpoints

### Authentication
```
POST   /api/auth/register          # Register new user
POST   /api/auth/login             # Login user
POST   /api/auth/logout            # Logout user
```

### Contacts
```
GET    /api/contacts               # List all contacts
POST   /api/contacts               # Add new contact
GET    /api/contacts/{id}          # Get contact details
PUT    /api/contacts/{id}          # Update contact
DELETE /api/contacts/{id}          # Delete contact
```

### Chats
```
GET    /api/chats                  # List all chats
```

### Messages
```
GET    /api/chats/{chat}/messages           # Get messages
POST   /api/chats/{chat}/messages           # Send message
PATCH  /api/chats/{chat}/messages/read      # Mark as read
GET    /api/chats/{chat}/messages/unread    # Get unread
```

See [INSTALLATION.md](./INSTALLATION.md) for detailed API documentation with request/response examples.

---

## 🏗️ Project Structure

```
whatsapp-clone/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API & Web controllers
│   │   ├── Requests/             # Form request validation
│   │   ├── Resources/            # API response transformers
│   │   ├── Middleware/           # HTTP middleware
│   │   └── Livewire/             # Livewire components
│   ├── Models/                   # Eloquent models
│   ├── Services/                 # Business logic
│   ├── Policies/                 # Authorization policies
│   ├── Traits/                   # Reusable traits
│   └── Notifications/            # Notification classes
├── database/
│   ├── migrations/               # Database migrations
│   ├── factories/                # Model factories
│   └── seeders/                  # Database seeds
├── routes/
│   ├── api.php                   # API routes
│   ├── web.php                   # Web routes
│   └── channels.php              # Broadcasting channels
├── resources/
│   ├── views/                    # Blade templates
│   ├── js/                       # JavaScript files
│   └── css/                      # Stylesheets
├── tests/
│   ├── Feature/                  # Feature tests
│   └── Unit/                     # Unit tests
├── INSTALLATION.md               # Setup guide
├── ARCHITECTURE.md               # Design documentation
├── CONTRIBUTING.md               # Contribution guidelines
└── README.md                     # This file
```

---

## 💡 Key Features

### Database Optimization
- Query scopes for reusable filters
- Eager loading to prevent N+1 queries
- Pagination for large datasets
- Proper indexing on frequently queried columns

### API Design
- Consistent JSON response format
- RESTful endpoint design
- Proper HTTP status codes
- Comprehensive error handling

### Security
- User authentication with Sanctum tokens
- Authorization policies for model access
- Input validation with FormRequest classes
- Password hashing with bcrypt

### Testing
- Unit tests for services
- Feature tests for API endpoints
- Database seeding for test data
- Coverage tracking

---

## 🔒 Security Features

- **Authentication:** API token-based with Sanctum
- **Authorization:** Model policies and gates
- **Validation:** FormRequest classes with custom rules
- **CSRF Protection:** For web routes
- **Password Security:** Bcrypt hashing
- **Rate Limiting:** Configurable per endpoint

---

## 📊 Database Design

### Core Tables
- `users` - User accounts
- `chats` - Conversations (1-to-1 or groups)
- `messages` - Chat messages
- `contacts` - User contact lists
- `chat_user` - Chat participants (pivot)
- `message_reactions` - Message emoji reactions
- `deleted_messages` - Message deletion tracking
- `starred_messages` - Important message markers
- `stories` - User status updates
- `story_views` - Story view tracking

---

## 🧪 Testing

Run tests with:
```bash
# All tests
php artisan test

# Specific test class
php artisan test tests/Feature/Api/MessageApiTest.php

# With coverage
php artisan test --coverage
```

---

## 🚀 Performance Tips

1. **Enable Query Caching**
   ```php
   // Use Redis for cache and sessions
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   ```

2. **Optimize Queries**
   - Use eager loading with `with()`
   - Use scopes for reusable filters
   - Implement pagination

3. **Asset Optimization**
   - Minify CSS/JS: `npm run build`
   - Enable gzip compression
   - Use CDN for static files

---

## 📱 Real-Time Features

Uses **Pusher** or **Laravel WebSockets** for:
- Instant messaging
- Typing indicators
- Read receipts
- Online/offline status
- Notification delivery

Configure in `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1
```

---

## 📝 License

This project is open-source and licensed under the [MIT License](LICENSE).

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](./CONTRIBUTING.md) for guidelines on:
- Code style
- Git workflow
- Testing requirements
- Documentation updates

---

## 📧 Support

- **Email:** mohamedelkenany001@gmail.com
- **Issues:** GitHub Issues
- **Discussions:** GitHub Discussions

---

<div align="center">
  <p>Developed with ❤️ by <strong>MOHAMED ELKENANY</strong></p>
  <p>
    <a href="https://github.com/mohamedtarek64">GitHub</a> •
    <a href="mailto:mohamedelkenany001@gmail.com">Email</a>
  </p>
</div>

