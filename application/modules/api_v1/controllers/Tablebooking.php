<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Tablebooking extends REST_Controller
{
    public function __construct($config = 'rest')
    {
        parent::__construct($config);
        if ($this->server->require_scope()) {
            $this->load->model('Customers');
            $this->load->model('Tablebookings');
        }
    }
    public function add_get()
    {
        $results['date_slots'] = date_slot((int) config_item('booking_offest_day'), (int) config_item('booking_max_day'));
        $results['selected_date'] = array_values($results['date_slots'])[0];
        $opening = $this->Openings->get(['day' => date('D')], true, true);
        $from_now = count($results['date_slots']) > 1 ? false : true;
        $results['time_slots'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . $opening->start)), date('h:i A', strtotime(date('Y-m-d ') . $opening->end)), 0, (int) config_item('slot_interval_time'), $from_now);
        $results['selected_time'] = array_values($results['time_slots'])[0];
        return $this->response($results);
    }
    public function index_get($id = null, $edit = null, $deleted = null, $from = null, $to = null, $report_type = "sitting", $stage = null)
    {
        if (!empty($id) && empty($edit)) {
            $results['entity'] = $this->Tablebookings->get_bookings($id);
            if (!empty($results['entity'])) {
                $results['entity']->name = $this->Customers->decode($results['entity']->name);
                $results['entity']->hub = $this->Customers->decode($results['entity']->hub);
            }
        } else {
            $results['list'] = $this->Tablebookings->get_bookings(null, $deleted, $from, $to, $report_type, $stage);
            $results['guest_total'] = 0;
            foreach ($results['list'] as $index => $obj) {
                $results['list'][$index]->name = $this->Customers->decode($obj->name);
                $results['list'][$index]->hub = $this->Customers->decode($obj->hub);
                $results['guest_total'] += $obj->guest;
                if ($id == $obj->id) $results['entity'] = $results['list'][$index];
            }
        }
        $results['date_slots'] = date_slot((int) config_item('booking_offest_day'), (int) config_item('booking_max_day'));
        $opening = $this->Openings->get(['day' => date('D')], true, true);
        $from_now = count($results['date_slots']) > 1 ? false : true;
        $results['time_slots'] = time_slot(date('h:i A', strtotime(date('Y-m-d ') . $opening->start)), date('h:i A', strtotime(date('Y-m-d ') . $opening->end)), 0, (int) config_item('slot_interval_time'), $from_now);
        return $this->response($results);
    }

    public function index_post()
    {
        if ($this->Tablebookings->validate()) {
            $this->db->trans_start();
            $entity = $this->Tablebookings->post_entity($this->post());
            $entity['name'] = $this->Tablebookings->encode(['en' => $entity['name_en'], 'bn' => $entity['name_bn']]);
            $booking_entity = $this->Tablebookings->entity($entity);

            $customer_entity = $this->Customers->entity($entity);
            if (!empty($customer_entity['id'])) unset($customer_entity['id']);
            $customer = $this->Customers->get_by(['phone' => $customer_entity['phone']], true, true);
            if (!empty($customer)) {
                $booking_entity['cust_id'] = $customer->id;
                if (empty($customer->email) && !empty($customer_entity['email'])) $this->Customers->save(['email' => $customer_entity['email']], $customer->id);
            } else $booking_entity['cust_id'] = $this->Customers->save($customer_entity);

            $AsapTime = DateTime::createFromFormat('d/m/Y h:i A', $entity['date'] . ' ' . $entity['time']);
            if (empty($AsapTime)) $AsapTime = new DateTime($entity['date'] . ' ' . $entity['time']);
            $booking_entity['sit_time'] = $AsapTime->format('Y-m-d H:i:s');
            $booking_entity['lang'] = getallheaders()['lang'];

            if (empty($booking_entity['hub_id']))  $booking_entity['hub_id'] = !empty(getallheaders()['hub_id']) ? getallheaders()['hub_id'] : 1;
            if (!empty($booking_entity['id'])) unset($booking_entity['id']);
            if (!empty($entity['id'])) {
                $results['status'] = $this->Tablebookings->save($booking_entity, $entity['id']);
                $this->__send_message($entity['id']);
                if (!empty($results['status'])) add_activity('Table booking', 'A table booking created');
            } else {
                $results['status'] = $id = $this->Tablebookings->save($booking_entity);
                $this->__send_message($id);
                if (!empty($results['status'])) add_activity('Table booking', 'A new table booking created');
            }
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('saved_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('saved_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'entity' => $this->post(), 'message' => $this->form_validation->error_array()]);
    }
    public function index_put($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $entity = $this->Tablebookings->entity($this->put());
            $results['status'] = $this->Tablebookings->save($entity, $id);
            if (!empty($results['status']))  add_activity('Table booking', 'A table booking updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status() && $results['status'];
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $this->Tablebookings->save($this->patch(), $id);
            add_activity('Table booking', 'A table booking updated');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('discount') . ' ' . lang('unit')) : sprintf(lang('update_failed_msg'), lang('discount') . ' ' . lang('unit'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function index_delete($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Tablebookings->delete($id);
            add_activity('Table booking', 'A table booking deleted');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function undo_patch($id = null)
    {
        if (!empty($id)) {
            $this->db->trans_start();
            $results['status'] = $this->Tablebookings->save([$this->Tablebookings->deletedAt() => false], $id);
            add_activity('Table booking', 'A table booking restored from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('update_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('update_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request'], REST_Controller::HTTP_BAD_REQUEST);
    }
    public function trash_delete($id = null)
    {
        if (!empty($id) && config_item('lock_permanent_delete') === 'no') {
            $this->db->trans_start();
            $this->Tablebookings->delete($id, true);
            add_activity('Table booking', 'A table booking deleted permanently from bin');
            $this->db->trans_complete();
            $results['status'] = $this->db->trans_status();
            $results['message'] = $results['status'] ? sprintf(lang('delete_success_msg'), lang('delivery') . ' ' . lang('plan')) : sprintf(lang('delete_failed_msg'), lang('delivery') . ' ' . lang('plan'));
            return $this->response($results);
        }
        return $this->response(['status' => false, 'message' => 'invalid request']);
    }
    public function confirm_post()
    {
        $this->db->trans_start();
        if (!empty($this->post('dispatcher'))) {
            $this->load->model('Dispatchers');
            $this->Dispatchers->save(['status' => 'done'], $this->post('dispatcher'));
        }
        if ($this->post('action') === 'accept') $this->Tablebookings->save(['stage' => 2], $this->post('booking'));
        if ($this->post('action') === 'reject' || $this->post('action') === 'cancel') $this->Tablebookings->save(['stage' => 3], $this->post('booking'));
        $this->db->trans_complete();
        $this->__send_message($this->post('booking'));
        return $this->response(['status' => $this->db->trans_status()]);
    }
    private function __send_message($id)
    {
        $booking = $this->Tablebookings->get($id, true);
        $booking->customer = $this->Customers->get($booking->cust_id, true, true, ['name', 'phone', 'email']);
        $booking->customer->name = $this->Customers->decode($booking->customer->name, true);
        $store_info = [];
        if (config_item('store_type') === 'multiple') $store_info = store_info($booking->hub_id, $booking->lang);
        else $store_info = store_info(null, $booking->lang);
        $data['store_info'] = $store_info;
        $data['name'] = $booking->customer->name[$booking->lang];
        $data['to'] =    $booking->customer->email;
        $data['from'] = $store_info['email'];
        $data['from_title'] = $store_info['name'];
        $message = '';
        if ($booking->stage == 2) {
            $data['subject'] = $booking->lang == 'en' ? "Booking Confirmed" : "বুকিং নিশ্চিত করা হয়েছে";
            $line1 = ['en' => 'Thnak you ' . $booking->customer->name['en'], 'bn' => $booking->customer->name['bn'] . ', আপনাকে ধন্যবাদ'];
            $line2 = ['en' => 'Your booking has been confirmed', 'bn' => 'আপনার বুকিং নিশ্চিত করা হয়েছে।'];
            $message .= "<h3 style='text-transform:capitalize; text-align:center;line-height:20px;'>" . $line1[$booking->lang] . "</h3>";
            $message .= "<h4 style='text-align:center;line-height:20px;'>" . $line2[$booking->lang] . "</h4>";
        }
        if ($booking->stage == 3) {
            $data['subject'] = $booking->lang == 'en' ? "Booking Canceled" : "বুকিং বাতিল করা হয়েছে";
            $line1 = ['en' => 'Sorry! ' . $booking->customer->name['en'], 'bn' => 'দুঃখিত! ' . $booking->customer->name['bn']];
            $line2 = ['en' => 'We could not take your booking due to unavoidable reasons. It has been canceled.', 'bn' => 'অনিবার্য কারণে, আমরা আপনার বুকিংটি নিতে পারিনি। এটি বাতিল করা হয়েছে।'];
            $message .= "<h3 style='text-transform:capitalize; text-align:center;line-height:20px;'>" . $line1[$booking->lang] . "</h3>";
            $message .= "<h4 style='text-align:center;line-height:25px;'>" . $line2[$booking->lang] . "</h4>";
        }
        $message .= "<br/>";
        $line3 = ['en' => 'Booking Id', 'bn' => 'বুকিং আইডি'];
        $message .= "<h3 style='text-align:center;font-size:24px'><strong style='border-bottom:1px solid #000;'>" . $line3[$booking->lang] . ' : ' . to_bengali(str_pad($booking->id, 7, '0', STR_PAD_LEFT), $booking->lang)  . "</strong></h3>";
        $date = ['en' => 'Date : ' . date('d/m/Y', strtotime($booking->sit_time)), 'bn' => 'তারিখ : ' . to_bengali(date('d/m/Y', strtotime($booking->sit_time)), $booking->lang)];
        $time = ['en' => 'Time : ' . date('h:i A', strtotime($booking->sit_time)), 'bn' => 'সময় : ' . to_bengali(date('h:i A', strtotime($booking->sit_time)), $booking->lang)];
        $guest = ['en' => 'Guests : ' . $booking->guest . ' People', 'bn' => 'অতিথি : ' . to_bengali($booking->guest, $booking->lang) . ' জন'];
        $message .= "<div style='text-align:center'>";
        $message .= "<div style='width:33%;float:left;'><div><strong>" . $date[$booking->lang] . "</strong></div></div>";
        $message .= "<div style='width:33%;float:left;'><div><strong>" . $time[$booking->lang] . "</strong></div></div>";
        $message .= "<div style='width:33%;float:left;'><div><strong>" . $guest[$booking->lang] . "</strong></div></div>";
        $message .= "</div>";
        $message .= "<br/><br/>";
        if ($booking->stage == 2) {
            $ending = ['en' => 'Your booking request was sent at ' . date('d/m/Y h:i A', strtotime($booking->created_at)) . ' and confirmed at ' . date('d/m/Y h:i A', strtotime($booking->updated_at)) . '. Any Question ?  call us at 01717103734', 'bn' => 'আপনার বুকিংয়ের অনুরোধ ' . to_bengali(date('d/m/Y h:i A', strtotime($booking->created_at)), $booking->lang) . ' পাঠানো হয়েছিল এবং ' . to_bengali(date('d/m/Y h:i A', strtotime($booking->updated_at)), $booking->lang) . ' এ নিশ্চিতকরণ করা হয়েছে । কোনো প্রশ্ন থাকলে ফোন করুন ' . to_bengali($store_info['phone'], $booking->lang)  . ' নম্বরে।'];
            $message .= "<div style='text-align:center;'><small>" . $ending[$booking->lang] . "</small></div>";
        }
        if ($booking->stage == 3) {
            $ending = ['en' => 'Your booking request was sent at ' . date('d/m/Y h:i A', strtotime($booking->created_at)) . ' and canceled at ' . date('d/m/Y h:i A', strtotime($booking->updated_at)) . '. Any Question ?  call us at 01717103734', 'bn' => 'আপনার বুকিংয়ের অনুরোধ ' . to_bengali(date('d/m/Y h:i A', strtotime($booking->created_at)), $booking->lang)  . ' পাঠানো হয়েছিল এবং ' . to_bengali(date('d/m/Y h:i A', strtotime($booking->updated_at)), $booking->lang)  . ' এ বাতিল করা হয়েছে । কোনো প্রশ্ন থাকলে ফোন করুন ' . to_bengali($store_info['phone'], $booking->lang)  . ' নম্বরে।'];
            $message .= "<div style='text-align:center;'><small>" . $ending[$booking->lang] . "</small></div>";
        }
        $data['message'] = $message;
        $sms_en = "Thank you " . $booking->customer->name['en'] . ". Your booking has been confirmed. ID : " . str_pad($booking->id, 7, '0', STR_PAD_LEFT) . ", Date : " . date('d/m/Y', strtotime($booking->sit_time)) . ", Time : " . date('h:i A', strtotime($booking->sit_time)) . ", Guests : " . $booking->guest . " people, call us at " . $store_info['phone'] . " for detail.";
        $sms_bn = "ধন্যবাদ " . $booking->customer->name['bn'] . " । আপনার বুকিং নিশ্চিত করা হয়েছে । আইডি : " . to_bengali(str_pad($booking->id, 7, '0', STR_PAD_LEFT), 'bn')  . ", তারিখ : " . to_bengali(date('d/m/Y', strtotime($booking->sit_time)), "bn") . ", সময় : " . to_bengali(date('h:i A', strtotime($booking->sit_time)), "bn")  . ", অতিথি : " . to_bengali($booking->guest, 'bn') . " জন, " . " বিস্তারিত জানতে কল করুন " . to_bengali($store_info['phone'], 'bn')  . " নম্বরে ।";
        sms($booking->customer->phone, $booking->lang == "bn" ? $sms_bn : $sms_en);
        email($data);
    }
}
