<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('Admin — User Management') }}
    </h2>
  </x-slot>

  <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if (session('status'))
      <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 text-left">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Current Role</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Member Account</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Change Role</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @foreach ($users as $user)
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                    @if (Auth::id() === $user->id)
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        Current Session
                      </span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ $user->email }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    @if ($user->role === 'admin')
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        admin
                      </span>
                    @elseif ($user->role === 'librarian')
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                        librarian
                      </span>
                    @else
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                        member
                      </span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if ($user->member)
                      <span class="text-green-700 font-medium">Linked ({{ $user->member->loans->count() }} loans)</span>
                    @else
                      <span class="text-gray-400">None</span>
                    @endif
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    @if (Auth::id() === $user->id)
                      <span class="text-xs text-gray-500 italic">
                        {{ __('Cannot demote self') }}
                      </span>
                    @else
                      <form method="POST" action="{{ route('admin.users.role', $user) }}" class="inline-flex items-center space-x-2">
                        @csrf
                        @method('PATCH')
                        <select name="role" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1">
                          <option value="member" @selected($user->role === 'member')>member</option>
                          <option value="librarian" @selected($user->role === 'librarian')>librarian</option>
                          <option value="admin" @selected($user->role === 'admin')>admin</option>
                        </select>
                        <button type="submit" class="px-2.5 py-1 bg-gray-800 text-white rounded text-xs font-medium hover:bg-gray-700 transition">
                          {{ __('Update') }}
                        </button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="mt-6">
          {{ $users->links() }}
        </div>
      </div>
    </div>
  </div>
</x-app-layout>

