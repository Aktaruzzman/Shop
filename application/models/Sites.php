
<?php

class Sites extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Userhubs');
        $this->load->model('Customers');
        $this->load->model('Customeraddresses');
        $this->load->model('Subscribers');
        $this->load->model('Cfgunits');
        $this->load->model('Prodcats');
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodcustsets');
        $this->load->model('Prodcustsetoptions');
        $this->load->model('Stockdefines');
        $this->load->model('Stockentries');
        $this->load->model('Stocklocations');
        $this->load->model('Stockhubentries');
        $this->load->model('Stockhubprices');
        $this->load->model('Cfgdelplans');
        $this->load->model('Cart');
        $this->load->model('Geoareas');
        $this->load->model('Cfgdiscounts');
    }
    public function subscribe()
    {
        if ($this->Subscribers->validate()) {
            $this->db->trans_start();
            $entity = $this->Subscribers->entity($this->input->post());
            $this->Subscribers->save($entity);
            $this->db->trans_complete();
            $results['status']  = $this->db->trans_status();
            $results['message'] = $results['status'] ? lang('thank_for_subscription') : lang('saved_failed_msg');
            return $this->encode($results);
        } else {
            $phone = $this->Subscribers->get_by(['phone' => $this->input->post('phone')], true, true);
            if (!empty($phone)) {
                $entity = $this->Subscribers->entity($this->input->post());
                if (empty($entity['email'])) {
                    unset($entity['email']);
                }
                $this->Subscribers->save($entity, $phone->id);
                return $this->encode(['status' => true, 'message' => lang('thank_for_subscription')]);
            }
            return $this->encode(['status' => false, 'entity' => $this->input->post(), 'message' => $this->form_validation->error_array()]);
        }
    }
    public function products($store_branch = 0, $just_set = false)
    {
        if ($store_branch > 0) {
            if ($this->session->set_userdata('store_branch') != $store_branch) {
                $this->session->set_userdata('store_branch', $store_branch);
                $this->Cart->set_delivery_area($this->Userhubs->get($store_branch, true, true)->area_id);
                $this->Cookie_Model->set_store();
                if (!empty($this->Cart->get_cart())) $this->Cart->reshuffle($store_branch);
            }
        } else {
            if (empty($this->Cookie_Model->get_store())) {
                if (!empty($this->session->userdata('store_branch'))) {
                    if (config_item('store_type') === 'multiple') {
                        $this->Cookie_Model->set_store();
                    } else {
                        $default = $this->Userhubs->get(['default' => true], true, true)->id;
                        if ($default != $this->session->userdata('store_branch')) {
                            $this->session->set_userdata('store_branch', $default);
                            $this->Cookie_Model->set_store();
                            $this->Cart->set_delivery_area(config_item('store_area_id'));
                        }
                    }
                } else {
                    $default = $this->Userhubs->get(['default' => true], true, true);
                    $this->session->set_userdata('store_branch', $default->id);
                    $this->Cookie_Model->set_store();
                    config_item('store_type') === 'multiple' ? $this->Cart->set_delivery_area($default->area_id) : $this->Cart->set_delivery_area(config_item('store_area_id'));
                }
                if (!empty($this->Cart->get_cart())) $this->Cart->reshuffle($store_branch);
            }
        }
        if ($just_set) return true;
        return $this->categories();
    }
    public function categories()
    {
        $lang = lang_option();
        $categorries = $this->Prodcats->get(['show_out' => true, 'status' => 'active'], false, false, ['id', 'name', 'description'], 'order', 'ASC');
        foreach ($categorries as $key => $value) {
            $categorries[$key]->name = $this->Prodcats->decode($value->name, true);
            $categorries[$key]->description = $this->Prodcats->decode($value->description, true);
            $categorries[$key]->items = $this->items_by($value->id);
            $categorries[$key]->lang = $lang;
        }
        return $categorries;
    }
    public function items_by($category_id)
    {
        $items = $this->Proditems->get(['category_id' => $category_id, 'show_out' => true, 'status' => 'active'], false, false, ['id', 'category_id', 'name', 'description', 'unit_id', 'out_price', 'role', 'qty_lower', 'qty_upper', 'qty_step', 'photo', 'thumb', 'stock_id', 'has_top', 'discount_id', 'discount_by', 'tax_id', 'tax_by'], 'order', 'ASC');
        foreach ($items as $key => $value) {
            $items[$key] = $this->item_entity($value);
        }
        return $items;
    }
    public function item_entity($entity)
    {
        $store_branch = $this->session->userdata('store_branch');
        $lang = lang_option();
        $entity->name = $this->Proditems->decode($entity->name, true);
        $entity->description = $this->Proditems->decode($entity->description, true);
        $entity->unit = $this->Cfgunits->decode($this->Cfgunits->get($entity->unit_id, true, true, ['short'])->short, true);
        $entity->photos['full'] = !empty($entity->photo) ? site_url('/uploads/product/item/' . $entity->photo) : './img/no-image.png';
        $entity->photos['thumb'] = !empty($entity->thumb) ? site_url('/uploads/product/item/thumbnail/' . $entity->thumb) : './img/no-image.png';
        $entity->stock = $this->Stockdefines->get($entity->stock_id, true, true, ['id', 'maintain_stock', 'warn_below_qty', 'stop_sale_qty']);
        $entity->stock->balance = $this->Stockentries->get(['stock_id' => $entity->stock_id], true, true)->balance;
        if (empty($entity->item_id)) $entity->options = $this->options_by($entity->id, $store_branch);
        $entity->lang = $lang;
        $entity->show_unit = config_item('show_unit') == "yes" ? true : false;
        $entity->allow_item_comment = config_item('allow_item_comment');
        $entity->hub_id = $store_branch;
        if (config_item('store_type') === "multiple" && !empty($entity->hub_id)) {
            $hubentry = $this->Stockhubentries->get(['stock_id' => $entity->stock->id, 'hub_id' =>   $entity->hub_id], true, true);
            if (!empty($hubentry)) $entity->stock->balance = $hubentry->balance;
            else $entity->stock->balance = 0;
            $hubprice = $this->Stockhubprices->get(['stock_id' =>  $entity->stock->id, 'hub_id' =>   $entity->hub_id], true, true);
            if (!empty($hubprice)) {
                $entity->base_price = $hubprice->base_price;
                $entity->in_price = $hubprice->in_price;
                $entity->out_price = $hubprice->out_price;
            }
        }
        $entity->discount = $this->Cfgdiscounts->get($entity->discount_id, true, true, ['id', 'value', 'func', 'default']);
        if ($entity->discount_by === 'bogo') $entity->bogo = $this->Discountmultis->get_by(['name_id' => '0_' . $entity->id], true);
        $entity->tax = $this->Cfgtaxes->get($entity->tax_id, true, true, ['id', 'value']);
        return $entity;
    }
    public function options_by($item_id, $store_branch = 1)
    {
        $items = $this->Prodoptions->get(['item_id' => $item_id, 'show_out' => true, 'status' => 'active'], false, false, ['id', 'category_id', 'item_id', 'name', 'description', 'unit_id', 'out_price', 'role', 'qty_lower', 'qty_upper', 'qty_step', 'photo', 'thumb', 'stock_id', 'has_top', 'discount_id', 'discount_by', 'tax_id', 'tax_by'], 'order', 'ASC');
        foreach ($items as $key => $value) {
            $items[$key] = $this->item_entity($value);
            $items[$key]->item_name = $this->Prodoptions->decode($this->Proditems->get($value->item_id)->name);
        }
        return $items;
    }
    public function item_by_search($input)
    {
        $search = str_replace(array('-', '_', ',', '+'), array(' ', ' ', ' ', ' '), filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS));
        $items = $this->Proditems->get_by(['status' => 'active'], false, null, null, 0, trim($search), ['name_en', 'name_bn', 'description_en', 'description_bn']);
        foreach ($items as $key => $value) {
            $items[$key] = $this->item_entity($value);
            $items[$key]->options = $this->options_by($value->id);
        }
        if (empty($items)) {
            $items = $this->Prodoptions->get_by(['status' => 'active'], false, null, null, 0, trim($search), ['name_en', 'name_bn', 'description_en', 'description_bn']);
            foreach ($items as $key => $value) {
                $items[$key] = $this->item_entity($value);
                $items[$key]->item_name = $this->Prodoptions->decode($this->Proditems->get($value->item_id)->name);
            }
        }
        return $items;
    }
    public  function item($id, $opt = false)
    {
        $obj = null;
        if ($opt) {
            $entity = $this->Prodoptions->get($id, true);
            $entity->edit = false;
            if (!empty($entity)) $obj = $this->Prodoptions->build_entity($entity, $this->session->userdata('store_branch'));
        } else {
            $entity = $this->Proditems->get($id, true);
            $entity->edit = false;
            if (!empty($entity)) $obj = $this->Proditems->build_entity($entity, $this->session->userdata('store_branch'));
        }
        $obj->show_unit = config_item('show_unit') == "yes" ? true : false;
        return $obj;
    }
    public function itembox($array)
    {
        $obj = !empty($array['option_id']) ? $this->item($array['option_id'], true) : $this->item($array['item_id'], false);
        $obj->show_unit = config_item('show_unit') == 'yes' ? true : false;
        $obj->lang = lang_option();
        if ($obj->has_top) {
            $tops = $this->Prodtops->get(['status' => 'active'], false, false);
            foreach ($tops as $i => $v) {
                $v->edit = false;
                $tops[$i] = $this->Prodtops->build_entity($v, $this->session->userdata('store_branch'));
                $tops[$i]->group_id = 888888;
                $tops[$i]->group_name = ['en' => 'Toppings', 'bn' => 'টপিংস'];
                $tops[$i]->show_unit = config_item('show_unit') == "yes" ? true : false;
                $tops[$i]->lang = lang_option();
            }
            $obj->tops = $tops;
        }
        return $obj;
    }
    public function customset($array)
    {
        $conditions['status'] = "active";
        $obj = !empty($array['option_id']) ? $this->item($array['option_id'], true) : $this->item($array['item_id'], false);
        $obj->show_unit = config_item('show_unit') == 'yes' ? true : false;
        $obj->lang = lang_option();
        if ($array['item_id'])  $conditions['hook_item_id'] = $array['item_id'];
        if ($array['option_id'])  $conditions['hook_option_id'] = $array['option_id'];
        $sets = $this->Prodcustsets->get_by($conditions, false, false, null, null, null, null, 'level', 'ASC');
        $levels = [];
        foreach ($sets as $set) {
            $set->name = $this->Prodcustsets->decode($set->name);
            $set->options = $this->Prodcustsetoptions->get_by(['set_id' => $set->id, 'status' => 'active'], false, false);
            foreach ($set->options as $index => $option) {
                $set->options[$index] = $this->Prodcustsetoptions->build_entity($option, $this->session->userdata('store_branch'));
                $set->options[$index]->lang = lang_option();
                $set->options[$index]->show_unit = config_item('show_unit') == "yes" ? true : false;
            }
            if (array_key_exists($set->level, $levels))  array_push($levels[$set->level], $set);
            else $levels[$set->level] = array($set);
            $set->lang = lang_option();
        }
        $obj->levels = $levels;
        return $obj;
    }
    public function area_dropdown($id = null)
    {
        return $this->Geoareas->formatted($id, $this->session->userdata('store_branch'), true);
    }
    public function delivery_plan($id = null, $area_id = null)
    {
        if ($id && !is_array($id)) {
            return $this->Cfgdelplans->build_entity($this->Cfgdelplans->get($id, true));
        } else {
            $conditions = $id && is_array($id) ? $id : [];
            $conditions['status'] = 'active';
            if ($area_id) $conditions['area_id'] = $area_id;
            if (config_item('store_type') == "multiple" && empty($conditions['hub_id'])) $conditions['hub_id'] = $this->session->userdata('store_branch');
            $list = $this->Cfgdelplans->get($conditions);
            usort($list, function ($a, $b) {
                if ($a->base < $b->base) return true;
                else return false;
            });
            return $list;
        }
    }
    public function checkout()
    {
        $checkout_info = $this->Cart->get_checkout_info();
        $delivery_time = delivery_time($this->Cart->get_order_type(), $this->Cart->get_order_receive_date(), $this->Cart->get_order_receive_time());
        $customer = $this->Customers->get($this->session->userdata('customerId'), true);
        $order['invoice'] = '';
        $order['subtotal'] = $this->Cart->subtotal();
        $order['discount'] = $this->Cart->discount();
        $order['tax'] = $this->Cart->tax();
        $order['service_charge'] = $this->Cart->seervice_charge();
        $order['delivery_charge'] = $this->Cart->delivery_charge();
        $order['payment_fee'] = 0;
        $order['admin_fee'] = $this->Cart->admin_fee();
        $order['admin_fee_per'] = config_item('admin_fee_per');
        $order['grand_total'] = rounding($this->Cart->total());
        $order['cost_total'] = $this->Cart->cost_total();
        $order['rounding'] = $this->Cart->rounding();
        $order['is_paid'] = false;
        $order['service'] = $this->Cart->get_order_type();
        $order['source'] = "web";
        $order['source_app'] = "web";
        $order['cust_id'] = $customer->id;
        $order['cust_phone'] = $customer->phone;
        $order['cust_area'] = $this->Cart->get_delivery_area();
        $order['cust_meta'] = [
            'service' => $this->Cart->get_order_type(),
            'cust_id' => $customer->id,
            'name' => $this->Customers->decode($customer->name, true),
            'phone' =>  $customer->phone,
            'who_receive' => $checkout_info['who_receive'],
            'receiver_name' => !empty($checkout_info['name_en']) ? ['en' => $checkout_info['name_en'], 'bn' =>  $checkout_info['name_en']] : $this->Customers->decode($customer->name, true),
            'receiver_phone' => !empty($checkout_info['phone']) ? $checkout_info['phone'] : $customer->phone,
            'delivery_time' => $delivery_time['time'],
            'delivery_date' => $delivery_time['date'],
        ];
        if ($this->Cart->get_order_type() === 'delivery') {
            $order['cust_meta']['area'] = $this->Geoareas->formatted($this->Cart->get_delivery_area())->formatted;
            if (!empty($checkout_info['delivery_home_id'])) {
                $order['cust_meta']['house'] = $this->Sites->decode($this->Customeraddresses->get($checkout_info['delivery_home_id'], true)->house);
            } else {
                $customer_address_entity = ['cust_id' => $customer->id, 'label_en' => $checkout_info['delivery_home_name'], 'label_bn' => $checkout_info['delivery_home_name'], 'house_en' => $checkout_info['delivery_home_name'], 'house_bn' => $checkout_info['delivery_home_name'], 'area_id' => $this->Cart->get_delivery_area()];
                $address_en = $this->Customeraddresses->get_by(['cust_id' => $customer->id, 'area_id' => $this->Cart->get_delivery_area()], FALSE, FALSE, NULL, NULL, $customer_address_entity['house_en'], ['house_en', 'house_bn']);
                $address_bn = $this->Customeraddresses->get_by(['cust_id' => $customer->id, 'area_id' => $this->Cart->get_delivery_area()], FALSE, FALSE, NULL, NULL, $customer_address_entity['house_bn'], ['house_en', 'house_bn']);
                if (empty($address_en) || empty($address_bn)) {
                    $customer_address_entity['label'] = $this->Customeraddresses->encode(['en' => $customer_address_entity['house_en'], 'bn' => $customer_address_entity['house_bn']]);
                    $customer_address_entity['house'] = $customer_address_entity['label'];
                    $home_id = $this->Customeraddresses->save($customer_address_entity);
                    $order['cust_meta']['house'] = $this->Sites->decode($this->Customeraddresses->get($home_id, true)->house);
                }
            }
        }
        $order['cust_meta'] = $this->Sales->encode($order['cust_meta']);
        if ($this->Cart->get_promo_discount_plan()) {
            $order['promo_id'] = $this->Cart->get_promo_discount_plan()['id'];
            $order['promo_meta'] = $this->Sales->encode($this->Cart->get_promo_discount_plan());
        }
        $order['hub_id'] = config_item('store_type') === 'multiple' ? $this->session->userdata('store_branch') : $this->Userhubs->get(['default' => true], true, true)->id;
        $order['device_code'] = '';
        $order['stage'] = 1;
        $order['status'] = 'pending';
        $order['delivery_time'] = $delivery_time['timestamps'];
        $order['order_note'] = $checkout_info['order_note'];
        $order['payment_type'] = $checkout_info['payment_type'];
        $order['closed_on'] = closed_on();
        $data = ['sale' => $order, 'sale_details' => objectToArray($this->Cart->get_cart()), 'sale_discount_details' => ['plan_wise' => $this->Cart->get_plan_discount(), 'promo_wise' => $this->Cart->get_promo_discount(), 'bogo_wise' => $this->Cart->get_multi_discount(), 'default_wise' => 0, 'item_wise' => $this->Cart->get_item_discount(), 'mannual' => 0], 'sale_payment_details' => []];
        $this->process_checkout($data);
    }
    public function process_checkout($data)
    {
        $this->load->model('Sales');
        $this->load->model('Saledetails');
        $this->load->model('Salediscountdetails');
        $this->load->model('Salepaymentdetails');
        $this->load->model('Dispatchers');
        $this->db->trans_start();
        $id = $this->Sales->save($data['sale']);
        $invoice = $this->invoice($data['sale']['hub_id'], '0', $id);
        $this->Sales->save(['invoice' => $invoice], $id);
        foreach ($data['sale_details'] as $key => $val) $data['sale_details'][$key]['sale_id'] = $id;
        $this->Saledetails->save_details($data['sale_details']);
        $data['sale_discount_details']['sale_id'] = $id;
        $this->Salediscountdetails->save($data['sale_discount_details']);
        $this->Dispatchers->save(['status' => 'await', 'event' => 'online', 'document' => 'invoice', 'hub_id' => $data['sale']['hub_id'], 'sale_id' => $id]);
        $this->db->trans_complete();
        if ($this->db->trans_status()) {
            $this->Cart->destroy();
            redirect('customer/orderdetail/' . $id);
        }
    }
}
