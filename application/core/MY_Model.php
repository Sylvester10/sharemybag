<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();

    }


    
    public function update($data, $key = false)
    {
        if (!$key) {
            return false;
        }

        if ($data && isAssoc($data)) {
            $columns = $this->getColumns();

            $data = extractKeys($data, $columns);

            // if (!isset($data['date_updated']) && in_array('date_updated', $columns)) {
            //     $data['date_updated'] = time();
            // }

            $this->handlePrimaryColumnWhere($this, $key);

            $this->db->update($this->table, $data);
            return ($this->db->affected_rows() > 0) ? true : false;

        }

        return false;
    }


    // Creates a unique or where with the primary cols specified
    function handlePrimaryColumnWhere(&$class, $key)
    {
        if (isset($class->primary_cols) && is_array($class->primary_cols)) {

            foreach ($class->primary_cols as $col) {
                $class->db->or_where($col, $key);
            }

        } else {
            $class->db->where('id', $key);
        }
    }


    // this returns all columns from the table
    function getColumns($remove_columns = array())
    {
        return valueRemoveKeys($this->db->list_fields($this->table), $remove_columns);
    }



}









?>