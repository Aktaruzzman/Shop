<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Template {
    private $_module = '';
    private $_controller = '';
    private $_method = '';
    private $_theme = NULL;
    private $_theme_path = NULL;
    private $_layout = FALSE;
    private $_layout_subdir = '';
    private $_title = '';
    private $_metadata = array();
    private $_partials = array();
    private $_breadcrumbs = array();
    private $_title_separator = ' | ';
    private $_parser_enabled = TRUE;
    private $_parser_body_enabled = TRUE;
    private $_theme_locations = array();
    private $_is_mobile = FALSE;
    private $cache_lifetime = 0;
    private $_ci;
    private $_data = array();

    function __construct($config = array()) {
        $this->_ci = & get_instance();

        if (!empty($config)) {
            $this->initialize($config);
        }

        log_message('debug', 'Template Class Initialized');
    }

    function initialize($config = array()) {
        foreach ($config as $key => $val) {
            if ($key == 'theme' AND $val != '') {
                $this->set_theme($val);
                continue;
            }
            $this->{'_' . $key} = $val;
        }
        if ($this->_theme_locations === array()) {
            $this->_theme_locations = array(
                APPPATH . 'themes/'
            );
        }
        if ($this->_theme) {
            $this->set_theme($this->_theme);
        }

        if ($this->_parser_enabled === TRUE) {
            $this->_ci->load->library('parser');
        }
        if (method_exists($this->_ci->router, 'fetch_module')) {
            $this->_module = $this->_ci->router->fetch_module();
        }
        $this->_controller = $this->_ci->router->fetch_class();
        $this->_method = $this->_ci->router->fetch_method();
        $this->_ci->load->library('user_agent');
        $this->_is_mobile = $this->_ci->agent->is_mobile();
    }

    public function __get($name) {
        return isset($this->_data[$name]) ? $this->_data[$name] : NULL;
    }

    public function __set($name, $value) {
        $this->_data[$name] = $value;
    }

    public function set($name, $value = NULL) {
        if (is_array($name) OR is_object($name)) {
            foreach ($name as $item => $value) {
                $this->_data[$item] = $value;
            }
        } else {
            $this->_data[$name] = $value;
        }

        return $this;
    }

    public function build($view, $data = array(), $return = FALSE) {
        is_array($data) OR $data = (array) $data;
        $this->_data = array_merge($this->_data, $data);
        unset($data);
        if (empty($this->_title)) {
            $this->_title = $this->_guess_title();
        }
        $template['title'] = $this->_title;
        $template['breadcrumbs'] = $this->_breadcrumbs;
        $template['metadata'] = implode("\n\t\t", $this->_metadata);
        $template['partials'] = array();
        $this->_data['template'] = & $template;

        foreach ($this->_partials as $name => $partial) {
            is_array($partial['data']) OR $partial['data'] = (array) $partial['data'];
            if (isset($partial['view'])) {
                $template['partials'][$name] = $this->_find_view($partial['view'], $partial['data']);
            } else {
                if ($this->_parser_enabled === TRUE) {
                    $partial['string'] = $this->_ci->parser->parse_string($partial['string'], $this->_data + $partial['data'], TRUE, TRUE);
                }

                $template['partials'][$name] = $partial['string'];
            }
        }
        $this->_body = $this->_find_view($view, array(), $this->_parser_body_enabled);
        if ($this->_layout) {
            $template['body'] = $this->_body;
            $this->_body = self::_load_view('layouts/' . $this->_layout, $this->_data, TRUE, self::_find_view_folder());
        }
        if (!$return) {
            $this->_ci->output->set_output($this->_body);
        }

        return $this->_body;
    }

    public function title() {
        if (func_num_args() >= 1) {
            $title_segments = func_get_args();
            $this->_title = implode($this->_title_separator, $title_segments);
        }
        return $this;
    }

    public function prepend_metadata($line) {
        array_unshift($this->_metadata, $line);
        return $this;
    }

    public function append_metadata($line) {
        $this->_metadata[] = $line;
        return $this;
    }

    public function set_metadata($name, $content, $type = 'meta') {
        $name = htmlspecialchars(strip_tags($name));
        $content = htmlspecialchars(strip_tags($content));
        if ($name == 'keywords' AND ! strpos($content, ',')) {
            $content = preg_replace('/[\s]+/', ', ', trim($content));
        }

        switch ($type) {
            case 'meta':
                $this->_metadata[$name] = '<meta name="' . $name . '" content="' . $content . '" />';
                break;

            case 'link':
                $this->_metadata[$content] = '<link rel="' . $name . '" href="' . $content . '" />';
                break;
        }

        return $this;
    }

    public function set_theme($theme = NULL) {
        $this->_theme = $theme;
        foreach ($this->_theme_locations as $location) {
            if ($this->_theme AND file_exists($location . $this->_theme)) {
                $this->_theme_path = rtrim($location . $this->_theme . '/');
                break;
            }
        }

        return $this;
    }

    public function get_theme() {
        return $this->_theme;
    }

    public function get_theme_path() {
        return $this->_theme_path;
    }

    public function set_layout($view, $_layout_subdir = '') {
        $this->_layout = $view;

        $_layout_subdir AND $this->_layout_subdir = $_layout_subdir;

        return $this;
    }

    public function set_partial($name, $view, $data = array()) {
        $this->_partials[$name] = array(
            'view' => $view,
            'data' => $data
        );
        return $this;
    }

    public function inject_partial($name, $string, $data = array()) {
        $this->_partials[$name] = array(
            'string' => $string,
            'data' => $data
        );
        return $this;
    }

    public function set_breadcrumb($name, $uri = '') {
        $this->_breadcrumbs[] = array(
            'name' => $name,
            'uri' => $uri
        );
        return $this;
    }

    public function set_cache($minutes = 0) {
        $this->cache_lifetime = $minutes;
        return $this;
    }

    public function enable_parser($bool) {
        $this->_parser_enabled = $bool;
        return $this;
    }

    public function enable_parser_body($bool) {
        $this->_parser_body_enabled = $bool;
        return $this;
    }

    public function theme_locations() {
        return $this->_theme_locations;
    }

    public function add_theme_location($location) {
        $this->_theme_locations[] = $location;
    }

    public function theme_exists($theme = NULL) {
        $theme OR $theme = $this->_theme;

        foreach ($this->_theme_locations as $location) {
            if (is_dir($location . $theme)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    public function get_layouts() {
        $layouts = array();

        foreach (glob(self::_find_view_folder() . 'layouts/*.*') as $layout) {
            $layouts[] = pathinfo($layout, PATHINFO_BASENAME);
        }

        return $layouts;
    }

    public function get_theme_layouts($theme = NULL) {
        $theme OR $theme = $this->_theme;

        $layouts = array();

        foreach ($this->_theme_locations as $location) {
            if (is_dir($location . $theme . '/views/web/layouts/')) {
                foreach (glob($location . $theme . '/views/web/layouts/*.*') as $layout) {
                    $layouts[] = pathinfo($layout, PATHINFO_BASENAME);
                }
                break;
            }
            if (is_dir($location . $theme . '/views/layouts/')) {
                foreach (glob($location . $theme . '/views/layouts/*.*') as $layout) {
                    $layouts[] = pathinfo($layout, PATHINFO_BASENAME);
                }
                break;
            }
        }

        return $layouts;
    }

    public function layout_exists($layout) {

        if (!empty($this->_theme) AND in_array($layout, self::get_theme_layouts())) {
            return TRUE;
        }
        return file_exists(self::_find_view_folder() . 'layouts/' . $layout . self::_ext($layout));
    }

    public function load_view($view, $data = array()) {
        return $this->_find_view($view, (array) $data);
    }

    private function _find_view_folder() {
        if ($this->_ci->load->get_var('template_views')) {
            return $this->_ci->load->get_var('template_views');
        }

        $view_folder = APPPATH . 'views/';
        if (!empty($this->_theme)) {
            $view_folder = $this->_theme_path . 'views/';
        }
        if ($this->_is_mobile === TRUE AND is_dir($view_folder . 'mobile/')) {
            $view_folder .= 'mobile/';
        } else if (is_dir($view_folder . 'web/')) {
            $view_folder .= 'web/';
        }
        if ($this->_layout_subdir) {
            $view_folder .= $this->_layout_subdir . '/';
        }
        $this->_ci->load->vars('template_views', $view_folder);

        return $view_folder;
    }

    private function _find_view($view, array $data, $parse_view = TRUE) {
        if (!empty($this->_theme)) {
            foreach ($this->_theme_locations as $location) {
                $theme_views = array(
                    $this->_theme . '/views/modules/' . $this->_module . '/' . $view,
                    $this->_theme . '/views/' . $view
                );

                foreach ($theme_views as $theme_view) {
                    if (file_exists($location . $theme_view . self::_ext($theme_view))) {
                        return self::_load_view($theme_view, $this->_data + $data, $parse_view, $location);
                    }
                }
            }
        }
        return self::_load_view($view, $this->_data + $data, $parse_view);
    }

    private function _load_view($view, array $data, $parse_view = TRUE, $override_view_path = NULL) {
        if ($override_view_path !== NULL) {
            if ($this->_parser_enabled === TRUE AND $parse_view === TRUE) {
                $content = $this->_ci->parser->parse_string($this->_ci->load->file($override_view_path . $view . self::_ext($view), TRUE), $data, TRUE);
            } else {
                $this->_ci->load->vars($data);

                $content = $this->_ci->load->file($override_view_path . $view . self::_ext($view), TRUE);
            }
        } else {

            $content = ($this->_parser_enabled === TRUE AND $parse_view === TRUE) ? $this->_ci->parser->parse($view, $data, TRUE) : $this->_ci->load->view($view, $data, TRUE);
        }

        return $content;
    }

    private function _guess_title() {
        $this->_ci->load->helper('inflector');
        $title_parts = array();
        if ($this->_method != 'index') {
            $title_parts[] = $this->_method;
        }
        if (!in_array($this->_controller, $title_parts)) {
            $title_parts[] = $this->_controller;
        }
        if (!empty($this->_module) AND ! in_array($this->_module, $title_parts)) {
            $title_parts[] = $this->_module;
        }
        $title = humanize(implode($this->_title_separator, $title_parts));

        return $title;
    }

    private function _ext($file) {
        return pathinfo($file, PATHINFO_EXTENSION) ? '' : '.php';
    }

}
