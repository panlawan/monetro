{{-- resources/views/components/legal-modal.blade.php --}}

<!-- Legal Documents Modal -->
<div class="modal fade" id="legalModal" tabindex="-1" aria-labelledby="legalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title" id="legalModalLabel">
                    <i class="fas fa-file-contract me-2"></i>Legal Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <!-- Content will be loaded here via AJAX -->
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading content...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <button type="button" class="btn btn-auth" id="acceptLegalBtn" data-type="">
                    <i class="fas fa-check me-1"></i>I Accept
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal-specific styles */
.modal-content {
    border-radius: var(--monetro-brand-border-radius);
    overflow: hidden;
}

.modal-header.bg-gradient {
    background: linear-gradient(135deg, var(--monetro-brand-primary) 0%, var(--monetro-brand-secondary) 100%);
}

.legal-header-modal {
    border-bottom: 2px solid var(--monetro-brand-primary);
    padding-bottom: 1rem;
}

.legal-section-modal {
    border-left: 3px solid var(--monetro-gray-300);
    padding-left: 1rem;
    margin-left: 0.5rem;
}

.legal-section-modal h6 {
    color: var(--monetro-brand-primary);
    margin-bottom: 0.75rem;
}

.legal-content-modal {
    font-size: 0.95rem;
    line-height: 1.6;
}

.legal-content-modal p {
    margin-bottom: 1rem;
}

.legal-content-modal ul {
    margin-bottom: 1rem;
    padding-left: 1.5rem;
}

.legal-content-modal li {
    margin-bottom: 0.5rem;
}

/* Scrollbar styling for modal body */
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-track {
    background: var(--monetro-gray-100);
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: var(--monetro-brand-primary);
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: var(--monetro-brand-secondary);
}

/* Loading animation */
.modal-body .fa-spinner {
    color: var(--monetro-brand-primary);
}

/* Responsive modal on mobile */
@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
        margin: 1rem auto;
    }
    
    .modal-body {
        max-height: 50vh;
        font-size: 0.9rem;
    }
    
    .legal-section-modal {
        border-left-width: 2px;
        padding-left: 0.75rem;
        margin-left: 0.25rem;
    }
}

/* Animation for modal content loading */
.legal-content-modal {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Highlight important sections */
.legal-content-modal strong {
    color: var(--monetro-brand-primary);
    font-weight: 600;
}

/* Link styling within modal */
.legal-content-modal a {
    color: var(--monetro-brand-primary);
    text-decoration: none;
}

.legal-content-modal a:hover {
    color: var(--monetro-brand-secondary);
    text-decoration: underline;
}
</style>