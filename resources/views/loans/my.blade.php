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

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
      <div class="p-6 text-gray-900">
        @if ($loans->isEmpty())
          <div class="text-center py-8 text-gray-500">
            <p>{{ __('You have not borrowed any books yet.') }}</p>
            <a href="{{ route('books.index') }}" class="mt-4 inline-block text-indigo-600 hover:text-indigo-900 font-semibold text-sm">
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
                      {{ $loan->borrowed_at->format('M d, Y h:i A') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      @if ($loan->returned_at)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Returned ({{ $loan->returned_at->format('M d, Y') }})
                        </span>
                      @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                          Active Loan (In your possession)
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

