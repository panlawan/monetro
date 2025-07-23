{{-- resources/views/layouts/navigation.blade.php (แก้ไข) --}}

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <i class="fas fa-home mr-1"></i>{{ __('Dashboard') }}
                    </x-nav-link>
                    
                    <!-- Profile Link -->
                    <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                        <i class="fas fa-user-edit mr-1"></i>{{ __('Profile') }}
                    </x-nav-link>

                    <!-- Admin Link (if admin) -->
                    @if(auth()->user()->canAccessAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            <i class="fas fa-shield-alt mr-1"></i>{{ __('Admin Panel') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <!-- User Avatar -->
                            <img src="{{ auth()->user()->getAvatarUrl() }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="rounded-full mr-2" 
                                 style="width: 24px; height: 24px; object-fit: cover;">
                            
                            <div class="text-left">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-400">{{ Auth::user()->getRoleDisplayName() }}</div>
                            </div>

                            <svg class="fill-current h-4 w-4 ml-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400 border-b">
                            {{ __('Manage Account') }}
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fas fa-user-edit mr-2"></i>{{ __('Profile Settings') }}
                        </x-dropdown-link>

                        <!-- Admin Panel (if admin) -->
                        @if(auth()->user()->canAccessAdmin())
                            <x-dropdown-link :href="route('admin.dashboard')">
                                <i class="fas fa-shield-alt mr-2"></i>{{ __('Admin Panel') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Legal Documents -->
                        <button type="button" 
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                                data-bs-toggle="modal" 
                                data-bs-target="#legalModal" 
                                data-type="terms">
                            <i class="fas fa-file-contract mr-2"></i>{{ __('Legal Documents') }}
                        </button>

                        <div class="border-t border-gray-200"></div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <i class="fas fa-home mr-1"></i>{{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                <i class="fas fa-user-edit mr-1"></i>{{ __('Profile') }}
            </x-responsive-nav-link>

            @if(auth()->user()->canAccessAdmin())
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                    <i class="fas fa-shield-alt mr-1"></i>{{ __('Admin Panel') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                <div class="text-xs text-gray-400">{{ Auth::user()->getRoleDisplayName() }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="fas fa-user-edit mr-2"></i>{{ __('Profile Settings') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i>{{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>