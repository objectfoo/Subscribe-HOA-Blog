<?php
class FeedbackPages_SHB {

	function __construct( $admin ) {
		$this->admin = $admin;
	}
	
    function init() {
        add_action( 'init', array( $this, 'add_rules_shb' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

        // add_action( 'init', array($this, 'print_r_wp_rewrite') );
    }

    function add_rules_shb() {
        // add rewrite rules
    }
    
    function add_query_vars( $vars ) {
        // add query vars so wp recognizes it
		$vars[] = 'address';
		$vars[] = 'name';
		$vars[] = 'code';
		return $vars;
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