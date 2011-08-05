<?php
class ShortCode_SHB {
    static $need_assets;
    
    function __construct( $admin ) {
        $this->admin = $admin;

        add_shortcode( 'announcelist',  array( $this, 'replace_shortcode') );
        add_action( 'init',             array( $this, 'enqueue_assets' ) );
        add_filter( 'query_vars',       array( $this, 'add_query_vars' ) );
    }
    
    function replace_shortcode() {
        // replace the shortcode with the subscribe / unsubscribe form
        // also show feedback from server when it redirects the browser back to this
        // page (set in the webpanel)
        $opts = $this->admin->getOptions();
        if( $opts[SHB_ANNOUNCE_KEY] ) {
            $resp = '';
            $dh_response_page = get_query_var( 'dh_response_page' );
            $response_data = array(
              'address' => get_query_var( 'address' ),
              'name'    => get_query_var( 'name' ),
              'code'    => get_query_var( 'code' ),
              'type'    => $dh_response_page
            );

             if( !empty($dh_response_page) ) {
                 $resp .= $this->render_response_message( $response_data );
             } 
             $resp .= $this->render_subcribe_form( $response_data );             
        }

        return $resp;
    }
    
    // enqueue scripts and style to be included on the page
    function enqueue_assets() {
        wp_enqueue_style ( 'subscribe-hoa', SHB_DIR.'assets/style.css', false, 1.1, 'all' );
    }
    
    // not used
    // set need_assets if post has shortcut
    function post_has_shortcode( $post ) {
        
        if( preg_match( '/\[announcelist\]/', $post->post_content) ) {
            self::$need_assets = true;
        } else {
            self::$need_assets = false;
        }
    }
    
    // not used
    // if ! $need_assets deregister scripts/styles
    function deregister_assets_check() {
        
        if( !self::$need_assets ) {
            wp_dequeue_style( 'subscribe-hoa' );
        }
    }
    
    // Add query vars
    // this tells wordpress to allow these query vars in $wp_query
    function add_query_vars( $vars ) {
        $vars[] = 'dh_response_page';
        $vars[] = 'address';
        $vars[] = 'name';
        $vars[] = 'code';

        return $vars;
    }

    // render subscription form
    function render_subcribe_form( $vars ) {
        $opts = $this->admin->getOptions();
        $htm  = '<div class="subscribe-hoa">' . "\n";
        $htm .= '<div class="hd">' . "\n";
        $htm .= sprintf('<p>%s Announcement List Subscription</p>', get_bloginfo('name')) . "\n";
        $htm .= '</div>' . "\n";
        $htm .= '<form method="post" action="http://scripts.dreamhost.com/add_list.cgi">' . "\n";
        $htm .= sprintf('<input type="hidden" name="list" value="%s" id="list" />', $opts[SHB_LIST_KEY]) . "\n";
        $htm .= sprintf('<input type="hidden" name="domain" value="%s" id="domain" />', $opts[SHB_DOMAIN_KEY]) . "\n";
        $htm .= sprintf('<input type="hidden" name="url" value="%s" id="url" />', get_permalink() . '/?dh_response_page=subscribed') . "\n";
        $htm .= sprintf('<input type="hidden" name="unsuburl" value="%s" id="unsuburl" />', get_permalink() . '/?dh_response_page=unsubscribed') . "\n";
        $htm .= sprintf('<input type="hidden" name="alreadyonurl" value="%s" id="alreadyonurl" />', get_permalink() . '/?dh_response_page=already_subscribed') . "\n";
        $htm .= sprintf('<input type="hidden" name="notonurl" value="%s" id="notonurl" />', get_permalink() . '/?dh_response_page=not_subscribed') . "\n";
        $htm .= sprintf('<input type="hidden" name="invalidurl" value="%s" id="invalidurl" />', get_permalink() . '/?dh_response_page=invalid_email') . "\n";
        $htm .= sprintf('<input type="hidden" name="emailconfirmurl" value="%s" id="emailconfirmurl" />', get_permalink() . '/?dh_response_page=confirm') . "\n";
        $htm .= '<table border="0" cellspacing="0" cellpadding="0">' . "\n";
        $htm .= '<tr>' . "\n";
        $htm .= sprintf( '<td class="left"><label for="email">E-mail:</label> <input type="text" name="email" value="%s" id="email" /></td>', $vars['address'] ) . "\n";
        $htm .= '<td><input type="submit" name="submit" value="Subscribe" id="submit" /></td>' . "\n";
        $htm .= '<td><input type="submit" name="unsub" value="Unsubscribe" id="unsub"></td>' . "\n";
        $htm .= '</tr>' . "\n";
        $htm .= '</table>' . "\n";
        $htm .= '</form>' . "\n";
        $htm .= '</div>' . "\n";

        return $htm;
    }
    
