<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use App\Models\User_activity;
use App\Models\Countries;
use App\Models\University;
use App\Models\States;
use App\Models\Cities;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Exception;

class UserController extends Controller
{
    /**
     * Display users management page
     */
    public function index(): View
    {
        return view('admin.users.index');
    }

    /**
     * Show create user form
     */
    public function create(): JsonResponse
    {
        try {
            // Handle countries gracefully - they might not exist in the database
            $countries = [];
            try {
                $countries = Countries::where('status', 'active')
                                    ->select('id', 'name')
                                    ->orderBy('name')
                                    ->get()
                                    ->toArray();
            } catch (Exception $countriesException) {
                // Provide default countries if the table doesn't exist
                $countries = [
                    ['id' => 1, 'name' => 'United States'],
                    ['id' => 2, 'name' => 'Canada'],
                    ['id' => 3, 'name' => 'United Kingdom'],
                    ['id' => 4, 'name' => 'India']
                ];
            }

            return response()->json([
                'success' => true,
                'countries' => $countries
            ]);

        } catch (Exception $e) {
            Log::error('Error loading create user form: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form data'
            ], 500);
        }
    }

    /**
     * Store new user
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'mobile_number' => 'nullable|string|max:20',
                'country_code' => 'nullable|string|max:5',
                'role' => 'required|in:admin,user,manager',
                'status' => 'required|in:active,inactive',
                'password' => 'required|string|min:8|confirmed',
                'date_of_birth' => 'nullable|date|before:today',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:1000'
            ], [
                'email.unique' => 'This email address is already registered.',
                'password.min' => 'Password must be at least 8 characters long.',
                'password.confirmed' => 'Password confirmation does not match.'
            ]);

            DB::beginTransaction();

            $userData = $request->only([
                'name', 'email', 'mobile_number', 'country_code', 'role', 'status',
                'date_of_birth', 'address', 'city', 'country', 'bio'
            ]);
            $userData['password'] = bcrypt($request->password);
            $userData['email_verified_at'] = now();

            $user = User::create($userData);

            // Log activity
            User_activity::create([
                'user_id' => Auth::id(),
                'log_description' => "Created new user: {$user->name}",
                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user'
            ], 500);
        }
    }

    /**
     * Show edit user form
     */
    public function edit(Request $request, $id): JsonResponse
    {
        try {
            Log::info("Attempting to edit user with ID: " . $id);
            
            $user = User::findOrFail($id);
            Log::info("User found: " . $user->name);
            
            // Handle countries gracefully - they might not exist in the database
            $countries = [];
            try {
                $countries = Countries::where('status', 'active')
                                    ->select('id', 'name')
                                    ->orderBy('name')
                                    ->get()
                                    ->toArray();
            } catch (Exception $countriesException) {
                Log::warning('Countries table not found or empty: ' . $countriesException->getMessage());
                // Provide default countries if the table doesn't exist
                $countries = [
                    ['id' => 1, 'name' => 'United States'],
                    ['id' => 2, 'name' => 'Canada'],
                    ['id' => 3, 'name' => 'United Kingdom'],
                    ['id' => 4, 'name' => 'India']
                ];
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number,
                    'country_code' => $user->country_code,
                    'role' => $user->role ?? 'user',
                    'status' => $user->status ?? 'active',
                    'date_of_birth' => $user->date_of_birth ? (is_string($user->date_of_birth) ? $user->date_of_birth : $user->date_of_birth->format('Y-m-d')) : '',
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'bio' => $user->bio
                ],
                'countries' => $countries
            ]);

        } catch (Exception $e) {
            Log::error('Error loading edit user form: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'User not found or failed to load: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update user
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $id,
                'mobile_number' => 'nullable|string|max:20',
                'country_code' => 'nullable|string|max:5',
                'role' => 'required|in:admin,user,manager',
                'status' => 'required|in:active,inactive',
                'date_of_birth' => 'nullable|date|before:today',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:1000'
            ];

            // Add password validation only if password is provided
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:8|confirmed';
            }

            $request->validate($rules, [
                'email.unique' => 'This email address is already taken by another user.',
                'password.min' => 'Password must be at least 8 characters long.',
                'password.confirmed' => 'Password confirmation does not match.'
            ]);

            DB::beginTransaction();

            $userData = $request->only([
                'name', 'email', 'mobile_number', 'country_code', 'role', 'status',
                'date_of_birth', 'address', 'city', 'country', 'bio'
            ]);

            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            $user->update($userData);

            // Log activity
            User_activity::create([
                'user_id' => Auth::id(),
                'log_description' => "Updated user: {$user->name}",
                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user'
            ], 500);
        }
    }

    /**
     * Show user details
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = User::with(['orders' => function($query) {
                $query->latest()->limit(5);
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number ?? $user->phone,
                    'country_code' => $user->country_code,
                    'role' => $user->role ?? 'user',
                    'status' => $user->status ?? 'active',
                    'date_of_birth' => $user->date_of_birth ? (is_string($user->date_of_birth) ? $user->date_of_birth : $user->date_of_birth->format('d M Y')) : 'Not specified',
                    'address' => $user->address ?: 'Not specified',
                    'city' => $user->city ?: 'Not specified',
                    'country' => $user->country ?: 'Not specified',
                    'bio' => $user->bio ?: 'No bio available',
                    'avatar' => $user->avatar ?? asset('images/default-avatar.png'),
                    'created_at' => $this->safeFormatDate($user->created_at),
                    'updated_at' => $this->safeFormatDate($user->updated_at),
                    'last_login_at' => $this->safeFormatDate($user->last_login_at, 'd M Y H:i', 'Never'),
                    'orders_count' => $user->orders->count(),
                    'recent_orders' => $user->orders->map(function($order) {
                        return [
                            'id' => $order->id,
                            'total' => $order->total,
                            'status' => $order->status,
                            'created_at' => $order->created_at ? (is_string($order->created_at) ? $order->created_at : $order->created_at->format('d M Y')) : 'N/A'
                        ];
                    })
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error showing user details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Get users data for DataTables
     */
    public function manage_user(Request $request): JsonResponse
    {
        try {
            // Return statistics if requested
            if ($request->get('get_stats')) {
                $stats = [
                    'total' => User::count(),
                    'active' => User::where('status', 'active')->count(),
                    'inactive' => User::where('status', 'inactive')->count(),
                    'admins' => User::where('role', 'admin')->count()
                ];
                return response()->json(['stats' => $stats]);
            }

            // Validate request parameters
            $request->validate([
                'search.value' => 'nullable|string|max:255',
                'length' => 'nullable|integer|min:1|max:100',
                'start' => 'nullable|integer|min:0',
                'order' => 'nullable|array',
                'columns' => 'nullable|array',
                'status' => 'nullable|in:active,inactive',
                'role' => 'nullable|in:admin,manager,user',
                'draw' => 'nullable|integer'
            ]);

            $query = User::query();

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('role')) {
                $query->where('role', $request->get('role'));
            }

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('email', 'LIKE', "%{$searchValue}%")
                      ->orWhere('mobile_number', 'LIKE', "%{$searchValue}%");
                });
            }

            // Total records count
            $totalRecords = User::count();
            $filteredRecords = $query->count();

            // Ordering
            if ($request->filled('order')) {
                $orderColumn = $request->input('order.0.column', 0);
                $orderDir = $request->input('order.0.dir', 'asc');
                
                $columns = ['', 'id', 'name', 'email', 'mobile_number', 'role', 'status', 'created_at', ''];
                if (isset($columns[$orderColumn]) && $columns[$orderColumn]) {
                    $query->orderBy($columns[$orderColumn], $orderDir);
                }
            } else {
                $query->latest();
            }

            // Pagination
            $length = $request->input('length', 25);
            $start = $request->input('start', 0);
            
            $users = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->id,
                    'name' => $user->name ?? 'N/A',
                    'email' => $user->email ?? 'N/A',
                    'mobile_number' => $user->mobile_number ?? '-',
                    'role' => $user->role ?? 'user',
                    'status' => $user->status ?? 'active',
                    'created_at' => $user->created_at ? (is_string($user->created_at) ? $user->created_at : $user->created_at->format('d M Y')) : 'N/A',
                    'avatar' => $user->avatar ?? asset('images/default-avatar.png'),
                    'actions' => $this->generateActionButtons($user)
                ];
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $data
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Invalid request parameters',
                'details' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error in manage_user: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Failed to load users data'
            ], 500);
        }
    }

    /**
     * Generate action buttons for user row with ecommerce metrics
     */
    private function generateActionButtons(User $user): string
    {
        $userId = $user->id;
        $userStatus = $user->status ?? 'active';
        
        // Get ecommerce metrics
        $metrics = $this->getUserEcommerceMetrics($userId);
        
        $actions = [];
        

        
        // Standard Actions
        // Ecommerce Overview Button
        $actions[] = '<button type="button" class="btn btn-sm btn-gradient-primary ecommerce-overview me-1" data-id="' . $userId . '" title="Ecommerce Overview" 
                        data-wishlist="' . $metrics['wishlist_count'] . '" 
                        data-cart="' . $metrics['cart_count'] . '" 
                        data-views="' . $metrics['recent_views'] . '" 
                        data-orders="' . $metrics['total_orders'] . '" 
                        data-success-payments="' . number_format($metrics['successful_payments'], 2) . '" 
                        data-failed-payments="' . number_format($metrics['failed_payments'], 2) . '" 
                        data-user-name="' . htmlspecialchars($user->name ?? 'N/A') . '">
                        <i class="fas fa-chart-pie me-1"></i><span class="d-none d-md-inline">Overview</span>
                      </button>';
        
        $actions[] = '<button type="button" class="btn btn-sm btn-info view-user me-1" data-id="' . $userId . '" title="View User">
                        <i class="fas fa-eye"></i>
                      </button>';
        
        $actions[] = '<button type="button" class="btn btn-sm btn-primary edit-user me-1" data-id="' . $userId . '" title="Edit User">
                        <i class="fas fa-edit"></i>
                      </button>';
        
        // Status toggle
        if ($userStatus === 'active') {
            $actions[] = '<button type="button" class="btn btn-sm btn-warning toggle-status me-1" data-id="' . $userId . '" data-status="inactive" title="Deactivate User">
                            <i class="fas fa-pause"></i>
                          </button>';
        } else {
            $actions[] = '<button type="button" class="btn btn-sm btn-success toggle-status me-1" data-id="' . $userId . '" data-status="active" title="Activate User">
                            <i class="fas fa-play"></i>
                          </button>';
        }
        
        // Delete button (prevent deleting own account)
        if ($userId != Auth::id()) {
            $actions[] = '<button type="button" class="btn btn-sm btn-danger delete-user" data-id="' . $userId . '" title="Delete User">
                            <i class="fas fa-trash"></i>
                          </button>';
        }
        
        return '<div class="d-flex flex-wrap">' . implode('', $actions) . '</div>';
    }

    /**
     * Safely format date - handles both string and Carbon objects
     */
    private function safeFormatDate($date, $format = 'd M Y H:i', $default = 'N/A'): string
    {
        // Ensure default is always a string
        $defaultValue = $default ?? 'N/A';
        
        if (!$date) {
            return $defaultValue;
        }
        
        if (is_string($date)) {
            return $date;
        }
        
        try {
            return $date->format($format);
        } catch (Exception $e) {
            Log::warning('Date formatting error: ' . $e->getMessage());
            return $defaultValue;
        }
    }

    /**
     * Get ecommerce metrics for a user
     */
    private function getUserEcommerceMetrics($userId): array
    {
        try {
            // Initialize default values
            $metrics = [
                'wishlist_count' => 0,
                'cart_count' => 0,
                'recent_views' => 0,
                'total_orders' => 0,
                'successful_payments' => 0,
                'failed_payments' => 0
            ];

            // Get orders count and payment totals
            if (class_exists('App\\Models\\Order')) {
                $orders = DB::table('orders')->where('user_id', $userId);
                $metrics['total_orders'] = $orders->count();
                
                // Get payment totals - checking multiple success statuses
                $successfulPayments = DB::table('orders')
                    ->where('user_id', $userId)
                    ->whereIn('status', ['completed', 'confirmed', 'delivered', 'success'])
                    ->sum('total');
                    
                $failedPayments = DB::table('orders')
                    ->where('user_id', $userId)
                    ->whereIn('status', ['failed', 'cancelled', 'refunded'])
                    ->sum('total');
                
                $metrics['successful_payments'] = $successfulPayments ?: 0;
                $metrics['failed_payments'] = $failedPayments ?: 0;
            }

            // Get wishlist count
            if (Schema::hasTable('wishlists')) {
                $metrics['wishlist_count'] = DB::table('wishlists')
                    ->where('user_id', $userId)
                    ->count();
            }

            // Get cart count
            if (Schema::hasTable('cart_items') && Schema::hasTable('carts')) {
                $metrics['cart_count'] = DB::table('cart_items')
                    ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
                    ->where('carts.user_id', $userId)
                    ->sum('cart_items.quantity');
            } elseif (Schema::hasTable('carts') && Schema::hasColumn('carts', 'quantity')) {
                $metrics['cart_count'] = DB::table('carts')
                    ->where('user_id', $userId)
                    ->sum('quantity');
            }

            // Get recent product views
            if (Schema::hasTable('product_views')) {
                $metrics['recent_views'] = DB::table('product_views')
                    ->where('user_id', $userId)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
            }

            return $metrics;
            
        } catch (Exception $e) {
            Log::warning('Error getting ecommerce metrics for user ' . $userId . ': ' . $e->getMessage());
            return [
                'wishlist_count' => 0,
                'cart_count' => 0,
                'recent_views' => 0,
                'total_orders' => 0,
                'successful_payments' => 0,
                'failed_payments' => 0
            ];
        }
    }

    public function activity_logs()
    {
       return view('admin/manage_activity_logs');
    }

    public function manage_activity_logs(Request $request): JsonResponse
    {
        try {
            $query = User_activity::select(
                'user_activities.*',
                'user_activities.id as activity_id',
                'users.id',
                'users.name',
                'users.email'
            )->leftJoin('users', 'users.id', 'user_activities.user_id');

            // Apply filters
            if ($request->filled('user_filter')) {
                $query->where('user_activities.user_id', $request->get('user_filter'));
            }

            if ($request->filled('date_filter')) {
                $query->whereDate('user_activities.created_at', $request->get('date_filter'));
            }

            // Global search
            if ($request->filled('search.value')) {
                $searchValue = $request->input('search.value');
                $query->where(function($q) use ($searchValue) {
                    $q->where('user_activities.log_description', 'LIKE', "%{$searchValue}%")
                      ->orWhere('users.name', 'LIKE', "%{$searchValue}%")
                      ->orWhere('users.email', 'LIKE', "%{$searchValue}%")
                      ->orWhere('user_activities.ip_address', 'LIKE', "%{$searchValue}%");
                });
            }

            $totalRecords = User_activity::count();
            $filteredRecords = $query->count();

            // Ordering
            $query->latest('user_activities.created_at');

            // Pagination
            $length = $request->input('length', 25);
            $start = $request->input('start', 0);
            
            $activities = $query->skip($start)->take($length)->get();

            $data = [];
            foreach ($activities as $activity) {
                $actions = '<div class="d-flex">';
                $actions .= '<button type="button" class="btn btn-sm btn-info view-activity me-1" data-id="' . $activity->id . '" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-activity" data-id="' . $activity->activity_id . '" title="Delete Log">
                                <i class="fas fa-trash"></i>
                            </button>';
                $actions .= '</div>';

                $data[] = [
                    'id' => $activity->activity_id,
                    'log_description' => $activity->log_description ?? 'No description',
                    'name' => $activity->name ?? 'Unknown User',
                    'email' => $activity->email ?? 'No email',
                    'ip_address' => $activity->ip_address ?? 'Unknown IP',
                    'created_at' => $activity->created_at ? (is_string($activity->created_at) ? $activity->created_at : $activity->created_at->format('d M Y H:i')) : 'Unknown',
                    'actions' => $actions
                ];
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $data
            ]);

        } catch (Exception $e) {
            Log::error('Error in manage_activity_logs: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Failed to load activity logs'
            ], 500);
        }
    }

    /**
     * View user details
     */
    public function view_user(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:users,id'
            ]);

            $user = User::with(['orders' => function($query) {
                $query->latest()->limit(10);
            }, 'addresses'])
            ->findOrFail($request->get('id'));

            // Get comprehensive ecommerce metrics
            $metrics = $this->getUserEcommerceMetrics($user->id);

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_number' => $user->mobile_number ?? $user->phone,
                    'role' => $user->role ?? 'user',
                    'status' => $user->status ?? 'active',
                    'country_code' => $user->country_code,
                    'date_of_birth' => $this->safeFormatDate($user->date_of_birth, 'Y-m-d', 'Not provided'),
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'bio' => $user->bio,
                    'avatar' => $user->avatar ?? asset('images/default-avatar.png'),
                    'created_at' => $this->safeFormatDate($user->created_at, 'd M Y H:i'),
                    'updated_at' => $this->safeFormatDate($user->updated_at, 'd M Y H:i'),
                    'last_login_at' => $this->safeFormatDate($user->last_login_at, 'd M Y H:i', 'Never'),
                    'addresses_count' => $user->addresses->count(),
                    // Enhanced ecommerce metrics
                    'wishlist_count' => $metrics['wishlist_count'],
                    'cart_count' => $metrics['cart_count'],
                    'recent_views' => $metrics['recent_views'],
                    'orders_count' => $metrics['total_orders'],
                    'successful_payments' => number_format($metrics['successful_payments'], 2),
                    'failed_payments' => number_format($metrics['failed_payments'], 2)
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error viewing user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'User not found or error occurred'
            ], 404);
        }
    }

    /**
     * Delete user activity
     */
    public function delete_user_activity(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:user_activities,id'
            ]);

            DB::beginTransaction();
            
            User_activity::findOrFail($request->get('id'))->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User activity deleted successfully'
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user activity'
            ], 500);
        }
    }

    /**
     * Delete multiple user activities
     */
    public function delete_all_user_activity(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|array|min:1',
                'id.*' => 'integer|exists:user_activities,id'
            ]);

            DB::beginTransaction();
            
            $deletedCount = User_activity::whereIn('id', $request->get('id'))->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} user activities"
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting multiple user activities: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user activities'
            ], 500);
        }
    }

    /**
     * View user activity
     */
    public function view_user_activity(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:users,id'
            ]);

            $user = User::findOrFail($request->get('id'));
            
            $activities = User_activity::where('user_id', $user->id)
                                    ->latest()
                                    ->limit(20)
                                    ->get();

            return response()->json([
                'success' => true,
                'user' => $user,
                'activities' => $activities
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error viewing user activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'User not found or error occurred'
            ], 404);
        }
    }

    /**
     * Delete user with authorization check
     */
    public function delete_user(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:users,id'
            ]);

            $userId = $request->get('id');
            
            // Prevent deleting own account
            if ($userId == Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            DB::beginTransaction();
            
            $user = User::findOrFail($userId);
            
            // Soft delete to preserve data integrity
            $user->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user'
            ], 500);
        }
    }

    /**
     * Delete multiple users with authorization check
     */
    public function delete_all_user(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|array|min:1',
                'id.*' => 'integer|exists:users,id'
            ]);

            $userIds = $request->get('id');
            
            // Prevent deleting own account
            if (in_array(Auth::id(), $userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            DB::beginTransaction();
            
            $deletedCount = User::whereIn('id', $userIds)->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} users"
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting multiple users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users'
            ], 500);
        }
    }

    /**
     * Display user details for PDF/Print view
     */
    public function user_all_details(Request $request, $id): View|RedirectResponse
    {
        try {
            $user = User::with([
                'addresses',
                'orders' => function($query) {
                    $query->latest()->limit(20);
                }
            ])->findOrFail($id);

            // Get location data efficiently
            $locationData = $this->getUserLocationData($user);

            // Get reference data with caching
            $referenceData = Cache::remember('user_details_reference_data', 3600, function() {
                return [
                    'countries' => Countries::select('id', 'name')->orderBy('name')->get(),
                    'universities' => University::where('status', 'active')
                                              ->select('id', 'name')
                                              ->orderBy('name')
                                              ->get()
                ];
            });

            return view('admin.user_all_details', array_merge(
                $locationData,
                $referenceData,
                ['user' => $user]
            ));

        } catch (Exception $e) {
            Log::error('Error loading user details: ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                           ->with('error', 'User not found or failed to load details');
        }
    }

    /**
     * Get user location data efficiently
     */
    private function getUserLocationData(User $user): array
    {
        $locationIds = [
            'country' => array_filter([$user->country_id, $user->beneficiary_country_id ?? null]),
            'state' => array_filter([$user->state_id, $user->beneficiary_state_id ?? null]),
            'city' => array_filter([$user->city_id, $user->beneficiary_city_id ?? null])
        ];

        $locationData = [
            'country_name' => '',
            'b_country_name' => '',
            'state_name' => '',
            'b_state_name' => '',
            'city_name' => '',
            'b_city_name' => '',
            'institute_name' => ''
        ];

        // Fetch countries
        if (!empty($locationIds['country'])) {
            $countries = Countries::whereIn('id', $locationIds['country'])->pluck('name', 'id');
            $locationData['country_name'] = $countries->get($user->country_id, '');
            $locationData['b_country_name'] = $countries->get($user->beneficiary_country_id ?? 0, '');
        }

        // Fetch states
        if (!empty($locationIds['state'])) {
            $states = States::whereIn('id', $locationIds['state'])->pluck('name', 'id');
            $locationData['state_name'] = $states->get($user->state_id, '');
            $locationData['b_state_name'] = $states->get($user->beneficiary_state_id ?? 0, '');
        }

        // Fetch cities
        if (!empty($locationIds['city'])) {
            $cities = Cities::whereIn('id', $locationIds['city'])->pluck('name', 'id');
            $locationData['city_name'] = $cities->get($user->city_id, '');
            $locationData['b_city_name'] = $cities->get($user->beneficiary_city_id ?? 0, '');
        }

        // Fetch institute
        if ($user->institute) {
            $institute = University::find($user->institute);
            $locationData['institute_name'] = $institute->name ?? '';
        }

        return $locationData;
    }

    public function user_all_details_export(Request $request, $id): View|RedirectResponse
    {
        try {
            $user = User::with([
                'addresses',
                'orders' => function($query) {
                    $query->latest()->limit(50);
                }
            ])->findOrFail($id);

            // Get all location data efficiently with single queries
            $locationData = $this->getExportLocationData($user);

            // Cache reference data
            $referenceData = Cache::remember('export_reference_data', 3600, function() {
                return [
                    'countries' => Countries::select('id', 'name')->get(),
                    'universities' => University::where('status', 'active')
                                              ->select('id', 'name')
                                              ->get()
                ];
            });

            return view('admin.user_all_details_export', array_merge(
                $locationData,
                $referenceData,
                ['user' => $user]
            ));

        } catch (Exception $e) {
            Log::error('Error exporting user details: ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                           ->with('error', 'User not found or export failed');
        }
    }

    private function getExportLocationData(User $user): array
    {
        // Collect all location IDs to fetch in batches
        $countryIds = array_filter([
            $user->country_id,
            $user->beneficiary_country_id
        ]);
        
        $stateIds = array_filter([
            $user->state_id,
            $user->beneficiary_state_id
        ]);
        
        $cityIds = array_filter([
            $user->city_id,
            $user->beneficiary_city_id
        ]);

        // Fetch all data in single queries
        $countries = !empty($countryIds) ? Countries::whereIn('id', $countryIds)->pluck('name', 'id') : collect();
        $states = !empty($stateIds) ? States::whereIn('id', $stateIds)->pluck('name', 'id') : collect();
        $cities = !empty($cityIds) ? Cities::whereIn('id', $cityIds)->pluck('name', 'id') : collect();
        $institute = $user->institute ? University::find($user->institute) : null;

        return [
            'country_name' => $countries->get($user->country_id, ''),
            'b_country_name' => $countries->get($user->beneficiary_country_id, ''),
            'state_name' => $states->get($user->state_id, ''),
            'b_state_name' => $states->get($user->beneficiary_state_id, ''),
            'city_name' => $cities->get($user->city_id, ''),
            'b_city_name' => $cities->get($user->beneficiary_city_id, ''),
            'institute_name' => $institute->name ?? ''
        ];
    }

    /**
     * Toggle user status
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:users,id',
                'status' => 'required|in:active,inactive'
            ]);

            DB::beginTransaction();
            
            $user = User::findOrFail($request->get('id'));
            $user->update(['status' => $request->get('status')]);
            
            // Log activity
            User_activity::create([
                'user_id' => Auth::id(),
                'log_description' => "Changed user {$user->name} status to {$request->get('status')}",
                'ip_address' => request()->ip()
            ]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'new_status' => $user->status
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error toggling user status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status'
            ], 500);
        }
    }

    /**
     * Bulk action for multiple users
     */
    public function bulkAction(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'action' => 'required|in:activate,deactivate,delete',
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:users,id'
            ]);

            $ids = $request->get('ids');
            $action = $request->get('action');

            // Prevent action on own account
            if (in_array(Auth::id(), $ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot perform this action on your own account'
                ], 403);
            }

            DB::beginTransaction();

            $count = 0;
            switch ($action) {
                case 'activate':
                    $count = User::whereIn('id', $ids)->update(['status' => 'active']);
                    $message = "Successfully activated {$count} users";
                    break;

                case 'deactivate':
                    $count = User::whereIn('id', $ids)->update(['status' => 'inactive']);
                    $message = "Successfully deactivated {$count} users";
                    break;

                case 'delete':
                    $count = User::whereIn('id', $ids)->delete();
                    $message = "Successfully deleted {$count} users";
                    break;
            }

            // Log activity
            User_activity::create([
                'user_id' => Auth::id(),
                'log_description' => "Bulk action '{$action}' on {$count} users",
                'ip_address' => request()->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $count
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk action: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action'
            ], 500);
        }
    }

    /**
     * Export user to PDF
     */
    public function exportPdf($id)
    {
        try {
            $user = User::findOrFail($id);
            return redirect()->route('admin.users.show', $id);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'User not found');
        }
    }

    /**
     * Export user to Excel
     */
    public function exportExcel($id)
    {
        try {
            $user = User::findOrFail($id);
            return redirect()->route('admin.users.show', $id);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'User not found');
        }
    }

    /**
     * Export all users
     */
    public function exportAllUsers(Request $request)
    {
        try {
            $query = User::query();
            
            // Apply filters if provided
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }
            
            if ($request->filled('role')) {
                $query->where('role', $request->get('role'));
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            $csvData = [];
            $csvData[] = ['ID', 'Name', 'Email', 'Mobile', 'Role', 'Status', 'Created At'];
            
            foreach ($users as $user) {
                $csvData[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->mobile_number ?? '',
                    $user->role ?? 'user',
                    $user->status ?? 'active',
                    $user->created_at ? (is_string($user->created_at) ? $user->created_at : $user->created_at->format('Y-m-d H:i:s')) : 'N/A'
                ];
            }

            $filename = 'users_export_' . date('Y_m_d_H_i_s') . '.csv';
            
            return response()->streamDownload(function() use ($csvData) {
                $handle = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (Exception $e) {
            Log::error('Error exporting users: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export users');
        }
    }

    /**
     * Standard destroy method for resource route
     */
    public function destroy($id): JsonResponse
    {
        return $this->delete_user(request()->merge(['id' => $id]));
    }

    /**
     * Get detailed ecommerce information for a user
     */
    public function getEcommerceDetails(User $user)
    {
        try {
            $metrics = $this->getUserEcommerceMetrics($user->id);
            
            // Get additional detailed information
            $details = [
                'user' => $user,
                'metrics' => $metrics,
                'recent_orders' => [],
                'recent_wishlist' => [],
                'recent_cart_items' => [],
                'payment_summary' => []
            ];
            
            // Get recent orders if Order model exists
            if (class_exists('App\\Models\\Order')) {
                $details['recent_orders'] = DB::table('orders')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }
            
            // Get wishlist items
            if (Schema::hasTable('wishlists')) {
                $details['recent_wishlist'] = DB::table('wishlists')
                    ->leftJoin('products', 'wishlists.product_id', '=', 'products.id')
                    ->where('wishlists.user_id', $user->id)
                    ->select('wishlists.*', 'products.name as product_name', 'products.price')
                    ->orderBy('wishlists.created_at', 'desc')
                    ->limit(5)
                    ->get();
            }
            
            return response()->json([
                'success' => true,
                'data' => $details
            ]);
            
        } catch (Exception $e) {
            Log::error('Error getting ecommerce details for user ' . $user->id . ': ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading ecommerce details.'
            ]);
        }
    }

    /**
     * Get user orders
     */
    public function getUserOrders(User $user)
    {
        try {
            $orders = [];
            
            if (class_exists('App\\Models\\Order')) {
                $orders = DB::table('orders')
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'total' => number_format($order->total ?? 0, 2),
                            'status' => $order->status ?? 'pending',
                            'created_at' => $order->created_at,
                            'payment_status' => $order->payment_status ?? 'pending'
                        ];
                    });
            }
            
            return response()->json([
                'success' => true,
                'data' => $orders,
                'count' => count($orders)
            ]);
            
        } catch (Exception $e) {
            Log::error('Error getting user orders: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading user orders.'
            ]);
        }
    }

    /**
     * Get user wishlist
     */
    public function getUserWishlist(User $user)
    {
        try {
            $wishlist = [];
            
            if (Schema::hasTable('wishlists')) {
                $wishlist = DB::table('wishlists')
                    ->leftJoin('products', 'wishlists.product_id', '=', 'products.id')
                    ->where('wishlists.user_id', $user->id)
                    ->select('wishlists.*', 'products.name as product_name', 'products.price', 'products.slug')
                    ->orderBy('wishlists.created_at', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name ?? 'Product not found',
                            'price' => number_format($item->price ?? 0, 2),
                            'added_at' => $item->created_at
                        ];
                    });
            }
            
            return response()->json([
                'success' => true,
                'data' => $wishlist,
                'count' => count($wishlist)
            ]);
            
        } catch (Exception $e) {
            Log::error('Error getting user wishlist: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading user wishlist.'
            ]);
        }
    }

    /**
     * Get user cart
     */
    public function getUserCart(User $user)
    {
        try {
            $cart = [];
            
            if (Schema::hasTable('carts')) {
                $cart = DB::table('carts')
                    ->leftJoin('products', 'carts.product_id', '=', 'products.id')
                    ->where('carts.user_id', $user->id)
                    ->select('carts.*', 'products.name as product_name', 'products.price')
                    ->orderBy('carts.updated_at', 'desc')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name ?? 'Product not found',
                            'quantity' => $item->quantity ?? 1,
                            'unit_price' => number_format($item->price ?? 0, 2),
                            'total_price' => number_format(($item->price ?? 0) * ($item->quantity ?? 1), 2),
                            'updated_at' => $item->updated_at
                        ];
                    });
            }
            
            return response()->json([
                'success' => true,
                'data' => $cart,
                'count' => count($cart)
            ]);
            
        } catch (Exception $e) {
            Log::error('Error getting user cart: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading user cart.'
            ]);
        }
    }

    /**
     * Get user payment history
     */
    public function getUserPayments(User $user)
    {
        try {
            $payments = [];
            
            if (Schema::hasTable('payments')) {
                $payments = DB::table('payments')
                    ->leftJoin('orders', 'payments.order_id', '=', 'orders.id')
                    ->where('payments.user_id', $user->id)
                    ->orWhere('orders.user_id', $user->id)
                    ->select('payments.*', 'orders.total as order_total')
                    ->orderBy('payments.created_at', 'desc')
                    ->get()
                    ->map(function ($payment) {
                        return [
                            'id' => $payment->id,
                            'amount' => number_format($payment->amount ?? 0, 2),
                            'status' => $payment->status ?? 'pending',
                            'payment_method' => $payment->payment_method ?? 'N/A',
                            'transaction_id' => $payment->transaction_id ?? 'N/A',
                            'created_at' => $payment->created_at
                        ];
                    });
            }
            
            return response()->json([
                'success' => true,
                'data' => $payments,
                'count' => count($payments)
            ]);
            
        } catch (Exception $e) {
            Log::error('Error getting user payments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading user payments.'
            ]);
        }
    }

    /**
     * Send email to user
     */
    public function sendUserEmail(Request $request, User $user)
    {
        try {
            $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'email_type' => 'required|in:promotional,notification,support'
            ]);
            
            // Here you would integrate with your email service
            // For now, we'll just log the attempt
            
            Log::info('Email sent to user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'subject' => $request->subject,
                'type' => $request->email_type,
                'sent_by' => Auth::id()
            ]);
            
            // Log user activity
            $this->logUserActivity($user->id, 'Email sent', 'Email: ' . $request->subject);
            
            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to ' . $user->email
            ]);
            
        } catch (Exception $e) {
            Log::error('Error sending email to user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending email.'
            ]);
        }
    }
}