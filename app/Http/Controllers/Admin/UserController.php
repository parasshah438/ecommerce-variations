<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Get DataTable parameters
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';
            $orderColumn = $request->get('order')[0]['column'] ?? 0;
            $orderDir = $request->get('order')[0]['dir'] ?? 'desc';
            
            // Column mapping
            $columns = ['id', 'name', 'city', 'role', 'status', 'created_at', 'actions'];
            $orderBy = $columns[$orderColumn] ?? 'created_at';
            
            // Base query
            $query = User::select([
                'id', 'name', 'email', 'mobile_number', 'role', 'status', 
                'avatar', 'created_at', 'city', 'country'
            ]);
            
            // Search functionality
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('mobile_number', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('country', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%");
                });
            }            
            // Column-specific search
            if ($request->has('columns')) {
                foreach ($request->get('columns') as $index => $column) {
                    if (!empty($column['search']['value']) && isset($columns[$index])) {
                        $columnName = $columns[$index];
                        $searchValue = $column['search']['value'];
                        
                        if ($columnName === 'role' || $columnName === 'status') {
                            $query->where($columnName, $searchValue);
                        } else {
                            $query->where($columnName, 'like', "%{$searchValue}%");
                        }
                    }
                }
            }
            
            // Get total count before pagination
            $totalRecords = User::count();
            $filteredRecords = $query->count();
            
            // Apply ordering and pagination
            $users = $query->orderBy($orderBy, $orderDir)
                          ->skip($start)
                          ->take($length)
                          ->get();
            
            // Format data for DataTable
            $data = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'avatar' => $this->formatAvatarColumn($user),
                    'location' => $this->formatLocationColumn($user),
                    'role' => '<span class="' . $user->role_badge . '">' . ucfirst($user->role) . '</span>',
                    'status' => '<span class="' . $user->status_badge . '">' . ucfirst($user->status) . '</span>',
                    'created_at' => $this->formatDateColumn($user->created_at),
                    'actions' => $this->formatActionsColumn($user),
                    'DT_RowId' => 'row_' . $user->id,
                    'DT_RowClass' => 'user-row'
                ];
            });
            
            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }

        return view('admin.users.index');
    }
    
    /**
     * Format avatar column for DataTable
     */
    private function formatAvatarColumn($user)
    {
        return '<div class="d-flex align-items-center">
            <img src="' . $user->avatar_url . '" alt="Avatar" class="avatar-sm rounded-circle me-2" width="40" height="40">
            <div>
                <div class="fw-bold">' . $user->name . '</div>
                <small class="text-muted">' . $user->email . '</small>
            </div>
        </div>';
    }
    
    /**
     * Format location column for DataTable
     */
    private function formatLocationColumn($user)
    {
        if ($user->city || $user->country) {
            return '<i class="fas fa-map-marker-alt text-muted me-1"></i>' . 
                   ($user->city ? $user->city : '') . 
                   ($user->city && $user->country ? ', ' : '') . 
                   ($user->country ? $user->country : '');
        }
        return '<span class="text-muted">Not specified</span>';
    }
    
    /**
     * Format date column for DataTable
     */
    private function formatDateColumn($date)
    {
        return '<div>
            <div>' . $date->format('M d, Y') . '</div>
            <small class="text-muted">' . $date->format('H:i A') . '</small>
        </div>';
    }
    
    /**
     * Format actions column for DataTable
     */
    private function formatActionsColumn($user)
    {
        return '<div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-info" onclick="viewUser(' . $user->id . ')" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="editUser(' . $user->id . ')" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-outline-danger" onclick="deleteUser(' . $user->id . ', \'' . addslashes($user->name) . '\')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>';
    }
    
    /**
     * Get users statistics
     */
    public function getStatistics()
    {
        try {
            $statistics = [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'managers' => User::where('role', 'manager')->count(),
                'users' => User::where('role', 'user')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $statistics,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Statistics error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage(),
                'error_code' => 'STATS_ERROR'
            ], 500);
        }
    }

    /**
     * Test method to verify controller is accessible
     */
    public function test()
    {
        try {
            // Simple test without database queries initially
            $response = [
                'success' => true,
                'message' => 'UserController is working properly',
                'timestamp' => now()->toDateTimeString(),
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'request_method' => request()->method(),
                    'request_uri' => request()->getRequestUri(),
                    'is_ajax' => request()->ajax()
                ]
            ];

            // Try to get user count safely
            try {
                $response['user_count'] = User::count();
                $response['sample_users'] = User::take(3)->get(['id', 'name', 'email', 'role', 'status']);
            } catch (\Exception $e) {
                $response['database_error'] = $e->getMessage();
                $response['user_count'] = 'N/A';
            }

            $response['routes_available'] = [
                'index' => 'GET /admin/users',
                'statistics' => 'GET /admin/users/statistics',
                'create' => 'GET /admin/users/create',
                'store' => 'POST /admin/users',
            ];

            return response()->json($response)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization, X-CSRF-Token');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode to see trace'
            ], 500);
        }
    }

    /**
     * Debug method to test DataTable response
     */
    public function debug(Request $request)
    {
        try {
            $users = User::select(['id', 'name', 'email', 'role', 'status', 'created_at'])
                         ->take(5)
                         ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Debug data loaded successfully',
                'request_params' => $request->all(),
                'users_count' => User::count(),
                'sample_data' => $users,
                'formatted_sample' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'avatar' => $this->formatAvatarColumn($user),
                        'location' => $this->formatLocationColumn($user),
                        'role' => '<span class="' . $user->role_badge . '">' . ucfirst($user->role) . '</span>',
                        'status' => '<span class="' . $user->status_badge . '">' . ucfirst($user->status) . '</span>',
                        'created_at' => $this->formatDateColumn($user->created_at),
                        'actions' => $this->formatActionsColumn($user),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,user',
            'status' => 'required|in:active,inactive,suspended',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['password'] = Hash::make($data['password']);
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $filename = 'avatars/' . uniqid() . '.' . $avatar->getClientOriginalExtension();
                $avatar->storeAs('public', $filename);
                $data['avatar'] = $filename;
            }

            $user = User::create($data);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load(['posts' => function($query) {
                $query->latest()->take(5);
            }])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'mobile_number' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,user',
            'status' => 'required|in:active,inactive,suspended',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Handle password update
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                $avatar = $request->file('avatar');
                $filename = 'avatars/' . uniqid() . '.' . $avatar->getClientOriginalExtension();
                $avatar->storeAs('public', $filename);
                $data['avatar'] = $filename;
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Delete avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users data for select dropdown
     */
    public function getUsers(Request $request)
    {
        $search = $request->get('search', '');
        $users = User::where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->active()
                    ->limit(10)
                    ->get(['id', 'name', 'email', 'avatar']);

        return response()->json([
            'success' => true,
            'data' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' (' . $user->email . ')',
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar_url
                ];
            })
        ]);
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status'
            ], 422);
        }

        $user->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully'
        ]);
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user IDs'
            ], 422);
        }

        try {
            $users = User::whereIn('id', $request->ids)->get();
            
            // Delete avatars
            foreach ($users as $user) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
            }
            
            User::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' users deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk update user status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'status' => 'required|in:active,inactive,suspended'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided'
            ], 422);
        }

        try {
            User::whereIn('id', $request->ids)
                ->update(['status' => $request->status]);

            $statusText = ucfirst($request->status);
            
            return response()->json([
                'success' => true,
                'message' => count($request->ids) . " users marked as {$statusText} successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update users: ' . $e->getMessage()
            ], 500);
        }
    }
}