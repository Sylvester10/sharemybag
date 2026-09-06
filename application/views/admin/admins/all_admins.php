<?php
// views/admin/admins/all_admins.php

$role_labels = [
    'super_admin'       => '<span class="badge badge-danger"><b>Super Admin</b></span>',
    'customer_support'  => '<span class="badge badge-info"><b>Customer Support</b></span>',
    'traveller_support' => '<span class="badge badge-warning"><b>Traveller Support</b></span>',
];
?>

<table class="table table-striped table-bordered table-hover" id="admins-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Shipping Access</th>
            <th>Date Added</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($admins as $i => $a): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($a->name); ?></td>
                <td><?php echo htmlspecialchars($a->email); ?></td>
                <td><?php echo htmlspecialchars($a->phone ?? '—'); ?></td>
                <td><?php echo $role_labels[$a->role] ?? '<span class="badge badge-secondary">' . htmlspecialchars($a->role) . '</span>'; ?></td>
                <td>
                    <?php
                    $has_shipping_access = $a->role === 'super_admin'
                        || (property_exists($a, 'can_manage_shipping')
                            ? (int) $a->can_manage_shipping === 1
                            : $a->role === 'customer_support');
                    ?>
                    <?php if ($has_shipping_access): ?>
                        <span class="badge badge-success"><i class="las la-check"></i> Allowed</span>
                    <?php else: ?>
                        <span class="badge badge-secondary"><i class="las la-lock"></i> Not allowed</span>
                    <?php endif; ?>
                </td>
                <td><?php echo isset($a->date_added) ? x_date($a->date_added) : '—'; ?></td>
                <td>
                    <a href="<?php echo site_url('edit-admin/' . $a->id); ?>"
                        class="btn btn-sm btn-default" title="Edit">
                        <i class="las la-pen"></i> Edit
                    </a>
                    <?php
                    // Cannot delete yourself
                    $current_email = $this->session->admin_email;
                    if ($a->email !== $current_email): ?>
                        <a href="<?php echo site_url('delete-admin/' . $a->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this admin account? This cannot be undone.');" title="Delete">
                            <i class="las la-trash"></i> Delete
                        </a>
                    <?php else: ?>
                        <span class="text-muted small"><i class="las la-lock"></i> (You)</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#admins-table').DataTable({
            order: []
        });
    });
</script>
