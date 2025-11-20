{{-- Advanced Form Components JavaScript --}}
<script>
class AdvancedFormComponents {
    constructor() {
        this.initializeComponents();
    }

    initializeComponents() {
        this.initRichEditor();
        this.initFileUpload();
        this.initImageCropper();
        this.initColorPicker();
        this.initDateTimePicker();
        this.initTagInput();
        this.initAutocomplete();
    }

    // Rich Text Editor
    initRichEditor() {
        const editors = document.querySelectorAll('[data-component="rich-editor"]');
        
        editors.forEach(editorContainer => {
            const toolbar = editorContainer.querySelector('.editor-toolbar');
            const content = editorContainer.querySelector('.editor-content');
            const hiddenInput = editorContainer.querySelector('.editor-input');
            
            // Toolbar button handlers
            toolbar.addEventListener('click', (e) => {
                const btn = e.target.closest('.toolbar-btn');
                if (!btn) return;
                
                e.preventDefault();
                const command = btn.dataset.command;
                
                if (command === 'createLink') {
                    const url = prompt('Enter URL:');
                    if (url) {
                        document.execCommand(command, false, url);
                    }
                } else if (command === 'insertImage') {
                    const url = prompt('Enter image URL:');
                    if (url) {
                        document.execCommand(command, false, url);
                    }
                } else {
                    document.execCommand(command, false, null);
                }
                
                this.updateEditorState(toolbar, content);
                this.updateHiddenInput(content, hiddenInput);
            });
            
            // Format block select handler
            const formatSelect = toolbar.querySelector('.toolbar-select');
            formatSelect?.addEventListener('change', (e) => {
                document.execCommand('formatBlock', false, e.target.value);
                this.updateHiddenInput(content, hiddenInput);
            });
            
            // Content change handler
            content.addEventListener('input', () => {
                this.updateHiddenInput(content, hiddenInput);
            });
            
            // Initial state
            this.updateHiddenInput(content, hiddenInput);
        });
    }
    
    updateEditorState(toolbar, content) {
        const buttons = toolbar.querySelectorAll('.toolbar-btn');
        buttons.forEach(btn => {
            const command = btn.dataset.command;
            btn.classList.toggle('active', document.queryCommandState(command));
        });
    }
    
    updateHiddenInput(content, hiddenInput) {
        hiddenInput.value = content.innerHTML;
    }

