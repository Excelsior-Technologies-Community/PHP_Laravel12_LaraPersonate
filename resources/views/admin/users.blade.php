<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">
                    👥 Users Dashboard
                </h2>

                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    Manage all users, impersonation access, filters and permissions.
                </p>
            </div>

            <div class="flex gap-3">

                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-5 py-3 rounded-2xl shadow-lg">
                    <p class="text-sm opacity-80">Total Users</p>
                    <h3 class="text-2xl font-bold">
                        {{ $users->total() }}
                    </h3>
                </div>

            </div>

        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-5 bg-emerald-500 text-white px-5 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Error Message --}}
            @if(session('error'))
                <div class="mb-5 bg-red-500 text-white px-5 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                    <span class="text-xl">❌</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Main Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700">

                {{-- Search Section --}}
                <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 border-b dark:border-gray-700">

                    <form method="GET"
                        class="grid grid-cols-1 md:grid-cols-4 gap-4">

                        {{-- Search --}}
                        <div class="md:col-span-2">
                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="🔍 Search by name or email..."
                                class="w-full rounded-2xl border-0 bg-white dark:bg-gray-900 dark:text-white shadow-md focus:ring-2 focus:ring-indigo-500 px-4 py-3">
                        </div>

                        {{-- Filter --}}
                        <div>
                            <select
                                name="type"
                                class="w-full rounded-2xl border-0 bg-white dark:bg-gray-900 dark:text-white shadow-md focus:ring-2 focus:ring-indigo-500 px-4 py-3">

                                <option value="">All Users</option>

                                <option value="admin"
                                    {{ request('type') == 'admin' ? 'selected' : '' }}>
                                    👑 Admin
                                </option>

                                <option value="user"
                                    {{ request('type') == 'user' ? 'selected' : '' }}>
                                    👤 User
                                </option>

                            </select>
                        </div>

                        {{-- Button --}}
                        <div class="flex gap-2">

                            <button
                                class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:scale-105 transition transform text-white font-semibold px-5 py-3 rounded-2xl shadow-lg">
                                Search
                            </button>

                            <a href="{{ route('users') }}"
                                class="bg-gray-300 hover:bg-gray-400 transition px-5 py-3 rounded-2xl text-gray-800 font-semibold">
                                Reset
                            </a>

                        </div>

                    </form>

                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">

                    <table class="min-w-full">

                        <thead class="bg-gray-900 text-white">

                            <tr>

                                <th class="px-6 py-5 text-left text-sm uppercase tracking-wider">
                                    User
                                </th>

                                <th class="px-6 py-5 text-left text-sm uppercase tracking-wider">
                                    Email
                                </th>

                                <th class="px-6 py-5 text-left text-sm uppercase tracking-wider">
                                    Role
                                </th>

                                <th class="px-6 py-5 text-center text-sm uppercase tracking-wider">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                            @forelse($users as $user)

                                <tr class="hover:bg-indigo-50 dark:hover:bg-gray-700 transition duration-200">

                                    {{-- User --}}
                                    <td class="px-6 py-5">

                                        <div class="flex items-center gap-4">

                                            <div
                                                class="w-12 h-12 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-lg">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>

                                            <div>
                                                <h4 class="font-bold text-gray-800 dark:text-white">
                                                    {{ $user->name }}
                                                </h4>

                                                <p class="text-xs text-gray-500">
                                                    User ID: #{{ $user->id }}
                                                </p>
                                            </div>

                                        </div>

                                    </td>

                                    {{-- Email --}}
                                    <td class="px-6 py-5 text-gray-700 dark:text-gray-300 font-medium">
                                        {{ $user->email }}
                                    </td>

                                    {{-- Role --}}
                                    <td class="px-6 py-5">

                                        @if($user->is_admin)

                                            <span
                                                class="bg-yellow-100 text-yellow-700 px-4 py-1 rounded-full text-xs font-bold shadow-sm">
                                                👑 ADMIN
                                            </span>

                                        @else

                                            <span
                                                class="bg-blue-100 text-blue-700 px-4 py-1 rounded-full text-xs font-bold shadow-sm">
                                                👤 USER
                                            </span>

                                        @endif

                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5">

                                        <div class="flex justify-center gap-3">

                                            {{-- Impersonate --}}
                                            @if(auth()->id() !== $user->id)

                                                <a href="{{ route('impersonate', $user->id) }}"
                                                    class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-xl shadow-lg transition hover:scale-105">
                                                    🎭 Impersonate
                                                </a>

                                            @endif

                                            {{-- Delete --}}
                                            <form
                                                action="{{ route('users.destroy', $user->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this user?')">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl shadow-lg transition hover:scale-105">
                                                    🗑 Delete
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="4" class="py-16 text-center">

                                        <div class="flex flex-col items-center">

                                            <div class="text-6xl mb-4">
                                                😕
                                            </div>

                                            <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                                No Users Found
                                            </h3>

                                            <p class="text-gray-500 mt-2">
                                                Try changing search keywords or filters.
                                            </p>

                                        </div>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-700">
                    {{ $users->links() }}
                </div>

            </div>

        </div>
    </div>

</x-app-layout>