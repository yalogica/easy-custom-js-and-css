<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);
$author = get_the_author_meta('display_name', $item->author);
$modified = mysql2date(get_option('date_format'), $item->modified) . ' at ' . mysql2date(get_option('time_format'), $item->modified);

?>
<div class="wrap customjscss">
	<?php require 'page-info.php'; ?>
	<h2 class="customjscss-main-title"><span><?php _e('Easy Custom JS & CSS Filter', EASYJC_PLUGIN_NAME); ?></span></h2>
	<div class="customjscss-messages" id="customjscss-messages">
	</div>
	<p class="customjscss-actions">
		<a href="?page=<?php echo $page; ?>&action=new" class="page-title-action"><?php _e('Add New Filter', EASYJC_PLUGIN_NAME); ?></a>
	</p>
	<!-- customjscss app -->
	<div id="customjscss-app-filter" class="customjscss-app" style="display:none;">
		<div class="customjscss-loader-wrap">
			<div class="customjscss-loader">
				<div class="customjscss-loader-bar"></div>
				<div class="customjscss-loader-bar"></div>
				<div class="customjscss-loader-bar"></div>
				<div class="customjscss-loader-bar"></div>
			</div>
		</div>
		<div class="customjscss-wrap">
			<div class="customjscss-header">
				<input class="customjscss-title" type="text" placeholder="<?php _e('Title', EASYJC_PLUGIN_NAME); ?>" al-text="appData.config.title">
			</div>
			<div class="customjscss-workplace">
				<div al-filter="appData.config.data"
					 data-get-operations="appData.fn.getOperations(appData)"
					 data-get-actions="appData.fn.getActions(appData)"
					 data-get-rule-fields="appData.fn.getRuleFields(appData)"
					 data-get-rule-operations="appData.fn.getRuleOperations(appData)"
					 data-get-list="appData.fn.getFilterData(appData, type)"
					 data-string-value-blank="<?php _e('[enter a value]', EASYJC_PLUGIN_NAME); ?>">
				</div>
			</div>
			<br>
			<button class="customjscss-button customjscss-button-submit" al-on.click="appData.fn.saveConfig(appData);"><?php _e('Save Changes', EASYJC_PLUGIN_NAME); ?></button>
		</div>
	</div>
	<!-- /end customjscss app -->
</div>