<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $book->title }}
      </h2>
      <a href="{{ route('books.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
        &larr; {{ __('Back to catalog') }}
      </a>
    </div>
  </x-slot>

  <div class="py-8 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    @if (session('status'))
      <div class="mb-6 p-4 rounded-md bg-green-50 border border-green-200 text-green-800">
        {{ session('status') }}
      </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="px-4 py-5 sm:px-6 flex justify-between items-center border-b border-gray-200">
        <div>
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ $book->title }}
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Author: <span class="font-semibold text-gray-800">{{ $book->author->name }}</span>
          </p>
        </div>

        @if ($book->is_reference)
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
            Reference Copy
          </span>
        @elseif ($hasActiveLoan)
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
            Currently on Loan
          </span>
        @else
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
            Available to Borrow
          </span>
        @endif
      </div>

      <div class="px-4 py-5 sm:p-6 space-y-4">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500">ISBN</dt>
            <dd class="mt-1 text-sm font-mono text-gray-900">{{ $book->isbn }}</dd>
          </div>

          <div>
            <dt class="text-sm font-medium text-gray-500">Published Year</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $book->published_year }}</dd>
          </div>

          <div>
            <dt class="text-sm font-medium text-gray-500">Circulation Type</dt>
            <dd class="mt-1 text-sm text-gray-900">
              {{ $book->is_reference ? 'Reference material (Non-circulating)' : 'Standard circulating copy' }}
            </dd>
          </div>

          <div>
            <dt class="text-sm font-medium text-gray-500">Availability</dt>
            <dd class="mt-1 text-sm font-semibold">
              @if ($book->is_reference)
                <span class="text-amber-700">Must be read inside the library</span>
              @elseif ($hasActiveLoan)
                <span class="text-red-600">Checked out by a member</span>
              @else
                <span class="text-green-600">Ready on shelves</span>
              @endif
            </dd>
          </div>
        </dl>

        <!-- Borrow Action Section -->
        <div class="pt-6 border-t border-gray-100">
          @if ($book->is_reference)
            <div class="p-4 rounded-md bg-amber-50 text-amber-800 text-sm">
              <strong>Notice:</strong> This is a reference book. Reference books cannot be checked out or borrowed.
            </div>
          @elseif ($hasActiveLoan)
            <div class="p-4 rounded-md bg-yellow-50 text-yellow-800 text-sm">
              <strong>Currently Unavailable:</strong> This book has been borrowed and has not been returned yet.
            </div>
          @else
            @auth
              <form method="POST" action="{{ route('books.borrow', $book) }}" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                  <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                  {{ __('Borrow this Book') }}
                </button>
              </form>
            @else
              <div class="p-4 rounded-md bg-gray-50 text-sm text-gray-700 flex items-center justify-between">
                <span>Want to borrow this book?</span>
                <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-800">
                  {{ __('Log in to borrow') }} &rarr;
                </a>
              </div>
            @endauth
          @endif
        </div>

        <!-- Staff Actions Section -->
        <div class="pt-6 border-t border-gray-100 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            @can('update', $book)
              <a href="{{ route('books.edit', $book) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900">
                {{ __('Edit Book') }}
              </a>
            @endcan

            @can('delete', $book)
              <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Are you sure you want to delete this book?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900">
                  {{ __('Delete') }}
                </button>
              </form>
            @elsecan('update', $book)
              <span class="text-xs text-gray-500 italic">
                Cannot delete: book has active or historic loans.
              </span>
            @endcan
          </div>

          <a href="{{ route('books.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
            {{ __('Back to Books') }}
          </a>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>

