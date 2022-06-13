<?php
/*
Plugin Name: VDZ Content Navigation
Plugin URI:  http://online-services.org.ua
Description: Simple navigation for your content
Version:     1.3.3
Author:      VadimZ
Author URI:  http://online-services.org.ua#vdz-content-navigation
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VDZ_CN_API', 'vdz_info_content_navigation' );

$plugin_data    = get_file_data( __FILE__, array( 'Version' => 'Version' ), false );
$plugin_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : time();
define( 'VDZ_CN_VERSION', $plugin_version );

require_once 'api.php';
require_once 'updated_plugin_admin_notices.php';

// Код активации плагина
register_activation_hook( __FILE__, 'vdz_cn_activate_plugin' );
function vdz_cn_activate_plugin() {
	global $wp_version;
	if ( version_compare( $wp_version, '3.8', '<' ) ) {
		// Деактивируем плагин
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'This plugin required WordPress version 3.8 or higher' );
	}
	add_option( 'vdz_content_navigation_front_show', 1 );
	add_option( 'vdz_content_navigation_title', '' );
	add_option( 'vdz_content_navigation_find_selector', 'article' );

	do_action( VDZ_CN_API, 'on', plugin_basename( __FILE__ ) );
}


// Код деактивации плагина
register_deactivation_hook( __FILE__, function () {
	$plugin_name = preg_replace( '|\/(.*)|', '', plugin_basename( __FILE__ ));
	$response = wp_remote_get( "http://api.online-services.org.ua/off/{$plugin_name}" );
	if ( ! is_wp_error( $response ) && isset( $response['body'] ) && ( json_decode( $response['body'] ) !== null ) ) {
		//TODO Вывод сообщения для пользователя
	}
} );
//Сообщение при отключении плагина
add_action( 'admin_init', function (){
	if(is_admin()){
		$plugin_data = get_plugin_data(__FILE__);
		$plugin_name = isset($plugin_data['Name']) ? $plugin_data['Name'] : ' us';
		$plugin_dir_name = preg_replace( '|\/(.*)|', '', plugin_basename( __FILE__ ));
		$handle = 'admin_'.$plugin_dir_name;
		wp_register_script( $handle, '', null, false, true );
		wp_enqueue_script( $handle );
		$msg = '';
		if ( function_exists( 'get_locale' ) && in_array( get_locale(), array( 'uk', 'ru_RU' ), true ) ) {
			$msg .= "Спасибо, что были с нами! ({$plugin_name}) Хорошего дня!";
		}else{
			$msg .= "Thanks for your time with us! ({$plugin_name}) Have a nice day!";
		}
		wp_add_inline_script( $handle, "document.getElementById('deactivate-".esc_attr($plugin_dir_name)."').onclick=function (e){alert('".esc_attr( $msg )."');}" );
	}
} );





/*Добавляем новые поля для в настройках шаблона шаблона для верификации сайта*/
function vdz_cn_theme_customizer( $wp_customize ) {

	if ( ! class_exists( 'WP_Customize_Control' ) ) {
		exit;
	}

	// Добавляем секцию для идетнтификатора YS
	$wp_customize->add_section(
		'vdz_content_navigation_section',
		array(
			'title'    => __( 'VDZ Content Navigation' ),
			'priority' => 10,
		// 'description' => __( 'Content Navigation code on your site' ),
		)
	);
	// Добавляем настройки
	$wp_customize->add_setting(
		'vdz_content_navigation_title',
		array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	// Добавляем настройки
	$wp_customize->add_setting(
		'vdz_content_navigation_find_selector',
		array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_setting(
		'vdz_content_navigation_front_show',
		array(
			'type'              => 'option',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Footer OR HEAD
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'vdz_content_navigation_front_show',
			array(
				'label'       => __( 'VDZ Content Navigation' ),
				'section'     => 'vdz_content_navigation_section',
				'settings'    => 'vdz_content_navigation_front_show',
				'type'        => 'select',
				'description' => __( 'ON/OFF' ) . '<br>' . __( 'To show Navigation => ON => use shortcode on page' ) . ':<br><code>[vdz_cn_show]</code><br>' . __( 'Before or After content in your Article' ),
				'choices'     => array(
					1 => __( 'Show' ),
					0 => __( 'Hide' ),
				),
			)
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'vdz_content_navigation_title',
			array(
				'label'       => __( 'Title' ),
				'section'     => 'vdz_content_navigation_section',
				'settings'    => 'vdz_content_navigation_title',
				'type'        => 'text',
				'description' => __( 'Title for content navigation' ),
			)
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'vdz_content_navigation_find_selector',
			array(
				'label'       => __( 'Find in selector or Tag' ),
				'section'     => 'vdz_content_navigation_section',
				'settings'    => 'vdz_content_navigation_find_selector',
				'type'        => 'text',
				'description' => __( 'Default find Headers in article tag (use any jQuery selectors)' ),
			)
		)
	);

	// Добавляем ссылку на сайт
	$wp_customize->add_setting(
		'vdz_content_navigation_link',
		array(
			'type' => 'option',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'vdz_content_navigation_link',
			array(
				// 'label'    => __( 'Link' ),
									'section' => 'vdz_content_navigation_section',
				'settings'                    => 'vdz_content_navigation_link',
				'type'                        => 'hidden',
				'description'                 => '<br/><a href="//online-services.org.ua#vdz-content-navigation" target="_blank">VadimZ</a>',
			)
		)
	);
}
add_action( 'customize_register', 'vdz_cn_theme_customizer', 1 );


// add_filter(
// 'the_content',
// function ( $content ) {
// if ( ! (int) get_option( 'vdz_content_navigation_front_show' ) ) {
// return str_replace( '[vdz_cn_show]', '', $content);
// }
// return $content;
// },
// 1000,
// 1
// );


// Добавляем допалнительную ссылку настроек на страницу всех плагинов
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'customize.php?autofocus[section]=vdz_content_navigation_section' ) ) . '">' . esc_html__( 'Settings' ) . '</a>';
		array_unshift( $links, $settings_link );
		array_walk( $links, 'wp_kses_post' );
		return $links;
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( (int) get_option( 'vdz_content_navigation_front_show' ) ) {
			wp_enqueue_script(
				'vdz_content_nav',
				plugin_dir_url( __FILE__ ) . 'assets/js/vdz_content_nav.js',
				array( 'jquery' ),
				VDZ_CN_VERSION,
				true
			);
		}
	}
);


function vdz_cn_shortcode( $atts, $content ) {
	// Add defaults params and extract variables
	$attributes = shortcode_atts(
		array(
			'vdz_cn_title'            => esc_attr( get_option( 'vdz_content_navigation_title' ) ),
			'vdz_cn_append_to'        => '.vdz_cn_shortcode',
			'vdz_cn_speed'            => 400,
			'vdz_cn_find_in_selector' => esc_attr( get_option( 'vdz_content_navigation_find_selector' ) ),
		),
		$atts
	);
	// TODO: Добавить множественный шорткод на странице (в начале и в конце статьи)
	$vdz_cn_html = '<div class="vdz_cn_shortcode" 
		data-vdz_cn_shortcode_title="' . esc_attr( $attributes['vdz_cn_title'] ) . '"
		data-vdz_cn_shortcode_speed="' . esc_attr( $attributes['vdz_cn_speed'] ) . '"
		data-vdz_cn_shortcode_find_in_selector="' . esc_attr( $attributes['vdz_cn_find_in_selector'] ) . '"
		></div>';
	return $vdz_cn_html;
}

add_shortcode( 'vdz_cn_show', 'vdz_cn_shortcode' );