    // File Upload with Drag & Drop
    initFileUpload() {
        const uploaders = document.querySelectorAll('[data-component="file-upload"]');
        
        uploaders.forEach(uploader => {
            const dropZone = uploader.querySelector('#fileDropZone');
            const fileInput = uploader.querySelector('#fileInput');
            const browseBtn = uploader.querySelector('#browseFiles');
            const fileList = uploader.querySelector('#fileList');
            
            // Drag and drop handlers
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
            
            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
            });
            
            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                this.handleFiles(e.dataTransfer.files, fileList);
            });
            
            // Browse button handler
            browseBtn.addEventListener('click', () => {
                fileInput.click();
            });
            
            // File input change handler
            fileInput.addEventListener('change', (e) => {
                this.handleFiles(e.target.files, fileList);
            });
        });
    }
    
    handleFiles(files, fileList) {
        Array.from(files).forEach(file => {
            this.addFileToList(file, fileList);
            this.uploadFile(file, fileList.lastElementChild);
        });
    }
    
    addFileToList(file, fileList) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <div class="file-icon">
                <i class="fas ${this.getFileIcon(file.type)}"></i>
            </div>
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${this.formatFileSize(file.size)}</div>
                <div class="progress file-progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>
            <div class="file-actions">
                <button type="button" class="btn btn-sm btn-outline-danger remove-file">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        fileList.appendChild(fileItem);
        
        // Remove file handler
        fileItem.querySelector('.remove-file').addEventListener('click', () => {
            fileItem.remove();
        });
    }
    
    uploadFile(file, fileItem) {
        const formData = new FormData();
        formData.append('file', file);
        
        const progressBar = fileItem.querySelector('.progress-bar');
        
        // Simulate upload progress
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                progressBar.parentElement.classList.add('d-none');
            }
            progressBar.style.width = `${Math.min(progress, 100)}%`;
        }, 200);
    }
    
    getFileIcon(mimeType) {
        if (mimeType.startsWith('image/')) return 'fa-image';
        if (mimeType.startsWith('video/')) return 'fa-video';
        if (mimeType.startsWith('audio/')) return 'fa-music';
        if (mimeType.includes('pdf')) return 'fa-file-pdf';
        if (mimeType.includes('word')) return 'fa-file-word';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fa-file-excel';
        return 'fa-file';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Image Cropper
    initImageCropper() {
        const croppers = document.querySelectorAll('[data-component="image-crop"]');
        
        croppers.forEach(cropper => {
            const uploadArea = cropper.querySelector('#cropUploadArea');
            const selectBtn = cropper.querySelector('#selectImage');
            const imageInput = cropper.querySelector('#imageInput');
            const cropEditor = cropper.querySelector('#cropEditor');
            const canvas = cropper.querySelector('#cropCanvas');
            const ctx = canvas.getContext('2d');
            const aspectRatioSelect = cropper.querySelector('#aspectRatio');
            const applyCropBtn = cropper.querySelector('#applyCrop');
            const resetCropBtn = cropper.querySelector('#resetCrop');
            const preview = cropper.querySelector('#croppedPreview');
            const previewImage = cropper.querySelector('#previewImage');
            const hiddenInput = cropper.querySelector('#croppedImageData');
            
            let currentImage = null;
            let cropData = { x: 0, y: 0, width: 0, height: 0 };
            let isDragging = false;
            let startX, startY;
            
            selectBtn.addEventListener('click', () => {
                imageInput.click();
            });
            
            imageInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    this.loadImageForCrop(file, canvas, ctx, cropEditor, uploadArea);
                    currentImage = file;
                }
            });
            
            // Canvas mouse events for cropping
            canvas.addEventListener('mousedown', (e) => {
                isDragging = true;
                const rect = canvas.getBoundingClientRect();
                startX = e.clientX - rect.left;
                startY = e.clientY - rect.top;
                cropData.x = startX;
                cropData.y = startY;
            });
            
            canvas.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                
                const rect = canvas.getBoundingClientRect();
                const currentX = e.clientX - rect.left;
                const currentY = e.clientY - rect.top;
                
                cropData.width = currentX - startX;
                cropData.height = currentY - startY;
                
                this.drawCropArea(canvas, ctx, currentImage, cropData);
            });
            
            canvas.addEventListener('mouseup', () => {
                isDragging = false;
            });
            
            applyCropBtn.addEventListener('click', () => {
                if (currentImage && cropData.width > 0 && cropData.height > 0) {
                    this.applyCrop(currentImage, cropData, previewImage, hiddenInput, preview);
                }
            });
            
            resetCropBtn.addEventListener('click', () => {
                if (currentImage) {
                    this.loadImageForCrop(currentImage, canvas, ctx, cropEditor, uploadArea);
                    cropData = { x: 0, y: 0, width: 0, height: 0 };
                }
            });
        });
    }
    
    loadImageForCrop(file, canvas, ctx, editor, uploadArea) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                canvas.width = Math.min(img.width, 500);
                canvas.height = (canvas.width / img.width) * img.height;
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                uploadArea.classList.add('d-none');
                editor.classList.remove('d-none');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    
    drawCropArea(canvas, ctx, image, cropData) {
        // Redraw image
        const img = new Image();
        img.onload = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            
            // Draw crop overlay
            ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Clear crop area
            ctx.clearRect(cropData.x, cropData.y, cropData.width, cropData.height);
            ctx.drawImage(img, 
                cropData.x, cropData.y, cropData.width, cropData.height,
                cropData.x, cropData.y, cropData.width, cropData.height
            );
            
            // Draw crop border
            ctx.strokeStyle = '#fff';
            ctx.lineWidth = 2;
            ctx.strokeRect(cropData.x, cropData.y, cropData.width, cropData.height);
        };
        img.src = canvas.toDataURL();
    }
    
    applyCrop(file, cropData, previewImg, hiddenInput, preview) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        const reader = new FileReader();
        reader.onload = (e) => {
            img.onload = () => {
                canvas.width = Math.abs(cropData.width);
                canvas.height = Math.abs(cropData.height);
                
                const scaleX = img.width / 500; // Assuming max canvas width is 500
                const scaleY = img.height / (500 * img.height / img.width);
                
                ctx.drawImage(img,
                    cropData.x * scaleX, cropData.y * scaleY,
                    cropData.width * scaleX, cropData.height * scaleY,
                    0, 0, canvas.width, canvas.height
                );
                
                const croppedDataURL = canvas.toDataURL('image/jpeg', 0.9);
                previewImg.src = croppedDataURL;
                hiddenInput.value = croppedDataURL;
                preview.classList.remove('d-none');
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    // Color Picker
    initColorPicker() {
        const pickers = document.querySelectorAll('[data-component="color-picker"]');
        
        pickers.forEach(picker => {
            const preview = picker.querySelector('#colorPreview');
            const input = picker.querySelector('#colorInput');
            const openBtn = picker.querySelector('#openColorPicker');
            const panel = picker.querySelector('#colorPickerPanel');
            const closeBtn = picker.querySelector('#closeColorPicker');
            const canvas = picker.querySelector('#colorCanvas');
            const ctx = canvas.getContext('2d');
            const cursor = picker.querySelector('#colorCursor');
            const hueSlider = picker.querySelector('#hueSlider');
            const hexInput = picker.querySelector('#hexValue');
            const rgbInput = picker.querySelector('#rgbValue');
            const hslInput = picker.querySelector('#hslValue');
            const presetColors = picker.querySelectorAll('.preset-color');
            
            let currentHue = 0;
            let currentColor = { r: 102, g: 126, b: 234 };
            
            // Initialize canvas
            this.drawColorCanvas(ctx, canvas, currentHue);
            
            openBtn.addEventListener('click', () => {
                panel.classList.toggle('d-none');
            });
            
            closeBtn.addEventListener('click', () => {
                panel.classList.add('d-none');
            });
            
            // Canvas click handler
            canvas.addEventListener('click', (e) => {
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const imageData = ctx.getImageData(x, y, 1, 1);
                currentColor = {
                    r: imageData.data[0],
                    g: imageData.data[1],
                    b: imageData.data[2]
                };
                
                this.updateColorValues(currentColor, preview, input, hexInput, rgbInput, hslInput);
                this.updateCursor(cursor, x, y);
            });
            
            // Hue slider
            hueSlider.addEventListener('input', (e) => {
                currentHue = parseInt(e.target.value);
                this.drawColorCanvas(ctx, canvas, currentHue);
            });
            
            // Preset colors
            presetColors.forEach(preset => {
                const color = preset.dataset.color;
                preset.style.backgroundColor = color;
                
                preset.addEventListener('click', () => {
                    const rgb = this.hexToRgb(color);
                    currentColor = rgb;
                    this.updateColorValues(currentColor, preview, input, hexInput, rgbInput, hslInput);
                });
            });
            
            // Input change handler
            input.addEventListener('change', (e) => {
                const color = e.target.value;
                if (this.isValidHex(color)) {
                    const rgb = this.hexToRgb(color);
                    currentColor = rgb;
                    preview.style.backgroundColor = color;
                    this.updateColorValues(currentColor, preview, input, hexInput, rgbInput, hslInput);
                }
            });
            
            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!picker.contains(e.target)) {
                    panel.classList.add('d-none');
                }
            });
        });
    }
    
    drawColorCanvas(ctx, canvas, hue) {
        const width = canvas.width;
        const height = canvas.height;
        
        // Create gradient
        const gradient1 = ctx.createLinearGradient(0, 0, width, 0);
        gradient1.addColorStop(0, '#ffffff');
        gradient1.addColorStop(1, `hsl(${hue}, 100%, 50%)`);
        
        ctx.fillStyle = gradient1;
        ctx.fillRect(0, 0, width, height);
        
        const gradient2 = ctx.createLinearGradient(0, 0, 0, height);
        gradient2.addColorStop(0, 'rgba(0, 0, 0, 0)');
        gradient2.addColorStop(1, 'rgba(0, 0, 0, 1)');
        
        ctx.fillStyle = gradient2;
        ctx.fillRect(0, 0, width, height);
    }
    
    updateColorValues(color, preview, input, hexInput, rgbInput, hslInput) {
        const hex = this.rgbToHex(color.r, color.g, color.b);
        const hsl = this.rgbToHsl(color.r, color.g, color.b);
        
        preview.style.backgroundColor = hex;
        input.value = hex;
        hexInput.value = hex;
        rgbInput.value = `rgb(${color.r}, ${color.g}, ${color.b})`;
        hslInput.value = `hsl(${Math.round(hsl.h)}, ${Math.round(hsl.s)}%, ${Math.round(hsl.l)}%)`;
    }
    
    updateCursor(cursor, x, y) {
        cursor.style.left = `${x}px`;
        cursor.style.top = `${y}px`;
    }
    
    hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }
    
    rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }
    
    rgbToHsl(r, g, b) {
        r /= 255;
        g /= 255;
        b /= 255;
        
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;
        
        if (max === min) {
            h = s = 0;
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        
        return { h: h * 360, s: s * 100, l: l * 100 };
    }
    
    isValidHex(hex) {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
    }

    // Date/Time Picker
    initDateTimePicker() {
        const pickers = document.querySelectorAll('[data-component="datetime-picker"]');
        
        pickers.forEach(picker => {
            const input = picker.querySelector('.datetime-input');
            const openBtn = picker.querySelector('#openDatePicker');
            const panel = picker.querySelector('#datetimePickerPanel');
            const closeBtn = picker.querySelector('#closeDatePicker');
            const calendarGrid = picker.querySelector('#calendarGrid');
            const calendarTitle = picker.querySelector('#calendarTitle');
            const prevBtn = picker.querySelector('#prevMonth');
            const nextBtn = picker.querySelector('#nextMonth');
            const hourSelect = picker.querySelector('#hourSelect');
            const minuteSelect = picker.querySelector('#minuteSelect');
            const secondSelect = picker.querySelector('#secondSelect');
            const setNowBtn = picker.querySelector('#setNow');
            const applyBtn = picker.querySelector('#applyDateTime');
            
            let currentDate = new Date();
            let selectedDate = null;
            
            openBtn.addEventListener('click', () => {
                panel.classList.toggle('d-none');
                this.renderCalendar(calendarGrid, calendarTitle, currentDate);
            });
            
            closeBtn.addEventListener('click', () => {
                panel.classList.add('d-none');
            });
            
            prevBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                this.renderCalendar(calendarGrid, calendarTitle, currentDate);
            });
            
            nextBtn.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                this.renderCalendar(calendarGrid, calendarTitle, currentDate);
            });
            
            setNowBtn.addEventListener('click', () => {
                const now = new Date();
                selectedDate = now;
                hourSelect.value = String(now.getHours()).padStart(2, '0');
                minuteSelect.value = String(now.getMinutes()).padStart(2, '0');
                secondSelect.value = String(now.getSeconds()).padStart(2, '0');
                this.renderCalendar(calendarGrid, calendarTitle, now);
            });
            
            applyBtn.addEventListener('click', () => {
                if (selectedDate) {
                    const hour = hourSelect.value;
                    const minute = minuteSelect.value;
                    const second = secondSelect.value;
                    
                    selectedDate.setHours(parseInt(hour));
                    selectedDate.setMinutes(parseInt(minute));
                    selectedDate.setSeconds(parseInt(second));
                    
                    input.value = this.formatDateTime(selectedDate);
                    panel.classList.add('d-none');
                }
            });
            
            // Calendar day click handler
            calendarGrid.addEventListener('click', (e) => {
                if (e.target.classList.contains('calendar-day') && !e.target.classList.contains('other-month')) {
                    // Remove previous selection
                    calendarGrid.querySelectorAll('.calendar-day').forEach(day => {
                        day.classList.remove('selected');
                    });
                    
                    // Add selection to clicked day
                    e.target.classList.add('selected');
                    
                    const day = parseInt(e.target.textContent);
                    selectedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                }
            });
        });
    }
    
    renderCalendar(grid, title, date) {
        const year = date.getFullYear();
        const month = date.getMonth();
        
        title.textContent = `${date.toLocaleString('default', { month: 'long' })} ${year}`;
        
        // Clear grid
        grid.innerHTML = '';
        
        // Day headers
        const dayHeaders = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
        dayHeaders.forEach(day => {
            const header = document.createElement('div');
            header.className = 'calendar-day-header';
            header.textContent = day;
            header.style.fontWeight = '600';
            header.style.color = 'var(--bs-secondary-color)';
            grid.appendChild(header);
        });
        
        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        // Previous month days
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = document.createElement('div');
            day.className = 'calendar-day other-month';
            day.textContent = daysInPrevMonth - i;
            grid.appendChild(day);
        }
        
        // Current month days
        const today = new Date();
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            
            if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
                dayElement.classList.add('today');
            }
            
            grid.appendChild(dayElement);
        }
        
        // Next month days
        const totalCells = grid.children.length;
        const remainingCells = 42 - totalCells; // 6 rows × 7 days
        for (let day = 1; day <= remainingCells; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day other-month';
            dayElement.textContent = day;
            grid.appendChild(dayElement);
        }
    }
    
    formatDateTime(date) {
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    }

    // Tag Input
    initTagInput() {
        const tagInputs = document.querySelectorAll('[data-component="tag-input"]');
        
        tagInputs.forEach(container => {
            const wrapper = container.querySelector('#tagInputWrapper');
            const input = container.querySelector('#tagInput');
            const tagsContainer = container.querySelector('#selectedTags');
            const suggestions = container.querySelector('#tagSuggestions');
            const suggestionsList = container.querySelector('#suggestionsList');
            
            const commonTags = ['JavaScript', 'Python', 'React', 'Vue', 'Angular', 'PHP', 'Laravel', 'CSS', 'HTML', 'MySQL'];
            
            wrapper.addEventListener('click', () => {
                input.focus();
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    this.addTag(input.value.trim(), tagsContainer, container);
                    input.value = '';
                    suggestions.classList.add('d-none');
                } else if (e.key === 'Backspace' && input.value === '') {
                    const lastTag = tagsContainer.lastElementChild;
                    if (lastTag) {
                        lastTag.remove();
                    }
                }
            });
            
            input.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                if (query.length > 0) {
                    const filtered = commonTags.filter(tag => 
                        tag.toLowerCase().includes(query) && 
                        !this.isTagSelected(tag, tagsContainer)
                    );
                    this.showSuggestions(filtered, suggestionsList, suggestions, input, tagsContainer, container);
                } else {
                    suggestions.classList.add('d-none');
                }
            });
            
            // Handle tag removal
            tagsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('tag-remove')) {
                    e.target.closest('.tag-item').remove();
                }
            });
        });
    }
    
    addTag(tagText, container, parentContainer) {
        if (!tagText || this.isTagSelected(tagText, container)) return;
        
        const tag = document.createElement('span');
        tag.className = 'tag-item';
        tag.innerHTML = `
            ${tagText}
            <button type="button" class="tag-remove">×</button>
            <input type="hidden" name="${parentContainer.querySelector('.tag-input').name || 'tags[]'}" value="${tagText}">
        `;
        
        container.appendChild(tag);
    }
    
    isTagSelected(tagText, container) {
        const existingTags = Array.from(container.querySelectorAll('.tag-item'));
        return existingTags.some(tag => tag.textContent.trim().replace('×', '') === tagText);
    }
    
    showSuggestions(suggestions, list, container, input, tagsContainer, parentContainer) {
        list.innerHTML = '';
        
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.textContent = suggestion;
            item.addEventListener('click', () => {
                this.addTag(suggestion, tagsContainer, parentContainer);
                input.value = '';
                container.classList.add('d-none');
            });
            list.appendChild(item);
        });
        
        container.classList.toggle('d-none', suggestions.length === 0);
    }

    // Autocomplete
    initAutocomplete() {
        const autocompletes = document.querySelectorAll('[data-component="autocomplete"]');
        
        autocompletes.forEach(container => {
            const input = container.querySelector('#autocompleteInput');
            const dropdown = container.querySelector('#autocompleteDropdown');
            const results = container.querySelector('#autocompleteResults');
            const hiddenInput = container.querySelector('#selectedValue');
            const selectedItem = container.querySelector('#selectedItem');
            const spinner = container.querySelector('.autocomplete-spinner');
            const icon = container.querySelector('.autocomplete-icon');
            
            let searchTimeout;
            let currentIndex = -1;
            let searchResults = [];
            
            // Sample data - replace with your actual data source
            const sampleData = [
                { id: 1, title: 'John Doe', description: 'Software Developer' },
                { id: 2, title: 'Jane Smith', description: 'UI/UX Designer' },
                { id: 3, title: 'Mike Johnson', description: 'Project Manager' },
                { id: 4, title: 'Sarah Wilson', description: 'Data Analyst' },
                { id: 5, title: 'Tom Brown', description: 'System Administrator' }
            ];
            
            input.addEventListener('input', (e) => {
                const query = e.target.value.trim();
                
                if (query.length === 0) {
                    dropdown.classList.add('d-none');
                    return;
                }
                
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(query, sampleData, results, dropdown, spinner, icon);
                }, 300);
            });
            
            input.addEventListener('keydown', (e) => {
                const items = results.querySelectorAll('.autocomplete-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentIndex = Math.min(currentIndex + 1, items.length - 1);
                    this.highlightItem(items, currentIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentIndex = Math.max(currentIndex - 1, -1);
                    this.highlightItem(items, currentIndex);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentIndex >= 0 && items[currentIndex]) {
                        this.selectItem(searchResults[currentIndex], input, hiddenInput, selectedItem, dropdown);
                    }
                } else if (e.key === 'Escape') {
                    dropdown.classList.add('d-none');
                    currentIndex = -1;
                }
            });
            
            // Handle item clicks
            results.addEventListener('click', (e) => {
                const item = e.target.closest('.autocomplete-item');
                if (item) {
                    const index = Array.from(results.querySelectorAll('.autocomplete-item')).indexOf(item);
                    this.selectItem(searchResults[index], input, hiddenInput, selectedItem, dropdown);
                }
            });
            
            // Handle selection removal
            selectedItem.querySelector('.remove-selection')?.addEventListener('click', () => {
                input.value = '';
                hiddenInput.value = '';
                selectedItem.classList.add('d-none');
                input.style.display = 'block';
            });
            
            // Close dropdown on outside click
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    dropdown.classList.add('d-none');
                    currentIndex = -1;
                }
            });
        });
    }
    
    performSearch(query, data, results, dropdown, spinner, icon) {
        // Show spinner
        icon.classList.add('d-none');
        spinner.classList.remove('d-none');
        
        // Simulate API call delay
        setTimeout(() => {
            const filtered = data.filter(item => 
                item.title.toLowerCase().includes(query.toLowerCase()) ||
                item.description.toLowerCase().includes(query.toLowerCase())
            );
            
            this.displayResults(filtered, results, dropdown);
            
            // Hide spinner
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
        }, 500);
    }
    
    displayResults(data, results, dropdown) {
        searchResults = data;
        results.innerHTML = '';
        
        if (data.length === 0) {
            results.innerHTML = `
                <div class="no-results">
                    <i class="fas fa-search text-muted"></i>
                    <p class="mb-0 text-muted">No results found</p>
                </div>
            `;
        } else {
            data.forEach(item => {
                const element = document.createElement('div');
                element.className = 'autocomplete-item';
                element.innerHTML = `
                    <div class="item-title">${item.title}</div>
                    <div class="item-description">${item.description}</div>
                `;
                results.appendChild(element);
            });
        }
        
        dropdown.classList.remove('d-none');
    }
    
    highlightItem(items, index) {
        items.forEach((item, i) => {
            item.classList.toggle('highlighted', i === index);
        });
    }
    
    selectItem(item, input, hiddenInput, selectedItem, dropdown) {
        input.value = item.title;
        hiddenInput.value = item.id;
        
        selectedItem.querySelector('.selected-title').textContent = item.title;
        selectedItem.querySelector('.selected-description').textContent = item.description;
        selectedItem.classList.remove('d-none');
        
        dropdown.classList.add('d-none');
        input.style.display = 'none';
    }
}

// Initialize components when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AdvancedFormComponents();
});
</script>