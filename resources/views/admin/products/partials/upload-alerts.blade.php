@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('upload_info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong>Upload Information:</strong> {{ session('upload_info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="alert alert-warning" role="alert">
    <strong>📁 Upload Limits:</strong> 
    Maximum file size per image: <strong>~2MB</strong> (due to server configuration). 
    Files larger than this will be skipped automatically. 
    <a href="{{ route('debug.limits-test') }}" target="_blank" class="alert-link">Test your files here</a>
</div>