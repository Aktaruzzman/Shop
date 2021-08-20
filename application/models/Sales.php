
<?php

class Sales extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->_table_name = 'sales';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "DESC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = true;
        $this->_timestamps = true;
        $this->_rules = array();
        /*Load necessary models */
        $this->load->model('Proditems');
        $this->load->model('Prodoptions');
        $this->load->model('Prodtops');
        $this->load->model('Prodmods');
        $this->load->model('Cfgdelplans');
        $this->load->model('Cfgservices');
        $this->load->model('Cfgtaxes');
        $this->load->model('Cfgdiscounts');
        $this->load->model('Cfgconfigs');
        $this->load->model('Customers');
        $this->load->model('Prodfreeplans');
        $this->load->model('Prodfreeitems');
        $this->load->model('Payments');
        $this->load->model('Tillaccounts');
        $this->load->model('Users');
        $this->load->model('Salepaymentdetails');
    }

    private function build_product_identity($entity)
    {
        if (!empty($entity)) {
            $row = null;
            if (!empty($entity->modify_id)) {
                $row = $this->Prodmods->get($entity->modify_id, true);
                $entity->name = $this->decode($row->name);
            } else if (!empty($entity->topping_id)) {
                $row = $this->Prodtops->get($entity->topping_id, true, true);
                $entity->name = $this->decode($row->name);
            } else if (!empty($entity->option_id)) {
                $row = $this->Prodoptions->get($entity->option_id, true, true);
                $row->name = $this->decode($row->name);
                $item = $this->Proditems->get($entity->item_id, true);
                $item->name = $this->decode($item->name);
                $entity->name = array('en' => $item->name->en . ' ' . $item->name->en, 'bn' => $row->name->bn . ' ' . $item->name->bn);
            } else {
                $row = $this->Proditems->get($entity->item_id, true, true);
                $entity->name = $this->decode($row->name);
            }
            $entity->photos['full'] = !empty($row->photo) ? site_url('/uploads/product/item/' . $row->photo) : './img/no-image.png';
            $entity->photos['thumb'] = !empty($row->thumb) ? site_url('/uploads/product/item/thumbnail/' . $row->thumb) : './img/no-image.png';
        }

        return $entity;
    }

    public function list_ongoings($service, $stage, $header)
    {
        $user = $this->Users->get($header['user_id'], true);
        $this->db->select('id,grand_total,is_paid,cust_id,cust_meta,delivery_time,service,status,stage,created_at,created_by,updated_at,updated_by,locked,hub_id,device_code');
        $this->db->from($this->Sales->getTable());
        $this->db->where('source', 'pos');
        $this->db->where_not_in('status', ['deleted', 'rejected']);
        if (!empty($service))  $this->db->where('service', $service);
        (!empty($stage)) ? $this->db->where('stage', $stage) : $this->db->where('stage<=', 4);

        $is_master = "superadmin" === $user->role || "admin" === $user->role || "owner" === $user->role ? true : false;
        $device_code = $header['device_code'];
        $locked = true;
        if (config_item('store_type') === 'multiple') {
            if ($is_master) $this->db->where("hub_id", $user->hub_id);
            else $this->db->where("(locked=$locked AND created_by=$user->id AND device_code=$device_code)", null, false);
        } else {
            if ($is_master) $this->db->where("(hub_id=$user->hub_id OR device_code=$device_code)", null, false);
            else $this->db->where("(locked=$locked AND created_by=$user->id AND device_code=$device_code)", null, false);
        }

        return $this->db->get()->result();
    }
    public function initial_till_cash($header)
    {
        return $this->db->where('hub_id', $header['hub_id'])->where('closed_on BETWEEN "' . $header['start'] . '" and "' . $header['end'] . '"')->get($this->Tillaccounts->getTable())->row();
    }
    public function create($sale)
    {
        $headers = getallheaders();
        $sale['created_by'] = $headers['user_id'];
        $sale['updated_by'] = $headers['user_id'];
        $sale['hub_id'] = $headers['hub_id'];
        $sale['device_code'] = $headers['device_code'];
        $sale['closed_on'] = closed_on();
        $sale['source'] = 'pos';
        $sale['stage'] = 1;
        $sale['status'] = 'pending';
        $sale['admin_fee_per'] = config_item('admin_fee_per');
        $id = $this->save($sale);
        $this->save(['invoice' => $this->My_Model->invoice($sale['updated_by'], $sale['created_by'], $id)], $id);
        $order = $this->get($id, true);
        $order->cust_meta = $this->decode($order->cust_meta);
        $data = $this->Sales->initialize($order);
        $data['order'] = $order;
        return $data;
    }
    public function initialize($order)
    {
        $data['service'] = $service = $this->Cfgservices->get_by(['value' => $order->service], true, true, null, null, null, ['id', 'value', 'charge', 'func', 'delay']);
        $data['default'] = $this->get_default($order, $service);
        $data['discount_plans'] = $this->get_discount_plans($order->service, $order->source_app !== 'pos' ? 'web' : 'pos');
        $data['discount_promo'] = $order->promo_meta ? $order->promo_meta : [];
        $data['discount_bogos'] = $this->get_bogo_plans($order->service, null, $order->source_app !== 'pos' ? 'web' : 'pos');
        $data['free_plans'] = $this->get_free_plans($order->service, $order->source_app !== 'pos' ? 'web' : 'pos');
        $data['delivery_plans'] = "delivery" === $order->service ? $this->get_delivery_plans($order->cust_area) : [];
        if ($order->is_paid) $data['payments'] = $this->Salepaymentdetails->get(['sale_id' => $order->id], true, true)->details;
        else $data['payments'] = $this->get_payments();
        return $data;
    }
    public function get_default($order, $service)
    {
        $tax = $this->Cfgtaxes->get_by(['default' => true], true, true, null, null, null, ['id', 'value']);
        $discount = $this->Cfgdiscounts->get_by(['default' => true], true, true, null, null, null, ['id', 'value', 'func']);
        $default['tax'] = ['id' => $tax->id, 'value' => $tax->value, 'func' => 'percent', 'tax_by' => 'default'];
        $default['discount'] = ['id' => $discount->id, 'value' => $discount->value, 'func' => $discount->func, 'discount_by' => 'default'];
        $default['service_charge'] = ['id' => $service->id, 'value' => $service->charge, 'func' => $service->func];
        $default['delivery_charge'] = ['id' => null, 'value' => doubleval(config_item('delivery_charge')), 'func' => 'fixed'];
        $default['admin_fee'] = ['id' => null, 'value' => 0, 'func' => 'fixed'];
        $adminFeePer = config_item('admin_fee_per');
        if ("order_excluded" === $adminFeePer || "order_included" === $adminFeePer) {
            $adminFee = config_item('admin_fee');
            $adminFeeFunc = config_item('admin_fee_func');
            $default['admin_fee'] = ['id' => null, 'value' => doubleval($adminFee), 'func' => $adminFeeFunc, 'per' => $adminFeePer];
        }
        $default['cust_due'] = ['cust_id' => 0, 'due' => 0];
        if (!empty($order->cust_id)) {
            $customer = $this->Customers->get($order->cust_id, true);
            $default['cust_due'] = ['cust_id' => $customer->id, 'due' => $customer->due];
        }
        return $default;
    }
    public function get_payments()
    {
        $payments = $this->Payments->get_by(['status' => 'active'], false, false, null, null, null, ['id', 'value', 'name', 'fee', 'order']);
        foreach ($payments as $index => $payment) {
            $payments[$index]->name = $this->decode($payment->name);
            $payments[$index]->amount = 0;
            $payments[$index]->reference = null;
        }
        return $payments;
    }
    public function get_discount_plans($service, $source = 'pos')
    {
        $this->db->select('disp.id,disp.min_order,disp.kitchen,disp.drinks,disp.desserts,disp.beverage,disp.alcohol,disp.discount_id,cfgd.value,cfgd.func');
        $this->db->from('discountplans as disp');
        $this->db->join('cfgdiscounts as cfgd', 'cfgd.id=disp.discount_id');
        $this->db->where('disp.' . $service, true);
        $this->db->where('disp.date_start <', time() * 1000);
        $this->db->where('disp.date_end >', time() * 1000);
        $this->db->where('disp.' . date('D'), true);
        $this->db->where('disp.time_start <', date('H:i:s'));
        $this->db->where('disp.time_end >', date('H:i:s'));
        $this->db->where('disp.status', 'active');
        $this->db->where('disp.' . $source, true);
        $this->db->order_by('disp.min_order', 'DESC');
        return $this->db->get()->result();
    }
    public function get_promo_plans($service, $code, $source = 'pos')
    {
        $this->db->select('disp.id,disp.code,disp.min_order,disp.kitchen,disp.drinks,disp.desserts,disp.beverage,disp.alcohol,disp.action,disp.discount_id,cfgd.value,cfgd.func');
        $this->db->from('discountpromos as disp');
        $this->db->join('cfgdiscounts as cfgd', 'cfgd.id=disp.discount_id');
        $this->db->where('disp.code', $code);
        $this->db->where('disp.' . $service, true);
        $this->db->where('disp.date_start <', time() * 1000);
        $this->db->where('disp.date_end >', time() * 1000);
        $this->db->where('disp.' . date('D'), true);
        $this->db->where('disp.time_start <', date('H:i:s'));
        $this->db->where('disp.time_end >', date('H:i:s'));
        $this->db->where('disp.status', 'active');
        $this->db->where('disp.' . $source, true);
        return $this->db->get()->row();
    }
    public function get_bogo_plans($service, $name_id = null, $source = 'pos')
    {
        $this->db->select('disp.id,disp.item_id,disp.option_id,disp.buy,disp.get');
        $this->db->from('discountmulti as disp');
        $this->db->where('disp.' . $service, true);
        $this->db->where('disp.date_start <', time() * 1000);
        $this->db->where('disp.date_end >', time() * 1000);
        $this->db->where('disp.' . date('D'), true);
        $this->db->where('disp.time_start <', date('H:i:s'));
        $this->db->where('disp.time_end >', date('H:i:s'));
        $this->db->where('disp.status', 'active');
        $this->db->where('disp.' . $source, true);
        if (!empty($name_id)) {
            $this->db->where('disp.name_id', $name_id);
            $row = $this->db->get()->row();
            return !empty($row) ? $this->build_product_identity($row) : null;
        } else {
            $results = $this->db->get()->result();
            foreach ($results as $index => $obj) {
                $results[$index] = $this->build_product_identity($obj);
            }
        }

        return $results;
    }
    public function get_free_plans($service, $source = 'pos')
    {
        $this->db->select('disp.id,disp.name,disp.min_order,disp.quantity');
        $this->db->from('prodfreeplans as disp');
        $this->db->where('disp.' . $service, true);
        $this->db->where('disp.date_start <', time() * 1000);
        $this->db->where('disp.date_end >', time() * 1000);
        $this->db->where('disp.' . date('D'), true);
        $this->db->where('disp.time_start <', date('H:i:s'));
        $this->db->where('disp.time_end >', date('H:i:s'));
        $this->db->where('disp.status', 'active');
        $this->db->where('disp.' . $source, true);
        $free_plans = $this->db->get()->result();
        if (!empty($free_plans)) {
            foreach ($free_plans as $index => $plan) {
                $free_plans[$index]->name = $this->decode($plan->name);
                $free_plans[$index]->options = $this->Prodfreeitems->get_by(['plan_id' => $plan->id, 'status' => 'active'], false, false, null, null, null, ['item_id', 'option_id', 'topping_id', 'modify_id']);
                if (!empty($free_plans[$index]->options)) {
                    foreach ($free_plans[$index]->options as $i => $option) {
                        $free_plans[$index]->options[$i] = $this->build_product_identity($option);
                    }
                } else unset($free_plans[$index]);
            }
        }
        return $free_plans;
    }
    public function get_delivery_plans($area_id, $source = 'pos')
    {

        return $this->Cfgdelplans->get_by([$source => true, 'area_id' => $area_id], false, false, null, null, null, ['id', 'base', 'charge', 'delay', 'free_on', 'consider_on'], 'base', 'DESC');
    }
}
