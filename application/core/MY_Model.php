<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property object $db
 */
class MY_Model extends \CI_Model
{
    public $table = '';
    public $primary_cols = [];
    protected static $soft_delete_columns = array();
    protected $cache_store = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();

    }

    protected function getCacheStore()
    {
        if ($this->cache_store !== null) {
            return $this->cache_store;
        }

        $this->load->driver('cache', array(
            'adapter' => 'file',
            'backup'  => 'dummy',
        ));

        $this->cache_store = $this->cache;
        return $this->cache_store;
    }

    protected function rememberCache($key, $ttl, callable $resolver)
    {
        $cache = $this->getCacheStore();
        $cached = $cache->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $value = $resolver();
        $cache->save($key, $value, $ttl);
        return $value;
    }

    protected function forgetCache($key)
    {
        return $this->getCacheStore()->delete($key);
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

    protected function supportsSoftDeletes($table = null)
    {
        $table = $table ?: $this->table;
        if (!$table) {
            return false;
        }

        if (!array_key_exists($table, self::$soft_delete_columns)) {
            self::$soft_delete_columns[$table] = in_array('deleted_at', $this->db->list_fields($table), true);
        }

        return self::$soft_delete_columns[$table];
    }

    protected function applyNotDeleted($table = null)
    {
        $table = $table ?: $this->table;

        if ($this->supportsSoftDeletes($table)) {
            ci_where_not_deleted($this->db, $table);
        }

        return $this;
    }

    public function softDelete($key = false)
    {
        if (!$key || !$this->supportsSoftDeletes()) {
            return false;
        }

        $this->handlePrimaryColumnWhere($this, $key);
        $this->applyNotDeleted();
        $this->db->set('deleted_at', 'NOW()', false);
        $this->db->update($this->table);

        return ($this->db->affected_rows() > 0);
    }

    public function generate_hash($length = 400)
    {
        //return base64_encode($this->encryption->encrypt($string));
        if (is_string($length)) {
            $length = 400;
        }
        $ref = generate_tracking_id($length);

        while (isValue($this->table, 'hash', $ref)) {
            $ref = generate_tracking_id($length);
        }

        return $ref;
    }


	function dataById($id) {
        $this->db->where(array('id' => $id));
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }

    function dataByHash($hash) {
        $this->db->where(array('hash' => $hash));
        $this->applyNotDeleted();
        return $this->db->get($this->table)->row();
    }



}











?>
