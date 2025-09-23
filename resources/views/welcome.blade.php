<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ShopEase - Your Ultimate Shopping Destination</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #2c3e50;
            --accent-color: #f39c12;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
            --border-color: #dee2e6;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        }

        [data-bs-theme="dark"] {
            --primary-color: #ff7849;
            --secondary-color: #34495e;
            --accent-color: #f1c40f;
            --light-color: #1a1a1a;
            --dark-color: #ffffff;
            --border-color: #495057;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: var(--light-color);
            transition: all 0.3s ease;
        }

        /* Header Styles */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #ff8c69);
            box-shadow: var(--shadow-md);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.75rem;
            color: white !important;
            text-decoration: none;
        }

        .navbar-brand:hover {
            color: #f8f9fa !important;
        }

        /* Search Bar Styles */
        .search-container {
            position: relative;
            flex: 1;
            max-width: 600px;
            margin: 0 2rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 1rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.25);
            transform: translateY(-1px);
        }

        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: #e55a2b;
            transform: translateY(-50%) scale(1.05);
        }

        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            max-height: 500px;
            overflow-y: auto;
            display: none;
            margin-top: 5px;
            border: 1px solid var(--border-color);
        }

        [data-bs-theme="dark"] .search-suggestions {
            background: #2c3e50;
            color: white;
            border-color: #495057;
        }

        .search-section {
            padding: 0.75rem 0;
        }

        .search-section:not(:last-child) {
            border-bottom: 1px solid var(--border-color);
        }

        [data-bs-theme="dark"] .search-section:not(:last-child) {
            border-bottom-color: #495057;
        }

        .search-section-title {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
        }

        .suggestion-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
        }

        .suggestion-item:hover {
            background: var(--light-color);
            padding-left: 1.25rem;
        }

        [data-bs-theme="dark"] .suggestion-item:hover {
            background: #34495e;
        }

        .suggestion-item.active {
            background: var(--primary-color);
            color: white;
        }

        .suggestion-item.active .suggestion-icon {
            color: white;
        }

        .suggestion-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 0.9rem;
        }

        .suggestion-content {
            flex: 1;
            min-width: 0;
        }

        .suggestion-main {
            font-weight: 500;
            margin-bottom: 0.1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .suggestion-sub {
            font-size: 0.8rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        [data-bs-theme="dark"] .suggestion-sub {
            color: #adb5bd;
        }

        .suggestion-item.active .suggestion-sub {
            color: rgba(255, 255, 255, 0.8);
        }

        .suggestion-meta {
            font-size: 0.75rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        [data-bs-theme="dark"] .suggestion-meta {
            color: #adb5bd;
        }

        .suggestion-item.active .suggestion-meta {
            color: rgba(255, 255, 255, 0.7);
        }

        .suggestion-price {
            font-weight: 600;
            color: var(--success-color);
        }

        .suggestion-item.active .suggestion-price {
            color: rgba(255, 255, 255, 0.9);
        }

        .suggestion-rating {
            display: flex;
            align-items: center;
            gap: 0.2rem;
        }

        .suggestion-trending {
            background: linear-gradient(45deg, #ff6b35, #f39c12);
            color: white;
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 10px;
            font-weight: 500;
        }

        .suggestion-history {
            color: #6c757d;
        }

        .suggestion-clear {
            color: #6c757d;
            font-size: 0.8rem;
            padding: 0.25rem;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .suggestion-clear:hover {
            background: var(--danger-color);
            color: white;
        }

        .search-no-results {
            padding: 2rem 1rem;
            text-align: center;
            color: #6c757d;
        }

        .search-no-results i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .search-filters {
            padding: 0.75rem 1rem;
            background: var(--light-color);
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        [data-bs-theme="dark"] .search-filters {
            background: #34495e;
            border-top-color: #495057;
        }

        .search-filter-chip {
            background: white;
            border: 1px solid var(--border-color);
            color: var(--dark-color);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        [data-bs-theme="dark"] .search-filter-chip {
            background: #2c3e50;
            border-color: #495057;
            color: white;
        }

        .search-filter-chip:hover,
        .search-filter-chip.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .search-loading {
            padding: 1rem;
            text-align: center;
            color: var(--primary-color);
        }

        .search-loading .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
        }

        /* Search Input Enhancements */
        .search-input-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 3rem 0.75rem 3rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.25);
            transform: translateY(-1px);
        }

        .search-input.has-suggestions {
            border-radius: 50px 50px 0 0;
        }

        .search-icon-left {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1rem;
        }

        .search-clear {
            position: absolute;
            right: 50px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 50%;
            transition: all 0.2s ease;
            display: none;
        }

        .search-clear:hover {
            background: var(--danger-color);
            color: white;
        }

        .search-clear.show {
            display: block;
        }

        /* Keyboard Navigation */
        .suggestion-item.keyboard-active {
            background: var(--primary-color);
            color: white;
        }

        .suggestion-item.keyboard-active .suggestion-icon,
        .suggestion-item.keyboard-active .suggestion-sub,
        .suggestion-item.keyboard-active .suggestion-meta {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Navigation Icons */
        .nav-icons {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nav-icon {
            position: relative;
            color: white;
            font-size: 1.25rem;
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 50%;
        }

        .nav-icon:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .nav-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Theme Toggle */
        .theme-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-1px);
        }

        /* Category Navigation */
        .category-nav {
            background: white;
            box-shadow: var(--shadow-sm);
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        [data-bs-theme="dark"] .category-nav {
            background: #2c3e50;
            border-bottom-color: #495057;
        }

        .category-item {
            color: var(--dark-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-weight: 500;
            white-space: nowrap;
        }

        .category-item:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            animation: fadeInUp 1s ease;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }

        .hero-cta {
            background: var(--primary-color);
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            animation: fadeInUp 1s ease 0.4s both;
        }

        .hero-cta:hover {
            background: #e55a2b;
            color: white;
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        /* Product Cards */
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        [data-bs-theme="dark"] .product-card {
            background: #2c3e50;
            border-color: #495057;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 280px;
            background: #f8f9fa;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.4s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.08);
        }

        .discount-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(238, 90, 82, 0.3);
        }

        .stock-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .stock-badge.out-of-stock {
            background: rgba(108, 117, 125, 0.9);
            color: white;
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-actions {
            display: flex;
            gap: 10px;
        }

        .quick-action-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: white;
            border: none;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .quick-action-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-brand {
            color: #6c757d;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .product-title a {
            color: var(--dark-color);
            transition: color 0.3s ease;
        }

        .product-title a:hover {
            color: var(--primary-color);
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 0.9rem;
        }

        .rating-text {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        .product-price {
            margin-bottom: 20px;
        }

        .current-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .original-price {
            font-size: 1rem;
            color: #6c757d;
            text-decoration: line-through;
            margin-left: 8px;
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            gap: 0;
        }

        .btn-add-cart {
            flex: 1;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(247, 102, 49, 0.3);
        }

        .btn-add-cart.btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #e55a2b);
            color: white;
        }

        .btn-add-cart.btn-outline-secondary {
            background: transparent;
            border: 2px solid #dee2e6;
            color: #6c757d;
        }

        /* Section Improvements */
        .section-subtitle {
            font-size: 1.1rem;
            margin-top: -1rem;
            margin-bottom: 2rem;
        }

        #featured-products {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 50%, #f8f9fa 100%);
        }

        [data-bs-theme="dark"] #featured-products {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 50%, #1a1a1a 100%);
        }

        /* Load More Button */
        .btn-load-more {
            background: linear-gradient(135deg, var(--primary-color), #e55a2b);
            border: none;
            color: white;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(247, 102, 49, 0.2);
            min-width: 200px;
            height: 54px;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .btn-load-more:hover {
            background: linear-gradient(135deg, #e55a2b, #d94d1a);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(247, 102, 49, 0.3);
            color: white;
        }

        .btn-load-more:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(247, 102, 49, 0.2);
        }

        .btn-load-more:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 4px 15px rgba(247, 102, 49, 0.1);
        }

        .btn-load-more:disabled:hover {
            transform: none;
            background: linear-gradient(135deg, var(--primary-color), #e55a2b);
        }

        .btn-load-more .btn-content {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-load-more .btn-text,
        .btn-load-more .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .btn-load-more .btn-text {
            opacity: 1;
            visibility: visible;
        }

        .btn-load-more .loading {
            opacity: 0;
            visibility: hidden;
        }

        .btn-load-more .loading.show {
            opacity: 1;
            visibility: visible;
        }

        .btn-load-more .btn-text.hide {
            opacity: 0;
            visibility: hidden;
        }

        .btn-load-more .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        /* Section Titles */
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--dark-color);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        /* Features Section */
        .features-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        [data-bs-theme="dark"] .features-section {
            background: linear-gradient(135deg, #1a1a1a 0%, #2c3e50 100%);
        }

        .feature-card {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            height: 100%;
        }

        [data-bs-theme="dark"] .feature-card {
            background: #34495e;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark-color);
        }

        .feature-description {
            color: #6c757d;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 3rem 0 1rem;
        }

        .footer-section h5 {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .footer-link {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s ease;
            display: block;
            padding: 0.25rem 0;
        }

        .footer-link:hover {
            color: white;
            padding-left: 0.5rem;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
        }

        .social-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            background: #e55a2b;
            color: white;
            transform: translateY(-2px);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-container {
                margin: 1rem 0;
                order: 3;
                flex-basis: 100%;
            }

            .nav-icons {
                gap: 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .category-nav .d-flex {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 0.5rem;
            }

            .category-nav::-webkit-scrollbar {
                height: 4px;
            }

            .category-nav::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .category-nav::-webkit-scrollbar-thumb {
                background: var(--primary-color);
                border-radius: 2px;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Notification Toast */
        .toast-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1050;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #e55a2b;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-track {
            background: #2c3e50;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb {
            background: #495057;
        }

        [data-bs-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="bi bi-shop me-2"></i>ShopEase
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list text-white fs-4"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <i class="bi bi-search search-icon-left"></i>
                        <input type="text" class="search-input" id="searchInput" placeholder="Search for products, brands, categories..." autocomplete="off">
                        <button class="search-clear" id="searchClear">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                        <button class="search-btn" id="searchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>

                <!-- Navigation Icons -->
                <div class="nav-icons ms-auto">
                    <!-- Social Login Demo -->
                    <a href="{{ route('social.login.demo') }}" class="nav-icon" title="Social Login Demo">
                        <i class="bi bi-people"></i>
                    </a>

                    <!-- Theme Toggle -->
                    <button class="theme-toggle btn" id="themeToggle">
                        <i class="bi bi-sun-fill" id="themeIcon"></i>
                    </button>

                    <!-- User Account -->
                    @auth
                        <div class="dropdown">
                            <a class="nav-icon dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-bag me-2"></i>Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('test.single.login') }}"><i class="bi bi-shield-check me-2"></i>Single Login Test</a></li>
                                <li><a class="dropdown-item" href="{{ route('social.login.demo') }}"><i class="bi bi-people me-2"></i>Social Login Demo</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-icon" title="Login">
                            <i class="bi bi-person"></i>
                        </a>
                    @endauth

                    <!-- Wishlist -->
                    <a href="#" class="nav-icon" title="Wishlist">
                        <i class="bi bi-heart"></i>
                        <span class="badge">3</span>
                    </a>

                    <!-- Cart -->
                    <a href="#" class="nav-icon" title="Cart">
                        <i class="bi bi-bag"></i>
                        <span class="badge">5</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Category Navigation -->
    <div class="category-nav">
        <div class="container-fluid">
            <div class="d-flex gap-3 align-items-center">
                <a href="#" class="category-item">Electronics</a>
                <a href="#" class="category-item">Fashion</a>
                <a href="#" class="category-item">Home & Garden</a>
                <a href="#" class="category-item">Sports</a>
                <a href="#" class="category-item">Books</a>
                <a href="#" class="category-item">Beauty</a>
                <a href="#" class="category-item">Automotive</a>
                <a href="#" class="category-item">Toys</a>
                <a href="#" class="category-item">Health</a>
                <a href="#" class="category-item">Groceries</a>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Discover Amazing Products</h1>
                        <p class="hero-subtitle">Shop from millions of products with the best deals, fastest delivery, and excellent customer service.</p>
                        <a href="#featured-products" class="hero-cta">
                            <i class="bi bi-arrow-down-circle me-2"></i>Start Shopping
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Shopping" class="img-fluid rounded-3" style="max-height: 400px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose ShopEase?</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <h4 class="feature-title">Free Shipping</h4>
                        <p class="feature-description">Free shipping on orders over $50. Fast and reliable delivery to your doorstep.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="feature-title">Secure Payment</h4>
                        <p class="feature-description">Your payment information is encrypted and secure with our advanced security measures.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="bi bi-arrow-clockwise"></i>
                        </div>
                        <h4 class="feature-title">Easy Returns</h4>
                        <p class="feature-description">30-day return policy. Not satisfied? Return your items hassle-free.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card fade-in-up">
                        <div class="feature-icon">
                            <i class="bi bi-headset"></i>
                        </div>
                        <h4 class="feature-title">24/7 Support</h4>
                        <p class="feature-description">Our customer support team is available 24/7 to help you with any questions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5 bg-light" id="featured-products">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Featured Products</h2>
                <p class="section-subtitle text-muted">Discover our handpicked selection of premium products</p>
            </div>
            <div class="row g-4" id="productsContainer">
                <!-- Products will be loaded here -->
            </div>
            <div class="text-center mt-5">
                <div class="d-flex justify-content-center">
                    <button class="btn btn-load-more" id="loadMoreBtn">
                        <div class="btn-content">
                            <span class="btn-text">
                                <i class="bi bi-plus-circle me-2"></i>Load More Products
                            </span>
                            <span class="loading d-none">
                                <i class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></i>Loading...
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3 class="mb-3">Stay Updated with Our Latest Offers</h3>
                    <p class="mb-0">Subscribe to our newsletter and get exclusive deals, new product announcements, and more!</p>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex gap-2">
                        <input type="email" class="form-control form-control-lg" placeholder="Enter your email address">
                        <button class="btn btn-light btn-lg px-4">Subscribe</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h5><i class="bi bi-shop me-2"></i>ShopEase</h5>
                        <p class="text-muted">Your ultimate shopping destination with millions of products, great deals, and excellent customer service.</p>
                        <div class="social-icons">
                            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-section">
                        <h5>Shop</h5>
                        <a href="#" class="footer-link">Electronics</a>
                        <a href="#" class="footer-link">Fashion</a>
                        <a href="#" class="footer-link">Home & Garden</a>
                        <a href="#" class="footer-link">Sports</a>
                        <a href="#" class="footer-link">Books</a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-section">
                        <h5>Customer Service</h5>
                        <a href="#" class="footer-link">Contact Us</a>
                        <a href="#" class="footer-link">FAQ</a>
                        <a href="#" class="footer-link">Shipping Info</a>
                        <a href="#" class="footer-link">Returns</a>
                        <a href="#" class="footer-link">Size Guide</a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-section">
                        <h5>Company</h5>
                        <a href="#" class="footer-link">About Us</a>
                        <a href="#" class="footer-link">Careers</a>
                        <a href="#" class="footer-link">Press</a>
                        <a href="#" class="footer-link">Blog</a>
                        <a href="#" class="footer-link">Affiliate</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-section">
                        <h5>Contact Info</h5>
                        <p class="text-muted mb-2"><i class="bi bi-geo-alt me-2"></i>123 Shopping Street, City, State 12345</p>
                        <p class="text-muted mb-2"><i class="bi bi-telephone me-2"></i>+1 (555) 123-4567</p>
                        <p class="text-muted mb-2"><i class="bi bi-envelope me-2"></i>support@shopease.com</p>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2024 ShopEase. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="footer-link me-3">Privacy Policy</a>
                    <a href="#" class="footer-link me-3">Terms of Service</a>
                    <a href="#" class="footer-link">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div class="toast-container"></div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;

        // Check for saved theme preference or default to 'light'
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-bs-theme', currentTheme);
        updateThemeIcon(currentTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }

        // Advanced Search System with Real-World eCommerce Features
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        const searchClear = document.getElementById('searchClear');
        const searchSuggestions = document.getElementById('searchSuggestions');

        // Comprehensive search data (simulating real eCommerce database)
        const searchData = {
            products: [
                { id: 1, name: 'iPhone 15 Pro Max', category: 'Electronics', brand: 'Apple', price: 1199, originalPrice: 1299, rating: 4.8, reviews: 2547, trending: true, image: 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=100' },
                { id: 2, name: 'Samsung Galaxy S24 Ultra', category: 'Electronics', brand: 'Samsung', price: 1099, originalPrice: 1199, rating: 4.7, reviews: 1834, trending: true, image: 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=100' },
                { id: 3, name: 'MacBook Air M3', category: 'Electronics', brand: 'Apple', price: 1299, originalPrice: 1399, rating: 4.9, reviews: 3421, trending: false, image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=100' },
                { id: 4, name: 'Nike Air Max 270', category: 'Fashion', brand: 'Nike', price: 150, originalPrice: 180, rating: 4.6, reviews: 892, trending: true, image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=100' },
                { id: 5, name: 'Adidas Ultraboost 22', category: 'Fashion', brand: 'Adidas', price: 180, originalPrice: 200, rating: 4.5, reviews: 1247, trending: false, image: 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=100' },
                { id: 6, name: 'Sony WH-1000XM5 Headphones', category: 'Electronics', brand: 'Sony', price: 399, originalPrice: 449, rating: 4.8, reviews: 1567, trending: true, image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=100' },
                { id: 7, name: 'Apple Watch Series 9', category: 'Electronics', brand: 'Apple', price: 399, originalPrice: 429, rating: 4.7, reviews: 2134, trending: false, image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=100' },
                { id: 8, name: 'Levi\'s 501 Original Jeans', category: 'Fashion', brand: 'Levi\'s', price: 89, originalPrice: 98, rating: 4.4, reviews: 756, trending: false, image: 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=100' },
                { id: 9, name: 'Canon EOS R5 Camera', category: 'Electronics', brand: 'Canon', price: 3899, originalPrice: 3999, rating: 4.9, reviews: 423, trending: false, image: 'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=100' },
                { id: 10, name: 'Dyson V15 Detect Vacuum', category: 'Home & Garden', brand: 'Dyson', price: 749, originalPrice: 799, rating: 4.6, reviews: 1089, trending: true, image: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=100' },
                { id: 11, name: 'KitchenAid Stand Mixer', category: 'Home & Garden', brand: 'KitchenAid', price: 379, originalPrice: 429, rating: 4.8, reviews: 2341, trending: false, image: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=100' },
                { id: 12, name: 'Fitbit Charge 5', category: 'Health', brand: 'Fitbit', price: 179, originalPrice: 199, rating: 4.3, reviews: 1456, trending: false, image: 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=100' },
            ],
            categories: [
                { name: 'Electronics', icon: 'bi-laptop', count: 15420 },
                { name: 'Fashion', icon: 'bi-bag', count: 8934 },
                { name: 'Home & Garden', icon: 'bi-house', count: 6721 },
                { name: 'Sports', icon: 'bi-trophy', count: 4532 },
                { name: 'Books', icon: 'bi-book', count: 12456 },
                { name: 'Beauty', icon: 'bi-heart', count: 3421 },
                { name: 'Health', icon: 'bi-heart-pulse', count: 2876 },
                { name: 'Automotive', icon: 'bi-car-front', count: 1987 },
                { name: 'Toys', icon: 'bi-puzzle', count: 2134 },
                { name: 'Groceries', icon: 'bi-basket', count: 5643 }
            ],
            brands: [
                { name: 'Apple', icon: 'bi-apple', count: 234 },
                { name: 'Samsung', icon: 'bi-phone', count: 189 },
                { name: 'Nike', icon: 'bi-lightning', count: 156 },
                { name: 'Adidas', icon: 'bi-star', count: 143 },
                { name: 'Sony', icon: 'bi-speaker', count: 98 },
                { name: 'Canon', icon: 'bi-camera', count: 76 },
                { name: 'Dyson', icon: 'bi-wind', count: 45 },
                { name: 'KitchenAid', icon: 'bi-cup-hot', count: 67 },
                { name: 'Levi\'s', icon: 'bi-person', count: 89 },
                { name: 'Fitbit', icon: 'bi-smartwatch', count: 54 }
            ],
            trending: [
                'iPhone 15 Pro',
                'Black Friday deals',
                'Winter jackets',
                'Gaming laptops',
                'Air fryers',
                'Wireless earbuds',
                'Smart watches',
                'Running shoes'
            ]
        };

        // Search history management
        let searchHistory = JSON.parse(localStorage.getItem('searchHistory') || '[]');
        let currentSuggestionIndex = -1;
        let searchTimeout;
        let isLoading = false;
        
        // Product loading variables
        let currentPage = 1;
        const productsPerPage = 6;

        // Search input event listeners
        searchInput.addEventListener('input', handleSearchInput);
        searchInput.addEventListener('focus', handleSearchFocus);
        searchInput.addEventListener('keydown', handleKeyboardNavigation);
        searchInput.addEventListener('blur', () => {
            // Delay hiding suggestions to allow clicks
            setTimeout(() => hideSuggestions(), 150);
        });

        searchClear.addEventListener('click', clearSearch);
        searchBtn.addEventListener('click', handleSearchSubmit);

        // Handle search input with debouncing
        function handleSearchInput(e) {
            const query = e.target.value.trim();
            
            // Show/hide clear button
            searchClear.classList.toggle('show', query.length > 0);
            
            clearTimeout(searchTimeout);
            
            if (query.length === 0) {
                hideSuggestions();
                return;
            }

            if (query.length === 1) {
                showTrendingSuggestions();
                return;
            }

            // Show loading state
            if (query.length >= 2) {
                showLoadingSuggestions();
                
                searchTimeout = setTimeout(() => {
                    showAdvancedSuggestions(query);
                }, 300);
            }
        }

        // Handle search focus
        function handleSearchFocus(e) {
            const query = e.target.value.trim();
            
            if (query.length === 0) {
                showTrendingSuggestions();
            } else if (query.length >= 2) {
                showAdvancedSuggestions(query);
            }
        }

        // Handle keyboard navigation
        function handleKeyboardNavigation(e) {
            const suggestions = searchSuggestions.querySelectorAll('.suggestion-item');
            
            if (suggestions.length === 0) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    currentSuggestionIndex = Math.min(currentSuggestionIndex + 1, suggestions.length - 1);
                    updateSuggestionHighlight(suggestions);
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    currentSuggestionIndex = Math.max(currentSuggestionIndex - 1, -1);
                    updateSuggestionHighlight(suggestions);
                    break;
                    
                case 'Enter':
                    e.preventDefault();
                    if (currentSuggestionIndex >= 0) {
                        const selectedSuggestion = suggestions[currentSuggestionIndex];
                        selectSuggestion(selectedSuggestion);
                    } else {
                        handleSearchSubmit();
                    }
                    break;
                    
                case 'Escape':
                    hideSuggestions();
                    searchInput.blur();
                    break;
            }
        }

        // Update suggestion highlight for keyboard navigation
        function updateSuggestionHighlight(suggestions) {
            suggestions.forEach((item, index) => {
                item.classList.toggle('keyboard-active', index === currentSuggestionIndex);
            });
        }

        // Show trending suggestions (when input is empty or has 1 character)
        function showTrendingSuggestions() {
            const recentSearches = searchHistory.slice(0, 3);
            const trendingSearches = searchData.trending.slice(0, 5);
            
            let sectionsHTML = '';
            
            // Recent searches section
            if (recentSearches.length > 0) {
                sectionsHTML += `
                    <div class="search-section">
                        <div class="search-section-title">Recent Searches</div>
                        ${recentSearches.map(search => `
                            <div class="suggestion-item" data-value="${search}" data-type="history">
                                <div class="suggestion-icon">
                                    <i class="bi bi-clock-history suggestion-history"></i>
                                </div>
                                <div class="suggestion-content">
                                    <div class="suggestion-main">${search}</div>
                                </div>
                                <button class="suggestion-clear" onclick="removeFromHistory('${search}')" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            
            // Trending searches section
            sectionsHTML += `
                <div class="search-section">
                    <div class="search-section-title">Trending Searches</div>
                    ${trendingSearches.map(search => `
                        <div class="suggestion-item" data-value="${search}" data-type="trending">
                            <div class="suggestion-icon">
                                <i class="bi bi-graph-up-arrow"></i>
                            </div>
                            <div class="suggestion-content">
                                <div class="suggestion-main">${search}</div>
                                <div class="suggestion-sub">Popular right now</div>
                            </div>
                            <div class="suggestion-meta">
                                <span class="suggestion-trending">Trending</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
            
            displaySuggestions(sectionsHTML);
        }

        // Show loading suggestions
        function showLoadingSuggestions() {
            const loadingHTML = `
                <div class="search-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Searching...</div>
                </div>
            `;
            
            displaySuggestions(loadingHTML);
            isLoading = true;
        }

        // Show advanced suggestions with multiple sections
        function showAdvancedSuggestions(query) {
            isLoading = false;
            const lowerQuery = query.toLowerCase();
            
            // Filter data
            const matchingProducts = searchData.products.filter(product => 
                product.name.toLowerCase().includes(lowerQuery) ||
                product.brand.toLowerCase().includes(lowerQuery) ||
                product.category.toLowerCase().includes(lowerQuery)
            ).slice(0, 4);
            
            const matchingCategories = searchData.categories.filter(category =>
                category.name.toLowerCase().includes(lowerQuery)
            ).slice(0, 3);
            
            const matchingBrands = searchData.brands.filter(brand =>
                brand.name.toLowerCase().includes(lowerQuery)
            ).slice(0, 3);

            if (matchingProducts.length === 0 && matchingCategories.length === 0 && matchingBrands.length === 0) {
                showNoResults(query);
                return;
            }

            let sectionsHTML = '';

            // Products section
            if (matchingProducts.length > 0) {
                sectionsHTML += `
                    <div class="search-section">
                        <div class="search-section-title">Products</div>
                        ${matchingProducts.map(product => createProductSuggestion(product, query)).join('')}
                    </div>
                `;
            }

            // Categories section
            if (matchingCategories.length > 0) {
                sectionsHTML += `
                    <div class="search-section">
                        <div class="search-section-title">Categories</div>
                        ${matchingCategories.map(category => createCategorySuggestion(category, query)).join('')}
                    </div>
                `;
            }

            // Brands section
            if (matchingBrands.length > 0) {
                sectionsHTML += `
                    <div class="search-section">
                        <div class="search-section-title">Brands</div>
                        ${matchingBrands.map(brand => createBrandSuggestion(brand, query)).join('')}
                    </div>
                `;
            }

            // Quick filters
            sectionsHTML += createQuickFilters(query);

            displaySuggestions(sectionsHTML);
        }

        // Create product suggestion HTML
        function createProductSuggestion(product, query) {
            const discount = product.originalPrice > product.price ? 
                Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100) : 0;
            
            return `
                <div class="suggestion-item" data-value="${product.name}" data-type="product" data-id="${product.id}">
                    <div class="suggestion-icon">
                        <img src="${product.image}" alt="${product.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-main">${highlightMatch(product.name, query)}</div>
                        <div class="suggestion-sub">${product.brand} • ${product.category}</div>
                    </div>
                    <div class="suggestion-meta">
                        <div class="suggestion-price">${product.price}</div>
                        ${discount > 0 ? `<span class="badge bg-danger">${discount}% OFF</span>` : ''}
                        ${product.trending ? '<span class="suggestion-trending">Hot</span>' : ''}
                        <div class="suggestion-rating">
                            <i class="bi bi-star-fill text-warning"></i>
                            <span>${product.rating}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        // Create category suggestion HTML
        function createCategorySuggestion(category, query) {
            return `
                <div class="suggestion-item" data-value="${category.name}" data-type="category">
                    <div class="suggestion-icon">
                        <i class="bi ${category.icon}"></i>
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-main">${highlightMatch(category.name, query)}</div>
                        <div class="suggestion-sub">${category.count.toLocaleString()} products</div>
                    </div>
                </div>
            `;
        }

        // Create brand suggestion HTML
        function createBrandSuggestion(brand, query) {
            return `
                <div class="suggestion-item" data-value="${brand.name}" data-type="brand">
                    <div class="suggestion-icon">
                        <i class="bi ${brand.icon}"></i>
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-main">${highlightMatch(brand.name, query)}</div>
                        <div class="suggestion-sub">${brand.count} products</div>
                    </div>
                </div>
            `;
        }

        // Create quick filters
        function createQuickFilters(query) {
            const filters = [
                { name: 'On Sale', active: false },
                { name: 'Free Shipping', active: false },
                { name: 'Trending', active: false },
                { name: 'Top Rated', active: false }
            ];

            return `
                <div class="search-filters">
                    ${filters.map(filter => `
                        <span class="search-filter-chip ${filter.active ? 'active' : ''}" 
                              onclick="toggleFilter('${filter.name}', this)">
                            ${filter.name}
                        </span>
                    `).join('')}
                </div>
            `;
        }

        // Show no results
        function showNoResults(query) {
            const noResultsHTML = `
                <div class="search-no-results">
                    <i class="bi bi-search"></i>
                    <div class="fw-medium">No results found for "${query}"</div>
                    <div class="text-muted">Try different keywords or check spelling</div>
                </div>
            `;
            
            displaySuggestions(noResultsHTML);
        }

        // Display suggestions
        function displaySuggestions(html) {
            searchSuggestions.innerHTML = html;
            searchSuggestions.style.display = 'block';
            searchInput.classList.add('has-suggestions');
            currentSuggestionIndex = -1;

            // Add click handlers
            searchSuggestions.querySelectorAll('.suggestion-item').forEach(item => {
                item.addEventListener('click', () => selectSuggestion(item));
            });
        }

        // Hide suggestions
        function hideSuggestions() {
            searchSuggestions.style.display = 'none';
            searchInput.classList.remove('has-suggestions');
            currentSuggestionIndex = -1;
        }

        // Select suggestion
        function selectSuggestion(item) {
            const value = item.dataset.value;
            const type = item.dataset.type;
            
            searchInput.value = value;
            hideSuggestions();
            
            // Add to search history
            addToSearchHistory(value);
            
            // Perform search
            performSearch(value, type);
        }

        // Clear search
        function clearSearch() {
            searchInput.value = '';
            searchClear.classList.remove('show');
            hideSuggestions();
            searchInput.focus();
        }

        // Handle search submit
        function handleSearchSubmit() {
            const query = searchInput.value.trim();
            if (query) {
                addToSearchHistory(query);
                performSearch(query);
                hideSuggestions();
            }
        }

        // Add to search history
        function addToSearchHistory(query) {
            // Remove if already exists
            searchHistory = searchHistory.filter(item => item !== query);
            // Add to beginning
            searchHistory.unshift(query);
            // Keep only last 10 searches
            searchHistory = searchHistory.slice(0, 10);
            // Save to localStorage
            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
        }

        // Remove from search history
        function removeFromHistory(query) {
            searchHistory = searchHistory.filter(item => item !== query);
            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
            
            // Refresh suggestions if currently showing
            if (searchInput.value.trim().length <= 1) {
                showTrendingSuggestions();
            }
        }

        // Toggle filter
        function toggleFilter(filterName, element) {
            element.classList.toggle('active');
            const query = searchInput.value.trim();
            
            // In real app, this would update the search results
            showToast(`Filter "${filterName}" ${element.classList.contains('active') ? 'applied' : 'removed'}`, 'info');
        }

        // Toggle filter
        function toggleFilter(filterName, element) {
            element.classList.toggle('active');
            const query = searchInput.value.trim();
            
            // In real app, this would update the search results
            showToast(`Filter "${filterName}" ${element.classList.contains('active') ? 'applied' : 'removed'}`, 'info');
        }

        // Highlight matching text
        function highlightMatch(text, query) {
            if (!query) return text;
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }

        // Load Products from API
        currentPage = 1;
        const productsContainer = document.getElementById('productsContainer');
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        let hasMoreProducts = true;

        function createProductCard(product) {
            const discount = product.original_price && product.original_price > product.price ? 
                Math.round(((product.original_price - product.price) / product.original_price) * 100) : 0;
            
            // Amazon-style price display
            let priceDisplay = '';
            if (product.has_variations) {
                priceDisplay = `₹${new Intl.NumberFormat('en-IN').format(product.min_price)}`;
                if (product.max_price > product.min_price) {
                    priceDisplay += ` <small class="text-muted">onwards</small>`;
                }
            } else {
                priceDisplay = `₹${new Intl.NumberFormat('en-IN').format(product.price)}`;
            }
            
            return `
                <div class="col-lg-4 col-md-6">
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                            ${discount > 0 ? `<span class="discount-badge">${discount}% OFF</span>` : ''}
                            ${!product.in_stock ? '<span class="stock-badge out-of-stock">Out of Stock</span>' : ''}
                            <div class="product-overlay">
                                <div class="quick-actions">
                                    <button class="quick-action-btn" onclick="addToWishlist(${product.id})" ${!product.in_stock ? 'disabled' : ''}>
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    <a href="/products/${product.slug}" class="quick-action-btn">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            ${product.brand ? `<div class="product-brand">${product.brand}</div>` : ''}
                            <h5 class="product-title">
                                <a href="/products/${product.slug}" class="text-decoration-none">${product.name}</a>
                            </h5>
                            <div class="product-rating">
                                <div class="rating-stars">
                                    ${generateStars(product.rating)}
                                </div>
                                <span class="rating-text">${product.rating} (${product.reviews})</span>
                            </div>
                            <div class="product-price">
                                <span class="current-price">${priceDisplay}</span>
                                ${product.original_price && product.original_price > product.price ? `<span class="original-price">₹${new Intl.NumberFormat('en-IN').format(product.original_price)}</span>` : ''}
                            </div>
                            <div class="product-actions">
                                ${product.in_stock ? 
                                    `<a href="/products/${product.slug}" class="btn btn-primary btn-add-cart">
                                        <i class="bi bi-eye me-2"></i>View Product
                                    </a>` : 
                                    `<button class="btn btn-outline-secondary btn-add-cart" disabled>
                                        <i class="bi bi-x-circle me-2"></i>Out of Stock
                                    </button>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function generateStars(rating) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 !== 0;
            let stars = '';
            
            for (let i = 0; i < fullStars; i++) {
                stars += '<i class="bi bi-star-fill"></i>';
            }
            
            if (hasHalfStar) {
                stars += '<i class="bi bi-star-half"></i>';
            }
            
            const emptyStars = 5 - Math.ceil(rating);
            for (let i = 0; i < emptyStars; i++) {
                stars += '<i class="bi bi-star"></i>';
            }
            
            return stars;
        }

        async function loadProducts() {
            if (isLoading || !hasMoreProducts) {
                console.log('Stopping load - isLoading:', isLoading, 'hasMoreProducts:', hasMoreProducts);
                return;
            }
            
            isLoading = true;
            const btnText = loadMoreBtn.querySelector('.btn-text');
            const loading = loadMoreBtn.querySelector('.loading');
            
            if (btnText && loading) {
                btnText.classList.add('d-none');
                loading.classList.remove('d-none');
            }
            loadMoreBtn.disabled = true;

            try {
                const apiUrl = `/api/featured-products?page=${currentPage}&per_page=${productsPerPage}`;
                console.log('Fetching from API:', apiUrl);
                
                const response = await fetch(apiUrl);
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('API Error Response:', errorText);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }
                
                const data = await response.json();
                console.log('API Response:', data);

                if (data.products && data.products.length > 0) {
                    console.log('Found products:', data.products.length);
                    const productsHTML = data.products.map(createProductCard).join('');
                    productsContainer.insertAdjacentHTML('beforeend', productsHTML);
                    
                    // Animate new products
                    const newProducts = productsContainer.querySelectorAll('.col-lg-4:nth-last-child(-n+' + data.products.length + ')');
                    newProducts.forEach((product, index) => {
                        setTimeout(() => {
                            product.classList.add('fade-in-up');
                        }, index * 100);
                    });

                    currentPage++;
                    hasMoreProducts = data.has_more;
                    
                    if (!hasMoreProducts) {
                        loadMoreBtn.style.display = 'none';
                    }
                } else {
                    // No products found
                    if (currentPage === 1) {
                        productsContainer.innerHTML = `
                            <div class="col-12 text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                                    <h5>No Products Found</h5>
                                    <p>We're working on adding products. Please check back later!</p>
                                    <a href="/products" class="btn btn-primary mt-3">Browse All Products</a>
                                </div>
                            </div>
                        `;
                    }
                    loadMoreBtn.style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading products:', error);
                showToast('Failed to load products. Please try again.', 'danger');
                
                if (currentPage === 1) {
                    productsContainer.innerHTML = `
                        <div class="col-12 text-center py-5">
                            <div class="text-danger">
                                <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                                <h5>Error Loading Products</h5>
                                <p>There was an issue loading the products. Please refresh the page or try again later.</p>
                                <button class="btn btn-outline-primary mt-3" onclick="location.reload()">Refresh Page</button>
                            </div>
                        </div>
                    `;
                }
            } finally {
                isLoading = false;
                const btnText = loadMoreBtn.querySelector('.btn-text');
                const loading = loadMoreBtn.querySelector('.loading');
                
                if (btnText && loading) {
                    btnText.classList.remove('d-none');
                    loading.classList.add('d-none');
                }
                loadMoreBtn.disabled = false;
            }
        }

        loadMoreBtn.addEventListener('click', loadProducts);

        // Cart and Wishlist Functions
        function addToCart(productId) {
            const product = sampleProducts.find(p => p.id === productId);
            showToast(`${product.name} added to cart!`, 'success');
            updateCartBadge();
        }

        function addToWishlist(productId) {
            const product = sampleProducts.find(p => p.id === productId);
            showToast(`${product.name} added to wishlist!`, 'info');
            updateWishlistBadge();
        }

        function updateCartBadge() {
            const cartBadge = document.querySelector('.nav-icon .badge');
            if (cartBadge) {
                const currentCount = parseInt(cartBadge.textContent);
                cartBadge.textContent = currentCount + 1;
            }
        }

        function updateWishlistBadge() {
            const wishlistBadge = document.querySelectorAll('.nav-icon .badge')[0];
            if (wishlistBadge) {
                const currentCount = parseInt(wishlistBadge.textContent);
                wishlistBadge.textContent = currentCount + 1;
            }
        }

        // Toast Notification Function
        function showToast(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container');
            const toastId = 'toast-' + Date.now();
            
            const toastHTML = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert" id="${toastId}">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-${getToastIcon(type)} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }

        function getToastIcon(type) {
            switch (type) {
                case 'success': return 'check-circle';
                case 'danger': return 'exclamation-triangle';
                case 'warning': return 'exclamation-triangle';
                case 'info': return 'info-circle';
                default: return 'info-circle';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
            
            // Add scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in-up');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.feature-card').forEach(card => {
                observer.observe(card);
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>