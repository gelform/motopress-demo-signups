<?php
/*
* Plugin Name: Motopress Demo: Signups
* Plugin URI: http://www.getmotopress.com
* Description: View Motopress Demo signups
* Version: 0.0.1
* Author: Gelform (Motopress Demo created by MotoPress)
* Author URI: https://gelform.com (Motopress Demo: http://www.getmotopress.com)
* License: GPLv2 or later
* Text Domain: mp-demo
* Domain Path: /languages
* Network: True
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



function mp_signups_add_admin_menu() {

	add_submenu_page(
		'mp-demo',   //or 'options.php'
		'Signups',
		'Signups',
		'manage_options',
		'mp-signups',
		'mp_signups_render_admin_page'
	);
}

add_action( 'network_admin_menu', 'mp_signups_add_admin_menu', 100 );


function mp_signups_render_admin_page() {
	global $wpdb;

	$statuses = array( 'pending', 'active', 'archived' );

	$where = '';
	if ( isset( $_GET[ 'mp-demo-filter' ] ) && in_array( $_GET[ 'mp-demo-filter' ], $statuses ) ) {

		$status = $_GET[ 'mp-demo-filter' ];

		$where = " WHERE `status` = '$status' ";
	}


	$table_users     = $wpdb->prefix . 'mp_demo_users';
	$table_sandboxes = $wpdb->prefix . 'mp_demo_sandboxes';



	$users = $wpdb->get_results( "
		SELECT * 
		FROM $table_users
		
		JOIN $table_sandboxes
		ON $table_users.user_id = $table_sandboxes.user_id
		
		$where
		
		ORDER BY `creation_date` DESC
	;" );
	?>
	<div class="wrap">
		<h1>Signups</h1>

		<p id="mp-demo-filter">
			<a href="<?php echo remove_query_arg( 'mp-demo-filter' ) ?>">
				All
			</a>

			<?php foreach ( $statuses as $status ) : ?>
				<a href="<?php echo add_query_arg( array( 'mp-demo-filter' => $status ) ) ?>">
					<?php echo $status ?>
				</a>
			<?php endforeach; ?>

		</p>
		<table class="widefat">
			<thead>
			<tr>
				<td>Email</td>
				<td>Link</td>
				<td>Created</td>
				<td>Activated</td>
				<td>Expired</td>
			</tr>
			</thead>
			<tbody>
			<?php $i = 0;
			foreach ( $users as $user ) : ?>
				<tr class="<?php echo $i % 2 == 0 ? 'alternate' : '' ?>">
					<td class="col-email signup-<?php echo $user->status ?>" title="<?php echo $user->status ?>">
						<a href="mailto:<?php echo esc_attr( $user->email ) ?>" target="_blank">
							<?php echo $user->email ?>
						</a>
						<?php if ( ! empty( $user->wp_user_id ) ) : ?>
						<a href="<?php echo sprintf( '%s?user_id=%s', admin_url( 'user-edit.php' ), $user->wp_user_id ) ?>">
							<span class="dashicons dashicons-edit"></span>
							<?php endif ?>
					</td>
					<td>
						<?php if ( $user->status == 'pending' ) : ?>
							<a href="<?php echo sprintf( '%s?demo-access=%s', site_url(), $user->secret ) ?>"
							   target="_blank" onclick="return false;"
							   title="Copy link only! Right click, Copy link address">
								<span class="dashicons dashicons-admin-page"></span>
							</a>
						<?php elseif ( ! empty( $user->site_url ) && $user->status != 'archived' ) : ?>
							<a href="<?php echo $user->site_url ?>" target="_blank">
								<span class="dashicons dashicons-external"></span>
							</a>
						<?php endif ?>
					</td>
					<td><?php echo mp_signups_format_date( $user->creation_date ) ?></td>
					<td><?php echo mp_signups_format_date( $user->activation_date ) ?></td>
					<td><?php echo mp_signups_format_date( $user->expiration_date ) ?></td>
				</tr>
				<?php $i ++; endforeach; // users ?>
			</tbody>
		</table>

	</div><!--wrap-->


	<style>
		#mp-demo-filter a {
			display: inline-block;
			margin-right: 1em;
		}

		.col-email {
			border-left: 10px solid transparent;
		}

		.signup-deleted {
			border-color: black;
		}

		.signup-archived {
			border-color: #999;
		}

		.signup-pending {
			border-color: yellow;
		}

		.signup-active {
			border-color: Chartreuse;
		}
	</style>
	<?php
}


function mp_signups_format_date( $datestr ) {
	if ( empty( $datestr ) || substr( $datestr, 0, 4 ) == '0000' ) {
		return '';
	}

	$difference = human_time_diff( time(), strtotime( $datestr ) );

	return time() > strtotime( $datestr ) ? sprintf( '%s ago', $difference ) : sprintf( 'in %s', $difference );
}