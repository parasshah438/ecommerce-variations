@extends('layouts.app')

@section('title', 'Manage Profile')

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Manage Profile</li>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <!-- Profile Header Section -->
            <div class="profile-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center text-md-start mb-3 mb-md-0">
                        <div class="avatar-container position-relative d-inline-block">
                            <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=120&background=667eea&color=ffffff' }}" 
                                 alt="Profile Avatar" 
                                 class="avatar-preview rounded-circle" 
                                 id="avatarPreview"
                                 style="width: 120px; height: 120px; object-fit: cover; border: 4px solid rgba(255,255,255,0.3); cursor: pointer; transition: all 0.3s ease;">
                            <button type="button" 
                                    class="avatar-upload-btn position-absolute bottom-0 end-0 btn btn-sm rounded-circle p-2" 
                                    onclick="document.getElementById('avatarInput').click()"
                                    style="background: white; color: #667eea; border: none; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: all 0.3s ease;">
                                <i class="bi bi-camera-fill"></i>
                            </button>
                            <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="fw-bold mb-2 text-primary">{{ $user->name }}</h2>
                        <p class="mb-1 text-muted"><i class="bi bi-envelope me-2"></i>{{ $user->email }}</p>
                        @if($user->mobile)
                            <p class="mb-1 text-muted"><i class="bi bi-phone me-2"></i>{{ $user->mobile }}</p>
                        @endif
                        <p class="mb-0 text-muted opacity-75"><i class="bi bi-calendar me-2"></i>Member since {{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="col-md-3 text-center text-md-end">
                        <div class="stat-badge d-inline-block rounded-3 px-3 py-2" style="background: rgba(102, 126, 234, 0.1); border: 1px solid rgba(102, 126, 234, 0.3);">
                            <div class="fw-bold fs-5 text-primary">{{ $stats['total_orders'] ?? 0 }}</div>
                            <small class="text-muted">Total Orders</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4 g-3">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stat-card h-100 border-0 shadow-sm rounded-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-2 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-clock-history text-primary fs-5"></i>
                        </div>
                        <div class="fw-bold text-primary fs-6">{{ $stats['pending_orders'] ?? 0 }}</div>
                        <small class="text-muted">Pending Orders</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stat-card h-100 border-0 shadow-sm rounded-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-2 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        </div>
                        <div class="fw-bold text-success fs-6">{{ $stats['completed_orders'] ?? 0 }}</div>
                        <small class="text-muted">Completed Orders</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stat-card h-100 border-0 shadow-sm rounded-3">
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-2 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-heart-fill text-danger fs-5"></i>
                        </div>
                        <div class="fw-bold text-danger fs-6">{{ $stats['wishlist_items'] ?? 0 }}</div>
                        <small class="text-muted">Wishlist Items</small>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <div class="stat-card h-100 border-0 shadow-sm rounded-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-2 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-geo-alt-fill text-info fs-5"></i>
                        </div>
                        <div class="fw-bold text-info fs-6">{{ $stats['saved_addresses'] ?? 0 }}</div>
                        <small class="text-muted">Saved Addresses</small>
                    </div>
                </div>
                <div class="col-lg-4 col-md-8">
                    <div class="stat-card h-100 border-0 shadow-sm rounded-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-2 mb-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bag-check-fill text-warning fs-5"></i>
                        </div>
                        <div class="fw-bold text-warning fs-6">{{ $stats['last_order'] ?? 'No orders yet' }}</div>
                        <small class="text-muted">Last Order</small>
                    </div>
                </div>
            </div>

            <!-- Profile Management Tabs -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-bottom-0 p-0">
                    <ul class="nav nav-tabs border-bottom-0 px-4 pt-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                <i class="bi bi-person-circle me-2"></i>Personal Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                                <i class="bi bi-sliders me-2"></i>Preferences
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="bi bi-shield-lock me-2"></i>Security
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Personal Information Tab -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <form id="profileForm" method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                            <label for="name"><i class="bi bi-person me-2"></i>Full Name *</label>
                                            @error('name')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                            <label for="email"><i class="bi bi-envelope me-2"></i>Email Address *</label>
                                            @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" placeholder="Enter mobile number">
                                            <label for="mobile"><i class="bi bi-telephone me-2"></i>Mobile Number</label>
                                            @error('mobile')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Enter alternative phone">
                                            <label for="phone"><i class="bi bi-telephone-fill me-2"></i>Alternative Phone</label>
                                            @error('phone')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" 
                                                   value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                                            <label for="date_of_birth"><i class="bi bi-calendar me-2"></i>Date of Birth</label>
                                            @error('date_of_birth')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                <option value="">Select Gender</option>
                                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                                <option value="prefer_not_to_say" {{ old('gender', $user->gender) === 'prefer_not_to_say' ? 'selected' : '' }}>Prefer not to say</option>
                                            </select>
                                            <label for="gender"><i class="bi bi-person-badge me-2"></i>Gender</label>
                                            @error('gender')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" style="height: 100px" maxlength="500" placeholder="Tell us about yourself">{{ old('bio', $user->bio) }}</textarea>
                                            <label for="bio"><i class="bi bi-chat-left-text me-2"></i>Bio</label>
                                            @error('bio')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-text mt-2">Tell us a bit about yourself (max 500 characters)</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>* Required fields</small>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-lg me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Preferences Tab -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h5 class="fw-bold mb-4"><i class="bi bi-bell me-2"></i>Notification Preferences</h5>
                                    <div class="row g-3 mb-4">
                                        <div class="col-12">
                                            <div class="form-check form-switch p-3 rounded-3 pref-card">
                                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                                <label class="form-check-label ms-2" for="emailNotifications">
                                                    <strong><i class="bi bi-envelope me-2"></i>Email Notifications</strong>
                                                    <div class="small text-muted mt-1">Receive order updates and promotions via email</div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch p-3 rounded-3 pref-card">
                                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                                <label class="form-check-label ms-2" for="smsNotifications">
                                                    <strong><i class="bi bi-chat-dots me-2"></i>SMS Notifications</strong>
                                                    <div class="small text-muted mt-1">Receive order updates via SMS</div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch p-3 rounded-3 pref-card">
                                                <input class="form-check-input" type="checkbox" id="marketingEmails" checked>
                                                <label class="form-check-label ms-2" for="marketingEmails">
                                                    <strong><i class="bi bi-megaphone me-2"></i>Marketing Communications</strong>
                                                    <div class="small text-muted mt-1">Receive promotional offers and new product updates</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="fw-bold mb-4"><i class="bi bi-gear me-2"></i>Shopping Preferences</h5>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select class="form-select" id="preferredLanguage">
                                                    <option value="en" selected>English</option>
                                                    <option value="hi">Hindi</option>
                                                    <option value="es">Spanish</option>
                                                </select>
                                                <label for="preferredLanguage"><i class="bi bi-globe me-2"></i>Preferred Language</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <select class="form-select" id="preferredCurrency">
                                                    <option value="INR" selected>Indian Rupee (₹)</option>
                                                    <option value="USD">US Dollar ($)</option>
                                                    <option value="EUR">Euro (€)</option>
                                                </select>
                                                <label for="preferredCurrency"><i class="bi bi-currency-rupee me-2"></i>Currency</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                                        <button type="button" class="btn btn-primary btn-lg" onclick="savePreferences()" disabled title="Feature coming soon">
                                            <i class="bi bi-check-lg me-2"></i>Save Preferences
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h5 class="fw-bold mb-4"><i class="bi bi-shield-lock me-2"></i>Password & Security</h5>
                                    
                                    <div class="card border-0 mb-3 rounded-3" style="background: var(--bs-light);">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-1"><i class="bi bi-key me-2"></i>Password</h6>
                                                    <small class="text-muted">Last changed {{ $user->updated_at->diffForHumans() }}</small>
                                                </div>
                                                <a href="{{ route('profile.password') }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil me-1"></i>Change Password
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-0 mb-3 rounded-3" style="background: var(--bs-light);">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-1"><i class="bi bi-shield-plus me-2"></i>Two-Factor Authentication</h6>
                                                    <small class="text-muted">Add an extra layer of security to your account</small>
                                                </div>
                                                <button class="btn btn-outline-success btn-sm" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>Enable 2FA
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-0 mb-4 rounded-3" style="background: var(--bs-light);">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="fw-bold mb-1"><i class="bi bi-link-45deg me-2"></i>Social Logins</h6>
                                                    <small class="text-muted">Manage connected social media accounts</small>
                                                </div>
                                                <button class="btn btn-outline-info btn-sm" disabled>
                                                    <i class="bi bi-gear me-1"></i>Manage
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <h5 class="fw-bold mb-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h5>
                                    <div class="card border-danger border-2 rounded-3">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-trash me-2"></i>Delete Account</h6>
                                            <p class="text-muted mb-3">Permanently delete your account and all associated data. This action cannot be undone.</p>
                                            <button type="button" class="btn btn-danger" onclick="showDeleteAccountModal()">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Delete My Account
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Crop Modal -->
<div class="modal fade" id="avatarCropModal" tabindex="-1" aria-labelledby="avatarCropModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="avatarCropModalLabel">
                    <i class="bi bi-crop me-2"></i>Crop Your Avatar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Drag to reposition, scroll to zoom</small>
                </div>
                <div id="cropperContainer">
                    <img id="cropperImage" style="max-width: 100%;">
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="uploadCroppedAvatar()">
                    <i class="bi bi-upload me-1"></i>Upload Avatar
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Profile Header Styling */
    .profile-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.05));
        border: 1px solid rgba(102, 126, 234, 0.2);
        border-radius: 20px;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
        pointer-events: none;
    }

    .avatar-preview:hover {
        border-color: rgba(102, 126, 234, 0.8) !important;
        transform: scale(1.05);
    }

    .avatar-upload-btn:hover {
        background: #667eea !important;
        color: white !important;
        transform: scale(1.1);
    }

    /* Stat Cards */
    .stat-card {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    /* Form Styling */
    .form-floating > label {
        color: var(--text-secondary);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }

    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    .form-check-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
    }

    /* Tab Styling */
    .nav-tabs .nav-link {
        color: var(--text-secondary);
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .nav-tabs .nav-link:hover {
        color: #667eea;
        border-bottom-color: rgba(102, 126, 234, 0.3);
    }

    .nav-tabs .nav-link.active {
        color: #667eea;
        background: transparent;
        border-bottom-color: #667eea;
    }

    /* Button Styling */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-primary:hover::before {
        left: 100%;
    }

    /* Preference Cards */
    .pref-card {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .pref-card:hover {
        background: #f0f1f3;
        border-color: #dee2e6;
    }

    /* Dark Mode Support */
    [data-theme="dark"] .profile-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.1));
        border-color: rgba(102, 126, 234, 0.3);
    }

    [data-theme="dark"] .form-check-input {
        background-color: #374151;
        border-color: #4b5563;
    }

    [data-theme="dark"] .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }

    [data-theme="dark"] .form-floating > label {
        color: #9ca3af;
    }

    [data-theme="dark"] .pref-card {
        background: #374151;
        border-color: #4b5563;
    }

    [data-theme="dark"] .pref-card:hover {
        background: #4b5563;
        border-color: #6b7280;
    }

    [data-theme="dark"] .card {
        background: var(--card-bg) !important;
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .card-body {
        background: var(--card-bg) !important;
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }

    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus {
        background-color: #374151;
        border-color: #667eea;
        color: #f9fafb;
    }

    [data-theme="dark"] .form-control::placeholder {
        color: #9ca3af;
    }

    [data-theme="dark"] .form-select option {
        background-color: #374151;
        color: #f9fafb;
    }

    [data-theme="dark"] .nav-tabs .nav-link {
        color: #9ca3af;
    }

    [data-theme="dark"] .nav-tabs .nav-link:hover {
        color: #818cf8;
    }

    [data-theme="dark"] .nav-tabs .nav-link.active {
        color: #818cf8;
        border-bottom-color: #818cf8;
    }

    [data-theme="dark"] .form-text {
        color: #9ca3af;
    }

    [data-theme="dark"] .text-muted {
        color: #9ca3af !important;
    }

    [data-theme="dark"] .border-top {
        border-color: #4b5563 !important;
    }

    [data-theme="dark"] hr {
        border-color: #4b5563;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .profile-header {
            text-align: center;
            padding: 1.5rem;
        }

        .profile-header .row > div {
            margin-bottom: 1rem;
        }

        .profile-header .row > div:last-child {
            margin-bottom: 0;
        }

        .stat-card {
            margin-bottom: 1rem;
            padding: 1rem;
        }

        .nav-tabs {
            flex-direction: column;
        }

        .nav-tabs .nav-link {
            border-bottom: 1px solid var(--border-color) !important;
            border-radius: 0 !important;
            padding: 1rem;
        }

        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #667eea !important;
        }

        .avatar-preview {
            width: 100px !important;
            height: 100px !important;
        }

        .avatar-upload-btn {
            width: 35px !important;
            height: 35px !important;
        }

        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .profile-header {
            border-radius: 15px;
            padding: 1.25rem;
        }

        .stat-card {
            padding: 1rem;
        }

        .card {
            border-radius: 15px !important;
        }

        .form-floating > label {
            font-size: 0.85rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.12/dist/cropper.min.js"></script>
<script>
    let cropper = null;

    // Avatar upload handler
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById('cropperImage');
                img.src = event.target.result;
                
                if (cropper) {
                    cropper.destroy();
                }
                
                cropper = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    guides: true,
                    highlight: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: true,
                });
                
                const modal = new bootstrap.Modal(document.getElementById('avatarCropModal'));
                modal.show();
            };
            reader.readAsDataURL(file);
        }
    });

    function uploadCroppedAvatar() {
        if (!cropper) return;
        
        const canvas = cropper.getCroppedCanvas();
        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('avatar', blob, 'avatar.png');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch('{{ route("profile.avatar.upload") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('avatarPreview').src = data.avatar_url;
                    bootstrap.Modal.getInstance(document.getElementById('avatarCropModal')).hide();
                    toastr.success('Avatar updated successfully!');
                } else {
                    toastr.error(data.message || 'Failed to upload avatar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while uploading avatar');
            });
        });
    }

   
    function showDeleteAccountModal() {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            if (confirm('This will permanently delete all your data. Type "DELETE" to confirm.')) {
                const userInput = prompt('Type DELETE to confirm account deletion:');
                if (userInput === 'DELETE') {
                    fetch('{{ route("profile.delete") }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toastr.success('Account deleted successfully. Redirecting...');
                            setTimeout(() => {
                                window.location.href = '{{ route("home") }}';
                            }, 2000);
                        } else {
                            toastr.error(data.message || 'Failed to delete account');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toastr.error('An error occurred while deleting account');
                    });
                }
            }
        }
    }
</script>
@endpush
