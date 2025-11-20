{{-- Advanced Form Components for Admin Dashboard --}}

{{-- Rich Text Editor Component --}}
<div class="form-group mb-4" data-component="rich-editor">
    <label class="form-label fw-semibold">{{ $label ?? 'Content' }}</label>
    <div class="rich-editor-container">
        <div class="editor-toolbar">
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="bold" title="Bold">
                    <i class="fas fa-bold"></i>
                </button>
                <button type="button" class="toolbar-btn" data-command="italic" title="Italic">
                    <i class="fas fa-italic"></i>
                </button>
                <button type="button" class="toolbar-btn" data-command="underline" title="Underline">
                    <i class="fas fa-underline"></i>
                </button>
            </div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                    <i class="fas fa-list-ul"></i>
                </button>
                <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                    <i class="fas fa-list-ol"></i>
                </button>
            </div>
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link">
                    <i class="fas fa-link"></i>
                </button>
                <button type="button" class="toolbar-btn" data-command="insertImage" title="Insert Image">
                    <i class="fas fa-image"></i>
                </button>
            </div>
            <div class="toolbar-group">
                <select class="form-select toolbar-select" data-command="formatBlock">
                    <option value="p">Paragraph</option>
                    <option value="h1">Heading 1</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                </select>
            </div>
        </div>
        <div class="editor-content" contenteditable="true" data-placeholder="Start typing your content...">
            {!! $value ?? '' !!}
        </div>
        <input type="hidden" name="{{ $name ?? 'content' }}" class="editor-input">
    </div>
</div>

{{-- File Upload Component with Drag & Drop --}}
<div class="form-group mb-4" data-component="file-upload">
    <label class="form-label fw-semibold">{{ $label ?? 'Upload Files' }}</label>
    <div class="file-upload-container">
        <div class="file-drop-zone" id="fileDropZone">
            <div class="drop-zone-content">
                <i class="fas fa-cloud-upload-alt drop-icon"></i>
                <h6>Drag & Drop files here</h6>
                <p class="text-muted">or click to browse</p>
                <button type="button" class="btn btn-outline-primary btn-sm" id="browseFiles">
                    <i class="fas fa-folder-open me-2"></i>Browse Files
                </button>
            </div>
        </div>
        <input type="file" id="fileInput" class="d-none" multiple 
               accept="{{ $accept ?? '*/*' }}" 
               name="{{ $name ?? 'files[]' }}">
        
        <div class="file-list mt-3" id="fileList"></div>
    </div>
</div>

{{-- Image Cropping Component --}}
<div class="form-group mb-4" data-component="image-crop">
    <label class="form-label fw-semibold">{{ $label ?? 'Upload & Crop Image' }}</label>
    <div class="image-crop-container">
        <div class="crop-upload-area" id="cropUploadArea">
            <div class="crop-drop-zone">
                <i class="fas fa-image crop-icon"></i>
                <h6>Upload Image to Crop</h6>
                <button type="button" class="btn btn-outline-primary btn-sm" id="selectImage">
                    Select Image
                </button>
            </div>
        </div>
        
        <div class="crop-editor d-none" id="cropEditor">
            <div class="crop-canvas-container">
                <canvas id="cropCanvas"></canvas>
            </div>
            <div class="crop-controls mt-3">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Aspect Ratio</label>
                        <select class="form-select" id="aspectRatio">
                            <option value="free">Free</option>
                            <option value="1">Square (1:1)</option>
                            <option value="1.333">Landscape (4:3)</option>
                            <option value="1.777">Widescreen (16:9)</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" class="btn btn-success me-2" id="applyCrop">
                            <i class="fas fa-check me-2"></i>Apply Crop
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="resetCrop">
                            <i class="fas fa-undo me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cropped-preview d-none" id="croppedPreview">
            <h6>Cropped Image Preview:</h6>
            <img id="previewImage" class="img-thumbnail" style="max-width: 200px;">
            <input type="hidden" name="{{ $name ?? 'cropped_image' }}" id="croppedImageData">
        </div>
        
        <input type="file" id="imageInput" class="d-none" accept="image/*">
    </div>
</div>

