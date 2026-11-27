
<div id="appModal" class="app-modal" aria-hidden="true">
    <div class="modal-backdrop"></div>

    <div
        class="modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modalTitle"
        aria-describedby="modalMessage"
    >
        <div class="modal-header warning" id="modalHeader">
            <button
                type="button"
                class="modal-close"
                id="modalClose"
                aria-label="Close"
            >
                &times;
            </button>

            <div class="modal-icon" id="modalIcon">
                !
            </div>

            <h2 id="modalTitle">Confirmation</h2>
        </div>

        <div class="modal-body">
            <div
                id="modalMessage"
                class="modal-message-list"
                aria-live="polite"
            >
                Are you sure you want to continue?
            </div>
        </div>

        <div class="modal-actions" id="modalActions">
            <button
                type="button"
                class="modal-btn modal-cancel"
                id="modalCancel"
            >
                Cancel
            </button>

            <button
                type="button"
                class="modal-btn modal-confirm"
                id="modalConfirm"
            >
                Confirm
            </button>
        </div>
    </div>
</div>
