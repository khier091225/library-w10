<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Library Books') }}
      </h2>
      @can('create', App\Models\Book::class)
        <a href="{{ route('books.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150">
          + {{ __('Add New Book') }}
        </a>
      @endcan
    </div>
  </x-slot>

  <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if (session('status'))
      <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @forelse ($books as $book)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100 flex flex-col justify-between p-6">
          <div>
            <div class="flex items-start justify-between">
              <h3 class="text-lg font-bold text-gray-900 leading-snug">
                <a href="{{ route('books.show', $book) }}" class="hover:text-indigo-600">
                  {{ $book->title }}
                </a>
              </h3>
              @if ($book->is_reference)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                  Reference
                </span>
              @endif
            </div>

            <p class="mt-2 text-sm text-gray-600">
              By <span class="font-medium text-gray-900">{{ $book->author->name }}</span>
            </p>

            <div class="mt-4 text-xs text-gray-500 space-y-1">
              <div><span class="font-semibold">ISBN:</span> {{ $book->isbn }}</div>
              <div><span class="font-semibold">Year:</span> {{ $book->published_year }}</div>
              <div>
                <span class="font-semibold">Status:</span>
                @if ($book->is_reference)
                  <span class="text-amber-600 font-medium">Library Use Only</span>
                @elseif ($book->active_loans_count > 0)
                  <span class="text-red-600 font-medium">Currently On Loan</span>
                @else
                  <span class="text-green-600 font-medium">Available</span>
                @endif
              </div>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('books.show', $book) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">
              View Details &rarr;
            </a>

            <div class="flex items-center space-x-2">
              @can('update', $book)
                <a href="{{ route('books.edit', $book) }}" class="text-xs text-gray-600 hover:text-gray-900">
                  Edit
                </a>
              @endcan
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-3 bg-white p-8 rounded-lg shadow-sm text-center text-gray-500">
          No books found in the library catalog.
        </div>
      @endforelse
    </div>

    <div class="mt-8">
      {{ $books->links() }}
    </div>
  </div>
</x-app-layout>

