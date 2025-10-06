@extends('admin.layout')

@section('title', 'View Attribute Value: ' . $attributeValue->value)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Attribute Value: {{ $attributeValue->value }}</h1>
                <div>
                    <a href="{{ route('admin.attribute-values.edit', $attributeValue) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.attribute-values.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Values
                    </a>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attribute Value Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>ID:</strong></td>
                            <td>{{ $attributeValue->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Attribute:</strong></td>
                            <td>
                                <a href="{{ route('admin.attributes.show', $attributeValue->attribute) }}" 
                                   class="text-decoration-none">
                                    {{ $attributeValue->attribute->name }}
                                </a>
                                <span class="badge badge-{{ $attributeValue->attribute->type === 'color' ? 'info' : ($attributeValue->attribute->type === 'size' ? 'warning' : 'secondary') }} ml-2">
                                    {{ ucfirst($attributeValue->attribute->type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Value:</strong></td>
                            <td>{{ $attributeValue->value }}</td>
                        </tr>
                        @if($attributeValue->hex_color)
                        <tr>
                            <td><strong>Color:</strong></td>
                            <td>
                                <div style="display: inline-flex; align-items: center;">
                                    <div style="width: 40px; height: 40px; background-color: {{ $attributeValue->hex_color }}; border: 1px solid #ddd; border-radius: 6px; margin-right: 15px;"></div>
                                    <span class="h6 mb-0">{{ $attributeValue->hex_color }}</span>
                                </div>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Default Value:</strong></td>
                            <td>
                                @if($attributeValue->is_default)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $attributeValue->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td>{{ $attributeValue->updated_at->format('M d, Y H:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Related Information -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Usage Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Product Variations</h6>
                            @php
                                $variationsCount = $attributeValue->productVariations()->count();
                            @endphp
                            @if($variationsCount > 0)
                                <p class="text-success">
                                    <i class="fas fa-check-circle"></i> 
                                    Used in {{ $variationsCount }} product variation(s)
                                </p>
                            @else
                                <p class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Not used in any product variations yet
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Attribute Information</h6>
                            <p class="small text-muted">
                                Belongs to <strong>{{ $attributeValue->attribute->name }}</strong> attribute
                                @if($attributeValue->attribute->is_required)
                                    <span class="badge badge-danger badge-sm ml-1">Required</span>
                                @endif
                                @if($attributeValue->attribute->is_filterable)
                                    <span class="badge badge-success badge-sm ml-1">Filterable</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($variationsCount == 0)
                        <div class="alert alert-info mt-3">
                            <h6 class="alert-heading">Safe to Delete</h6>
                            <p class="mb-0">This attribute value is not being used by any product variations, so it can be safely deleted if no longer needed.</p>
                        </div>
                    @else
                        <div class="alert alert-warning mt-3">
                            <h6 class="alert-heading">In Use</h6>
                            <p class="mb-0">This attribute value is currently being used by {{ $variationsCount }} product variation(s). Deleting it may affect those products.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection