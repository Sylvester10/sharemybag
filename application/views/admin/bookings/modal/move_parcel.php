<div class="modal fade admin-form-modal admin-form-modal--compact admin-parcel-modal" id="moveParcelModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="moveParcelModalLabel">
    <div class="modal-dialog modal-md admin-form-modal__dialog" role="document">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header">
                <div class="pull-right">
                    <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">×</button>
                </div>
                <h4 class="modal-title" id="moveParcelModalLabel">
                    Move Parcel: <strong id="move_parcel_reference">—</strong>
                </h4>
            </div>
            <div class="modal-body admin-form-modal__body admin-parcel-modal__body">
                <input type="hidden" id="move_booking_id">

                <div id="move_parcel_context" class="admin-parcel-note mb-3" style="margin-top:0;">
                    <i class="las la-spinner la-spin"></i> Loading eligible travellers...
                </div>

                <div class="admin-parcel-modal__grid">
                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label class="admin-parcel-field__label" for="move_target_traveller_id">Move To *</label>
                        <select id="move_target_traveller_id" class="form-control admin-parcel-field__input" disabled>
                            <option value="">Loading eligible travellers...</option>
                        </select>
                        <small id="move_target_traveller_error" class="admin-form-field-error d-none"></small>
                        <small class="admin-form-modal__note">Only approved, unlocked travellers on the same route with enough available space are shown.</small>
                    </div>

                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label class="admin-parcel-field__label" for="move_parcel_reason">Reason for Move *</label>
                        <textarea id="move_parcel_reason" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="4" maxlength="500" placeholder="Explain why this parcel is being reassigned"></textarea>
                        <small id="move_parcel_reason_error" class="admin-form-field-error d-none"></small>
                    </div>

                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label style="font-weight:500; cursor:pointer;">
                            <input type="checkbox" id="confirm_move_parcel"> I confirm that the parcel should be moved to the selected traveller.
                        </label>
                        <small id="confirm_move_parcel_error" class="admin-form-field-error d-none"></small>
                    </div>
                </div>

                <div id="move_parcel_error" class="alert alert-danger d-none admin-parcel-alert"></div>
            </div>
            <div class="modal-footer admin-form-modal__footer admin-parcel-modal__footer">
                <button type="button" class="btn btn-primary" id="confirmMoveParcel" disabled>
                    <i class="las la-exchange-alt"></i> Move Parcel
                </button>
            </div>
        </div>
    </div>
</div>
