<!-- views/admin/admins/add_admin.php -->

<div class="new-item admin-page-actions">
    <a class="btn btn-default btn-sm admin-back-btn" href="<?php echo site_url('all_admins'); ?>">
        <i class="las la-arrow-left"></i> Back to Admins
    </a>
</div>

<div class="row admin-form-grids">
    <div class="col-md-6 col-sm-12">
        <div class="admin-panel-card ">
            <p class="admin-form-note">Create internal admin access with a role that matches the exact operational scope.</p>

                <?php echo form_open(site_url('add_admin_ajax'), ['id' => 'add_admin_form']); ?>

                <div class="form-group">
                    <label>Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                        placeholder="e.g. Jane Smith" value="<?php echo set_value('name'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                        placeholder="e.g. jane@sharemybag.co.uk" value="<?php echo set_value('email'); ?>" required>
                </div>

                <div class="form-group">
                    <?php $this->load->view('partials/phone_input', array(
                        'wrapper_class' => '',
                        'field_name' => 'phone',
                        'country_code_name' => 'country_code',
                        'country_code_id' => 'adminCountryCode',
                        'input_id' => 'adminPhone',
                        'country_code' => set_value('country_code', '+44'),
                        'local_number' => set_value('phone'),
                        'label' => 'Phone Number',
                        'required' => true,
                    )); ?>
                </div>

                <div class="form-group">
                    <label>Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">— Select Role —</option>
                        <option value="super_admin" <?php echo set_select('role', 'super_admin'); ?>>
                            Super Admin (full access)
                        </option>
                        <option value="customer_support" <?php echo set_select('role', 'customer_support'); ?>>
                            Customer Support (bookings + users, no finances)
                        </option>
                        <option value="traveller_support" <?php echo set_select('role', 'traveller_support'); ?>>
                            Traveller Support (travellers section only)
                        </option>
                    </select>
                    <small class="form-text text-muted" id="role_hint"></small>
                </div>

                <div class="form-group">
                    <input type="hidden" name="can_manage_shipping" value="0">
                    <div class="checkbox">
                        <label for="canManageShipping">
                            <input type="checkbox" id="canManageShipping" name="can_manage_shipping" value="1"
                                <?php echo set_checkbox('can_manage_shipping', '1'); ?>>
                            Allow access to the Shipping workspace
                        </label>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <label>Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Min. 6 characters" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="c_password" class="form-control"
                        placeholder="Repeat password" required>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-plus me-1"></i> Create Admin Account
                    </button>
                </div>

                <?php echo form_close(); ?>
        </div>
    </div>

    <div class="col-md-5 col-sm-12">
        <div class="admin-panel-card">
            <h2>Role Permissions</h2>
            <table class="table table-sm table-bordered admin-panel-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Area</th>
                            <th class="text-center">Super Admin</th>
                            <th class="text-center">Customer Support</th>
                            <th class="text-center">Traveller Support</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Dashboard</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                        </tr>
                        <tr>
                            <td>Bookings (view + manage parcels)</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                        <tr>
                            <td>Shipping workspace</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center"><span class="badge badge-info">Admin controlled</span></td>
                            <td class="text-center"><span class="badge badge-info">Admin controlled</span></td>
                        </tr>
                        <tr>
                            <td>Users (view + approve + block)</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                        <tr>
                            <td>Delete Users</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                        <tr>
                            <td>Travellers (view + approve + add + edit)</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                        </tr>
                        <tr>
                            <td>Delete Travellers</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                        <tr>
                            <td>Finances</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                        <tr>
                            <td>Exchange Rates</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                        <tr>
                            <td>Admin Accounts</td>
                            <td class="text-center text-success"><i class="las la-check"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                            <td class="text-center text-danger"><i class="las la-times"></i></td>
                        </tr>
                    </tbody>
            </table>
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
    var shippingAccess = document.getElementById('canManageShipping');

    function updateAdminAccessFields() {
        document.getElementById('role_hint').textContent = roleHints[roleSelect.value] || '';
        var isSuperAdmin = roleSelect.value === 'super_admin';
        if (isSuperAdmin) {
            shippingAccess.checked = true;
        }
        shippingAccess.disabled = isSuperAdmin;
    }

    roleSelect.addEventListener('change', updateAdminAccessFields);
    updateAdminAccessFields();
</script>
