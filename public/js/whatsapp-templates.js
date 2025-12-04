/**
 * WhatsApp Templates JavaScript
 */

// Avoid duplicate declarations
if (typeof window.WhatsAppTemplatesLoaded === 'undefined') {
    window.WhatsAppTemplatesLoaded = true;

    // Template management functionality using IIFE to avoid global scope issues
    (function() {
        'use strict';
        
        let siteName = 'Your Website';
        let siteUrl = 'https://yourwebsite.com';

        function initializeEventHandlers() {
            // Template content preview
            const contentTextarea = document.querySelector('textarea[name="content"]');
            if (contentTextarea) {
                contentTextarea.addEventListener('input', updatePreview);
            }

            // Sample template selection
            document.querySelectorAll('.sample-template').forEach(template => {
                template.addEventListener('click', selectSampleTemplate);
            });

            // Create template form submission
            const createForm = document.getElementById('createTemplateForm');
            if (createForm) {
                createForm.addEventListener('submit', handleCreateTemplate);
            }

            // Reset form when modal is hidden
            const createModal = document.getElementById('createTemplateModal');
            if (createModal) {
                createModal.addEventListener('hidden.bs.modal', resetForm);
            }
        }

        function updatePreview() {
            const content = document.querySelector('textarea[name="content"]')?.value || '';
            const previewElement = document.getElementById('templatePreview');
            
            if (!previewElement) return;

            if (content) {
                // Replace common variables with sample data for preview
                let preview = content
                    .replace(/\{\{user_name\}\}/g, 'John Doe')
                    .replace(/\{\{user_email\}\}/g, 'john@example.com')
                    .replace(/\{\{current_date\}\}/g, new Date().toLocaleDateString())
                    .replace(/\{\{current_time\}\}/g, new Date().toLocaleTimeString())
                    .replace(/\{\{site_name\}\}/g, siteName)
                    .replace(/\{\{site_url\}\}/g, siteUrl)
                    .replace(/\{\{customer_name\}\}/g, 'Jane Smith')
                    .replace(/\{\{order_id\}\}/g, '#12345')
                    .replace(/\{\{amount\}\}/g, '$99.99')
                    .replace(/\{\{product_name\}\}/g, 'Sample Product');

                previewElement.innerHTML = '<div class="template-preview">' + preview.replace(/\n/g, '<br>') + '</div>';
            } else {
                previewElement.innerHTML = '<em class="text-muted">Template preview will appear here...</em>';
            }
        }

        function selectSampleTemplate(event) {
            const templateType = event.currentTarget.getAttribute('data-template');
            let content = getTemplateContent(templateType);
            
            const textarea = document.querySelector('textarea[name="content"]');
            if (textarea) {
                textarea.value = content;
                updatePreview();
            }

            // Highlight selected template
            document.querySelectorAll('.sample-template').forEach(t => {
                t.classList.remove('bg-primary', 'text-white');
            });
            event.currentTarget.classList.add('bg-primary', 'text-white');

            // Collapse the accordion
            const collapse = document.getElementById('sampleTemplates');
            if (collapse && window.bootstrap) {
                const bsCollapse = new bootstrap.Collapse(collapse, { toggle: false });
                bsCollapse.hide();
            }
        }

        function getTemplateContent(templateType) {
            const templates = {
                welcome: `Hi {{user_name}}! 👋

Welcome to {{site_name}}! We're excited to have you on board.

If you have any questions, feel free to reach out to us anytime.

Best regards,
{{site_name}} Team`,

                order: `Order Confirmed! 🎉

Hi {{customer_name}},

Your order {{order_id}} has been confirmed for {{amount}}.

We'll notify you once it's shipped.

Thank you for shopping with {{site_name}}!`,

                reminder: `Reminder: Don't miss out! ⏰

Hi {{customer_name}},

You have items waiting in your cart at {{site_name}}.

Complete your purchase now and get free shipping on orders above $50.

Shop now: {{site_url}}`,

                support: `Hello {{customer_name}} 👋

Thank you for contacting {{site_name}} support.

We've received your inquiry and our team will get back to you within 24 hours.

Reference ID: #{{order_id}}

Best regards,
Support Team`
            };

            return templates[templateType] || '';
        }

        function handleCreateTemplate(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            fetch('/whatsapp/templates/store', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = document.getElementById('createTemplateModal');
                    if (modal && window.bootstrap) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) bsModal.hide();
                    }
                    
                    showAlert('success', 'Template created successfully!');
                    setTimeout(() => location.reload(), 1500); // Refresh the page
                } else {
                    showAlert('error', data.message || 'Failed to create template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to create template');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }

        function resetForm() {
            const form = document.getElementById('createTemplateForm');
            if (form) {
                form.reset();
                updatePreview();
            }
            
            // Reset sample template highlights
            document.querySelectorAll('.sample-template').forEach(t => {
                t.classList.remove('bg-primary', 'text-white');
            });
        }

        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
            const alertHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Remove existing alerts
            document.querySelectorAll('.alert').forEach(alert => alert.remove());
            
            // Add new alert at top of main content
            const container = document.querySelector('.container-fluid');
            if (container) {
                container.insertAdjacentHTML('afterbegin', alertHtml);
            }
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        }

        // Global functions for backward compatibility
        window.previewTemplate = function(templateId) {
            showAlert('info', 'Template preview functionality would be implemented here.');
        };

        window.useTemplate = function(templateId) {
            window.location.href = '/whatsapp/send?template=' + templateId;
        };

        window.editTemplate = function(templateId) {
            showAlert('info', 'Edit template functionality would be implemented here.');
        };

        window.deleteTemplate = function(templateId) {
            if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
                showAlert('info', 'Delete template functionality would be implemented here.');
            }
        };

        // Initialize when DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeEventHandlers);
        } else {
            initializeEventHandlers();
        }

    })();
}