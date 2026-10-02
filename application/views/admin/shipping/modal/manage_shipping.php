<?php
$current_admin_id = isset($current_admin_id) ? (int) $current_admin_id : 0;
$lock_staff_selection = !empty($lock_staff_selection);
?>
<div class="modal fade admin-form-modal admin-form-modal--wide admin-shipping-modal" id="manageShippingModal" tabindex="-1" role="dialog" aria-hidden="true" aria-modal="true" aria-labelledby="shippingModalTitle">
    <div class="modal-dialog modal-lg admin-form-modal__dialog" role="document">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header ">
                <div class="pull-right"><button class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">&times;</button></div>
                <h4 class="modal-title admin-form-modal__title" id="shippingModalTitle">Create Shipping Record</h4>
            </div>

            <div class="modal-body admin-form-modal__body admin-shipping-modal__body">
                <div class="admin-shipping-steps">
                    <div class="admin-shipping-step is-active" data-step-indicator="1">1. Search Booking</div>
                    <div class="admin-shipping-step" data-step-indicator="2">2. Shipping Details</div>
                </div>

                <div class="alert alert-danger d-none admin-parcel-alert" id="shipping_modal_error"></div>
                <div class="alert alert-success d-none admin-parcel-alert" id="shipping_modal_success"></div>

                <input type="hidden" id="shipping_mode" value="create">
                <input type="hidden" id="shipping_booking_id" value="0">

                <div class="admin-shipping-step-panel" data-step-panel="1">
                    <div class="admin-shipping-search-card">
                        <label class="admin-parcel-field__label" for="shipping_search_query">Quick Booking Search</label>
                        <div class="input-group">
                            <input type="text" class="form-control admin-parcel-field__input" id="shipping_search_query" placeholder="Search by tracking ID, user name, traveller name, phone or email">
                            <span class="input-group-btn">
                                <button class="btn btn-primary admin-shipping-search-btn" type="button" id="shipping_search_btn">
                                    <i class="las la-search"></i> Search
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive admin-shipping-search-results-wrap">
                        <table class="table table-bordered table-hover admin-shipping-search-results" id="shipping_search_results">
                            <thead>
                                <tr>
                                    <th>Tracking ID</th>
                                    <th>User</th>
                                    <th>Traveller</th>
                                    <th>Pickup Address</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Search for a booking to continue.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-shipping-step-panel d-none" data-step-panel="2">
                    <div class="admin-shipping-context" id="shipping_selected_context">
                        <div class="admin-shipping-context__tracking">No booking selected yet.</div>
                    </div>

                    <div class="admin-parcel-modal__grid">
                        <div class="admin-parcel-field--full">
                            <label class="admin-parcel-field__label" for="shipping_carrier_tracking_id">Carrier Tracking ID</label>
                            <input type="text" id="shipping_carrier_tracking_id" class="form-control admin-parcel-field__input" maxlength="150" placeholder="Enter the courier or carrier tracking ID">
                        </div>

                        <div class="admin-parcel-field--full">
                            <label class="admin-parcel-field__label" for="shipping_pickup_address">Traveler's Pickup Address</label>
                            <textarea id="shipping_pickup_address" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="3"></textarea>
                        </div>

                        <div class="admin-parcel-field--full">
                            <label class="admin-parcel-field__label" for="shipping_dropoff_address">Receiver's Drop-off Address</label>
                            <textarea id="shipping_dropoff_address" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="3"></textarea>
                        </div>

                        <div>
                            <label class="admin-parcel-field__label" for="shipping_pickup_country">Pickup Country</label>
                            <input type="text" id="shipping_pickup_country" class="form-control admin-parcel-field__input" placeholder="Pickup country">
                        </div>

                        <div>
                            <label class="admin-parcel-field__label" for="shipping_courier">Courier</label>
                            <select id="shipping_courier" class="form-control admin-parcel-field__input">
                                <?php foreach ($courier_options as $courier) { ?>
                                    <option value="<?php echo html_escape($courier); ?>"><?php echo html_escape($courier); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label class="admin-parcel-field__label" for="shipping_staff_admin_id">Staff</label>
                            <select id="shipping_staff_admin_id" class="form-control admin-parcel-field__input" <?php echo $lock_staff_selection ? 'disabled' : ''; ?>>
                                <option value="">Select staff</option>
                                <?php foreach ($staff_options as $staff) { ?>
                                    <option value="<?php echo (int) $staff->id; ?>" <?php echo (int) $staff->id === $current_admin_id ? 'selected' : ''; ?>>
                                        <?php echo html_escape($staff->name); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div>
                            <label class="admin-parcel-field__label" for="shipping_status">Status</label>
                            <select id="shipping_status" class="form-control admin-parcel-field__input">
                                <?php foreach (shipping_status_creation_options() as $statusOption) { ?>
                                    <option value="<?php echo html_escape($statusOption); ?>"><?php echo html_escape($statusOption); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="admin-parcel-field--full">
                            <label class="admin-parcel-field__label" for="shipping_tracking_note">Shipping Note</label>
                            <textarea id="shipping_tracking_note" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="3" placeholder="Optional note to include in the shipping update history."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer admin-form-modal__footer admin-shipping-modal__footer">
                <button type="button" class="btn btn-default admin-parcel-btn--muted d-none" id="shipping_back_btn">
                    <i class="las la-arrow-left"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="shipping_next_btn">
                    Continue <i class="las la-arrow-right"></i>
                </button>
                <button type="button" class="btn btn-primary d-none" id="shipping_submit_btn">
                    <i class="las la-save"></i> Save Shipping
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade admin-form-modal admin-form-modal--compact admin-shipping-modal" id="shippingStatusModal" tabindex="-1" role="dialog" aria-hidden="true" aria-modal="true" aria-labelledby="shippingStatusModalTitle">
    <div class="modal-dialog admin-form-modal__dialog" role="document">
        <div class="modal-content admin-form-modal__content">
            <div class="modal-header  admin-shipping-modal__header admin-shipping-modal__header--status">
                <div>
                    <p class="admin-shipping-modal__eyebrow">Shipping Update</p>
                    <h3 class="admin-shipping-modal__title admin-form-modal__title" id="shippingStatusModalTitle">Add Tracking Update</h3>
                    <p class="admin-shipping-modal__subtitle">Add a new update to the shipping history and sync the active status for the booking.</p>
                </div>
                <button type="button" class="btn btn-danger btn-sm modal_close_btn" data-dismiss="modal" aria-label="Close" title="Close">&times;</button>
            </div>
            <div class="modal-body admin-form-modal__body admin-shipping-modal__body">
                <div class="alert alert-danger d-none admin-parcel-alert" id="shipping_status_error"></div>
                <input type="hidden" id="shipping_status_booking_id" value="0">

                <div class="admin-parcel-modal__grid">
                    <div>
                        <label class="admin-parcel-field__label" for="shipping_status_update">Status</label>
                        <select id="shipping_status_update" class="form-control admin-parcel-field__input">
                            <option value="">Select the next status</option>
                        </select>
                    </div>

                    <div>
                        <label class="admin-parcel-field__label" for="shipping_status_heading">Heading</label>
                        <input type="text" id="shipping_status_heading" class="form-control admin-parcel-field__input" placeholder="Optional heading">
                    </div>

                    <div class="admin-parcel-field--full">
                        <label class="admin-parcel-field__label" for="shipping_status_body">Update Note</label>
                        <textarea id="shipping_status_body" class="form-control admin-parcel-field__input admin-parcel-field__textarea" rows="4" placeholder="Describe what changed for this shipment."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer admin-form-modal__footer admin-shipping-modal__footer">
                <button type="button" class="btn btn-default admin-parcel-btn--muted" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="shipping_status_submit_btn">
                    <i class="las la-sync"></i> Add Update
                </button>
            </div>
        </div>
    </div>
</div>
