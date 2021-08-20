<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Oops extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Pages');
    }
    public function index()
    {
        $page = $this->Pages->get(['slug' => '404'], true, true);
        $data = !empty($page) ? $this->prepare($page) : [];
        $data['breadcrumb'][site_url()] = $this->page_title;
        $this->render('oops', $data);
    }

    private function prepare($page = null)
    {
        $page = (array) $page;
        $lang = lang_option();
        $this->body_class = ["page-home"];
        $data['lang'] = $lang;
        $data['default'] = $this->Pages->get(['default' => true], true)->slug;
        $data['slug'] = $page['slug'];
        $data['isLoggedin'] = $this->isLoggedin;
        $data['page_title'] = $this->page_title = $page['title_' . $lang];
        $data['page_subtitle'] = $this->page_subtitle = $page['subtitle_' . $lang];
        $data['page_meta_keywords'] = $this->page_meta_keywords = $page['meta_keys_' . $lang];
        $data['page_meta_description'] = $this->page_meta_description = $page['meta_desc_' . $lang];
        $data['page'] = ['title' => $this->page_title, 'subtitle' => $this->page_subtitle, 'description' => $page['description_' . $lang], 'photo' => $page['photo'], 'thumb' => $page['thumb']];
        $data['header_nav'] = $page['slug'];
        $data['sidebar_nav'] = $page['slug'];
        return $data;
    }
}
