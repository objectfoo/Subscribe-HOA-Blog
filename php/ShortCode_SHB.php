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
            $dh_response_page = get_query_var( 'dh_response_page' );
            $response_data = array(
              'address' => get_query_var( 'address' ),
              'name'    => get_query_var( 'name' ),
              'code'    => get_query_var( 'code' ),
              'type'    => $dh_response_page
            );

             if( empty($dh_response_page) ) {
                 // not a dreamhost response
                 $this->render_subcribe_form();
             } else {
                 // dreamhost response page
                $this->render_placeholder_response( $response_data );

                
                 // subscribed: confirmation email has been sent
                 // already_subscribed: goodbye page
                 // already_on: you are already on the list
                 // not_subscribed: you are not on the list
                 // invalid_email: malformed email address
                 // welcome: Email confirm (they just clicked the confirm link in their email)
                 
                 
                 
             }

        }
    }
    
    function enqueue_assets() {
        // enqueue scripts & styles for shortcode
        // print scripts in footer so you can dequeue them in wp_print_footer_scripts
        // (which happens after the short code has been swapped out)
        wp_enqueue_style ( 'subscribe-hoa', SHB_DIR.'assets/style.css', false, 1.0, 'all' );
        
    }
    
    function post_has_shortcode( $post ) {
        // set need_assets if post has shortcut
        if( preg_match( '/\[announcelist\]/', $post->post_content) ) {
            self::$need_assets = true;
        } else {
            self::$need_assets = false;
        }
    }
    
    function deregister_assets_check() {
        // if ! $need_assets deregister scripts/styles
        if( !self::$need_assets ) {
            wp_dequeue_style( 'subscribe-hoa' );
        }
    }
    
    function add_query_vars( $vars ) {
        $vars[] = 'dh_response_page';
        $vars[] = 'address';
        $vars[] = 'name';
        $vars[] = 'code';
        return $vars;
    }

    // render subscription form
    function render_subcribe_form() {
        $opts = $this->admin->getOptions();
        ?>
        <div id="subscribe-hoa">
            <div class="hd">
                <p><?php echo bloginfo( 'name' ) ?> Announcement List Subscription</p>
            </div>
        
            <form method="post" action="http://scripts.dreamhost.com/add_list.cgi">
                <input type="hidden" name="list" value="<?php echo $opts[SHB_LIST_KEY] ?>" id="list" />
                <input type="hidden" name="domain" value="<?php echo $opts[SHB_DOMAIN_KEY] ?>" id="domain" />

                <input type="hidden" name="url" value="<?php          echo get_permalink() .'/?dh_response_page=subscribed' ?>" /> 
                <input type="hidden" name="unsuburl" value="<?php     echo get_permalink() .'/?dh_response_page=unsubscribed' ?>" /> 
                <input type="hidden" name="alreadyonurl" value="<?php echo get_permalink() .'/?dh_response_page=already_subscribed' ?>" /> 
                <input type="hidden" name="notonurl" value="<?php     echo get_permalink() .'/?dh_response_page=not_subscribed' ?>" /> 
                <input type="hidden" name="invalidurl" value="<?php   echo get_permalink() .'/?dh_response_page=invalid_email' ?>" /> 
                <?php
                /*
                don't think I need this one, it's set via the webpanel
                <input type="hidden" name="emailconfirmurl" value="<?php echo get_permalink() .'/?dh_response_page=confirm' ?>" />
                */ ?>
                

                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="left"><label for="email">E-mail:</label> <input type="text" name="email" value="" id="email" /></td>
                        <td><input type="submit" name="submit" value="Subscribe" id="submit" /></td>
                        <td><input type="submit" name="unsub" value="Unsubscribe" id="unsub"></td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
    }
    
    // render placeholder response page
    function render_placeholder_response( $response_data ) {
        ?>
        <div id="subscribe-hoa">
            <p>Response Page: <?php echo $response_data['type'] ?></p>
        </div>
        <?php
    }

}
?>