<!-- views/admin/admins/edit_admin.php -->

<div class="new-item admin-page-actions">
    <a class="btn btn-default btn-sm admin-back-btn" href="<?php echo site_url('all_admins'); ?>">
        <i class="las la-arrow-left"></i> Back to Admins
    </a>
</div>

<div class="row admin-form-grid">
    <div class="col-md-6 col-sm-12">
        <div class="admin-panel-card ">
            <h2>Edit Admin: <?php echo htmlspecialchars($y->name); ?></h2>
            <p class="admin-form-note">Use role changes carefully. Access controls here affect the live admin surface immediately.</p>

                <?php echo form_open(site_url('edit_admin_ajax/' . $y->id), ['id' => 'edit_admin_form']); ?>

                <div class="form-group">
                    <label>Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                        value="<?php echo htmlspecialchars(set_value('name', $y->name)); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo htmlspecialchars(set_value('email', $y->email)); ?>" required>
                </div>

                <div class="form-group">
                    <?php $this->load->view('partials/phone_input', array(
                        'wrapper_class' => '',
                        'field_name' => 'phone',
                        'country_code_name' => 'country_code',
                        'country_code_id' => 'adminCountryCode',
                        'input_id' => 'adminPhone',
                        'value' => set_value('phone', $y->phone ?? ''),
                        'label' => 'Phone Number',
                        'required' => true,
                    )); ?>
                </div>

                <div class="form-group">
                    <label>Role <span class="text-danger">*</span></label>
                    <?php
                    // Prevent super_admin from accidentally downgrading themselves
                    $is_self = ($y->email === $this->session->admin_email);
                    if ($is_self): ?>
                        <input type="hidden" name="role" value="super_admin">
                        <input type="text" class="form-control" value="Super Admin (your own account)" disabled>
                        <small class="text-muted">You cannot change your own role.</small>
                    <?php else: ?>
                        <select name="role" class="form-control" required>
                            <option value="super_admin"
                                <?php echo (set_value('role', $y->role) === 'super_admin') ? 'selected' : ''; ?>>
                                Super Admin (full access)
                            </option>
                            <option value="customer_support"
                                <?php echo (set_value('role', $y->role) === 'customer_support') ? 'selected' : ''; ?>>
                                Customer Support (bookings + users, no finances)
                            </option>
                            <option value="traveller_support"
                                <?php echo (set_value('role', $y->role) === 'traveller_support') ? 'selected' : ''; ?>>
                                Traveller Support (travellers section only)
                            </option>
                        </select>
                        <small class="form-text text-muted" id="role_hint_edit"></small>
                    <?php endif; ?>
                </div>

                <?php
                $selected_role = set_value('role', $y->role);
                $stored_shipping_access = property_exists($y, 'can_manage_shipping')
                    ? (int) $y->can_manage_shipping === 1
                    : in_array($y->role, array('super_admin', 'customer_support'), true);
                $shipping_access_checked = $selected_role === 'super_admin'
                    || set_value('can_manage_shipping', $stored_shipping_access ? '1' : '0') === '1';
                ?>
                <div class="form-group">
                    <input type="hidden" name="can_manage_shipping" value="0">
                    <div class="checkbox">
                        <label for="canManageShippingEdit">
                            <input type="checkbox" id="canManageShippingEdit" name="can_manage_shipping" value="1"
                                <?php echo $shipping_access_checked ? 'checked' : ''; ?>
                                <?php echo $selected_role === 'super_admin' ? 'disabled' : ''; ?>>
                            Allow access to the Shipping workspace
                        </label>
                    </div>
                    <small class="form-text text-muted">Shipping access is account-specific for support staff. Super Admin access cannot be disabled.</small>
                </div>

                <hr>
                <p class="text-muted small">
                    <i class="las la-info-circle"></i>
                    Leave password fields blank to keep the current password unchanged.
                </p>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Leave blank to keep current password">
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="c_password" class="form-control"
                        placeholder="Repeat new password">
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-save me-1"></i> Save Changes
                    </button>
                    <a href="<?php echo site_url('all_admins'); ?>" class="btn btn-default">Cancel</a>
                </div>

                <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    var roleHints = {
        'super_admin': 'Full access to everything including finances and admin management.',
        'customer_support': 'Can manage bookings and users. Cannot access finances, travellers, or admin accounts.',
        'traveller_support': 'Can only view and manage travellers. No access to users, bookings, or finances.'
    };
    var roleSelect = document.querySelector('select[name="role"]');
    var shippingAccess = document.getElementById('canManageShippingEdit');
    if (roleSelect) {
        var hintEl = document.getElementById('role_hint_edit');

        function updateHint() {
            hintEl.textContent = roleHints[roleSelect.value] || '';
            var isSuperAdmin = roleSelect.value === 'super_admin';
            if (isSuperAdmin) {
                shippingAccess.checked = true;
            }
            shippingAccess.disabled = isSuperAdmin;
        }
        roleSelect.addEventListener('change', updateHint);
        updateHint(); // show on load
    }
</script>
