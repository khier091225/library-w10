<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ __('My Borrowed Books') }}
    </h2>
  </x-slot>

  <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if (session('status'))
      <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    @if (session('error'))
      <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200 text-red-800">
        {{ session('error') }}
      </div>
    @endif

    <!-- Active Reservations Section -->
    @if (isset($reservations) && $reservations->isNotEmpty())
      <div class="mb-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ __('My Active Book Holds') }} ({{ $reservations->count() }})
          </h3>
          <span class="text-xs text-gray-500">
            {{ __('Books you have reserved while they are on loan') }}
          </span>
        </div>
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved At</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($reservations as $reservation)
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <a href="{{ route('books.show', $reservation->book) }}" class="font-semibold text-indigo-600 hover:text-indigo-900">
                        {{ $reservation->book->title }}
                      </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ $reservation->book->author->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ $reservation->reserved_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      <form method="POST" action="{{ route('reservations.destroy', $reservation) }}" onsubmit="return confirm('Cancel this hold?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                          {{ __('Cancel Hold') }}
                        </button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ __('Loan History & Borrowed Items') }}
        </h3>
      </div>
      <div class="p-6 text-gray-900">
        @if ($loans->isEmpty())
          <div class="text-center py-8 text-gray-500">
            <p>{{ __('You have not borrowed any books yet.') }}</p>
            <a href="{{ route('books.index', ['status' => 'available']) }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900 font-semibold text-sm">
              {{ __('Browse available books in the catalog &rarr;') }}
            </a>
          </div>
        @else
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Borrowed At</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                  <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($loans as $loan)
                  <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <a href="{{ route('books.show', $loan->book) }}" class="font-semibold text-indigo-600 hover:text-indigo-900">
                        {{ $loan->book->title }}
                      </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                      {{ $loan->book->author->name }}
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
                          Returned ({{ $loan->returned_at->format('M d, Y') }})
                        </span>
                      @elseif ($loan->isOverdue())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                          Overdue ({{ $loan->daysOverdue() }} days late)
                        </span>
                      @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                          Active (Due {{ $loan->due_at ? $loan->due_at->diffForHumans() : '' }})
                        </span>
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
