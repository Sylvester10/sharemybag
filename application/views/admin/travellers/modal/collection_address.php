<div class="modal fade admin-form-modal admin-form-modal--compact" id="travellerCollectionAddressModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="travellerCollectionAddressTitle">
    <div class="modal-dialog admin-form-modal__dialog" role="document">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header">
                <div class="pull-right"><button type="button" class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close">&times;</button></div>
                <h4 class="modal-title admin-form-modal__title" id="travellerCollectionAddressTitle">Edit Collection Address</h4>
            </div>
            <form id="travellerCollectionAddressForm" class="admin-form-modal__form" data-traveller-id="<?php echo (int) $y->id; ?>" novalidate>
                <div class="modal-body admin-form-modal__body">
                    <div id="traveller_collection_address_form_error" class="alert alert-danger d-none" role="alert"></div>
                    <input type="hidden" name="address_token" value="<?php echo hash('sha256', (string) $y->additional_info); ?>">
                    <div class="form-group">
                        <label for="traveller_collection_address">Traveler's Collection Address</label>
                        <textarea id="traveller_collection_address" name="collection_address" class="form-control" rows="3" maxlength="100" required><?php echo html_escape((string) $y->additional_info); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer admin-form-modal__footer">
                    <button type="submit" class="btn btn-primary" id="traveller_collection_address_submit"><i class="las la-check"></i> Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>
