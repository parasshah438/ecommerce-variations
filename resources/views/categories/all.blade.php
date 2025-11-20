@extends('layouts.frontend')

@section('title', 'All Categories')

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <i class="fas fa-home me-1"></i>Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-sitemap me-1"></i>All Categories
            </li>
        </ol>
    </div>
</nav>

<div class="container-fluid py-5">
    <div class="row">
        <div class="col-12">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Our Categories</h1>
                <p class="lead text-muted">Explore our complete range of product categories</p>
                <hr class="mx-auto" style="width: 100px; height: 3px; background: linear-gradient(to right, #007bff, #6f42c1);">
                
                @if($categories->count() > 0)
                    <!-- Search Bar -->
                    <div class="row justify-content-center mt-4 mb-4">
                        <div class="col-lg-6">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-primary text-white">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="categorySearch" 
                                       placeholder="Search categories..."
                                       style="border-left: none;">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="row text-center g-3">
                                <div class="col-6 col-md-3">
                                    <div class="stats-card">
                                        <h3 class="fw-bold text-primary">{{ $categories->count() }}</h3>
                                        <p class="text-muted small mb-0">Main Categories</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stats-card">
                                        <h3 class="fw-bold text-success">{{ $categories->sum(function($cat) { return $cat->children->count(); }) }}</h3>
                                        <p class="text-muted small mb-0">Subcategories</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stats-card">
                                        <h3 class="fw-bold text-warning">{{ $categories->sum('products_count') }}</h3>
                                        <p class="text-muted small mb-0">Products</p>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stats-card">
                                        <h3 class="fw-bold text-info">{{ $categories->sum(function($cat) { return $cat->children->sum(function($subCat) { return $subCat->children->count(); }); }) }}</h3>
                                        <p class="text-muted small mb-0">Sub-subcategories</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($categories->count() > 0)
        <div class="row g-4">
            @foreach($categories as $category)
                <div class="col-12">
                    <div class="category-tree-item">
                        <!-- Main Category Card -->
                        <div class="card border-0 shadow-lg mb-4 category-main">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-3 mb-md-0">
                                        <div class="category-image-wrapper">
                                            <img src="{{ $category->getThumbnailUrl(200) }}" 
                                                 alt="{{ $category->name }}" 
                                                 class="img-fluid rounded-3 category-main-image"
                                                 style="max-height: 150px; object-fit: cover; width: 100%;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h2 class="card-title h3 mb-3 text-primary fw-bold">
                                            <i class="fas fa-folder-open me-2"></i>{{ $category->name }}
                                        </h2>
                                        @if($category->description)
                                            <p class="card-text text-muted mb-3">{{ $category->description }}</p>
                                        @endif
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-primary-subtle text-primary px-3 py-2">
                                                <i class="fas fa-tag me-1"></i>Main Category
                                            </span>
                                            @if($category->products_count > 0)
                                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                                    <i class="fas fa-box me-1"></i>{{ $category->products_count }} Products
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                        <a href="{{ route('category.products', $category->slug) }}" 
                                           class="btn btn-primary btn-lg rounded-pill px-4 py-2 shadow-sm">
                                            <i class="fas fa-arrow-right me-2"></i>View Products
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subcategories -->
                        @if($category->children->count() > 0)
                            <div class="subcategories-section ms-4">
                                <h4 class="text-muted mb-4 fw-semibold">
                                    <i class="fas fa-sitemap me-2"></i>Subcategories of {{ $category->name }}
                                </h4>
                                
                                <div class="row g-3">
                                    @foreach($category->children as $subcategory)
                                        <div class="col-lg-6 col-xl-4">
                                            <div class="card border-0 bg-light subcategory-card h-100 hover-lift">
                                                <div class="card-body p-4">
                                                    <div class="row align-items-center">
                                                        <div class="col-4">
                                                            <div class="subcategory-image-wrapper">
                                                                <img src="{{ $subcategory->getThumbnailUrl(100) }}" 
                                                                     alt="{{ $subcategory->name }}" 
                                                                     class="img-fluid rounded-2"
                                                                     style="height: 60px; width: 100%; object-fit: cover;">
                                                            </div>
                                                        </div>
                                                        <div class="col-8">
                                                            <h5 class="card-title mb-2 fw-semibold text-dark">
                                                                {{ $subcategory->name }}
                                                            </h5>
                                                            @if($subcategory->products_count > 0)
                                                                <small class="text-muted">
                                                                    <i class="fas fa-cube me-1"></i>{{ $subcategory->products_count }} items
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <a href="{{ route('category.products', $subcategory->slug) }}" 
                                                           class="btn btn-outline-primary btn-sm rounded-pill w-100">
                                                            <i class="fas fa-eye me-1"></i>Browse
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Sub-subcategories (Level 3) -->
                                            @if($subcategory->children->count() > 0)
                                                <div class="mt-3 ms-3">
                                                    <div class="sub-subcategories">
                                                        <h6 class="text-muted small mb-2 fw-semibold">
                                                            <i class="fas fa-arrow-right me-1"></i>Sub-categories:
                                                        </h6>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            @foreach($subcategory->children as $subSubcategory)
                                                                <a href="{{ route('category.products', $subSubcategory->slug) }}" 
                                                                   class="badge bg-secondary-subtle text-secondary text-decoration-none px-2 py-1 rounded-pill hover-badge">
                                                                    <i class="fas fa-angle-right me-1"></i>{{ $subSubcategory->name }}
                                                                    @if($subSubcategory->products_count > 0)
                                                                        <span class="ms-1">({{ $subSubcategory->products_count }})</span>
                                                                    @endif
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-folder-open fa-5x text-muted"></i>
                    </div>
                    <h3 class="text-muted mb-3">No Categories Available</h3>
                    <p class="text-muted">Categories will appear here once they are added to the system.</p>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
