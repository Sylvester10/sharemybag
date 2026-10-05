<div class="modal fade admin-form-modal admin-form-modal--compact admin-parcel-modal" id="cancelParcelModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="cancelParcelModalLabel">
    <div class="modal-dialog modal-md admin-form-modal__dialog" role="document">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header">
                <div class="pull-right">
                    <button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">×</button>
                </div>
                <h4 class="modal-title" id="cancelParcelModalLabel">
                    Cancel Parcel: <strong id="cancel_parcel_reference">—</strong>
                </h4>
            </div>
            <div class="modal-body admin-form-modal__body admin-parcel-modal__body">
                <input type="hidden" id="cancel_booking_id">

                <div class="admin-parcel-note" style="margin-top:0;">
                    <i class="las la-exclamation-triangle"></i>
                    Cancellation restores the traveller’s bag space.
                </div>

                <div class="admin-parcel-modal__grid">
                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label class="admin-parcel-field__label" for="cancellation_reason">Cancellation Reason *</label>
                        <textarea id="cancellation_reason" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="4" maxlength="500" placeholder="Explain why this parcel is being cancelled"></textarea>
                        <small id="cancellation_reason_error" class="admin-form-field-error d-none"></small>
                    </div>

                    <div class="admin-parcel-field">
                        <label class="admin-parcel-field__label" for="cancel_refund_status">Refund Status *</label>
                        <select id="cancel_refund_status" class="form-control admin-parcel-field__input">
                            <option value="">Select refund status</option>
                            <option value="refunded">Refunded Manually</option>
                            <option value="not_required">No Refund Required</option>
                        </select>
                        <small id="cancel_refund_status_error" class="admin-form-field-error d-none"></small>
                    </div>

                    <div class="admin-parcel-field">
                        <label class="admin-parcel-field__label" for="cancel_refund_amount">Refund Amount (<span id="cancel_refund_currency">—</span>) *</label>
                        <input type="number" id="cancel_refund_amount" class="form-control admin-parcel-field__input" min="0" step="0.01">
                        <small id="cancel_refund_amount_error" class="admin-form-field-error d-none"></small>
                    </div>

                    <div class="admin-parcel-field admin-parcel-field--full" id="cancel_refund_reference_group" style="display:none;">
                        <label class="admin-parcel-field__label" for="cancel_refund_reference">Manual Refund Reference *</label>
                        <input type="text" id="cancel_refund_reference" class="form-control admin-parcel-field__input" maxlength="191" placeholder="Enter the bank or payment-provider refund reference">
                        <small id="cancel_refund_reference_error" class="admin-form-field-error d-none"></small>
                    </div>

                    <div class="admin-parcel-field admin-parcel-field--full">
                        <label style="font-weight:500; cursor:pointer;">
                            <input type="checkbox" id="confirm_cancel_parcel"> I confirm that I want to cancel this parcel and record the refund status shown above.
                        </label>
                        <small id="confirm_cancel_parcel_error" class="admin-form-field-error d-none"></small>
                    </div>
                </div>

                <div id="cancel_parcel_error" class="alert alert-danger d-none admin-parcel-alert"></div>
            </div>
            <div class="modal-footer admin-form-modal__footer admin-parcel-modal__footer">
                <button type="button" class="btn btn-danger" id="confirmCancelParcel" disabled>
                    <i class="las la-times"></i> Cancel Parcel
                </button>
            </div>
        </div>
    </div>
</div>
