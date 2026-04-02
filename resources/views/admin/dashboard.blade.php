<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="bg-gray-100 min-h-screen py-6 sm:py-10">
        <div class="max-w-screen-2xl mx-auto space-y-6 sm:space-y-8">

            {{-- ================= FLASH MESSAGES ================= --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
                     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
                     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif

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

                <div class="bg-white rounded-xl shadow-sm p-4 border-t-4 border-emerald-500 flex-1 min-w-[160px]">
                    <p class="text-xs text-gray-500">{{ __('TOTAL ADMINS') }}</p>
                    <p class="text-2xl font-semibold text-emerald-500">
                        {{ $totalAdmins }}
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
                          x-data="phoneFormatter()"
                          x-init="initPhone()">
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
                            <div class="flex mt-1">
                                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-200 bg-gray-50 text-gray-500 text-sm font-medium">
                                    +62
                                </span>
                                <input type="tel"
                                       x-ref="phoneInput"
                                       x-model="phoneDisplay"
                                       @input="formatPhone()"
                                       @keydown="preventNonNumeric($event)"
                                       @paste="handlePaste($event)"
                                       inputmode="numeric"
                                       placeholder="812 3456 7890"
                                       class="w-full border border-gray-200 rounded-r-lg px-3 py-2 text-sm
                                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <input type="hidden" name="phone_number" :value="fullPhone">
                            </div>
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
                                <option value="ADMIN">{{ __('Admin') }}</option>
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

            {{-- ================= DEPARTMENT MANAGEMENT ================= --}}
            <div class="bg-white rounded-2xl shadow-sm border border-indigo-100 overflow-hidden">

                <div class="h-1 bg-emerald-500"></div>

                <div class="p-6 space-y-4">

                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Department Management') }}</h3>

                    {{-- ADD DEPARTMENT --}}
                    <form method="POST" action="{{ route('admin.departments.store') }}"
                          class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <div class="flex-1">
                            <input type="text"
                                   name="department_name"
                                   value="{{ old('department_name') }}"
                                   required
                                   placeholder="{{ __('New department name...') }}"
                                   class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm
                                          focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            @error('department_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-emerald-600 text-white text-sm font-medium
                                       hover:bg-emerald-700 transition whitespace-nowrap">
                            {{ __('Add Department') }}
                        </button>
                    </form>

                    {{-- DEPARTMENT LIST --}}
                    <div class="overflow-x-auto rounded-xl border">
                        <table class="w-full text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-6 py-3 text-left">{{ __('Department Name') }}</th>
                                    <th class="px-6 py-3 text-center">{{ __('Agents') }}</th>
                                    <th class="px-6 py-3 text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($departments as $dept)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium">{{ $dept->name }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                                {{ $dept->agents_count > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $dept->agents_count }} {{ __('agent(s)') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($dept->agents_count > 0)
                                                <span class="text-gray-400 text-xs font-medium px-3 py-1 rounded-lg bg-gray-50 cursor-not-allowed"
                                                      title="{{ __('Cannot delete: agents are assigned') }}">
                                                    {{ __('Delete') }}
                                                </span>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('admin.departments.destroy', $dept) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to delete department :name?', ['name' => $dept->name]) }}')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-red-500 hover:text-red-700 text-xs font-medium
                                                                   px-3 py-1 rounded-lg hover:bg-red-50 transition">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-400">
                                            {{ __('No departments found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

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
                            $allRoles = ['ALL', 'USER', 'AGENT', 'SUPERVISOR', 'ADMIN'];
                            $currentRole = request('role', 'ALL');
                        @endphp

                        <div class="flex gap-3 text-sm flex-wrap">
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
                                    <th class="px-6 py-3 text-left">{{ __('Department') }}</th>
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
                                            'ADMIN'      => 'bg-emerald-100 text-emerald-700',
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
                                            {{ $u->department?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">
                                            {{ $u->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($u->role === 'ADMIN')
                                                <span class="text-gray-400 text-xs font-medium px-3 py-1 rounded-lg bg-gray-50 cursor-not-allowed"
                                                      title="{{ __('Cannot delete admin') }}">
                                                    {{ __('Protected') }}
                                                </span>
                                            @else
                                                <form method="POST"
                                                      action="{{ route('admin.users.destroy', $u) }}"
                                                      onsubmit="return confirm('{{ __('Are you sure you want to permanently delete this user? This action cannot be undone.') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="text-red-500 hover:text-red-700 text-xs font-medium
                                                                   px-3 py-1 rounded-lg hover:bg-red-50 transition">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">
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

    {{-- ================= PHONE FORMATTER SCRIPT ================= --}}
    <script>
        function phoneFormatter() {
            return {
                phoneDisplay: '{{ old("phone_number") ? preg_replace("/^\+?62/", "", old("phone_number")) : "" }}',
                role: '{{ old("role", "") }}',
                fullPhone: '',

                initPhone() {
                    this.updateFullPhone();
                },

                preventNonNumeric(e) {
                    const allowed = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
                                     'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                                     'Home', 'End'];
                    if (allowed.includes(e.key)) return;
                    if ((e.ctrlKey || e.metaKey) && ['a','c','v','x'].includes(e.key.toLowerCase())) return;
                    if (!/^\d$/.test(e.key)) {
                        e.preventDefault();
                    }
                },

                handlePaste(e) {
                    e.preventDefault();
                    const pasted = (e.clipboardData || window.clipboardData).getData('text');
                    let cleaned = pasted.replace(/\D/g, '');
                    if (cleaned.startsWith('62')) {
                        cleaned = cleaned.substring(2);
                    } else if (cleaned.startsWith('0')) {
                        cleaned = cleaned.substring(1);
                    }
                    this.phoneDisplay = cleaned;
                    this.updateFullPhone();
                },

                formatPhone() {
                    this.phoneDisplay = this.phoneDisplay.replace(/\D/g, '');
                    if (this.phoneDisplay.length > 13) {
                        this.phoneDisplay = this.phoneDisplay.substring(0, 13);
                    }
                    this.updateFullPhone();
                },

                updateFullPhone() {
                    if (this.phoneDisplay && this.phoneDisplay.length > 0) {
                        this.fullPhone = '+62' + this.phoneDisplay;
                    } else {
                        this.fullPhone = '';
                    }
                }
            }
        }
    </script>

</x-app-layout>
