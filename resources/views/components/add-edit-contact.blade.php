<div class="contact-modal-overlay" id="contactModal" data-popup-overlay aria-hidden="true">

    <div class="contact-modal">

        <div class="contact-modal-header">
            <div>
                <h2 id="contactModalTitle">Add New Contact</h2>
                <p>Enter the contact's business and contact information.</p>
            </div>

            <button type="button" class="contact-modal-close" id="closeContactModal" data-popup-close="contactModal" aria-label="Close">&times;</button>
        </div>

        <form id="contactForm" novalidate>

            <input type="hidden" id="contactId" name="contact_id">

            <div class="contact-form-body">

                <div class="contact-form-grid">

                    <div class="form-group full-width">
                        <label for="brokerage_name">Brokerage Name *</label>
                        <input type="text"
                               id="brokerage_name"
                               name="brokerage_name"
                               maxlength="255"
                               required>
                        <div class="field-error" id="brokerage_name_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact Person</label>
                        <input type="text"
                               id="contact"
                               name="contact"
                               maxlength="255">
                        <div class="field-error" id="contact_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="authorizing_authority">Authorizing Authority</label>
                        <input type="text"
                               id="authorizing_authority"
                               name="authorizing_authority"
                               maxlength="255">
                        <div class="field-error" id="authorizing_authority_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               maxlength="255">
                        <div class="field-error" id="email_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="cell_phone">Phone Number</label>
                        <input type="tel"
                               id="cell_phone"
                               name="cell_phone"
                               maxlength="50">
                        <div class="field-error" id="cell_phone_error"></div>
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Street Address</label>
                        <input type="text"
                               id="address"
                               name="address"
                               maxlength="255">
                        <div class="field-error" id="address_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text"
                               id="city"
                               name="city"
                               maxlength="255">
                        <div class="field-error" id="city_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="province_state">Province / State</label>
                        <input type="text"
                               id="province_state"
                               name="province_state"
                               maxlength="255">
                        <div class="field-error" id="province_state_error"></div>
                    </div>

                    <div class="form-group">
                        <label for="zip_code">Postal / ZIP Code</label>
                        <input type="text"
                               id="zip_code"
                               name="zip_code"
                               maxlength="50">
                        <div class="field-error" id="zip_code_error"></div>
                    </div>

                </div>

                <div id="contactFormErrors"
                     class="contact-form-errors"
                     style="display:none;">
                </div>

            </div>

            <div class="contact-modal-footer">

                <button type="button" class="contact-cancel-btn" id="cancelContactModal" data-popup-close="contactModal">Cancel</button>

                <button type="submit"
                        class="contact-save-btn"
                        id="saveContactBtn">
                    Save Contact
                </button>

            </div>

        </form>

    </div>

</div>
