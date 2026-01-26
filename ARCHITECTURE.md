# WhatsApp Clone - Architecture & Code Structure

## Project Architecture

This WhatsApp clone follows the **MVC (Model-View-Controller)** pattern with additional layers:

```
┌─────────────────────────────────────────────────────┐
│            HTTP Request / WebSocket                  │
└────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│              Routes (api.php / web.php)              │
└────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│              Middleware / Requests                    │
└────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│              Controllers                             │
└────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│         Services / Business Logic                    │
└────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────┐
│              Models / Database                       │
└────────────────────────────────────────────────────────┘
```

---

## Directory Structure

```
whatsapp-clone/
├── app/
│   ├── Actions/               # Fortify/Jetstream actions
│   ├── Http/
│   │   ├── Controllers/       # API & Web Controllers
│   │   ├── Requests/          # Form Request validation classes
│   │   ├── Resources/         # API resource transformations
│   │   ├── Middleware/        # HTTP middleware
│   │   ├── Livewire/          # Livewire components
│   │   └── Kernel.php         # HTTP kernel
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic services
│   ├── Policies/              # Authorization policies
│   ├── Traits/                # Reusable traits
│   ├── Notifications/         # Notification classes
│   ├── Events/                # Event classes
│   ├── Listeners/             # Event listeners
│   ├── Exceptions/            # Custom exceptions
│   ├── Rules/                 # Custom validation rules
│   └── Providers/             # Service providers
├── database/
│   ├── migrations/            # Database migrations
│   ├── factories/             # Model factories
│   └── seeders/               # Database seeders
├── routes/
│   ├── api.php                # API routes
│   ├── web.php                # Web routes
│   ├── channels.php           # Broadcasting channels
│   └── console.php            # Artisan commands
├── resources/
│   ├── views/                 # Blade templates
│   ├── css/                   # Stylesheets
│   └── js/                    # JavaScript files
├── config/                    # Configuration files
├── storage/                   # File storage
├── tests/                     # Unit & feature tests
└── public/                    # Public assets
```

---

## Core Models

### User Model
Represents system users with authentication and relationships.

**Relationships:**
- `contacts()`: User's saved contacts (One-to-Many)
- `messages()`: Messages sent by user (One-to-Many)
- `chats()`: Chats user is part of (Many-to-Many)
- `stories()`: Stories created by user (One-to-Many)
- `activeStories()`: Non-expired stories

**Key Attributes:**
- `id`: Primary key
- `name`: User's display name
- `email`: Unique email address
- `password`: Hashed password
- `profile_photo_url`: Avatar URL
- `last_seen_at`: Last activity timestamp
- `dark_mode`: User preference (boolean)
- `chat_wallpaper`: Chat background image path

### Chat Model
Represents one-on-one or group conversations.

**Relationships:**
- `users()`: Participants in chat (Many-to-Many with pivot data)
- `messages()`: Chat messages (One-to-Many)

**Pivot Attributes:**
- `is_pinned`: Chat pinned status
- `is_archived`: Chat archived status
- `muted_until`: Notification mute expiration
- `is_admin`: Group admin status

**Key Methods:**
- `scopeForUser()`: Get chats for specific user
- `scopeWithRelations()`: Eager load relations

**Accessors:**
- `name`: Auto-generate name from contact or email
- `image`: Auto-generate avatar URL
- `otherUser`: Get chat partner for direct chats

### Message Model
Represents individual messages in chats.

**Relationships:**
- `chat()`: Parent chat (Many-to-One)
- `user()`: Message sender (Many-to-One)
- `parent()`: Original message if reply (Self-referential)
- `reactions()`: Emoji reactions (One-to-Many)
- `deletedByUsers()`: Users who deleted message (Many-to-Many)
- `starredByUsers()`: Users who starred message (Many-to-Many)

**Key Attributes:**
- `id`: Primary key
- `body`: Message content
- `type`: Message type (text, image, file, voice)
- `file_path`: Path to media file
- `is_read`: Read status
- `deleted_for_everyone`: Deletion status
- `parent_id`: Parent message ID for replies
- `user_id`: Sender user ID
- `chat_id`: Chat ID

**Scopes:**
- `withRelations()`: Eager load related data
- `visibleToUser()`: Exclude deleted messages
- `unread()`: Get unread messages

### Contact Model
Represents saved contacts of a user.

**Relationships:**
- `user()`: Contact owner (Many-to-One)
- `contactUser()`: The actual user being contacted (Many-to-One)

**Key Attributes:**
- `id`: Primary key
- `name`: Custom name for contact
- `user_id`: Owner user ID
- `contact_id`: Contact user ID

### MessageReaction Model
Represents emoji reactions to messages.

**Relationships:**
- `message()`: Target message (Many-to-One)
- `user()`: User who reacted (Many-to-One)

**Key Attributes:**
- `id`: Primary key
- `emoji`: Reaction emoji
- `message_id`: Target message ID
- `user_id`: Reacting user ID

### Story Model
Represents user status updates (stories).

**Relationships:**
- `user()`: Story creator (Many-to-One)
- `views()`: Story views (One-to-Many)

**Key Attributes:**
- `id`: Primary key
- `body`: Story content
- `type`: Story type (text, image, video)
- `file_path`: Media file path
- `expires_at`: Expiration timestamp
- `user_id`: Creator user ID

---

## Service Layer

### MessageService
Handles message-related operations.

