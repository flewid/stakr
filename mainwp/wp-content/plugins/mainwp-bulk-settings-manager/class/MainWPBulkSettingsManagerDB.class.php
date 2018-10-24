<?php

class MainWPBulkSettingsManagerDB {
	/**
	 * @var string
	 */
	private $mainwp_bulk_settings_manager_db_version = '0.3';
	/**
	 * @var null
	 */
	private static $instance = null;
	/**
	 * @var string
	 */
	private $table_prefix;

	/**
	 * @return null
	 */
	public static function Instance() {
		if ( MainWPBulkSettingsManagerDB::$instance == null ) {
			MainWPBulkSettingsManagerDB::$instance = new MainWPBulkSettingsManagerDB();
		}

		return MainWPBulkSettingsManagerDB::$instance;
	}

	/**
	 *
	 */
	public function __construct() {
		global $wpdb;
		$this->table_prefix = $wpdb->prefix . "mainwp_";
	}

	/**
	 * @param $suffix
	 *
	 * @return string
	 */
	public function tableName( $suffix ) {
		return $this->table_prefix . $suffix;
	}

	/**
	 * @return bool
	 *
	 * Plugin instalation
	 * Return true on success
	 */
	public function install() {
		global $wpdb;
		$currentVersion = get_site_option( 'mainwp_bulk_settings_manager_db_version' );

		if ( $currentVersion == $this->mainwp_bulk_settings_manager_db_version ) {
			// No migrations right now
			return true;
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = array();

		// We are using LONGTEXT instead of text because text can contain only 65,535 bytes
		$sql[] = 'CREATE TABLE `' . $this->tableName( 'bulk_settings_manager_entries' ) . '` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`key_ring` int(11) NOT NULL DEFAULT 0,
			`name` VARCHAR(255) NOT NULL,
			`url` MEDIUMTEXT NOT NULL,
			`settings` LONGTEXT NOT NULL,
			`created_time` int(11) NOT NULL DEFAULT 0,
			`edited_time` int(11) NOT NULL DEFAULT 0,
			`note` MEDIUMTEXT NOT NULL,
			PRIMARY KEY  (`id`)
			)' . $charset_collate;

		$sql[] = 'CREATE TABLE `' . $this->tableName( 'bulk_settings_manager_previews' ) . '` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`entry_id` int(11) NOT NULL DEFAULT 0,
			`website_id` int(11) NOT NULL DEFAULT 0,
			`secret` VARCHAR(255) NOT NULL,
			`content` LONGTEXT NOT NULL,
			`params` MEDIUMTEXT NOT NULL,
			`created_time` int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY  (`id`)
			)' . $charset_collate;

