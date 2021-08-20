
<?php

class Packers extends MY_Model
{

    function __construct()
    {
        parent::__construct();
        $this->_table_name = 'dispatchers';
        $this->_primary_key = 'id';
        $this->_order_by = 'created_at';
        $this->_order_type = "ASC";
        $this->_default = '';
        $this->_created_at = 'created_at';
        $this->_updated_at = 'updated_at';
        $this->_deleted_at = 'deleted_at';
        $this->_soft_deleted = TRUE;
        $this->_timestamps = TRUE;
        $this->_rules = array();
        $this->load->model('Prodbases');
        $this->load->model('Userhubpackers');
    }
    public function validate()
    {
        if ($this->input->post()) {
            $this->form_validation->set_rules('hub_id', lang('hub'), 'required|numeric');
            $this->form_validation->set_rules('sale_id', lang('sale'), 'required');
            $this->form_validation->set_rules('event', lang('event'), 'required|in_list[driver,packer,printer,online]');
            $this->form_validation->set_rules('document', lang('document'), 'required|in_list[bill,invoice,packer]');
            $this->form_validation->set_rules('sent_type', lang('sent') . ' ' . lang('type'), 'in_list[all,new,resend]');
            return $this->form_validation->run();
        }
        return true;
    }
    public function disperse($data = [])
    {
        $data['shop_type'] = config_item('shop_type'); //restaurant/takeaway or other
        $data['station_type'] = config_item('print_chef_station'); //single or multiple 
        if ($data['shop_type'] === 'restaurant' || $data['shop_type'] === 'takeaway') {
            if ($data['station_type'] === 'single') return $this->combined_packing($data);
            else {
                if ($data['service'] === "table" || $data['service'] === "bar")
                    return $this->seperate_packing($data, TRUE);
                else return $this->combined_packing($data);
            }
        } else return $this->combined_packing($data);
    }
    public function get_packer($base_value = 'kitchen')
    {
        $hub_id = !empty(getallheaders()['hub_id']) ? getallheaders()['hub_id'] : 1;
        if (config_item('shop_type') === 'restaurant' || config_item('shop_type') === 'takeaway') {
            $base = $this->Prodbases->get(['value' => $base_value], TRUE, TRUE);
            $packer_id = !empty($base) ? $base->packer_id : null;
            if (config_item('store_type') === 'multiple') {
                $hubpacker = $this->Userhubpackers->get(['base_id' => $base->id, 'hub_id' => $hub_id], TRUE);
                if (empty($hubpacker))   $hubpacker = $this->Userhubpackers->get(['default' => true, 'hub_id' => $hub_id], TRUE);
                $packer_id = !empty($hubpacker) ? $hubpacker->packer_id : null;
            }
            return $packer_id;
        } else {
            $packers = [];
            if (config_item('store_type') === 'multiple') $packers  =  $this->Userhubpackers->get(['hub_id' => $hub_id], FALSE, FALSE, ['packer_id', 'default']);
            else  $packers = $this->Prodbases->get(NULL, FALSE, FALSE, ['packer_id', 'default']);
            $packers = unique_multidim_array(objectToArray($packers), 'packer_id');
            foreach ($packers as $k => $v) $packers[$k]['jobs'] = $this->count_rows(['event' => 'packer', 'listener' => $v['packer_id'], 'hub_id' => $hub_id, 'status' => 'await']);
            usort($packers, function ($a, $b) {
                if ($a['jobs'] == $b['jobs'])  return 0;
                return ($b['jobs'] < $a['jobs']) ? -1 : 1;
            });
            return $packers[count($packers) - 1]['packer_id'];
        }
    }

