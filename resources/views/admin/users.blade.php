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
                    <h3 class="text-2xl font-bold" id="totalUsersCount">
                        {{ $users->total() }}
                    </h3>
                </div>

            </div>

        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 bg-emerald-500 text-white px-5 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-rose-500 text-white px-5 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                    <span class="text-xl">❌</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <div class="p-6 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between">
                    
                    <div class="w-full md:w-96 relative">
                        <span class="absolute left-4 top-3.5 text-gray-400">🔍</span>
                        <input type="text" id="ajaxSearch" placeholder="Search by name, email or ID..." class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <select id="roleFilter" class="w-full md:w-48 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white">
                            <option value="">All Account Roles</option>
                            <option value="admin">Administrators Only</option>
                            <option value="user">Regular Users Only</option>
                        </select>
                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-4">User Details</th>
                                <th class="px-6 py-4">Email Signature</th>
                                <th class="px-6 py-4 text-center">Account Role</th>
                                <th class="px-6 py-4 text-right">System Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userContainer" class="divide-y divide-gray-100 dark:divide-gray-700">
                            @include('admin.partials.user-rows')
                        </tbody>
                    </table>
                </div>

                <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t dark:border-gray-700" id="paginationContainer">
                    {{ $users->links() }}
                </div>

            </div>

        </div>
    </div>

    <script>
        const searchInput = document.getElementById('ajaxSearch');
        const roleFilter = document.getElementById('roleFilter');
        const container = document.getElementById('userContainer');
        const pagination = document.getElementById('paginationContainer');

        function fetchFilteredUsers(page = 1) {
            const search = searchInput.value;
            const type = roleFilter.value;

            fetch(`{{ route('users') }}?search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}&page=${page}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                container.innerHTML = data.html;
                pagination.innerHTML = data.pagination;
                attachPaginationLinks();
            });
        }

        function attachPaginationLinks() {
            pagination.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const urlParams = new URLSearchParams(this.getAttribute('href').split('?')[1]);
                    const page = urlParams.get('page') || 1;
                    fetchFilteredUsers(page);
                });
            });
        }

        searchInput.addEventListener('input', () => fetchFilteredUsers(1));
        roleFilter.addEventListener('change', () => fetchFilteredUsers(1));
        attachPaginationLinks();
    </script>

</x-app-layout>x