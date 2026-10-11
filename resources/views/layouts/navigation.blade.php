<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">

      <!-- Left: logo + links -->
      <div class="flex">
        <div class="shrink-0 flex items-center">
          <a href="{{ route('books.index') }}">
            <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
          </a>
        </div>

        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
          <x-nav-link :href="route('books.index')" :active="request()->routeIs('books.*')">
            {{ __('Books') }}
          </x-nav-link>

          @auth
            <x-nav-link :href="route('loans.my')" :active="request()->routeIs('loans.my')">
              {{ __('My Loans') }}
            </x-nav-link>
          @endauth

          @can('access-desk')
            <x-nav-link :href="route('desk.index')" :active="request()->routeIs('desk.*')">
              {{ __('Circulation Desk') }}
            </x-nav-link>
          @endcan

          @can('manage-users')
            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.*')">
              {{ __('Admin') }}
            </x-nav-link>
          @endcan
        </div>
      </div>

      <!-- Right: user menu (logged in) or login/register (guest) -->
      <div class="hidden sm:flex sm:items-center sm:ms-6">
        @auth
          <x-dropdown align="right" width="48">
            <x-slot name="trigger">
              <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                <span>{{ Auth::user()->name }}</span>
                <span class="ms-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                  {{ Auth::user()->role }}
                </span>
                <svg class="ms-2 fill-current h-4 w-4" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </x-slot>
            <x-slot name="content">
              <x-dropdown-link :href="route('profile.edit')">
                {{ __('Profile') }}
              </x-dropdown-link>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                  {{ __('Log Out') }}
                </x-dropdown-link>
              </form>
            </x-slot>
          </x-dropdown>
        @endauth

        @guest
          <div class="space-x-4">
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
              {{ __('Log in') }}
            </a>
            <a href="{{ route('register') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
              {{ __('Register') }}
            </a>
          </div>
        @endguest
      </div>

      <!-- Hamburger (small screens) -->
      <div class="-me-2 flex items-center sm:hidden">
        <button @click="open = ! open" class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none">
          <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Responsive menu (small screens) -->
  <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden pb-3 border-t">
    <div class="space-y-1 pt-2 pb-3">
      <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.*')">
        {{ __('Books') }}
      </x-responsive-nav-link>
      @auth
        <x-responsive-nav-link :href="route('loans.my')" :active="request()->routeIs('loans.my')">
          {{ __('My Loans') }}
        </x-responsive-nav-link>
      @endauth
      @can('access-desk')
        <x-responsive-nav-link :href="route('desk.index')" :active="request()->routeIs('desk.*')">
          {{ __('Circulation Desk') }}
        </x-responsive-nav-link>
      @endcan
      @can('manage-users')
        <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.*')">
          {{ __('Admin') }}
        </x-responsive-nav-link>
      @endcan
    </div>

    @auth
      <div class="pt-4 pb-1 border-t border-gray-200">
        <div class="px-4">
          <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }} ({{ Auth::user()->role }})</div>
          <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
        </div>
        <div class="mt-3 space-y-1">
          <x-responsive-nav-link :href="route('profile.edit')">
            {{ __('Profile') }}
          </x-responsive-nav-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-responsive-nav-link :href="route('logout')"
                onclick="event.preventDefault(); this.closest('form').submit();">
              {{ __('Log Out') }}
            </x-responsive-nav-link>
          </form>
        </div>
      </div>
    @else
      <div class="pt-4 pb-1 border-t border-gray-200 space-y-1">
        <x-responsive-nav-link :href="route('login')">
          {{ __('Log in') }}
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('register')">
          {{ __('Register') }}
        </x-responsive-nav-link>
      </div>
    @endauth
  </div>
</nav>
