<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Discountmulti extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Discountmultis');
            $this->load->model('Proditems');
            $this->load->model('Prodoptions');
        }
    }
    public function index_get($id = NULL, $config = NULL, $edit = NULL, $deleted = NULL, $limit = NULL, $offset = NULL)
    {
        $results = [];
        if (!empty($id) && empty($edit)) {
            $entity = $this->Discountmultis->get($id, TRUE, $deleted);
            $results['entity'] = $this->Discountmultis->build_entity($entity);
        } else {
            $results['list'] = $this->Discountmultis->get_by(NULL, FALSE, $deleted, $limit, $offset);
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index] = $this->Discountmultis->build_entity($obj);
                if (intval($id) === $obj->id) $results['entity'] =  $results['list'][$index];
            }
            $results['total'] = $this->Discountmultis->count_rows(NULL, $deleted);
        }
        if (!empty($config)) {
            $configs = $this->Discountmultis->build_config(!empty($results['entity']) ? $results['entity'] : NULL);
            $results['services'] = $configs['services'];
            $results['items'] = $configs['items'];
        }
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Discountmultis->validate()) {
            $this->db->trans_start();
            $entity = $this->Discountmultis->arrange_entity($this->Discountmultis->entity($this->post()));
            if (!empty($entity['id'])) {
                $results['status'] = $this->Discountmultis->save($entity, $entity['id']);
                $option_menu = explode('_', $entity['name_id']);
                if (!empty($option_menu[0])) {
                    $this->Prodoptions->save(['discount_by' => 'bogo'], $option_menu[0]);
                    add_sync($this->Prodoptions->getTable(), 'update', $option_menu[0]);
                } else {
                    $this->Proditems->save(['discount_by' => 'bogo'], $option_menu[1]);
                    add_sync($this->Proditems->getTable(), 'update', $option_menu[1]);
                }
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'update', $entity['id']);
                    add_activity('bogo discount', 'A bogo discount updated');
                }
            } else {
                $results['status'] = $id = $this->Discountmultis->save($entity);
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'create', $id);
                    add_activity('bogo discount', 'A bogo discount created');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('saved_failed_msg'), lang('bogo') . ' ' . lang('discount'));
            return $this->response($results);
        }
        return  $this->response(['status' => FALSE, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Discountmultis->entity($this->put());
            $results['status'] = $this->Discountmultis->save($entity, $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'update', $id);
                add_activity('bogo discount', 'A bogo discount updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('update_failed_msg'), lang('bogo') . ' ' . lang('discount'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function index_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Discountmultis->save($this->patch(), $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'update', $id);
                add_activity('bogo discount', 'A bogo discount updated');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('update_failed_msg'), lang('bogo') . ' ' . lang('discount'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function default_patch($id = NULL)
    {
        if (!empty($id) && !empty($this->Discountmultis->defaultBy())) {
            $this->db->trans_start();
            $obj = (array) $this->Discountmultis->get($id, TRUE);
            $results['status'] = TRUE;
            if (!empty($obj) && !$obj[$this->Discountmultis->defaultBy()]) {
                $this->Discountmultis->unset_default(NULL);
                $results['status'] = $this->Discountmultis->set_default(['id' => $obj['id']]);
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'update', $id);
                    add_activity('bogo discount', 'A bogo discount updated');
                }
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('update_failed_msg'), lang('bogo') . ' ' . lang('discount'));
            return $this->response($results);
        }
        return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = NULL)
    {
        if (!empty($id)) {
            $obj = $this->Discountmultis->get($id, TRUE);
            if (!empty($obj) && empty($obj->default)) {
                $this->db->trans_start();
                if (!empty($obj->option_id)) {
                    $this->Prodoptions->save(['discount_by' => 'nill'], $obj->option_id);
                    add_sync($this->Prodoptions->getTable(), 'update', $obj->option_id);
                } else {
                    $this->Proditems->save(['discount_by' => 'nill'], $obj->item_id);
                    add_sync($this->Proditems->getTable(), 'update', $obj->item_id);
                }
                $results['status'] = $this->Discountmultis->delete($id);
                if (!empty($results['status'])) {
                    add_sync($this->Discountmultis->getTable(), 'delete', $id);
                    add_activity('bogo discount', 'A bogo discount deleted into bin');
                }
                $this->db->trans_complete();
                $results['status'] = $this->db->trans_status() &&  $results['status'];
                $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('delete_failed_msg'), lang('bogo') . ' ' . lang('discount'));
                return $this->response($results);
            } else return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
        }
    }
    public function undo_patch($id = NULL)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Discountmultis->save([$this->Discountmultis->deletedAt() => FALSE], $id);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'update', $id);
                add_activity('bogo discount', 'A bogo discount restored from bin');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('update_failed_msg'), lang('bogo') . ' ' . lang('discount'));
            return $this->response($results);
        } else  return $this->response(['status' => FALSE, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = NULL)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $obj = $this->Discountmultis->get($id, TRUE);
            if (!empty($obj->option_id)) {
                $this->Prodoptions->save(['discount_by' => 'nill'], $obj->option_id);
                add_sync($this->Prodoptions->getTable(), 'update', $obj->option_id);
            } else {
                $this->Proditems->save(['discount_by' => 'nill'], $obj->item_id);
                add_sync($this->Proditems->getTable(), 'update', $obj->item_id);
            }
            $results['status'] = $this->Discountmultis->delete($id, TRUE);
            if (!empty($results['status'])) {
                add_sync($this->Discountmultis->getTable(), 'trash', $id);
                add_activity('bogo discount', 'A bogo discount deleted permanently');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() &&  $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('bogo') . ' ' . lang('discount')) : sprintf(lang('delete_failed_msg'), lang('bogo') . ' ' . lang('discount'));
            return $this->response($results);
        } else {
            return $this->response(['status' => FALSE, 'message' => 'invalid request']);
        }
    }
}
