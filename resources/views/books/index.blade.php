<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Library Books Catalog') }}
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

    <!-- Search & Filter Controls -->
    <div class="mb-8 bg-white p-5 rounded-lg shadow-sm border border-gray-100">
      <form method="GET" action="{{ route('books.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <!-- Search Keyword -->
        <div>
          <label for="q" class="block text-xs font-semibold text-gray-600 uppercase mb-1">{{ __('Search') }}</label>
          <input type="text" id="q" name="q" value="{{ $search ?? '' }}" placeholder="Title, author, or ISBN..." class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <!-- Availability Status -->
        <div>
          <label for="status" class="block text-xs font-semibold text-gray-600 uppercase mb-1">{{ __('Availability') }}</label>
          <select id="status" name="status" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="available" @selected(($status ?? '') === 'available')>{{ __('Available to Borrow') }}</option>
            <option value="borrowed" @selected(($status ?? '') === 'borrowed')>{{ __('Currently on Loan') }}</option>
            <option value="reference" @selected(($status ?? '') === 'reference')>{{ __('Reference (Library only)') }}</option>
          </select>
        </div>

        <!-- Author -->
        <div>
          <label for="author_id" class="block text-xs font-semibold text-gray-600 uppercase mb-1">{{ __('Author') }}</label>
          <select id="author_id" name="author_id" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">{{ __('All Authors') }}</option>
            @foreach ($authors as $author)
              <option value="{{ $author->id }}" @selected(($authorId ?? '') == $author->id)>{{ $author->name }}</option>
            @endforeach
          </select>
        </div>

        <!-- Buttons -->
        <div class="flex items-center space-x-2">
          <button type="submit" class="flex-1 py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold transition">
            {{ __('Filter') }}
          </button>
          @if (!empty($search) || !empty($status) || !empty($authorId))
            <a href="{{ route('books.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm font-medium transition text-center">
              {{ __('Reset') }}
            </a>
          @endif
        </div>
      </form>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @forelse ($books as $book)
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-100 flex flex-col justify-between p-6">
          <div>
            <div class="flex gap-4 items-start mb-4">
              <!-- Book Cover Thumbnail -->
              <div class="w-20 h-28 shrink-0 bg-gray-100 rounded border border-gray-200 overflow-hidden flex items-center justify-center">
                @if ($book->cover_path)
                  <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
                @else
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                @endif
              </div>

              <!-- Title & Badges -->
              <div class="flex-1">
                <div class="flex items-start justify-between">
                  <h3 class="text-base font-bold text-gray-900 leading-snug">
                    <a href="{{ route('books.show', $book) }}" class="hover:text-indigo-600">
                      {{ $book->title }}
                    </a>
                  </h3>
                </div>
                <p class="mt-1 text-sm text-gray-600">
                  By <span class="font-medium text-gray-900">{{ $book->author->name }}</span>
                </p>
                <div class="mt-2">
                  @if ($book->is_reference)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                      Reference
                    </span>
                  @elseif ($book->active_loans_count > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                      On Loan
                    </span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                      Available
                    </span>
                  @endif
                </div>
              </div>
            </div>

            <div class="text-xs text-gray-500 space-y-1 pt-2 border-t border-gray-100">
              <div><span class="font-semibold">ISBN:</span> <span class="font-mono">{{ $book->isbn }}</span></div>
              <div><span class="font-semibold">Published:</span> {{ $book->published_year }}</div>
            </div>
          </div>

          <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
            <a href="{{ route('books.show', $book) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">
              View Details &rarr;
            </a>

            <div class="flex items-center space-x-2">
              @can('update', $book)
                <a href="{{ route('books.edit', $book) }}" class="text-xs text-gray-600 hover:text-gray-900 font-medium">
                  Edit
                </a>
              @endcan
            </div>
          </div>
        </div>
      @empty
        <div class="col-span-3 bg-white p-8 rounded-lg shadow-sm text-center text-gray-500">
          {{ __('No books match the selected search or filter criteria.') }}
        </div>
      @endforelse
    </div>

    <div class="mt-8">
      {{ $books->links() }}
    </div>
  </div>
</x-app-layout>
