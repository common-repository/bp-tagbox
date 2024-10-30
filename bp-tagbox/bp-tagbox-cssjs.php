<?php

function bp_tagbox_add_js() {
	global $bp;

	if ( $bp->current_component == $bp->tagbox->slug )
		wp_enqueue_script( 'bp-tagbox-js', WP_PLUGIN_URL . '/bp-tagbox/bp-tagbox/js/general.js' );
}
add_action( 'template_redirect', 'bp_tagbox_add_js', 1 );

function bp_tagbox_add_icon_css() { ?>
	<style type="text/css">
		li a#user-bookmarks, li a#my-bookmarks { background: url( <?php echo plugins_url( '/bp-tagbox/bp-tagbox/images/tagbox.png' ) ?> ) no-repeat 88% 52%; padding: 0.55em 3em 0.55em 0 !important; margin-right: 0.85em !important; }
		li#afilter-bookmarks a { background: url( <?php echo plugins_url( '/bp-tagbox/bp-tagbox/images/tagbox.png' ) ?> ) no-repeat; }
	</style>
<?php	
}
add_action( 'wp_footer', 'bp_tagbox_add_icon_css' );

?>