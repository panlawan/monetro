// resources/js/legal-modal.js
// Legal Modal Helper Functions

class LegalModal {
    constructor() {
        this.modal = null;
        this.modalTitle = null;
        this.modalBody = null;
        this.acceptBtn = null;
        this.csrfToken = null;
        this.init();
    }

    init() {
        // Get CSRF token
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        this.csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : null;

        // Get modal elements
        this.modal = document.getElementById('legalModal');
        if (this.modal) {
            this.modalTitle = this.modal.querySelector('.modal-title');
            this.modalBody = this.modal.querySelector('.modal-body');
            this.acceptBtn = this.modal.querySelector('#acceptLegalBtn');
            
            this.bindEvents();
        }
    }

    bindEvents() {
        // Handle terms and privacy links
        document.addEventListener('click', (e) => {
            if (e.target.matches('.terms-link, .privacy-link')) {
                e.preventDefault();
                const type = e.target.getAttribute('data-type');
                this.loadContent(type);
            }
        });

        // Handle accept button
        if (this.acceptBtn) {
            this.acceptBtn.addEventListener('click', () => {
                this.handleAcceptance();
            });
        }

        // Handle modal shown event
        if (this.modal) {
            this.modal.addEventListener('shown.bs.modal', () => {
                // Focus on first focusable element for accessibility
                const firstFocusable = this.modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (firstFocusable) {
                    firstFocusable.focus();
                }
            });
        }
    }

    async loadContent(type) {
        if (!this.modal || !this.modalTitle || !this.modalBody || !this.acceptBtn) {
            console.error('Modal elements not found');
            return;
        }

        // Set loading state
        this.setLoadingState();
        
        // Set accept button data
        this.acceptBtn.setAttribute('data-type', type);
        
        try {
            const response = await fetch(`/api/legal/${type}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.renderContent(data);
            
        } catch (error) {
            console.error('Error loading legal content:', error);
            this.setErrorState();
        }
    }

    setLoadingState() {
        this.modalTitle.innerHTML = '<i class="fas fa-file-contract me-2"></i>Loading...';
        this.modalBody.innerHTML = `
            <div class="modal-loading">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p>Loading legal document...</p>
            </div>
        `;
    }

    setErrorState() {
        this.modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Error';
        this.modalBody.innerHTML = `
            <div class="modal-error">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Failed to load content. Please try again or contact support.
                </div>
            </div>
        `;
    }

    renderContent(data) {
        // Set modal title
        this.modalTitle.innerHTML = `<i class="fas fa-file-contract me-2"></i>${data.title}`;
        
        // Render content
        let html = `
            <div class="legal-header-modal">
                <h5>${data.title}</h5>
                <p class="text-muted">Last updated: ${data.lastUpdated}</p>
            </div>
            <div class="legal-content-modal">
        `;
        
        data.sections.forEach(section => {
            html += `
                <div class="legal-section-modal">
                    <h6>${section.title}</h6>
                    <div class="text-muted">${section.content}</div>
                </div>
            `;
        });
        
        html += '</div>';
        this.modalBody.innerHTML = html;
    }

    async handleAcceptance() {
        const type = this.acceptBtn.getAttribute('data-type');
        const termsCheckbox = document.getElementById('terms');
        
        if (!type) {
            console.error('No document type specified');
            return;
        }

        // Check the terms checkbox
        if (termsCheckbox) {
            termsCheckbox.checked = true;
            
            // Trigger change event for any validation listeners
            const event = new Event('change', { bubbles: true });
            termsCheckbox.dispatchEvent(event);
        }

        // Record acceptance for authenticated users
        await this.recordAcceptance(type);
        
        // Show success message briefly
        this.showAcceptanceSuccess(type);
        
        // Close modal after short delay
        setTimeout(() => {
            const modalInstance = bootstrap.Modal.getInstance(this.modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }, 1000);
    }

    async recordAcceptance(type) {
        // Only record if user is authenticated (check if we have auth routes)
        const isAuthenticated = document.body.hasAttribute('data-authenticated') || 
                              document.querySelector('meta[name="user-id"]');
        
        if (!isAuthenticated || !this.csrfToken) {
            return;
        }

        try {
            const response = await fetch('/api/legal/accept', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    document_type: type,
                    accepted: true
                })
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Acceptance recorded:', data.message);
            } else {
                console.warn('Failed to record acceptance:', response.status);
            }
        } catch (error) {
            console.error('Error recording acceptance:', error);
        }
    }

    showAcceptanceSuccess(type) {
        const documentName = type === 'terms' ? 'Terms of Service' : 'Privacy Policy';
        
        this.modalBody.innerHTML = `
            <div class="acceptance-success text-center py-4">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Thank you! You have accepted our ${documentName}.
                </div>
                <p class="text-muted">This window will close automatically...</p>
            </div>
        `;
    }

    // Public method to manually trigger modal
    show(type) {
        if (this.modal) {
            this.loadContent(type);
            const modalInstance = new bootstrap.Modal(this.modal);
            modalInstance.show();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.legalModal = new LegalModal();
});

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LegalModal;
}