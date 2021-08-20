<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Salereports extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'sales';
        $this->_primary_key = 'id';
        $this->_order_by = 'invoice';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        $this->load->model('Users');
    }

    public function get_sales($from = null, $to = null, $search = null, $fields = null, $limit = null, $offset = null, $hub_id = null, $deleted = false)
    {
        $header = getallheaders();
        $user = $this->Users->get($header['user_id'], true);
        $this->db->where('stage >=', 5);
        if (empty($search)) $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        if (config_item('store_type') === "multiple") {
            if (!empty($hub_id)) {
                if ($user->role_value <= 3) $this->db->where('hub_id', $hub_id);
                else $this->db->where('updated_by', $user->id);
            } else {
                if ($user->role_value <= 3) $this->db->where('hub_id', $user->hub_id);
                else $this->db->where('updated_by', $user->id);
            }
        } else {
            if ($user->role_value <= 3) $this->db->where('hub_id', $user->hub_id);
            else $this->db->where('updated_by', $user->id);
        }
        if (!empty($search)) {
            $criteria = [];
            foreach ($fields as $col) $criteria[$col] = $search;
            $this->db->where($criteria);
        }
        if ($limit) $this->db->limit($limit, $offset);
        if (!$deleted && $this->_soft_deleted && !empty($this->_deleted_at)) $this->db->where($this->_table_name . '.' . $this->_deleted_at, false);
        $this->db->order_by($this->_order_by, $this->_order_type);
        return $this->db->get($this->_table_name)->result();
    }
    public function due_sale_cash_in($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(amount) as total', false);
        $this->db->from('customerduepayment');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $this->db->where('source', 'till');
        $this->db->where('payment', 'cash');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }
    public function purchase_return_cash_in($start, $end, $header)
    {
        return 0;
    }
    public function other_income_cash_in($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(amount) as total', false);
        $this->db->from('incomes');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $this->db->where('source', 'till');
        $this->db->where('payment', 'cash');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $this->db->where('deleted_at', 0);
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }

    public function voucher_sale_cash_in($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(price) as total', false);
        $this->db->from('cfgvouchers');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $this->db->where('source', 'till');
        $this->db->where('payment', 'cash');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }

    public function purchase_cash_out($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(cash_from_till) as total', false);
        $this->db->from('purchases');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }

    public function due_purchase_cash_out($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(amount) as total', false);
        $this->db->from('supplierduepayment');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $this->db->where('source', 'till');
        $this->db->where('payment', 'cash');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }

    public function sale_return_cash_out($start, $end, $hub_id)
    {
        return 0;
    }

    public function other_expense_cash_out($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(amount) as total', false);
        $this->db->from('expenses');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $this->db->where('source', 'till');
        $this->db->where('payment', 'cash');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $this->db->where('deleted_at', 0);
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }
    public function employee_salary_cash_out($start, $end, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(salary) as total', false);
        $this->db->from('empsalaries');
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $this->db->where('source', 'till');
        $this->db->where('payment', 'cash');
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }
    public function initial_till_cash($from, $to, $hub_id)
    {
        $amount = 0;
        $this->db->select('SUM(balance) as total', false);
        $this->db->from($this->Tillaccounts->getTable());
        if (config_item('store_type') === "multiple" && !empty($hub_id)) {
            $this->db->where('hub_id', $hub_id);
        }
        $this->db->where('closed_on BETWEEN "' . $from . '" and "' . $to . '"');
        $row = $this->db->get()->row();
        if (!empty($row) && !empty($row->total)) {
            $amount = $row->total;
        }
        return $amount;
    }
}
