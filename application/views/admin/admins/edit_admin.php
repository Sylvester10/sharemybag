<!-- views/admin/admins/edit_admin.php -->

<div class="new-item admin-page-actions">
    <a class="btn btn-default btn-sm" href="<?php echo site_url('all_admins'); ?>">
        <i class="las la-arrow-left"></i> All Admins
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
                    <label>Phone Number <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars(set_value('phone', $y->phone ?? '')); ?>" required>
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
    if (roleSelect) {
        var hintEl = document.getElementById('role_hint_edit');

        function updateHint() {
            hintEl.textContent = roleHints[roleSelect.value] || '';
        }
        roleSelect.addEventListener('change', updateHint);
        updateHint(); // show on load
    }
</script>
