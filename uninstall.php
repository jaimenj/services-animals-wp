<?php

defined('ABSPATH') or die('No no no');
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

include_once 'services-animals.php';

ServicesAnimals::get_instance()->uninstall();
