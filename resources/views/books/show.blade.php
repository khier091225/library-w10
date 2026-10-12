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

    @if (session('error'))
      <div class="mb-6 p-4 rounded-md bg-red-50 border border-red-200 text-red-800">
        {{ session('error') }}
      </div>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row gap-6 items-start">
        <!-- Book Cover -->
        <div class="w-32 h-44 shrink-0 bg-gray-100 rounded-lg border border-gray-200 overflow-hidden flex items-center justify-center shadow-sm">
          @if ($book->cover_path)
            <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
          @else
            <div class="text-center p-3 text-gray-400">
              <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
              <span class="text-xs">No cover</span>
            </div>
          @endif
        </div>

        <div class="flex-1 flex flex-col justify-between self-stretch">
          <div>
            <div class="flex flex-wrap items-center justify-between gap-2">
              <h3 class="text-2xl font-bold text-gray-900 leading-tight">
                {{ $book->title }}
              </h3>

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

            <p class="mt-2 text-base text-gray-600">
              Author: <span class="font-semibold text-gray-900">{{ $book->author->name }}</span>
            </p>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-100">
            <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4">
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">ISBN</dt>
                <dd class="mt-1 text-sm font-mono text-gray-900">{{ $book->isbn }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Year</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $book->published_year }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Type</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $book->is_reference ? 'Reference' : 'Circulating' }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <div class="px-4 py-5 sm:p-6 space-y-4">
        <!-- Action Section: Borrow / Hold -->
        <div>
          @if ($book->is_reference)
            <div class="p-4 rounded-md bg-amber-50 text-amber-800 text-sm">
              <strong>Notice:</strong> This is a reference book. Reference books cannot be checked out or reserved.
            </div>
          @elseif ($hasActiveLoan)
            <div class="p-5 rounded-md bg-amber-50 border border-amber-200 text-sm space-y-4">
              <div>
                <h4 class="font-semibold text-amber-900 text-base">Book is Currently Checked Out</h4>
                <p class="text-amber-800 mt-1">
                  This copy has been borrowed by another member. 
                  @if ($book->activeReservations->count() > 0)
                    There are currently <strong>{{ $book->activeReservations->count() }}</strong> member(s) waiting in the reservation queue.
                  @else
                    There are no holds in line yet. You can be the first to reserve it!
                  @endif
                </p>
              </div>

              @auth
                @if ($userReservation)
                  <div class="bg-white p-4 rounded-md border border-amber-300 flex items-center justify-between">
                    <div>
                      <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 mr-2">
                        Hold Placed
                      </span>
                      <span class="text-sm font-medium text-gray-900">
                        You are <strong>#{{ $queuePosition }}</strong> in line. Reserved on {{ $userReservation->reserved_at->format('M d, Y') }}.
                      </span>
                    </div>

                    <form method="POST" action="{{ route('reservations.destroy', $userReservation) }}" onsubmit="return confirm('Cancel your hold on this book?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-semibold rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none">
                        {{ __('Cancel Hold') }}
                      </button>
                    </form>
                  </div>
                @else
                  <div>
                    <form method="POST" action="{{ route('books.reserve', $book) }}">
                      @csrf
                      <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-indigo-700 active:bg-indigo-900 transition shadow-sm">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('Place a Hold (Reserve)') }}
                      </button>
                    </form>
                  </div>
                @endif
              @else
                <div class="pt-2 text-sm text-amber-900">
                  <a href="{{ route('login') }}" class="font-semibold underline hover:text-amber-950">Log in</a> to place a hold on this book.
                </div>
              @endauth
            </div>
          @else
            @auth
              <form method="POST" action="{{ route('books.borrow', $book) }}" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition shadow-sm">
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

