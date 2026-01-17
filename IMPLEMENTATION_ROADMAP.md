# Essencience.com - Implementation Roadmap

## Project Initialization

### 1. Laravel Project Setup
```bash
# From /Volumes/EXTERNAL/Essencience.com
composer create-project laravel/laravel .
npm install
php artisan key:generate
```

### 2. Install Livewire & Dependencies
```bash
composer require livewire/livewire
npm install -D tailwindcss postcss autoprefixer
php artisan tailwindcss:install
```

### 3. Database Configuration
Create `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=essencience_db
DB_USERNAME=essencience_user
DB_PASSWORD=secure_password

MAIL_FROM_ADDRESS=contact@essencience.com
MAIL_MAILER=smtp

APP_ENV=local
APP_DEBUG=true
```

### 4. Key Packages to Install
```bash
composer require laravel/sanctum     # Authentication
composer require laravel/scout       # Full-text search
composer require laravel/tinker      # REPL debugging
npm install -D flowbite             # UI component library (Tailwind)
```

---

## Database Schema

### Core Models & Migrations

#### Users & Roles
```bash
php artisan make:model User -m
php artisan make:model Role -m
php artisan make:model Permission -m
```

**Users Table** (with roles)
- id, name, email, password, bio, avatar_url, role_id
- is_verified, subscribed_to_proclamations
- created_at, updated_at, deleted_at

**Roles** (member, contributor, steward, admin)

---

#### Quintessentials
```bash
php artisan make:model Quintessential -m
```

**Quintessentials Table**
- id, name (Goodness at theta, 1-truth, 2-connection, 3-justice, 4-Expression, 5-Balance), slug
- description, philosophy (long text)
- icon_url, color_hex
- order_by, created_at

---

#### Content & Posts
```bash
php artisan make:model Post -m
php artisan make:model Ritual -m
php artisan make:model KnowledgeArticle -m
```

**Posts Table** (Proclamations & Chronicles)
- id, title, slug, content (markdown), excerpt
- author_id (User)
- featured_image_url, is_proclamation
- published_at, created_at, updated_at
- Relationships: quintessentials (many-to-many)

**Rituals Table**
- id, title, slug, description, full_guide (markdown)
- difficulty (1-5), duration_minutes, required_materials
- author_id, quintessential_id
- is_approved, community_votes
- published_at, created_at, updated_at

**KnowledgeArticles Table**
- id, title, slug, content (markdown), category
- parent_id (for hierarchy), order_by
- author_id
- is_published, created_at, updated_at

---

#### Community & Forum
```bash
php artisan make:model ForumThread -m
php artisan make:model ForumPost -m
php artisan make:model Event -m
```

**ForumThreads Table**
- id, title, slug, content, author_id
- is_pinned, is_closed
- quintessential_id (optional category)
- views, created_at, updated_at

**ForumPosts Table**
- id, thread_id, author_id, content
- is_solution, likes_count
- created_at, updated_at, deleted_at

**Events Table**
- id, title, slug, description, ritual_id (optional)
- event_date, duration_minutes, location (or "Online")
- max_attendees, current_attendees
- author_id, is_published, created_at

---

### Migration Commands
```bash
php artisan make:migration create_users_table
php artisan make:migration create_quintessentials_table
php artisan make:migration create_posts_table
php artisan make:migration create_rituals_table
php artisan make:migration create_knowledge_articles_table
php artisan make:migration create_forum_threads_table
php artisan make:migration create_forum_posts_table
php artisan make:migration create_events_table
php artisan make:migration create_subscriptions_table
# ... then edit in database/migrations/

php artisan migrate
```

---

## Blade Components & Views

### Global Layout
```
resources/views/
├── layouts/
│   ├── app.blade.php          # Main layout with Livewire
│   └── guest.blade.php        # Unauthenticated layout
├── components/
│   ├── navbar.blade.php       # Navigation with Q colors
│   ├── footer.blade.php       # Proclamation seal
│   ├── quintessential-card.blade.php
│   ├── button.blade.php       # Q-Sunrise, Q-Flame variants
│   └── search-input.blade.php
├── pages/
│   ├── home.blade.php         # Landing page
│   ├── philosopher-king.blade.php
│   ├── quintessentials/show.blade.php
│   ├── science.blade.php      # Science Hub
│   ├── chronicle/index.blade.php
│   └── circle.blade.php       # Community
└── livewire/
    ├── quintessential-carousel.blade.php
    ├── ritual-finder.blade.php
    ├── knowledge-searcher.blade.php
    ├── forum-thread.blade.php
    └── events-calendar.blade.php
```

