<?php
/**
 * Main class and entry point
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if(!class_exists('EasyCustomCssAndJs')) :

class EasyCustomJsAndCss {
	private $pluginBasename = NULL;
	
	private $ajax_action_item_update = NULL;
	private $ajax_action_item_update_status = NULL;
	private $ajax_action_filter_update = NULL;
	private $ajax_action_settings_update = NULL;
	private $ajax_action_settings_get = NULL;
	private $ajax_action_delete_data = NULL;
	
	private $css_admin_header_data = array();
	private $css_admin_footer_data = array();
	private $css_header_data = array();
	private $css_footer_data = array();
	
	private $js_admin_header_data = array();
	private $js_admin_footer_data = array();
	private $js_header_data = array();
	private $js_footer_data = array();
	
	private $html_admin_header_data = array();
	private $html_admin_footer_data = array();
	private $html_header_data = array();
	private $html_footer_data = array();
	
	public function __construct($pluginBasename) {
		$this->pluginBasename = $pluginBasename;
	}
	
	public function run() {
		$upload_dir = wp_upload_dir();
		
		define('EASYJC_PLUGIN_UPLOAD_DIR', wp_normalize_path($upload_dir['basedir'] . '/' . EASYJC_PLUGIN_NAME));
		define('EASYJC_PLUGIN_UPLOAD_URL', $upload_dir['baseurl'] . '/' . EASYJC_PLUGIN_NAME);
		
		define('EASYJC_PLUGIN_PLAN', 'lite');
		
		if ( is_admin() ) {
			$this->ajax_action_filter_update = EASYJC_PLUGIN_NAME . '_ajax_filter_update';
			$this->ajax_action_filter_get = EASYJC_PLUGIN_NAME . '_ajax_filter_get';
			$this->ajax_action_item_update = EASYJC_PLUGIN_NAME . '_ajax_item_update';
			$this->ajax_action_item_update_status = EASYJC_PLUGIN_NAME . '_ajax_item_update_status';
			$this->ajax_action_settings_update = EASYJC_PLUGIN_NAME . '_ajax_settings_update';
			$this->ajax_action_settings_get = EASYJC_PLUGIN_NAME . '_ajax_settings_get';
			$this->ajax_action_delete_data =  EASYJC_PLUGIN_NAME . '_ajax_delete_data';
			
			load_plugin_textdomain(EASYJC_PLUGIN_NAME, false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');
			
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_notices', array($this, 'admin_notices'));
			
			// important, because ajax has another url
			add_action('wp_ajax_' . $this->ajax_action_item_update, array($this, 'ajax_item_update'));
			add_action('wp_ajax_' . $this->ajax_action_item_update_status, array($this, 'ajax_item_update_status'));
			add_action('wp_ajax_' . $this->ajax_action_filter_update, array($this, 'ajax_filter_update'));
			add_action('wp_ajax_' . $this->ajax_action_filter_get, array($this, 'ajax_filter_get'));
			add_action('wp_ajax_' . $this->ajax_action_settings_update, array($this, 'ajax_settings_update'));
			add_action('wp_ajax_' . $this->ajax_action_settings_get, array($this, 'ajax_settings_get'));
			add_action('wp_ajax_' . $this->ajax_action_delete_data, array($this, 'ajax_delete_data'));
			
			$this->prepare_code();
		} else {
			add_action('wp', array($this, 'prepare_code'));
		}
	}
	
	/**
	 * Prepare upload directory
	 */
	function admin_notices() {
		$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
		
		if(!($page===EASYJC_PLUGIN_NAME || 
			 $page===EASYJC_PLUGIN_NAME . '_filters' ||
			 $page===EASYJC_PLUGIN_NAME . '_settings')) {
				 return;
		}
		
		if(!file_exists(EASYJC_PLUGIN_UPLOAD_DIR)) {
			wp_mkdir_p(EASYJC_PLUGIN_UPLOAD_DIR);
		}
		
		if(!file_exists(EASYJC_PLUGIN_UPLOAD_DIR)) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . sprintf(__('The "%s" directory could not be created', EASYJC_PLUGIN_NAME), '<b>' . EASYJC_PLUGIN_NAME . '</b>') . '</p>';
			echo '<p>' . __('Please run the following commands in order to make the directory', EASYJC_PLUGIN_NAME) . '<br>';
			echo '<b>mkdir ' . EASYJC_PLUGIN_UPLOAD_DIR . '</b><br>';
			echo '<b>chmod 777 ' . EASYJC_PLUGIN_UPLOAD_DIR . '</b></p>';
			echo '</div>';
			return;
		}
		
		if(!wp_is_writable(EASYJC_PLUGIN_UPLOAD_DIR)) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<p>' . sprintf(__('The "%s" directory is not writable, therefore the css and js files cannot be saved.', EASYJC_PLUGIN_NAME), '<b>' . EASYJC_PLUGIN_NAME . '</b>') . '</p>';
			echo '<p>' . __('Please run the following commands in order to make the directory', EASYJC_PLUGIN_NAME) . '<br>';
			echo '<b>chmod 777 ' . EASYJC_PLUGIN_UPLOAD_DIR . '</b></p>';
			echo '</div>';
			return;
		}
		
		if(!file_exists(EASYJC_PLUGIN_UPLOAD_DIR . '/' . 'index.php')) {
			$data = '<?php' . PHP_EOL . '// silence is golden' . PHP_EOL . '?>';
			@file_put_contents(EASYJC_PLUGIN_UPLOAD_DIR . '/' . 'index.php', $data);
		}
	}
	
	function compare_strings($operation, $lvalue, $rvalue) {
		switch($operation) {
			case 'contains':          return (strpos($lvalue, $rvalue) !== false); break;
			case 'does_not_contains': return (strpos($lvalue, $rvalue) === false); break;
			case 'start_with':        return (strpos($lvalue, $rvalue) === 0); break;
			case 'end_with':          return (strpos($lvalue, $rvalue) === strlen($lvalue)-strlen($rvalue)); break;
			case 'equals':            return ($lvalue == $rvalue); break;
			case 'does_not_equals':   return ($lvalue != $rvalue); break;
			case 'in_list':           return ((bool) @preg_match('/' . $lvalue . ';|' . $lvalue .'$/', $rvalue)); break;
			case 'not_in_list':       return (!(bool) @preg_match('/' . $lvalue . ';|' . $lvalue .'$/', $rvalue)); break;
			case 'regex':             return ((bool) @preg_match($rvalue, $lvalue)); break;
		}
		return false;
	}
	
	function compare_values($operation, $lvalue, $rvalue) {
		switch($operation) {
			case 'equals':                   return ($lvalue == $rvalue); break;
			case 'does_not_equals':          return ($lvalue != $rvalue); break;
			case 'less_than':                return ($lvalue <  $rvalue); break;
			case 'greater_than':             return ($lvalue >  $rvalue); break;
			case 'less_than_or_equal_to':    return ($lvalue <= $rvalue); break;
			case 'greater_than_or_equal_to': return ($lvalue >= $rvalue); break;
		}
		return false;
	}
	
	function validate_filter_rule_url($filterRule) {
		$value = $filterRule->value;
		
		if($value) {
			$operation = $filterRule->operation;
			$url = strtolower(ltrim($_SERVER['REQUEST_URI'], '/'));
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			switch($operation) {
				case 'contains':          return (strpos($url, $value) !== false); break;
				case 'does_not_contains': return (strpos($url, $value) === false); break;
				case 'start_with':        return (strpos($url, $value) === 0); break;
				case 'end_with':          return (strpos($url, $value) === strlen($url)-strlen($value)); break;
				case 'equals':            return ($url == $value); break;
				case 'does_not_equals':   return ($url != $value); break;
				case 'regex':             return ((bool) @preg_match($value, $url)); break;
			}
		}
		
		return false;
	}
	
	function validate_filter_rule_post__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value && is_singular('post')) {
			$operation = $filterRule->operation;
			$post_title = strtolower(get_the_title());
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $post_title, $value);
		}
		return false;
	}
	
	function validate_filter_rule_page__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value && is_singular('page')) {
			$operation = $filterRule->operation;
			$page_title = strtolower(get_the_title());
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $page_title, $value);
		}
		return false;
	}
	
	function validate_filter_rule_page_template__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value && is_singular('page')) {
			$operation = $filterRule->operation;
			$page_template_filename = get_page_template_slug();
			$page_template = '';
			
			if($page_template_filename !== false) {
				if($page_template_filename === '') {
					$page_template = 'default';
				} else {
					$templates = wp_get_theme()->get_page_templates();
					$page_template = (isset($templates[$page_template_filename]) ? $templates[$page_template_filename] : '');
				}
			}
			
			$page_template = strtolower($page_template);
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $page_template, $value);
		}
		return false;
	}
	
	function validate_filter_rule_device__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/lib/mobile-detect.php' );
			
			$operation = $filterRule->operation;
			$detect = new Mobile_Detect();
			$device = 'desktop';
			if($detect->isTablet()) {
				$device = 'tablet';
			} else if($detect->isMobile()) {
				$device = 'mobile';
			}
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $device, $value);
		}
		return false;
	}
	
	function validate_filter_rule_os__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/lib/browser.php' );
			
			$operation = $filterRule->operation;
			$platform = strtolower((new Browser())->getPlatform());
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $platform, $value);
		}
		return false;
	}
	
	function validate_filter_rule_browser__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/lib/browser.php' );
			
			$operation = $filterRule->operation;
			$browser = strtolower((new Browser())->getBrowser());
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $browser, $value);
		}
		return false;
	}
	
	function validate_filter_rule_logged_in__premium_only($filterRule) {
		$operation = $filterRule->operation;
		
		switch($operation) {
			case 'yes': return  is_user_logged_in(); break;
			case 'no':  return !is_user_logged_in(); break;
		}
		
		return false;
	}
	
	function validate_filter_rule_user__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value && is_user_logged_in()) {
			$operation = $filterRule->operation;
			$user_login = strtolower(wp_get_current_user()->user_login);
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			return $this->compare_strings($operation, $user_login, $value);
		}
		return false;
	}
	
	function validate_filter_rule_role__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value && is_user_logged_in()) {
			$operation = $filterRule->operation;
			$current_user = wp_get_current_user();
			$roles = $current_user->roles;
			$value = strtolower($value);
			
			if($operation == 'regex') {
				$value = '/' . rtrim(ltrim($value, '/'),'/') . '/';
			}
			
			$result = false;
			foreach ($roles as $role) {
				$result = $this->compare_strings($operation, $role, $value);
				if($result) {
					break;
				}
			}
			return $result;
		}
		return false;
	}
	
	function validate_filter_rule_date__premium_only($filterRule) {
		$timezone = new DateTimeZone('UTC');
		$value = DateTime::createFromFormat('d/m/Y', $filterRule->value, $timezone);
		if($value) {
			$operation = $filterRule->operation;
			$today = new DateTime();
			
			return $this->compare_values($operation, $today, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_time__premium_only($filterRule) {
		$timezone = new DateTimeZone('UTC');
		$value = DateTime::createFromFormat('H:s', $filterRule->value, $timezone);
		if($value) {
			$operation = $filterRule->operation;
			$current_time = new DateTime();
			
			return $this->compare_values($operation, $current_time, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_minutes__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			$value = intval($value);
			$operation = $filterRule->operation;
			$minutes = getDate()['minutes'];
			
			return $this->compare_values($operation, $minutes, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_hours__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			$value = intval($value);
			$operation = $filterRule->operation;
			$hours = getDate()['hours'];
			
			return $this->compare_values($operation, $hours, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_day__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			$value = intval($value);
			$operation = $filterRule->operation;
			$day = getDate()['mday'];
			
			return $this->compare_values($operation, $day, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_day_of_week__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			$value = intval($value);
			$operation = $filterRule->operation;
			$day = getDate()['wday'];
			
			return $this->compare_values($operation, $day, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_month__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			$value = intval($value);
			$operation = $filterRule->operation;
			$month = getDate()['mon'];
			
			return $this->compare_values($operation, $month, $value);
		}
		
		return false;
	}
	
	function validate_filter_rule_year__premium_only($filterRule) {
		$value = $filterRule->value;
		if($value) {
			$value = intval($value);
			$operation = $filterRule->operation;
			$year = getDate()['year'];
			
			return $this->compare_values($operation, $year, $value);
		}
		
		return false;
	}
	
	/**
	 * Validate filter rule
	 */
	function validate_filter_rule($filterRule) {
		$field = $filterRule->field;
		
		switch($field) {
			case 'none': return true; break; // the none field always return true value
			case 'url': return $this->validate_filter_rule_url($filterRule); break;
		}
		
		return false;
	}
	
	/**
	 * Validate filter group
	 */
	function validate_filter_group($filterGroup) {
		$operation = $filterGroup->operation;
		$list = $filterGroup->list;
		$result = true; // if group empty
		
		foreach($list as $item) {
			if($item->type == 'group') {
				$result = $this->validate_filter_group($item);
			} else if($item->type == 'rule') {
				$result = $this->validate_filter_rule($item);
			}
			
			if($operation == 'and') {
				if(!$result) {
					return false;
				}
			} else if($operation == 'or') {
				if($result) {
					return true;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Validate filter by id
	 */
	function validate_filter($filterId) {
		global $wpdb;
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
		
		$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id=%s', $filterId);
		$item = $wpdb->get_row($query, OBJECT);
		if($item) {
			$filterGroup = unserialize($item->data);
			return $this->validate_filter_group($filterGroup);
		}
		
		return false;
	}
	
	/**
	 * Prepare JS, CSS or HTML code
	 */
	function prepare_code() {
		global $wpdb;
		$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
		
		//$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE active ORDER BY type, priority', null);
		$query = 'SELECT * FROM ' . $table . ' WHERE active ORDER BY type, priority';
		$items = $wpdb->get_results($query);
		
		foreach($items as $key => $item) {
			$id = $item->id;
			$version = strtotime(mysql2date('d M Y H:i:s', $item->modified));
			$data = $item->data;
			$options = unserialize($item->options);
			
			// validate filter if it sets
			if($options->filter && !$this->validate_filter($options->filter)) {
				continue;
			}
			
			if($item->type == 'css') {
				if($options->whereonpage == 'header') {
					switch($options->whereinsite) {
						case 'user': $this->css_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'admin': $this->css_admin_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'both': {
							$this->css_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
							$this->css_admin_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
						}
						break;
					}
				} if($options->whereonpage == 'footer') {
					switch($options->whereinsite) {
						case 'user': $this->css_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'admin': $this->css_admin_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'both': {
							$this->css_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
							$this->css_admin_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
						}
						break;
					}
				}
			} else if($item->type == 'js') {
				if($options->whereonpage == 'header') {
					switch($options->whereinsite) {
						case 'user': $this->js_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'admin': $this->js_admin_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'both': {
							$this->js_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
							$this->js_admin_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
						}
						break;
					}
				} if($options->whereonpage == 'footer') {
					switch($options->whereinsite) {
						case 'user': $this->js_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'admin': $this->js_admin_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'both': {
							$this->js_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
							$this->js_admin_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
						}
						break;
					}
				}
			} else if($item->type == 'html') {
				if($options->whereonpage == 'header') {
					switch($options->whereinsite) {
						case 'user': $this->html_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'admin': $this->html_admin_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'both': {
							$this->html_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
							$this->html_admin_header_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
						}
						break;
					}
				} if($options->whereonpage == 'footer') {
					switch($options->whereinsite) {
						case 'user': $this->html_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'admin': $this->html_admin_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options); break;
						case 'both': {
							$this->html_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
							$this->html_admin_footer_data[] = array('id' => $id, 'version' => $version, 'data' => $data, 'options' => $options);
						}
						break;
					}
				}
			}
		}
		
		add_action('admin_head', array($this, 'print_admin_header'));
		add_action('admin_footer', array($this, 'print_admin_footer'));
		add_action('wp_head', array($this, 'print_header'));
		add_action('wp_footer', array($this, 'print_footer'));
	}
	
	function print_code($css_data, $js_data, $html_data) {
		$begin = '<!-- ' . EASYJC_PLUGIN_NAME . ' begin -->' . PHP_EOL;
		$end = '<!-- ' . EASYJC_PLUGIN_NAME . ' end -->' . PHP_EOL;
		
		// CSS section
		$before = $begin . '<style type="text/css">' . PHP_EOL;
		$after = PHP_EOL . '</style>' . PHP_EOL . $end;
		
		foreach($css_data as $key => $item) {
			$file_name = $item['id'] . '.css';
			$version = $item['version'];
			$options = $item['options'];
			
			if($options->file == 'internal') {
				echo $before;
				include_once(EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name);
				echo $after;
			} else if($options->file == 'external') {
				echo '<link rel="stylesheet" href="' . EASYJC_PLUGIN_UPLOAD_URL . '/' . $file_name . '?v=' . $version . '" type="text/css" media="all" />' . PHP_EOL;
			}
		}
		
		// JS section
		$before = $begin . '<script type="text/javascript">' . PHP_EOL;
		$after = PHP_EOL . '</script>' . PHP_EOL . $end;
		
		foreach($js_data as $key => $item) {
			$file_name = $item['id'] . '.js';
			$version = $item['version'];
			$options = $item['options'];
			
			if($options->file == 'internal') {
				echo $before;
				include_once(EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name);
				echo $after;
			} else if($options->file == 'external') {
				echo '<script src="' . EASYJC_PLUGIN_UPLOAD_URL . '/' . $file_name . '?v=' . $version . '" type="text/javascript"></script>' . PHP_EOL;
			}
		}
		
		// HTML section
		$before = $begin . PHP_EOL;
		$after = PHP_EOL . $end;
		
		foreach($html_data as $key => $item) {
			$file_name = $item['id'] . '.html';
			$version = $item['version'];
			$options = $item['options'];
			
			if($options->file == 'internal') {
				echo $before;
				include_once(EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name);
				echo $after;
			}
		}
	}
	
	function print_admin_header() {
		$this->print_code($this->css_admin_header_data, $this->js_admin_header_data, $this->html_admin_header_data);
	}
	
	function print_admin_footer() {
		$this->print_code($this->css_admin_footer_data, $this->js_admin_footer_data, $this->html_admin_footer_data);
	}
	
	function print_header() {
		$this->print_code($this->css_header_data, $this->js_header_data, $this->html_header_data);
	}
	
	function print_footer() {
		$this->print_code($this->css_footer_data, $this->js_footer_data, $this->html_footer_data);
	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	function admin_menu() {
		// add "edit_posts" if we want to give access to author, editor and contributor roles
		add_menu_page(__('Easy Custom JS & CSS', EASYJC_PLUGIN_NAME), __('Custom JS & CSS', EASYJC_PLUGIN_NAME), 'manage_options', EASYJC_PLUGIN_NAME, array( $this, 'admin_menu_page_items' ), 'dashicons-admin-generic');
		add_submenu_page(EASYJC_PLUGIN_NAME, __('Easy Custom JS & CSS', EASYJC_PLUGIN_NAME), __('All Items', EASYJC_PLUGIN_NAME), 'manage_options', EASYJC_PLUGIN_NAME, array( $this, 'admin_menu_page_items' ));
		add_submenu_page(EASYJC_PLUGIN_NAME, __('Easy Custom JS & CSS Filters', EASYJC_PLUGIN_NAME), __('All filters', EASYJC_PLUGIN_NAME), 'manage_options', EASYJC_PLUGIN_NAME . '_filters', array( $this, 'admin_menu_page_filters' ));
		add_submenu_page(EASYJC_PLUGIN_NAME, __('Easy Custom JS & CSS Settings', EASYJC_PLUGIN_NAME), __('Settings', EASYJC_PLUGIN_NAME), 'manage_options', EASYJC_PLUGIN_NAME . '_settings', array( $this, 'admin_menu_page_settings' ));
	}
	
	/**
	 * Show admin menu items page
	 */
	function admin_menu_page_items() {
		$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
		
		if($page===EASYJC_PLUGIN_NAME) {
			$plugin_url = plugin_dir_url( dirname(__FILE__) );
			
			// styles
			wp_enqueue_style( EASYJC_PLUGIN_NAME . '_admin_css', $plugin_url . 'assets/css/admin.min.css', array(), EASYJC_PLUGIN_VERSION, 'all' );
			wp_enqueue_style( EASYJC_PLUGIN_NAME . '_customjscssicons_css', $plugin_url . 'assets/css/customjscssicons.min.css', array(), EASYJC_PLUGIN_VERSION, 'all' );
			
			// scripts
			wp_enqueue_script( EASYJC_PLUGIN_NAME . '_ace', $plugin_url . 'assets/js/lib/ace/ace.js', array(), EASYJC_PLUGIN_VERSION, false );
			wp_enqueue_script( EASYJC_PLUGIN_NAME . '_admin_js', $plugin_url . 'assets/js/admin.min.js', array('jquery'), EASYJC_PLUGIN_VERSION, false );
			
			
			// global settings to help ajax work
			$globals = array(
				'plan' => EASYJC_PLUGIN_PLAN,
				'msg_pro_title' => __('Available only in Pro version', EASYJC_PLUGIN_NAME),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( EASYJC_PLUGIN_NAME . '_ajax' ),
				'ajax_msg_error' => __('Uncaught Error', EASYJC_PLUGIN_NAME) //Look at the console (F12 or Ctrl+Shift+I, Console tab) for more information
			);
			
			$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
			$type = filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING);
			$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
			
			if($action=='new' || $action=='edit') {
				$globals['ajax_action_update'] = $this->ajax_action_item_update;
				$globals['ajax_action_get'] = $this->ajax_action_filter_get;
				$globals['ajax_id'] = $id;
				$globals['ajax_type'] = $type;
				$globals['settings'] = NULL;
				$globals['config'] = NULL;
				
				
				$settings_key = EASYJC_PLUGIN_NAME . '_settings';
				$settings_value = get_option($settings_key);
				if($settings_value) {
					$globals['settings'] = json_encode(unserialize($settings_value));
				}
				
				
				// get item data from DB
				if($id) {
					global $wpdb;
					$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
					
					$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id=%s', $id);
					$item = $wpdb->get_row($query, OBJECT);
					if($item) {
						//{
						//id: null,
						//title: null,
						//data: null,
						//type: null,
						//active: true,
						//options: {...}
						//}
						$globals['config'] = json_encode( array(
							'title' => $item->title,
							'data' => $item->data,
							'type' => $item->type,
							'active' => ($item->active ? true : false),
							'options' => unserialize($item->options)
						));
					}
				} else {
					// new item
					$item = (object) array(
						'author' => get_current_user_id(),
						'date' => current_time('mysql', 1),
						'modified' => current_time('mysql', 1)
					);
				}
				
				require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/page-item.php' );
			} else {
				$nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
				
				if($action && $nonce && wp_verify_nonce($nonce, EASYJC_PLUGIN_NAME)) {
					global $wpdb;
					$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
					
					if($action == 'duplicate') {
						$result = false;
						
						$query = $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id=%s', $id);
						$item = $wpdb->get_row($query, OBJECT);
						if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
							$result = $wpdb->insert(
								$table,
								array(
									'title' => __('[Duplicate] ', EASYJC_PLUGIN_NAME) . $item->title,
									'data' => $item->data,
									'type' => $item->type,
									'active' => $item->active,
									'options' => $item->options,
									'author' => get_current_user_id(),
									'date' => current_time('mysql', 1),
									'modified' => current_time('mysql', 1)
							));
							
							//======================================
							// [filemanager] create an external file
							if($result && wp_is_writable(EASYJC_PLUGIN_UPLOAD_DIR)) {
								$file_name_src = $item->id . '.' . $item->type;
								$file_name_dst = $wpdb->insert_id . '.' . $item->type;
								copy(EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name_src, EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name_dst);
							}
							//======================================
						}
					}
					if($action=='delete') {
						$result = false;
						
						$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id=%s', $id);
						$item = $wpdb->get_row($query, OBJECT);
						if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
							$result = $wpdb->delete( $table, ['id'=>$id], ['%d']);
							
							//======================================
							// [filemanager] delete file
							$file_name = $item->id . '.' . $item->type;
							wp_delete_file(EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name);
							//======================================
						}
					}
				}
				
				$globals['ajax_action_update'] = $this->ajax_action_item_update_status;
				
				require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/list-table-items.php' );
				require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/page-items.php' );
			}
			
			// set global settings
			wp_localize_script(EASYJC_PLUGIN_NAME . '_admin_js', EASYJC_PLUGIN_NAME, $globals);
		}
	}
	
	/**
	 * Show admin menu filters page
	 */
	function admin_menu_page_filters() {
		$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
		
		if($page===EASYJC_PLUGIN_NAME . '_filters') {
			$plugin_url = plugin_dir_url( dirname(__FILE__) );
			
			// styles
			wp_enqueue_style( EASYJC_PLUGIN_NAME . '_admin_css', $plugin_url . 'assets/css/admin.min.css', array(), EASYJC_PLUGIN_VERSION, 'all' );
			wp_enqueue_style( EASYJC_PLUGIN_NAME . '_customjscssicons_css', $plugin_url . 'assets/css/customjscssicons.min.css', array(), EASYJC_PLUGIN_VERSION, 'all' );
			
			// scripts
			wp_enqueue_script( EASYJC_PLUGIN_NAME . '_imask_js', $plugin_url . 'assets/js/lib/imask/imask.min.js', array(), EASYJC_PLUGIN_VERSION, false );
			wp_enqueue_script( EASYJC_PLUGIN_NAME . '_admin_js', $plugin_url . 'assets/js/admin.min.js', array('jquery'), EASYJC_PLUGIN_VERSION, false );
			
			// global settings to help ajax work
			$globals = array(
				'plan' => EASYJC_PLUGIN_PLAN,
				'msg_pro_title' => __('Available only in Pro version', EASYJC_PLUGIN_NAME),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( EASYJC_PLUGIN_NAME . '_ajax' ),
				'ajax_msg_error' => __('Uncaught Error', EASYJC_PLUGIN_NAME) //Look at the console (F12 or Ctrl+Shift+I, Console tab) for more information
			);
			
			$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
			$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
			
			if($action=='new' || $action=='edit') {
				$globals['ajax_action_update'] = $this->ajax_action_filter_update;
				$globals['ajax_action_get'] = $this->ajax_action_filter_get;
				$globals['ajax_id'] = $id;
				$globals['config'] = NULL;
				
				// get filter data from DB
				if($id) {
					global $wpdb;
					$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
					
					$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id=%s', $id);
					$item = $wpdb->get_row($query, OBJECT);
					if($item) {
						//{
						//id: null,
						//title: null,
						//data: null
						//}
						$globals['config'] = json_encode( array(
							'title' => $item->title,
							'data' => unserialize($item->data)
						));
					}
				} else {
					// new item
					$item = (object) array(
						'author' => get_current_user_id(),
						'date' => current_time('mysql', 1),
						'modified' => current_time('mysql', 1)
					);
					
					$data = (object) array(
						'type' => 'group',
						'operation' => 'and',
						'list' => array()
					);
					
					$globals['config'] = json_encode( array(
						'title' => NULL,
						'data' => $data
					));
				}
				
				require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/page-filter.php' );
			} else {
				$nonce = filter_input(INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING);
				
				if($action && $nonce && wp_verify_nonce($nonce, EASYJC_PLUGIN_NAME)) {
					global $wpdb;
					$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
					
					if($action == 'duplicate') {
						$result = false;
						
						$query = $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id=%s', $id);
						$item = $wpdb->get_row($query, OBJECT);
						if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
							$result = $wpdb->insert(
								$table,
								array(
									'title' => __('[Duplicate] ', EASYJC_PLUGIN_NAME) . $item->title,
									'data' => $item->data,
									'author' => get_current_user_id(),
									'date' => current_time('mysql', 1),
									'modified' => current_time('mysql', 1)
							));
						}
					}
					if($action=='delete') {
						$result = false;
						
						$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id=%s', $id);
						$item = $wpdb->get_row($query, OBJECT);
						if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
							$result = $wpdb->delete( $table, ['id'=>$id], ['%d']);
						}
					}
				}
				
				require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/list-table-filters.php' );
				require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/page-filters.php' );
			}
			
			// set global settings
			wp_localize_script(EASYJC_PLUGIN_NAME . '_admin_js', EASYJC_PLUGIN_NAME, $globals);
		}
	}
	
	/**
	 * Show admin menu settings page
	 */
	function admin_menu_page_settings() {
		$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
		
		if($page===EASYJC_PLUGIN_NAME . '_settings') {
			$plugin_url = plugin_dir_url( dirname(__FILE__) );
			
			// styles
			wp_enqueue_style( EASYJC_PLUGIN_NAME . '_admin_css', $plugin_url . 'assets/css/admin.min.css', array(), EASYJC_PLUGIN_VERSION, 'all' );
			
			// scripts
			wp_enqueue_script( EASYJC_PLUGIN_NAME . '_admin_js', $plugin_url . 'assets/js/admin.min.js', array('jquery'), EASYJC_PLUGIN_VERSION, false );
			
			// global settings to help ajax work
			$globals = array(
				'plan' => EASYJC_PLUGIN_PLAN,
				'msg_pro_title' => __('Available only in Pro version', EASYJC_PLUGIN_NAME),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( EASYJC_PLUGIN_NAME . '_ajax' ),
				'ajax_msg_error' => __('Uncaught Error', EASYJC_PLUGIN_NAME), //Look at the console (F12 or Ctrl+Shift+I, Console tab) for more information
				'ajax_action_update' => $this->ajax_action_settings_update,
				'ajax_action_get' => $this->ajax_action_settings_get,
				'ajax_action_delete_data' => $this->ajax_action_delete_data,
				'config' => NULL
			);
			
			// read settings
			$settings_key = EASYJC_PLUGIN_NAME . '_settings';
			$settings_value = get_option($settings_key);
			if($settings_value) {
				$globals['config'] = json_encode(unserialize($settings_value));
			}
			
			require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/page-settings.php' );
			
			// set global settings
			wp_localize_script( EASYJC_PLUGIN_NAME . '_admin_js', EASYJC_PLUGIN_NAME, $globals);
		}
	}
	
	/**
	 * Ajax update item state
	 */
	function ajax_item_update_status() {
		$error = false;
		$data = array();
		$config = filter_input(INPUT_POST, 'config', FILTER_UNSAFE_RAW);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			global $wpdb;
			
			$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
			$config = json_decode($config);
			$result = false;
			
			if(isset($config->id) && isset($config->active)) {
				$query = $wpdb->prepare('SELECT * FROM ' . $table . ' WHERE id=%s', $config->id);
				$item = $wpdb->get_row($query, OBJECT );
				if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
					$result = $wpdb->update(
						$table,
						array('active'=>$config->active),
						array('id'=>$config->id));
				}
			}
			
			if($result) {
				$data['id'] = $config->id;
				$data['msg'] = __('Item updated', EASYJC_PLUGIN_NAME);
			} else {
				$error = true;
				$data['msg'] = __('The operation failed, can\'t update item', EASYJC_PLUGIN_NAME);
			}
		} else {
			$error = true;
			$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax update item data
	 */
	function ajax_item_update() {
		$error = false;
		$data = array();
		$config = filter_input(INPUT_POST, 'config', FILTER_UNSAFE_RAW);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			global $wpdb;
			
			$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
			$config = json_decode($config);
			
			if(isset($config->id)) {
				$result = false;
				
				$query = $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id=%s', $config->id);
				$item = $wpdb->get_row($query, OBJECT);
				if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
					$result = $wpdb->update(
						$table,
						array(
							'title' => $config->title,
							'data' => $config->data,
							'type' => $config->type,
							'active' => $config->active,
							'options' => serialize($config->options),
							'author' => get_current_user_id(),
							//'date' => NULL,
							'modified' => current_time('mysql', 1)
						),
						array('id'=>$config->id));
				}
				
				if($result) {
					$data['id'] = $config->id;
					$data['msg'] = __('Item updated', EASYJC_PLUGIN_NAME);
				} else {
					$error = true;
					$data['msg'] = __('The operation failed, can\'t update item', EASYJC_PLUGIN_NAME);
				}
			} else {
				$result = $wpdb->insert(
					$table,
					array(
						'title' => $config->title,
						'data' => $config->data,
						'type' => $config->type,
						'active' => $config->active,
						'options' => serialize($config->options),
						'author' => get_current_user_id(),
						'date' => current_time('mysql', 1),
						'modified' => current_time('mysql', 1)
					));
				
				if($result) {
					$data['id'] = $config->id = $wpdb->insert_id;
					$data['msg'] = __('Item created', EASYJC_PLUGIN_NAME);
				} else {
					$error = true;
					$data['msg'] = __('The operation failed, can\'t create item', EASYJC_PLUGIN_NAME);
				}
			}
			
			//======================================
			// [filemanager] create an external file
			if(!$error && wp_is_writable(EASYJC_PLUGIN_UPLOAD_DIR)) {
				$file_name = $config->id . '.' . $config->type;
				$file_path = EASYJC_PLUGIN_UPLOAD_DIR . '/' . $file_name;
				$file_data = $config->data;
				
				if(!$error) {
					@file_put_contents($file_path, $file_data);
					
					}
			}
			//======================================
		} else {
			$error = true;
			$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax update filter data
	 */
	function ajax_filter_update() {
		$error = false;
		$data = array();
		$config = filter_input(INPUT_POST, 'config', FILTER_UNSAFE_RAW);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			global $wpdb;
			
			$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
			$config = json_decode($config);
			
			if(isset($config->id)) {
				$result = false;
				
				$query = $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE id=%s', $config->id);
				$item = $wpdb->get_row($query, OBJECT);
				if($item && (current_user_can('administrator') || get_current_user_id()==$item->author) ) {
					$result = $wpdb->update(
						$table,
						array(
							'title' => $config->title,
							'data' => serialize($config->data),
							'author' => get_current_user_id(),
							//'date' => NULL,
							'modified' => current_time('mysql', 1)
						),
						array('id'=>$config->id));
				}
				
				if($result) {
					$data['id'] = $config->id;
					$data['msg'] = __('Item updated', EASYJC_PLUGIN_NAME);
				} else {
					$error = true;
					$data['msg'] = __('The operation failed, can\'t update item', EASYJC_PLUGIN_NAME);
				}
			} else {
				$result = $wpdb->insert(
					$table,
					array(
						'title' => $config->title,
						'data' => serialize($config->data),
						'author' => get_current_user_id(),
						'date' => current_time('mysql', 1),
						'modified' => current_time('mysql', 1)
					));
				
				if($result) {
					$data['id'] = $config->id = $wpdb->insert_id;
					$data['msg'] = __('Item created', EASYJC_PLUGIN_NAME);
				} else {
					$error = true;
					$data['msg'] = __('The operation failed, can\'t create item', EASYJC_PLUGIN_NAME);
				}
			}
		} else {
			$error = true;
			$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax filter get data
	 */
	function ajax_filter_get() {
		$error = false;
		$data = array();
		$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			switch($type) {
				case 'actions': {
					$data['list'] = array(
						array('id' => 'rule', 'title' => __('Add rule', EASYJC_PLUGIN_NAME)),
						array('id' => 'group', 'title' => __('Add group', EASYJC_PLUGIN_NAME))
					);
				}
				break;
				case 'operations': {
					$data['list'] = array(
						array('id' => 'and', 'title' => __('And', EASYJC_PLUGIN_NAME)),
						array('id' => 'or', 'title' => __('Or', EASYJC_PLUGIN_NAME))
					);
				}
				break;
				case 'ruleFields': {
					$data['list'] = array(
						array('id' => 'none', 'title' => __('None', EASYJC_PLUGIN_NAME), 'type' => 'none', 'enter' => false, 'getlist' => NULL, 'pro' => false),
						array(
							'id' => 'group00',
							'title' => __('Server', EASYJC_PLUGIN_NAME),
							'list' => array(
								array('id' => 'url', 'title' => __('Request URI', EASYJC_PLUGIN_NAME), 'type' => 'url', 'enter' => true, 'getlist' => NULL, 'pro' => false)
							)
						),
						array(
							'id' => 'group01',
							'title' => __('WordPress', EASYJC_PLUGIN_NAME),
							'list' => array(
								array('id' => 'post', 'title' => __('Post Title', EASYJC_PLUGIN_NAME), 'type' => 'post', 'enter' => true, 'getlist' => 'posts', 'pro' => true),
								array('id' => 'page', 'title' => __('Page Title', EASYJC_PLUGIN_NAME), 'type' => 'page', 'enter' => true, 'getlist' => 'pages', 'pro' => true),
								array('id' => 'page_template', 'title' => __('Page Template', EASYJC_PLUGIN_NAME), 'type' => 'page_template', 'enter' => true, 'getlist' => 'page_templates', 'pro' => true)
							)
						),
						array(
							'id' => 'group02',
							'title' => __('Users', EASYJC_PLUGIN_NAME),
							'list' => array(
								array('id' => 'logged_in', 'title' => __('Logged in', EASYJC_PLUGIN_NAME), 'type' => 'logged_in', 'enter' => false, 'getlist' => NULL, 'pro' => true),
								array('id' => 'user', 'title' => __('User login', EASYJC_PLUGIN_NAME), 'type' => 'user', 'enter' => true, 'getlist' => 'users', 'pro' => true),
								array('id' => 'role', 'title' => __('Role', EASYJC_PLUGIN_NAME), 'type' => 'role', 'enter' => true, 'getlist' => 'roles', 'pro' => true)
							)
						),
						array(
							'id' => 'group03',
							'title' => __('System', EASYJC_PLUGIN_NAME),
							'list' => array(
								array('id' => 'device', 'title' => __('Device', EASYJC_PLUGIN_NAME), 'type' => 'device', 'enter' => true, 'getlist' => 'devices', 'pro' => true),
								array('id' => 'os', 'title' => __('OS', EASYJC_PLUGIN_NAME), 'type' => 'os', 'enter' => true, 'getlist' => 'os', 'pro' => true),
								array('id' => 'browser', 'title' => __('Browser', EASYJC_PLUGIN_NAME), 'type' => 'browser', 'enter' => true, 'getlist' => 'browsers', 'pro' => true)
							)
						),
						array(
							'id' => 'group04',
							'title' => __('Server Date & Time', EASYJC_PLUGIN_NAME),
							'list' => array(
								array('id' => 'date', 'title' => __('Date', EASYJC_PLUGIN_NAME), 'type' => 'date', 'enter' => true, 'getlist' => NULL, 'placeholder' => 'DD/MMM/YYYY', 'pro' => true),
								array('id' => 'time', 'title' => __('Time', EASYJC_PLUGIN_NAME), 'type' => 'time', 'enter' => true, 'getlist' => NULL, 'placeholder' => 'HH:mm', 'pro' => true),
								array('id' => 'minutes', 'title' => __('Minutes', EASYJC_PLUGIN_NAME), 'type' => 'minutes', 'enter' => true, 'getlist' => NULL, 'placeholder' => '0-59', 'pro' => true),
								array('id' => 'hours', 'title' => __('Hours', EASYJC_PLUGIN_NAME), 'type' => 'hours', 'enter' => true, 'getlist' => NULL, 'placeholder' => '0-23', 'pro' => true),
								array('id' => 'day', 'title' => __('Day', EASYJC_PLUGIN_NAME), 'type' => 'day', 'enter' => true, 'getlist' => NULL, 'placeholder' => '1-31', 'pro' => true),
								array('id' => 'day_of_week', 'title' => __('Day of week', EASYJC_PLUGIN_NAME), 'type' => 'day_of_week', 'enter' => true, 'getlist' => NULL, 'placeholder' => '1-7', 'pro' => true),
								array('id' => 'month', 'title' => __('Month', EASYJC_PLUGIN_NAME), 'type' => 'month', 'enter' => true, 'getlist' => NULL, 'placeholder' => '1-12', 'pro' => true),
								array('id' => 'year', 'title' => __('Year', EASYJC_PLUGIN_NAME), 'type' => 'year', 'enter' => true, 'getlist' => NULL, 'pro' => true)
							)
						)
					);
				}
				break;
				case 'ruleOperations': {
					$data['list'] = array(
						'properties' => array(
							array('id' => 'yes', 'title' => '<i class="customjscss-icon-is-not-blank"></i>' . __('Yes', EASYJC_PLUGIN_NAME)),
							array('id' => 'no', 'title' => '<i class="customjscss-icon-is-blank"></i>' . __('No', EASYJC_PLUGIN_NAME)),
							array('id' => 'contains', 'title' => '<i class="customjscss-icon-contains"></i>' . __('Contains', EASYJC_PLUGIN_NAME)),
							array('id' => 'does_not_contains', 'title' => '<i class="customjscss-icon-not-contains"></i>' . __('Does not contains', EASYJC_PLUGIN_NAME)),
							array('id' => 'start_with', 'title' => '<i class="customjscss-icon-start-with"></i>' . __('Start with', EASYJC_PLUGIN_NAME)),
							array('id' => 'end_with', 'title' => '<i class="customjscss-icon-end-with"></i>' . __('End with', EASYJC_PLUGIN_NAME)),
							array('id' => 'equals', 'title' => '<i class="customjscss-icon-equals"></i>' . __('Equals', EASYJC_PLUGIN_NAME)),
							array('id' => 'does_not_equals', 'title' => '<i class="customjscss-icon-not-equals"></i>' . __('Does not equals', EASYJC_PLUGIN_NAME)),
							array('id' => 'is_blank', 'title' => '<i class="customjscss-icon-is-blank"></i>' . __('Is blank', EASYJC_PLUGIN_NAME)),
							array('id' => 'is_not_blank', 'title' => '<i class="customjscss-icon-is-not-blank"></i>' . __('Is not blank', EASYJC_PLUGIN_NAME)),
							array('id' => 'less_than', 'title' => '<i class="customjscss-icon-less-than"></i>' . __('Less than', EASYJC_PLUGIN_NAME)),
							array('id' => 'greater_than', 'title' => '<i class="customjscss-icon-greater-than"></i>' . __('Greater than', EASYJC_PLUGIN_NAME)),
							array('id' => 'less_than_or_equal_to', 'title' => '<i class="customjscss-icon-less-than-or-equal"></i>' . __('Less than or equal to', EASYJC_PLUGIN_NAME)),
							array('id' => 'greater_than_or_equal_to', 'title' => '<i class="customjscss-icon-greater-than-or-equal"></i>' . __('Greater than or equal to', EASYJC_PLUGIN_NAME)),
							array('id' => 'in_list', 'title' => '<i class="customjscss-icon-in-list"></i>' . __('In list', EASYJC_PLUGIN_NAME)),
							array('id' => 'not_in_list', 'title' => '<i class="customjscss-icon-not-in-list"></i>' . __('Not in list', EASYJC_PLUGIN_NAME)),
							array('id' => 'regex', 'title' => '<i class="customjscss-icon-regex"></i>' . __('RegEx', EASYJC_PLUGIN_NAME))
						),
						'operations' => array(
							array('id' => 'none', 'list' => array()),
							array('id' => 'integer', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to', 'is_blank', 'is_not_blank')),
							array('id' => 'string', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'is_blank', 'is_not_blank', 'regex')),
							array('id' => 'url', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'regex')),
							array('id' => 'post', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'page', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'page_template', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'device', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'os', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'browser', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'logged_in', 'list' => array('yes', 'no')),
							array('id' => 'user', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'role', 'list' => array('contains', 'does_not_contains', 'start_with', 'end_with', 'equals', 'does_not_equals', 'in_list', 'not_in_list', 'regex')),
							array('id' => 'date', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'time', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'minutes', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'hours', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'day', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'day_of_week', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'month', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to')),
							array('id' => 'year', 'list' => array('equals', 'does_not_equals', 'less_than', 'greater_than', 'less_than_or_equal_to', 'greater_than_or_equal_to'))
						)
					);
				}
				break;
				case 'filters': {
					$data['list'] = array(
						array('id' => NULL, 'title' => __('None', EASYJC_PLUGIN_NAME))
					);
					
					global $wpdb;
					$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
					//$query = $wpdb->prepare('SELECT id, title FROM ' . $table . ' ORDER BY title');
					$query = 'SELECT id, title FROM ' . $table . ' ORDER BY title';
					$items = $wpdb->get_results($query, OBJECT);
					
					foreach($items as $item) {
						array_push($data['list'], array('id' => $item->id, 'title' => $item->title));
					}
				}
				break;
				case 'posts': {
					$data['list'] = array();
					
					global $wpdb;
					$table = $wpdb->posts;
					$query = $wpdb->prepare('SELECT id, post_title FROM ' . $table . ' WHERE post_type=%s AND post_status=%s ORDER BY post_date DESC', 'post', 'publish');
					$items = $wpdb->get_results($query, OBJECT);
					
					foreach($items as $item) {
						array_push($data['list'], array('id' => NULL, 'title' => $item->post_title));
					}
				}
				break;
				case 'pages': {
					$data['list'] = array();
					
					global $wpdb;
					$table = $wpdb->posts;
					$query = $wpdb->prepare('SELECT id, post_title FROM ' . $table . ' WHERE post_type=%s AND post_status=%s ORDER BY post_date DESC', 'page', 'publish');
					$items = $wpdb->get_results($query, OBJECT);
					
					foreach($items as $item) {
						array_push($data['list'], array('id' => NULL, 'title' => $item->post_title));
					}
				}
				break;
				case 'page_templates': {
					$data['list'] = array();
					
					$default = array(
						'default' => 'Default'
					);
					$templates = wp_get_theme()->get_page_templates();
					$templates = array_merge($default, $templates);
					
					foreach($templates as $template) {
						array_push($data['list'], array('id' => NULL, 'title' => $template));
					}
				}
				break;
				case 'users': {
					$data['list'] = array();
					
					$args = array(
						'fields' => array('ID','user_login')
					);
					$users = get_users($args);
					foreach ($users as $user) {
						array_push($data['list'], array('id' => NULL, 'title' => $user->user_login));
					}
				}
				break;
				case 'roles': {
					$data['list'] = array();
					
					global $wp_roles;
					$roles = $wp_roles->get_names();
					
					foreach ($roles as $role) {
						array_push($data['list'], array('id' => NULL, 'title' => $role));
					}
				}
				break;
				case 'devices': {
					$data['list'] = array(
						array('id' => NULL, 'title' => 'Mobile'),
						array('id' => NULL, 'title' => 'Tablet'),
						array('id' => NULL, 'title' => 'Desktop')
					);
				}
				break;
				case 'os': {
					require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/lib/browser.php' );
					
					$data['list'] = array(
						array('id' => NULL, 'title' => Browser::PLATFORM_ANDROID),
						array('id' => NULL, 'title' => Browser::PLATFORM_APPLE),
						array('id' => NULL, 'title' => Browser::PLATFORM_BEOS),
						array('id' => NULL, 'title' => Browser::PLATFORM_BLACKBERRY),
						array('id' => NULL, 'title' => Browser::PLATFORM_FREEBSD),
						array('id' => NULL, 'title' => Browser::PLATFORM_IPAD),
						array('id' => NULL, 'title' => Browser::PLATFORM_IPHONE),
						array('id' => NULL, 'title' => Browser::PLATFORM_IPOD),
						array('id' => NULL, 'title' => Browser::PLATFORM_LINUX),
						array('id' => NULL, 'title' => Browser::PLATFORM_NETBSD),
						array('id' => NULL, 'title' => Browser::PLATFORM_NOKIA),
						array('id' => NULL, 'title' => Browser::PLATFORM_OPENBSD),
						array('id' => NULL, 'title' => Browser::PLATFORM_OS2),
						array('id' => NULL, 'title' => Browser::PLATFORM_SUNOS),
						array('id' => NULL, 'title' => Browser::PLATFORM_WINDOWS),
						array('id' => NULL, 'title' => Browser::PLATFORM_WINDOWS_CE)
					);
				}
				break;
				case 'browsers': {
					require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/lib/browser.php' );
					
					$data['list'] = array(
						array('id' => NULL, 'title' => Browser::BROWSER_ANDROID),
						array('id' => NULL, 'title' => Browser::BROWSER_BINGBOT),
						array('id' => NULL, 'title' => Browser::BROWSER_BLACKBERRY),
						array('id' => NULL, 'title' => Browser::BROWSER_CHROME),
						array('id' => NULL, 'title' => Browser::BROWSER_EDGE),
						array('id' => NULL, 'title' => Browser::BROWSER_FIREBIRD),
						array('id' => NULL, 'title' => Browser::BROWSER_FIREFOX),
						array('id' => NULL, 'title' => Browser::BROWSER_GALEON),
						array('id' => NULL, 'title' => Browser::BROWSER_GOOGLEBOT),
						array('id' => NULL, 'title' => Browser::BROWSER_ICAB),
						array('id' => NULL, 'title' => Browser::BROWSER_IE),
						array('id' => NULL, 'title' => Browser::BROWSER_IPAD),
						array('id' => NULL, 'title' => Browser::BROWSER_IPHONE),
						array('id' => NULL, 'title' => Browser::BROWSER_IPOD),
						array('id' => NULL, 'title' => Browser::BROWSER_KONQUEROR),
						array('id' => NULL, 'title' => Browser::BROWSER_MOZILLA),
						array('id' => NULL, 'title' => Browser::BROWSER_MSN),
						array('id' => NULL, 'title' => Browser::BROWSER_MSNBOT),
						array('id' => NULL, 'title' => Browser::BROWSER_NETPOSITIVE),
						array('id' => NULL, 'title' => Browser::BROWSER_OMNIWEB),
						array('id' => NULL, 'title' => Browser::BROWSER_OPERA),
						array('id' => NULL, 'title' => Browser::BROWSER_OPERA_MINI),
						array('id' => NULL, 'title' => Browser::BROWSER_POCKET_IE),
						array('id' => NULL, 'title' => Browser::BROWSER_PHOENIX),
						array('id' => NULL, 'title' => Browser::BROWSER_SAFARI),
						array('id' => NULL, 'title' => Browser::BROWSER_WEBTV),
						array('id' => NULL, 'title' => Browser::BROWSER_W3CVALIDATOR)
					);
				}
				break;
				default: {
					$error = true;
					$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
				}
				break;
			}
		} else {
			$error = true;
			$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax update settings data
	 */
	function ajax_settings_update() {
		$error = false;
		$data = array();
		$config = filter_input(INPUT_POST, 'config', FILTER_UNSAFE_RAW);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			$settings_key = EASYJC_PLUGIN_NAME . '_settings';
			$settings_value = serialize(json_decode($config));
			$result = false;
			
			if(get_option($settings_key) == false) {
				$deprecated = null;
				$autoload = 'no';
				$result = add_option($settings_key, $settings_value, $deprecated, $autoload);
			} else {
				$result = update_option($settings_key, $settings_value);
			}
			
			if($result) {
				$data['msg'] = __('Settings updated', EASYJC_PLUGIN_NAME);
			} else {
				$error = true;
				$data['msg'] = __('The operation failed, can\'t update settings', EASYJC_PLUGIN_NAME);
			}
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax settings get data
	 */
	function ajax_settings_get() {
		$error = false;
		$data = array();
		$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			switch($type) {
				case 'themes': {
					$data['list'] = array();
					$files = glob(plugin_dir_path( dirname(__FILE__) ) . 'assets/js/lib/ace/theme-*.js');
					
					foreach($files as $file) {
						$filename = str_replace('theme-','',basename($file, '.js'));
						array_push($data['list'], array('id' => $filename, 'title' => str_replace('_', ' ', $filename)));
					}
				}
				break;
				default: {
					$error = true;
					$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
				}
				break;
			}
		} else {
			$error = true;
			$data['msg'] = __('The operation failed', EASYJC_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax delete all data from tables
	 */
	function ajax_delete_data() {
		$error = true;
		$data = array();
		$data['msg'] = __('The operation failed, can\'t delete data', EASYJC_PLUGIN_NAME);
		
		if(check_ajax_referer(EASYJC_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			global $wpdb;
			
			$table = $wpdb->prefix . EASYJC_PLUGIN_NAME;
			$query = 'TRUNCATE TABLE ' . $table;
			$result_01 = $wpdb->query($query);
			
			$table = $wpdb->prefix . EASYJC_PLUGIN_NAME . '_filters';
			$query = 'TRUNCATE TABLE ' . $table;
			$result_02 = $wpdb->query($query);
			
			if($result_01 && $result_02) {
				$error = false;
				$data['msg'] = __('All data deleted', EASYJC_PLUGIN_NAME);
			}
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
}

endif;

?>