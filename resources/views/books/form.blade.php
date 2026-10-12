<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
      {{ $book->exists ? __('Edit Book: ') . $book->title : __('Add New Book') }}
    </h2>
  </x-slot>

  <div class="py-8 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow sm:rounded-lg p-6">
      <form method="POST" action="{{ $book->exists ? route('books.update', $book) : route('books.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($book->exists)
          @method('PUT')
        @endif

        <div class="space-y-6">
          <!-- Title -->
          <div>
            <x-input-label for="title" :value="__('Title')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $book->title)" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
          </div>

          <!-- Author -->
          <div>
            <x-input-label for="author_id" :value="__('Author')" />
            <select id="author_id" name="author_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
              <option value="">{{ __('-- Select Author --') }}</option>
              @foreach ($authors as $author)
                <option value="{{ $author->id }}" @selected(old('author_id', $book->author_id) == $author->id)>
                  {{ $author->name }}
                </option>
              @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('author_id')" />
          </div>

          <!-- ISBN -->
          <div>
            <x-input-label for="isbn" :value="__('ISBN (13 characters)')" />
            <x-text-input id="isbn" name="isbn" type="text" maxlength="13" class="mt-1 block w-full font-mono" :value="old('isbn', $book->isbn)" required placeholder="9780000000000" />
            <x-input-error class="mt-2" :messages="$errors->get('isbn')" />
          </div>

          <!-- Published Year -->
          <div>
            <x-input-label for="published_year" :value="__('Published Year')" />
            <x-text-input id="published_year" name="published_year" type="number" min="1000" max="{{ date('Y') + 1 }}" class="mt-1 block w-full" :value="old('published_year', $book->published_year ?? date('Y'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('published_year')" />
          </div>

          <!-- Book Cover Upload -->
          <div>
            <x-input-label for="cover" :value="__('Book Cover Image (Optional, max 2MB)')" />
            @if ($book->cover_path)
              <div class="mt-2 mb-3 flex items-center space-x-4">
                <img src="{{ asset('storage/' . $book->cover_path) }}" alt="{{ $book->title }}" class="w-16 h-24 object-cover rounded border">
                <span class="text-xs text-gray-500">{{ __('Current cover image') }}</span>
              </div>
            @endif
            <input id="cover" name="cover" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
            <x-input-error class="mt-2" :messages="$errors->get('cover')" />
          </div>

          <!-- Is Reference -->
          <div class="flex items-center">
            <input id="is_reference" name="is_reference" type="checkbox" value="1" @checked(old('is_reference', $book->is_reference)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="is_reference" class="ms-2 text-sm text-gray-700">
              {{ __('Reference Book (Cannot be borrowed; library use only)') }}
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('is_reference')" />
          </div>

          <!-- Action buttons -->
          <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
            <a href="{{ $book->exists ? route('books.show', $book) : route('books.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
              {{ __('Cancel') }}
            </a>
            <x-primary-button>
              {{ $book->exists ? __('Save Changes') : __('Create Book') }}
            </x-primary-button>
          </div>
        </div>
      </form>
    </div>
  </div>
</x-app-layout>
