# PHP_Laravel12_LaraPersonate

## Introduction

PHP_Laravel12_LaraPersonate is a modern Laravel 12 application that demonstrates a secure and efficient user impersonation system.

This application enables administrators to temporarily log in as another user without requiring their credentials. It is particularly useful for debugging, user support, and testing role-based functionalities in real-world applications.

The project is built using Laravel Breeze for authentication and follows best practices such as middleware-based control, session management, and role-based authorization to ensure both security and maintainability.

---

## Project Overview

This project implements an admin-controlled impersonation feature in a Laravel 12 application.

The system allows an authenticated admin to:

- View all registered users
- Impersonate any user (except themselves)
- Access the application as the selected user
- Exit impersonation and return to the original admin account

The impersonation process works by storing the original admin ID in the session and switching authentication to the selected user. When the admin exits impersonation, the system restores the original session securely.

### Key Components

- **Controllers**
  - Handle impersonation logic and user management

- **Middleware**
  - Detects impersonation state and shares it with views

- **Blade Views**
  - Display user interface and impersonation banner

- **Routes**
  - Protected using authentication middleware

### Security Measures

- Only admin users can impersonate others
- Self-impersonation is prevented
- Unauthorized access returns a 403 error

---

## Requirements

- PHP >= 8.2

- Composer

- Node.js & npm

- MySQL / PostgreSQL

- Laravel 12

---

## Step 1: Create Laravel 12 Project

Run the following command:

```bash
composer create-project laravel/laravel PHP_Laravel12_LaraPersonate "12.*"
```

Go inside the project:

```bash
cd PHP_Laravel12_LaraPersonate
```

---

## Step 2: Create Authentication

Install Laravel UI / Breeze (recommended):

```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install 
npm run build
```
---

## Step 3: Configure Environment

Update .env file:

```.env
DB_DATABASE=larapersonate
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```
---

## Step 4: Add Admin Column 

```bash
php artisan make:migration add_is_admin_to_users_table
```

Migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
```

Run migrations:

```bash
php artisan migrate
```

---

## Step 5: Update User Model

File: app/Models/User.php

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin', // ADD THIS 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean', //  ADD THIS 
        ];
    }
}
```

---

## Step 6: Create Impersonation Controller

```bash
php artisan make:controller Admin/ImpersonationController
```

File: app/Http/Controllers/Admin/ImpersonationController.php

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    // Start impersonation
    public function impersonate($id)
    {
        $currentUser = Auth::user();

        // Only allow admin to impersonate
        if (!$currentUser || !$currentUser->is_admin) {
            abort(403, 'Unauthorized');
        }

        $user = User::findOrFail($id);

        // Prevent impersonating self
        if ($currentUser->id === $user->id) {
            return back()->with('error', 'You cannot impersonate yourself');
        }

        // Store original user
        session(['impersonate_original_id' => $currentUser->id]);

        // Login as target user
        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Now impersonating ' . $user->name);
    }

    // Stop impersonation
    public function leave()
    {
        if (session()->has('impersonate_original_id')) {

            $originalId = session('impersonate_original_id');

            Auth::loginUsingId($originalId);

            session()->forget('impersonate_original_id');

            return redirect('/dashboard')->with('success', 'Back to your account');
        }

        return redirect('/dashboard');
    }
}
```

---

## Step 7: Middleware

```bash
php artisan make:middleware ImpersonateMiddleware
```

File: app/Http/Middleware/ImpersonateMiddleware.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ImpersonateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Share flag to UI
        if (session()->has('impersonate_original_id')) {
            view()->share('isImpersonating', true);
        }

        return $next($request);
    }
}
```

---

## Step 8: Register Middleware

File: bootstrap/app.php

Add:

```php
->withMiddleware(function ($middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\ImpersonateMiddleware::class,
    ]);
})
```
---

## Step 9: Create Users List (Admin)

