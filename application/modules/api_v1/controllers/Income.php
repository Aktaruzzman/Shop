<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Income extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Incomes');
        }
    }

    public function index_get($id = null, $sector = null, $config = null, $hub_id = null, $deleted = 0, $from = null, $to = null)
    {
        if ($config) $results = $this->Incomes->build_config($hub_id);
        $results['list'] = $this->Incomes->get_incomes($sector, $from, $to, $deleted, $hub_id);
        $results['cash'] = 0;
        $results['other'] = 0;
        $results['cash_in_till'] = 0;
        foreach ($results['list'] as $index => $obj) {
            $results['list'][$index]->sector_name = $this->Incomes->decode($obj->sector_name);
            if ($results['list'][$index]->payment === "Cash") {
                $results['cash'] += $results['list'][$index]->amount;
                $results['list'][$index]->source == "till" ? $results['cash_in_till'] += $results['list'][$index]->amount : true;
            } else {
                $results['other'] += $results['list'][$index]->amount;
            }
            if (intval($id) === intval($obj->id)) {
                $results['entity'] = $results['list'][$index];
            }
        }
        $results['total'] = $results['cash'] + $results['other'];
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Incomes->validate()) {
            $this->db->trans_start();
            $entity = $this->Incomes->arrange_entity($this->Incomes->entity($this->post()));
            if (!empty($entity['id'])) {
                $this->Incomes->save($entity, $entity['id']);
                add_activity('income', 'An income updated');
            } else {
                $this->Incomes->save($entity);
                add_activity('income', 'A new income added');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('income') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('income') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Incomes->entity($this->put());
            $this->Incomes->save($entity, $id);
            add_activity('income', 'An income updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('income') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('income') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Incomes->save($this->patch(), $id);
            add_activity('income', 'An income updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('income') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('income') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Incomes->delete($id);
            add_activity('income', 'An income deleted into bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('income') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('income') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Incomes->save([$this->Incomes->deletedAt() => false], $id);
            add_activity('income', 'An income restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('income') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('income') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Incomes->delete($id, true);
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('income') . ' ' . lang('unit')) : sprintf(lang('delete_failed_msg'), lang('income') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
}
