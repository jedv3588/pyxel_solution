<?php

/*
Plugin Name: Pyxel Calisoft Sequrity
Plugin URI: https://github.com/jedv3588/pyxel_solution
Description: Activa funcionalidades en el sitio para garantizar parámetros de seguridad requridos por  Calisoft
Version: 1.0
Author: JEDV
Author URI: https://github.com/jedv3588
License: GPL2
*/
defined('ABSPATH') or die('Access denied');

/*
 * Variables globales
 */
$url_plugin_pyxel = WP_PLUGIN_URL . '/wp_pyxel_calisoftsequrity';
$options = array();

/*
 * Añadir un enlace al plugin en el menu adminitsracion
 */
function pyxel_sequrity_calisoft_menu()
{
    /*
     * Uso de la funcion add_option_page
     * add_option_page($titulo_pagina, $titulo_menu, $capacidad, $menu, $funcion)
     */
    add_options_page(
        'Plugin Pyxel solutions con requisitos de Calisoft',
        'Pyxel Calisoft squrity',
        'manage_options',
        'pyxel_calisoft_sequrity',
        'pyxel_calisoft_sequrity_opciones_pagina'
    );
}
add_action('admin_menu', 'pyxel_sequrity_calisoft_menu');

/*
 * Controlar que el usuario tenga los permisos necesarios para acceder a la pagina
 */
function pyxel_calisoft_sequrity_opciones_pagina()
{
    if(!current_user_can('manage_options')){
        wp_die('No tiene los permisos necesarios para aceder a la pagina.');
    }
    global $url_plugin_pyxel;
    $options['ultima_actualizacion']    = time();
    require('modelo/contenedor_plugin_opciones_pagina.php');
    update_option('wp_pyxel_calisoftsequrity', $options);
}

/*
 * Estilos
 */
function wp_pyxel_estilo()
{
    wp_enqueue_style('wp_pyxel_estilo', plugins_url('wp_pyxel_calisoftsequrity/css/pyxel_style.css'));
}
add_action('admin_head', 'wp_pyxel_estilo');

/*
 * Remove confirm use week passwd
 */
function wp_pyxel_script_remove_use_week_passwd()
{
    wp_enqueue_script('wp_pyxel_script_remove_use_week_passwd', plugins_url('wp_pyxel_calisoftsequrity/js/delete-use-week-passwd.js'));
}
add_action('admin_head', 'wp_pyxel_script_remove_use_week_passwd');

/*
 *Close inactive sesions destroy ID
 */
function wp_pyxel_script_close_inactive_sesion()
{
    wp_enqueue_script('wp_pyxel_script_remove_use_week_passwd', plugins_url('wp_pyxel_calisoftsequrity/js/close-inactive-sesion.js'));
}
add_action('admin_head', 'wp_pyxel_script_close_inactive_sesion');

function expiration_destroy_session_id( $expiration, $user_id, $remember ) {
    return $remember ? $expiration : 600;
    $sessions = WP_Session_Tokens::get_instance($user_id);
    $sessions->destroy_all();
    wp_destroy_current_session();
    wp_clear_auth_cookie();
}
add_filter( 'auth_cookie_expiration', 'expiration_destroy_session_id', 99, 3 );
add_action('wp_logout', 'expiration_destroy_session_id');

/*
 * Funcion para forzar el uso de password fuerte
 */
function pyxel_force_strong_passwords( $errors, $update, $user_data ) {
    $user_login = $user_data->user_login;
    $user_pass = $user_data->user_pass;

    if ( !is_null( $user_pass ) ) {
        if ( strtolower( $user_login ) === strtolower( $user_pass ) ) {
            $errors->add( 'my_distinct_user_pass', __( 'Usuario y contraseña deben ser diferentes', 'your_textdomain' ) );
        }
        if ( strlen( $user_pass ) < 8 ) {
            $errors->add( 'my_pass_length', __( 'La contraseña debe tener al menos 8 caracteres', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[0-9]/', $user_pass ) ) {
            $errors->add( 'my_pass_numeric', __( 'La contraseña debe tener al menos 1 caracter numérico', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[a-z]/', $user_pass ) ) {
            $errors->add( 'my_pass_lowercase', __( 'La contraseña debe tener al menos una minuscula', 'your_textdomain' ) );
        }
        if ( ! preg_match( '/[A-Z]/', $user_pass ) ) {
            $errors->add( 'my_pass_uppercase', __( 'La contraseña debe tener al menos una mayuscula', 'your_textdomain' ) );
        }
    }
}
add_action( 'user_profile_update_errors', 'pyxel_force_strong_passwords', 0, 3 );

// Restrict back-end for administrators only
function restrict_admin_area_by_rol() {
    if ( ! current_user_can( 'manage_options' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
        wp_redirect( site_url() );
        exit;
    }
}
add_action( 'admin_init', 'restrict_admin_area_by_rol', 1 );

// Show admin bar only for administrators
function hide_admin_bar($content) {
    return ( current_user_can('manage_options') ) ? $content : false;
}
add_filter( 'show_admin_bar' , 'hide_admin_bar');

/*function wpum_my_psw_field_confirmation( $fields ) {

    $fields[ 'confirm_psw' ] = array(
        'label'       => 'Confirm password',
        'type'        => 'password',
        'meta'        => false,
        'required'    => true,
        'description' => 'Add something here if needed',
        'priority' => 30
    );

    return $fields;

}
add_filter( 'wpum_get_registration_fields', 'wpum_my_psw_field_confirmation' );

function wpum_verify_my_psw_confirmation( $pass, $fields, $values, $form ) {

    if ( $form === 'registration' && isset( $values['register']['confirm_psw'] ) ) {

        $psw1 = $values['register']['user_password'];
        $psw2 = $values['register']['confirm_psw'];

        if ( $psw1 !== $psw2 ) {
            return new WP_Error( 'psw-validation-error', 'Passwords do not match.' );
        }

    }

    return $pass;

}
add_filter( 'submit_wpum_form_validate_fields', 'wpum_verify_my_psw_confirmation', 10, 4 );*/

//register_activation_hook(__FILE__.array($jedvPlugin, 'activate'));
//register_deactivation_hook(__FILE__.array($jedvPlugin, 'deactivate'));