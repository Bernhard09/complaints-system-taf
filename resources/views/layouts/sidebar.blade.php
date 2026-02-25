<aside
    :class="collapsed ? 'w-20' : 'w-64'"
    class="bg-white border-r border-gray-200
           transition-all duration-300
           flex flex-col
           h-full"
    :class="mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>

    <div class="flex flex-col h-full">

        {{-- LOGO --}}
        <div class="px-6 py-5 border-b">
            <h2 class="font-bold text-indigo-600 text-lg"
                x-show="!collapsed">
                Complaint System
            </h2>
            <span x-show="collapsed"
                  class="font-bold text-indigo-600">
                CS
            </span>
        </div>

        {{-- NAV --}}
        <nav class="flex-1 px-4 py-6 text-sm space-y-8 overflow-y-auto">

            {{-- WORKSPACE --}}
            <div>
                <p class="text-xs uppercase text-gray-400 tracking-wider mb-2"
                   x-show="!collapsed">
                    Workspace
                </p>

                <div class="space-y-1">

                    <a href="{{ route('agent.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">

                        <x-heroicon-o-home class="w-5 h-5" />
                        <span x-show="!collapsed">Dashboard</span>
                    </a>

                    <a href="{{ route('agent.history') }}"
                       class="sidebar-link {{ request()->routeIs('agent.history') ? 'active' : '' }}">

                        <x-heroicon-o-archive-box class="w-5 h-5" />
                        <span x-show="!collapsed">Complaint History</span>
                    </a>

                </div>
            </div>

            {{-- MONITORING --}}
            <div>
                <p class="text-xs uppercase text-gray-400 tracking-wider mb-2"
                   x-show="!collapsed">
                    Monitoring
                </p>

                <a href="{{ route('agent.sla') }}"
                   class="sidebar-link {{ request()->routeIs('agent.sla') ? 'active' : '' }}">

                    <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                    <span x-show="!collapsed">SLA Monitor</span>
                </a>
            </div>

            {{-- ACCOUNT --}}
            <div>
                <p class="text-xs uppercase text-gray-400 tracking-wider mb-2"
                   x-show="!collapsed">
                    Account
                </p>

                <a href="{{ route('profile.edit') }}"
                   class="sidebar-link {{ request()->routeIs('profile') ? 'active' : '' }}">

                    <x-heroicon-o-user class="w-5 h-5" />
                    <span x-show="!collapsed">Profile</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="sidebar-link text-red-600 hover:bg-red-50 w-full">

                        <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                        <span x-show="!collapsed">Logout</span>
                    </button>
                </form>
            </div>

        </nav>

        {{-- USER --}}
        <div class="border-t px-4 py-4">

            <div class="w-8 h-8 bg-indigo-500 text-white
                        rounded-full flex items-center justify-center text-xs">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>

            <div x-show="!collapsed">
                <p class="text-sm font-medium">
                    {{ auth()->user()->name }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ auth()->user()->role }}
                </p>
            </div>

        </div>

    </div>

</aside>
