<?php
defined('BASEPATH') or exit('No direct script access allowed');
$route['default_controller'] = 'site';
$route['404_override'] = 'Oops';
$route['translate_uri_dashes'] = false;
$route['/'] = 'site';
$route['booking'] = 'site/booking';
$route['bengali'] = 'site/bengali';
$route['contact'] = 'site/contact';
$route['english'] = 'site/english';
$route['gallery'] = 'site/gallery';
$route['gallery/(:num)'] = 'site/gallery/$1';
$route['home'] = 'site/home';
$route['login'] = 'site/login';

$route['order'] = 'site/order';
$route['shop'] = 'site/order';
$route['menu'] = 'site/order';
$route['takeaway'] = 'site/order';
$route['online-order'] = 'site/order';
$route['catalog'] = 'site/order';
$route['store'] = 'site/order';

$route['order/(:num)'] = 'site/order/$1';
$route['shop/(:num)'] = 'site/order/$1';
$route['menu/(:num)'] = 'site/order/$1';
$route['takeaway/(:num)'] = 'site/order/$1';
$route['online-order/(:num)'] = 'site/order/$1';
$route['catalog/(:num)'] = 'site/order/$1';
$route['store/(:num)'] = 'site/order/$1';

$route['order/checkout'] = 'site/checkout';
$route['review'] = 'site/review';
$route['review/(:num)'] = 'site/review/$1';
$route['signup'] = 'site/signup';
$route['privacy-policy'] = 'site/privacypolicy';
$route['terms-of-use'] = 'site/termsofuse';
$route['cookie-policy'] = 'site/cookiepolicy';
$route['customer/orderlist'] = 'customer/orderlist';
$route['customer/orderlist/(:num)'] = 'customer/orderlist/$1';
$route['forgot-password'] = 'site/forgot_password';
