<?php
class Admin_SHB {

    function __construct() {}

    function init() {
        $this->getOptions();
        add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );
        add_action( 'admin_init', array( $this, 'setup_options' ) );
    }
    
    function install() {
        $this->getOptions();
    }


    // set default and get options
    function getOptions() {
        $defaults = array(
            SHB_API_KEY         => '',
            SHB_LIST_KEY        => '',
            SHB_UUID_KEY        => '',
            SHB_ANNOUNCE_KEY    => 0
        );
        
        $opts = get_option( SHB_OPTIONS_KEY );
        if( !empty($opts) ) {
            foreach( $opts as $key => $val )  {
                $defaults[$key] = $val;
            }
        }
        update_option( SHB_OPTIONS_KEY, $defaults );
        $defaults[SHB_DOMAIN_KEY] = SHB_DOMAIN_NAME;
        
        return $defaults;
    }
    
    // add Subscribe HOA menu to Admin > Settings menu
    function add_settings_menu() {
        add_options_page(
            'Subscribe HOA Options',            // page title
            'Subscribe HOA',                    // menu title
            'manage_options',                   // visible to capability
            SHB_FILE,                           // menu slug
            array( $this, 'render_admin_page' ) // render page method
        );
    }
    
    // render administration page
    function render_admin_page() {
        ?>
        <div class="wrap">
            <?php screen_icon() ?>
            <h2>Dreamhost Announce List</h2>
            <form action="options.php" method="post">
            <?php settings_fields( SHB_OPTIONS_KEY ) ?>
            <?php do_settings_sections( SHB_FILE ) ?>
            <p class="submit">
                <input type="submit" class="button-primary" name="Submit" value="<?php esc_attr_e('Save Changes') ?>" />
            </p>
            </form>
        </div>
        <?php
    }
    
    // setup options via settings api
    function setup_options() {
        register_setting( SHB_OPTIONS_KEY, SHB_OPTIONS_KEY, Array( $this, 'validate') );
        add_settings_section( 'main', 'Settings', array($this, 'print_section_text'), SHB_FILE );
        
        add_settings_field( SHB_API_KEY,     'API Key',            array($this, 'print_api_key_input'),     SHB_FILE, 'main' );
        add_settings_field( SHB_LIST_KEY,    'List Name',          array($this, 'print_list_name_input'),   SHB_FILE, 'main' );
        add_settings_field( SHB_UUID_KEY,    'UUID Prefix',        array($this, 'print_UUID_prefix_input'), SHB_FILE, 'main' );
        add_settings_field( SHB_ANNOUNCE_KEY,'Send Announcements', array($this, 'print_announce_key_input'),SHB_FILE, 'main' );
        
    }

    // validate
    function validate( $opts ) {
        $re_api_key = '/^[\w\-]{16}$/';

        $valid = array();
        $valid[SHB_API_KEY]         = '';
        $valid[SHB_LIST_KEY]        = '';
        $valid[SHB_UUID_KEY]        = '';
        $valid[SHB_ANNOUNCE_KEY]    = 0;

        // API_KEY
        $opts[SHB_API_KEY] = trim( $opts[SHB_API_KEY] );
        if( preg_match( $re_api_key, $opts[SHB_API_KEY] ) ) {
            $valid[SHB_API_KEY] = $opts[SHB_API_KEY];
        }
        else if( $opts[SHB_API_KEY] != '' ) {
            add_settings_error( SHB_OPTIONS_KEY, 'myerr', 'Dreamhost API key: not 16 char alphanumeric value' );
        }

        // LIST_KEY
        $opts[SHB_LIST_KEY] = trim( $opts[SHB_LIST_KEY] );
        if( preg_match( '/^[\w\-]+$/', $opts[SHB_LIST_KEY] ) ) {
            $valid[SHB_LIST_KEY] = $opts[SHB_LIST_KEY];
        }
        else if( $opts[SHB_LIST_KEY] != '' ) {
            add_settings_error( SHB_LIST_KEY, 'myerr', 'Announce list name: not alphanumeric' );
        }
        
        // UUID_KEY
        $opts[SHB_UUID_KEY] = trim( $opts[SHB_UUID_KEY] );
        if( preg_match( '/^[a-zA-Z0-9\-\_]+$/', $opts[SHB_UUID_KEY] ) ) {
            $valid[SHB_UUID_KEY] = $opts[SHB_UUID_KEY];
        }
        else if( $opts[SHB_UUID_KEY] != '' ) {
            add_settings_error( SHB_UUID_KEY, 'myerr', 'UUID prefix: not alphanumeric' );
        }

        // ANNOUNCE_KEY
        $ann_err = array();
        if( isset( $opts[SHB_ANNOUNCE_KEY] ) && $opts[SHB_ANNOUNCE_KEY] == 1 ) {
            if( $valid[SHB_API_KEY] == '' )
                $ann_err[] = 'Dreamhost API key required';

            if( $valid[SHB_LIST_KEY] == '' )
                $ann_err[] = 'List Name required';
            
            if( $valid[SHB_UUID_KEY] == '' )
                $ann_err[] = 'UUID Prefix required';

            // if all good
            if( count( $ann_err ) == 0 ) {
                $valid[SHB_ANNOUNCE_KEY] = 1;
            } else {
                add_settings_error(
                    SHB_ANNOUNCE_KEY,
                    'myerr',
                    'Send Announcements Error:<br /><blockquote>' . 
                        implode( '<br>', $ann_err ) .
                        '</blockquote>'
                );
            }
        }
        return $valid;
    }
        
    // section_text
    function print_section_text() {
        echo '<p>Required settings if using the Dreamhost\'s Announce List API</p>' .
        '<p>UUID is a unique number to stop duplicate commands</p>';
    }
        
    // API key input field
    function print_api_key_input() {
        $opts = get_option( SHB_OPTIONS_KEY );
        printf( '<input id="%1$s" name="%2$s[%1$s]" size="40" type="text" value="%3$s" />',
            SHB_API_KEY,
            SHB_OPTIONS_KEY,
            $opts[SHB_API_KEY] );
        echo '<span class="description">16 characters, Alphanumeric</span>';
    }
    
    function print_list_name_input() {
        $opts = get_option( SHB_OPTIONS_KEY );
        printf('<input id="%1$s" name="%2$s[%1$s]" size="40" type="text" value="%3$s" />',
            SHB_LIST_KEY,
            SHB_OPTIONS_KEY,
            $opts[SHB_LIST_KEY]
        );
        echo '<span class="description">Alphanumeric</span>';
    }

    function print_UUID_prefix_input() {
        $opts = get_option( SHB_OPTIONS_KEY );
        printf('<input id="%1$s" name="%2$s[%1$s]" size="40" type="text" value="%3$s" />',
            SHB_UUID_KEY,
            SHB_OPTIONS_KEY,
            $opts[SHB_UUID_KEY]
        );
        echo '<span class="description">Alphanumeric</span>';
    }

    function print_announce_key_input() {
        $opts = get_option( SHB_OPTIONS_KEY );
        if (!isset($opts[SHB_ANNOUNCE_KEY])) {
            $opts[SHB_ANNOUNCE_KEY] = 0;
        }
        printf('<input id="%1$s" name="%2$s[%1$s]" type="checkbox" value="1" %3$s />',
            SHB_ANNOUNCE_KEY,
            SHB_OPTIONS_KEY,
            checked( 1, $opts[SHB_ANNOUNCE_KEY], false )
        );
    }
}

?>