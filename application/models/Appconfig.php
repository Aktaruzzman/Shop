<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appconfig extends CI_Model
{
    public function exists($key)
    {
        $this->db->from('cfgconfigs');
        $this->db->where('cfgconfigs.key', $key);
        $query = $this->db->get();
        return ($query->num_rows() == 1);
    }

    public function get_all()
    {
        $this->db->from('cfgconfigs');
        $this->db->order_by("key", "asc");
        return $this->db->get();
    }

    public function get($key)
    {
        $query = $this->db->get_where('cfgconfigs', array('key' => $key), 1);
        if ($query->num_rows() == 1) {
            return $query->row()->value;
        }
        return "";
    }

    public function save($key, $value)
    {
        $config_data = array(
            'key' => $key,
            'value' => $value
        );
        if (!$this->exists($key)) {
            return $this->db->insert('cfgconfigs', $config_data);
        }

        $this->db->where('key', $key);
        return $this->db->update('cfgconfigs', $config_data);
    }

    public function batch_save($data)
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

    public function delete($key)
    {
        return $this->db->delete('cfgconfigs', array('key' => $key));
    }

    public function delete_all()
    {
        return $this->db->empty_table('cfgconfigs');
    }
}