    // render response message
    function render_response_message( $vars ) {
        $resp_codes = array(
            '1'  => 'Address Successfully Subscribed',
            '2'  => 'Address Successfully Unsubscribed',
            '3'  => 'Address Successfully Mailed Confirmation Link',
            '-1' => 'Address Already on List',
            '-2' => 'Address Not In List',
            '-3' => 'Invalid Email Address',
            '-4' => 'Missing Required Field',
            '-5' => 'Re-typed email doesn\'t match first email'
        );
        

        if ( intval($vars['code']) < 0 ) {
            $msg_title = 'Whoops ' . $resp_codes[$vars['code']];
            $msg_class = 'subscribe-error';
        }
        else {
            $msg_title = 'Success';
            $msg_class = '';
        }

        $msg_body = $this->get_message_body( $vars, $resp_codes[$vars['code']] );
        
        $htm  = '<div class="subscribe-feedback">';
        $htm .= sprintf('<span class="%s feedback-result">%s</span><br />', $msg_class, $msg_title );
        $htm .= $msg_body;
        $htm .= '</div>';
        return $htm;
    }
    
    function get_message_title( $type ) {
        return 'title for: '. $type;
    }
    
    function get_message_body( $vars, $msg ) {
        switch ($vars['code']) {
            case '1':
                return $this->success_subscribed( $vars['address'] );
                break;
            case '2':
                return $this->success_unsubscribed( $vars['address'] );
                break;
            case '3':
                return $this->success_mailed_link( $vars['address'] );
                break;
            case '-1':
                return $this->error_already_subscribed( $vars['address'] );
                break;
            case '-2':
                return $this->error_not_in_list( $vars['address'] );
                break;
            case '-3':
                return $this->invalid_email( $vars['address'] );
                break;
        }

        return $vars['code'] . ': ' . $msg;
    }

    function success_subscribed( $email_addr ) {
        return "<span class=\"subscribe-email\">{$email_addr}</span> been added to the announcement list.";
        return 'success subscribed';
    }

    function success_unsubscribed( $email_addr ) {
        return "<span class=\"subscribe-email\">{$email_addr}</span> has been removed from the announcement list.";
        return 'success_unsubscribed';
    }

    function success_mailed_link( $email_addr ) {
        return "A confirmation email has been sent to <span class=\"subscribe-email\">{$email_addr}</span>.";
        return 'success_mailed_link';
    }

    function error_already_subscribed( $email_addr ) {
        return "<span class=\"subscribe-email\">{$email_addr}</span> is already on the announcement list.";
        return 'error_already_subscribed';
    }

    function error_not_in_list( $email_addr ) {
        return "<span class=\"subscribe-email\">{$email_addr}</span> is not subscribed to the announcement list.";
        return 'error_not_in_list';
    }

    function invalid_email( $email_addr ) {
        return "<span class=\"subscribe-email\">{$email_addr}</span> is an invalid email address.";
        return 'invalid_email';
    }
    
}
?>