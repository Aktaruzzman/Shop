<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Tillaccount extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Tillaccounts');
        }
    }
    public function index_get($id = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Tillaccounts->get($id, TRUE, $deleted);
        } else {
            $results['list'] = $this->Tillaccounts->get_by(NULL, FALSE, $deleted, $limit, $offset);
            $results['total'] = $this->Tillaccounts->count_rows(NULL, $deleted);
            $results['entity'] = !empty($id) ? $this->Tillaccounts->get($id, TRUE, $deleted) : NULL;
        }
        return $this->response($results);
    }
    public function index_post()
    {
        if ($this->Tillaccounts->validate()) {
            $this->db->trans_start();
            $entity = $this->Tillaccounts->entity($this->post());
            $entity['hub_id'] = getallheaders()['hub_id'];
            $entity['closed_on'] = closed_on();
            $entity['created_by'] = getallheaders()['user_id'];
            $results['status'] = $id = $this->Tillaccounts->save($entity);
            if (!empty($results['status'])) {
                add_sync($this->Tillaccounts->getTable(), 'create', $id);
                add_activity('Till balance', 'A day\'s till balance updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
}
