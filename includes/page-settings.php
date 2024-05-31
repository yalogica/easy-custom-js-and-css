<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );

?>
<div class="wrap customjscss">
	<?php require 'page-info.php'; ?>
	<h2 class="customjscss-main-title"><span><?php _e('Easy Custom JS & CSS Settings', EASYJC_PLUGIN_NAME); ?></span></h2>
	<div class="customjscss-messages" id="customjscss-messages">
	</div>
	<!-- customjscss app -->
	<div id="customjscss-app-settings" class="customjscss-app" style="display:none;">
		<div class="customjscss-loader-wrap">
			<div class="customjscss-loader">
				<div class="customjscss-loader-bar"></div>
				<div class="customjscss-loader-bar"></div>
				<div class="customjscss-loader-bar"></div>
				<div class="customjscss-loader-bar"></div>
			</div>
		</div>
		<div class="customjscss-wrap">
			<div class="customjscss-workplace">
				<div class="customjscss-section">
					<h2><?php _e('Settings', EASYJC_PLUGIN_NAME); ?></h2>
					<table>
						<tr>
							<th><label><?php _e('JavaScript Editor Theme', EASYJC_PLUGIN_NAME); ?></label></th>
							<td>
								<select al-select="appData.config.themeJavaScript">
									<option al-option="null"><?php _e('default', EASYJC_PLUGIN_NAME); ?></option>
									<option al-repeat="theme in appData.themes" al-option="theme.id">{{theme.title}}</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('CSS Editor Theme', EASYJC_PLUGIN_NAME); ?></label></th>
							<td>
								<select al-select="appData.config.themeCSS">
									<option al-option="null"><?php _e('default', EASYJC_PLUGIN_NAME); ?></option>
									<option al-repeat="theme in appData.themes" al-option="theme.id">{{theme.title}}</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('HTML Editor Theme', EASYJC_PLUGIN_NAME); ?></label></th>
							<td>
								<select al-select="appData.config.themeHTML">
									<option al-option="null"><?php _e('default', EASYJC_PLUGIN_NAME); ?></option>
									<option al-repeat="theme in appData.themes" al-option="theme.id">{{theme.title}}</option>
								</select>
							</td>
						</tr>
						<tr>
							<th><br><button class="customjscss-button customjscss-button-submit" al-on.click="appData.fn.saveConfig(appData);"><?php _e('Save Changes', EASYJC_PLUGIN_NAME); ?></button></th>
							<td></td>
						</tr>
					</table>
				</div>
				<div class="customjscss-section">
					<h2><?php _e('Actions', EASYJC_PLUGIN_NAME); ?></h2>
					<table>
						<tr>
							<th><button class="customjscss-button customjscss-button-red" al-on.click="appData.fn.deleteAllData(appData, '. <?php _e('Do you really want to delete all data?', EASYJC_PLUGIN_NAME); ?> . ');"><?php _e('Delete all data', EASYJC_PLUGIN_NAME); ?></button></th>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- /end customjscss app -->
</div>