@forelse($users as $user)
    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition duration-150">
        
        <td class="px-6 py-5 whitespace-nowrap">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm">
                        {{ $user->name }}
                    </h4>
                    <p class="text-xs text-gray-400 mt-0.5">
                        ID: #{{ $user->id }}
                    </p>
                </div>
            </div>
        </td>

        <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 font-mono">
            {{ $user->email }}
        </td>

        <td class="px-6 py-5 whitespace-nowrap text-center">
            @if($user->is_admin)
                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 rounded-full">
                    Admin
                </span>
            @else
                <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300 rounded-full">
                    User
                </span>
            @endif
        </td>

        <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex justify-end gap-2">
                
                @if(auth()->id() !== $user->id)
                    <form action="{{ route('admin.impersonate.start', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/40 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                            Personate
                        </button>
                    </form>
                @endif

                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to completely remove this identity node?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/40 px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm">
                        Delete
                    </button>
                </form>

            </div>
        </td>

    </tr>
@empty
    <tr>
        <td colspan="4" class="py-16 text-center">
            <div class="flex flex-col items-center">
                <div class="text-6xl mb-4">😕</div>
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