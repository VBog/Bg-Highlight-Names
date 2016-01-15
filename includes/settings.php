<?php
/******************************************************************************************
	Страница настроек плагина
	
*******************************************************************************************/
function bg_hlnames_options_page() {
	add_option('bg_hlnames_mode', "online");
	add_option('bg_hlnames_maxlinks', 0);
	add_option('bg_hlnames_target', "_blank");
	add_option('bg_hlnames_datebase', "");
	add_option('bg_hlnames_maxtime', 60);
	add_option('bg_hlnames_debug', "");
?>
<div class="wrap">
<h2><?php _e('Plugin\'s &#171;Highlight Names&#187; settings', 'bg-highlight-names') ?></h2>

<form method="post" action="options.php">
<?php wp_nonce_field('update-options'); ?>

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Plugin mode', 'bg-highlight-names') ?></th>
<td><input type="radio" name="bg_hlnames_mode" value="online" <?php if(get_option('bg_hlnames_mode')=="online") echo "checked" ?> /> <?php _e(' online ', 'bg-highlight-names') ?><br>
<input type="radio" name="bg_hlnames_mode" value="offline" <?php if(get_option('bg_hlnames_mode')=="offline") echo "checked" ?> /> <?php _e(' offline ', 'bg-highlight-names') ?><br>
<i><font color="red"><?php _e('(This mode makes permanent changes in the text of the saved post.) <br><b>We strongly recommend to keep your SQL-database dump.</b>', 'bg-highlight-names') ?></font></i><br>
<input type="radio" name="bg_hlnames_mode" disabled value="mixed" <?php if(get_option('bg_hlnames_mode')=="mixed") echo "checked" ?> /> <?php _e(' mixed ', 'bg-highlight-names') ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Limit the amount of links per person', 'bg-highlight-names') ?></th>
<td><input type="number" name="bg_hlnames_maxlinks" value="<?php echo get_option('bg_hlnames_maxlinks'); ?>" /><br>
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
<td><input type="number" name="bg_hlnames_maxtime" value="<?php echo get_option('bg_hlnames_maxtime'); ?>" /> <?php _e('sec.', 'bg-highlight-names') ?><br>
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
<p class="help">
<?php _e('Download XML schema:', 'bg-highlight-names') ?> <a href="<?php echo plugins_url( 'schema.xml', dirname(__FILE__) ); ?>">schema.xml</a><br>
<?php _e('How to create the XML with names list read', 'bg-highlight-names') ?> <a href="<?php echo plugins_url( 'readme.txt', dirname(__FILE__) ); ?>">readme.txt</a><br>
<?php _e('How to create and edit of XML-file in Excel is written in', 'bg-highlight-names') ?> <a href="https://bogaiskov.ru/xml-excel/"><?php _e(' this article', 'bg-highlight-names') ?></a>.
</p>


</form>
</div>
<?php

}
