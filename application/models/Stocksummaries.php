<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stocksummaries extends MY_Model
{

    function __construct()
    {
        parent::__construct();
    }
    function get_itemin_rows($start, $end, $hub_id = NULL)
    {
        $this->db->select('sl.*,sd.item_id,sd.option_id,sd.topping_id,userhubs.name as hub_name');
        $this->db->group_by('sl.stock_id');
        $this->db->select_sum('sl.item_in');
        $this->db->from('stocklogs as sl');
        $this->db->join('stockdefines as sd', 'sd.id=sl.stock_id');
        $this->db->join('userhubs as userhubs', 'sl.hub_id=userhubs.id');
        $this->db->where('sl.item_in >', 0);
        if (!empty($hub_id) && intval($hub_id)) $this->db->where('sl.hub_id', $hub_id);
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $items_in = $this->db->get()->result();
        foreach ($items_in as $index => $obj) {
            if (!empty($hub_id))  $items_in[$index]->hub_name = $this->decode($obj->hub_name);
            else $items_in[$index]->hub_name = null;
            if ($obj->topping_id > 0) {
                $top = $this->db->where('id', $obj->topping_id)->get('prodtops')->row();
                if (!empty($top)) {
                    $items_in[$index]->price = $top->base_price;
                    $items_in[$index]->total = $top->base_price * $obj->item_in;
                    $items_in[$index]->name = $this->decode($top->name);
                    $items_in[$index]->unit_name = $this->decode($this->db->where('id', $top->unit_id)->get('cfgunits')->row()->short);
                }
            }
            if ($obj->option_id > 0) {
                $optionObj = $this->db->where('id', $obj->option_id)->get('prodoptions')->row();
                if (!empty($optionObj)) {
                    $optionObj->name = $this->decode($optionObj->name);
                    $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                    $itemObj->name = $this->decode($itemObj->name);
                    $items_in[$index]->price = $optionObj->base_price;
                    $items_in[$index]->total = $optionObj->base_price * $obj->item_in;
                    $items_in[$index]->name = array('en' => $optionObj->name->en . ' ' . $itemObj->name->en, 'bn' => $optionObj->name->bn . ' ' . $itemObj->name->bn);
                    $items_in[$index]->unit_name = $this->decode($this->db->where('id', $optionObj->unit_id)->get('cfgunits')->row()->short);
                }
            } else {
                $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                if (!empty($itemObj)) {
                    $items_in[$index]->price = $itemObj->base_price;
                    $items_in[$index]->total = $itemObj->base_price * $obj->item_in;
                    $items_in[$index]->name = $this->decode($itemObj->name);
                    $items_in[$index]->unit_name = $this->decode($this->db->where('id', $itemObj->unit_id)->get('cfgunits')->row()->short);
                }
            }
        }
        return $items_in;
    }
    function get_itemout_rows($start, $end, $hub_id = NULL)
    {
        $this->db->select('sl.*,sd.item_id,sd.option_id,sd.topping_id,userhubs.name as hub_name');
        $this->db->group_by('sl.stock_id');
        $this->db->select_sum('sl.item_out');
        $this->db->from('stocklogs as sl');
        $this->db->join('stockdefines as sd', 'sd.id=sl.stock_id');
        $this->db->join('userhubs as userhubs', 'sl.hub_id=userhubs.id');
        $this->db->where('sl.item_out >', 0);
        if (!empty($hub_id) && intval($hub_id)) $this->db->where('sl.hub_id', $hub_id);
        $this->db->where('closed_on BETWEEN "' . $start . '" and "' . $end . '"');
        $items_out = $this->db->get()->result();
        foreach ($items_out as $index => $obj) {
            if (!empty($hub_id)) $items_out[$index]->hub_name = $this->decode($obj->hub_name);
            else $items_out[$index]->hub_name = null;
            if ($obj->topping_id > 0) {
                $top = $this->db->where('id', $obj->topping_id)->get('prodtops')->row();
                if (!empty($top)) {
                    $items_out[$index]->price = $top->in_price;
                    $items_out[$index]->total = $top->in_price * $obj->item_out;
                    $items_out[$index]->name = $this->decode($top->name);
                    $items_out[$index]->unit_name = $this->decode($this->db->where('id', $top->unit_id)->get('cfgunits')->row()->short);
                }
            }
            if ($obj->option_id > 0) {
                $optionObj = $this->db->where('id', $obj->option_id)->get('prodoptions')->row();
                if (!empty($optionObj)) {
                    $optionObj->name = $this->decode($optionObj->name);
                    $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                    $itemObj->name = $this->decode($itemObj->name);
                    $items_out[$index]->price = $optionObj->in_price;
                    $items_out[$index]->total = $optionObj->in_price * $obj->item_out;
                    $items_out[$index]->name = array('en' => $optionObj->name->en . ' ' . $itemObj->name->en, 'bn' => $optionObj->name->bn . ' ' . $itemObj->name->bn);
                    $items_out[$index]->unit_name = $this->decode($this->db->where('id', $optionObj->unit_id)->get('cfgunits')->row()->short);
                }
            } else {
                $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                if (!empty($itemObj)) {
                    $items_out[$index]->price = $itemObj->in_price;
                    $items_out[$index]->total = $itemObj->in_price * $obj->item_out;
                    $items_out[$index]->name = $this->decode($itemObj->name);
                    $items_out[$index]->unit_name = $this->decode($this->db->where('id', $itemObj->unit_id)->get('cfgunits')->row()->short);
                }
            }
        }
        return $items_out;
    }
    function get_item_in_low_qty_rows($hub_id = NULL)
    {
        $this->db->select('se.*,sd.item_id,sd.option_id,sd.topping_id,sd.maintain_stock,sd.warn_below_qty');
        $this->db->from('stockentries as se');
        $this->db->join('stockdefines as sd', 'sd.id=se.stock_id');
        $this->db->where('sd.maintain_stock', true);
        $items_low = $this->db->get()->result();
        if (!empty($hub_id) && intval($hub_id) > 0) {
            $hub_name = $this->decode($this->db->where('id', $hub_id)->get('userhubs')->row()->name);
            foreach ($items_low as $k => $v) {
                $hubstock = $this->db->where(array('stock_id' => $v->stock_id, 'hub_id' => $hub_id))->get('stockhubentries')->row();
                if (!empty($hubstock)) {
                    $items_low[$k]->balance = $hubstock->balance;
                    $items_low[$k]->sales = $hubstock->sales;
                } else {
                    $items_low[$k]->balance = 0;
                    $items_low[$k]->sales = 0;
                }
                $items_low[$k]->hub_name = $hub_name;
            }
        }
        foreach ($items_low as $index => $obj) {
            if ($obj->balance > $obj->warn_below_qty) {
                unset($items_low[$index]);
            }
        }
        foreach ($items_low as $index => $obj) {
            if ($obj->topping_id > 0) {
                $top = $this->db->where('id', $obj->topping_id)->get('prodtops')->row();
                if (!empty($top)) {
                    $items_low[$index]->name = $this->decode($top->name);
                    $items_low[$index]->unit_name = $this->decode($this->db->where('id', $top->unit_id)->get('cfgunits')->row()->short);
                }
            }
            if ($obj->option_id > 0) {
                $optionObj = $this->db->where('id', $obj->option_id)->get('prodoptions')->row();
                if (!empty($optionObj)) {
                    $optionObj->name = $this->decode($optionObj->name);
                    $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                    $itemObj->name = $this->decode($itemObj->name);
                    $items_low[$index]->name = array('en' => $optionObj->name->en . ' ' . $itemObj->name->en, 'bn' => $optionObj->name->bn . ' ' . $itemObj->name->bn);
                    $items_low[$index]->unit_name = $this->decode($this->db->where('id', $optionObj->unit_id)->get('cfgunits')->row()->short);
                }
            } else {
                $itemObj = $this->db->where('id', $obj->item_id)->get('proditems')->row();
                if (!empty($itemObj)) {
                    $items_low[$index]->name = $this->decode($itemObj->name);
                    $items_low[$index]->unit_name = $this->decode($this->db->where('id', $itemObj->unit_id)->get('cfgunits')->row()->short);
                }
            }
        }
        return  $items_low;
    }
}
