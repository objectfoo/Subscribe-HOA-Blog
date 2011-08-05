<?php
class FeedbackPages_SHB {

	function __construct($admin) {
		$this->admin = $admin;
	}
	
    function init() {
        add_action( 'init' 'add_rules' );
        add_filter( 'query_vars', 'add_query_vars' );

        // add_action( 'init', array($this, 'print_r_wp_rewrite') );
    }

    add_rules() {
        // add rewrite rules
    }
    
    add_query_vars() {
        // add query vars so wp recognizes it
    }

    function print_r_wp_rewrite() {
        global $wp_rewrite;
        global $wp_query;

        echo '<pre>';
        print_r( $wp_rewrite );
        echo '</pre>';
        echo '------------------------------------------------------------------';
        echo '<pre>';
        print_r( $wp_query );
        echo '</pre>';
        die();
    }
}
?>