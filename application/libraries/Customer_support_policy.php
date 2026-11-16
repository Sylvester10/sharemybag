<?php
defined('BASEPATH') or exit('No direct script access allowed');

/** The Customer Support role has a deliberately small, explicit action set. */
class Customer_support_policy
{
    private const ACTIONS = array(
        'admin_users' => array('index', 'user_ajax', 'user_profile', 'update_user_ajax',
            'valid_user_phone', 'activity_history', 'send_registration_email'),
        'admin_travellers' => array('index', 'upcoming_travellers_ajax', 'traveller_profile',
            'get_user_details', 'add_traveller_bag_space', 'remove_traveller_bag_space',
            'add_offline_booking', 'lock_traveller_bag'),
        'admin_bookings' => array('index', 'all_bookings_ajax', 'completed_bookings',
            'completed_bookings_ajax', 'canceled_bookings', 'canceled_bookings_ajax',
            'view_booking', 'add_parcel_ajax'),
        'shipping' => array('index', 'records_ajax', 'search_bookings_ajax',
            'shipping_context_ajax', 'create_shipping_ajax', 'edit_shipping_ajax',
            'update_status_ajax', 'view_shipping', 'delete_shipping'),
    );

    public function allows($controller, $method)
    {
        return in_array(strtolower($method), self::ACTIONS[strtolower($controller)] ?? array(), true);
    }

    public function can_access_traveller($traveller, $today)
    {
        return $traveller && empty($traveller->deleted_at)
            && $traveller->status === 'Approved'
            && (string) $traveller->travel_date >= $today;
    }
}