		$sql[] = 'CREATE TABLE `' . $this->tableName( 'bulk_settings_manager_rings' ) . '` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(255) NOT NULL,
			`note` MEDIUMTEXT NOT NULL,
			PRIMARY KEY  (`id`)
			) AUTO_INCREMENT=2 ' . $charset_collate;

		$sql[] = 'CREATE TABLE `' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . '` (
			`ring_id` int(11) NOT NULL DEFAULT 0,
			`entry_id` int(11) NOT NULL DEFAULT 0
			)' . $charset_collate;

		$sql[] = 'ALTER TABLE ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' ADD INDEX(`ring_id`, `entry_id`)';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ( $sql as $query ) {
			dbDelta( $query );
		}

		$wpdb->query( "INSERT IGNORE INTO " . $this->tableName( 'bulk_settings_manager_rings' ) . " (`id`, `name`, `note`) VALUES (1, 'No Key Ring', 'Default Key Ring')" );

		update_option( 'mainwp_bulk_settings_manager_db_version', $this->mainwp_bulk_settings_manager_db_version );

		return true;
	}

	/**
	 * @return bool
	 */
	public function uninstall() {
		// Nothing here right now
		global $wpdb;

		// $wpdb->query( 'DROP TABLE IF EXISTS ' . $this->tableName( 'bulk_settings_manager_entries' ) );

		// $wpdb->query( 'DROP TABLE IF EXISTS ' . $this->tableName( 'bulk_settings_manager_previews' ) );

		// $wpdb->query( 'DROP TABLE IF EXISTS ' . $this->tableName( 'bulk_settings_manager_rings' ) );

		// $wpdb->query( 'DROP TABLE IF EXISTS ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) );

		return true;
	}

	/**
	 * @param $id
	 *
	 * @return array|null|object|void
	 *
	 * Get form entry by id
	 */
	public function get_entry_by_id( $id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' WHERE `id` = %d', $id ), OBJECT );
	}

	/**
	 * @param $ids
	 *
	 * @return array|null|object
	 *
	 * Get entries names by ids array
	 */
	public function get_entries_id_name_by_id( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->get_results( 'SELECT `id`, `name` FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' WHERE `id` IN (' . implode( ", ", $ids ) . ')', OBJECT );
	}

	/**
	 * @param $keyrings
	 *
	 * @return array|null|object
	 *
	 * Get entries names by keyring id
	 */
	public function get_entries_id_name_by_key_ring( $keyrings ) {
		global $wpdb;
		$keyrings = array_map( 'intval', (array) $keyrings );

		return $wpdb->get_results( 'SELECT `id`, `name` FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' as a INNER JOIN ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' as b ON a.id = b.entry_id WHERE b.ring_id IN (' . implode( ", ", $keyrings ) . ')', OBJECT );
	}

	/**
	 * @param $entry_id
	 *
	 * @return array|null|object
	 *
	 * Get keyring by entry id
	 */
	public function get_key_rings_by_entry_id( $entry_id ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( 'SELECT `id`, `name`, (SELECT 1 FROM ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' as a WHERE a.ring_id = b.id and a.entry_id = %d) as `checked` FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' as b', $entry_id ), ARRAY_A );
	}

	/**
	 * @param $ring_id
	 *
	 * @return array|null|object
	 *
	 * Get entries by keyring id
	 */
	public function get_entries_by_key_ring( $ring_id ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( 'SELECT `id`, `name`, `url` FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' as a LEFT JOIN ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' as b ON a.id = b.entry_id WHERE b.ring_id = %d', $ring_id ), ARRAY_A );
	}

	/**
	 * @return array|null|object
	 *
	 * Get all keyrings
	 */
	public function get_key_rings( $limit, $offset, $sort_by, $sort_order, $name ) {
		global $wpdb;

		if ( strlen( $name ) > 1 ) {
			return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' WHERE `name` LIKE %s ORDER BY ' . $sort_by . ' ' . $sort_order . ' LIMIT %d OFFSET %d', '%' . $wpdb->esc_like( $name ) . '%', $limit, $offset ), ARRAY_A );
		} else {
			return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' ORDER BY ' . $sort_by . ' ' . $sort_order . ' LIMIT %d OFFSET %d', $limit, $offset ), ARRAY_A );
		}
	}

	/**
	 * @param $name
	 *
	 * @return array|null|object|void
	 *
	 * Keyrings count for table
	 */
	public function get_key_rings_count( $name ) {
		global $wpdb;

		if ( strlen( $name ) > 1 ) {
			return $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(id) as c FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' WHERE `name` LIKE %s', '%' . $wpdb->esc_like( $name ) . '%' ), ARRAY_A );
		} else {
			return $wpdb->get_row( 'SELECT COUNT(id) as c FROM ' . $this->tableName( 'bulk_settings_manager_rings' ), ARRAY_A );
		}
	}

	/**
	 * @param $limit
	 * @param $offset
	 * @param $sort_by
	 * @param $sort_order
	 *
	 * @return array|null|object
	 *
	 * Get history for table
	 */
	public function get_history( $limit, $offset, $sort_by, $sort_order ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( 'SELECT a.*, b.name, c.url FROM ' . $this->tableName( 'bulk_settings_manager_previews' ) . ' as a INNER JOIN ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' as b ON a.entry_id = b.id INNER JOIN ' . $this->tableName( 'wp' ) . ' as c ON c.id = a.website_id ORDER BY ' . $sort_by . ' ' . $sort_order . ' LIMIT %d OFFSET %d', $limit, $offset ), ARRAY_A );

	}

	/**
	 * @return array|null|object|void
	 *
	 * History count for table
	 */
	public function get_history_count() {
		global $wpdb;

		return $wpdb->get_row( 'SELECT COUNT(id) as c FROM ' . $this->tableName( 'bulk_settings_manager_previews' ), ARRAY_A );
	}


	/**
	 * @param $id
	 *
	 * @return array|null|object|void
	 *
	 * Get keyring by id
	 */
	public function get_key_ring_by_id( $id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' WHERE `id` = %d', $id ), OBJECT );
	}

	/**
	 * @param $ids
	 *
	 * @return array|null|object|void
	 *
	 * Get keyrings by ids array
	 */
	public function get_key_rings_by_ids( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->get_row( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' WHERE `id` IN (' . implode( ", ", $ids ) . ')', OBJECT );
	}

	/**
	 * @param $id
	 * @param $note
	 *
	 * @return false|int
	 *
	 * Update entry note
	 */
	public function update_entry_note( $id, $note ) {
		global $wpdb;

		return $wpdb->update( $this->tableName( 'bulk_settings_manager_entries' ), array(
			'note' => $note
		), array( 'id' => $id ) );
	}

	/**
	 * @param $id
	 * @param $name
	 *
	 * @return false|int
	 *
	 * Update keyring name
	 */
	public function update_keyring_name( $id, $name ) {
		global $wpdb;

		return $wpdb->update( $this->tableName( 'bulk_settings_manager_rings' ), array(
			'name' => $name
		), array( 'id' => $id ) );
	}

	/**
	 * @param $id
	 * @param $note
	 *
	 * @return false|int
	 *
	 * Update keyring note
	 */
	public function update_key_ring_note( $id, $note ) {
		global $wpdb;

		return $wpdb->update( $this->tableName( 'bulk_settings_manager_rings' ), array(
			'note' => $note
		), array( 'id' => $id ) );
	}

	/**
	 * @param $id
	 *
	 * @return array|null|object|void
	 *
	 * Get response returned by child using id
	 */
	public function get_preview_by_id( $id ) {
		global $wpdb;

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_previews' ) . ' WHERE `id` = %d', $id ), OBJECT );
	}

	/**
	 * @param $ids
	 *
	 * @return array|null|object
	 *
	 * Get two or more entries using array of id
	 */
	public function get_entries_by_ids( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->get_results( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' WHERE `id` IN (' . implode( ", ", $ids ) . ')', OBJECT );
	}

	/**
	 * @param $ids
	 *
	 * @return false|int
	 *
	 * Delete form entry using array of id
	 */
	public function delete_entries_by_ids( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->query( 'DELETE FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' WHERE `id` IN (' . implode( ', ', $ids ) . ')' );
	}

	/**
	 * @param $ids
	 *
	 * @return false|int
	 *
	 * Delete keyrings by ids array
	 */
	public function delete_keyrings_by_ids( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->query( 'DELETE FROM ' . $this->tableName( 'bulk_settings_manager_rings' ) . ' WHERE `id` IN (' . implode( ', ', $ids ) . ')' );
	}

	/**
	 * @param $ids
	 *
	 * @return false|int
	 *
	 * Delete connection between entry and keyring by entry ids array
	 */
	public function delete_entries_from_key_ring( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->query( 'DELETE FROM ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' WHERE `entry_id` IN (' . implode( ', ', $ids ) . ')' );
	}

	/**
	 * @param $ids
	 *
	 * @return false|int
	 *
	 * Delete connection between entry and keyring by ring ids array
	 */
	public function delete_keyrings_conntections_by_ids( $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', (array) $ids );

		return $wpdb->query( 'DELETE FROM ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' WHERE `ring_id` IN (' . implode( ', ', $ids ) . ')' );
	}

	/**
	 * @return false|int
	 *
	 * Delete form preview by id
	 */
	public function delete_all_preview() {
		global $wpdb;

		return $wpdb->query( 'DELETE FROM ' . $this->tableName( 'bulk_settings_manager_previews' ) );
	}

	/**
	 * @param $entry_id
	 *
	 * @return false|int
	 *
	 * Delete connection between entry and keyring by entry id
	 */
	public function delete_key_ring_to_entry_by_entry_id( $entry_id ) {
		global $wpdb;

		return $wpdb->delete( $this->tableName( 'bulk_settings_manager_rings_to_entry' ), array( 'entry_id' => $entry_id ), array( '%d' ) );
	}

	/**
	 * @param $entry_id
	 * @param $ring_id
	 *
	 * @return false|int
	 *
	 * Delete connection between entry and keyring using entry id and keyring id
	 */
	public function delete_key_ring_to_entry_by_entry_id_and_keyring_id( $entry_id, $ring_id ) {
		global $wpdb;

		return $wpdb->delete( $this->tableName( 'bulk_settings_manager_rings_to_entry' ), array(
			'entry_id' => $entry_id,
			'ring_id'  => $ring_id
		), array( '%d', '%d' ) );
	}

	/**
	 * @return array|null|object
	 *
	 * Get id, name, url for all entries
	 * Used in ajax table
	 */
	public function get_id_name_url_from_all_entries( $limit, $offset, $sort_by, $sort_order, $name ) {
		global $wpdb;

		if ( strlen( $name ) > 1 ) {
			return $wpdb->get_results( $wpdb->prepare( 'SELECT `id`, `name`, `url`, `created_time`, `edited_time` FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' WHERE `name` LIKE %s ORDER BY ' . $sort_by . ' ' . $sort_order . ' LIMIT %d OFFSET %d', '%' . $wpdb->esc_like( $name ) . '%', $limit, $offset ), ARRAY_A );
		} else {
			return $wpdb->get_results( $wpdb->prepare( 'SELECT `id`, `name`, `url`, `created_time`, `edited_time` FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' ORDER BY ' . $sort_by . ' ' . $sort_order . ' LIMIT %d OFFSET %d', $limit, $offset ), ARRAY_A );
		}
	}

	/**
	 * @param $name
	 *
	 * @return array|null|object|void
	 *
	 * Entries count for table
	 */
	public function get_all_entries_count( $name ) {
		global $wpdb;

		if ( strlen( $name ) > 1 ) {
			return $wpdb->get_row( $wpdb->prepare( 'SELECT COUNT(id) as c FROM ' . $this->tableName( 'bulk_settings_manager_entries' ) . ' WHERE `name` LIKE %s', '%' . $wpdb->esc_like( $name ) . '%' ), ARRAY_A );
		} else {
			return $wpdb->get_row( 'SELECT COUNT(id) as c FROM ' . $this->tableName( 'bulk_settings_manager_entries' ), ARRAY_A );
		}
	}

	/**
	 * @param $entry_id
	 *
	 * @return array|null|object|void
	 *
	 * Check in which keyring given entry is
	 */
	public function get_key_rings_ids_by_entry_id( $entry_id ) {
		global $wpdb;

		return $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $this->tableName( 'bulk_settings_manager_rings_to_entry' ) . ' WHERE `entry_id` = %d', $entry_id ), OBJECT );
	}

	/**
	 * @param $name
	 * @param $url
	 * @param $settings
	 *
	 * @return false|int
	 *
	 * Insert single form entry
	 */
	public function insert_entry( $name, $url, $settings ) {
		global $wpdb;

		$insert_time = time();

		return $wpdb->insert( $this->tableName( 'bulk_settings_manager_entries' ), array(
			'name'         => $name,
			'url'          => $url,
			'settings'     => $settings,
			'created_time' => $insert_time,
			'edited_time'  => $insert_time
		), array( '%s', '%s', '%s' ) );
	}

	/**
	 * @param $secret
	 * @param $content
	 * @param $params
	 * @param $entry_id
	 *
	 * @return false|int
	 *
	 * Insert single form preview
	 */
	public function insert_preview( $secret, $content, $params, $entry_id, $website_id ) {
		global $wpdb;

		return $wpdb->insert( $this->tableName( 'bulk_settings_manager_previews' ), array(
			'secret'       => $secret,
			'content'      => $content,
			'params'       => $params,
			'entry_id'     => $entry_id,
			'website_id'   => $website_id,
			'created_time' => time()
		), array( '%s', '%s', '%s', '%d', '%d', '%d' ) );
	}

	/**
	 * @param $name
	 *
	 * @return false|int
	 *
	 * Insert single keyring
	 */
	public function insert_key_ring( $name ) {
		global $wpdb;

		return $wpdb->insert( $this->tableName( 'bulk_settings_manager_rings' ), array(
			'name' => $name
		), array( '%s' ) );
	}

	/**
	 * @param $ring_id
	 * @param $entry_id
	 *
	 * @return false|int
	 *
	 * Insert single connection between keyring and entry
	 */
	public function insert_key_ring_to_entry( $ring_id, $entry_id ) {
		global $wpdb;

		return $wpdb->insert( $this->tableName( 'bulk_settings_manager_rings_to_entry' ), array(
			'ring_id'  => $ring_id,
			'entry_id' => $entry_id
		), array( '%d', '%d' ) );
	}

	/**
	 * @param $name
	 * @param $url
	 * @param $settings
	 * @param $id
	 *
	 * @return false|int
	 *
	 * Update single form
	 */
	public function update_entry( $name, $url, $settings, $id ) {
		global $wpdb;

		return $wpdb->update( $this->tableName( 'bulk_settings_manager_entries' ), array(
			'name'        => $name,
			'url'         => $url,
			'settings'    => $settings,
			'edited_time' => time()
		), array( 'id' => $id ) );
	}

	/**
	 * @return int
	 *
	 * Get last inserted ID
	 */
	public function get_insert_id() {
		global $wpdb;

		return $wpdb->insert_id;
	}
}