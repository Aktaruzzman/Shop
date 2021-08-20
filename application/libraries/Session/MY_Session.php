<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MY_Session extends CI_Session
{

    public function __construct(array $params = array())
    {
        if ($this->ignore_sessions()) {
            return;
        }
        parent::__construct();
    }
    public function ignore_sessions()
    {
        $uri = str_replace("//", "/", $_SERVER['REQUEST_URI']);
        return strpos($uri, '/api_v1/') || strpos($uri, '/api_v2/') ? true : false;
    }
}