File: app/Http/Controllers/Admin/UserController.php

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }
}
```

---

## Step 10: Blade View

File: resources/views/admin/users.blade.php

```blade
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Users List
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <table class="min-w-full border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700">
                            <th class="p-2">Name</th>
                            <th class="p-2">Email</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($users as $user)
                        <tr class="border-t">
                            <td class="p-2">{{ $user->name }}</td>
                            <td class="p-2">{{ $user->email }}</td>
                            <td class="p-2">
                                @if(auth()->id() !== $user->id)
                                    <a href="{{ route('impersonate', $user->id) }}"
                                       class="bg-yellow-500 text-white px-3 py-1 rounded">
                                        Impersonate
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

            </div>

        </div>
    </div>

</x-app-layout>
````

## Step 11: Stop Impersonation Button

File: resources/views/layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        {{--  Impersonation Banner --}}
        @if(session()->has('impersonate_original_id'))
        <div class="bg-red-600 text-white text-center p-3 flex justify-center items-center gap-3">
            <span>⚠️ You are impersonating another user</span>
            <a href="{{ route('impersonate.leave') }}"
                class="bg-white text-red-600 px-3 py-1 rounded font-semibold">
                Leave
            </a>
        </div>
        @endif

        <!-- Page Heading -->
        @isset($header)
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>
```

---

## Step 12: Routes

File: routes/web.php

```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/users', [UserController::class, 'index'])->name('users');

    Route::get('/impersonate/{id}', [ImpersonationController::class, 'impersonate'])
        ->name('impersonate');

    Route::get('/impersonate-leave', [ImpersonationController::class, 'leave'])
        ->name('impersonate.leave');
});


require __DIR__.'/auth.php';
```

---

## Step 13: Add Users Link In Navbar 

File: resources/views/layouts/navigation.blade.php

Find this section:
	
```
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
```

Replace with:

```
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

    <!-- Dashboard -->
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    <!-- Users (Only Admin) -->
    @if(auth()->user()->is_admin)
        <x-nav-link :href="route('users')" :active="request()->routeIs('users')">
            {{ __('Users') }}
        </x-nav-link>
    @endif

</div>
```
---

## Step 14: Seed Admin User

```bash
php artisan tinker
```

```
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'is_admin' => true,
]);
```
---

## Step 15: Create another normal user

- Open the application in browser:

```bash
  http://127.0.0.1:8000/register
```

- Register a new user (this will act as the target user for impersonation)

---

## Step 16: Run Development Server

Run:

```bash
php artisan serve
```

Open:

```bash
http://127.0.0.1:8000
```

---

## How It Works

1. Login as Admin

- Use admin credentials created via Tinker

- Admin will see "Users" link in navbar

2. View Users

- Click on "Users"

- List of all registered users will be displayed

3. Impersonate User

- Click "Impersonate" button

- System logs in as selected user

- You will be redirected to dashboard

4. Impersonation Mode

- Red banner appears at top: "You are impersonating another user"

- You can use the system as that user

5. Exit Impersonation

- Click "Leave" button in banner

- You will return to original admin account

---

## Output

<img src="screenshots/Screenshot 2026-03-20 110846.png" width="1000">
 
<img src="screenshots/Screenshot 2026-03-20 111204.png" width="1000">
 
<img src="screenshots/Screenshot 2026-03-20 111329.png" width="1000">
 
<img src="screenshots/Screenshot 2026-03-20 111342.png" width="1000">
 
---

## Project Structure

```
PHP_Laravel12_LaraPersonate/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── ImpersonationController.php
│   │   │       └── UserController.php
│   │   │
│   │   └── Middleware/
│   │       └── ImpersonateMiddleware.php
│
├── bootstrap/
│   └── app.php
│
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_users_table.php // default 
│   │   └── 2024_01_02_000000_add_is_admin_to_users_table.php 
│   │
│   └── seeders/
│
├── routes/
│   └── web.php
│
├── resources/
│   └── views/
│       ├── admin/
│       │   └── users.blade.php
│       │
│       └── layouts/
│           └── app.blade.php
│
├── public/
├── config/
├── storage/
├── tests/
├── vendor/
│
├── .env
├── artisan
└── composer.json
```

---

Your PHP_Laravel12_LaraPersonate Project is now ready!