/* Custom Styles for Category Tree */
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.category-main {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-left: 5px solid #007bff;
}

.category-main-image {
    border: 3px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.subcategory-card {
    background: #fff;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.subcategory-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 15px rgba(0,123,255,0.1) !important;
}

.subcategories-section {
    position: relative;
}

.subcategories-section::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #6f42c1);
    opacity: 0.3;
}

.hover-badge {
    transition: all 0.2s ease;
}

.hover-badge:hover {
    background: #007bff !important;
    color: #fff !important;
    transform: scale(1.05);
}

.sub-subcategories {
    background: rgba(0,123,255,0.05);
    padding: 10px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.stats-card {
    background: #fff;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category-main .row {
        text-align: center;
    }
    
    .subcategories-section {
        margin-left: 0 !important;
        margin-top: 2rem;
    }
    
    .subcategories-section::before {
        display: none;
    }
    
    .sub-subcategories {
        margin-left: 0 !important;
        margin-top: 1rem;
    }
    
    .display-4 {
        font-size: 2rem !important;
    }
    
    .category-main .card-body {
        padding: 1.5rem !important;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .category-main-image {
        max-height: 100px !important;
    }
    
    .btn-lg {
        font-size: 0.9rem !important;
        padding: 8px 16px !important;
    }
}

/* Loading animation */
.category-tree-item {
    animation: fadeInUp 0.6s ease forwards;
}

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

/* Add staggered animation delay for each category */
.category-tree-item:nth-child(1) { animation-delay: 0.1s; }
.category-tree-item:nth-child(2) { animation-delay: 0.2s; }
.category-tree-item:nth-child(3) { animation-delay: 0.3s; }
.category-tree-item:nth-child(4) { animation-delay: 0.4s; }
.category-tree-item:nth-child(5) { animation-delay: 0.5s; }
</style>

<!-- Add Font Awesome if not already included -->
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('categorySearch');
    const clearBtn = document.getElementById('clearSearch');
    const categoryItems = document.querySelectorAll('.category-tree-item');
    
    function filterCategories() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        categoryItems.forEach(item => {
            const categoryName = item.querySelector('.category-main .card-title')?.textContent.toLowerCase() || '';
            const subcategoryNames = Array.from(item.querySelectorAll('.subcategory-card .card-title')).map(el => el.textContent.toLowerCase()).join(' ');
            const subSubcategoryNames = Array.from(item.querySelectorAll('.hover-badge')).map(el => el.textContent.toLowerCase()).join(' ');
            
            const allText = categoryName + ' ' + subcategoryNames + ' ' + subSubcategoryNames;
            
            if (searchTerm === '' || allText.includes(searchTerm)) {
                item.style.display = 'block';
                item.style.animation = 'fadeInUp 0.3s ease forwards';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show "no results" message if needed
        const visibleItems = Array.from(categoryItems).filter(item => item.style.display !== 'none');
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (visibleItems.length === 0 && searchTerm !== '') {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.className = 'text-center py-5';
                noResultsMsg.innerHTML = `
                    <div class="mb-4">
                        <i class="fas fa-search fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No categories found</h4>
                    <p class="text-muted">Try searching with different keywords</p>
                `;
                document.querySelector('.container-fluid .row:last-child').appendChild(noResultsMsg);
            }
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    
    // Search functionality
    searchInput.addEventListener('input', filterCategories);
    
    // Clear search
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
    });
    
    // Enter key search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterCategories();
        }
    });
});
</script>
@endsection
@endsection