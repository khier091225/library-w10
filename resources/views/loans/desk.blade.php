<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Circulation Desk — Loan Management') }}
      </h2>
      <div class="flex items-center space-x-2 text-sm">
        <a href="{{ route('desk.index', ['status' => 'active']) }}" class="px-3 py-1.5 rounded-md {{ $filter === 'active' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
          {{ __('Active Loans') }}
        </a>
        <a href="{{ route('desk.index', ['status' => 'overdue']) }}" class="px-3 py-1.5 rounded-md {{ $filter === 'overdue' ? 'bg-red-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
          {{ __('Overdue Loans') }}
        </a>
        <a href="{{ route('desk.index', ['status' => 'returned']) }}" class="px-3 py-1.5 rounded-md {{ $filter === 'returned' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
          {{ __('Returned Loans') }}
        </a>
        <a href="{{ route('desk.index', ['status' => 'all']) }}" class="px-3 py-1.5 rounded-md {{ $filter === 'all' ? 'bg-indigo-600 text-white font-semibold' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
          {{ __('All History') }}
        </a>
      </div>
    </div>
  </x-slot>

  <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if (session('status'))
      <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900">
        @if ($loans->isEmpty())
          <p class="text-center py-8 text-gray-500">
            {{ __('No loan records found for this filter.') }}
          </p>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed At</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($loans as $loan)
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-semibold text-gray-900">{{ $loan->member->name }}</div>
                      <div class="text-xs text-gray-500">{{ $loan->member->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <a href="{{ route('books.show', $loan->book) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                        {{ $loan->book->title }}
                      </a>
                      <div class="text-xs text-gray-500">By {{ $loan->book->author->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ $loan->borrowed_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                      {{ $loan->due_at ? $loan->due_at->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      @if ($loan->returned_at)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Returned on {{ $loan->returned_at->format('M d, Y') }}
                        </span>
                      @elseif ($loan->isOverdue())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                          Overdue ({{ $loan->daysOverdue() }}d)
                        </span>
                      @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                          Out on Loan
                        </span>
                      @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                      @if (! $loan->returned_at)
                        @can('return', $loan)
                          <form method="POST" action="{{ route('desk.loans.return', $loan) }}" class="inline-block">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-indigo-700 transition">
                              {{ __('Mark Returned') }}
                            </button>
                          </form>
                        @endcan
                      @else
                        <span class="text-xs text-gray-400">Completed</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="mt-6">
            {{ $loans->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
</x-app-layout>
