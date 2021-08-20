<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Pos extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Sales');
            $this->load->model('Tableplans');
        }
    }
    public function index_get()
    {
    }
    public function index_post()
    {
    }
    public function index_put()
    {
    }
    public function index_patch()
    {
    }
    public function index_delete()
    {
    }
}
