@extends('admin.layout')

@section('title', 'View Attribute: ' . $attribute->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 text-gray-800">Attribute: {{ $attribute->name }}</h1>
                <div>
                    <a href="{{ route('admin.attributes.edit', $attribute) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.attributes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Attributes
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Attribute Details -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Attribute Details</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $attribute->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $attribute->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $attribute->type === 'color' ? 'info' : ($attribute->type === 'size' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($attribute->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Required:</strong></td>
                                    <td>
                                        @if($attribute->is_required)
                                            <span class="badge badge-danger">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Filterable:</strong></td>
                                    <td>
                                        @if($attribute->is_filterable)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $attribute->created_at->format('M d, Y H:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated:</strong></td>
                                    <td>{{ $attribute->updated_at->format('M d, Y H:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Attribute Values -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Attribute Values ({{ $attribute->attributeValues->count() }})
                            </h6>
                            <a href="{{ route('admin.attribute-values.create', ['attribute_id' => $attribute->id]) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Add Value
                            </a>
                        </div>
                        <div class="card-body">
                            @if($attribute->attributeValues->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Value</th>
                                                @if($attribute->type === 'color')
                                                    <th>Color</th>
                                                @endif
                                                <th>Default</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($attribute->attributeValues as $value)
                                                <tr>
                                                    <td>{{ $value->value }}</td>
                                                    @if($attribute->type === 'color')
                                                        <td>
                                                            @if($value->hex_color)
                                                                <div style="display: inline-flex; align-items: center;">
                                                                    <div style="width: 20px; height: 20px; background-color: {{ $value->hex_color }}; border: 1px solid #ddd; border-radius: 3px; margin-right: 8px;"></div>
                                                                    <span class="small">{{ $value->hex_color }}</span>
                                                                </div>
                                                            @else
                                                                <span class="text-muted">No color</span>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td>
                                                        @if($value->is_default)
                                                            <span class="badge badge-success">Yes</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('admin.attribute-values.edit', $value) }}" 
                                                               class="btn btn-warning btn-sm" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-danger btn-sm" 
                                                                    onclick="confirmDeleteValue({{ $value->id }}, '{{ $value->value }}')" 
                                                                    title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-muted mb-3">No values created for this attribute yet.</p>
                                    <a href="{{ route('admin.attribute-values.create', ['attribute_id' => $attribute->id]) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create First Value
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Value Confirmation Modal -->
<div class="modal fade" id="deleteValueModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the value "<span id="deleteValueName"></span>"?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteValueForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDeleteValue(id, name) {
    document.getElementById('deleteValueName').textContent = name;
    document.getElementById('deleteValueForm').action = `/admin/attribute-values/${id}`;
    $('#deleteValueModal').modal('show');
}
</script>
@endpush