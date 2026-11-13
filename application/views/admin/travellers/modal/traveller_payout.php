<div class="modal fade admin-form-modal admin-form-modal--compact" id="travellerPayoutModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="travellerPayoutTitle">
    <div class="modal-dialog admin-form-modal__dialog" role="document">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header">
                <div class="pull-right"><button type="button" class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close">&times;</button></div>
                <h4 class="modal-title admin-form-modal__title" id="travellerPayoutTitle">Traveller Payout</h4>
            </div>
            <form id="travellerPayoutForm" class="admin-form-modal__form" novalidate>
                <div class="modal-body admin-form-modal__body">
                    <div class="alert alert-danger d-none" id="traveller_payout_error" role="alert"></div>
                    <div id="traveller_payout_loading" role="status"><i class="las la-spinner la-spin" aria-hidden="true"></i> Loading payout details...</div>
                    <div id="traveller_payout_details" class="d-none">
                        <p id="traveller_payout_name"></p>
                        <p><b>Outstanding Payout</b></p>
                        <div id="traveller_payout_amounts"></div>
                        <p id="traveller_payout_eligibility" class="text-muted"></p>
                        <div class="form-group" id="traveller_payout_confirmation">
                            <label for="traveller_payout_confirm_paid"><input type="checkbox" id="traveller_payout_confirm_paid" name="confirm_paid" value="1" required> I confirm this traveller has been paid.</label>
                        </div>
                        <div id="traveller_payout_history" class="d-none">
                            <h5>Payout History</h5>
                            <div class="table-responsive"><table class="table table-bordered table-hover">
                                <thead><tr><th>Recorded</th><th>Amount</th><th>Type</th></tr></thead>
                                <tbody id="traveller_payout_history_rows"></tbody>
                            </table></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer admin-form-modal__footer">
                    <button type="submit" class="btn btn-primary" id="traveller_payout_submit" disabled><i class="las la-check"></i> Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
