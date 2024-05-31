<?php
/**
 * Fired during plugin activation and loading.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */

// If this file is called directly, abort.
if(!defined('WPINC')) {
	die;
}

if(!class_exists( 'EasyCustomJsAndCss_Activator')) :

class EasyCustomJsAndCss_Activator {
	public function activate() {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		global $wpdb;
		
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
		$sql = 'CREATE TABLE ' . $table . ' (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title text COLLATE utf8_unicode_ci DEFAULT NULL,
			data longtext COLLATE utf8_unicode_ci DEFAULT NULL,
			type varchar(8) NOT NULL DEFAULT "",
			active tinyint NOT NULL DEFAULT 1,
			priority int(11) UNSIGNED NOT NULL DEFAULT 0,
			options text COLLATE utf8_unicode_ci DEFAULT NULL,
			author bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
			modified datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
			UNIQUE KEY id (id)
		);';
		dbDelta($sql);
		
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
		$sql = 'CREATE TABLE ' . $table . ' (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title text COLLATE utf8_unicode_ci DEFAULT NULL,
			data longtext COLLATE utf8_unicode_ci DEFAULT NULL,
			author bigint(20) UNSIGNED NOT NULL DEFAULT 0,
			date datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
			modified datetime NOT NULL DEFAULT "0000-00-00 00:00:00",
			UNIQUE KEY id (id)
		);';
		dbDelta($sql);
		
		update_option(EASYJC_PLUGIN_NAME . '_db_version', EASYJC_DB_VERSION, false);
		
		$this->update_data();
		
		if( get_option(EASYJC_PLUGIN_NAME . '_activated') == false ) {
			$this->install_data();
		}
		
		update_option(EASYJC_PLUGIN_NAME . '_activated', time(), false);
	}
	
	public function update_data() {
		global $wpdb;
		
		//===========
		// Add support Emoji
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
		
		$sql = 'ALTER TABLE ' . $table . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE ' . $table . ' MODIFY title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE ' . $table . ' MODIFY data longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE ' . $table . ' MODIFY type varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE ' . $table . ' MODIFY options text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		//===========
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
		
		$sql = 'ALTER TABLE ' . $table . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE ' . $table . ' MODIFY title text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
		
		$sql = 'ALTER TABLE ' . $table . ' MODIFY data longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		$wpdb->query($sql);
	}
	
	public function install_data() {
	}
	
	public function check_db() {
		if ( get_option(EASYJC_PLUGIN_NAME . '_db_version') != EASYJC_DB_VERSION ) {
			$this->activate();
		}
	}
}

endif;