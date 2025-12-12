@extends('admin.layout')

@section('title', 'Create Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Create New Sale</h1>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Sales
                </a>
            </div>

            <form action="{{ route('admin.sales.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Sale Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Sale Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="banner_image">Banner Image</label>
                                    <input type="file" class="form-control-file @error('banner_image') is-invalid @enderror" 
                                           id="banner_image" name="banner_image" accept="image/*">
                                    @error('banner_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                                   id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                            @error('start_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_date">End Date <span class="text-danger">*</span></label>
                                            <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                                   id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                            @error('end_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Discount Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="type">Discount Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('type') is-invalid @enderror" 
                                                    id="type" name="type" required>
                                                <option value="">Select Type</option>
                                                <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                                <option value="bogo" {{ old('type') === 'bogo' ? 'selected' : '' }}>Buy One Get One</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="discount_value">Discount Value <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control @error('discount_value') is-invalid @enderror" 
                                                   id="discount_value" name="discount_value" value="{{ old('discount_value') }}" required>
                                            @error('discount_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="max_discount">Maximum Discount (₹)</label>
                                            <input type="number" step="0.01" class="form-control @error('max_discount') is-invalid @enderror" 
                                                   id="max_discount" name="max_discount" value="{{ old('max_discount') }}">
                                            @error('max_discount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="min_order_value">Minimum Order Value (₹)</label>
                                            <input type="number" step="0.01" class="form-control @error('min_order_value') is-invalid @enderror" 
                                                   id="min_order_value" name="min_order_value" value="{{ old('min_order_value') }}">
                                            @error('min_order_value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="usage_limit">Usage Limit</label>
                                    <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" 
                                           id="usage_limit" name="usage_limit" value="{{ old('usage_limit') }}">
                                    <small class="form-text text-muted">Leave empty for unlimited usage</small>
                                    @error('usage_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Applicable Products</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Select Products</label>
                                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #e3e6f0; padding: 10px;">
                                        @foreach($products as $product)
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="product_{{ $product->id }}" name="products[]" value="{{ $product->id }}"
                                                   {{ in_array($product->id, old('products', [])) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="product_{{ $product->id }}">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        // Get image using the same approach as new-arrivals
                                                        $firstVariation = $product->variations->first();
                                                        $variationImage = $firstVariation ? $firstVariation->images->first() : null;
                                                        $productImage = $product->images->first();
                                                        $selectedImage = $variationImage ?? $productImage;
                                                        
                                                        if ($selectedImage) {
                                                            $imageUrl = str_starts_with($selectedImage->path, 'http') 
                                                                ? $selectedImage->path 
                                                                : $selectedImage->getThumbnailUrl(300);
                                                        } else {
                                                            $imageUrl = asset('images/no-image.png');
                                                        }
                                                    @endphp
                                                    
                                                    <img src="{{ $imageUrl }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="mr-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;"
                                                         onerror="this.src='{{ asset('images/no-image.png') }}'">
                                                    
                                                    <div>
                                                        <div class="font-weight-bold">{{ $product->name }}</div>
                                                        <small class="text-muted">
                                                            @if($product->category)
                                                                <span class="badge badge-secondary mr-1">{{ $product->category->name }}</span>
                                                            @endif
                                                            ₹{{ number_format($product->price, 2) }}
                                                            @if($product->variations->count() > 1)
                                                                <span class="text-info ml-1">({{ $product->variations->count() }} variants)</span>
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Applicable Categories</label>
                                    <select class="form-control" name="applicable_categories[]" multiple>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ in_array($category->id, old('applicable_categories', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Applicable Brands</label>
                                    <select class="form-control" name="applicable_brands[]" multiple>
                                        @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" 
                                                {{ in_array($brand->id, old('applicable_brands', [])) ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-body text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-lg"></i> Create Sale
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection