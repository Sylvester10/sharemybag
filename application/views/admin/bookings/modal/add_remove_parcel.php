<div class="modal fade admin-parcel-modal" id="addParcelModal" tabindex="-1" role="dialog" aria-labelledby="addParcelModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header admin-parcel-modal__header admin-parcel-modal__header--add">
                <div>
                    <p class="admin-parcel-modal__eyebrow">Booking Update</p>
                    <h4 class="modal-title admin-parcel-modal__title" id="addParcelModalLabel">
                        <i class="las la-plus-circle"></i> Add Parcel
                    </h4>
                    <p class="admin-parcel-modal__subtitle">Add a new item and recalculate the booking total automatically.</p>
                </div>
                <button type="button" class="close admin-parcel-modal__close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body admin-parcel-modal__body">
                <input type="hidden" id="add_booking_id">
                <div class="admin-parcel-modal__grid">
                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label class="admin-parcel-field__label" for="add_item_name">Item Name</label>
                        <input type="text" id="add_item_name" class="form-control admin-parcel-field__input" placeholder="e.g. Shoes, clothing, documents">
                    </div>
                    <div class="admin-parcel-field">
                        <label class="admin-parcel-field__label" for="add_category">Category</label>
                        <select id="add_category" class="form-control admin-parcel-field__input">
                            <option value="">Select category</option>
                            <option value="Normal">Normal</option>
                            <option value="Fish/Meat">Fish/Meat (Special)</option>
                            <option value="Medication">Medication (Special)</option>
                            <option value="Documents/Small Electronics">Documents/Small Electronics (Premium)</option>
                            <option value="Laptop">Laptop (Premium)</option>
                            <option value="Duty Free">Duty Free</option>
                        </select>
                    </div>
                    <div class="admin-parcel-field">
                        <label class="admin-parcel-field__label" id="add_size_label" for="add_item_size">Size (KG)</label>
                        <input type="number" id="add_item_size" class="form-control admin-parcel-field__input" min="0.5" step="0.5" placeholder="e.g. 2">
                    </div>
                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label class="admin-parcel-field__label" for="add_notes">Notes</label>
                        <textarea id="add_notes" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="3" placeholder="Optional notes for the booking log and traveller notification"></textarea>
                    </div>
                </div>
                <div class="admin-parcel-note">
                    <i class="las la-info-circle"></i>
                    Pricing, commission, and available bag space will be updated after submission.
                </div>
                <div id="add_parcel_error" class="alert alert-danger d-none admin-parcel-alert"></div>
            </div>
            <div class="modal-footer admin-parcel-modal__footer">
                <button type="button" class="btn btn-default admin-parcel-btn admin-parcel-btn--muted" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmAddParcel">
                    <i class="las la-plus"></i> Add Parcel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade admin-parcel-modal" id="removeParcelModal" tabindex="-1" role="dialog" aria-labelledby="removeParcelModalLabel">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header admin-parcel-modal__header admin-parcel-modal__header--remove">
                <div>
                    <p class="admin-parcel-modal__eyebrow">Booking Update</p>
                    <h4 class="modal-title admin-parcel-modal__title" id="removeParcelModalLabel">
                        <i class="las la-minus-circle"></i> Remove Parcel
                    </h4>
                    <p class="admin-parcel-modal__subtitle">Choose a parcel to remove and keep a short audit note if needed.</p>
                </div>
                <button type="button" class="close admin-parcel-modal__close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body admin-parcel-modal__body">
                <input type="hidden" id="remove_booking_id">
                <div class="admin-parcel-select-header">
                    <span>Select the parcel to remove</span>
                    <small>One item per request</small>
                </div>
                <div id="remove_items_list" class="admin-parcel-items"></div>
                <div class="admin-parcel-field admin-parcel-field--full admin-parcel-field--stacked">
                    <label class="admin-parcel-field__label" for="remove_notes">Notes</label>
                    <textarea id="remove_notes" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="3" placeholder="Optional reason for removal"></textarea>
                </div>
                <div id="remove_parcel_error" class="alert alert-danger d-none admin-parcel-alert"></div>
            </div>
            <div class="modal-footer admin-parcel-modal__footer">
                <button type="button" class="btn btn-default admin-parcel-btn admin-parcel-btn--muted" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmRemoveParcel" disabled>
                    <i class="las la-minus"></i> Remove Selected
                </button>
            </div>
        </div>
    </div>
</div>
