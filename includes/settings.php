<?php
/******************************************************************************************
	Страница настроек плагина
	
*******************************************************************************************/
function bg_hlnames_options_page() {
	$debug_file = plugins_url( 'parsing.log', dirname(__FILE__) );
	bg_hlnames_add_options ();

	$active_tab = 'settings';
	if( isset( $_GET[ 'tab' ] ) ) $active_tab = $_GET[ 'tab' ];
?>
<div class="wrap">
<h2><?php _e('Plugin\'s &#171;Highlight Names&#187; settings', 'bg-highlight-names') ?></h2>
<div id="bg_hlnames_resalt"></div>
<p><?php printf( __( 'Version', 'bg-highlight-names' ).' <b>'.bg_hlnames_version().'</b>' ); ?></p>

<h2 class="nav-tab-wrapper">
	<a href="?page=bg-highlight-names%2Fbg-hlnames.php&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'bg-highlight-names') ?></a>
	<a href="?page=bg-highlight-names%2Fbg-hlnames.php&tab=data" class="nav-tab <?php echo $active_tab == 'data' ? 'nav-tab-active' : ''; ?>"><?php _e('Data', 'bg-highlight-names') ?></a>
	<a href="?page=bg-highlight-names%2Fbg-hlnames.php&tab=batch_mode" class="nav-tab <?php echo $active_tab == 'batch_mode' ? 'nav-tab-active' : ''; ?>"><?php _e('Batch mode', 'bg-highlight-names') ?></a>
</h2>

<form id="bg_hlnames_options" method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<!-- Общие Настройки -->
<?php if ($active_tab == 'settings') { ?>

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
<input type="hidden" name="page_options" value="bg_hlnames_mode, bg_hlnames_maxlinks, bg_hlnames_distance, bg_hlnames_target, bg_hlnames_classname, bg_hlnames_maxtime, bg_hlnames_debug" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>


<!-- Файл данных -->
<?php } elseif ($active_tab == 'data') { ?>

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Current XML-file', 'bg-highlight-names') ?></th>
<td><p id="bg_hlnames_datafile"><?php 
	if (get_option("bg_hlnames_datafile")) echo get_option("bg_hlnames_datafile"); 
	else _e('default', 'bg-highlight-names'); 
?></p>
<p id="bg_hlnames_current_file"><b><i><?php 
	if (get_option("bg_hlnames_datafile")) $url = get_option("bg_hlnames_datafile"); 
	else $url = dirname(dirname(__FILE__ )).'/data.xml';		// Файл по умолчанию
	$xml = file_get_contents($url);		
	$p = json_decode(json_encode((array)simplexml_load_string($xml)),1);							
	if ( isset($p['about']) ) echo $p['about'];
	else echo __('XML-file without comment.', 'bg-highlight-names');
?>
</i></b></p></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Upload XML-file', 'bg-highlight-names') ?></th>
<td>
<?php 
$url = "http://plugins.svn.wordpress.org/bg-highlight-names/xml/";
$xml = @file_get_contents($url."filelist.xml");

if ($xml) {
	$files = json_decode(json_encode((array)simplexml_load_string($xml)),1);
	$file = $files['file'];
	$cnt = count($file);
	echo "<select id='bg_hlnames_get_datafile' name='bg_hlnames_get_datafile'>";
		echo "<option value='' disabled selected>--- ".__('Select file', 'bg-highlight-names')." ---</option>";
		for ($i = 0; $i < $cnt; $i++) {
			if ($file[$i]['name']) echo "<option value='".$file[$i]['name']."' title='".$file[$i]['about']."'>".$file[$i]['name']."</option>";
		}
	echo "</select>";
}
?> <input type="button" id='bg_hlnames_upload_button' class="button" onclick="bg_hlnames_upload_datafile();" value="<?php _e('Upload', 'bg-highlight-names') ?>" />
<script>function bg_hlnames_upload_datafile() {
	datafile = document.getElementById('bg_hlnames_get_datafile').value;
	if (datafile) {
		datafile = "http://plugins.svn.wordpress.org/bg-highlight-names/xml/" + datafile;
		if (confirm("<?php _e('Really upload the file?', 'bg-highlight-names') ?>"+"\n"+datafile)) {
			jQuery.post( ajaxurl, { action: 'bg_hlnames', datafile: datafile }, function (t) {
				el = document.getElementById('bg_hlnames_resalt');
				if (t[0] == '*') {
					el.innerHTML  = '<?php _e('XML-file uploaded.', 'bg-highlight-names'); ?>';
					el.className  = "updated";
					document.getElementById('bg_hlnames_datafile').innerHTML = "<font color='darkblue'>"+datafile+"</font>";
					document.getElementById('bg_hlnames_current_file').innerHTML = "<b><i><font color='darkblue'>"+t.substr(1)+"</font></i></b>";
				}
				else if (t[0] == '~') {
					el.innerHTML  = t.substr(1);
					el.className  = "update-nag";
				}
				else {
					if (!t) t="<?php _e('No response.', 'bg-highlight-names'); ?>";
					el.innerHTML  = t;
					el.className  = "error";
				}
			} );
		}
	}
}</script>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Custom XML-file with names list', 'bg-highlight-names') ?></th>
<td><input type="search" id="bg_hlnames_datebase" name="bg_hlnames_datebase" value="<?php echo get_option('bg_hlnames_datebase'); ?>" onblur='bg_hlnames_datebase_check();' /><br>
<i><?php _e('(Specify a local URL of XML-file that contain the names to highlight them in text. <br> Leave blank to use the XML-file by default.)', 'bg-highlight-names') ?></i></td>
</tr>
<script>function bg_hlnames_datebase_check() {
	datafile = document.getElementById('bg_hlnames_datebase').value;
	jQuery.post( ajaxurl, { action: 'bg_hlnames', database: datafile }, function (t) {
		el = document.getElementById('bg_hlnames_resalt');
		if (t[0] == '*') {
			el.innerHTML  = '<?php _e("XML-file changed. Don\'t forget save settings.", 'bg-highlight-names'); ?>';
			el.className  = "update-nag";
			document.getElementById('bg_hlnames_datafile').innerHTML = "<font color='darkblue'>"+datafile+"</font>";
			document.getElementById('bg_hlnames_current_file').innerHTML = "<b><i><font color='darkblue'>"+t.substr(1)+"</font></i></b>";
		}
		else {
			if (!t) {
				el.innerHTML  = '<?php _e("XML-file set to default. Don\'t forget save settings.", 'bg-highlight-names'); ?>';
				el.className  = "update-nag";
				document.getElementById('bg_hlnames_datafile').innerHTML = "<font color='darkblue'><?php _e('default', 'bg-highlight-names') ?></font>";
				document.getElementById('bg_hlnames_current_file').innerHTML = "<b><i><font color='darkblue'></font></i></b>";
				
			} else {	
				el.innerHTML  = t;
				el.className  = "error";
			}
		}
	} );

}</script>

</table>
 
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value=" bg_hlnames_datebase" />

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>

<p class="help">
<?php _e('XML-files and Excel templates on', 'bg-highlight-names') ?> <a href="http://plugins.svn.wordpress.org/bg-highlight-names/xml/"><?php _e(' WordPress.org', 'bg-highlight-names') ?></a>.<br>
<?php _e('Download XML schema:', 'bg-highlight-names') ?> <a href="<?php echo plugins_url( 'schema.xml', dirname(__FILE__) ); ?>">schema.xml</a><br>
<?php _e('How to create the XML with names list read', 'bg-highlight-names') ?> <a href="<?php echo plugins_url( 'readme.txt', dirname(__FILE__) ); ?>">readme.txt</a><br>
<?php _e('How to create and edit of XML-file in Excel is written in', 'bg-highlight-names') ?> <a href="https://bogaiskov.ru/xml-excel/"><?php _e(' this article', 'bg-highlight-names') ?></a>.
</p>


<!-- Пакетная обработка -->
<?php } elseif ($active_tab == 'batch_mode') { ?>

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Batch mode<br>(depend on plugin mode)', 'bg-highlight-names') ?></th>
<td><p id="bg_hlnames_batch_mode_title"></p>
<?php _e('Parse posts: start #', 'bg-highlight-names') ?> <input type="number" id="bg_hlnames_start_no" name="bg_hlnames_start_no" min="1" value="<?php echo get_option('bg_hlnames_start_no') ?>" /> 
<?php " "._e('finish #', 'bg-highlight-names') ?> <input type="number" id="bg_hlnames_finish_no" name="bg_hlnames_finish_no" min="1" value="<?php echo get_option('bg_hlnames_finish_no') ?>" /> (max.: <?php echo bg_hlnames_count_posts (); ?>)</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Do not clean before parsing', 'bg-highlight-names') ?></th>
<td><input type="checkbox" id="bg_hlnames_not_clean" name="bg_hlnames_not_clean" <?php if(get_option('bg_hlnames_not_clean')) echo "checked" ?> value="on" /><br>
<?php _e('Note: New links will be added to the existing ones.', 'bg-highlight-names') ?></td>
</tr>

<tr valign="top">
<th scope="row"><input type="button" id='bg_hlnames_backend_button' class="button" <?php if(get_option('bg_hlnames_in_progress')) echo "disabled" ?> onclick="bg_hlnames_parse_posts('go');" value="<?php _e('Parse posts', 'bg-highlight-names') ?>" /></th>
<td>
<span id="bg_hlnames_warning" style="color: red;" ><i><?php _e('(It makes permanent changes in the text of all pages and posts.) <br><b>We strongly recommend to keep your SQL-database dump.</b>', 'bg-highlight-names') ?></i></span>
<span id="bg_hlnames_wait" style="color: darkblue; display: none;" ><b><?php _e('Don\'t close or update this tab. Parsing in progress!<br>Wait, please.', 'bg-highlight-names'); ?></b></span><br>
<?php _e('Don\'t forget save options before start of batch mode!', 'bg-highlight-names') ?><br>
<?php _e('For detail of results see: ', 'bg-highlight-names') ?> <a href='<?php echo $debug_file; ?>' target='_blank'>parsing.log</a></td>
</tr>

</table>

<script>
if (bg_hlnames_in_progress (<?php echo "'".get_option('bg_hlnames_in_progress')."'"; ?>) == 'on') {
	document.getElementById('bg_hlnames_resalt').innerHTML  = "<?php _e("You had reloaded this tab. Sorry, but you can not watch the process here. Check the log file to see the result of parsing. When the process is completed, just reload the tab again.<br>To interrupt the process, deactivate and then activate the plugin on the plugins page.", 'bg-highlight-names'); ?>";
	document.getElementById('bg_hlnames_resalt').className  = "update-nag";
}

jQuery( "document" ).ready( bg_hlnames_batch_mode_title);
//jQuery( "input[name='bg_hlnames_mode']" ).on( "click", bg_hlnames_batch_mode_title);
jQuery( "#bg_hlnames_not_clean" ).on( "click", bg_hlnames_batch_mode_title);

function bg_hlnames_batch_mode_title() {
//	var mode = jQuery( "input[name='bg_hlnames_mode']:checked" ).val();
	var mode = '<?php echo get_option('bg_hlnames_mode'); ?>';
//	alert(mode);
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
		var  not_clean = (document.getElementById('bg_hlnames_not_clean').checked)? 'on' : '';

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
				finish_no: finish_no,
				not_clean: not_clean
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

<?php } ?>

</form>
</div>
<?php

}
