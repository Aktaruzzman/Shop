<?php
if (!function_exists('lang_option')) {
    function lang_option()
    {
        $CI = &get_instance();
        $CI->load->model('Cookie_Model');
        return $CI->Cookie_Model->get_lang();
    }
}
if (!function_exists('currency')) {
    function currency($amount)
    {
        return lang_option() === "bn" ? to_bengali(number_format($amount, 2)) . config_item('currency_bn') : config_item('currency_en') . ' ' . number_format($amount, 2);
    }
}
if (!function_exists('amount')) {
    function amount($amount)
    {
        return lang_option() === "bn" ? to_bengali(number_format($amount, 2)) : number_format($amount, 2);
    }
}
if (!function_exists('number')) {
    function number($number)
    {
        return lang_option() === "bn" ? to_bengali($number) : $number;
    }
}
if (!function_exists('rounding')) {
    function rounding($val)
    {
        return round($val, config_item('precesion'), PHP_ROUND_HALF_UP);
    }
}
if (!function_exists('to_bengali')) {

    function to_bengali($bn_number, $lang = null)
    {
        $lang = $lang ? $lang : lang_option();
        if ($lang === "bn") {
            $bn_digits = array("১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০");
            $en_digits = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "০");
            $output = str_replace($en_digits, $bn_digits, $bn_number);
            return $output;
        } else return $bn_number;
    }
}
if (!function_exists('is_english')) {
    function is_english($str)
    {
        return strlen($str) != strlen(utf8_decode($str)) ? false : true;
    }
}
if (!function_exists('verify_date')) {
    function verify_date($format = "m/d/Y", $date)
    {
        return (DateTime::createFromFormat($format, $date) !== false);
    }
}
if (!function_exists('add_time')) {

    function add_time($pattern = '1H1M1S', $format = false)
    {
        $date = new DateTime();
        $date->add(new DateInterval('PT' . $pattern));
        if ($format) {
            return $date->format('Y-m-d H:i:s');
        } else {
            $tenDigitTime = strtotime($date->format('Y-m-d H:i:s'));
            return $tenDigitTime * 1000;
        }
    }
}
if (!function_exists('sub_time')) {

    function sub_time($pattern = '1H1M1S', $format = false)
    {
        $date = new DateTime();
        $date->sub(new DateInterval('PT' . $pattern));
        if ($format)
            return $date->format('Y-m-d H:i:s');
        else {
            $tenDigitTime = strtotime($date->format('Y-m-d H:i:s'));
            return $tenDigitTime * 1000;
        }
    }
}
if (!function_exists('closed_on')) {

    function closed_on()
    {
        $CI = &get_instance();
        $late_night_delay = $CI->db->where('key', 'late_night_delay')->get('cfgconfigs')->row()->value;
        $date = new DateTime();
        $Time = explode(':', $date->format('H:i:s'));
        if (intval($Time[0]) >= 0 && intval($Time[0]) < intval($late_night_delay)) {
            $date->sub(new DateInterval('PT' . intval($Time[0]) . 'H' . intval($Time[1]) . 'M' . (intval($Time[2]) + 1) . 'S'));
            $tenDigitTime = strtotime($date->format('Y-m-d H:i:s'));
            return $tenDigitTime * 1000;
        } else  return time() * 1000;
    }
}
if (!function_exists('business_date')) {

    function business_date()
    {
        $CI = &get_instance();
        $late_night_delay = $CI->db->where('key', 'late_night_delay')->get('cfgconfigs')->row()->value;
        $date = new DateTime();
        $Time = explode(':', $date->format('H:i:s'));
        if (intval($Time[0]) >= 0 && intval($Time[0]) < intval($late_night_delay)) {
            $date->sub(new DateInterval('PT' . intval($Time[0]) . 'H' . intval($Time[1]) . 'M' . (intval($Time[2]) + 1) . 'S'));
            return $date->format('Y-m-d');
        } else return $date->format('Y-m-d');
    }
}
if (!function_exists('delivery_time')) {
    function delivery_time($order_type, $date, $time)
    {
        if (!empty($time) && $time !== 'ASAP') {
            $timestamps = (strtotime($date . ' ' . $time));
            $date = date('d/m/Y', $timestamps);
            $time = date('h:i A', $timestamps);
            $timestamps *= 1000;
            return ['timestamps' => $timestamps, 'date' => $date, 'time' => $time];
        } else {
            if (in_preorder() && empty($time)) {
                $opening_hour = config_item('opening_hour');
                $AsapTime = new DateTime(date('Y-m-d') . ' ' . $opening_hour->start);
                $delay = get_row('cfgservices', ['value' => $order_type])->delay;
                $AsapTime->add(new DateInterval('PT' . $delay . 'M'));
                $date = $AsapTime->format('d/m/Y');
                $time = $AsapTime->format('h:i A');
                $timestamps = strtotime($AsapTime->format('Y-m-d H:i:s')) * 1000;
                return ['timestamps' => $timestamps, 'date' => $date, 'time' =>$time];
            } else {
                $AsapTime = new DateTime();
                $delay = get_row('cfgservices', ['value' => $order_type])->delay;
                $AsapTime->add(new DateInterval('PT' . $delay . 'M'));
                $date = $AsapTime->format('d/m/Y');
                $time = $AsapTime->format('h:i A');
                $timestamps = strtotime($AsapTime->format('Y-m-d H:i:s')) * 1000;
                return ['timestamps' => $timestamps, 'date' => $date, 'time' => $time];
            }
        }
    }
}
if (!function_exists('get_domain')) {

    function get_domain()
    {
        $CI = &get_instance();
        return preg_replace("/^[\w]{2,6}:\/\/([\w\d\.\-]+).*$/", "$1", $CI->config->slash_item('base_url'));
    }
}
if (!function_exists('sms')) {
    function sms($phone, $message)
    {
        if (config_item('sms_service') === "yes") {
            //API URL (GET & POST) : https://esms.mimsms.com/smsapi?api_key=(APIKEY)&type=text&contacts=(NUMBER)&senderid=(Approved Sender ID)&msg=(Message Content)
            $content = 'api_key=' . rawurlencode('C20061625e936d00a4c2c0.96874058') . '&type=' . rawurlencode('text') . '&contacts=' . rawurlencode($phone) . '&senderid=' . rawurlencode('8809601000100') . '&msg=' . rawurlencode($message);
            $ch = curl_init('https://esms.mimsms.com/smsapi');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }
    }
}
if (!function_exists('email')) {
    function email($data, $template = "common")
    {
        if (config_item('email_service') === "yes") {
            $CI = &get_instance();
            $CI->load->library('email');
            $config['mailtype'] = 'html';
            $config['charset'] = 'utf-8';
            $config['priority'] = 1;
            $config['protocol'] = 'sendmail';
            $CI->email->initialize($config);
            $CI->email->from($data['from'], $data['from_title']);
            $CI->email->to($data['to']);
            $CI->email->subject($data['subject']);
            return $CI->load->view('email/' . $template, $data, true);
            $CI->email->message($CI->load->view('email/' . $template, $data, true));
            $CI->email->reply_to($data['from'], $data['from_title']);
            return $CI->email->send();
        } else return  true;
    }
}
if (!function_exists('get_rows')) {

    function get_rows($table, $condition = null, $columns = '*', $order = null, $order_type = "ASC")
    {
        $CI = &get_instance();
        if ($columns) $CI->db->select($columns);
        if ($condition)  $CI->db->where($condition);
        if ($order) $CI->db->order_by($order, $order_type);
        return $CI->db->get($table)->result();
    }
}
if (!function_exists('get_row')) {

    function get_row($table, $condition = null, $columns = '*')
    {
        $CI = &get_instance();
        $CI->db->select($columns);
        if ($condition) {
            $CI->db->where($condition);
        }
        return $CI->db->get($table)->row();
    }
}
if (!function_exists('count_rows')) {

    function count_rows($table, $condition = null)
    {
        $CI = &get_instance();
        if ($condition) {
            $CI->db->where($condition);
        }
        $CI->db->from($table);
        return $CI->db->count_all_results();
    }
}
if (!function_exists('get_name')) {

    function get_name($table, $condition, $lang = null)
    {
        $record = get_row($table, $condition);
        return json_decode($record->name, true)[!empty($lang) ? $lang : lang_option()];
    }
}
if (!function_exists('is_active_page')) {

    function is_active_page($slug)
    {
        $obj = get_row('pages', array('slug' => $slug, 'status' => 'active'));
        return !empty($obj) ? true : false;
    }
}
if (!function_exists('pagination')) {

    function pagination($base_url, $total_rows, $per_page = 10, $first_url = "review")
    {
        $CI = &get_instance();
        $CI->load->library('pagination');
        $config['base_url'] = $base_url;
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $per_page;
        $config['full_tag_open'] = "<div class='w3-bar pagination'>";
        $config['full_tag_close'] = "</div>";
        $config['first_url'] = site_url($first_url);
        $config['attributes'] = array('class' => 'w3-button w3-theme-d1 w3-hover-theme w3-round', 'style' => 'margin:0 1px');
        $config['cur_tag_open'] = "<a class='w3-button w3-theme-l1 w3-hover-theme w3-round'>";
        $config['cur_tag_close'] = "</a>";
        $CI->pagination->initialize($config);
        return $CI->pagination->create_links();
    }
}
if (!function_exists('get_avg')) {
    function get_avg($table, $field, $condition = null)
    {
        $CI = &get_instance();
        $CI->db->select_avg($field);
        $CI->db->from($table);
        if ($condition) {
            $CI->db->where($condition);
        }
        return $CI->db->get()->row();
    }
}

