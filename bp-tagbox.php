<?php
/*
Plugin Name: BP-TagBox
Plugin URI: http://www.wptoolbox.de/plugins/2009/07/tagbox-plugin-fuer-buddypress/
Description: BP-TagBox erweitert den Profilbereich der BuddyPress-User um die M&ouml;glichkeit, die beim <a href="http://www.tagbox.de" target="_blank" title="Social Bookmarks Dienst TagBox">Social Bookmarks Dienst TagBox</a> abgespeicherten Favoriten zu importieren. In den Einstellungen des Nutzerprofils hat man die M&ouml;glichkeit, TagBox-Benutzernamen und die gew&uuml;nschte Anzahl der Favoriten zu definieren.
Version: 1.0
Revision Date: 01.10.2009
Requires at least: WPMU 2.7.1, BuddyPress 1.0.2
Tested up to: WPMU 2.8.4, BuddyPress 1.1
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: Yaway Media
Author URI: http://www.yaway.de
Site Wide Only: true
*/

define ( 'BP_TAGBOX_IS_INSTALLED', 1 );
define ( 'BP_TAGBOX_VERSION', '1.0' );
define ( 'BP_TAGBOX_DB_VERSION', '1.0' );

if ( !defined( 'BP_TAGBOX_SLUG' ) )
	define ( 'BP_TAGBOX_SLUG', 'bookmarks' );

require ( 'bp-tagbox/bp-tagbox-cssjs.php' );

function bp_tagbox_install() {
	global $wpdb, $bp;
	
	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	
	$sql[] = "CREATE TABLE {$bp->tagbox->table_name} (
		  		id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  		field_1 bigint(20) NOT NULL,
		  		field_2 bigint(20) NOT NULL,
		  		field_3 bool DEFAULT 0,
				field_4 bigint(20) NOT NULL,
			    KEY field_1 (field_1),
			    KEY field_2 (field_2)
		 	   ) {$charset_collate};";

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );
	
	update_site_option( 'bp-tagbox-db-version', BP_TAGBOX_DB_VERSION );
}

function bp_tagbox_setup_globals() {
	global $bp, $wpdb;
	
	$bp->tagbox->table_name = $wpdb->base_prefix . 'bp_tagbox';
	$bp->tagbox->image_base = WP_PLUGIN_URL . '/bp-tagbox/images';
	$bp->tagbox->slug = BP_TAGBOX_SLUG;
	$bp->version_numbers->tagbox = BP_TAGBOX_VERSION;
}
add_action( 'plugins_loaded', 'bp_tagbox_setup_globals', 5 );	
add_action( 'admin_menu', 'bp_tagbox_setup_globals', 1 );


function bp_tagbox_check_installed() {	
	global $wpdb, $bp;

	if ( !is_site_admin() )
		return false;
	

	if ( get_site_option('bp-tagbox-db-version') < BP_TAGBOX_DB_VERSION )
		bp_tagbox_install();
}
add_action( 'admin_menu', 'bp_tagbox_check_installed' );

function bp_tagbox_setup_nav() {
	global $bp;
	
	bp_core_add_nav_item( 
		__( 'TagBox', 'bp-tagbox' ), 
		$bp->tagbox->slug 
	);
	
	bp_core_add_nav_default( 
		$bp->tagbox->slug, 
		'bp_tagbox_bookmarks', 
		'bookmarks' 
	);
	
	$tagbox_link = $bp->loggedin_user->domain . $bp->tagbox->slug . '/';

bp_core_add_subnav_item( 'settings', 'tagbox', __( 'TagBox Einstellungen', 'bp-tagbox' ), $bp->loggedin_user->domain . 'settings/', 'bp_tagbox_screen_settings_menu', false, bp_is_home() );	
	
	if ( $bp->current_component == $bp->tagbox->slug ) {
		if ( bp_is_home() ) {
			$bp->bp_options_title = __( 'Meine TagBox', 'bp-tagbox' );
		} else {
			$bp->bp_options_avatar = bp_core_get_avatar( $bp->displayed_user->id, 1 );
			$bp->bp_options_title = $bp->displayed_user->fullname;
		}
	}
}
add_action( 'wp', 'bp_tagbox_setup_nav', 2 );
add_action( 'admin_menu', 'bp_tagbox_setup_nav', 2 );

