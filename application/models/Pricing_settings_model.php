<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pricing_settings_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->table = 'pricing_settings';
        $this->primary_cols = array('id');
    }

    public function has_pricing_table()
    {
        return $this->db->table_exists('pricing_settings')
            && $this->db->field_exists('premium_small_rate', 'pricing_settings')
            && $this->db->field_exists('premium_laptop_rate', 'pricing_settings')
            && $this->db->field_exists('premium_small_payout_rate', 'pricing_settings')
            && $this->db->field_exists('premium_laptop_payout_rate', 'pricing_settings');
    }

    public function get_all_routes()
    {
        $definitions = booking_route_definition_map();
        $rows = array();

        foreach ($definitions as $route_key => $definition) {
            $rows[$route_key] = array_merge($definition, array(
                'id' => null,
                'updated_at' => null,
                'updated_by_name' => null,
                'updated_by_role' => null,
                'source' => 'fallback',
            ));
        }

        if (!$this->has_pricing_table()) {
            return array_values($rows);
        }

        $this->db->select('pricing_settings.*, admins.name AS updated_by_name, admins.role AS updated_by_role');
        $this->db->from('pricing_settings');
        $this->db->join('admins', 'admins.id = pricing_settings.updated_by', 'left');
        $this->db->where('pricing_settings.is_active', 1);
        $query = $this->db->get();

        foreach ($query->result() as $row) {
            if (!isset($rows[$row->route_key])) {
                continue;
            }

            $rows[$row->route_key] = array_merge($rows[$row->route_key], array(
                'id' => (int) $row->id,
                'origin' => $row->origin,
                'destination' => $row->destination,
                'currency' => currency_code_normalize($row->currency),
                'service_charge' => (float) $row->service_charge,
                'normal_rate' => (float) $row->normal_rate,
                'special_rate' => (float) $row->special_rate,
                'duty_free_rate' => (float) $row->duty_free_rate,
                'premium_small_rate' => (float) $row->premium_small_rate,
                'premium_laptop_rate' => (float) $row->premium_laptop_rate,
                'normal_payout_rate' => (float) $row->normal_payout_rate,
                'special_payout_rate' => (float) $row->special_payout_rate,
                'premium_small_payout_rate' => (float) $row->premium_small_payout_rate,
                'premium_laptop_payout_rate' => (float) $row->premium_laptop_payout_rate,
                'updated_at' => $row->updated_at,
                'updated_by_name' => $row->updated_by_name,
                'updated_by_role' => $row->updated_by_role,
                'source' => 'database',
            ));
        }

        return array_values($rows);
    }

    public function save_route_pricing($route_key, array $payload, $admin)
    {
        if (!$this->has_pricing_table()) {
            return array('status' => false, 'msg' => 'Pricing tables are not available yet. Run the setup SQL first.');
        }

        $definitions = booking_route_definition_map();
        if (!isset($definitions[$route_key])) {
            return array('status' => false, 'msg' => 'Invalid route selected.');
        }

        $definition = $definitions[$route_key];
        $admin_id = isset($admin->id) ? (int) $admin->id : null;
        $admin_name = isset($admin->name) ? (string) $admin->name : 'Unknown Admin';
        $now = date('Y-m-d H:i:s');

        $data = array(
            'route_key' => $route_key,
            'origin' => $definition['origin'],
            'destination' => $definition['destination'],
            'currency' => $definition['currency'],
            'service_charge' => round((float) $payload['service_charge'], 2),
            'normal_rate' => round((float) $payload['normal_rate'], 2),
            'special_rate' => round((float) $payload['special_rate'], 2),
            'duty_free_rate' => round((float) $payload['duty_free_rate'], 2),
            'premium_small_rate' => round((float) $payload['premium_small_rate'], 2),
            'premium_laptop_rate' => round((float) $payload['premium_laptop_rate'], 2),
            'normal_payout_rate' => round((float) $payload['normal_payout_rate'], 2),
            'special_payout_rate' => round((float) $payload['special_payout_rate'], 2),
            'premium_small_payout_rate' => round((float) $payload['premium_small_payout_rate'], 2),
            'premium_laptop_payout_rate' => round((float) $payload['premium_laptop_payout_rate'], 2),
            'is_active' => 1,
            'updated_by' => $admin_id,
            'updated_at' => $now,
        );

        if ($data['normal_payout_rate'] > $data['normal_rate']) {
            return array('status' => false, 'msg' => 'Normal payout cannot be higher than the normal rate.');
        }
        if ($data['special_payout_rate'] > $data['special_rate']) {
            return array('status' => false, 'msg' => 'Special payout cannot be higher than the special rate.');
        }
        if ($data['premium_small_payout_rate'] > $data['premium_small_rate']) {
            return array('status' => false, 'msg' => 'Premium small payout cannot be higher than the premium small rate.');
        }
        if ($data['premium_laptop_payout_rate'] > $data['premium_laptop_rate']) {
            return array('status' => false, 'msg' => 'Premium laptop payout cannot be higher than the premium laptop rate.');
        }

        $existing = $this->db->where('route_key', $route_key)->get('pricing_settings')->row_array();
        $old_values = $existing ? $existing : $definition;

        $this->db->trans_start();

        if ($existing) {
            $this->db->where('id', (int) $existing['id'])->update('pricing_settings', $data);
            $pricing_setting_id = (int) $existing['id'];
        } else {
            $data['date_added'] = $now;
            $this->db->insert('pricing_settings', $data);
            $pricing_setting_id = (int) $this->db->insert_id();
        }

        if ($this->db->table_exists('pricing_settings_logs')) {
            $this->db->insert('pricing_settings_logs', array(
                'pricing_setting_id' => $pricing_setting_id,
                'route_key' => $route_key,
                'old_values' => json_encode($old_values),
                'new_values' => json_encode($data),
                'admin_id' => $admin_id,
                'admin_name' => $admin_name,
                'date_added' => $now,
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return array('status' => false, 'msg' => 'Pricing could not be updated. Please try again.');
        }

        return array('status' => true, 'msg' => 'Pricing updated successfully.');
    }
}