{{-- Advanced Color Picker --}}
<div class="form-group mb-4" data-component="color-picker">
    <label class="form-label fw-semibold">{{ $label ?? 'Choose Color' }}</label>
    <div class="color-picker-container">
        <div class="color-input-group">
            <div class="color-preview" id="colorPreview" style="background-color: {{ $value ?? '#667eea' }}"></div>
            <input type="text" class="form-control color-input" 
                   name="{{ $name ?? 'color' }}" 
                   value="{{ $value ?? '#667eea' }}" 
                   id="colorInput" 
                   placeholder="#000000">
            <button type="button" class="btn btn-outline-secondary" id="openColorPicker">
                <i class="fas fa-palette"></i>
            </button>
        </div>
        
        <div class="color-picker-panel d-none" id="colorPickerPanel">
            <div class="color-picker-header">
                <h6 class="mb-0">Color Picker</h6>
                <button type="button" class="btn-close" id="closeColorPicker"></button>
            </div>
            
            <div class="color-picker-body">
                <div class="color-canvas-container">
                    <canvas id="colorCanvas" width="200" height="150"></canvas>
                    <div class="color-cursor" id="colorCursor"></div>
                </div>
                
                <div class="color-slider-container mt-3">
                    <input type="range" class="form-range color-slider" 
                           id="hueSlider" min="0" max="360" value="0">
                </div>
                
                <div class="color-presets mt-3">
                    <div class="preset-colors">
                        <div class="preset-color" data-color="#667eea"></div>
                        <div class="preset-color" data-color="#764ba2"></div>
                        <div class="preset-color" data-color="#f093fb"></div>
                        <div class="preset-color" data-color="#f5576c"></div>
                        <div class="preset-color" data-color="#4facfe"></div>
                        <div class="preset-color" data-color="#43e97b"></div>
                        <div class="preset-color" data-color="#fa709a"></div>
                        <div class="preset-color" data-color="#fee140"></div>
                    </div>
                </div>
                
                <div class="color-values mt-3">
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label small">HEX</label>
                            <input type="text" class="form-control form-control-sm" id="hexValue">
                        </div>
                        <div class="col-4">
                            <label class="form-label small">RGB</label>
                            <input type="text" class="form-control form-control-sm" id="rgbValue" readonly>
                        </div>
                        <div class="col-4">
                            <label class="form-label small">HSL</label>
                            <input type="text" class="form-control form-control-sm" id="hslValue" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Advanced Date/Time Picker --}}
<div class="form-group mb-4" data-component="datetime-picker">
    <label class="form-label fw-semibold">{{ $label ?? 'Select Date & Time' }}</label>
    <div class="datetime-picker-container">
        <div class="datetime-input-group">
            <input type="text" class="form-control datetime-input" 
                   name="{{ $name ?? 'datetime' }}" 
                   value="{{ $value ?? '' }}" 
                   placeholder="Select date and time..." 
                   readonly>
            <button type="button" class="btn btn-outline-secondary" id="openDatePicker">
                <i class="fas fa-calendar-alt"></i>
            </button>
        </div>
        
        <div class="datetime-picker-panel d-none" id="datetimePickerPanel">
            <div class="picker-header">
                <h6 class="mb-0">Select Date & Time</h6>
                <button type="button" class="btn-close" id="closeDatePicker"></button>
            </div>
            
            <div class="picker-body">
                <div class="calendar-container">
                    <div class="calendar-header">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="prevMonth">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="calendar-title" id="calendarTitle"></span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="nextMonth">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <div class="calendar-grid" id="calendarGrid"></div>
                </div>
                
                <div class="time-picker mt-3">
                    <div class="row">
                        <div class="col-4">
                            <label class="form-label small">Hour</label>
                            <select class="form-select form-select-sm" id="hourSelect">
                                @for($i = 0; $i <= 23; $i++)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Minute</label>
                            <select class="form-select form-select-sm" id="minuteSelect">
                                @for($i = 0; $i <= 59; $i += 5)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small">Second</label>
                            <select class="form-select form-select-sm" id="secondSelect">
                                @for($i = 0; $i <= 59; $i += 5)
                                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="picker-actions mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="setNow">
                        Now
                    </button>
                    <button type="button" class="btn btn-primary btn-sm ms-2" id="applyDateTime">
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tag Input Component --}}
<div class="form-group mb-4" data-component="tag-input">
    <label class="form-label fw-semibold">{{ $label ?? 'Tags' }}</label>
    <div class="tag-input-container">
        <div class="tag-input-wrapper" id="tagInputWrapper">
            <div class="selected-tags" id="selectedTags">
                @if(isset($value) && is_array($value))
                    @foreach($value as $tag)
                        <span class="tag-item">
                            {{ $tag }}
                            <button type="button" class="tag-remove">×</button>
                            <input type="hidden" name="{{ $name ?? 'tags[]' }}" value="{{ $tag }}">
                        </span>
                    @endforeach
                @endif
            </div>
            <input type="text" class="tag-input" placeholder="Add tags..." id="tagInput">
        </div>
        
        <div class="tag-suggestions d-none" id="tagSuggestions">
            <div class="suggestions-header">
                <small class="text-muted">Suggestions</small>
            </div>
            <div class="suggestions-list" id="suggestionsList"></div>
        </div>
        
        <div class="tag-input-help">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Press Enter or comma to add tags. Use backspace to remove.
            </small>
        </div>
    </div>
