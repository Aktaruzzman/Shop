<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Data extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->model('Sites');
        $this->load->model('Customers');
        $this->load->model('Customeraddresses');
        $this->load->model('Cart');
        $this->load->model('Openings');
        $this->load->model('Geoareas');
        $this->load->model('Cookie_Model');
    }
    public function product_get($store = 0)
    {
        //$this->Cart->destroy();
        $results['shop_type'] = config_item('shop_type');
        $results['store_type'] = config_item('store_type');
        $results['stores'] = stores($store);
        $results['categories'] = $this->Sites->products($store);
        $results['cart'] = $this->Cart->body();
        $results['lang'] = lang_option();
        return $this->response($results);
    }
    public function outlet_get()
    {
        $results = stores($this->session->userdata('store_branch') ? $this->session->userdata('store_branch') : 1);
        return $this->response($results);
    }
    public function outlet_post()
    {
        $store_branch = $this->post('outlet');
        if ($this->session->set_userdata('store_branch') != $store_branch) {
            $this->session->set_userdata('store_branch', $store_branch);
            $this->Cart->set_delivery_area($this->Userhubs->get($store_branch, true, true)->area_id);
            $this->Cookie_Model->set_store();
            if (!empty($this->Cart->get_cart())) $this->Cart->reshuffle($store_branch);
        }
        return $this->response(['status' => true]);
    }
    public function itembox_get($item_id, $option_id = 0)
    {
        return $this->response($this->Sites->itembox(['item_id' => $item_id, 'option_id' => $option_id]));
    }
    public function customset_get($item_id, $option_id = 0)
    {
        return $this->response($this->Sites->customset(['item_id' => $item_id, 'option_id' => $option_id]));
    }
    public function cart_get()
    {
        return $this->response(['cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function ordertype_post()
    {
        $this->Cart->clear_order_receive_date();
        $this->Cart->clear_order_receive_time();
        $this->Cart->clear_delivery_plan();
        $this->Cart->clear_minimum_delivery();
        $this->Cart->set_order_type($this->post('order_type'));
        $results['status'] = $this->Cart->get_order_type() ? true : false;
        $results['page_slug'] = get_page_slug();
        $results['type'] = $type = $this->Cart->get_order_type();
        $results['delivery_area_popup'] = $type === 'delivery' && empty($this->Cart->get_delivery_area()) ? true : false;
        $results['delivery_home_popup'] = empty($this->Cart->get_delivery_home()) && $this->session->userdata('customerId') ? true : false;
        $this->Cart->set_order_receive_date(date('Y-m-d'));
        $results['receive_time_popup'] = empty($this->Cart->get_order_receive_time()) ? true : false;
        return $this->response($results);
    }
    public function deliveryarea_get($id = 0)
    {
        $data['lang'] = lang_option();
        $conditions = null;
        //if (config_item('store_type') == "multiple" && !empty($this->session->userdata('store_branch'))) $conditions['hub_id'] = $this->session->userdata('store_branch');
        $data['areas'] = $this->Cfgdelareas->get_by($conditions, false, false);
        foreach ($data['areas'] as $index => $obj) {
            $data['areas'][$index] = $this->Cfgdelareas->build_entity($obj);
            if ($id == $obj->id)  $results['entity'] = $data['areas'][$index];
        }
        if ($this->Cart->get_delivery_area()) $data['area'] = $this->Geoareas->formatted($this->Cart->get_delivery_area())->formatted;
        return $this->response($data);
    }
    public function deliveryarea_post()
    {
        if ($this->Sites->area_dropdown($this->post('area'))) {
            $this->Cart->set_delivery_area($this->post('area'));
            $this->Cart->clear_delivery_home();
            $this->Cookie_Model->clear_delivery_home();
        }
        $results['status'] = $this->Cart->get_delivery_area() ? true : false;
        $results['delivery_home_popup'] = empty($this->Cart->get_delivery_home()) && !empty($this->session->userdata('customerId')) ? true : false;
        $results['receive_time_popup'] = empty($this->Cart->get_order_receive_time()) ? true : false;
        $results['page_slug'] = get_page_slug();
        return $this->response($results);
    }
    public function deliveryhome_get()
    {
        $results['area'] = $this->Sites->area_dropdown($this->Cart->get_delivery_area());
        $results['homes'] = $this->Customeraddresses->get(['area_id' => $this->Cart->get_delivery_area(), 'cust_id' => $this->session->userdata('customerId')], false, false, ['id', 'label', 'house', 'default']);
        if (!empty($results['homes'])) {
            foreach ($results['homes'] as $k => $v) {
                $results['homes'][$k]->house = $this->Customeraddresses->decode($v->house);
                if ($this->Cart->get_delivery_home() == $v->id) $results['home'] = $results['homes'][$k];
            }
            if (empty($results['home'])) $results['home'] = $results['homes'][0];
        }
        $results['lang'] = lang_option();
        return $this->response($results);
    }
    public function deliveryhome_post()
    {
        $post = $this->post();
        if ($this->Cart->get_delivery_area() && $this->session->userdata('customerId') && !empty($post['house_en'])) {
            $post['house_bn'] = $post['label_en'] = $post['label_bn'] = $post['house_en'];
            $post['house'] = $post['label'] = $this->Sites->encode(['en' => $post['house_en'], 'bn' => $post['house_bn']]);
            $post['cust_id'] = $this->session->userdata('customerId');
            $post['area_id'] = $this->Cart->get_delivery_area();
            $id = $this->Customeraddresses->save($post);
            $this->Cart->set_delivery_home($id);
        } else {
            if (!empty($post['house_id'])) $this->Cart->set_delivery_home($post['house_id']);
        }
        $post['status'] = $this->Cart->get_delivery_home() ? true : false;
        $post['receive_time_popup'] = empty($this->Cart->get_order_receive_time()) ? true : false;
        $pos['page_slug'] = get_page_slug();
        return $this->response($post);
    }

    public function cartadd_post()
    {
        $this->Cart->add_item($this->post());
        return $this->response(['cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function cartplus_post()
    {
        $this->Cart->plus($this->post());
        return $this->response(['cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function cartminus_post()
    {
        $this->Cart->minus($this->post('line'));
        return $this->response(['cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function cartremove_post()
    {
        $line = $this->Cart->remove($this->post('line'));
        return $this->response(['line' => $line, 'cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function cartsideplus_post()
    {
        $this->Cart->side_plus($this->post());
        return $this->response(['cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function cartsideminus_post()
    {
        $this->Cart->side_minus($this->post());
        return $this->response(['cart' => $this->Cart->body(), 'lang' => lang_option()]);
    }
    public function cartdestroy_post()
    {
        $this->Cart->destroy();
        return $this->response(['cart' => $this->Cart->body(),  'lang' => lang_option()]);
    }

    /**
     * Undocumented function
     *
     * @param string $type order/booking
     * @return void
     */
    public function datetimeslot_get($type = 'order')
    {
        $data['lang'] = lang_option();
        $data['label_select_option'] = sprintf(lang('select_option'), sprintf(lang($this->Cart->get_order_type() . '_option'), lang('time')));
        $data['label_select_date'] = sprintf(lang('select_option'), lang('date'));
        $data['label_select_time'] = sprintf(lang('select_option'), lang('time'));
        $data['label_asap'] = lang('asap');
        $data['label_save_and_set'] = sprintf(lang('option_and_option'), lang('save'), lang('set'));
        $data['offset'] = $offset = $type === "booking" ? (int) config_item('booking_offest_day') : (int) config_item('order_offest_day');
        $data['limit'] = $limit = $type === "booking" ? (int) config_item('booking_max_day') : (int) config_item('order_max_day');
        $data['from_now'] = $from_now = $type == "booking" ? false : ((int) config_item('order_max_day') > 0 ? false : true);
        if ($type === 'order' && !empty($this->Cart->get_order_receive_date())) {
            if (strtotime($this->Cart->get_order_receive_date()) === strtotime(date('Y-m-d')))  $data['from_now'] = $from_now = true;
        }
        $data['opening'] = $opening = $this->Openings->get(['day' => date('D', strtotime($this->Cart->get_order_receive_date()))], true, false);
        $obj = config_item($this->Cart->get_order_type() ? $this->Cart->get_order_type() : 'delivery');
        $obj->name = $this->Sites->decode($obj->name);
        $data['service'] = $obj;
        $data['date_slots'] = $dates = date_slot($offset, $limit);

        $data['time_slots'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . $opening->start)), date('h:i A', strtotime(date('Y-m-d ') . $opening->end)), $obj->delay, (int) config_item('slot_interval_time'), $from_now);
        if ($from_now) array_unshift($data['time_slots'], 'ASAP');

        $receive_date = $this->Cart->get_order_receive_date();
        if (!empty($receive_date)) {
            $data['selected_date']['date_value'] = $receive_date;
            $data['selected_date']['date_text'] = date('d/m/Y', strtotime($receive_date));
            $data['selected_date']['date_day'] = lang(date('D', strtotime($receive_date)));
        } else $data['selected_date'] = array_shift($data['date_slots']);
        $receive_time = $this->Cart->get_order_receive_time();
        if ($receive_time) $data['selected_time'] =  $receive_time;
        return $this->response($data);
    }
    public function receivedate_post()
    {
        $results['status'] = true;
        $date = $this->post('date');
        $time = $this->Cart->get_order_receive_time();
        if (!empty($time) && verify_date('Y-m-d h:i A', $date . ' ' . $time) && (strtotime($date . ' ' . $time) < time())) {
            return $this->response(['status' => false, 'page_slug' => get_page_slug(), 'message' => sprintf(lang('you_have_selected_wrong_option'), lang('date'))]);
        } else if (verify_date('Y-m-d', $date)) {
            $this->Cart->set_order_receive_date($date);
            $this->response(['status' => true, 'page_slug' => get_page_slug(), 'message' => sprintf(lang('you_have_selected_wrong_option'), lang('date'))]);
        }
    }
    public function receivetime_post()
    {
        $date = $this->post('date');
        $time = $this->post('time');
        if ($time === "ASAP") {
            $this->Cart->set_order_receive_time($time);
            return $this->response(['status' => true, 'page_slug' => get_page_slug()]);
        } else  if (verify_date('Y-m-d h:i A', $date . ' ' . $time) && strtotime($date . ' ' . $time) >= time()) {
            $this->Cart->set_order_receive_time($time);
            return $this->response(['status' => true, 'page_slug' => get_page_slug()]);
        } else return $this->response(['status' => false, 'page_slug' => get_page_slug(), 'message' => sprintf(lang('you_have_selected_wrong_option'), lang('time'))]);
    }
    public function setpreorder_post()
    {
        $this->Cart->set_preorder_permission();
        return $this->response(['branch' => $this->session->userdata('store_branch'),  'lang' => lang_option()]);
    }
    public function search_get($input)
    {
        return $this->response($this->Sites->item_by_search($input));
    }
    public function checkout_get()
    {
        if ($this->My_Model->isLoggedin()) {
            $results['lang'] = lang_option();
            $results['shop'] = ['en' => config_item('shop_name_en'), 'bn' => config_item('shop_name_bn')];
            $results['customer'] = $this->Customers->get($this->session->userdata('customerId'), true, true, ['id', 'name', 'phone', 'email']);
            $results['customer']->name = $this->Customers->decode($results['customer']->name);

            if ($this->Cart->get_order_type() === 'delivery') {
                $results['homes'] = $this->Customeraddresses->get(['area_id' => $this->Cart->get_delivery_area(), 'cust_id' => $this->session->userdata('customerId')], false, false, ['id', 'label', 'house', 'default']);
                if (!empty($results['homes'])) {
                    foreach ($results['homes'] as $k => $v) {
                        $results['homes'][$k]->house = $this->Customeraddresses->decode($v->house);
                        if ($this->Cart->get_delivery_home() == $v->id) $results['home'] = $results['homes'][$k];
                    }
                    if (empty($results['home'])) $results['home'] = $results['homes'][0];
                }
                $results['del_home'] = $this->Cart->get_delivery_home();
            }
            $results['order_type'] = $this->Cart->get_order_type();
            $results['cart'] = $this->Cart->body();
            $results['delivery_date'] = $this->Cart->get_order_receive_date() ? date('d/m/Y', strtotime($this->Cart->get_order_receive_date())) : date('d/m/Y');
            $results['delivery_time'] = $this->Cart->get_order_receive_time() ? ($this->Cart->get_order_receive_time() === 'ASAP' ? lang('asap') : $this->Cart->get_order_receive_time()) : lang('asap');;
            $results['store_type'] = config_item('store_type');
            //Purchase store
            $results['store'] = ['id' => $this->session->userdata('store_branch')];
            if ($results['store_type'] === 'multiple') {
                $store = stores($this->session->userdata('store_branch'))['selected'];
                $results['store']['area'] = $store->area;
                $results['store']['area_id'] = $store->area_id;
                $results['store']['house'] = $store->house;
            } else {
                $results['store']['area'] = $this->Geoareas->formatted(config_item('store_area_id'))->formatted;
                $results['store']['area_id'] = config_item('store_area_id');
                $results['store']['house'] = ['en' => config_item('store_house_en'), 'bn' => config_item('store_house_bn')];
            }
            //language
            $results['order_type_text'] = lang($this->Cart->get_order_type());
            $results['order_type_date'] = sprintf(lang($this->Cart->get_order_type() . '_option'), lang('date'));
            $results['order_type_time'] = sprintf(lang($this->Cart->get_order_type() . '_option'), lang('time'));
            $results['who_receive_on_behalf'] = sprintf(lang('who_receive_on_behalf'), lang($this->Cart->get_order_type()));
            $results['page_slug'] = get_page_slug();
            if ($this->Cart->get_promo_discount_plan()) {
                $results['coupon'] = $this->Cart->get_promo_discount_plan()['code'];
                $results['coupon_button'] = lang('remove');
            } else $results['coupon_button'] = lang('apply');
            $results['edit_date'] = config_item('order_max_day') > 0 ? true : false;
            return $this->response($results);
        }
    }
    public function applycoupon_post()
    {
        $this->load->model('Sales');
        $plan = $this->Sales->get_promo_plans($this->Cart->get_order_type(), $this->post('code'), 'web');
        if (!empty($plan)) $this->Cart->set_promo_discount_plan(objectToArray($plan));
        else $this->Cart->clear_promo_discount_plan();
        $msg = (!empty($plan)) ? sprintf(lang('your_option_is_correct'), lang('code')) : sprintf(lang('your_option_is_incorrect'), lang('code'));
        return $this->response(['status' => $plan ? true : false, 'plan' => $plan, 'msg' => $msg]);
    }
    public function removecoupon_post()
    {
        $this->Cart->clear_promo_discount_plan();
        return $this->response(['status' => true]);
    }
    function order_get($id)
    {
        $this->load->model('Sales');
        return $this->response($this->Sales->get($id, true));
    }
    public function test_get()
    {

        return $this->response(['']);
        //return $this->response(['dates' => date_slot(0, 3), 'plans' => $this->Cart->get_multi_discount(), "is_opened" => is_opened(), "temp_closed" => temp_closed(), 'preorder' => has_preorder()]);
    }
}
