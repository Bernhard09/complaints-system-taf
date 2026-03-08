<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10">
        <div class="max-w-screen-2xl mx-auto space-y-6 sm:space-y-8">

            {{-- ================= METRICS ================= --}}
            <div class="flex flex-wrap gap-4">

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-indigo-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">{{ __('TOTAL USERS') }}</p>
                    <p class="text-2xl font-semibold text-indigo-500">
                        {{ $totalUsers }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-blue-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">{{ __('TOTAL AGENTS') }}</p>
                    <p class="text-2xl font-semibold text-blue-500">
                        {{ $totalAgents }}
                    </p>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-amber-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">{{ __('TOTAL SUPERVISORS') }}</p>
                    <p class="text-2xl font-semibold text-amber-500">
                        {{ $totalSupervisors }}
                    </p>
                </div>

            </div>

            {{-- ================= ADD USER FORM ================= --}}
            <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">

                <div class="h-1 bg-indigo-500"></div>

                <div class="p-6 space-y-4">

                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Add New User') }}</h3>

                    <form method="POST" action="{{ route('admin.users.store') }}"
                          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                          x-data="{ role: '{{ old('role', '') }}' }">
                        @csrf

                        {{-- NAME --}}
                        <div>
                            <label class="text-xs text-gray-500 font-medium">{{ __('Name') }}</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="Full name"
                                   class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- EMAIL --}}
                        <div>
                            <label class="text-xs text-gray-500 font-medium">{{ __('Email') }}</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="email@example.com"
                                   class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('email')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PHONE --}}
                        <div>
                            <label class="text-xs text-gray-500 font-medium">{{ __('Phone Number') }}</label>
                            <input type="text"
                                   name="phone_number"
                                   value="{{ old('phone_number') }}"
                                   placeholder="08xxxxxxxxxx"
                                   class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('phone_number')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PASSWORD --}}
                        <div>
                            <label class="text-xs text-gray-500 font-medium">{{ __('Password') }}</label>
                            <input type="password"
                                   name="password"
                                   required
                                   placeholder="Min. 8 characters"
                                   class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('password')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ROLE --}}
                        <div>
                            <label class="text-xs text-gray-500 font-medium">{{ __('Role') }}</label>
                            <select name="role"
                                    x-model="role"
                                    required
                                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('Select role...') }}</option>
                                <option value="USER">{{ __('User') }}</option>
                                <option value="AGENT">{{ __('Agent') }}</option>
                                <option value="SUPERVISOR">{{ __('Supervisor') }}</option>
                            </select>
                            @error('role')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- DEPARTMENT (Only for AGENT) --}}
                        <div x-show="role === 'AGENT'" x-cloak>
                            <label class="text-xs text-gray-500 font-medium">{{ __('Department') }} <span class="text-red-500">*</span></label>
                            <select name="department_id"
                                    :required="role === 'AGENT'"
                                    class="w-full mt-1 border border-gray-200 rounded-lg px-3 py-2 text-sm
                                           focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('Select department...') }}</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- SUBMIT --}}
                        <div class="flex items-end">
                            <button type="submit"
                                    class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium
                                           hover:bg-indigo-700 transition">
                                {{ __('Add User') }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            {{-- ================= USER TABLE ================= --}}
            <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">

                <div class="h-1 bg-indigo-500"></div>

                <div class="p-6 space-y-4">

                    <h3 class="text-lg font-semibold text-gray-800">{{ __('All Users') }}</h3>

                    {{-- FILTER & SEARCH --}}
                    <div class="flex flex-col md:flex-row justify-between gap-4">

                        {{-- ROLE TABS --}}
                        @php
                            $allRoles = ['ALL', 'USER', 'AGENT', 'SUPERVISOR'];
                            $currentRole = request('role', 'ALL');
                        @endphp

                        <div class="flex gap-3 text-sm">
                            @foreach($allRoles as $r)
                                <a href="{{ request()->fullUrlWithQuery([
                                        'role' => $r === 'ALL' ? null : $r,
                                        'page' => null,
                                    ]) }}"
                                   class="px-4 py-2 rounded-lg transition
                                   {{ ($currentRole === $r || ($r === 'ALL' && !request('role')))
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    {{ $r }}
                                </a>
                            @endforeach
                        </div>

                        {{-- SEARCH --}}
                        <form method="GET" class="flex gap-2">
                            @if(request('role'))
                                <input type="hidden" name="role" value="{{ request('role') }}">
                            @endif
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="{{ __('Search name or email...') }}"
                                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-indigo-500 w-64">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm">
                                {{ __('Search') }}
                            </button>
                            @if(request('search'))
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}"
                                   class="px-4 py-2 rounded-lg bg-gray-200 text-sm">
                                    {{ __('Clear') }}
                                </a>
                            @endif
                        </form>

                    </div>

                    {{-- SHOW ENTRIES --}}
                    <div class="flex justify-end text-sm">
                        <form method="GET" class="flex items-center gap-2">
                            <span class="text-gray-500">{{ __('Show') }}</span>
                            <select name="per_page"
                                    onchange="this.form.submit()"
                                    class="border border-gray-200 rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500">
                                @foreach([15, 25, 50, 100] as $size)
                                    <option value="{{ $size }}"
                                        {{ request('per_page', 15) == $size ? 'selected' : '' }}>
                                        {{ $size }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-gray-500">{{ __('entries') }}</span>
                            @foreach(request()->except('per_page') as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                        </form>
                    </div>

                    {{-- TABLE --}}
                    <div class="overflow-x-auto rounded-xl border">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left">ID</th>
                                    <th class="px-6 py-3 text-left">{{ __('Name') }}</th>
                                    <th class="px-6 py-3 text-left">{{ __('Email') }}</th>
                                    <th class="px-6 py-3 text-left">{{ __('Phone') }}</th>
                                    <th class="px-6 py-3 text-left">{{ __('Role') }}</th>
                                    <th class="px-6 py-3 text-left">{{ __('Created') }}</th>
                                    <th class="px-6 py-3 text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                @forelse($users as $u)
                                    @php
                                        $roleColor = match($u->role) {
                                            'USER'       => 'bg-indigo-100 text-indigo-700',
                                            'AGENT'      => 'bg-blue-100 text-blue-700',
                                            'SUPERVISOR' => 'bg-amber-100 text-amber-700',
                                            default      => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp

                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">#{{ $u->id }}</td>
                                        <td class="px-6 py-4 font-medium">{{ $u->name }}</td>
                                        <td class="px-6 py-4">{{ $u->email }}</td>
                                        <td class="px-6 py-4">{{ $u->phone_number ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $roleColor }}">
                                                {{ $u->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $u->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <form method="POST"
                                                  action="{{ route('admin.users.destroy', $u) }}"
                                                  onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-red-500 hover:text-red-700 text-xs font-medium
                                                               px-3 py-1 rounded-lg hover:bg-red-50 transition">
                                                    {{ __('Delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                            {{ __('No users found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div>
                        {{ $users->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>

</x-app-layout>