</div>

{{-- Autocomplete Component --}}
<div class="form-group mb-4" data-component="autocomplete">
    <label class="form-label fw-semibold">{{ $label ?? 'Search & Select' }}</label>
    <div class="autocomplete-container">
        <div class="autocomplete-input-wrapper">
            <input type="text" class="form-control autocomplete-input" 
                   placeholder="{{ $placeholder ?? 'Start typing to search...' }}" 
                   id="autocompleteInput" 
                   autocomplete="off">
            <div class="autocomplete-icon">
                <i class="fas fa-search"></i>
            </div>
            <div class="autocomplete-spinner d-none">
                <div class="spinner-border spinner-border-sm" role="status"></div>
            </div>
        </div>
        
        <div class="autocomplete-dropdown d-none" id="autocompleteDropdown">
            <div class="autocomplete-results" id="autocompleteResults">
                <div class="no-results">
                    <i class="fas fa-search text-muted"></i>
                    <p class="mb-0 text-muted">Start typing to see suggestions</p>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="{{ $name ?? 'selected_item' }}" id="selectedValue">
        
        <div class="selected-item d-none" id="selectedItem">
            <div class="selected-content">
                <div class="selected-info">
                    <span class="selected-title"></span>
                    <small class="selected-description text-muted"></small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-selection">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Component Styles --}}
<style>
/* Rich Text Editor Styles */
.rich-editor-container {
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    background: var(--card-bg);
}

.editor-toolbar {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem;
    border-bottom: 1px solid var(--bs-border-color);
    flex-wrap: wrap;
}

.toolbar-group {
    display: flex;
    gap: 0.25rem;
}

.toolbar-btn {
    background: none;
    border: 1px solid var(--bs-border-color);
    padding: 0.375rem 0.5rem;
    border-radius: 6px;
    color: var(--bs-body-color);
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
}

.toolbar-btn:hover {
    background: var(--bs-secondary-bg);
}

.toolbar-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.toolbar-select {
    height: 32px;
    font-size: 0.875rem;
    min-width: 120px;
}

.editor-content {
    min-height: 200px;
    padding: 1rem;
    outline: none;
    color: var(--bs-body-color);
}

.editor-content:empty::before {
    content: attr(data-placeholder);
    color: var(--bs-secondary-color);
    font-style: italic;
}

/* File Upload Styles */
.file-upload-container {
    width: 100%;
}

.file-drop-zone {
    border: 2px dashed var(--bs-border-color);
    border-radius: var(--border-radius);
    padding: 2rem;
    text-align: center;
    background: var(--bs-secondary-bg);
    transition: var(--transition);
    cursor: pointer;
}

.file-drop-zone:hover,
.file-drop-zone.drag-over {
    border-color: var(--primary-color);
    background: rgba(var(--primary-color), 0.05);
}

.drop-icon {
    font-size: 3rem;
    color: var(--bs-secondary-color);
    margin-bottom: 1rem;
}

.file-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    margin-bottom: 0.5rem;
    background: var(--card-bg);
}

.file-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bs-secondary-bg);
    border-radius: 8px;
    font-size: 1.2rem;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.file-size {
    font-size: 0.875rem;
    color: var(--bs-secondary-color);
}

.file-progress {
    margin-top: 0.5rem;
}

.file-actions {
    display: flex;
    gap: 0.25rem;
}

/* Image Cropping Styles */
.image-crop-container {
    width: 100%;
}

.crop-drop-zone {
    border: 2px dashed var(--bs-border-color);
    border-radius: var(--border-radius);
    padding: 2rem;
    text-align: center;
    background: var(--bs-secondary-bg);
    cursor: pointer;
}

.crop-icon {
    font-size: 3rem;
    color: var(--bs-secondary-color);
    margin-bottom: 1rem;
}

.crop-canvas-container {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    overflow: hidden;
}

#cropCanvas {
    display: block;
    max-width: 100%;
    cursor: crosshair;
}

/* Color Picker Styles */
.color-picker-container {
    position: relative;
}

.color-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-preview {
    width: 40px;
    height: 40px;
    border: 2px solid var(--bs-border-color);
    border-radius: 8px;
    cursor: pointer;
    position: relative;
}

