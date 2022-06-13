<?php
/**
 *
 *  * @ author ( Zikiy Vadim )
 *  * @ site http://online-services.org.ua
 *  * @ name
 *  * @ copyright Copyright (C) 2016 All rights reserved.
 */

// if uninstall/delete not called from WordPress exit
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Удаляем все опции сохраненные плагином
delete_option( 'vdz_content_navigation_front_show' );
delete_option( 'vdz_content_navigation_title' );
delete_option( 'vdz_content_navigation_find_selector' );
