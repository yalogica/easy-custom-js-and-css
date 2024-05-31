<?php

// If this file is called directly, abort.
if(!defined('WPINC')) {
	die;
}

if(!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EasyCustomJsAndCss_List_Table_Filters extends WP_List_Table {
	function __construct() {
		parent::__construct(array(
			'singular'=> EASYJC_PLUGIN_NAME . '_filter',
			'plural' => EASYJC_PLUGIN_NAME . '_filters',
			'ajax' => false
		));
	}
	
	function handle_row_actions( $post, $column_name, $primary ) {
		return '';
	}
	
	function column_default($item, $column_name){
		switch($column_name){
			case 'title':
			case 'author':
			case 'date':
			case 'modified':
			case 'id':
				return $item[$column_name];
			default:
				return print_r($item, true);
		}
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s">',
			/*%1$s*/ $this->_args['singular'],
			/*%2$s*/ $item['id']
		);
	}
	
	function column_title($item) {
		$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);
		
		if(current_user_can('administrator') || get_current_user_id()==$item['author']) {
			$actions = array(
				'edit'      => sprintf('<a href="?page=%s&action=%s&id=%s&">%s</a>', $page, 'edit', $item['id'], __('Edit', EASYJC_PLUGIN_NAME)),
				'copy'      => sprintf('<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', $page, 'duplicate', $item['id'], wp_create_nonce(EASYJC_PLUGIN_NAME), __('Duplicate', EASYJC_PLUGIN_NAME)),
				'delete'    => sprintf('<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">%s</a>', $page, 'delete', $item['id'], wp_create_nonce(EASYJC_PLUGIN_NAME), __('Delete', EASYJC_PLUGIN_NAME)),
			);
			
			return sprintf('<a href="?page=%1$s&action=edit&id=%2$s" class="row-title">%3$s</a> %4$s',
				/*%1$s*/ $page,
				/*%2$s*/ $item['id'],
				/*%3$s*/ $item['title'],
				/*%4$s*/ $this->row_actions($actions)
			);
		} else {
			$actions = array();
			return sprintf('<strong>%1$s</strong>',
				/*%1$s*/ $item['title']
			);
		}
	}
	
	function column_author( $item ) {
		$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
		$args = array(
			'page'   => $page,
			'author' => $item['author']
		);
		$url = add_query_arg($args, 'admin.php');
		
		return sprintf(
			'<a href="%1$s">%2$s</a>',
			/*%1$s*/ esc_url( $url ),
			/*%2$s*/ get_the_author_meta('display_name', $item['author'])
		);
	}
	
	function column_date( $item ) {
		$m_time = mysql2date( __( 'Y/m/d g:i:s a' ), $item['date'] );
		$h_time = mysql2date( __( 'Y/m/d' ), $item['date'] );
		
		return sprintf(
			'<abbr title="%1$s">%2$s</abbr>',
			/*$1%s*/ $m_time,
			/*$2%s*/ $h_time
		);
	}
	
	function column_modified( $item ) {
		$m_time = mysql2date( __( 'Y/m/d g:i:s a' ), $item['modified'] );
		$h_time = mysql2date( __( 'Y/m/d' ), $item['modified'] );
		
		return sprintf(
			'<abbr title="%1$s">%2$s</abbr>',
			/*$1%s*/ $m_time,
			/*$2%s*/ $h_time
		);
	}
	
	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox">',
			'title'     => __('Title', EASYJC_PLUGIN_NAME),
			'author'    => __('Author', EASYJC_PLUGIN_NAME),
			'date'      => __('Date', EASYJC_PLUGIN_NAME),
			'modified'  => __('Modified', EASYJC_PLUGIN_NAME)
		);
		return $columns;
	}
	
	function get_sortable_columns() {
		$columns = array(
			'title'    => array('title',false),
			'author'   => array('author',false),
			'date'     => array('date',false),
			'modified' => array('modified',false)
		);
		return $columns;
	}
	
	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);
		return $actions;
	}
	
	function process_bulk_action() {
		$access = false;
		if( isset($_GET['_wpnonce']) && !empty($_GET['_wpnonce'])) {
			$nonce  = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
			$action = 'bulk-' . $this->_args['plural'];
			
			if(wp_verify_nonce($nonce, $action)) {
				$access = true;
			}
		}
		
		if(!$access) {
			return;
		}
		
		if( 'delete' === $this->current_action() ) {
			global $wpdb;
			$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
			
			$items = filter_input(INPUT_GET, $this->_args['singular'], FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
			foreach($items as $id) {
				$result = false;
				
				$query = $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id=%s', $id);
				$item = $wpdb->get_row($query, OBJECT);
				if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
					$result = $wpdb->delete($table, ['id'=>$id], ['%d']);
				}
			}
		}
	}
	
	function prepare_items() {
		$this->process_bulk_action();
		
		$columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$hidden = array();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		// make sql query
		global $wpdb;
		
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
		$orderby = (isset($_GET['orderby']) ? filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING ) : 'id');
		$order = (isset($_GET['order']) ? filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING ) : 'desc');
		$author = (isset($_GET['author']) ? filter_input( INPUT_GET, 'author', FILTER_SANITIZE_NUMBER_INT ) : NULL);
		$sql = '';
		
		// database operations
		if($author) {
			$sql = 'SELECT * FROM ' . $table . ' WHERE author=' . $author . ' ORDER BY ' . $orderby . ' ' . $order;
		} else {
			$sql = 'SELECT * FROM ' . $table . ' ORDER BY ' . $orderby . ' ' . $order;
		}
		
		$total_items = $wpdb->query($sql);
		
		$this->items = $wpdb->get_results($sql, 'ARRAY_A');
	}
}
?>