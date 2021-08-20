<?php
class MY_Model extends CI_Model
{
    /*Common Model Affairs Start*/
    protected $_table_name = '';
    protected $_primary_key = 'id';
    protected $_primary_filter = 'intval';
    protected $_order_by = 'id';
    protected $_order_type = 'ASC';
    protected $_default = '';
    protected $_created_at = '';
    protected $_updated_at = '';
    protected $_deleted_at = '';
    protected $_soft_deleted = true;
    protected $_rules = array();
    protected $_timestamps = false;
    protected $_allowable_fileds = array();
    protected $_group_by = array();
    public function __construct()
    {
        parent::__construct();
    }
    public function getTable()
    {
        return $this->_table_name;
    }
    public function hasField($field_name)
    {
        return $this->db->field_exists($field_name, $this->_table_name);
    }
    public function primaryKey()
    {
        return $this->_primary_key;
    }
    public function orderBy()
    {
        return $this->_order_by;
    }
    public function defaultBy()
    {
        return $this->_default;
    }
    public function createdAt()
    {
        return $this->_created_at;
    }
    public function updatedAt()
    {
        return $this->_updated_at;
    }
    public function deletedAt()
    {
        return $this->_deleted_at;
    }
    public function softDeleted()
    {
        return $this->_soft_deleted;
    }
    public function listFields()
    {
        return $this->db->list_fields($this->_table_name);
    }
    public function entity($inputs)
    {
        $columns = $this->listFields();
        $attributes = array();
        foreach ($inputs as $key => $value) {
            if (in_array($key, $columns)) $attributes[] = $key;
        }
        return elements($attributes, $inputs);
    }
    public function post_entity($inputs)
    {
        $attributes = array();
        foreach ($inputs as $key => $value) {
            $attributes[] = $key;
        }
        return elements($attributes, $inputs);
    }
    public function get_code($type = "basic", $length = null, $database = true)
    {
        return $this->_generate_code($type, $length, $database);
    }
    private function _generate_code($type, $length = null, $database = null)
    {
        $newkey = null;
        if ($database) {
            do  $newkey = $length ? random_string($type, $length) : random_string($type);
            while ($this->_code_exists($newkey));
            return $newkey;
        } else return $length ? random_string($type, $length) : random_string($type);
    }
    private function _code_exists($key)
    {
        return $this->db->where('code', $key)->count_all_results($this->_table_name) > 0;
    }
    public function get($id = null, $single = false, $deleted = false, $fields = null, $sortBy = null, $sortType = "ASC")
    {
        if (!empty($fields)) $this->_allowable_fileds = $fields;
        if ($id != null) {
            if (is_array($id)) {
                $this->db->where($id);
                if ($single) $method = 'row';
                else $method = 'result';
            } else {
                $filter = $this->_primary_filter;
                $id = $filter($id);
                $this->db->where($this->_primary_key, $id);
                $method = 'row';
            }
        } else if (empty($id) && $single == true)
            $method = 'row';
        else {
            if (!empty($this->_rules))
                $this->db->where($this->_rules);
            $method = 'result';
        }
        if (!empty($this->_allowable_fileds))
            $this->db->select($this->_allowable_fileds);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at))
            $this->db->where($this->_deleted_at, false);
        if (!empty($sortBy))
            $this->db->order_by($sortBy, $sortType);
        else {
            if (!empty($this->_order_by))
                $this->db->order_by($this->_order_by, $this->_order_type);
            else
                $this->db->order_by($this->_primary_key);
        }
        if (!empty($this->_group_by))
            $this->db->group_by($this->_group_by);

        return $this->db->get($this->_table_name)->$method();
    }

    public function get_by($where = null, $single = false, $deleted = false, $limit = null, $offset = 0, $search = null, $fields = [], $sortBy = null, $sortType = "ASC")
    {
        if (!empty($search)) {
            $criteria = [];
            $fields = !empty($fields) ? $fields : $this->listFields();
            foreach ($fields as $col) $criteria[$col] = $search;
            $this->db->or_like($criteria);
        }
        if ($where) $this->db->where($where);
        if ($limit)  $this->db->limit($limit, $offset);
        if (empty($search) && !empty($fields)) $this->_allowable_fileds = $fields;
        return $this->get(null, $single, $deleted, null, $sortBy, $sortType);
    }
    public function save($data, $id = null)
    {
        if ($id === null || empty($id)) {
            if ($this->_timestamps && !empty($this->_created_at)) $data[$this->_created_at] = date('Y-m-d H:i:s');
            if ($this->_timestamps && !empty($this->_updated_at)) $data[$this->_created_at] = date('Y-m-d H:i:s');
            !isset($data[$this->_primary_key]) || $data[$this->_primary_key] == null;
            $this->db->set($data);
            $status = $this->db->insert($this->_table_name);
            return $status ? $this->db->insert_id() : false;
        } else {
            if ($this->_timestamps && !empty($this->_updated_at)) $data[$this->_updated_at] = date('Y-m-d H:i:s');
            $filter = $this->_primary_filter;
            $id = $filter($id);
            $this->db->set($data);
            $this->db->where($this->_primary_key, $id);
            return $this->db->update($this->_table_name);
        }
    }
    public function save_by($data, $where)
    {
        $this->db->set($data);
        $this->db->where($where);
        return $this->db->update($this->_table_name);
    }
    public function sort($id, $old_position, $new_position, $base_field = null, $base_value = null)
    {
        if ($new_position > $old_position) {
            if ($base_field && $base_value) $this->db->where($base_field, $base_value);
            $this->db->where($this->_order_by . ' BETWEEN "' . $old_position . '" and "' . $new_position . '"');
            $units = $this->db->get($this->_table_name)->result();
            foreach ($units as $unit) {
                $now_order = $unit->order - 1;
                $this->save(array($this->_order_by => $now_order), $unit->id);
            }
            $this->save(array($this->_order_by => $new_position), $id);
        } else {
            if ($base_field && $base_value) $this->db->where($base_field, $base_value);
            $this->db->where($this->_order_by . ' BETWEEN "' . $new_position . '" and "' . $old_position . '"');
            $units = $this->db->get($this->_table_name)->result();
            foreach ($units as $unit) {
                $now_order = $unit->order + 1;
                $this->save(array($this->_order_by => $now_order), $unit->id);
            }
            $this->save(array($this->_order_by => $new_position), $id);
        }
    }
    public function delete($id, $trash = false)
    {
        $filter = $this->_primary_filter;
        $id = $filter($id);
        if (!$id) return false;
        return $this->delete_by(array($this->_primary_key => $id), $trash);
    }
    public function delete_by($where, $trash = false)
    {
        if ($trash)  return $this->clear_trash($where);
        if ($this->_deleted_at && $this->_soft_deleted) return $this->soft_delete($where);
        return $this->clear_trash($where);
    }
    public function soft_delete($where)
    {
        $this->db->set(array($this->_deleted_at => true));
        $this->db->where($where);
        return $this->db->update($this->_table_name);
    }
    public function clear_trash($where)
    {
        $this->db->where($where);
        return $this->db->delete($this->_table_name);
    }
    public function set_default($where)
    {
        if (!empty($this->_default)) {
            $this->db->set(array($this->_default => true));
            $this->db->where($where);
            return $this->db->update($this->_table_name);
        }
    }
    public function unset_default($where = null)
    {
        if (!empty($this->_default)) {
            $this->db->set(array($this->_default => false));
            if ($where) $this->db->where($where);
            return $this->db->update($this->_table_name);
        }
    }
    public function count_rows($condition = null, $deleted = false)
    {
        if ($condition)  $this->db->where($condition);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at))  $this->db->where($this->_deleted_at, false);
        $this->db->from($this->_table_name);
        return $this->db->count_all_results();
    }
    public function get_max($column, $condition = null, $return = null)
    {
        $this->db->select_max($column, 'max');
        if ($condition) $this->db->where($condition);
        $row = $this->db->get($this->_table_name)->row();
        if (!empty($return)) return $row;
        else return !empty($row) ? $row->max : 0;
    }
    public function get_min($column, $condition = null, $return = null)
    {
        $this->db->select_min($column, 'min');
        if ($condition) $this->db->where($condition);
        $row = $this->db->get($this->_table_name)->row();
        if (!empty($return)) return $row;
        else return !empty($row) ? $row->min : 0;
    }
    public function slug($string, $database = true)
    {
        $slug = url_title($string, '-', true);
        if (!empty($database) && $this->hasField('slug')) {
            $hasMore = $this->count_rows(['slug' => $slug], false, true);
            if ($hasMore) $slug .= '-' . (string) ($hasMore + 1);
        }
        return $slug;
    }
    public function check_unique($field, $value, $where = null)
    {
        if ($where) $this->db->where($where);
        $this->db->where($field, $value);
        $this->db->from($this->_table_name);
        return $this->db->count_all_results() > 0;
    }
    public function check_json_name($criteria = array())
    {
        if (!empty($criteria)) {
            $this->db->or_like($criteria);
            $this->db->from($this->_table_name);
            return $this->db->count_all_results() > 0;
        }
        return true;
    }
    public function invoice($hub, $user, $id, $id_length = 7)
    {
        $hub_str = str_pad($hub, 2, "0", STR_PAD_LEFT);
        $user_str = str_pad($user, 3, "0", STR_PAD_LEFT);
        $id_str = str_pad($id, $id_length, "0", STR_PAD_LEFT);
        return $hub_str . $user_str . $id_str;
    }
    public function decode($obj, $is_array = false)
    {
        return !empty($obj) ? json_decode($obj, $is_array) : null;
    }
    public function encode($array = [])
    {
        return !empty($array) ? json_encode($array, JSON_UNESCAPED_UNICODE) : null;
    }
    public function serialize($array)
    {
        return !empty($array) ? serialize($array) : null;
    }

    public function unserialize($str)
    {
        $str_result = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($match) {
            return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
        }, $str);
        return !empty($str_result) ? unserialize($str_result) : [];
    }
    /**Common Model Affairs End*/
    public function isLoggedin()
    {
        return $this->session->userdata('customerId') != false;
    }
    public function loggedout()
    {
        if (session_destroy())  return true;
    }
    public function is_admin_loggedin()
    {
        return $this->session->userdata('user_id') != false;
    }
    public function admin_loggedout()
    {
        return $this->session->unset_userdata('user_id');
    }
}
