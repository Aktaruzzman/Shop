<?php

defined('BASEPATH') OR exit('No direct script access allowed');
$hook['pre_controller'] = array(
    'class' => 'Preloader',
    'function' => 'load',
    'filename' => 'Preloader.php',
    'filepath' => 'hooks'
);