function bp_tagbox_bookmarks() {
	global $bp;

	do_action( 'bp_tagbox_bookmarks' );
	bp_core_load_template( 'tagbox/bookmarks' );
	
	 add_action( 'bp_template_content_header', 'bp_tagbox_bookmarks_header' );
	 add_action( 'bp_template_title', 'bp_tagbox_bookmarks_title' );
	 add_action( 'bp_template_content', 'bp_tagbox_bookmarks_content' );
		
	 bp_core_load_template( 'plugin-template' );
}


	function bp_tagbox_bookmarks_header() {
		_e( 'TagBox Bookmarks', 'bp-tagbox' );
	}

	function bp_tagbox_bookmarks_title() {
		bp_word_or_name( __( "Meine TagBox Bookmarks", 'bp-tagbox' ), __( "%s's TagBox Bookmarks", 'bp-tagbox' ) );


	}

	function bp_tagbox_bookmarks_content() {
		global $bp;
		
	?>
	
<?php do_action( 'template_notices' )  ?>

		
		
<?php 
include_once(ABSPATH . WPINC . '/rss.php');
$rss = fetch_rss('http://www.tagbox.de/rss/' .  get_usermeta( $bp->displayed_user->id, 'bp-tagbox-url' )    );
$maxitems = get_usermeta( $bp->displayed_user->id, 'bp-tagbox-count' );
$items = array_slice($rss->items, 0, $maxitems);
?>

<ul>
<?php if (empty($items)) echo '<li>		
<div id="message" class="info">
<p>Keine Bookmarks gefunden.</p>
</div>

</li>';
else
foreach ( $items as $item ) : ?>
<li><a href='<?php echo $item['link']; ?>' 
title='<?php echo $item['title']; ?>'>
<?php echo $item['title']; ?>
</a> <?php echo $item['description']; ?></li>
<?php endforeach; ?>
</ul>
<div class="button-block">
	<div class="generic-button">
		<a href="http://www.tagbox.de/bookmarks/<?php echo get_usermeta( $bp->displayed_user->id, 'bp-tagbox-url' )?>" target="_blank" >Mehr Bookmarks von <?php echo get_usermeta( $bp->displayed_user->id, 'bp-tagbox-url' )?></a>    
	</div>
</div>


<?php
	}

function bp_tagbox_screen_settings_menu() {
	global $bp, $current_user, $bp_settings_updated, $pass_error;

	if ( isset( $_POST['submit'] ) && check_admin_referer('bp-tagbox-admin') ) {
		$bp_settings_updated = true;
		 
		update_usermeta( $bp->loggedin_user->id, 'bp-tagbox-count', attribute_escape( $_POST['bp-tagbox-count'] ) );
		update_usermeta( $bp->loggedin_user->id, 'bp-tagbox-url', attribute_escape( $_POST['bp-tagbox-url'] ) );
	}

	add_action( 'bp_template_content_header', 'bp_tagbox_screen_settings_menu_header' );
	add_action( 'bp_template_title', 'bp_tagbox_screen_settings_menu_title' );
	add_action( 'bp_template_content', 'bp_tagbox_screen_settings_menu_content' );

	bp_core_load_template('plugin-template');
}

	function bp_tagbox_screen_settings_menu_header() {
		_e( 'TagBox Einstellungen', 'bp-tagbox' );
	}

	function bp_tagbox_screen_settings_menu_title() {
		_e( 'TagBox Einstellungen', 'bp-tagbox' );
	}

	function bp_tagbox_screen_settings_menu_content() {
		global $bp, $bp_settings_updated; 
		
		?>

		<?php if ( $bp_settings_updated ) { ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Einstellungen gespeichert.', 'bp-tagbox' ) ?></p>
			</div>
		<?php } ?>
		
		
<div class="left-menu">
<img src="<?php echo $bp->tagbox->image_base = WP_PLUGIN_URL . '/bp-tagbox/bp-tagbox/images'; ?>/tagbox.jpg" alt="TagBox Social Bookmarks" />
Du ben&ouml;tigst einen kostenlosen Account bei TagBox, um eigene Bookmarks importieren zu k&ouml;nnen.
<a href="http://www.tagbox.de/register/" target="_blank">Registriere Dich hier...</a>
<br /><br />
<strong>Tipp:</strong> Du kannst auch den Benutzernamen eines anderen TagBox-Users, dessen Bookmarks Dir gefallen, angeben.
</div>

<div class="main-column">
<div class="info-group">

		<form action="<?php echo $bp->loggedin_user->domain . 'settings/tagbox'; ?>" name="bp-tagbox-admin-form" id="account-delete-form" class="bp-tagbox-admin-form" method="post">
		
		<h4>Benutzername bei TagBox:</h4>
		<input class="settings-input" type="text" name="bp-tagbox-url" id="bp-tagbox-url" value="<?php echo get_usermeta( $bp->loggedin_user->id, 'bp-tagbox-url' ); ?>"  />

		<h4>Anzahl der angezeigten Bookmarks:</h4>
		<select  class="settings-input" name="bp-tagbox-count" id="bp-tagbox-count">
		      <option <?php if ( '5' == get_usermeta( $bp->loggedin_user->id, 'bp-tagbox-count' ) ) : ?> selected<?php endif; ?> value="5">5</option>
		      <option <?php if ( '10' == get_usermeta( $bp->loggedin_user->id, 'bp-tagbox-count' ) ) : ?> selected<?php endif; ?> value="10">10</option>
		      <option <?php if ( '15' == get_usermeta( $bp->loggedin_user->id, 'bp-tagbox-count' ) ) : ?> selected<?php endif; ?> value="15">15</option>
		</select>

		<p class="submit">
			<input type="submit" value="<?php _e( 'Einstellungen speichern', 'bp-tagbox' ) ?> &raquo;" id="submit" name="submit" />
		</p>

		<?php 
		wp_nonce_field( 'bp-tagbox-admin' );
		?>

		</form>
</div>
</div>
	<?php
	}



function bp_tagbox_remove_data( $user_id ) {
	delete_usermeta( $user_id, 'bp_tagbox_some_setting' );
	do_action( 'bp_tagbox_remove_data', $user_id );
}
add_action( 'wpmu_delete_user', 'bp_tagbox_remove_data', 1 );
add_action( 'delete_user', 'bp_tagbox_remove_data', 1 );

?>