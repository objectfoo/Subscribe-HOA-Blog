<?php
class ShortCode_SHB {
	static $need_assets;
	
	
	function __construct($admin) {
		$this->admin = $admin;
	}
	
	function init() {
		add_shortcode( 'announcelist',  array( $this, 'replace_shortcode') );
		add_action( 'init',             array( $this, 'enqueue_assets' ) );
		
		// can't dequeue stylesheets cause they don't print in the footer :(
		// add_action( 'the_post',					array( $this, 'post_has_shortcode' ) );
		// add_action( 'wp_print_footer_scripts',	array( $this, 'deregister_assets_check'), 1 );
	}
	
	function replace_shortcode() {
		// replace the shortcode with the subscribe / unsubscribe form
		$opts = $this->admin->getOptions();
		if( $opts[SHB_ANNOUNCE_KEY] ) {
		?>
		<div id="subscribe-hoa">
			<div class="hd">
				<p><?php echo bloginfo( 'name' ) ?> Announcement List Subscription</p>
			</div>
			
			<form method="post" action="http://scripts.dreamhost.com/add_list.cgi">
				<input type="hidden" name="list" value="<?php echo $opts[SHB_LIST_KEY] ?>" id="list" />
				<input type="hidden" name="domain" value="<?php echo $opts[SHB_DOMAIN_KEY] ?>" id="domain" />
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
}
?>