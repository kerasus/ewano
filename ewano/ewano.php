<?php

if (!defined('ABSPATH') ) exit;

/*
 Plugin Name: Ewano
 Plugin URI: https://your-plugin-uri.com/
 Description: Injects a custom JavaScript file in the header for custom state.
 Version: 1.0
 Author: Krasus
 Author URI: https://your-website.com/
 */

add_action('plugins_loaded', function () {
    include_once('EwanoInit.php');
    $ewanoInit = new EwanoInit();
    $ewanoInit->prepareForIncomingUser()
        ->prepareEwanoGateway()
        ->onWebAppReady();
}, 0);