    public function combined_packing($data)
    {
        $kitchen_items = array_filter($data['sale_details'], function ($v, $k) {
            return $v['new_qty'] > 0;
        }, ARRAY_FILTER_USE_BOTH);
        if (!empty($kitchen_items)) return $this->kitchen_packer($this->sort_items($kitchen_items, config_item('print_chef_seq')), $this->get_packer(), $data);
    }
    public function seperate_packing($data)
    {
        $results = [];
        $kitchen_items = array_filter($data['sale_details'], function ($v, $k) {
            return $v['new_qty'] > 0 && $v['base_name'] === 'kitchen';;
        }, ARRAY_FILTER_USE_BOTH);
        if (!empty($kitchen_items)) $results['kitchen'] = $this->kitchen_packer($this->sort_items($kitchen_items, config_item('print_chef_seq')), $this->get_packer('kitchen'), $data);

        $drinks_items = array_filter($data['sale_details'], function ($v, $k) {
            return $v['new_qty'] > 0 && $v['base_name'] === 'drinks';
        }, ARRAY_FILTER_USE_BOTH);
        if (!empty($drinks_items))  $results['drinks'] = $this->drinks_packer($this->sort_items($drinks_items, config_item('print_chef_seq')), $this->get_packer('drinks'), $data);

        $desserts_items = array_filter($data['sale_details'], function ($v, $k) {
            return $v['new_qty'] > 0 && $v['base_name'] === 'desserts';
        }, ARRAY_FILTER_USE_BOTH);
        if (!empty($desserts_items)) $results['desserts'] = $this->desserts_packer($this->sort_items($desserts_items, config_item('print_chef_seq')), $this->get_packer('desserts'), $data);

        $beverage_items = array_filter($data['sale_details'], function ($v, $k) {
            return $v['new_qty'] > 0 && $v['base_name'] === 'beverage';
        }, ARRAY_FILTER_USE_BOTH);
        if (!empty($beverage_items))  $results['beverage'] = $this->desserts_packer($this->sort_items($beverage_items, config_item('print_chef_seq')), $this->get_packer('beverage'), $data);

        $alcohol_items = array_filter($data['sale_details'], function ($v, $k) {
            return $v['new_qty'] > 0 && $v['base_name'] === 'alcohol';
        }, ARRAY_FILTER_USE_BOTH);
        if (!empty($alcohol_items))  $results['alcohol'] = $this->alcohol_packer($this->sort_items($alcohol_items, config_item('print_chef_seq')), $this->get_packer('alcohol'), $data);
        return $results;
    }

    public function kitchen_packer($items, $packer_id, $info)
    {
        $info['meta_data'] = $this->Packers->encode($items);
        $info['document'] = "kitchen";
        $packer = $this->Packers->get(['event' => $info['event'], 'document' =>  $info['document'], 'sale_id' => $info['sale_id']], TRUE);
        if (!empty($packer)) {
            $info['listener'] =  $packer->listener;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info, $packer->id);
        } else {
            $info['listener'] = $packer_id;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info);
        }
        return $info;
    }
    public function drinks_packer($items, $packer_id, $info)
    {
        $info['meta_data'] = $this->Packers->encode($items);
        $info['document'] = "drinks";
        $packer = $this->Packers->get(['event' => $info['event'], 'document' =>  $info['document'], 'sale_id' => $info['sale_id']], TRUE);
        if (!empty($packer)) {
            $info['listener'] =  $packer->listener;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info, $packer->id);
        } else {
            $info['listener'] = $packer_id;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info);
        }
        return $info;
    }
    public function desserts_packer($items, $packer_id, $info)
    {
        $info['meta_data'] = $this->Packers->encode($items);
        $info['document'] = "desserts";
        $packer = $this->Packers->get_by(['event' => $info['event'], 'document' => $info['document'], 'sale_id' => $info['sale_id']], TRUE);
        if (!empty($packer)) {
            $info['listener'] =  $packer->listener;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info, $packer->id);
        } else {
            $info['listener'] = $packer_id;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info);
        }
        return $info;
    }
    public function beverage_packer($items, $packer_id, $info)
    {
        $info['meta_data'] = $this->Packers->encode($items);
        $info['document'] = "beverage";
        $packer = $this->Packers->get_by(['event' => $info['event'], 'document' => $info['document'], 'sale_id' => $info['sale_id']], TRUE);
        if (!empty($packer)) {
            $info['listener'] =  $packer->listener;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info, $packer->id);
        } else {
            $info['listener'] = $packer_id;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info);
        }
        return $info;
    }
    public function alcohol_packer($items, $packer_id, $info)
    {
        $info['meta_data'] = $this->Packers->encode($items);
        $info['document'] = "alcohol";
        $packer = $this->Packers->get_by(['event' => $info['event'], 'document' => $info['document'], 'sale_id' => $info['sale_id']], TRUE);
        if (!empty($packer)) {
            $info['listener'] =  $packer->listener;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info, $packer->id);
        } else {
            $info['listener'] = $packer_id;
            $info = $this->Packers->entity($info);
            $this->Packers->save($info);
        }
        return $info;
    }

    public function sort_items($items, $sort_by)
    {
        if ($sort_by === "base") {
            usort($items, function ($a, $b) {
                if ($a == $b) return 0;
                return ($a['base_id'] < $b['base_id']) ? -1 : 1;
            });
        } else if ($sort_by === "course") {
            usort($items, function ($a, $b) {
                if ($a == $b) return 0;
                return ($a['course_id'] < $b['course_id']) ? -1 : 1;
            });
        } else {
            usort($items, function ($a, $b) {
                if ($a == $b) return 0;
                return ($a['category_id'] < $b['category_id']) ? -1 : 1;
            });
        }
        return $items;
    }
}
