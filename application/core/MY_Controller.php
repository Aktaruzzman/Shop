<?php

class MY_Controller extends MX_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function uploadimage($config, $image)
    {
        $this->load->helper('directory');
        $this->load->library('upload', $config);
        $this->upload->do_upload($image);
        return strlen($this->upload->display_errors()) == 0 || !strcmp($this->upload->display_errors(), '<p>' . $this->lang->line('upload_no_file_selected') . '</p>');
    }

    public function preparePostedData()
    {
        $items = array();
        foreach ($this->input->post() as $key => $value) {
            $items[] = $key;
        }
        return elements($items, $this->input->post());
    }
    public function post_data()
    {
        $items = array();
        foreach ($this->input->post() as $key => $value) {
            $items[] = $key;
        }
        return elements($items, $this->input->post());
    }

    public function randomcode($length = 5, $type = 'mixed')
    {
        $source = "01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        if ('number' === $type) {
            $source = '01234567890';
        }
        return substr(str_shuffle(str_repeat($source, ceil($length / strlen($source)))), 1, $length);
    }

    public function send_email($data)
    {
        $this->load->library('email');
        $config['mailtype'] = 'html';
        $config['charset']  = 'utf-8';
        $config['priority'] = 1;
        $config['protocol'] = 'sendmail';
        $this->email->initialize($config);
        $this->email->from($data['from'], $data['from_title']);
        $this->email->to($data['to']);
        $this->email->subject($data['subject']);
        $this->email->message($data['message']);
        if (isset($data['message_alt'])) {
            $this->email->set_alt_message($data['message_alt']);
        }
        $this->email->reply_to($data['from'], $data['from_title']);
        return $this->email->send();
    }

    public function sms($phone, $message)
    {
        $data = ["api_key" => "C20061625e936d00a4c2c0.96874058", "type" => "unicode", "contacts" => $phone, "senderid" => "8809601000100", "msg" => $message];
        $ch   = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://esms.mimsms.com/smsapi");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