if (!function_exists('get_sum')) {
    function get_sum($table, $field, $condition = null)
    {
        $CI = &get_instance();
        $CI->db->select_sum($field);
        $CI->db->from($table);
        if ($condition) {
            $CI->db->where($condition);
        }
        return $CI->db->get()->row();
    }
}
if (!function_exists('get_avatar')) {
    function get_avatar($id, $field = "photo", $table = "customers", $directory = 'customer')
    {
        $CI = &get_instance();
        $photo = $CI->db->where('id', $id)->get($table)->row_array()[$field];
        if (!empty($photo)) {
            return checkfile("./uploads/" . $directory . "/", $photo) ? UPLOAD_PATH . $directory . '/' . $photo : false;
        } else {
            return false;
        }
    }
}
if (!function_exists('get_themes')) {
    function get_themes()
    {
        $themes = array();
        $themes['amber'] = 'amber';
        $themes['black'] = 'black';
        $themes['blue'] = 'blue';
        $themes['light-blue'] = 'light-blue';
        $themes['blue-grey'] = 'blue-grey';
        $themes['brown'] = 'brown';
        $themes['cyan'] = 'cyan';
        $themes['green'] = 'green';
        $themes['light-green'] = 'light-green';
        $themes['grey'] = 'grey';
        $themes['dark-grey'] = 'dark-grey';
        $themes['orange'] = 'orange';
        $themes['deep-orange'] = 'deep-orange';
        $themes['purple'] = 'purple';
        $themes['deep-purple'] = 'deep-purple';
        $themes['indigo'] = 'indigo';
        $themes['khaki'] = 'khaki';
        $themes['lime'] = 'lime';
        $themes['pink'] = 'pink';
        $themes['red'] = 'red';
        $themes['teal'] = 'teal';
        $themes['leaf'] = 'leaf';
        $themes['yellow'] = 'yellow';
        $themes['white'] = 'white';
        return $themes;
    }
}
if (!function_exists('add_activity')) {
    function add_activity($issue = null, $activity = null)
    {
        $CI = &get_instance();
        $CI->load->model('Userlogs');
        $CI->load->model('Useractivities');
        $headers = getallheaders();
        if (!empty($headers['log_id'])) {
            $logger = $CI->Userlogs->get($headers['log_id']);
            $CI->Useractivities->save(array('user_id' => $logger->user_id, 'hub_id' => $headers['hub_id'], 'device_id' => $logger->device_code, 'issue' => $issue, 'activity' => $activity));
        }
    }
}
if (!function_exists('add_sync')) {
    function add_sync($sync_table, $sync_action, $sync_item = 0)
    {
        if (config_item('software_type') !== "online") {
            $CI = &get_instance();
            $CI->load->model('Userlogs');
            $CI->load->model('Syncupdates');
            $CI->load->model('Syncdevices');
            $devices = $CI->Userlogs->get_device_list();
            if (!empty($devices)) {
                $id = $CI->Syncupdates->save(array('sync_table' => $sync_table, 'sync_action' => $sync_action, 'sync_item' => $sync_item));
                foreach ($devices as $device) {
                    $CI->Syncdevices->save(array('sync_id' => $id, 'device_id' => $device->device_code));
                }
            }
        }
    }
}
if (!function_exists('objectToArray')) {

    function objectToArray($d)
    {
        if (is_object($d)) $d = get_object_vars($d);
        if (is_array($d)) return array_map(__FUNCTION__, $d);
        else return $d;
    }
}
if (!function_exists('unique_multidim_array')) {
    function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();
        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
if (!function_exists('checkfile')) {
    function checkfile($path = '', $file = '')
    {
        return file_exists($path . $file) ? true : false;
    }
}
if (!function_exists('cfgdiscount')) {
    function cfgdiscount($id = null, $status = false)
    {
        if ($id) {
            $conditions = ['id' => $id];
            if ($status)  $conditions['status'] = $status;
            return get_row('cfgdiscounts', $conditions);
        } else {
            $conditions = null;
            if ($status)  $conditions['status'] = $status;
            return $conditions ? get_rows('cfgdiscounts', $conditions) : get_rows('cfgdiscounts');
        }
    }
}
if (!function_exists('discountmulti')) {
    function discountmulti($id = null, $day = false, $order_type = "delivery")
    {
        $conditions['status'] = "active";
        $conditions['web'] = true;
        $conditions[$order_type] = true;
        if ($day) $conditions[$day] = true;
        $timestamps = time() * 1000;
        $conditions['date_start <='] = $timestamps;
        $conditions['date_end >='] = $timestamps;
        if ($id) {
            $conditions['id'] = $id;
            return get_row('discountmulti', $conditions, '*', 'min_order');
        } else return get_rows('discountmulti', $conditions);
    }
}
if (!function_exists('discountplan')) {
    function discountplan($id = null, $day = false, $order_type = "delivery")
    {
        $conditions['status'] = "active";
        $conditions['web'] = true;
        $conditions[$order_type] = true;
        if ($day) $conditions[$day] = true;
        $timestamps = time() * 1000;
        $conditions['date_start <='] = $timestamps;
        $conditions['date_end >='] = $timestamps;
        if ($id) {
            $conditions['id'] = $id;
            return get_row('discountplans', $conditions);
        } else return get_rows('discountplans', $conditions, '*', 'min_order', 'DESC');
    }
}
if (!function_exists('discountpromo')) {
    function discountpromo($code = null, $day = false, $order_type = null)
    {
        $conditions['status'] = "active";
        $conditions['web'] = true;
        if ($order_type && in_array($order_type, ['collection', 'delivery'])) $conditions[$order_type] = true;
        if ($day) $conditions[$day] = true;
        $timestamps = time() * 1000;
        $conditions['date_start <='] = $timestamps;
        $conditions['date_end >='] = $timestamps;
        if ($code) {
            $conditions = ['code' => $code];
            return get_row('discountpromos', $conditions);
        } else return get_rows('discountpromos', $conditions, '*', 'min_order', 'DESC');
    }
}
if (!function_exists('prodfreeplan')) {
    function prodfreeplan($id = null, $day = false, $order_type = "delivery")
    {
        $conditions['status'] = "active";
        $conditions['web'] = true;
        $conditions[$order_type] = true;
        if ($day) $conditions[$day] = true;
        $timestamps = time() * 1000;
        $conditions['date_start <='] = $timestamps;
        $conditions['date_end >='] = $timestamps;
        if ($id) {
            $conditions['id'] = $id;
            return get_row('prodfreeplans', $conditions);
        } else return get_rows('prodfreeplans', $conditions, '*', 'min_order', 'DESC');
    }
}
if (!function_exists('set_last_page')) {
    function set_last_page($slug = '')
    {
        $CI = &get_instance();
        $CI->session->set_userdata('lastAccessUrl', site_url($slug));
    }
}
if (!function_exists('get_last_page')) {
    function get_last_page()
    {
        $CI = &get_instance();
        return $CI->session->userdata('lastAccessUrl');
    }
}
if (!function_exists('set_page_slug')) {
    function set_page_slug($slug = '')
    {
        $CI = &get_instance();
        $CI->session->set_userdata('page_slug', $slug);
    }
}
if (!function_exists('get_page_slug')) {
    function get_page_slug()
    {
        $CI = &get_instance();
        return $CI->session->userdata('page_slug');
    }
}
if (!function_exists('stores')) {
    function stores($id = null)
    {
        $CI = &get_instance();
        $CI->load->model('Userhubs');
        $CI->load->model('Geoareas');
        $results['lang'] = lang_option();
        $results['list'] = $CI->Userhubs->get(['status' => 'active'], NULL, NULL, ['id', 'name', 'area_id', 'house_en', 'house_bn', 'phone', 'email']);
        $id = $id ? $id : $CI->session->userdata('store_branch');
        foreach ($results['list'] as $k => $v) {
            $results['list'][$k]->name = $CI->Userhubs->decode($v->name);
            $results['list'][$k]->house = array('en' => $v->house_en, 'bn' => $v->house_bn);
            $results['list'][$k]->area = $CI->Geoareas->formatted($v->area_id)->formatted;
            $results['list'][$k]->lang = lang_option();
            if ($v->id == $id) $results['selected'] = $results['list'][$k];
        }
        $results['branch_change_text'] = lang('branch_change_text');
        $results['spinning'] = ASSET_PATH . "img/ajax-loader.gif";
        return $results;
    }
}
if (!function_exists('store_info')) {

    function store_info($store_id = null, $lang = null)
    {
        $CI = &get_instance();
        $CI->load->model('Geoareas');
        $lang = $lang ? $lang : lang_option();
        $info = [];
        if (!empty($store_id)) {
            $CI->load->model('Userhubs');
            $info = objectToArray($CI->Userhubs->get($store_id, true));
            $info['hub'] = $info['name_' . $lang];
            $info['house'] = $info['house_' . $lang];
            $info['area'] = $CI->Geoareas->formatted((int)$info['area_id'])->formatted[$lang];
        } else {
            $info['hub'] = null;
            $info['phone'] = config_item('shop_phone');
            $info['email'] = config_item('shop_email');
            $info['house'] = config_item('store_house_' . $lang);
            $info['area'] = $CI->Geoareas->formatted((int) config_item('store_area_id'))->formatted[$lang];
        }
        $info['name'] = config_item('shop_name_' . $lang);
        return $info;
    }
}
if (!function_exists('collection_point')) {
    function collection_point($hub_id = null)
    {
        $CI = &get_instance();
        $CI->load->model('Userhubs');
        $CI->load->model('Geoareas');
        if (!empty($hub_id) && config_item('store_type') === 'multiple') {
            $hub = $CI->Userhubs->get($hub_id, true, true);
            $hub->name = json_decode($hub->name, true);
            $hub->house = json_decode($hub->house, true);
            $hub->area = $CI->Geoareas->formatted($hub->area_id)->formatted;
            return $hub;
        } else {
            $hub = new stdClass();
            $hub->name = ['en' => config_item('shop_name_en'), 'bn' => config_item('shop_name_bn')];
            $hub->house = ['en' => config_item('store_house_en'), 'bn' => config_item('store_house_bn')];
            $hub->area = $CI->Geoareas->formatted(config_item('store_area_id'))->formatted;
            $hub->phone = config_item('shop_phone');
            $hub->email = config_item('shop_email');
            return $hub;
        }
    }
}
if (!function_exists('is_opened')) {
    function is_opened()
    {
        $opening_hour = config_item('opening_hour');
        $start = strtotime(date('Y-m-d') . ' ' . $opening_hour->start);
        $end = strtotime(date('Y-m-d') . ' ' . $opening_hour->end);
        $now = time();
        return ($now >= $start && $now <= $end) && $opening_hour->opened;
    }
}
if (!function_exists('temp_closed')) {
    function temp_closed()
    {
        if (is_opened()) {
            $opening_hour = config_item('opening_hour');
            return  $opening_hour->temp_closed && (strtotime($opening_hour->closed_till) > time());
        } else return false;
    }
}
if (!function_exists('has_preorder')) {
    function has_preorder()
    {
        $opening_hour = config_item('opening_hour');
        $preorder_before_hour = $opening_hour->prehour;
        $start = strtotime(date('Y-m-d') . ' ' . $opening_hour->start);
        if (!is_opened() && (int) $preorder_before_hour > 0) {
            $startTime = new DateTime(date('Y-m-d') . ' ' . $opening_hour->start);
            $startTime->add(new DateInterval('PT' . $preorder_before_hour . 'H'));
            $now = strtotime($startTime->format('Y-m-d H:i:s'));
            return $now >= $start && $opening_hour->opened;
        }
        return false;
    }
}
if (!function_exists('in_preorder')) {
    function in_preorder()
    {
        $CI = &get_instance();
        return $CI->session->userdata('preorder_permission');
    }
}
if (!function_exists('time_slot')) {
    function time_slot($start = null,  $end = null, $offset = 0, $interval = 10, $from_now = false)
    {
        $startTime = null;
        if (!empty($start)) {
            if ($from_now) $startTime = new DateTime();
            else $startTime = new DateTime(date('Y-m-d h:i A', strtotime(date('Y-m-d') . ' ' . $start)));
        } else $startTime = new DateTime();
        $startTime->add(new DateInterval('PT' . $offset . 'M'));
        if (!empty($end)) $endTime = new DateTime(date('Y-m-d h:i A', strtotime(date('Y-m-d') . ' ' . $end)));
        else $endTime =  new DateTime(date('Y-m-d h:i A', strtotime(date('Y-m-d') . ' ' . '11:59 PM')));
        $slots = [];
        do {
            if (strtotime($startTime->format('Y-m-d h:i A')) <= strtotime($endTime->format('Y-m-d h:i A')))
                $slots[] = round_minutes($startTime->format('h:i A'), $interval);
            $startTime->add(new DateInterval('PT' . $interval . 'M'));
        } while (strtotime($endTime->format('Y-m-d h:i A')) > strtotime($startTime->format('Y-m-d h:i A')));
        return $slots;
    }
}
if (!function_exists('round_minutes')) {
    function round_minutes($hiA, $interval = 15)
    {
        $hi_A = explode(' ', $hiA);
        $hi = $hi_A[0];
        $A = $hi_A[1];
        $h_i = explode(':', $hi);
        $h = $h_i[0];
        $i = $h_i[1];
        $mod = $i % $interval;
        if ($mod === 0) {
            return $hiA;
        } else {
            $dif = $interval - $mod;
            if ($i + $dif <= 59)
                $i += $dif;
            else {
                $h++;
                if ($h > 12) $h -= 12;
                if ($h < 10) $h = "0" . $h;
                $i = 0;
            }
            if ($i < 10) $i = "0" . $i;
            return $h . ':' . $i . ' ' . $A;
        }
    }
}
if (!function_exists('date_slot')) {
    function date_slot($offset = 0, $limit = 15)
    {
        $slots = [];
        $CI = &get_instance();
        $CI->load->model('Openings');
        for ($i = $offset; $i <= $limit; $i++) {
            $date = new DateTime();
            $date->add(new DateInterval('P' . $i . 'D'));
            if (!empty($CI->Openings->get(['day' => $date->format('D'), 'opened' => true], true, false))) {
                $slots[$i]['date_value'] = $date->format('Y-m-d');
                $slots[$i]['date_text'] = $date->format('d/m/Y');
                $slots[$i]['date_day'] = lang($date->format('D'));
            }
        }
        return $slots;
    }
}
if (!function_exists('order_slug')) {
    function order_slug($id = 0)
    {
        $shop_type = config_item('shop_type');
        if (in_array($shop_type, ['restaurant', 'takeaway'])) return  $id ? 'menu/' . $id : 'menu';
        else if (in_array($shop_type, ['medicine', 'book'])) return $id ? 'store/' . $id : 'store';
        else return $id ? 'shop/' . $id : 'shop';
    }
}
if (!function_exists('order_status_css')) {
    function order_status_css($key, $type = 'bg')
    {
        $text = ['pending' => 'w3-text-orange', 'confirmed' => 'w3-text-green', 'paid' => 'w3-text-green', 'rejected' => 'w3-text-red', 'canceled' => 'w3-text-red', 'unpaid' => 'w3-text-red'];
        $bg = ['pending' => 'w3-orange', 'confirmed' => 'w3-green', 'paid' => 'w3-green', 'rejected' => 'w3-red', 'canceled' => 'w3-red', 'unpaid' => 'w3-red',];
        return $type === 'bg' ? $bg[$key] : $text[$key];
    }
}
if (!function_exists('reorder_sides')) {
    function reorder_sides($sides)
    {
        $sets = [];
        foreach ($sides as $i => $v) {
            $obj = ['group_id' => $v['group_id'], 'group_name' => $v['group_name']];
            $found = false;
            for ($j = 0; $j < count($sets); $j++) {
                if ($sets[$j]['group_id']  === $sides[$i]['group_id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) array_push($sets, $obj);
        }
        for ($index = 0; $index < count($sets); $index++) {
            $sets[$index]['sides'] = [];
            for ($s = 0; $s < count($sides); $s++) {
                if ($sides[$s]['group_id'] == $sets[$index]['group_id']) array_push($sets[$index]['sides'], $sides[$s]);
            }
        }
        return $sets;
    }
}
if (!function_exists('debugPrint')) {
    function debugPrint($object, $title = "", $isMarkup = false)
    {
        echo '<font color="red">Debug <<< START';
        if (!empty($title)) {
            echo "$title: ";
        }
        if (false == $isMarkup) {
            echo "<pre>";
            print_r($object);
            echo "</pre>";
        } else {
            echo htmlspecialchars($object);
        }
        echo 'END >>></font>';
    }
}
