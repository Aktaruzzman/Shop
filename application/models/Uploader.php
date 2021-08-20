<?php

class Uploader extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->library('image_lib');
        $this->load->library('upload');
    }
    public function upload_image($config, $image)
    {
        $this->upload->initialize($config);
        $this->upload->do_upload($image);
        return strlen($this->upload->display_errors()) == 0 || !strcmp($this->upload->display_errors(), '<p>' . $this->lang->line('upload_no_file_selected') . '</p>');
    }

    public function resize_image($source, $width, $ratio = true, $height = false)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $source;
        $config['maintain_ratio'] = TRUE;
        $config['width']         = $width;
        if (!$ratio && $height) {
            $config['height'] = $height;
        }
        $this->image_lib->initialize($config);
        $this->image_lib->resize();
        $this->image_lib->clear();
    }
    public function create_thumbnail($source, $desitination, $width = 75, $height = 50)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $source;
        $config['new_image'] = $desitination;
        $config['create_thumb'] = TRUE;
        $config['thumb_marker'] = '';
        $config['maintain_ratio'] = FALSE;
        $config['width']         = $width;
        $config['height']         = $height;
        $this->image_lib->initialize($config);
        $this->image_lib->resize();
        $this->image_lib->clear();
    }
    
    public function unlink_image($path, $image)
    {
        checkfile($path,  $image) ? @unlink(realpath($path . $image)) : true;
    }
}
