<?php

// If this file is called directly, abort.
if(!defined('WPINC')) {
	die;
}

$list_table = new EasyCustomJsAndCss_List_Table_Filters();
$list_table->prepare_items();

?>
<div class="wrap customjscss">
	<?php require 'page-info.php'; ?>
	<h2 class="customjscss-main-title"><span><?php _e('Easy Custom JS & CSS Filters', EASYJC_PLUGIN_NAME); ?></span></h2>
	<p class="customjscss-actions">
		<a href="?page=<?php echo $_REQUEST['page']; ?>&action=new" class="page-title-action"><?php _e('Add New Filter', EASYJC_PLUGIN_NAME); ?></a>
	</p>
	<!-- customjscss app -->
	<div id="customjscss-app-filters" class="customjscss-app">
		<form method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>">
			<?php $list_table->display() ?>
		</form>
	</div>
	<!-- /end customjscss app -->
</div>