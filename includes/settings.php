<?php
/******************************************************************************************
	Страница настроек плагина
	
*******************************************************************************************/
function bg_hlnames_options_page() {
	$debug_file = plugins_url( 'parsing.log', dirname(__FILE__) );

	add_option('bg_hlnames_in_progress', "");
	
	add_option('bg_hlnames_mode', "online");
	add_option('bg_hlnames_maxlinks', 0);
	add_option('bg_hlnames_target', "_blank");
	add_option('bg_hlnames_datebase', "");
	add_option('bg_hlnames_maxtime', 60);
	add_option('bg_hlnames_debug', "");
?>
<div class="wrap">
<h2><?php _e('Plugin\'s &#171;Highlight Names&#187; settings', 'bg-highlight-names') ?></h2>
<div id="bg_hlnames_resalt"></div>
<p><?php printf( __( 'Version', 'bg-highlight-names' ).' <b>'.bg_hlnames_version().'</b>' ); ?></p>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Plugin mode', 'bg-highlight-names') ?></th>
<td><input type="radio" name="bg_hlnames_mode" value="online" <?php if(get_option('bg_hlnames_mode')=="online") echo "checked" ?> /> <b><?php _e('online', 'bg-highlight-names') ?></b> <i><?php _e('(In this mode the plugin highlights the names only when text displays on the screen.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="offline" <?php if(get_option('bg_hlnames_mode')=="offline") echo "checked" ?> /> <b><?php _e('offline', 'bg-highlight-names') ?></b> <i><?php _e('(This mode makes permanent changes in the text of the saved posts.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="mixed" <?php if(get_option('bg_hlnames_mode')=="mixed") echo "checked" ?> /> <b><?php _e('mixed', 'bg-highlight-names') ?></b> <i><?php _e('(Mixing online & offline mode. Highlight the names when text displays on the screen, only if the text doesn\'t include links for names.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="clear" <?php if(get_option('bg_hlnames_mode')=="clear") echo "checked" ?> /> <b><?php _e('clear', 'bg-highlight-names') ?></b> <i><?php _e('(Removes links to the names from the text.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="" <?php if(get_option('bg_hlnames_mode')=="") echo "checked" ?> /> <b><?php _e('off', 'bg-highlight-names') ?></b> <i><?php _e('(The plugin does not work (batch mode only).)', 'bg-highlight-names') ?></i></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Batch mode', 'bg-highlight-names') ?></th>
<td><b><?php _e('Removes links (and/or highlight names) in all pages and posts in offline mode', 'bg-highlight-names') ?></b><br>
<?php _e('Parse posts: start #', 'bg-highlight-names') ?> <input type="number" id="bg_hlnames_start_no" min="1" value="1" /> <?php _e('finish #', 'bg-highlight-names') ?> <input type="number" id="bg_hlnames_finish_no" min="1" value="<?php echo bg_hlnames_count_posts (); ?>" /></br>
<button id='bg_hlnames_backend_button' type="button" class="button-primary" style="float: left; margin: 3px 10px 3px 0px;" <?php if(get_option('bg_hlnames_in_progress')) echo "disabled" ?> onclick="bg_hlnames_parse_posts ();"><?php _e('Parse all posts', 'bg-highlight-names') ?></button>
<span id="bg_hlnames_warning" style="color: red;" ><i><?php _e('(It makes permanent changes in the text of all pages and posts.) <br><b>We strongly recommend to keep your SQL-database dump.</b>', 'bg-highlight-names') ?></i></span>
<span id="bg_hlnames_wait" style="color: darkblue; display: none;" ><b><?php _e('Don\'t close this tab. Parsing in progress!<br>Wait, please.', 'bg-highlight-names') ?></b></span><br>
<?php _e('For detail see: ', 'bg-highlight-names') ?> <a href='<?php echo $debug_file; ?>' target='_blank'>parsing.log</a></td>
</tr>


<tr valign="top">
<th scope="row"><?php _e('Limit the amount of links per person', 'bg-highlight-names') ?></th>
<td><input type="number" name="bg_hlnames_maxlinks" min="0" value="<?php echo get_option('bg_hlnames_maxlinks'); ?>" /><br>
<?php _e('(0 - no limits).', 'bg-highlight-names') ?></td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('Open links', 'bg-highlight-names') ?></th>
<td><input type="radio" name="bg_hlnames_target" value="_blank" <?php if(get_option('bg_hlnames_target')=="_blank") echo "checked" ?> /> <?php _e('in blank window', 'bg-highlight-names') ?><br>
<input type="radio" name="bg_hlnames_target" value="_self" <?php if(get_option('bg_hlnames_target')=="_self") echo "checked" ?> /> <?php _e('in self window', 'bg-highlight-names') ?></td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('XML-file with names list', 'bg-highlight-names') ?></th>
<td><input type="text" name="bg_hlnames_datebase" value="<?php echo get_option('bg_hlnames_datebase'); ?>" /><br>
<i><?php _e('(Specify a local URL of XML-file that contain the names to highlight them in text. <br> Leave blank to use the XML-file by default.)', 'bg-highlight-names') ?></i></td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('The maximum execution time', 'bg-highlight-names') ?></th>
<td><input type="number" name="bg_hlnames_maxtime" min="0" value="<?php echo get_option('bg_hlnames_maxtime'); ?>" /> <?php _e('sec.', 'bg-highlight-names') ?><br>
<?php _e('(0 - no limits).', 'bg-highlight-names') ?></td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('Show debug info at the end of the post', 'bg-highlight-names') ?></th>
<td><input type="checkbox" name="bg_hlnames_debug" <?php if(get_option('bg_hlnames_debug')) echo "checked" ?> value="on" /></td>
</tr>

 </table>

<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="bg_hlnames_mode, bg_hlnames_maxlinks, bg_hlnames_target, bg_hlnames_datebase, bg_hlnames_maxtime, bg_hlnames_debug" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

</form>
</div>

<p class="help">
<?php _e('Download XML schema:', 'bg-highlight-names') ?> <a href="<?php echo plugins_url( 'schema.xml', dirname(__FILE__) ); ?>">schema.xml</a><br>
<?php _e('How to create the XML with names list read', 'bg-highlight-names') ?> <a href="<?php echo plugins_url( 'readme.txt', dirname(__FILE__) ); ?>">readme.txt</a><br>
<?php _e('How to create and edit of XML-file in Excel is written in', 'bg-highlight-names') ?> <a href="https://bogaiskov.ru/xml-excel/"><?php _e(' this article', 'bg-highlight-names') ?></a>.
</p>
<script>
bg_hlnames_in_progress (<?php echo "'".get_option('bg_hlnames_in_progress')."'"; ?>);

function bg_hlnames_in_progress (status) {
	if (status == 'on') {
		document.getElementById('bg_hlnames_backend_button').disabled = true;
		document.getElementById('bg_hlnames_warning').style.display = "none";
		document.getElementById('bg_hlnames_wait').style.display = "";
		document.getElementById('bg_hlnames_resalt').innerHTML  = "";
		document.getElementById('bg_hlnames_resalt').className  = "";
	} else {
		document.getElementById('bg_hlnames_backend_button').disabled = false;
		document.getElementById('bg_hlnames_warning').style.display = "";
		document.getElementById('bg_hlnames_wait').style.display = "none";
	}	
}

function bg_hlnames_parse_posts () {
	var doParse = confirm("<?php _e('Really highlight names in all posts and pages?', 'bg-highlight-names') ?>");
	if (doParse) doParse = confirm("<?php _e('Are you sure?', 'bg-highlight-names') ?>");
	if (doParse) {
		bg_hlnames_in_progress ('on');
		var start_no = document.getElementById('bg_hlnames_start_no').value;
		var finish_no = document.getElementById('bg_hlnames_finish_no').value;
		jQuery.ajax({
			type: 'GET',
			cache: false,
			async: true,		// Асинхронный запрос
			dataType: 'text',
			url: ajaxurl,		// Запрос на обработку постов
			data: {
				action: 'bg_hlnames',
				parseallposts: 'go',
				start_no: start_no,
				finish_no: finish_no
			},
			success: function (t) {
				el = document.getElementById('bg_hlnames_resalt');
				if (t[0] == '*') {
					el.innerHTML  = "<p><b>"+t+"</b></p>";
					el.className  = "updated";
					bg_hlnames_in_progress ('');
				}
				else if (t[0] == '~') {
					el.innerHTML  = "<p><b>"+t+"</b></p>";
					el.className  = "update-nag";
				}
				else {
					if (!t) t="<?php _e('No response.', 'bg-highlight-names'); ?>";
					el.innerHTML  = "<p>"+"<b><?php _e('Process aborted.', 'bg-highlight-names'); ?> </b>"+t+"</p>";
					el.className  = "error";
					var data = {
						action: 'bg_hlnames',
						parseallposts: 'reset'
					};
					jQuery.post( ajaxurl, data, function(response) {bg_hlnames_in_progress ('');});
				}
			}
		});
	}
}
</script>
<?php

}
function bg_hlnames_count_posts () {
	$count_posts = wp_count_posts()->publish;
	return $count_posts;
}
