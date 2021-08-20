<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Test extends Public_Layout
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('Tablebookings');
        $this->load->model('Customers');
    }



    public function index()
    {
        /*Booking emal/sms  */
        $booking = $this->Tablebookings->get(8, true);
        $booking->customer = $this->Customers->get($booking->cust_id, true, true, ['name', 'phone', 'email']);
        $booking->customer->name = $this->Customers->decode($booking->customer->name, true);
        $store_info = [];
        if (config_item('store_type') === 'multiple') $store_info = store_info($booking->hub_id, $booking->lang);
        else store_info(null, $booking->lang);
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
        /**End of booking sms/email */
    }
}