---

## Livewire Components

### 1. QuintessentialCarousel
**Location**: `app/Livewire/QuintessentialCarousel.php`

```php
class QuintessentialCarousel extends Component
{
    #[Reactive]
    public int $activeIndex = 0;
    
    #[Computed]
    public function quintessentials()
    {
        return Quintessential::orderBy('order_by')->get();
    }
    
    public function nextSlide()
    {
        $max = $this->quintessentials->count() - 1;
        $this->activeIndex = ($this->activeIndex + 1) > $max ? 0 : $this->activeIndex + 1;
    }
    
    public function previousSlide()
    {
        $max = $this->quintessentials->count() - 1;
        $this->activeIndex = ($this->activeIndex - 1) < 0 ? $max : $this->activeIndex - 1;
    }
    
    public function render()
    {
        return view('livewire.quintessential-carousel');
    }
}
```

---

### 2. RitualFinder
**Location**: `app/Livewire/RitualFinder.php`

```php
class RitualFinder extends Component
{
    #[Reactive]
    public string $search = '';
    
    #[Reactive]
    public ?int $selectedQuintessential = null;
    
    #[Reactive]
    public string $sortBy = 'recent';
    
    #[Computed]
    public function rituals()
    {
        $query = Ritual::query();
        
        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
        }
        
        if ($this->selectedQuintessential) {
            $query->where('quintessential_id', $this->selectedQuintessential);
        }
        
        return match ($this->sortBy) {
            'popular' => $query->orderByDesc('community_votes'),
            'duration' => $query->orderBy('duration_minutes'),
            default => $query->latest('published_at'),
        }->paginate(12);
    }
    
    public function render()
    {
        return view('livewire.ritual-finder', [
            'quintessentials' => Quintessential::all(),
        ]);
    }
}
```

---

### 3. KnowledgeSearcher
**Location**: `app/Livewire/KnowledgeSearcher.php`

```php
class KnowledgeSearcher extends Component
{
    #[Reactive]
    public string $query = '';
    
    #[Computed]
    public function results()
    {
        if (strlen($this->query) < 2) {
            return collect();
        }
        
        return KnowledgeArticle::where('is_published', true)
            ->where(function ($q) {
                $q->where('title', 'like', "%{$this->query}%")
                  ->orWhere('content', 'like', "%{$this->query}%");
            })
            ->limit(10)
            ->get();
    }
    
    public function render()
    {
        return view('livewire.knowledge-searcher');
    }
}
```

---

### 4. ForumThread (Real-time Discussion)
**Location**: `app/Livewire/ForumThread.php`

```php
class ForumThread extends Component
{
    public int $threadId;
    
    #[Reactive]
    public string $newReply = '';
    
    #[Computed]
    public function thread()
    {
        return ForumThread::with('posts.author')->findOrFail($this->threadId);
    }
    
    #[Validate('required|min:3')]
    public function postReply()
    {
        $this->thread->posts()->create([
            'author_id' => auth()->id(),
            'content' => $this->newReply,
        ]);
        
        $this->newReply = '';
        $this->dispatch('reply-posted');
    }
    
    public function render()
    {
        return view('livewire.forum-thread');
    }
}
```

---

## Routes

### Primary Routes (`routes/web.php`)
```php
use App\Http\Controllers\PageController;
use App\Livewire\{
    QuintessentialCarousel,
    RitualFinder,
    KnowledgeSearcher,
    ForumThread,
    EventsCalendar,
};

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/philosopher-king', [PageController::class, 'philosopherKing'])->name('philosopher-king');

Route::get('/quintessentials', [PageController::class, 'quintessentialsIndex'])->name('quintessentials.index');
Route::get('/quintessentials/{slug}', [PageController::class, 'quintessentialsShow'])->name('quintessentials.show');

Route::get('/science', [PageController::class, 'science'])->name('science');
Route::get('/rituals', RitualFinder::class)->name('rituals.index');
Route::get('/rituals/{slug}', [PageController::class, 'ritualsShow'])->name('rituals.show');

Route::get('/chronicle', [PageController::class, 'chronicle'])->name('chronicle.index');
Route::get('/chronicle/{slug}', [PageController::class, 'chronicleShow'])->name('chronicle.show');

Route::get('/knowledge', KnowledgeSearcher::class)->name('knowledge.index');
Route::get('/knowledge/{slug}', [PageController::class, 'knowledgeShow'])->name('knowledge.show');

Route::get('/circle', [PageController::class, 'circleForum'])->name('circle.index');
Route::get('/circle/threads/{id}', ForumThread::class)->name('circle.thread.show');

Route::get('/events', EventsCalendar::class)->name('events.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
    Route::post('/rituals/{id}/attend', [PageController::class, 'attendRitual'])->name('rituals.attend');
});

Auth::routes();
```

