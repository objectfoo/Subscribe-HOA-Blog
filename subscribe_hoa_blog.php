<?php
/**
 * Plugin Name: Subscribe HOA Blog
 * URI: http://pcmnw.com
 * Description: Form Based, subscribe/unsubscribe and respond to feed back for dreamhost's announce lists
 * Version: 3.0
 * Author: Satake
 * License: GPLv2
*/

global $admin_shb, $shortCode_shb, $feedback_shb;

// paths
define( 'SHB_FILE', __FILE__ );
define( 'SHB_DIR', plugin_dir_url( SHB_FILE ) );

// wp stored options
define( 'SHB_OPTIONS_KEY',  'SHB_OPTIONS' );
define( 'SHB_API_KEY',      'API_KEY' );
define( 'SHB_LIST_KEY',     'LIST_KEY' );
define( 'SHB_UUID_KEY',     'UUID_KEY' );
define( 'SHB_ANNOUNCE_KEY', 'ANNOUNCE_KEY' );

// string defines
define( 'SHB_DOMAIN_KEY',   'DOMAIN_NAME' );
define( 'SHB_DOMAIN_NAME',  'pcmnw2.com' );
define( 'SHB_MAIL_TYPE',    'text' );          // text or html

// Administration
if( !class_exists( 'Admin_SHB' ) )
    require_once dirname( SHB_FILE ) . '/php/Admin_SHB.php';

if( class_exists( 'Admin_SHB' ) ) {
    $admin_shb = new Admin_SHB();
    $admin_shb->init();
}

// Shortcode
if( !class_exists( 'ShortCode_SHB' ) )
    require_once dirname( SHB_FILE ) . '/php/ShortCode_SHB.php';

if( class_exists( 'ShortCode_SHB' ) ) {
    $shortCode_shb = new ShortCode_SHB( $admin_shb );
    $shortCode_shb->init();
}

// Feedback
if( !class_exists( 'FeedbackPages_SHB'))
    // require_once dirname( SHB_FILE ) . '/php/FeedbackPages_SHB.php';

if( class_exists( 'FeedbackPages_SHB' ) ) {
    $feedback_shb = new FeedbackPages_SHB( $admin_shb );
    $feedback_shb->init();
}
    
// Announcement
if( !class_exists( 'Announce_SHB') )
    require_once dirname( SHB_FILE ) . '/php/Announce_SHB.php';

if( class_exists( 'Announce_SHB') ) {
    $announce_shb = new Announce_SHB( $admin_shb );
}

// Activation hook
register_activation_hook( SHB_FILE, 'install_plugin_shb' );
function install_plugin_shb() {
    global $admin_shb, $feedback_shb;
    $admin_shb->install();
    // $feedback_shb->init();

    // $feedback_shb->add_rules();
    // flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook( SHB_FILE, 'deactivate_plugin');
function deactivate_plugin() {
    // flush_rewrite_rules();
}


?>