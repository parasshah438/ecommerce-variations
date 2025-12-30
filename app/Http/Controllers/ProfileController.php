<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use App\Helpers\ImageOptimizer;

class ProfileController extends Controller
{
    /**
     * Show the manage profile form
     */
    public function manage()
    {
        $user = Auth::user();
        
        // Get user statistics
        $stats = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'completed_orders' => $user->orders()->where('status', 'delivered')->count(),
            'wishlist_items' => $user->wishlist()->count(),
            'saved_addresses' => $user->addresses()->count(),
            'account_age' => $user->created_at->diffForHumans(),
            'last_order' => $user->orders()->latest()->first()?->created_at?->diffForHumans(),
        ];
        
        return view('profile.manage', compact('user', 'stats'));
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:20|unique:users,mobile,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'bio' => 'nullable|string|max:500',
        ]);

        try {
            $user->update($validatedData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'user' => $user->fresh()
                ]);
            }

            return redirect()->route('profile.manage')->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating your profile.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating your profile.');
        }
    }

    /**
     * Show the change password form
     */
    public function password()
    {
        $user = Auth::user();
        
        // Check if user has social logins
        $socialProviders = [];
        if (method_exists($user, 'socialProviders')) {
            $socialProviders = $user->socialProviders()->pluck('provider')->toArray();
        }
        
        return view('profile.password', compact('user', 'socialProviders'));
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $rules = [
            'new_password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
        ];

        // Only require current password if user has one (not social login only)
        if ($user->password) {
            $rules['current_password'] = 'required|string';
        }

        $validatedData = $request->validate($rules);

        try {
            // Verify current password if user has one
            if ($user->password && !Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                    'errors' => ['current_password' => ['Current password is incorrect.']]
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validatedData['new_password'])
            ]);

            // Log password change
            \Log::info('Password changed for user: ' . $user->id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully!'
                ]);
            }

            return redirect()->route('profile.password')->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Password update error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating your password.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while updating your password.');
        }
    }

    /**
     * Delete user account permanently
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|string|in:DELETE'
        ]);

        $user = Auth::user();
        
        try {
            DB::beginTransaction();

            // Cancel pending orders
            $user->orders()->whereIn('status', ['pending', 'confirmed'])->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'notes' => 'Account deleted by user'
            ]);

            // Clear personal data
            $user->wishlist()->delete();
            $user->addresses()->delete();
            $user->cart()?->items()?->delete();
            $user->cart()?->delete();
            
            // Clear recent views if exists
            if (class_exists('\App\Models\RecentView')) {
                \App\Models\RecentView::where('user_id', $user->id)->delete();
            }
            
            // Delete avatar if exists
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }

            // Anonymize completed orders instead of deleting them
            $user->orders()->whereIn('status', ['delivered', 'returned'])->update([
                'notes' => 'User account deleted'
            ]);

            // Delete user account
            $userId = $user->id;
            $user->delete();

            DB::commit();

            // Log account deletion
            \Log::info('User account deleted: ' . $userId);

            Auth::logout();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your account has been permanently deleted.',
                    'redirect_url' => route('welcome')
                ]);
            }

            return redirect()->route('welcome')->with('success', 'Your account has been permanently deleted.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Account deletion error: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting your account. Please try again.'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while deleting your account.');
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048' // 2MB max
        ]);

        $user = Auth::user();

        try {
            $file = $request->file('avatar');
            
            // Optimize and store the avatar
            $result = ImageOptimizer::optimizeUploadedImage(
                $file,
                'avatars/' . $user->id,
                [
                    'quality' => 90,
                    'maxWidth' => 400,
                    'maxHeight' => 400,
                    'generateWebP' => true,
                    'generateThumbnails' => true,
                    'thumbnailSizes' => [150, 300]
                ]
            );

            if (!$result || !isset($result['optimized'])) {
                throw new \Exception('Avatar optimization failed');
            }

            // Delete old avatar if exists
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
                
                // Also delete thumbnails
                $oldPath = dirname($user->avatar);
                Storage::deleteDirectory('public/' . $oldPath . '/thumbnails');
            }

            // Update user avatar path
            $avatarPath = $result['optimized'];
            $user->update(['avatar' => $avatarPath]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully!',
                'avatar_url' => Storage::url($avatarPath),
                'thumbnail_url' => isset($result['thumbnails'][150]) ? Storage::url($result['thumbnails'][150]) : Storage::url($avatarPath)
            ]);

        } catch (\Exception $e) {
            \Log::error('Avatar upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage()
            ], 500);
        }
    }
}