<?php
/******************************************************************************************
	Страница настроек плагина
	
*******************************************************************************************/
function bg_hlnames_options_page() {
	$debug_file = plugins_url( 'parsing.log', dirname(__FILE__) );

	add_option('bg_hlnames_in_progress', "");
	add_option('bg_hlnames_start_old', 0);

	add_option('bg_hlnames_start_no', 1);
	add_option('bg_hlnames_finish_no', bg_hlnames_count_posts ());
	
	add_option('bg_hlnames_mode', "online");
	add_option('bg_hlnames_not_clean', "");
	add_option('bg_hlnames_maxlinks', 0);
	add_option('bg_hlnames_distance', 0);
	add_option('bg_hlnames_target', "_blank");
	add_option('bg_hlnames_datebase', "");
	add_option('bg_hlnames_classname', "");
	add_option('bg_hlnames_maxtime', 60);
	add_option('bg_hlnames_debug', "");
?>
<div class="wrap">
<h2><?php _e('Plugin\'s &#171;Highlight Names&#187; settings', 'bg-highlight-names') ?></h2>
<div id="bg_hlnames_resalt"></div>
<p><?php printf( __( 'Version', 'bg-highlight-names' ).' <b>'.bg_hlnames_version().'</b>' ); ?></p>

<form id="bg_hlnames_options" method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Plugin mode', 'bg-highlight-names') ?></th>
<td><input type="radio" name="bg_hlnames_mode" value="online" <?php if(get_option('bg_hlnames_mode')=="online") echo "checked" ?> /> <b><?php _e('online', 'bg-highlight-names') ?></b> <i><?php _e('(In this mode the plugin highlights the names only when text displays on the screen.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="offline" <?php if(get_option('bg_hlnames_mode')=="offline") echo "checked" ?> /> <b><?php _e('offline', 'bg-highlight-names') ?></b> <i><?php _e('(This mode makes permanent changes in the text in editor mode.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="mixed" <?php if(get_option('bg_hlnames_mode')=="mixed") echo "checked" ?> /> <b><?php _e('mixed', 'bg-highlight-names') ?></b> <i><?php _e('(Mixing online & offline mode. Highlight the names when text displays on the screen, only if the text doesn\'t include links for names.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="clear" <?php if(get_option('bg_hlnames_mode')=="clear") echo "checked" ?> /> <b><?php _e('clear', 'bg-highlight-names') ?></b> <i><?php _e('(Removes links to the names from the text in editor and batch modes.)', 'bg-highlight-names') ?></i><br>
<input type="radio" name="bg_hlnames_mode" value="" <?php if(get_option('bg_hlnames_mode')=="") echo "checked" ?> /> <b><?php _e('off', 'bg-highlight-names') ?></b> <i><?php _e('(The plugin does not work (batch mode only).)', 'bg-highlight-names') ?></i></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Batch mode<br>(depend on plugin mode)', 'bg-highlight-names') ?></th>
<td><p id="bg_hlnames_batch_mode_title"></p>
<?php _e('Parse posts: start #', 'bg-highlight-names') ?> <input type="number" id="bg_hlnames_start_no" name="bg_hlnames_start_no" min="1" value="<?php echo get_option('bg_hlnames_start_no') ?>" /> 
<?php " "._e('finish #', 'bg-highlight-names') ?> <input type="number" id="bg_hlnames_finish_no" name="bg_hlnames_finish_no" min="1" value="<?php echo get_option('bg_hlnames_finish_no') ?>" /> (max.: <?php echo bg_hlnames_count_posts (); ?>)</br>
<input type="button" id='bg_hlnames_backend_button' class="button" style="float: left; margin: 3px 10px 3px 0px;" <?php if(get_option('bg_hlnames_in_progress')) echo "disabled" ?> onclick="bg_hlnames_parse_posts('go');" value="<?php _e('Parse posts', 'bg-highlight-names') ?>" />
<span id="bg_hlnames_warning" style="color: red;" ><i><?php _e('(It makes permanent changes in the text of all pages and posts.) <br><b>We strongly recommend to keep your SQL-database dump.</b>', 'bg-highlight-names') ?></i></span>
<span id="bg_hlnames_wait" style="color: darkblue; display: none;" ><b><?php _e('Don\'t close or update this tab. Parsing in progress!<br>Wait, please.', 'bg-highlight-names'); ?></b></span><br>
<?php _e('Don\'t forget save options before start of batch mode!', 'bg-highlight-names') ?><br>
<?php _e('For detail of results see: ', 'bg-highlight-names') ?> <a href='<?php echo $debug_file; ?>' target='_blank'>parsing.log</a></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Do not clean before parsing', 'bg-highlight-names') ?></th>
<td><input type="checkbox" id="bg_hlnames_not_clean" name="bg_hlnames_not_clean" <?php if(get_option('bg_hlnames_not_clean')) echo "checked" ?> value="on" /> <?php _e('in batch mode only', 'bg-highlight-names') ?><br>
<?php _e('(Note: New links will be added to the existing ones.)', 'bg-highlight-names') ?></td>
</tr>


<tr valign="top">
<th scope="row"><?php _e('Limit the amount of links per person', 'bg-highlight-names') ?></th>
<td><input type="number" name="bg_hlnames_maxlinks" min="0" value="<?php echo get_option('bg_hlnames_maxlinks'); ?>" /><br>
<?php _e('(0 - no limits).', 'bg-highlight-names') ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('The minimum number of chars between two links to one person', 'bg-highlight-names') ?></th>
<td><input type="number" name="bg_hlnames_distance" min="0" value="<?php echo get_option('bg_hlnames_distance'); ?>" /></td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('Where open links?', 'bg-highlight-names') ?></th>
<td><input type="text" name="bg_hlnames_target" value="<?php echo get_option('bg_hlnames_target'); ?>" /><br> 
<b>_blank</b> - <?php _e('in blank window', 'bg-highlight-names') ?>, <b>_self</b> - <?php _e('in self window', 'bg-highlight-names') ?> <?php _e('or any other window name', 'bg-highlight-names') ?>.</td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('Additional class for links', 'bg-highlight-names') ?></th>
<td><input type="text" name="bg_hlnames_classname" value="<?php echo get_option('bg_hlnames_classname'); ?>" /></td>
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
<input type="hidden" name="page_options" value="bg_hlnames_start_no, bg_hlnames_finish_no, bg_hlnames_mode, bg_hlnames_not_clean, bg_hlnames_maxlinks, bg_hlnames_distance, bg_hlnames_target, bg_hlnames_classname, bg_hlnames_datebase, bg_hlnames_maxtime, bg_hlnames_debug" />

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
if (bg_hlnames_in_progress (<?php echo "'".get_option('bg_hlnames_in_progress')."'"; ?>) == 'on') {
	document.getElementById('bg_hlnames_resalt').innerHTML  = "<?php _e("You had reloaded this tab. Sorry, but you can not watch the process here. Check the log file to see the result of parsing. When the process is completed, just reload the tab again.<br>To interrupt the process, deactivate and then activate the plugin on the plugins page.", 'bg-highlight-names'); ?>";
	document.getElementById('bg_hlnames_resalt').className  = "update-nag";
}

jQuery( "document" ).ready( bg_hlnames_batch_mode_title);
jQuery( "input[name='bg_hlnames_mode']" ).on( "click", bg_hlnames_batch_mode_title);
jQuery( "#bg_hlnames_not_clean" ).on( "click", bg_hlnames_batch_mode_title);

function bg_hlnames_batch_mode_title() {
	var mode = jQuery( "input[name='bg_hlnames_mode']:checked" ).val();
	if (mode == 'clear') {
		document.getElementById('bg_hlnames_not_clean').checked = false;
		document.getElementById('bg_hlnames_not_clean').disabled = true;
	} else {
		document.getElementById('bg_hlnames_not_clean').disabled = false;
	}
	var no_clean = document.getElementById('bg_hlnames_not_clean').checked;
	switch (mode) {
		case 'mixed':
			if (no_clean) title = "<b><?php _e('Adds links in pages and posts where the links don\'t yet added only', 'bg-highlight-names') ?></b>";
			else title = "<b><?php _e('Removes and then adds links in all pages and posts', 'bg-highlight-names') ?></b>";
			break;
		case 'clear':
			title = "<b><?php _e('Removes links from all pages and posts', 'bg-highlight-names') ?></b>";
			break;
		default:
			if (no_clean) title = "<b><?php _e('Adds links in all pages and posts', 'bg-highlight-names') ?></b>";
			else title = "<b><?php _e('Removes and then adds links in all pages and posts', 'bg-highlight-names') ?></b>";
	}
	document.getElementById('bg_hlnames_batch_mode_title').innerHTML  = title;
}

function bg_hlnames_parse_posts_repiad () {
	bg_hlnames_parse_posts ('repiad');		// Повторим еще раз
}
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
	return status;
}

function bg_hlnames_parse_posts (process) {
	if (process == 'repiad' || confirm("<?php _e('Really begin highlight names in all posts and pages in batch mode?', 'bg-highlight-names') ?>")) {
		if (process != 'repiad') bg_hlnames_in_progress ('on');
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
				parseallposts: process,
				start_no: start_no,
				finish_no: finish_no
			},
			success: function (t, textStatus) {
				el = document.getElementById('bg_hlnames_resalt');
				if (t[0] == '*') {
					el.innerHTML  = t.substr(1);
					document.getElementById('bg_hlnames_start_no').value=document.getElementById('bg_hlnames_result_p').getAttribute('start_no');
					document.getElementById('bg_hlnames_finish_no').value=document.getElementById('bg_hlnames_result_p').getAttribute('finish_no');
					el.className  = "updated";
					bg_hlnames_in_progress ('');
				}
				else if (t[0] == '~') {
					el.innerHTML  = t.substr(1);
					document.getElementById('bg_hlnames_start_no').value=document.getElementById('bg_hlnames_result_p').getAttribute('start_no');
					document.getElementById('bg_hlnames_finish_no').value=document.getElementById('bg_hlnames_result_p').getAttribute('finish_no');
					el.className  = "update-nag";
					bg_hlnames_in_progress ('');
				}
				else {
					if (!t) t="<?php _e('No response.', 'bg-highlight-names'); ?>";
					bg_hlnames_show_errorcode (el, textStatus, t);
				}
			},
			error: function (e, textStatus) {
				el = document.getElementById('bg_hlnames_resalt');
				t = " <b>" + e.status + "</b> " + e.responseText;
				bg_hlnames_show_errorcode (el, textStatus, t);
			}
		});
	}
}
function bg_hlnames_show_errorcode (el, textStatus, t) {
	date = new Date();
	if (textStatus == 'error' || textStatus == 'parsererror' ||  textStatus == 'timeout') {	// error, parsererror, timeout
		el.innerHTML  = date.toLocaleString("ru")+" <p><b>"+textStatus+". <?php _e('Fatal error:', 'bg-highlight-names'); ?> </b>"+t+" <?php _e('Try again...', 'bg-highlight-names'); ?></p>";
		el.className  = "error";
		bg_hlnames_parse_posts ('repiad');		// Повторим еще раз
	} else if (textStatus == 'abort') {														// abort
		el.innerHTML  = date.toLocaleString("ru")+" <p><b>"+textStatus+". <?php _e('Process aborted.', 'bg-highlight-names'); ?> </b>"+t+"</p>";
		el.className  = "error";
		bg_hlnames_in_progress ('');
	} else {																				// notmodified, success
		el.innerHTML  = date.toLocaleString("ru")+" <p><b>"+textStatus+". <?php _e('Warning:', 'bg-highlight-names'); ?> </b>"+t+"</p>";
			el.className  = "update-nag";
			bg_hlnames_in_progress ('');
	}
}
</script>
<?php

}
function bg_hlnames_count_posts () {
	$count_posts = wp_count_posts()->publish;
	return $count_posts;
}