**Methods:**
- `sendMessage(Chat $chat, $body, $media, $parentId)`: Send new message
- `broadcastNewMessage(Chat $chat, Message $message)`: Broadcast via notifications
- `deleteForMe(Message $message)`: Hide message for user
- `deleteForEveryone(Message $message)`: Permanently delete message
- `clearChat(Chat $chat)`: Hide all messages in chat

### ChatService
Handles chat-related operations.

**Methods:**
- `getOrCreateDirectChat($userId, $contactUserId)`: Get or create 1-on-1 chat
- `createGroupChat($name, $contactIds, $imagePath)`: Create group chat

---

## Authorization Policies

### ChatPolicy
Controls chat access and modifications.

**Methods:**
- `view($user, $chat)`: Can user access chat?
- `sendMessage($user, $chat)`: Can user send messages?
- `update($user, $chat)`: Can user modify chat? (admin only for groups)
- `delete($user, $chat)`: Can user delete chat? (admin only for groups)

### MessagePolicy
Controls message operations.

**Methods:**
- `delete($user, $message)`: Can user delete their message?
- `deleteForEveryone($user, $message)`: Can user delete for everyone?
- `react($user, $message)`: Can user add reaction?
- `star($user, $message)`: Can user star message?

### ContactPolicy
Controls contact management.

**Methods:**
- `view($user, $contact)`: Can user view contact?
- `update($user, $contact)`: Can user edit contact?
- `delete($user, $contact)`: Can user delete contact?

---

## API Resources (Transformers)

Resources transform models into consistent JSON responses.

### UserResource
Transforms User model to API response.

**Output:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "profile_photo_url": "...",
  "last_seen_at": "2024-01-26 10:00:00",
  "created_at": "2024-01-26 10:00:00"
}
```

### MessageResource
Transforms Message model with related data.

**Output:**
```json
{
  "id": 1,
  "body": "Hello!",
  "type": "text",
  "is_read": true,
  "user": {...},
  "reactions": [...],
  "starred_by_count": 2,
  "is_starred": false,
  "created_at": "2024-01-26 10:05:00"
}
```

### ChatResource
Transforms Chat model with metadata.

**Output:**
```json
{
  "id": 1,
  "name": "Jane Doe",
  "image": "...",
  "is_group": false,
  "users_count": 2,
  "unread_count": 5,
  "last_message": {...},
  "last_message_at": "2024-01-26 15:30:00"
}
```

---

## Request Validation Classes

Form Request classes validate and authorize API requests.

### StoreMessageRequest
- `message`: required, string, max 5000 chars

### StoreContactRequest
- `name`: required, string, max 255
- `contact_id`: required, exists in users, different from auth user

### UpdateContactRequest
- `name`: required, string, max 255

### LoginRequest
- `email`: required, valid email
- `password`: required, min 6 chars

### RegisterRequest
- `name`: required, string
- `email`: required, valid, unique
- `password`: required, confirmed, strong (min 8, letters, numbers, symbols)

---

## Response Format

All API responses follow consistent format:

**Success Response (200):**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

**Error Response (4xx/5xx):**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["validation error"]
  }
}
```

**Paginated Response:**
```json
{
  "success": true,
  "message": "Data retrieved",
  "data": [...],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

---

## Broadcasting & Real-Time Features

### Channels
- `App.Models.User.{userId}`: Private user channel
- `chat.{chatId}`: Chat-specific channel

### Events Broadcast
- New messages
- Message read receipts
- Typing indicators
- User online/offline status
- Story updates

---

## Best Practices

### Query Optimization
1. Always use `with()` for eager loading
2. Use `has()` to filter relationships
3. Implement query scopes for reusable filters
4. Paginate large result sets
5. Use select() to retrieve only needed columns

### Security
1. Always authorize user actions
2. Validate all inputs with FormRequests
3. Use policies for model-level access control
4. Hash sensitive data
5. Use prepared statements (Eloquent handles this)

### API Design
1. Use consistent response formats
2. Return appropriate HTTP status codes
3. Include helpful error messages
4. Use resource classes for transformations
5. Document all endpoints

### Code Organization
1. Put business logic in Services
2. Keep controllers thin and focused
3. Use traits for shared functionality
4. Use policies for authorization
5. Use scopes for query building

---

## Database Schema

### Key Tables
- `users`: User accounts
- `chats`: Conversations
- `messages`: Chat messages
- `contacts`: User contact lists
- `message_reactions`: Emoji reactions
- `deleted_messages`: Message deletion tracking
- `starred_messages`: Important message markers
- `stories`: User status updates
- `story_views`: Story view tracking
- `chat_user`: Chat-User relationships (pivot)
- `blocked_users`: Blocked user tracking
- `quick_replies`: Message templates

---

## Important Notes

### N+1 Query Prevention
Always use eager loading:
```php
// Bad
$chats = Chat::all();
foreach ($chats as $chat) {
    echo $chat->users()->count(); // N queries
}

// Good
$chats = Chat::with('users')->get();
foreach ($chats as $chat) {
    echo $chat->users()->count(); // 1 query
}
```

### Authorization
Always check permissions before operations:
```php
// Bad
$message->delete();

// Good
$this->authorize('delete', $message);
$message->delete();
```

### Validation
Use FormRequest classes instead of manual validation:
```php
// Bad
$request->validate([...]);

// Good
public function store(StoreMessageRequest $request) {
    $data = $request->validated();
}
```
