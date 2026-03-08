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
        $query = User::where('role', '!=', 'ADMIN'); // Admin doesn't manage other admins

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->input('role')) {
            if (in_array($role, ['USER', 'AGENT', 'SUPERVISOR'])) {
                $query->where('role', $role);
            }
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->input('per_page', 15))
                       ->withQueryString();

        // Metrics
        $totalUsers       = User::where('role', 'USER')->count();
        $totalAgents      = User::where('role', 'AGENT')->count();
        $totalSupervisors = User::where('role', 'SUPERVISOR')->count();

        // Departments for Agent creation
        $departments = Department::orderBy('name')->get();

        return view('admin.dashboard', compact(
            'users',
            'totalUsers',
            'totalAgents',
            'totalSupervisors',
            'departments'
        ));
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['USER', 'AGENT', 'SUPERVISOR'])],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        if ($validated['role'] === 'AGENT' && empty($validated['department_id'])) {
            return back()->withInput()->withErrors(['department_id' => __('Agent requires a department.')]);
        }
        
        if ($validated['role'] !== 'AGENT') {
            $validated['department_id'] = null; // Ensure only agents get departments
        }

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('admin.dashboard')
                         ->with('success', __('User created successfully.'));
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.dashboard')
                             ->with('error', __('You cannot delete yourself.'));
        }

        // Prevent deleting other admins
        if ($user->role === 'ADMIN') {
            return redirect()->route('admin.dashboard')
                             ->with('error', __('You cannot delete an admin account.'));
        }

        $user->delete();

        return redirect()->route('admin.dashboard')
                         ->with('success', __('User deleted successfully.'));
    }
}