---

## Seeding & Initial Data

### DatabaseSeeder
```bash
php artisan make:seeder QuintessentialSeeder
php artisan make:seeder PostSeeder
php artisan make:seeder RitualSeeder
php artisan make:seeder UserSeeder
```

**QuintessentialSeeder** (seeds the Quintessentials)
```php
public function run()
{
    $quintessentials = [
        ['name' => 'Goodness at theta', 'description' => '...', 'order' => 0],
        ['name' => '1-truth', 'description' => '...', 'order' => 1],
        ['name' => '2-connection', 'description' => '...', 'order' => 2],
        ['name' => '3-justice', 'description' => '...', 'order' => 3],
        ['name' => '4-Expression', 'description' => '...', 'order' => 4],
        ['name' => '5-Balance', 'description' => '...', 'order' => 5],
    ];
    
    foreach ($quintessentials as $q) {
        Quintessential::create($q);
    }
}
```

---

## Development Commands Cheat Sheet

```bash
# Create migrations, models, and controllers
php artisan make:model Post -mc
php artisan make:livewire QuintessentialCarousel

# Run migrations & seeders
php artisan migrate
php artisan db:seed

# Cache clearing
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# Watch mode for development
npm run dev
php artisan serve

# Tinker REPL for debugging
php artisan tinker

# Running tests
php artisan test
php artisan test --filter=PostTest

# Check for errors
php artisan lint
```

---

## File Structure Summary

```
essencience.com/
├── .env                          # Database, Mail, App config
├── .github/copilot-instructions.md
├── .secrets                      # SSH credentials (gitignored)
├── CONTENT_ANALYSIS.md           # This document
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   ├── Ritual.php
│   │   ├── Quintessential.php
│   │   ├── KnowledgeArticle.php
│   │   └── ForumThread.php
│   ├── Livewire/
│   │   ├── QuintessentialCarousel.php
│   │   ├── RitualFinder.php
│   │   ├── KnowledgeSearcher.php
│   │   ├── ForumThread.php
│   │   └── EventsCalendar.php
│   └── Http/Controllers/PageController.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php
│   │   ├── pages/
│   │   │   ├── home.blade.php
│   │   │   ├── quintessentials/
│   │   │   └── science.blade.php
│   │   ├── components/
│   │   └── livewire/
│   │       ├── quintessential-carousel.blade.php
│   │       ├── ritual-finder.blade.php
│   │       └── knowledge-searcher.blade.php
│   ├── css/
│   │   └── app.css              # Tailwind + Q colors
│   └── js/app.js
├── routes/web.php
├── public/
│   ├── images/
│   │   ├── theta-logo.svg
│   │   └── quintessentials/
│   └── css/app.css
└── storage/logs/
```

---

## Next Immediate Steps

1. **Initialize Laravel**: `composer create-project laravel/laravel .`
2. **Install Livewire & Tailwind**: `composer require livewire/livewire`, configure Tailwind
3. **Set up .env**: Database, mail, app config
4. **Create Database Schema**: Run migrations for Users, Quintessentials, Posts, etc.
5. **Seed Initial Data**: Quintessentials (Goodness at theta, 1-truth, 2-connection, 3-justice, 4-Expression, 5-Balance), Gene K. Goodreau user account
6. **Build Home Page**: Hero section + Quintessential Carousel (Livewire)
7. **Test locally**: `php artisan serve` + `npm run dev`
8. **Deploy to server**: Upload to `/home/u693982071/public_html/`

---

_Ready to begin Phase 1 development._
