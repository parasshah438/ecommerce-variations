@extends('layouts.app')

@section('title', 'My Orders')

@push('styles')
 <style>
        
       
        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            animation: slideDown 0.5s ease;
        }

        .page-title-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .page-title-section p {
            color: var(--text-secondary);
            margin: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

      
        /* Cards */
        .profile-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 2rem;
            animation: fadeInUp 0.5s ease;
        }

        .card-header-custom {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        /* Profile Avatar */
        .profile-avatar-section {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--main-bg);
            border-radius: 12px;
        }

        .avatar-container {
            position: relative;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: 700;
            box-shadow: var(--shadow-lg);
        }

        .avatar-upload-btn {
            position: absolute;
            bottom: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            background: var(--success-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .avatar-upload-btn:hover {
            transform: scale(1.1);
        }

        .avatar-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .avatar-info p {
            color: var(--text-secondary);
            margin: 0;
        }

        .badge-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: rgba(34, 197, 94, 0.1);
            color: var(--success-color);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        /* Form Elements */
        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control, .form-select {
            background: var(--main-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            background: var(--card-bg);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        /* Input Groups */
        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
        }

        .input-icon .form-control {
            padding-left: 2.75rem;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        /* Buttons */
        .btn-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        .btn-outline-custom {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-outline-custom:hover {
            background: var(--main-bg);
            transform: translateY(-2px);
        }

        /* Info Boxes */
        .info-box {
            padding: 1rem;
            background: var(--main-bg);
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .info-box.warning {
            border-left-color: var(--warning-color);
        }

        .info-box.success {
            border-left-color: var(--success-color);
        }

        .info-box-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-box-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        /* Password Strength Meter */
        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-fill.weak {
            width: 33%;
            background: var(--danger-color);
        }

        .strength-fill.medium {
            width: 66%;
            background: var(--warning-color);
        }

        .strength-fill.strong {
            width: 100%;
            background: var(--success-color);
        }

        .strength-text {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-box {
            padding: 1.25rem;
            background: var(--main-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
        }

        .stat-icon.primary {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.success {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success-color);
        }

        .stat-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        /* Animations */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container-custom {
                padding: 0 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .page-title-section h1 {
                font-size: 1.5rem;
            }

            .profile-avatar-section {
                flex-direction: column;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .nav-tabs-custom {
                flex-direction: column;
            }

            .nav-tabs-custom .nav-link {
                width: 100%;
            }
        }

        /* Tab Content */
        .tab-pane {
            animation: fadeInUp 0.5s ease;
        }
    </style>
@endpush

@section('content')
    <div class="container-custom">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title-section">
                <h1>My Profile</h1>
                <p>Manage your account settings and preferences</p>
            </div>
            <div class="header-actions">
                <div class="theme-toggle-btn" onclick="toggleTheme()">
                    <div class="theme-toggle-slider"></div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs-custom" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                    <i class="bi bi-person"></i>
                    Profile Information
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                    <i class="bi bi-shield-lock"></i>
                    Change Password
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="profileTabContent">
            <!-- Profile Tab -->
            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                <!-- Profile Avatar Section -->
                <div class="profile-card">
                    <div class="profile-avatar-section">
                        <div class="avatar-container">
                            <div class="profile-avatar">JD</div>
                            <div class="avatar-upload-btn" onclick="document.getElementById('avatarUpload').click()">
                                <i class="bi bi-camera"></i>
                            </div>
                            <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                        </div>
                        <div class="avatar-info">
                            <h3>John Doe</h3>
                            <p>john.doe@example.com</p>
                            <span class="badge-custom">
                                <i class="bi bi-patch-check-fill"></i>
                                Verified Account
                            </span>
                        </div>
                    </div>

                    <!-- Account Stats -->
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-icon primary">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="stat-value">24</div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-icon success">
                                <i class="bi bi-heart"></i>
                            </div>
                            <div class="stat-value">12</div>
                            <div class="stat-label">Wishlist Items</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-icon warning">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="stat-value">8</div>
                            <div class="stat-label">Reviews</div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="profile-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="bi bi-person-lines-fill"></i>
                        </div>
                        <div>
                            <h2 class="card-title">Personal Information</h2>
                            <p class="card-subtitle">Update your personal details here</p>
                        </div>
                    </div>

                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">
                                    <i class="bi bi-person"></i>
                                    First Name
                                </label>
                                <input type="text" class="form-control" id="firstName" value="John" placeholder="Enter first name">
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label">
                                    <i class="bi bi-person"></i>
                                    Last Name
                                </label>
                                <input type="text" class="form-control" id="lastName" value="Doe" placeholder="Enter last name">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" class="form-control" id="email" value="john.doe@example.com" placeholder="Enter email">
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    <i class="bi bi-telephone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phone" value="+1 234 567 8900" placeholder="Enter phone">
                            </div>
                            <div class="col-md-6">
                                <label for="dob" class="form-label">
                                    <i class="bi bi-calendar"></i>
                                    Date of Birth
                                </label>
                                <input type="date" class="form-control" id="dob" value="1990-01-01">
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label">
                                    <i class="bi bi-gender-ambiguous"></i>
                                    Gender
                                </label>
                                <select class="form-select" id="gender">
                                    <option value="">Select gender</option>
                                    <option value="male" selected>Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="bio" class="form-label">
                                    <i class="bi bi-chat-quote"></i>
                                    Bio
                                </label>
                                <textarea class="form-control" id="bio" rows="3" placeholder="Tell us about yourself...">Passionate about technology and innovation.</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn-custom btn-primary-custom">
                                <i class="bi bi-check-circle"></i>
                                Save Changes
                            </button>
                            <button type="reset" class="btn-custom btn-outline-custom">
                                <i class="bi bi-x-circle"></i>
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Address Information -->
                <div class="profile-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h2 class="card-title">Address Information</h2>
                            <p class="card-subtitle">Manage your shipping addresses</p>
                        </div>
                    </div>

                    <form>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="address" class="form-label">
                                    <i class="bi bi-house"></i>
                                    Street Address
                                </label>
                                <input type="text" class="form-control" id="address" value="123 Main Street, Apartment 4B" placeholder="Enter street address">
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">
                                    <i class="bi bi-building"></i>
                                    City
                                </label>
                                <input type="text" class="form-control" id="city" value="New York" placeholder="Enter city">
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label">
                                    <i class="bi bi-map"></i>
                                    State/Province
                                </label>
                                <input type="text" class="form-control" id="state" value="NY" placeholder="Enter state">
                            </div>
                            <div class="col-md-6">
                                <label for="zip" class="form-label">
                                    <i class="bi bi-mailbox"></i>
                                    ZIP/Postal Code
                                </label>
                                <input type="text" class="form-control" id="zip" value="10001" placeholder="Enter ZIP code">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label">
                                    <i class="bi bi-globe"></i>
                                    Country
                                </label>
                                <select class="form-select" id="country">
                                    <option value="us" selected>United States</option>
                                    <option value="uk">United Kingdom</option>
                                    <option value="ca">Canada</option>
                                    <option value="au">Australia</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn-custom btn-primary-custom">
                                <i class="bi bi-check-circle"></i>
                                Update Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Tab -->
            <div class="tab-pane fade" id="password" role="tabpanel">
                <div class="profile-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div>
                            <h2 class="card-title">Change Password</h2>
                            <p class="card-subtitle">Keep your account secure with a strong password</p>
                        </div>
                    </div>

                    <div class="info-box warning">
                        <div class="info-box-title">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            Password Requirements
                        </div>
                        <p class="info-box-text">Your password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters.</p>
                    </div>

                    <form id="passwordForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="currentPassword" class="form-label">
                                    <i class="bi bi-lock"></i>
                                    Current Password
                                </label>
                                <div class="input-icon">
                                    <i class="bi bi-key-fill"></i>
                                    <input type="password" class="form-control" id="currentPassword" placeholder="Enter current password">
                                    <span class="password-toggle" onclick="togglePassword('currentPassword')">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="newPassword" class="form-label">
                                    <i class="bi bi-lock-fill"></i>
                                    New Password
                                </label>
                                <div class="input-icon">
                                    <i class="bi bi-key-fill"></i>
                                    <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" oninput="checkPasswordStrength(this.value)">
                                    <span class="password-toggle" onclick="togglePassword('newPassword')">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-bar">
                                        <div class="strength-fill" id="strengthBar"></div>
                                    </div>
                                    <span class="strength-text" id="strengthText">Enter a password to check strength</span>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="confirmPassword" class="form-label">
                                    <i class="bi bi-lock-fill"></i>
                                    Confirm New Password
                                </label>
                                <div class="input-icon">
                                    <i class="bi bi-key-fill"></i>
                                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                                    <span class="password-toggle" onclick="togglePassword('confirmPassword')">
                                        <i class="bi bi-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="info-box success mt-3">
                            <div class="info-box-title">
                                <i class="bi bi-check-circle-fill"></i>
                                Security Tips
                            </div>
                            <p class="info-box-text">Use a unique password that you don't use for other websites. Consider using a password manager to keep track of your passwords.</p>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn-custom btn-primary-custom">
                                <i class="bi bi-shield-check"></i>
                                Update Password
                            </button>
                            <button type="reset" class="btn-custom btn-outline-custom">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="profile-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <div>
                            <h2 class="card-title">Two-Factor Authentication</h2>
                            <p class="card-subtitle">Add an extra layer of security to your account</p>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-box-title">
                            <i class="bi bi-info-circle-fill"></i>
                            What is 2FA?
                        </div>
                        <p class="info-box-text">Two-factor authentication adds an additional layer of security by requiring a second form of verification when you sign in.</p>
                    </div>

                    <div class="d-flex align-items-center justify-content-between p-3 bg-opacity-10" style="background: var(--main-bg); border-radius: 10px;">
                        <div>
                            <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 0.25rem;">Two-Factor Authentication</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);">Currently disabled</div>
                        </div>
                        <button class="btn-custom btn-primary-custom">
                            <i class="bi bi-shield-plus"></i>
                            Enable 2FA
                        </button>
                    </div>
                </div>

                <!-- Recent Security Activity -->
                <div class="profile-card">
                    <div class="card-header-custom">
                        <div class="card-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h2 class="card-title">Recent Security Activity</h2>
                            <p class="card-subtitle">Monitor your account's recent login activity</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table" style="margin-bottom: 0;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <th style="color: var(--text-secondary); font-weight: 600; font-size: 0.875rem; padding: 0.75rem 0;">Device</th>
                                    <th style="color: var(--text-secondary); font-weight: 600; font-size: 0.875rem; padding: 0.75rem 0;">Location</th>
                                    <th style="color: var(--text-secondary); font-weight: 600; font-size: 0.875rem; padding: 0.75rem 0;">Time</th>
                                    <th style="color: var(--text-secondary); font-weight: 600; font-size: 0.875rem; padding: 0.75rem 0;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem 0; color: var(--text-primary);">
                                        <i class="bi bi-laptop" style="margin-right: 0.5rem;"></i>
                                        Chrome on Windows
                                    </td>
                                    <td style="padding: 1rem 0; color: var(--text-secondary);">New York, US</td>
                                    <td style="padding: 1rem 0; color: var(--text-secondary);">2 hours ago</td>
                                    <td style="padding: 1rem 0;">
                                        <span style="padding: 0.25rem 0.75rem; background: rgba(34, 197, 94, 0.1); color: var(--success-color); border-radius: 6px; font-size: 0.75rem; font-weight: 600;">Success</span>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td style="padding: 1rem 0; color: var(--text-primary);">
                                        <i class="bi bi-phone" style="margin-right: 0.5rem;"></i>
                                        Safari on iPhone
                                    </td>
                                    <td style="padding: 1rem 0; color: var(--text-secondary);">New York, US</td>
                                    <td style="padding: 1rem 0; color: var(--text-secondary);">1 day ago</td>
                                    <td style="padding: 1rem 0;">
                                        <span style="padding: 0.25rem 0.75rem; background: rgba(34, 197, 94, 0.1); color: var(--success-color); border-radius: 6px; font-size: 0.75rem; font-weight: 600;">Success</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 1rem 0; color: var(--text-primary);">
                                        <i class="bi bi-tablet" style="margin-right: 0.5rem;"></i>
                                        Chrome on iPad
                                    </td>
                                    <td style="padding: 1rem 0; color: var(--text-secondary);">Los Angeles, US</td>
                                    <td style="padding: 1rem 0; color: var(--text-secondary);">3 days ago</td>
                                    <td style="padding: 1rem 0;">
                                        <span style="padding: 0.25rem 0.75rem; background: rgba(34, 197, 94, 0.1); color: var(--success-color); border-radius: 6px; font-size: 0.75rem; font-weight: 600;">Success</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
   
@push('scripts')
    <script>
        // Theme Toggle
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        // Password Toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = event.target.closest('.password-toggle').querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Password Strength Checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (!password) {
                strengthBar.className = 'strength-fill';
                strengthText.textContent = 'Enter a password to check strength';
                return;
            }

            let strength = 0;
            
            // Check length
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Check for uppercase
            if (/[A-Z]/.test(password)) strength++;
            
            // Check for lowercase
            if (/[a-z]/.test(password)) strength++;
            
            // Check for numbers
            if (/[0-9]/.test(password)) strength++;
            
            // Check for special characters
            if (/[^A-Za-z0-9]/.test(password)) strength++;

            // Set strength level
            if (strength <= 2) {
                strengthBar.className = 'strength-fill weak';
                strengthText.textContent = 'Weak password - Add more characters and variety';
                strengthText.style.color = 'var(--danger-color)';
            } else if (strength <= 4) {
                strengthBar.className = 'strength-fill medium';
                strengthText.textContent = 'Medium password - Consider adding special characters';
                strengthText.style.color = 'var(--warning-color)';
            } else {
                strengthBar.className = 'strength-fill strong';
                strengthText.textContent = 'Strong password - Great job!';
                strengthText.style.color = 'var(--success-color)';
            }
        }

        // Form Validation
        document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (!currentPassword) {
                alert('Please enter your current password');
                return;
            }

            if (newPassword.length < 8) {
                alert('New password must be at least 8 characters long');
                return;
            }

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }

            // Success - In real app, submit to server
            alert('Password updated successfully!');
            this.reset();
            document.getElementById('strengthBar').className = 'strength-fill';
            document.getElementById('strengthText').textContent = 'Enter a password to check strength';
        });

        // Avatar Upload Preview
        document.getElementById('avatarUpload')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatar = document.querySelector('.profile-avatar');
                    avatar.style.backgroundImage = `url(${e.target.result})`;
                    avatar.style.backgroundSize = 'cover';
                    avatar.style.backgroundPosition = 'center';
                    avatar.textContent = '';
                };
                reader.readAsDataURL(file);
            }
        });

        // Add animation delay to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.profile-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
@endpush