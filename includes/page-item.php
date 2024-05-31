<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
$author = get_the_author_meta('display_name', $item->author);
$modified = mysql2date(get_option('date_format'), $item->modified) . ' at ' . mysql2date(get_option('time_format'), $item->modified);

?>
<div class="wrap customjscss">
	<?php require 'page-info.php'; ?>
	<h2 class="customjscss-main-title"><span><?php _e('Easy Custom JS & CSS', EASYJC_PLUGIN_NAME); ?></span></h2>
	<div class="customjscss-messages" id="customjscss-messages">
	</div>
	<p class="customjscss-actions">
		<a href="?page=<?php echo $page; ?>&action=new&type=js" class="page-title-action"><?php _e('Add JS Code', EASYJC_PLUGIN_NAME); ?></a>
		<a href="?page=<?php echo $page; ?>&action=new&type=css" class="page-title-action"><?php _e('Add CSS Code', EASYJC_PLUGIN_NAME); ?></a>
		<a href="?page=<?php echo $page; ?>&action=new&type=html" class="page-title-action"><?php _e('Add HTML Code', EASYJC_PLUGIN_NAME); ?></a>
	</p>
	<!-- customjscss app -->
	<div id="customjscss-app-item" class="customjscss-app" style="display:none;">
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
				<div class="customjscss-options" al-attr.class.customjscss-options-active="appData.config.options.showOptionsPanel">
					<div class="customjscss-options-header" al-on.click="appData.fn.toggleOptionsPanel(appData);">
						<h3><?php _e('Options', EASYJC_PLUGIN_NAME); ?></h3>
					</div>
					<div class="customjscss-options-data">
						<table>
							<tbody>
								<tr>
									<th><label for="customjscss-active"><?php _e('Active', EASYJC_PLUGIN_NAME); ?></label></th>
									<td><div al-checkbox="appData.config.active"></div></td>
								</tr>
								<tr al-if="appData.config.type == 'css' || appData.config.type == 'js'">
									<th>
										<label for="customjscss-minify"><?php _e('Minify output', EASYJC_PLUGIN_NAME); ?></label>
										<div al-if="appData.plan == 'lite'" class="customjscss-pro" title="<?php _e('Available only in the pro version', EASYJC_PLUGIN_NAME); ?>">Pro</div>
									</th>
									<td><div al-checkbox="appData.config.options.minify"></div></td>
								</tr>
								<tr al-if="appData.config.type == 'css'">
									<th>
										<label for="customjscss-preprocessor"><?php _e('Preprocessor', EASYJC_PLUGIN_NAME); ?></label>
										<div al-if="appData.plan == 'lite'" class="customjscss-pro" title="<?php _e('Available only in the pro version', EASYJC_PLUGIN_NAME); ?>">Pro</div>
									</th>
									<td>
										<select id="customjscss-preprocessor" al-value="appData.config.options.preprocessor">
											<option value="none"><?php _e('None', EASYJC_PLUGIN_NAME); ?></option>
											<option value="scss"><?php _e('Scss', EASYJC_PLUGIN_NAME); ?></option>
											<option value="less"><?php _e('Less', EASYJC_PLUGIN_NAME); ?></option>
										</select>
									</td>
								</tr>
								<tr al-if="appData.config.type != 'html'">
									<th><label for="customjscss-linking-type"><?php _e('Linking type', EASYJC_PLUGIN_NAME); ?></label></th>
									<td>
										<select id="customjscss-linking-type" al-value="appData.config.options.file">
											<option value="internal"><?php _e('Internal', EASYJC_PLUGIN_NAME); ?></option>
											<option value="external"><?php _e('External', EASYJC_PLUGIN_NAME); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th><label for="customjscss-where-on-page-header"><?php _e('Where on page', EASYJC_PLUGIN_NAME); ?></label></th>
									<td>
										<select id="customjscss-where-on-page-header" al-value="appData.config.options.whereonpage">
											<option value="header"><?php _e('Header', EASYJC_PLUGIN_NAME); ?></option>
											<option value="footer"><?php _e('Footer', EASYJC_PLUGIN_NAME); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th><label for="customjscss-where-in-site"><?php _e('Where in site', EASYJC_PLUGIN_NAME); ?></label></th>
									<td>
										<select id="customjscss-where-in-site" al-value="appData.config.options.whereinsite">
											<option value="user"><?php _e('User side', EASYJC_PLUGIN_NAME); ?></option>
											<option value="admin"><?php _e('Admin side', EASYJC_PLUGIN_NAME); ?></option>
											<option value="both"><?php _e('All', EASYJC_PLUGIN_NAME); ?></option>
										</select>
									</td>
								</tr>
								<tr>
									<th><label for="customjscss-filter"><?php _e('Filter', EASYJC_PLUGIN_NAME); ?></label></th>
									<td>
										<select id="customjscss-filter" al-select="appData.config.options.filter">
											<option al-repeat="filter in appData.filters" al-option="filter.id">{{filter.title}}</option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="customjscss-editor" al-attr.class.customjscss-editor-active="appData.config.options.showEditorPanel">
					<div class="customjscss-editor-header" al-on.click="appData.fn.toggleEditorPanel(appData);">
						<h3><?php _e('Editor', EASYJC_PLUGIN_NAME); ?></h3>
					</div>
					<div class="customjscss-editor-data">
						<pre id="customjscss-notepad" class="customjscss-notepad"></pre>
						<div class="customjscss-info">
							<span><?php echo sprintf(__('Last edited by %1$s on %2$s', EASYJC_PLUGIN_NAME), $author, $modified); ?></span>
							<div id="customjscss-resizable-handle" class="customjscss-resizable-handle"></div>
						</div>
					</div>
				</div>
			</div>
			<br>
			<button class="customjscss-button customjscss-button-submit" al-on.click="appData.fn.saveConfig(appData);"><?php _e('Save Changes', EASYJC_PLUGIN_NAME); ?></button>
		</div>
	</div>
	<!-- /end customjscss app -->
</div>