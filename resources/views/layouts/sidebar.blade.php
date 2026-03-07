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

            @php $role = auth()->user()->role; @endphp

            {{-- =========== AGENT SIDEBAR =========== --}}
            @if($role === 'AGENT')

                {{-- WORKSPACE --}}
                <div class="mb-2">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Workspace') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">

                        <a href="{{ route('agent.dashboard') }}"
                           class="sidebar-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">

                            <x-heroicon-o-home class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Dashboard') }}</span>
                        </a>

                        <a href="{{ route('agent.history') }}"
                           class="sidebar-link {{ request()->routeIs('agent.history') ? 'active' : '' }}">

                            <x-heroicon-o-archive-box class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Complaint History') }}</span>
                        </a>

                    </div>
                </div>

                {{-- MONITORING --}}
                <div class="mt-4">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Monitoring') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">
                        <a href="{{ route('agent.sla') }}"
                           class="sidebar-link {{ request()->routeIs('agent.sla') ? 'active' : '' }}">

                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('SLA Monitor') }}</span>
                        </a>
                    </div>
                </div>

                {{-- INBOX --}}
                <div class="mt-4">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Communications') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">
                        <a href="{{ route('notifications.inbox') }}"
                           class="sidebar-link {{ request()->routeIs('notifications.inbox') ? 'active' : '' }}">

                            <x-heroicon-o-bell class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Inbox') }}</span>
                            @php $agentUnread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                            @if($agentUnread > 0)
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full" x-show="!collapsed">
                                    {{ $agentUnread > 9 ? '9+' : $agentUnread }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

            @endif

            {{-- =========== SUPERVISOR SIDEBAR =========== --}}
            @if($role === 'SUPERVISOR')

                {{-- WORKSPACE --}}
                <div class="mb-2">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Workspace') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">

                        <a href="{{ route('supervisor.dashboard') }}"
                           class="sidebar-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">

                            <x-heroicon-o-home class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Dashboard') }}</span>
                        </a>

                        <a href="{{ route('supervisor.history') }}"
                           class="sidebar-link {{ request()->routeIs('supervisor.history') ? 'active' : '' }}">

                            <x-heroicon-o-archive-box class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Complaint History') }}</span>
                        </a>

                    </div>
                </div>

                {{-- MONITORING --}}
                <div class="mt-4">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Monitoring') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">
                        <a href="{{ route('supervisor.sla') }}"
                           class="sidebar-link {{ request()->routeIs('supervisor.sla') ? 'active' : '' }}">

                            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('SLA Monitor') }}</span>
                        </a>
                    </div>
                </div>

                {{-- INBOX --}}
                <div class="mt-4">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Communications') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">
                        <a href="{{ route('notifications.inbox') }}"
                           class="sidebar-link {{ request()->routeIs('notifications.inbox') ? 'active' : '' }}">

                            <x-heroicon-o-bell class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Inbox') }}</span>
                            @php $supUnread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                            @if($supUnread > 0)
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full" x-show="!collapsed">
                                    {{ $supUnread > 9 ? '9+' : $supUnread }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

            @endif

            {{-- =========== USER SIDEBAR =========== --}}
            @if($role === 'USER')

                {{-- WORKSPACE --}}
                <div class="mb-2">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Workspace') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">

                        <a href="{{ route('user.dashboard') }}"
                           class="sidebar-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">

                            <x-heroicon-o-home class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Dashboard') }}</span>
                        </a>

                        <a href="{{ route('user.complaints') }}"
                           class="sidebar-link {{ request()->routeIs('user.complaints') ? 'active' : '' }}">

                            <x-heroicon-o-document-text class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Complaints') }}</span>
                        </a>

                    </div>
                </div>

                {{-- INBOX --}}
                <div class="mt-4">
                    <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                       x-show="!collapsed">
                        {{ __('Communications') }}
                    </p>
                    <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                    <div class="space-y-1">
                        <a href="{{ route('notifications.inbox') }}"
                           class="sidebar-link {{ request()->routeIs('notifications.inbox') ? 'active' : '' }}">

                            <x-heroicon-o-bell class="w-5 h-5" />
                            <span x-show="!collapsed">{{ __('Inbox') }}</span>
                            @php $userUnread = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                            @if($userUnread > 0)
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full" x-show="!collapsed">
                                    {{ $userUnread > 9 ? '9+' : $userUnread }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

            @endif

            {{-- =========== ACCOUNT (shared) =========== --}}
            <div class="mt-4">
                <p class="text-xs uppercase text-gray-400 tracking-wider mb-2 px-4"
                   x-show="!collapsed">
                    {{ __('Account') }}
                </p>
                <hr x-show="collapsed" class="mx-4 my-4 border-t-2 border-gray-300" x-cloak>

                <a href="{{ route('profile.edit') }}"
                   class="sidebar-link {{ request()->routeIs('profile') ? 'active' : '' }}">

                    <x-heroicon-o-user class="w-5 h-5" />
                    <span x-show="!collapsed">{{ __('Profile') }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="sidebar-link text-red-600 hover:bg-red-50 w-full">

                        <x-heroicon-o-arrow-left-on-rectangle class="w-5 h-5" />
                        <span x-show="!collapsed">{{ __('Logout') }}</span>
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
