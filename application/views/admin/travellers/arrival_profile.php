<?php
$this->load->view('admin/travellers/traveller_profile', array(
    'y' => $y,
    'booking_details' => $booking_details,
    'is_super_admin' => $is_super_admin,
    'is_arrival_profile' => true,
    'staff_options' => $staff_options,
    'courier_options' => $courier_options,
    'current_admin_id' => $current_admin_id,
    'lock_staff_selection' => $lock_staff_selection,
));
