
<?php

class Cart extends MY_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Sites');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodtops');
        $this->load->model('Prodfixedsetoptions');
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Sales');
        $this->load->model('Customeraddresses');
    }
    //All PRIVATE METHODS BLOCK
    private function __get_services($type = null)
    {
        $array = [];
        if (!empty(config_item('delivery')) && config_item('opening_hour')->delivery) {
            $array[] = [
                'name' => $this->decode(config_item('delivery')->name),
                'delay' => config_item('delivery')->delay,
                'value' => config_item('delivery')->value,
                'lang' => lang_option(),
                'delivery_time' => $this->get_order_receive_time() && $this->get_order_type() === "delivery" ? $this->get_order_receive_time() : false,
                'delivery_date' => $this->get_order_receive_date() ? date('d/m/Y', strtotime($this->get_order_receive_date())) : date('d/m/Y'),
                'limit' => config_item('order_max_day')
            ];
        }
        if (!empty(config_item('collection')) && config_item('opening_hour')->collection) {
            $array[] = [
                'name' => $this->decode(config_item('collection')->name),
                'delay' => config_item('collection')->delay,
                'value' => config_item('collection')->value,
                'lang' => lang_option(),
                'delivery_time' => $this->get_order_receive_time() &&  $this->get_order_type() === "collection" ? $this->get_order_receive_time() : false,
                'delivery_date' => $this->get_order_receive_date() ? date('d/m/Y', strtotime($this->get_order_receive_date())) : date('d/m/Y'),
                'limit' => config_item('order_max_day')
            ];
        }
        return $array;
    }
    private function __search($item)
    {
        $cart_items = $this->get_cart();
        foreach ($cart_items as $key => $stack) {
            if (empty($item['sides']) && $item['item_id'] == $stack['item_id'] && $item['option_id'] == $stack['option_id'] && $item['plan'] == $stack['free_rule']) {
                return $key;
            }
        }
        return false;
    }
    private function __push($item)
    {
        $cart_items = $this->get_cart();
        $cart_items[$item['cart_id']] = $item;
        $this->set_cart($cart_items);
        $this->__set_line($item['cart_id']);
    }
    private function __set_line($line)
    {
        $cart_items = $this->get_cart();
        if (!empty($cart_items[$line])) {
            foreach ($cart_items as $key => $val) {
                $cart_items[$key]['active'] = "inactive";
            }
            $cart_items[$line]['active'] = "w3-text-red active";
            $this->set_cart($cart_items);
        }
    }
    private function __sell_total($sell_price, $qty = 1, $sides = [])
    {
        if (empty($sides)) return $sell_price * $qty;
        else return $this->__side_sell_total($sides) + ($sell_price * $qty);
    }
    private function __cost_total($cost_price, $qty = 1, $sides = [])
    {
        if (empty($sides)) return $cost_price * $qty;
        else return $this->__side_cost_total($sides) + ($cost_price * $qty);
    }
    private function __side_cost_total($sides)
    {
        $cost_total = 0;
        foreach ($sides as $side) {
            $cost_total += $side['cost_total'];
        }
        return $cost_total;
    }
    private function __side_sell_total($sides)
    {
        $sell_total = 0;
        foreach ($sides as $side) {
            $sell_total += $side['total'];
        }
        return $sell_total;
    }
    private function __set_item_tops($array)
    {
        $sides = [];
        foreach ($array['sides'] as $side) {
            $top = $this->Prodtops->get($side['id'], true);
            $top->edit = false;
            $top = $this->Prodtops->build_entity($top, $this->session->userdata('store_branch'));
            $top->sell_price = $top->out_price;
            $top->qty = $side['qty'];
            $top->topping_id = $top->id;
            $top->group_id = 888888;
            $top->group_name = ['en' => 'Toppings', 'bn' => 'টপিংস'];
            $top->inc = true;
            $top->dec = true;
            array_push($sides, objectToArray($top));
        }
        $array['sides'] = $sides;
        return $array;
    }
    private function __set_fixed_setoption($array)
    {
        $conditions = ['hook_item_id' => $array['item_id'], 'status' => 'active'];
        if (!empty($array['option_id']))  $conditions['hook_option_id'] = $array['option_id'];
        $results = $this->Prodfixedsetoptions->get($conditions, false, false);
        foreach ($results as $index => $obj) {
            $results[$index] = $this->Prodfixedsetoptions->build_entity($obj, $this->session->userdata('store_branch'));
            $results[$index]->sell_price = 0;
            $results[$index]->group_id = $array['option_id'] . '_' . $array['item_id'];
            $results[$index]->group_name = ['en' => 'Fixed Set', 'bn' => 'ফিক্সড সেট'];
            $results[$index]->unit_id = $results[$index]->unit->id;
            $results[$index]->unit_name = $results[$index]->unit->short;
            $results[$index]->inc = false; //increase
            $results[$index]->dec = false; //decrease
        }
        $sides = objectToArray($results);
        if ($array['has_top']) {
            $tops = $this->__set_item_tops($array);
            $sides = array_merge($sides, $tops['sides']);
        }
        $array['sides'] = $sides;
        return $array;
    }
    private function __set_custom_setoption($array)
    {
        $sides = [];
        foreach ($array['sides'] as $side) {
            $option = $this->Prodcustsetoptions->get($side['id'], true);
            $option->edit = false;
            $option = $this->Prodcustsetoptions->build_entity($option, $this->session->userdata('store_branch'));
            $option->sell_price = $option->out_price;
            $option->qty = $side['qty'];
            $option->group_id = $option->set_id;
            $option->group_name = $side['group_name'];
            if ($option->out_price > 0 && empty($option->modify_id)) {
                $option->inc = true;
                $option->dec = true;
            }
            array_push($sides, objectToArray($option));
        }
        $array['sides'] = $sides;
        return $array;
    }
    private function __add_sides($sides)
    {
        $sides_arr = [];
        foreach ($sides as $side) {
            array_push($sides_arr, $this->__side($side));
        }
        return $sides_arr;
    }
    private function __side($side)
    {
        $side_arr = [];
        $side_arr['item_id'] = !empty($side['item_id']) ? $side['item_id'] : null;
        $side_arr['option_id'] = !empty($side['option_id']) ? $side['option_id'] : null;
        $side_arr['topping_id'] = !empty($side['topping_id']) ? $side['topping_id'] : null;
        $side_arr['modify_id'] = !empty($side['modify_id']) ? $side['modify_id'] : null;
        $side_arr['group_id'] = !empty($side['group_id']) ? $side['group_id'] : null;
        $side_arr['group_name'] = !empty($side['group_name']) ? $side['group_name'] : null;
        $side_arr['name'] = !empty($side['name']) ? $side['name'] : ['en' => '', 'bn' => ''];
        $side_arr['qty'] = !empty($side['qty']) ? $side['qty'] : 0;
        $side_arr['new_qty'] = $side_arr['qty'];
        $side_arr['base_price'] = !empty($side['base_price']) ? $side['base_price'] : 0;
        $side_arr['in_price'] = !empty($side['in_price']) ? $side['in_price'] : 0;
        $side_arr['out_price'] = !empty($side['out_price']) ? $side['out_price'] : 0;
        $side_arr['sell_price'] = array_key_exists('sell_price', $side) ? $side['sell_price'] : $side_arr['out_price'];
        $side_arr['total'] = $side_arr['sell_price'] * $side_arr['qty'];
        $side_arr['cost_total'] = $side_arr['base_price'] * $side_arr['qty'];
        $side_arr['stock_id'] = !empty($side['stock_id']) ? $side['stock_id'] : 0;
        $side_arr['unit_id'] = !empty($side['unit_id']) ? $side['unit_id'] : 0;
        $side_arr['unit_name'] = !empty($side['unit']['short']) ? $side['unit']['short'] : null;
        $side_arr['hub_id'] = $side['hub_id'];
        $side_arr['inc'] = !empty($side['inc']) ? true : false;
        $side_arr['dec'] = !empty($side['dec']) ? true : false;
        $side_arr['lang'] = lang_option();
        return $side_arr;
    }

    private function __body_items($items)
    {
        foreach ($items as $k => $v) {
            if (!empty($v['sides'])) {
                $sets = $this->__organise_sides($v['sides']);
                $items[$k]['sets'] = $sets;
            }
        }
        return $items;
    }
    private function __organise_sides($sides)
    {
        $sets = [];
        foreach ($sides as $value) {
            $sets[$value['group_id']][] = $value;
        }
        $arr = [];
        foreach ($sets as $value) {
            $a = [];
            $a['sides'] = $value;
            $a['group_name'] = $value[0]['group_name'];
            $a['lang'] = lang_option();
            $arr[] = $a;
        }
        return $arr;
    }
    private function __calculate_multi_discount($item, $plan)
    {
        $discount = 0;
        if ($plan['buy'] == 1) {
            if ($item['qty'] >= 2) {
                if ($plan['get'] == 1) {
                    $mask = floor($item['qty'] / 2);
                    $discount = $mask * $item['sell_price'];
                } else if ($plan['get'] < 1) $discount = $item['sell_price'] * $plan['get'];
            }
        }
        if ($plan['buy'] >= 2) {
            if ($item['qty'] >= ($plan['buy'] + $plan['get'])) {
                $mask = floor($item['qty'] / $plan['buy']) * $plan['get'];
                $discount = $item['sell_price'] * $mask;
            }
        }
        return $discount;
    }
    private function __delete_free_item_on_dissatisfaction()
    {
        $cart_items = $this->get_cart();
        $query = $this->item_free_query();
        foreach ($cart_items as $line => $item) {
            if (empty($query))  if ($item['is_free']) $this->remove($line);
            else if ($query['id'] != $item['free_rule']) $this->remove($line);
        }
    }
    private function __content()
    {
        $data['lang'] = lang_option();
        if ($this->get_order_type()) {
            /*!------CONDITIONAL BODY------*/
            $data['order_type'] = $this->get_order_type();
            $data['services'] = $this->__get_services();
            $data['service_counter'] = count($data['services']);
            foreach ($data['services'] as $k => $v) $data['services'][$k]['class'] = $v['value'] === $data['order_type'] ? " w3-text-bold w3-text-red " : "";
            if ($data['order_type'] === "delivery") {
                $data['delivery_popup'] = empty($this->get_delivery_area()) ? true : false;
                if (!empty($this->get_delivery_area())) $data['delivery_area'] = $this->Sites->area_dropdown($this->get_delivery_area());
                if (!empty($this->get_delivery_home())) {
                    $data['delivery_home'] = $this->Customeraddresses->get($this->get_delivery_home(), true, false, ['id', 'house']);
                    $data['delivery_home']->house = $this->Customeraddresses->decode($data['delivery_home']->house);
                }
                $data['delivery_charge'] = $this->delivery_charge();
                $data['minimum_delivery'] = $this->minimum_delivery_block();
                $data['free_delivery_warning'] = $this->free_delivery_warning();
                $data['delivery_home_popup'] = empty($this->Cart->get_delivery_home()) && !empty($this->session->userdata('customerId')) ? true : false;
            }
            $data['minimum_promo_order'] = $this->minimum_promo_discount_block();
            $data['limit'] = config_item('order_max_day');
            /*!------CORE BODY------*/
            $data['items'] = $this->__body_items($this->get_cart());
            $data['show_unit'] = config_item('show_unit') == 'yes' ? true : false;
            $data['subtotal'] = $this->subtotal();
            $data['service_charge'] = $this->seervice_charge();
            $data['tax'] = $this->tax();
            $data['discount'] = $this->discount();
            $data['discount_details'] = ['plan_wise' => $this->get_plan_discount(), 'promo_wise' => $this->get_promo_discount(), 'bogo_wise' => $this->get_multi_discount(), 'default_wise' => 0, 'item_wise' => $this->get_item_discount(), 'mannual' => 0];
            if ("order_excluded" === config_item('admin_fee_per'))  $data['admin_fee'] = $this->admin_fee();
            $data['total'] = rounding($this->total());
            $data['rounding'] = abs($this->rounding());
        } else {
            $data['services'] = $this->__get_services();
            $data['service_counter'] = count($data['services']);
            if (count($data['services']) === 1) {
                $this->set_order_type($data['services'][0]['value']);
                $data['services'][0]['class'] = " w3-text-bold w3-text-red ";
            } else $data['service_popup'] = true;
        }
        $data['receive_time_popup'] = empty($this->Cart->get_order_receive_time()) ? true : false;
        return $data;
    }
    //All PIBLIC METHODS BLOCK
    public function set_preorder_permission()
    {
        $this->session->set_userdata('preorder_permission', true);
    }
    public function get_preorder_permission()
    {
        return $this->session->get_userdata('preorder_permission');
    }
    public function clear_preorder_permission()
    {
        $this->session->unset_userdata('preorder_permission');
    }
    public function set_order_type($type)
    {
        if (!empty(config_item($type)) && $type != $this->get_order_type())
            $this->session->set_userdata('order_type', $type);
    }
    public function get_order_type()
    {
        return $this->session->userdata('order_type');
    }
    public function clear_order_type()
    {
        $this->session->unset_userdata('order_type');
    }
    public function set_delivery_area($area)
    {
        $this->session->set_userdata('delivery_area', $area);
        if ($this->get_order_type() === 'delivery') {
            $plans = $this->Sites->delivery_plan(['web' => true], $area);
            if (!empty($plans)) {
                $min_plan = $plans[count($plans) - 1];
                $this->set_minimum_delivery(objectToArray($min_plan));
            }
        }
        $this->Cookie_Model->set_delivery_area();
    }
    public function get_delivery_area()
    {
        return $this->session->userdata('delivery_area');
    }
    public function clear_delivery_area()
    {
        $this->session->unset_userdata('delivery_area');
    }
    public function set_delivery_home($home)
    {
        $this->session->set_userdata('delivery_home', $home);
        $this->Cookie_Model->set_delivery_home();
    }
    public function get_delivery_home()
    {
        return $this->session->userdata('delivery_home');
    }
    public function clear_delivery_home()
    {
        $this->session->unset_userdata('delivery_home');
    }
    public function set_order_receive_date($date)
    {
        $this->session->set_userdata('order_receive_date', $date);
    }
    public function get_order_receive_date()
    {
        return $this->session->userdata('order_receive_date');
    }
    public function clear_order_receive_date()
    {
        return $this->session->unset_userdata('order_receive_date');
    }
    public function set_order_receive_time($time)
    {
        $this->session->set_userdata('order_receive_time', $time);
    }
    public function get_order_receive_time()
    {
        return $this->session->userdata('order_receive_time');
    }
    public function clear_order_receive_time()
    {
        return $this->session->unset_userdata('order_receive_time');
    }
    public function set_delivery_plan($data)
    {
        $this->session->set_userdata('delivery_plan', $data);
    }
    public function get_delivery_plan()
    {
        return $this->session->userdata('delivery_plan');
    }
    public function clear_delivery_plan()
    {
        $this->session->unset_userdata('delivery_plan');
    }
    public function set_minimum_delivery($plan)
    {
        return $this->session->set_userdata('minimum_delivery', $plan);
    }
    public function get_minimum_delivery()
    {
        return $this->session->userdata('minimum_delivery');
    }
    public function clear_minimum_delivery()
    {
        return $this->session->unset_userdata('minimum_delivery');
    }
    public function minimum_delivery_block()
    {
        $plan = $this->get_minimum_delivery();
        $subtotal = $this->subtotal();
        if (!empty($plan)) {
            $diff = ((float) $plan['base'] - (float) $subtotal);
            if ($diff > 0) {
                $currency = currency($diff);
                return ['status' => true, 'subtotal' => $subtotal,  'diff' => $diff,  'message' => sprintf(lang('spend_more_option_to_get_delivery'), $currency)];
            }
        }
        return false;
    }
    public function free_delivery_warning()
    {
        if (empty($this->minimum_delivery_block())) {
            $plan = $this->get_delivery_plan();
            if (!empty($plan)) {
                $diff = $plan['free_on'] - $this->subtotal();
                if ($diff > 0) {
                    $currency = currency($diff);
                    return ['status' => true, 'message' => sprintf(lang('spend_more_option_to_get_free_delivery'), $currency)];
                }
            }
        }
        return false;
    }
    public function set_active_discount_plan($plan)
    {
        $this->session->set_userdata('active_discount_plan', $plan);
    }
    public function get_active_discount_plan()
    {
        return $this->session->userdata('active_discount_plan');
    }
    public function clear_active_discount_plan()
    {
        $this->session->unset_userdata('active_discount_plan');
    }
    public function set_promo_discount_plan($plan)
    {

        $this->session->set_userdata('promo_discount_plan', $plan);
    }
    public function get_promo_discount_plan()
    {
        return $this->session->userdata('promo_discount_plan');
    }
    public function clear_promo_discount_plan()
    {
        $this->session->unset_userdata('promo_discount_plan');
    }
    public function minimum_promo_discount_block()
    {
        $promo = $this->get_promo_discount_plan();
        if (!empty($promo)) {
            $subtotal = $this->subtotal();
            $diff = ((float) $promo['min_order'] - (float) $subtotal);
            if ($diff > 0) {
                $currency = currency($diff);
                return ['status' => true, 'subtotal' => $subtotal,  'diff' => $diff,  'message' => sprintf(lang('spend_more_option_to_get_promo_discount'), $currency)];
            }
        }
        return false;
    }
    public function get_plan_discount($service = 'delivery')
    {
        $plan_discount_total = 0;
        $promo = $this->get_promo_discount_plan();
        if (!empty($promo) && $promo['action'] === 'overlap') return 0;
        $plans = $this->Sales->get_discount_plans($this->get_order_type() ? $this->get_order_type() : $service, 'web');
        $plan = null;
        foreach ($plans as $p) {
            if ($this->subtotal() >= $p->min_order) $plan = $p;
        }

        if (!empty($plan)) {
            $plan = objectToArray($plan);
            if ($plan['func'] === 'fixed') {
                $plan_discount_total += $plan['value'];
            } else {
                $cart_items = $this->get_cart();
                $cart_items = array_filter($cart_items, function ($item, $index) {
                    return $item['discount_rule']['discount_by'] === 'plan';
                }, ARRAY_FILTER_USE_BOTH);
                foreach ($cart_items as $item) {
                    if (!empty($plan[$item['base_name']])) {
                        $plan_discount_total += ($item['sell_price'] * $item['qty']) * ($plan['value'] / 100);
                    }
                }
            }
            $this->set_active_discount_plan($plan);
        }
        return $plan_discount_total;
    }
    public function get_item_discount()
    {
        $promo = $this->get_promo_discount_plan();
        if (!empty($promo) && $promo['action'] === 'overlap') return 0;
        $cart_items = $this->get_cart();
        $cart_items = array_filter($cart_items, function ($item, $index) {
            return $item['discount_rule']['discount_by'] === 'this' || $item['discount_rule']['discount_by'] === 'default' || $item['discount_rule']['discount_by'] === 'parent';
        }, ARRAY_FILTER_USE_BOTH);
        $item_discount_total = 0;
        foreach ($cart_items as $k => $item) {
            if ($item['discount_rule']['value'] > 0) {
                if ($item['discount_rule']['func'] === 'fixed') $item_discount_total += $item['discount_rule']['value'];
                else  $item_discount_total += ($item['sell_price'] * $item['qty']) * ($item['discount_rule']['value'] / 100);
            }
        }
        return $item_discount_total;
    }
    public function get_multi_discount($service = 'delivery')
    {
        $multi_discount = 0;
        $plans = $this->Sales->get_bogo_plans($this->get_order_type() ? $this->get_order_type() : $service, null, 'web');
        if (!empty($plans)) {
            $plans = objectToArray($plans);
            $cart_items = $this->get_cart();
            $bogo_items = array_filter($cart_items, function ($item, $index) {
                return $item['discount_rule']['discount_by'] === 'bogo';
            }, ARRAY_FILTER_USE_BOTH);
            foreach ($bogo_items as $b_item) {
                foreach ($plans as $plan) {
                    if (($b_item['item_id'] == $plan['item_id']) && ($b_item['option_id'] == $plan['option_id'])) {
                        $multi_discount += $this->__calculate_multi_discount($b_item, $plan);
                    }
                }
            }
        }
        return $multi_discount;
    }
    public function get_promo_discount()
    {
        $promo_discount = 0;
        $promo = $this->get_promo_discount_plan();
        if (!empty($promo) && $this->subtotal() >= $promo['min_order']) {
            if ($promo['func'] === 'fixed') {
                $promo_discount += $promo['value'];
            } else {
                $cart_items = $this->get_cart();
                foreach ($cart_items as $item) {
                    if (!empty($promo[$item['base_name']]) && $item['discount_rule']['discount_by'] !== 'bogo') {
                        $promo_discount += ($item['sell_price'] * $item['qty']) * ($promo['value'] / 100);
                    }
                }
            }
        }
        return $promo_discount;
    }
    public function item_free_query($service = 'delivery')
    {
        $plans = $this->Sales->get_free_plans($this->get_order_type() ? $this->get_order_type() : $service, 'web');
        $active_plan = null;
        if (!empty($plans)) {
            $plans = objectToArray($plans);
            for ($i = 0; $i < count($plans); $i++) {
                if ($this->subtotal() >= $plans[$i]['min_order']) {
                    $active_plan = $plans[$i];
                    break;
                }
            }
        }
        if (!empty($active_plan)) {
            $cart_items = $this->get_cart();
            $free_items = [];
            $rule_items = [];
            foreach ($cart_items as $item) if (!empty($item['is_free'])) $free_items[] = $item;
            foreach ($free_items as $item) if ($item['free_rule'] == $active_plan['id']) $rule_items[] = $item;
            $item_rule_free_qty = 0;
            foreach ($free_items as $item) $item_rule_free_qty = $item['qty'];
            if ($active_plan['quantity'] > $item_rule_free_qty) $active_plan['is_satisfied'] = false;
            else $active_plan['is_satisfied'] = true;
            $active_plan['lang'] = lang_option();
        }
        return $active_plan;
    }

    public function admin_fee()
    {
        $admin_fee = 0;
        if ("order_excluded" === config_item('admin_fee_per') || "order_included" ===  config_item('admin_fee_per')) {
            if (config_item('admin_fee_func') === 'fixed') $fee = (float) config_item('admin_fee');
            else $admin_fee = $this->subtotal() * ((float) config_item('admin_fee') / 100);
        }
        return $admin_fee;
    }
    public function set_checkout_info($data)
    {
        $this->session->set_userdata('checkout_info', $data);
    }
    public function get_checkout_info()
    {
        return $this->session->userdata('checkout_info');
    }
    public function clear_checkout_info()
    {
        $this->session->unset_userdata('checkout_info');
    }
    /**CART DISPLAY AND OPERATION */
    public function body()
    {
        $data['lang'] = lang_option();
        if (is_opened()) {
            if (!temp_closed())
                $data = $this->__content();
            else {
                $data['set_temp_closed_popup'] = true;
                $duration = date('h:i A', strtotime(config_item('opening_hour')->closed_till));
                $data['temp_closed_popup_head'] = sprintf(lang('option_closed'), lang('temporary'));
                $data['temp_closed_popup_text'] = sprintf(lang('temp_closed_text'), $duration);
            }
        } else {
            if (in_preorder())
                $data = $this->__content();
            else {
                if (has_preorder()) {
                    $data['set_preorder_popup'] = true;
                    $data['preorder_popup_head'] = sprintf(lang('option_permission'), lang('preorder'));
                    $data['preorder_popup_text'] = lang('preorder_popup_text');
                    $data['click_here_for_preorder'] = sprintf(lang('click_here_for_option'), lang('preorder'));
                    $data['view_opening_hours'] = sprintf(lang('view_option'), lang('opening_hours'));
                } else {
                    $data['set_closed_popup'] = true;
                    $data['closed_popup_head'] = sprintf(lang('option_closed'), lang('store'));
                    $data['closed_popup_text'] = lang('store_closed_text');
                    $data['view_opening_hours'] = sprintf(lang('view_option'), lang('opening_hours'));
                }
            }
        }
        $data['free_item_qyery'] = $this->item_free_query();
        $data['allow_checkout'] = $this->allow_checkout();
        $data['page_slug'] = get_page_slug();
        return $data;
    }
    public function add_item($array)
    {
        if ($this->get_order_type()) {
            if ($this->get_order_type() === "delivery" && empty($this->get_delivery_area())) return;
            if ($array['role'] === 'fixed_set') $array = $this->__set_fixed_setoption($array);
            if ($array['role'] === 'custom_set') $array = $this->__set_custom_setoption($array);
            if ($array['role'] === 'normal' && !empty($array['has_top']) && !empty($array['sides'])) $array = $this->__set_item_tops($array);
            $line = $this->__search($array);
            if (!$line) {
                $obj = !empty($array['option_id']) ? $this->Sites->item($array['option_id'], true) : $this->Sites->item($array['item_id'], false);
                $row['hub_id'] = $obj->hub_id;
                $row['sale_id'] = null;
                $row['cart_id'] = strval(time() * 1000);
                $row['name'] = !empty($obj->item_id) ? (object)['en' => $obj->name->en . ' ' . $obj->item->name->en, 'bn' => $obj->name->bn . ' ' . $obj->item->name->bn] : $obj->name;
                $row['sides'] = !empty($array['sides']) ? $this->__add_sides($array['sides']) : [];
                $row['qty'] = !empty($array['qty']) ? $array['qty'] : 1;
                $row['base_price'] = $obj->base_price;
                $row['in_price'] = $obj->in_price;
                $row['out_price'] = !empty($array['plan']) ? 0 : $obj->out_price;
                $row['sell_price'] = $row['out_price'];
                $row['base_id'] = $obj->base_id;
                $row['base_name'] = ['en' => $obj->base->name->en, 'bn' => $obj->base->name->en, 'value' => $obj->base->value];  //$obj->base->name;
                $row['category_id'] = $obj->category_id;
                $row['category_name'] = ['en' => $obj->category->name->en, 'bn' =>  $obj->category->name->bn, 'type' => $obj->category->food_type];
                $row['item_id'] = !empty($obj->item_id) ? $obj->item_id : $obj->id;
                $row['item_name'] = !empty($obj->item_id) ? $obj->item->name : $obj->name;
                $row['option_id'] = !empty($obj->item_id) ? $obj->id : 0;
                $row['option_name'] = !empty($obj->item_id) ? $obj->name : (object)['en' => '', 'bn' => ''];
                $row['course_id'] = $obj->course_id;
                $row['course_name'] = $obj->course->name;
                $row['unit_id'] = $obj->unit_id;
                $row['unit_name'] = $obj->unit->short;
                $row['stock_id'] = $obj->stock_id;
                $row['tax_rule'] = ['id' => $obj->tax->id, 'value' => $obj->tax->value, 'func' => 'percent', 'tax_by' => $obj->tax_by];
                if ($obj->tax_by === 'parent') {
                    if (!empty($obj->item_id)) $row['tax_rule'] = ['id' => $obj->item->tax->id, 'value' => $obj->item->tax->value, 'func' => 'percent', 'tax_by' => $obj->tax_by];
                    else $row['tax_rule'] = ['id' => $obj->category->tax->id, 'value' => $obj->category->tax->value, 'func' => 'percent', 'tax_by' => $obj->tax_by];
                }
                $row['discount_rule'] = ['id' => $obj->discount->id, 'value' => $obj->discount->value, 'func' => $obj->discount->func, 'discount_by' => $obj->discount_by];
                if ($obj->discount_by === 'parent') {
                    if (!empty($obj->item_id)) $row['discount_rule'] = ['id' => $obj->item->discount->id, 'value' => $obj->item->discount->value, 'func' => $obj->item->discount->func, 'discount_by' => $obj->discount_by];
                    else $row['discount_rule'] = ['id' => $obj->category->discount->id, 'value' => $obj->category->discount->value, 'func' => $obj->category->discount->func, 'discount_by' => $obj->discount_by];
                }
                $row['is_free'] = !empty($array['plan']) ? true : 0;
                $row['free_rule'] = !empty($array['plan']) ? $array['plan'] : 0;
                $row['new_qty'] = $row['qty'];
                $row['is_sent'] = 0;
                $row['active'] = 'w3-text-theme';
                $row['created_at'] = time() * 1000;
                $row['updated_at'] = time() * 1000;
                $row['cost_total'] = $this->__cost_total($row['base_price'], $row['qty'], $row['sides']);
                $row['item_total'] = $this->__sell_total($row['sell_price'], $row['qty'], $row['sides']);
                $row['note'] = !empty($array['note']) ? $array['note'] : '';
                $this->__push($row);
            } else {
                $array['line'] = $line;
                $this->plus($array);
            }
        }
    }
    public function plus($item)
    {

        $cart_items = $this->get_cart();
        if ($cart_items[$item['line']]['free_rule'] <= 0) {
            $cart_items[$item['line']]['qty'] += !empty($item['qty']) ? $item['qty'] : 1;
            $cart_items[$item['line']]['new_qty'] = $cart_items[$item['line']]['qty'];
            $cart_items[$item['line']]['item_total'] = $this->__sell_total($cart_items[$item['line']]['sell_price'], $cart_items[$item['line']]['qty'], $cart_items[$item['line']]['sides']);
            $cart_items[$item['line']]['cost_total'] = $this->__cost_total($cart_items[$item['line']]['base_price'], $cart_items[$item['line']]['qty'], $cart_items[$item['line']]['sides']);
            $this->set_cart($cart_items);
            $this->__set_line($item['line']);
        }
    }
    public function minus($line)
    {
        $cart_items = $this->get_cart();
        $cart_items[$line]['qty'] -= 1;
        $cart_items[$line]['new_qty'] = $cart_items[$line]['qty'];
        if ($cart_items[$line]['qty'] > 0) {
            $cart_items[$line]['item_total'] = $this->__sell_total($cart_items[$line]['sell_price'], $cart_items[$line]['qty'], $cart_items[$line]['sides']);
            $cart_items[$line]['cost_total'] = $this->__cost_total($cart_items[$line]['base_price'], $cart_items[$line]['qty'], $cart_items[$line]['sides']);
            $this->set_cart($cart_items);
            $this->__set_line($line);
        } else {
            unset($cart_items[$line]);
            $this->set_cart($cart_items);
        }
        $this->__delete_free_item_on_dissatisfaction();
    }
    public function remove($line)
    {
        $cart_items = $this->get_cart();
        unset($cart_items[$line]);
        $this->set_cart($cart_items);
        $this->__delete_free_item_on_dissatisfaction();
        return $line;
    }
    public function side_plus($line)
    {
        $cart_items = $this->get_cart();
        $item = $cart_items[$line['line']];
        $sides = $item['sides'];
        foreach ($sides as $k => $side) {
            if ($side['group_id'] == $line['group_id']) {
                $found = false;
                if (!empty($line['topping_id']) && $line['topping_id'] == $side['topping_id'])  $found = true;
                else if (!empty($line['modify_id']) && $line['modify_id'] == $side['modify_id']) $found = true;
                else if (!empty($line['option_id']) && $line['option_id'] == $side['option_id']) $found = true;
                else if (!empty($line['item_id']) && $line['item_id'] == $side['item_id']) $found = true;
                else $found = false;
                if ($found) {
                    $sides[$k]['qty'] += 1;
                    $sides[$k]['new_qty'] = $sides[$k]['qty'];
                    $sides[$k]['total'] = $sides[$k]['qty'] * $sides[$k]['sell_price'];
                    $sides[$k]['cost_total'] = $sides[$k]['qty'] * $sides[$k]['base_price'];
                }
            }
        }
        $item['sides'] = $sides;
        $item['item_total'] = $this->__sell_total($item['sell_price'], $item['qty'], $item['sides']);
        $item['cost_total'] = $this->__cost_total($item['base_price'], $item['qty'], $item['sides']);
        $cart_items[$line['line']] = $item;
        $this->set_cart($cart_items);
        $this->__set_line($line['line']);
    }
    public function side_minus($line)
    {
        $cart_items = $this->get_cart();
        $item = $cart_items[$line['line']];
        $sides = $item['sides'];
        foreach ($sides as $k => $side) {
            if ($side['group_id'] == $line['group_id']) {
                $found = false;
                if (!empty($line['topping_id']) && $line['topping_id'] == $side['topping_id'])  $found = true;
                else if (!empty($line['modify_id']) && $line['modify_id'] == $side['modify_id']) $found = true;
                else if (!empty($line['option_id']) && $line['option_id'] == $side['option_id']) $found = true;
                else if (!empty($line['item_id']) && $line['item_id'] == $side['item_id']) $found = true;
                else $found = false;
                if ($found) {
                    $sides[$k]['qty'] -= 1;
                    if ($sides[$k]['qty'] > 0 && empty($line['modify_id'])) {
                        $sides[$k]['new_qty'] = $sides[$k]['qty'];
                        $sides[$k]['total'] = $sides[$k]['qty'] * $sides[$k]['sell_price'];
                        $sides[$k]['cost_total'] = $sides[$k]['qty'] * $sides[$k]['base_price'];
                    } else {
                        unset($sides[$k]);
                    }
                }
            }
        }
        $item['sides'] = $sides;
        $item['item_total'] = $this->__sell_total($item['sell_price'], $item['qty'], $item['sides']);
        $item['cost_total'] = $this->__cost_total($item['base_price'], $item['qty'], $item['sides']);
        $cart_items[$line['line']] = $item;
        $this->set_cart($cart_items);
        $this->__set_line($line['line']);
    }
    public function subtotal()
    {
        $subtotal = 0;
        $cart_items = $this->get_cart();
        foreach ($cart_items as $line) {
            $subtotal += $line['item_total'];
        }
        return $subtotal;
    }
    public function discount()
    {
        $discount = 0;
        $discount += $this->get_item_discount();
        $discount += $this->get_plan_discount();
        $discount += $this->get_multi_discount();
        $discount += $this->get_promo_discount();
        return $discount;
    }
    public function tax()
    {
        $tax = 0;
        $cart_items = $this->get_cart();
        foreach ($cart_items as $line) {
            $tax += $line['item_total'] * ($line['tax_rule']['value'] / 100);
        }
        return $tax;
    }
    public function delivery_charge()
    {
        if ($this->get_order_type() !== 'delivery' || $this->subtotal() <= 0) return 0;
        $charge = 0;
        $plans = $this->Sites->delivery_plan(['web' => true], $this->get_delivery_area());
        $subtotal = $this->subtotal();
        if (!empty($plans)) {
            $charge = $plans[count($plans) - 1]->charge;
            if (empty($this->get_minimum_delivery())) $this->set_minimum_delivery(objectToArray($plans[count($plans) - 1]));
            $planActive = $plans[count($plans) - 1];
            foreach ($plans as $plan) {
                if ($subtotal >= $plan->base) {
                    $charge = $plan->charge;
                    if ($plan->free_on > 0 && $subtotal >= $plan->free_on) $charge = 0;
                    $planActive = $plan;
                    break;
                }
            }
            $this->set_delivery_plan(objectToArray($planActive));
        } else {
            $charge = config_item('delivery_charge');
            $this->clear_minimum_delivery();
            $this->clear_delivery_plan();
        }
        return $charge;
    }
    public function seervice_charge()
    {
        $service_charge = 0;
        if ($this->get_order_type()) {
            $service = config_item($this->get_order_type());
            if (!empty($service)) {
                $service_charge += $service->func == 'fixed' ? $service->charge : '';
                $service_charge += $this->subtotal() * ($service->charge / 100);
            }
        }
        return $service_charge;
    }
    public function total()
    {
        $total = 0;
        if ($this->subtotal() > 0) {
            $total += $this->subtotal();
            $total += $this->tax();
            $total += ("order_excluded" === config_item('admin_fee_per')) ? $this->admin_fee() : 0;
            $total += $this->get_order_type() === 'delivery' ? $this->delivery_charge() : 0;
            $total += $this->seervice_charge();
            $total -= $this->discount();
        }
        return $total;
    }
    public function cost_total()
    {
        $cost_total = 0;
        if ($this->subtotal() > 0) {
            $cart_items = $this->get_cart();
            foreach ($cart_items as $line) $cost_total += $line['cost_total'];
            $cost_total += $this->tax();
        }
        return $cost_total;
    }
    public function rounding()
    {
        if ($this->subtotal() > 0) {
            return $this->total() - rounding($this->total());
        } else return 0;
    }
    public function reshuffle($store_id)
    {
        $cart_items = $this->get_cart();
        foreach ($cart_items as $line => $arr) {
            $obj = null;
            if ($arr['option_id'] > 0) {
                $entity = $this->Prodoptions->get($arr['option_id'], true);
                $entity->edit = false;
                if (!empty($entity)) $obj = $this->Prodoptions->build_entity($entity, $store_id);
            } else {
                $entity = $this->Proditems->get($arr['item_id'], true);
                $entity->edit = false;
                if (!empty($entity)) $obj = $this->Proditems->build_entity($entity, $store_id);
            }
            if (!empty($obj)) {
                $cart_items[$line]['base_price'] = $obj->base_price;
                $cart_items[$line]['in_price'] = $obj->in_price;
                $cart_items[$line]['out_price'] = $obj->out_price;
                $cart_items[$line]['sell_price'] = $obj->out_price;
                $cart_items[$line]['cost_total'] = $this->__cost_total($cart_items[$line]['base_price'], $arr['qty'], $arr['sides']);
                $cart_items[$line]['item_total'] = $this->__sell_total($cart_items[$line]['sell_price'], $arr['qty'], $arr['sides']);
            }
        }
        $this->set_cart($cart_items);
    }
    public function get_cart()
    {
        $cart = $this->session->userdata('cart');
        return empty($cart) ? [] : $cart;
    }
    public function set_cart($data)
    {
        $this->session->set_userdata('cart', $data);
    }
    public function allow_checkout()
    {
        return empty($this->minimum_delivery_block()) && !empty($this->subtotal()) && !empty($this->total());
    }
    public function clear_cart()
    {
        $this->session->unset_userdata('cart');
    }
    public function destroy()
    {
        $this->clear_active_discount_plan();
        $this->clear_promo_discount_plan();
        $this->clear_cart();
        $this->clear_delivery_home();
        $this->clear_delivery_area();
        $this->clear_delivery_plan();
        $this->clear_minimum_delivery();
        $this->clear_order_type();
        $this->clear_order_receive_date();
        $this->clear_order_receive_time();
        $this->clear_preorder_permission();
        $this->clear_checkout_info();
    }
}