.color-preview::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(45deg, #ccc 25%, transparent 25%), 
                linear-gradient(-45deg, #ccc 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #ccc 75%), 
                linear-gradient(-45deg, transparent 75%, #ccc 75%);
    background-size: 8px 8px;
    background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
    border-radius: 8px;
    z-index: -1;
}

.color-input {
    flex: 1;
}

.color-picker-panel {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    background: var(--card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow-hover);
    width: 280px;
    margin-top: 0.5rem;
}

.color-picker-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.color-picker-body {
    padding: 1rem;
}

.color-canvas-container {
    position: relative;
    width: 200px;
    height: 150px;
    border: 1px solid var(--bs-border-color);
    border-radius: 8px;
    overflow: hidden;
    cursor: crosshair;
}

#colorCanvas {
    display: block;
}

.color-cursor {
    position: absolute;
    width: 12px;
    height: 12px;
    border: 2px solid white;
    border-radius: 50%;
    pointer-events: none;
    transform: translate(-50%, -50%);
    box-shadow: 0 0 0 1px rgba(0,0,0,0.3);
}

.preset-colors {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.preset-color {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    cursor: pointer;
    border: 2px solid var(--bs-border-color);
    transition: var(--transition);
}

.preset-color:hover {
    transform: scale(1.1);
}

/* Date/Time Picker Styles */
.datetime-picker-container {
    position: relative;
}

.datetime-input-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.datetime-input {
    flex: 1;
}

.datetime-picker-panel {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    background: var(--card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow-hover);
    width: 320px;
    margin-top: 0.5rem;
}

.picker-header {
    display: flex;
    justify-content: between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.picker-body {
    padding: 1rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.calendar-title {
    font-weight: 600;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.875rem;
}

.calendar-day:hover {
    background: var(--bs-secondary-bg);
}

.calendar-day.selected {
    background: var(--primary-color);
    color: white;
}

.calendar-day.other-month {
    color: var(--bs-secondary-color);
}

.calendar-day.today {
    background: var(--bs-warning);
    color: var(--bs-dark);
    font-weight: 600;
}

/* Tag Input Styles */
.tag-input-container {
    position: relative;
}

.tag-input-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem;
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    background: var(--card-bg);
    min-height: 45px;
    align-items: center;
    cursor: text;
}

.tag-input-wrapper:focus-within {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-color), 0.25);
}

.selected-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.tag-item {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-size: 0.875rem;
    animation: tagSlideIn 0.2s ease-out;
}

@keyframes tagSlideIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.tag-remove {
    background: none;
    border: none;
    color: white;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
    padding: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: var(--transition);
}

.tag-remove:hover {
    background: rgba(255,255,255,0.2);
}

.tag-input {
    border: none;
    outline: none;
    background: transparent;
    flex: 1;
    min-width: 120px;
    color: var(--bs-body-color);
}

.tag-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: var(--card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow-hover);
    margin-top: 0.25rem;
    max-height: 200px;
    overflow-y: auto;
}

.suggestions-header {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.suggestion-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    transition: var(--transition);
}

.suggestion-item:hover,
.suggestion-item.highlighted {
    background: var(--bs-secondary-bg);
}

/* Autocomplete Styles */
.autocomplete-container {
    position: relative;
}

.autocomplete-input-wrapper {
    position: relative;
}

.autocomplete-input {
    padding-right: 2.5rem;
}

.autocomplete-icon,
.autocomplete-spinner {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--bs-secondary-color);
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: var(--card-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow-hover);
    margin-top: 0.25rem;
    max-height: 300px;
    overflow-y: auto;
}

.autocomplete-results {
    padding: 0.5rem 0;
}

.autocomplete-item {
    padding: 0.75rem;
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid var(--bs-border-color);
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item:hover,
.autocomplete-item.highlighted {
    background: var(--bs-secondary-bg);
}

.item-title {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.item-description {
    font-size: 0.875rem;
    color: var(--bs-secondary-color);
}

.no-results {
    padding: 2rem;
    text-align: center;
}

.no-results i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.selected-item {
    margin-top: 0.75rem;
    padding: 0.75rem;
    background: var(--bs-secondary-bg);
    border-radius: var(--border-radius);
    border: 1px solid var(--bs-border-color);
}

.selected-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selected-info {
    flex: 1;
}

.selected-title {
    font-weight: 500;
    display: block;
}

.selected-description {
    display: block;
    margin-top: 0.25rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .color-picker-panel,
    .datetime-picker-panel {
        width: 100%;
        max-width: calc(100vw - 2rem);
    }
    
    .toolbar-group {
        flex-wrap: wrap;
    }
    
    .calendar-grid {
        gap: 0.125rem;
    }
    
    .calendar-day {
        font-size: 0.75rem;
    }
}
</style>