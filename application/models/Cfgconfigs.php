<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cfgconfigs extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Geoareas');
        $this->load->model('Prodbases');
    }
    function exists($key)
    {
        $this->db->from('cfgconfigs');
        $this->db->where('cfgconfigs.key', $key);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }
    function get_all()
    {
        $this->db->from('cfgconfigs');
        $this->db->order_by("key", "asc");
        $result = $this->db->get()->result();
        $config = array();
        foreach ($result as $obj) {
            if ($obj->key === 'vendor_name' || $obj->key === 'vendor_address') {
                $config[$obj->key] = json_decode($obj->value);
            } else {
                $config[$obj->key] = $obj->value;
            }
        }
        return $config;
    }
    function get($key)
    {
        $query = $this->db->get_where('cfgconfigs', array('key' => $key), 1);
        if ($query->num_rows() == 1) {
            return $query->row()->value;
        }
        return "";
    }
    function save($key, $value)
    {
        $cfgconfigs_data = array(
            'key' => $key,
            'value' => $value
        );
        if (!$this->exists($key)) {
            return $this->db->insert('cfgconfigs', $cfgconfigs_data);
        }
        $this->db->where('key', $key);
        return $this->db->update('cfgconfigs', $cfgconfigs_data);
    }
    function batch_save($data)
    {
        $success = true;
        $this->db->trans_start();
        foreach ($data as $key => $value) {
            if (!$this->save($key, $value)) {
                $success = false;
                break;
            }
        }
        $this->db->trans_complete();
        return $success;
    }
    public function setup()
    {
        $results['areas'] = $this->Geoareas->formatted();
        $results['config'] = $this->get_all();
        $area = $this->Geoareas->formatted((int) $results['config']['store_area_id']);
        $results['config']['store_area'] = $area->formatted;
        $bases = $this->Prodbases->get_by(['status' => 'active'], false);
        foreach ($bases as $key => $v) {
            $results['config'][$v->value . '_ip'] = $v->ip;
        }
        return $results;
    }
}
