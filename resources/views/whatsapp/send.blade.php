@extends('layouts.app')

@section('title', 'Send WhatsApp Message')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Send WhatsApp Message</h6>
                </div>
                <div class="card-body">
                    <!-- Message Type Tabs -->
                    <ul class="nav nav-tabs" id="messageTypeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="text-tab" data-bs-toggle="tab" href="#text" role="tab">
                                <i class="bi bi-chat-text me-1"></i> Text Message
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="image-tab" data-bs-toggle="tab" href="#image" role="tab">
                                <i class="bi bi-image me-1"></i> Image
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="document-tab" data-bs-toggle="tab" href="#document" role="tab">
                                <i class="bi bi-file-text me-1"></i> Document
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="audio-tab" data-bs-toggle="tab" href="#audio" role="tab">
                                <i class="bi bi-mic me-1"></i> Audio
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#contact" role="tab">
                                <i class="bi bi-person-circle me-1"></i> Contact
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="messageTypeTabsContent">
                        <!-- Text Message Tab -->
                        <div class="tab-pane fade show active" id="text" role="tabpanel">
                            <form id="textMessageForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="text_phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="text_phone" name="phone" placeholder="+91 9876543210" required>
                                        <div class="form-text">Enter phone number with country code</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="text_contact_select" class="form-label">Or Select from Contacts</label>
                                        <select class="form-select" id="text_contact_select">
                                            <option value="">Choose a contact...</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->phone }}" data-name="{{ $contact->name }}">
                                                    {{ $contact->name }} ({{ $contact->phone }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="text_message" class="form-label">Message</label>
                                    <textarea class="form-control" id="text_message" name="message" rows="4" maxlength="1000" placeholder="Enter your message here..." required></textarea>
                                    <div class="form-text">
                                        <span id="text_char_count">0</span>/1000 characters
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Send Message
                                </button>
                            </form>
                        </div>

                        <!-- Image Tab -->
                        <div class="tab-pane fade" id="image" role="tabpanel">
                            <form id="imageMessageForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="image_phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="image_phone" name="phone" placeholder="+91 9876543210" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="image_contact_select" class="form-label">Or Select from Contacts</label>
                                        <select class="form-select" id="image_contact_select">
                                            <option value="">Choose a contact...</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->phone }}">{{ $contact->name }} ({{ $contact->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="image_file" class="form-label">Select Image</label>
                                    <input type="file" class="form-control" id="image_file" name="image" accept="image/*" required>
                                    <div class="form-text">Maximum file size: 10MB. Supported formats: JPG, PNG, GIF, WebP</div>
                                </div>
                                <div class="mb-3">
                                    <label for="image_caption" class="form-label">Caption (Optional)</label>
                                    <textarea class="form-control" id="image_caption" name="caption" rows="2" maxlength="500" placeholder="Add a caption..."></textarea>
                                    <div class="form-text">
                                        <span id="image_char_count">0</span>/500 characters
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div id="image_preview" class="text-center" style="display: none;">
                                        <img id="preview_img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-image me-1"></i> Send Image
                                </button>
                            </form>
                        </div>

                        <!-- Document Tab -->
                        <div class="tab-pane fade" id="document" role="tabpanel">
                            <form id="documentMessageForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="document_phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="document_phone" name="phone" placeholder="+91 9876543210" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="document_contact_select" class="form-label">Or Select from Contacts</label>
                                        <select class="form-select" id="document_contact_select">
                                            <option value="">Choose a contact...</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->phone }}">{{ $contact->name }} ({{ $contact->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="document_file" class="form-label">Select Document</label>
                                    <input type="file" class="form-control" id="document_file" name="document" required>
                                    <div class="form-text">Maximum file size: 50MB. Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT</div>
                                </div>
                                <div class="mb-3">
                                    <label for="document_filename" class="form-label">Filename (Optional)</label>
                                    <input type="text" class="form-control" id="document_filename" name="filename" placeholder="Enter custom filename">
                                    <div class="form-text">Leave empty to use original filename</div>
                                </div>
                                <button type="submit" class="btn btn-info">
                                    <i class="bi bi-file-text me-1"></i> Send Document
                                </button>
                            </form>
                        </div>

                        <!-- Audio Tab -->
                        <div class="tab-pane fade" id="audio" role="tabpanel">
                            <form id="audioMessageForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="audio_phone" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="audio_phone" name="phone" placeholder="+91 9876543210" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="audio_contact_select" class="form-label">Or Select from Contacts</label>
                                        <select class="form-select" id="audio_contact_select">
                                            <option value="">Choose a contact...</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->phone }}">{{ $contact->name }} ({{ $contact->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="audio_file" class="form-label">Select Audio File</label>
                                    <input type="file" class="form-control" id="audio_file" name="audio" accept="audio/*" required>
                                    <div class="form-text">Maximum file size: 10MB. Supported formats: MP3, WAV, OGG, AAC, M4A</div>
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-mic me-1"></i> Send Audio
                                </button>
                            </form>
                        </div>

                        <!-- Contact Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <form id="contactMessageForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone" class="form-label">Send to Phone Number</label>
                                        <input type="text" class="form-control" id="contact_phone" name="phone" placeholder="+91 9876543210" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_select_recipient" class="form-label">Or Select Recipient</label>
                                        <select class="form-select" id="contact_select_recipient">
                                            <option value="">Choose a contact...</option>
                                            @foreach($contacts as $contact)
                                                <option value="{{ $contact->phone }}">{{ $contact->name }} ({{ $contact->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_name" class="form-label">Contact Name</label>
                                        <input type="text" class="form-control" id="contact_name" name="contact_name" placeholder="John Doe" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone_share" class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" id="contact_phone_share" name="contact_phone" placeholder="+91 9876543210" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-secondary">
                                    <i class="bi bi-person-circle me-1"></i> Send Contact
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Templates -->
            @if($templates->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Message Templates</h6>
                </div>
                <div class="card-body">
                    @foreach($templates as $template)
                    <div class="card mb-2 template-card" style="cursor: pointer;" data-template="{{ $template->content }}">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1">{{ $template->name }}</h6>
                            <p class="card-text small text-muted">{{ Str::limit($template->content, 100) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Contacts -->
            @if($contacts->count() > 0)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Contacts</h6>
                </div>
                <div class="card-body">
                    @foreach($contacts->take(10) as $contact)
                    <div class="d-flex align-items-center mb-2 contact-item" style="cursor: pointer;" 
                         data-phone="{{ $contact->phone }}" data-name="{{ $contact->name }}">
                        <div class="avatar-sm me-2">
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                                 style="width: 32px; height: 32px;">
                                {{ strtoupper(substr($contact->name, 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold">{{ $contact->name }}</div>
                            <div class="text-muted small">{{ $contact->phone }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<style>
.template-card:hover {
    background-color: #f8f9fc;
    transform: translateY(-1px);
    transition: all 0.2s;
}
.contact-item:hover {
    background-color: #f8f9fc;
    border-radius: 5px;
    padding: 5px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Character counters
    $('#text_message').on('input', function() {
        $('#text_char_count').text($(this).val().length);
    });
    
    $('#image_caption').on('input', function() {
        $('#image_char_count').text($(this).val().length);
    });
    
    // Image preview
    $('#image_file').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview_img').attr('src', e.target.result);
                $('#image_preview').show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Contact selection
    $('.contact-item').on('click', function() {
        const phone = $(this).data('phone');
        const name = $(this).data('name');
        const activeTab = $('.tab-pane.active');
        activeTab.find('input[name="phone"]').val(phone);
    });
    
    // Template selection
    $('.template-card').on('click', function() {
        const template = $(this).data('template');
        $('#text_message').val(template);
        $('#text_char_count').text(template.length);
        $('#text-tab').click(); // Switch to text tab
    });
    
    // Contact dropdown selection
    $('select[id$="_contact_select"]').on('change', function() {
        const selectedPhone = $(this).val();
        if (selectedPhone) {
            const tabId = $(this).attr('id').split('_')[0];
            $(`#${tabId}_phone`).val(selectedPhone);
        }
    });
    
    // Form submissions
    $('#textMessageForm').on('submit', function(e) {
        e.preventDefault();
        sendMessage('text', $(this));
    });
    
    $('#imageMessageForm').on('submit', function(e) {
        e.preventDefault();
        sendMessage('image', $(this));
    });
    
    $('#documentMessageForm').on('submit', function(e) {
        e.preventDefault();
        sendMessage('document', $(this));
    });
    
    $('#audioMessageForm').on('submit', function(e) {
        e.preventDefault();
        sendMessage('audio', $(this));
    });
    
    $('#contactMessageForm').on('submit', function(e) {
        e.preventDefault();
        sendMessage('contact', $(this));
    });
    
    function sendMessage(type, form) {
        const formData = new FormData(form[0]);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');
        
        // Determine endpoint
        let endpoint;
        switch(type) {
            case 'text':
                endpoint = '{{ route("whatsapp.send.text") }}';
                break;
            case 'image':
                endpoint = '{{ route("whatsapp.send.image") }}';
                break;
            case 'document':
                endpoint = '{{ route("whatsapp.send.document") }}';
                break;
            case 'audio':
                endpoint = '{{ route("whatsapp.send.audio") }}';
                break;
            case 'contact':
                endpoint = '{{ route("whatsapp.send.contact") }}';
                break;
        }
        
        $.ajax({
            url: endpoint,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Message sent successfully!');
                    form[0].reset();
                    if (type === 'image') {
                        $('#image_preview').hide();
                    }
                    $('#text_char_count, #image_char_count').text('0');
                } else {
                    showAlert('error', response.message || 'Failed to send message');
                }
            },
            error: function(xhr) {
                let message = 'Failed to send message';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join(', ');
                }
                showAlert('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at top of main content
        $('.card-body').first().prepend(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
@endsection