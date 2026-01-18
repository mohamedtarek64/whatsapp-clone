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

*   **Backend:** [Laravel 10.x](https://laravel.com)
*   **Frontend Framework:** [Livewire](https://livewire.laravel.com)
*   **Interactivity:** [Alpine.js](https://alpinejs.dev)
*   **Styling:** [Tailwind CSS](https://tailwindcss.com) (WhatsApp Web Replica)
*   **Real-Time:** [Pusher](https://pusher.com) / Laravel Echo
*   **Database:** MySQL

---

## 🚀 Installation & Setup

Follow these steps to set up the project locally:

### 1. Prerequisities
Ensure you have the following installed:
*   PHP 8.1+
*   Composer
*   Node.js & NPM
*   MySQL

### 2. Clone the Repository
```bash
git clone https://github.com/mohamedtarek64/whatsapp-clone.git
cd whatsapp-clone
```

### 3. Install Dependencies
```bash
composer install
npm install
```

### 4. Environment Configuration
Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env
```
Open `.env` and configure your database and Pusher credentials:
```ini
DB_DATABASE=whatsapp_clone
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

### 5. Generate Key & Migrate
```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
```
*Note: The `storage:link` command is crucial for displaying profile photos and media.*

### 6. Build Assets
```bash
npm run build
```

### 7. Run the Application
```bash
php artisan serve
```
Visit `http://localhost:8000` in your browser.

---

## 📂 Project Structure (Key Files)

*   `app/Http/Livewire/ChatComponent.php`: The brain of the application. Handles all chat logic, realtime events, and state management.
*   `resources/views/livewire/chat-component.blade.php`: The main UI template containing the sidebar, chat area, and settings.
*   `app/Models/*`: Eloquent models for `Message`, `Chat`, `User`, `Story`, `Reaction`, etc.
*   `routes/channels.php`: Private presence channels for real-time online status.

---

## 📝 License

This project is open-source and licensed under the [MIT License](LICENSE).

---

<div align="center">
  <p>Developed with ❤️ by <strong>MOHAMED ELKENANY</strong></p>
  <p>📧 Email: <a href="mailto:mohamedelkenany001@gmail.com">mohamedelkenany001@gmail.com</a></p>
</div>
