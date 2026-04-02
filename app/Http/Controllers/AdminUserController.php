<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Admin dashboard – user management table.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            if (in_array($role, ['USER', 'AGENT', 'SUPERVISOR', 'ADMIN'])) {
                $query->where('role', $role);
            }
        }

        $users = $query->with('department')
                       ->orderBy('created_at', 'desc')
                       ->paginate($request->input('per_page', 15))
                       ->withQueryString();

        // Metrics
        $totalUsers       = User::where('role', 'USER')->count();
        $totalAgents      = User::where('role', 'AGENT')->count();
        $totalSupervisors = User::where('role', 'SUPERVISOR')->count();
        $totalAdmins      = User::where('role', 'ADMIN')->count();

        // Departments for Agent creation & management
        $departments = Department::withCount('agents')->orderBy('name')->get();

        return view('admin.dashboard', compact(
            'users',
            'totalUsers',
            'totalAgents',
            'totalSupervisors',
            'totalAdmins',
            'departments'
        ));
    }

    /**
     * Normalize phone number to +62 format.
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '+62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '62') && !str_starts_with($phone, '+62')) {
            $phone = '+' . $phone;
        }

        if (!str_starts_with($phone, '+')) {
            $phone = '+62' . $phone;
        }

        return $phone;
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20', 'regex:/^[\d+\- ]+$/'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['USER', 'AGENT', 'SUPERVISOR', 'ADMIN'])],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        if ($validated['role'] === 'AGENT' && empty($validated['department_id'])) {
            return back()->withInput()->withErrors(['department_id' => __('Agent requires a department.')]);
        }

        if ($validated['role'] !== 'AGENT') {
            $validated['department_id'] = null;
        }

        $validated['phone_number'] = $this->normalizePhone($validated['phone_number'] ?? null);
        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('admin.dashboard')
                         ->with('success', __('User created successfully.'));
    }

    /**
     * Permanently delete a user (forceDelete to bypass SoftDeletes).
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.dashboard')
                             ->with('error', __('You cannot delete yourself.'));
        }

        if ($user->role === 'ADMIN') {
            return redirect()->route('admin.dashboard')
                             ->with('error', __('You cannot delete an admin account.'));
        }

        $user->forceDelete();

        return redirect()->route('admin.dashboard')
                         ->with('success', __('User deleted permanently.'));
    }

    // ─── DEPARTMENT MANAGEMENT ───────────────────────────────

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:255', 'unique:departments,name'],
        ]);

        Department::create(['name' => $validated['department_name']]);

        return redirect()->route('admin.dashboard')
                         ->with('success', __('Department created successfully.'));
    }

    public function destroyDepartment(Department $department)
    {
        $agentCount = User::where('department_id', $department->id)
                          ->where('role', 'AGENT')
                          ->count();

        if ($agentCount > 0) {
            return redirect()->route('admin.dashboard')
                             ->with('error', __('Cannot delete department ":name" because :count agent(s) are still assigned to it.', [
                                 'name'  => $department->name,
                                 'count' => $agentCount,
                             ]));
        }

        $department->delete();

        return redirect()->route('admin.dashboard')
                         ->with('success', __('Department deleted successfully.'));
    }
}
