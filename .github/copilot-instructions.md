# Essencience.com - Laravel Project Copilot Instructions

## Project Overview
Essencience.com is a Laravel web application. This document guides AI agents in understanding the codebase architecture, development workflows, and project-specific conventions.

## Architecture & Key Components

### Directory Structure
- `app/` - Core application code (Models, Controllers, Services, etc.)
- `routes/` - Route definitions (web.php, api.php)
- `resources/` - Views, language files, and frontend assets
- `database/` - Migrations, seeders, and factories
- `config/` - Configuration files for services and libraries
- `tests/` - Test suite (Feature and Unit tests)
- `storage/` - Runtime storage (logs, uploads, cache)
- `bootstrap/` - Application bootstrapping
- `public/` - Publicly accessible files and compiled assets

### Key Architectural Patterns
- **Livewire Components**: Reactive UI components in `app/Livewire/` - handles server-side state, rendering, and user interactions
- **MVC Architecture**: Models (Eloquent ORM), Controllers (request handling), Views (response templates)
- **Service Layer**: Business logic in `app/Services/` to keep controllers and components thin
- **Repositories**: Data access abstraction in `app/Repositories/` (when used)
- **Middleware**: Request/response pipeline processing in `app/Http/Middleware/`
- **Events & Listeners**: Decoupled event-driven workflows in `app/Events/` and `app/Listeners/`

## Development Workflows

### Local Setup
```bash
composer install           # Install PHP dependencies
npm install               # Install JS dependencies
cp .env.example .env      # Create environment file
php artisan key:generate  # Generate app encryption key
php artisan migrate       # Run database migrations
npm run dev               # Build frontend assets
php artisan serve         # Start development server
```

### Database Management
- Migrations: `php artisan make:migration <name>` → edit in `database/migrations/`
- Seeds: `php artisan make:seeder <name>` → edit in `database/seeders/`
- Run migrations: `php artisan migrate` (use `--rollback` to undo, `--fresh` for clean slate)
- Tinker REPL: `php artisan tinker` for interactive testing

### Testing
- Run tests: `php artisan test` (or `php test`)
- Create tests: `php artisan make:test <name> --unit` or `--feature`
- Test locations: `tests/Unit/` and `tests/Feature/`
- Use factories for test data: `php artisan make:factory <Model>Factory`

##Livewire component: `php artisan make:livewire <Name>` (creates component class and Blade view)
- # Code Generation
- Model with migration: `php artisan make:model <Name> -m`
- Controller: `php artisan make:controller <Name>Controller`
- Request validation class: `php artisan make:request <Name>Request`
- Job/Queue: `php artisan make:job <Name>`
- Event: `php artisan make:event <Name>` (pair with `make:listener`)

## Project-Specific Conventions

### Naming Conventions
- Models: Singular, PascalCase (e.g., `User`, `BlogPost`)
- Tables: Plural, snake_case (e.g., `users`, `blog_posts`)
- Controllers: PascalCase + "Controller" (e.g., `UserController`)
- Routes: RESTful naming (index, show, create, store, edit, update, destroy)
- Methods: camelCase (e.g., `getUserById()`)
- Constants: UPPER_SNAKE_CASE

### Code Style
- PSR-12 PHP coding standard
- Use strict types: `declare(strict_types=1);` at top of files
- Eloquent over Query Builder when appropriate
- Type hints required for method parameters and return types
- Use dependency injection in constructors

### Livewire Component Lifecycle**: Use `mount()` for initialization, `render()` for views, `#[Reactive]` properties for two-way binding
- **Livewire Validation**: Use `#[Validate]` attributes or `$this->validate()` in component methods
- **Livewire Actions**: Use public methods in components as action handlers (automatically called by wire:click, wire:submit, etc.)
- **Form Requests**: Use `app/Http/Requests/` for validation logic in traditional controllers
- **Resource Classes**: Use `app/Http/Resources/` for API response transformation
- **Query Scopes**: Define reusable query filters in Models (e.g., `User::active()`)
- **Accessors/Mutators**: Use `$casts` property instead of deprecated accessors/mutators (Laravel 9+)
- **Relationships**: Define in Models; use `#[Computed]` or `#[Reactive]` in Livewire for efficient loading (Laravel 9+)
- **Relationships**: Define in Models; lazy-load unless intentional eager loading needed

