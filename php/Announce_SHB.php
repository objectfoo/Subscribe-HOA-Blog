<?php
class Announce_SHB {
	
	const URI = 'https://api.dreamhost.com/?key=%1$s&format=%2$s&unique_id=%3$s&cmd=%4$s&listname=%5$s&domain=%6$s&subject=%7$s&message=%8$s&type=%9$s&duplicate_ok=0';

	function __construct( $admin ) {
		$this->admin = $admin;
		
		add_action( 'transition_post_status', array( $this, 'announce_post'), 10, 3 );
	}

	function announce_post( $new_status, $old_status, $this_post ) {
		$opts = $this->admin->getOptions();
		if( $opts[SHB_ANNOUNCE_KEY] && 
			'publish' == $new_status &&
			'publish' != $old_status )
		{
			$opts       = $this->admin->getOptions();
			$uuid       = uniqid( $opts[SHB_UUID_KEY] );
			$subject    = get_bloginfo( 'name' ). ': ' . $this_post->post_title;
			$subject    = urlencode( $subject );
			
			if( SHB_MAIL_TYPE == 'text' ) {
				$link = "Log in to read more: ";
				$link .= $this_post->guid;
			} else {
				$link = sprintf( '<a href="%1$s">%2$s</a>',
									$this_post->guid,
									'Log in to read more &raquo;' );
			}

			$message = $this_post->post_content;
			$message = strip_shortcodes( $message );
			$message = str_replace(']]>', ']]&gt;', $message);
			$message = strip_tags( $message );

			$words = preg_split(
				"/[\n\r\t ]+/",
				$message,
				55 + 1,
				PREG_SPLIT_NO_EMPTY
			);
			if ( count( $words ) > 55 ) {
				array_pop( $words );
				$message = implode( ' ', $words );
			} 
			else {
				$message = implode( ' ', $words );
			}
			
			$message = $message . "\n\r" . $link;
			$message = urlencode($message);
			
			$uri = sprintf( self::URI,
				$opts[SHB_API_KEY],						// api key
				'json',									// response format
				$uuid,									// uuid
				'announcement_list-post_announcement',	// cmd
				$opts[SHB_LIST_KEY],					// list name
				SHB_DOMAIN,								// domain
				$subject,								// email subject 
				$message,								// message
				SHB_MAIL_TYPE							// email type html ||text
			);

			$dh_response = wp_remote_get( $uri, array( 'sslverify' => false ) );
			// print_r($dh_response);
		}
		
	}
}
?>