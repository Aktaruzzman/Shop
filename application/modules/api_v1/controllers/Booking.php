<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Booking extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Cfgservices');
            $this->load->model('Tablebookingoffs');
        }
    }
    public function index_get()
    {
        $results['date_slots'] = date_slot((int) config_item('booking_offest_day'), (int) config_item('booking_max_day'));
        $results['selected_date'] = array_values($results['date_slots'])[0];
        $opening = $this->Openings->get(['day' => date('D')], true, true);
        $from_now = count($results['date_slots']) > 1 ? false : true;
        $results['time_slots'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . $opening->start)), date('h:i A', strtotime(date('Y-m-d ') . $opening->end)), 0, (int) config_item('slot_interval_time'), $from_now);
        $results['selected_time'] = array_values($results['time_slots'])[0];
        return $this->response($results);
    }
}
