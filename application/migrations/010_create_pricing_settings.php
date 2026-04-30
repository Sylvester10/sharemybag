<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_pricing_settings extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('pricing_settings')) {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ),
                'route_key' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ),
                'origin' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ),
                'destination' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ),
                'currency' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                ),
                'service_charge' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'normal_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'special_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'duty_free_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'premium_small_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'premium_laptop_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'normal_payout_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'special_payout_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'premium_small_payout_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'premium_laptop_payout_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => '0.00',
                ),
                'is_active' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
                ),
                'updated_by' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ),
                'date_added' => array(
                    'type' => 'DATETIME',
                    'null' => true,
                ),
                'updated_at' => array(
                    'type' => 'DATETIME',
                    'null' => true,
                ),
            ));
            $this->dbforge->add_key('id', true);
            $this->dbforge->add_key('route_key');
            $this->dbforge->create_table('pricing_settings');
            $this->db->query('ALTER TABLE `pricing_settings` ADD UNIQUE KEY `uniq_pricing_route` (`route_key`)');
        }

        $this->sync_pricing_settings_schema();

        if (!$this->db->table_exists('pricing_settings_logs')) {
            $this->dbforge->add_field(array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ),
                'pricing_setting_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ),
                'route_key' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                ),
                'old_values' => array(
                    'type' => 'LONGTEXT',
                    'null' => true,
                ),
                'new_values' => array(
                    'type' => 'LONGTEXT',
                    'null' => true,
                ),
                'admin_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ),
                'admin_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ),
                'date_added' => array(
                    'type' => 'DATETIME',
                    'null' => true,
                ),
            ));
            $this->dbforge->add_key('id', true);
            $this->dbforge->create_table('pricing_settings_logs');
        }

        $now = date('Y-m-d H:i:s');
        $seed_routes = array(
            array(
                'route_key' => 'ng_uk',
                'origin' => 'Nigeria',
                'destination' => 'United Kingdom',
                'currency' => 'GBP',
                'service_charge' => 3.49,
                'normal_rate' => 9.50,
                'special_rate' => 10.00,
                'duty_free_rate' => 0.00,
                'premium_small_rate' => 15.00,
                'premium_laptop_rate' => 20.00,
                'normal_payout_rate' => 5.00,
                'special_payout_rate' => 5.00,
                'premium_small_payout_rate' => 10.00,
                'premium_laptop_payout_rate' => 15.00,
            ),
            array(
                'route_key' => 'uk_ng',
                'origin' => 'United Kingdom',
                'destination' => 'Nigeria',
                'currency' => 'GBP',
                'service_charge' => 3.99,
                'normal_rate' => 6.50,
                'special_rate' => 6.50,
                'duty_free_rate' => 9.50,
                'premium_small_rate' => 15.00,
                'premium_laptop_rate' => 20.00,
                'normal_payout_rate' => 4.50,
                'special_payout_rate' => 4.50,
                'premium_small_payout_rate' => 10.00,
                'premium_laptop_payout_rate' => 15.00,
            ),
            array(
                'route_key' => 'ng_ca',
                'origin' => 'Nigeria',
                'destination' => 'Canada',
                'currency' => 'CAD',
                'service_charge' => 6.44,
                'normal_rate' => 17.50,
                'special_rate' => 18.50,
                'duty_free_rate' => 0.00,
                'premium_small_rate' => 36.93,
                'premium_laptop_rate' => 46.16,
                'normal_payout_rate' => 10.00,
                'special_payout_rate' => 10.00,
                'premium_small_payout_rate' => 18.47,
                'premium_laptop_payout_rate' => 27.70,
            ),
            array(
                'route_key' => 'ca_ng',
                'origin' => 'Canada',
                'destination' => 'Nigeria',
                'currency' => 'CAD',
                'service_charge' => 6.44,
                'normal_rate' => 17.50,
                'special_rate' => 17.50,
                'duty_free_rate' => 18.50,
                'premium_small_rate' => 36.93,
                'premium_laptop_rate' => 46.16,
                'normal_payout_rate' => 10.00,
                'special_payout_rate' => 10.00,
                'premium_small_payout_rate' => 18.47,
                'premium_laptop_payout_rate' => 27.70,
            ),
        );

        foreach ($seed_routes as $route) {
            $exists = $this->db->where('route_key', $route['route_key'])->get('pricing_settings')->row();
            if ($exists) {
                $this->db->where('id', (int) $exists->id)->update('pricing_settings', array(
                    'origin' => $route['origin'],
                    'destination' => $route['destination'],
                    'currency' => $route['currency'],
                    'service_charge' => $route['service_charge'],
                    'normal_rate' => $route['normal_rate'],
                    'special_rate' => $route['special_rate'],
                    'duty_free_rate' => $route['duty_free_rate'],
                    'premium_small_rate' => $route['premium_small_rate'],
                    'premium_laptop_rate' => $route['premium_laptop_rate'],
                    'normal_payout_rate' => $route['normal_payout_rate'],
                    'special_payout_rate' => $route['special_payout_rate'],
                    'premium_small_payout_rate' => $route['premium_small_payout_rate'],
                    'premium_laptop_payout_rate' => $route['premium_laptop_payout_rate'],
                    'is_active' => 1,
                    'updated_at' => $now,
                ));
                continue;
            }

            $this->db->insert('pricing_settings', array(
                'route_key' => $route['route_key'],
                'origin' => $route['origin'],
                'destination' => $route['destination'],
                'currency' => $route['currency'],
                'service_charge' => $route['service_charge'],
                'normal_rate' => $route['normal_rate'],
                'special_rate' => $route['special_rate'],
                'duty_free_rate' => $route['duty_free_rate'],
                'premium_small_rate' => $route['premium_small_rate'],
                'premium_laptop_rate' => $route['premium_laptop_rate'],
                'normal_payout_rate' => $route['normal_payout_rate'],
                'special_payout_rate' => $route['special_payout_rate'],
                'premium_small_payout_rate' => $route['premium_small_payout_rate'],
                'premium_laptop_payout_rate' => $route['premium_laptop_payout_rate'],
                'is_active' => 1,
                'date_added' => $now,
                'updated_at' => $now,
            ));
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('pricing_settings_logs', true);
        $this->dbforge->drop_table('pricing_settings', true);
    }

    private function sync_pricing_settings_schema()
    {
        $columns = array(
            'premium_small_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'after' => 'duty_free_rate',
            ),
            'premium_laptop_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'after' => 'premium_small_rate',
            ),
            'premium_small_payout_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'after' => 'special_payout_rate',
            ),
            'premium_laptop_payout_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => '0.00',
                'after' => 'premium_small_payout_rate',
            ),
        );

        foreach ($columns as $column => $definition) {
            if (!$this->db->field_exists($column, 'pricing_settings')) {
                $this->dbforge->add_column('pricing_settings', array($column => $definition));
            }
        }

        if ($this->db->field_exists('premium_rate', 'pricing_settings')) {
            $this->db->query('UPDATE pricing_settings SET premium_small_rate = premium_rate WHERE premium_small_rate = 0');
        }

        if ($this->db->field_exists('premium_payout_rate', 'pricing_settings')) {
            $this->db->query('UPDATE pricing_settings SET premium_small_payout_rate = premium_payout_rate WHERE premium_small_payout_rate = 0');
        }

        $this->db->query("UPDATE pricing_settings SET premium_laptop_rate = CASE WHEN currency = 'CAD' THEN 46.16 ELSE 20.00 END WHERE premium_laptop_rate = 0");
        $this->db->query("UPDATE pricing_settings SET premium_laptop_payout_rate = CASE WHEN currency = 'CAD' THEN 27.70 ELSE 15.00 END WHERE premium_laptop_payout_rate = 0");
    }
}