## Integration Points & Dependencies

### External Services
- Check `.env` for service credentials (API keys, database URLs, etc.)
- SeLivewire**: Full-stack reactive framework for building dynamic UI components without JavaScript
- **Eloquent ORM**: Database abstraction and relationships
- **Blade**: Template engine for views (used within Livewire components)opment, testing, production

### Key Dependencies to Know
- **Eloquent ORM**: Database abstraction and relationships
- **Blade**: Template engine for views
- **Artisan**: CLI for common tasks
- **Laravel Mix/Vite**: Frontend asset bundling (check webpack.mix.js or vite.config.js)
- **Sanctum/Passport**: Authentication (check which is configured in `config/auth.php`)
- **Queue/Redis**: Background jobs (if configured in `.env`)

### Database & Models
- Migrations must be reversible (implement `down()` method)
- Soft deletes: use `SoftDeletes` trait for logical deletion
- Factories: Use `HasFactory` trait for seedable, testable models
- Relationships: Explicitly define `$table` if table name doesn't follow conventions

## Debugging & Inspection

### Essential Commands
- `php artisan tinker` - Interactive shell for debugging
- `php artisan db:seed` - Populate database with test data
- `php artisan cache:clear` - Clear application cache
- `php artisan view:clear` - Clear compiled views
- `php artisan config:clear` - Clear cached configuration
- `php artisan tail` - View application logs in real-time (Laravel 8+)

### Logging
- Log to `storage/logs/laravel.log`
- UseLivewire Reactivity**: Properties must be public to be reactive; use `#[Reactive]` for automatic updates
2. **N+1 Queries**: Use `with()` for eager loading relationships, especially in Livewire component properties
3. **Livewire Computed Properties**: Use `#[Computed]` for derived values instead of recalculating in render()
4. **Mass Assignment**: Define `$fillable` or `$guarded` on models
5. **Route Model Binding**: Use implicit binding in routes: `Route::get('/users/{user}', ...)`
6. **Livewire Asset Loading**: Ensure Livewire scripts are included in layout: `@livewireScripts` and `@livewireStyles`
7. **Cache Invalidation**: Clear cache when modifying configuration or seeds
8. **Testing Database**: Tests use separate database (check `phpunit.xml` for DB_DATABASE
3. **Route Model Binding**: Use implicit binding in routes: `Route::get('/users/{user}', ...)`
4. **Middleware Ordering**: Check `app/Http/Kernel.php` for middleware priority
### Traditional MVC Approach
1. Create migration: `php artisan make:migration create_<table>_table`
2. Create Model: `php artisan make:model <Name> -m`
3. Create Controller: `php artisan make:controller <Name>Controller`
4. Define routes in `routes/web.php`
5. Create views in `resources/views/`
6. Write tests: `php artisan test`

### Livewire Component Approach (Preferred for Interactive UI)
1. Create migration: `php artisan make:migration create_<table>_table`
2. Create Model: `php artisan make:model <Name> -m`
3. Create Livewire component: `php artisan make:livewire <Feature/ComponentName>`
4. Add properties, computed properties, and action methods to component
5. Edit Blade view in `resources/views/livewire/`
6. Register route: `Route::get('/path', <Namespace>\<ComponentName>::class)`
7. Writeate migration: `php artisan make:migration create_<table>_table`
2. Create Model: `php artisan make:model <Name> -m` (if not in migration)
3. Create Controller: `php artisan make:controller <Name>Controller`
4. Define routes in `routes/web.php` or `routes/api.php`
5. Create views in `resources/views/`
6. Write tests in `tests/Feature/` or `tests/Unit/`
7. Run tests: `php artisan test`

## Configuration Notes

- Database connection: `.env` (DB_CONNECTION, DB_HOST, DB_DATABASE, etc.)
- Mail driver: Check `config/mail.php` and `.env` MAIL_* settings
- Cache/Session: Configurable in `.env` (CACHE_DRIVER, SESSION_DRIVER)
- Queue: `QUEUE_CONNECTION` in `.env` (database, redis, sync)
- App timezone: Set in `config/app.php` (APP_TIMEZONE in newer versions)
