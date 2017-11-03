<?php
/*
Plugin Name: Bg Highlight Names
Plugin URI: https://bogaiskov.ru/highlight-names/
Description: Highlight Russian names in text of posts and pages.
Version: 1.2.3
Author: VBog
Author URI: http://bogaiskov.ru
Text Domain: bg-highlight-names
Domain Path: /languages
*/

/*  Copyright 2016  Vadim Bogaiskov  (email: vadim.bogaiskov@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*****************************************************************************************
	Блок загрузки плагина
	
******************************************************************************************/

// Запрет прямого запуска скрипта
if ( !defined('ABSPATH') ) {
	die( 'Sorry, you are not allowed to access this page directly.' ); 
}
define('BG_HLNAMES_VERSION', '1.2.3');


$bg_hlnames_start_time = microtime(true);

// Загрузка интернационализации
add_action( 'plugins_loaded', 'bg_highlight_load_textdomain' );
function bg_highlight_load_textdomain() {
  load_plugin_textdomain( 'bg-highlight-names', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}
// Функция, исполняемая при активации плагина.
function  bg_highlight_activate() {
	delete_option('bg_hlnames_in_progress');	
	bg_hlnames_copy_datafile();
}
register_activation_hook( __FILE__, 'bg_highlight_activate' );

// Подключаем дополнительные модули
include_once('includes/settings.php' );

// Задаем начальные значения параметров
bg_hlnames_add_options ();

ini_set('memory_limit', '256M');


// Проверяем текущую версию плагина и обновляем файл данных
function bg_hlnames_update_datefile() {
	if ( version_compare( get_option('bg_hlnames_version'), BG_HLNAMES_VERSION, '<' ) ) {
		bg_hlnames_copy_datafile();
		update_option( 'bg_hlnames_version', BG_HLNAMES_VERSION );
	}
}
add_action( 'plugins_loaded', 'bg_hlnames_update_datefile' );

if ( defined('ABSPATH') && defined('WPINC') ) {
	$plugin_mode = get_option('bg_hlnames_mode');
// Регистрируем крючок для обработки контента при его загрузке
	if ($plugin_mode == "online") {
		add_filter( 'the_content', 'bg_hlnames_proc', 9 );
		add_filter( 'the_excerpt', 'bg_hlnames_proc', 9 );
	}
// Регистрируем крючок для обработки контента при его сохранении в БД
	elseif ($plugin_mode == "offline") add_action('wp_insert_post_data', 'bg_hlnames_post_save', 20, 2 );
// Регистрируем крючок для обработки контента при его загрузке 
// и крючок для обработки контента при его сохранении в БД 
	elseif ($plugin_mode == "mixed") {
		add_filter( 'the_content', 'bg_hlnames_proc', 9 );
		add_filter( 'the_excerpt', 'bg_hlnames_proc', 9 );
		add_action('wp_insert_post_data', 'bg_hlnames_post_save', 20, 2 );
	}
// Регистрируем крючок для обработки контента при его сохранении в БД (удаление ссылок)
	elseif ($plugin_mode == "clear") add_action('wp_insert_post_data', 'bg_hlnames_post_clear', 20, 2 );
}

// Регистрируем шорт-код nonames
add_shortcode( 'nonames', 'bg_hlnames_nonames' );
// [nonames]
function bg_hlnames_nonames( $atts, $content = null ) {
	 return do_shortcode($content);
}

$bg_hlnames_maxlinks = (int) get_option('bg_hlnames_maxlinks');
$bg_hlnames_distance = (int) get_option('bg_hlnames_distance');
$bg_hlnames_debug_file = dirname(__FILE__ )."/parsing.log";
$bg_hlnames_start_old = 0;

/*****************************************************************************************
	Функции запуска плагина
	
******************************************************************************************/
 
// Функция обработки списка имён
function bg_hlnames_proc($content) {
	global $bg_hlnames_debug_file, $bg_hlnames_start_time ;

	$mode = get_option('bg_hlnames_mode');
	if ($mode=='mixed' && strstr ( $content ,'bg_hlnames' )) return $content;

	$maxtime = get_option('bg_hlnames_maxtime');
	if (!function_exists ('set_time_limit') || !(@set_time_limit ($maxtime))) {
		$systemtime = ini_get('max_execution_time'); 
		if (!$systemtime) $systemtime = 30;
		else $systemtime = intval($systemtime);
		if (get_option('bg_hlnames_debug')) {
			$content .= '<p class="bg_hlnames_debug">'.sprintf(__( 'The maximum execution time (%1$s sec.) could not be set. System limits the maximum execution time of %2$s sec.', 'bg-highlight-names'), $maxtime, $systemtime).'</p>';
		}
		if ( !empty($_GET['parseallposts'])) error_log(sprintf( 'The maximum execution time (%1$s sec.) could not be set. System limits the maximum execution time of %2$s sec. ', $maxtime, $systemtime), 3, $bg_hlnames_debug_file);
		
		$pretime = microtime(true) - $bg_hlnames_start_time;
		$maxtime = $systemtime - $pretime - 2;
		if ($maxtime < 2) return $content;
	} 
	$bg_hlnames = new BgHighlightNames();
	
	$content = $bg_hlnames->proc($content, $maxtime);
	return $content;
}
// Функция очистки от ссылок списка имён
function bg_hlnames_clear($content) {
	global $bg_hlnames_debug_file, $bg_hlnames_start_time ;

	$maxtime = get_option('bg_hlnames_maxtime');
	if (!function_exists ('set_time_limit') || !(@set_time_limit ($maxtime))) {
		$systemtime = ini_get('max_execution_time'); 
		if (!$systemtime) $systemtime = 30;
		else $systemtime = intval($systemtime);
		if (get_option('bg_hlnames_debug')) {
			$content .= '<p class="bg_hlnames_debug">'.sprintf(__( 'The maximum execution time (%1$s sec.) could not be set. System limits the maximum execution time of %2$s sec.', 'bg-highlight-names'), $maxtime, $systemtime).'</p>';
		}
		if ( !empty($_GET['parseallposts'])) error_log(sprintf('The maximum execution time (%1$s sec.) could not be set. System limits the maximum execution time of %2$s sec. ', $maxtime, $systemtime), 3, $bg_hlnames_debug_file);
		$pretime = microtime(true) - $bg_hlnames_start_time;
		$maxtime = $systemtime - $pretime - 2;
		if ($maxtime < 2) return $content;
	}
	$bg_hlnames = new BgHighlightNames();
	$content = $bg_hlnames->clear($content);
	return $content;
}
// Функция добавления ссылок к именам в офлайн режиме
function bg_hlnames_post_save( $data, $postarr ){
	if( isset($_POST['post_type']) && ($_POST['post_type'] == 'post' || $_POST['post_type'] == 'page') ) { 	// убедимся что мы редактируем нужный тип поста
		if( get_current_screen()->id != 'post' && get_current_screen()->id != 'post') return $data; 		// убедимся что мы на нужной странице админки
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return $data; 					// пропустим если это автосохранение
		if ( ! current_user_can('edit_post', $postarr['ID'] ) ) return $data; 				// убедимся что пользователь может редактировать запись

		// Все ОК! обрабатываем
		$data['post_content'] = bg_hlnames_clear($data['post_content']);	// Сначала удаляем ранее установленные ссылки
		$data['post_content'] = bg_hlnames_proc($data['post_content']);		// Затем устанавливаем новые ссылки
	}
	return $data;
}
// Функция удаления ссылок в офлайн режиме
function bg_hlnames_post_clear( $data, $postarr ){
	if( isset($_POST['post_type']) && ($_POST['post_type'] == 'post' || $_POST['post_type'] == 'page') ) { 	// убедимся что мы редактируем нужный тип поста
		if( get_current_screen()->id != 'post' && get_current_screen()->id != 'post') return $data; 		// убедимся что мы на нужной странице админки
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return $data; 					// пропустим если это автосохранение
		if ( ! current_user_can('edit_post', $postarr['ID'] ) ) return $data; 				// убедимся что пользователь может редактировать запись

		// Все ОК! обрабатываем
		$data['post_content'] =  bg_hlnames_clear($data['post_content']);
	}
	return $data;
}

// Функция загрузки файла данных из репозитория
function bg_hlnames_copy_datafile() {
	$datafile = get_option( 'bg_hlnames_datafile' );
	if (isset($datafile) && $datafile) {
		copy ( $datafile, dirname(__FILE__ ).'/data.xml' );
	}
}

// Hook for adding admin menus
if ( is_admin() ){ 				// admin actions
	add_action('admin_menu', 'bg_hlnames_add_pages');
}
// action function for above hook
function  bg_hlnames_add_pages() {
    // Add a new submenu under Options:
    add_options_page(__('Plugin\'s &#171;Highlight Names&#187; settings', 'bg-highlight-names'), __('Highlight names', 'bg-highlight-names'), 'manage_options', __FILE__, 'bg_hlnames_options_page');
}
/*****************************************************************************************
	Генератор ответа AJAX
	
******************************************************************************************/
add_action ('wp_ajax_bg_hlnames', 'bg_hlnames_callback');
//add_action ('wp_ajax_nopriv_bg_hlnames', 'bg_hlnames_callback');

function bg_hlnames_callback() {
	
	global $bg_hlnames_debug_file;
	$param = new stdClass();

	if (isset($_POST['datafile']) && $_POST['datafile']) {
		$datafile = $_POST['datafile'];
		$url = dirname(__FILE__ ).'/data.xml';		// Файл по умолчанию
		if (copy ( $datafile, $url )) {
			update_option( 'bg_hlnames_datafile', $datafile );
			$xml = file_get_contents($url);		
			$p = json_decode(json_encode((array)simplexml_load_string($xml)),1);							
			if ( isset($p['about']) ) echo '*'.$p['about'];
			else echo '*'.__('XML-file without comment.', 'bg-highlight-names');

		}
		else echo '*'.__('Error while XML-file uploading.', 'bg-highlight-names');
		wp_die();
	}
	if (isset($_POST['database'])) {
		$datafile = trim($_POST['database']);
		if ( $datafile ) {
			if ($datafile[0] == '/') $url = get_home_url().$datafile;
			else $url = get_home_url().'/'.$datafile;
			$xml = @file_get_contents($url);	
			if ($xml && @simplexml_load_string($xml)) {
				$p = json_decode(json_encode((array)simplexml_load_string($xml)),1);
				if ( isset($p['person']) ) {
					update_option( 'bg_hlnames_datafile', $url );
					if ( isset($p['about']) ) echo '*'.$p['about'];
					else echo '*'.__('XML-file without comment.', 'bg-highlight-names');
				}
				else {
					update_option( 'bg_hlnames_datafile', "" );
					update_option( 'bg_hlnames_datebase', "" );
					echo __($datafile.' is not correct database file.', 'bg-highlight-names');
				}
			}
			else {
				update_option( 'bg_hlnames_datafile', "" );
				update_option( 'bg_hlnames_datebase', "" );
				echo __($datafile.' - XML-file not exist.', 'bg-highlight-names');
			}
		} else update_option( 'bg_hlnames_datafile', "" );

		wp_die();
	}
	if (isset($_POST['parseallposts']) && $_POST['parseallposts']=='reset') {
		update_option( 'bg_hlnames_in_progress', '' );
		wp_die();
	}
	if ( !empty($_GET['parseallposts']) ) {
	
		$process = $_GET['parseallposts'];
		if (get_option('bg_hlnames_in_progress') && ($process != 'repiad')) {
			echo '~'.__('Processing has not yet completed. Please wait.', 'bg-highlight-names');
			wp_die();
		}
		
		if ( $process == 'go' ) {
			$param->cnt = wp_count_posts()->publish;
			if (file_exists($bg_hlnames_debug_file)) unlink ( $bg_hlnames_debug_file );
			
			$not_clean = $_GET['not_clean'];
			update_option( 'bg_hlnames_not_clean', $not_clean );
			$start_no = intval( $_GET['start_no'] );
			if ($start_no < 1) $start_no = 1;
			if ($start_no > $param->cnt) $start_no = $param->cnt;
			update_option( 'bg_hlnames_start_no', $start_no );

			$finish_no = intval( $_GET['finish_no'] );
			if ($finish_no > $param->cnt) $finish_no = $param->cnt;
			update_option( 'bg_hlnames_finish_no', $finish_no );
			if ($finish_no < $start_no) {
				error_log(date ("j-m-Y H:i")." Nothing more to process.\n", 3, $bg_hlnames_debug_file);
				printf ("* <p id='bg_hlnames_result_p' start_no='".$start_no."' finish_no='".$finish_no."'><b>".__('Nothing more to process.', 'bg-highlight-names')."</b></p>", $i);
				update_option( 'bg_hlnames_in_progress', '' );
				wp_die();
			}
			$param->start_time = microtime(true);
			$param->num_posts = $finish_no - $start_no +1;
			update_option( 'bg_hlnames_param', $param );
		} elseif ( $process == 'repiad' ) {
			error_log("\n".date ("j-m-Y H:i")." Repiad the process.\n\n", 3, $bg_hlnames_debug_file);
			$start_no = get_option('bg_hlnames_start_no');
			$finish_no = get_option('bg_hlnames_finish_no');
			$param = get_option('bg_hlnames_param');
		} else { 
			wp_die (); 
		}
// Начинаем парсинг статей		
		$mode = get_option('bg_hlnames_mode');
		update_option( 'bg_hlnames_in_progress', 'on' );
		$not_clean = get_option('bg_hlnames_not_clean');
		
		
		$start_time = microtime(true);
		$this_time = $start_time;
		error_log(date ("j-m-Y H:i"). " Start parse ".($finish_no-$start_no+1)." of ".$param->cnt." posts\n", 3, $bg_hlnames_debug_file);
		
		if (get_option('bg_hlnames_start_old') == $start_no) {				// Нельзя повторять парсинг статьи два раза подряд
			error_log($start_no.". Not allowed repeated parsing! Will try the next post.\n", 3, $bg_hlnames_debug_file);
			error_log(date ("j-m-Y H:i"). " Cann't parse the post: ".$start_no."\n", 3, dirname(__FILE__ )."/parsing_error.log");
			$start_no++;
			update_option( 'bg_hlnames_start_no', $start_no );
		} 
		update_option( 'bg_hlnames_start_old', $start_no);
		
		for ($k = $start_no-1; $k < $finish_no; $k++){
			update_option( 'bg_hlnames_start_no', ($k+1) );

			$args = array('post_type' => array( 'post', 'page'), 'post_status' => 'publish', 'numberposts' => 1, 'offset' => $k, 'orderby' => 'ID');
			$posts_array = get_posts($args);
			$post = $posts_array[0];
			error_log(($k+1).". ".get_permalink($post->ID)." (size=".number_format( (strlen($post->post_content)/1024), 1 )."kB)\n", 3, $bg_hlnames_debug_file);
				
			if (!$not_clean) $post->post_content = bg_hlnames_clear($post->post_content);
			if ($mode != 'clear') $post->post_content = bg_hlnames_proc($post->post_content);
			wp_update_post($post);
				
			$this_time = microtime(true);
			$time = number_format(($this_time - $start_time), 2);
			$memory = number_format( (memory_get_usage()/1024), 1 );
			error_log("Memory usage ".$memory."kB. Complited in ".$time." sec.\n", 3, $bg_hlnames_debug_file);
			$start_time = $this_time;
		}
		
		$start_no = $k+1;
		if ($start_no > $param->cnt) $start_no=1;
		$finish_no = $param->num_posts+$start_no-1;
		if ($finish_no > $param->cnt) $finish_no = $param->cnt;
		update_option( 'bg_hlnames_start_no', $start_no );
		update_option( 'bg_hlnames_finish_no', $finish_no );
		$time = number_format(($this_time - $param->start_time), 2);
		error_log(date ("j-m-Y H:i")." Updated ".$param->num_posts." pages and posts! Total time: ".$time." sec.\n", 3, $bg_hlnames_debug_file);
		printf ("* <p id='bg_hlnames_result_p' start_no='".$start_no."' finish_no='".$finish_no."'><b>"._n('Updated %1$d page or post!','Updated %1$d pages and posts!', $param->num_posts, 'bg-highlight-names')."</b></p>", $param->num_posts);
		update_option( 'bg_hlnames_in_progress', '' );
	}
	wp_die();
}
// Версия плагина
function bg_hlnames_version() {
	$plugin_data = get_plugin_data( __FILE__  );
	return $plugin_data['Version'];
}

/*****************************************************************************************
	Класс плагина
	
******************************************************************************************/
class BgHighlightNames
{
	public function proc ($txt, $maxtime) {

		global $bg_hlnames_debug_file;
		$space = "(?:\s|\x{00A0}|\x{00C2}|(?:&nbsp;))";
	
		$time0 = 0;
		$cycle_time = 1;
		$start_time = microtime(true);

		$url = get_option('bg_hlnames_datebase');			// Локальный URL файла
		$url = trim ($url, "\\\/");
		if (!$url) $url = dirname(__FILE__ ).'/data.xml';	// Файл по умолчанию
		$code = file_get_contents($url);		
		$p = $this->xml_array($code);						// Преобразовать xml в массив										
		$person = $p['person'];
		$cnt = count($person);
		
		for ($i=0; $i<$cnt; $i++) {
			$the_person = $person[$i];

			if ( isset($the_person['curacy'])) $curacy = $the_person['curacy'];
			else  $curacy="";
			if ( isset($the_person['name'])) $name = $the_person['name'];
			else  $name="";
			if ( isset($the_person['num'])) $num = $the_person['num'];
			else  $num="";
			if ( isset($the_person['middlename'])) $middlename = $the_person['middlename'];
			else  $middlename="";
			if ( isset($the_person['nick'])) $nick = $the_person['nick'];
			else  $nick="";
			if ( isset($the_person['surname'])) $surname = $the_person['surname'];
			else  $surname="";
			$is_monk = !$surname  || (substr($surname, 0, 1) == '('); 				// У монаха нет фамилии или фамилия в скобках

			$the_curacy = $curacy?("(".$curacy.$space.")?"):"";
			$the_num = $num?("(".$space.$num.")?"):"";
			$the_middlename = $middlename?("(".$space.$middlename.")?"):"";
			$initial_middlename = $middlename?("(".$space."?".mb_substr($middlename, 0, 1)."\\.)?"):"";
			if ($surname) {			// Если есть Фамилия
				$surname = preg_replace("/\(/ui", '\\(?',  $surname);		// Скобка необязательна, но допустима
				$surname = preg_replace("/\)/ui", '\\)?',  $surname);		//  - " -
			}
			$the_surname = $surname?("(".$space.$surname.")?"):"";

			//   Построение паттерна
			if ($is_monk) {		// Монах
				if ($nick) {		// Если есть Прозвище	
					// 1. Прозвище обязательно, а второе Прозвище или Фамилия необязательны.
					$template = "/\b".$this->template( $the_curacy.$name.$the_num.$space.$nick.$the_surname )."(\b|\)?)/iu";					// архиепископ Иоанн Шанхайский (Максимович)
					$txt = $this->add_link ($txt, $template, $the_person);
					// 2. Возможно Прозвище в комбинации с Саном
					if ($curacy) {
						// - после имени
						$template = "/\b".$this->template( $name.$the_num.$the_surname."\\,".$space.$curacy.$space.$nick )."(\b|\)?)/iu";		// Иоанн (Максимович), архиепископ Шанхайский
						$txt = $this->add_link ($txt, $template, $the_person);
						// - перед именем
						$template = "/\b".$this->template( $curacy.$space.$nick.$space.$name.$the_num.$the_surname )."(\b|\)?)/iu";				// архиепископ Шанхайский Иоанн (Максимович)
						$txt = $this->add_link ($txt, $template, $the_person);
					}
					// 3. Возможны Прозвища в обратной последовательности
					if ($surname) {
						$template = "/\b".$this->template( $the_curacy.$name.$the_num.$space.$surname."(".$space.$nick.")?" )."(\b|\)?)/iu";	// архиепископ Иоанн (Максимович) Шанхайский
						$txt = $this->add_link ($txt, $template, $the_person);
					}
				} elseif ($surname) {	// 4. Иначе если есть только Фамилия
					$template = "/\b".$this->template( $the_curacy.$name.$the_num.$space.$surname )."(\b|\)?)/iu";								// святитель Игнатий (Брянчанинов)
					$txt = $this->add_link ($txt, $template, $the_person);
				} elseif ($curacy) {	// 5. Если нет ни Прозвища ни Фамилии, то определяем по Сану
						// - после имени
						$template = "/\b".$this->template( $name.$the_num."\\,".$space.$curacy )."\b/iu";										// Варнава, апостол
						$txt = $this->add_link ($txt, $template, $the_person);
						// - перед именем
						$template = "/\b".$this->template( $curacy.$space.$name.$the_num )."\b/iu";												// апостол Варнава
						$txt = $this->add_link ($txt, $template, $the_person);
				} elseif ($num) {		// 6. Если нет ни Прозвища ни Фамилии, ни Сана - определяем по номеру
						$template = "/\b".$this->template( $name.$space.$num )."\b/iu";															// Феликс III
						$txt = $this->add_link ($txt, $template, $the_person);
				} else {				// 7. Если нет ни Прозвища ни Фамилии, ни Сана, ни номера - определяем только по имени 
										//    (Имя должно быть уникальными располагаться в конце базы данных!!!)
						$template = "/\b".$this->template( $name )."\b/iu";																		// Ерм
						$txt = $this->add_link ($txt, $template, $the_person);
				}
			} else {			// Мирянин
				// 1. Фамилия Имя Отчество (отчество не обязательно)
				$template = "/\b".$this->template( $surname.$space.$name.$the_middlename )."\b/iu";										// Лопухин Александр Павлович
				$txt = $this->add_link ($txt, $template, $the_person);
				// 2. Имя Отчество Фамилия (отчество не обязательно)
				$template = "/\b".$this->template( $name.$the_middlename.$space.$surname )."\b/iu";										// Александр Павлович Лопухин 
				$txt = $this->add_link ($txt, $template, $the_person);
				// 3. Фамилия И. О. (отчество не обязательно)
				$template = "/\b".$this->template( $surname.$space.mb_substr($name, 0, 1)."\\.".$initial_middlename )."/iu";			// Лопухин А. П.
				$txt = $this->add_link ($txt, $template, $the_person);
				// 4. И. О. Фамилия (отчество не обязательно)
				$template = "/\b".$this->template( mb_substr($name, 0, 1)."\\.".$initial_middlename.$space."?".$surname )."/iu";		// А. П. Лопухин
				$txt = $this->add_link ($txt, $template, $the_person);
			}

	// Ограничение времени работы функции
			$time = microtime(true) - $start_time;
			if ($time-$time0 > $cycle_time) $cycle_time = $time-$time0;
			$time0 = $time;
			if ($maxtime && $time > $maxtime-$cycle_time) {
				if (get_option('bg_hlnames_debug')) {
					$txt .= '<p class="bg_hlnames_debug">'.sprintf(__('Tested %1$d of %2$d names in %3$.1f seconds.', 'bg-highlight-names'), ($i+1), $cnt, $time).'</p>';
				}
				if ( !empty($_GET['parseallposts'])) error_log(sprintf('Tested %1$d of %2$d names in %3$.1f seconds. ', ($i+1), $cnt, $time), 3, $bg_hlnames_debug_file);
				return $txt;
			}
		}
		if (get_option('bg_hlnames_debug')) {
			$txt .= '<p class="bg_hlnames_debug">'.sprintf(__('Successfully tested all %1$d names in %2$.1f seconds.', 'bg-highlight-names'), $cnt, $time).'</p>';
		}
		if ( !empty($_GET['parseallposts'])) error_log(sprintf('Successfully tested all %1$d names in %2$.1f seconds. ', $cnt, $time), 3, $bg_hlnames_debug_file);
		return $txt;
	}
	/*******************************************************************************
	// Функция удаляет ранее установленную ссылку к имени персоны
	*******************************************************************************/  
	public function clear ($txt) {
		global $bg_hlnames_debug_file;
		// Ищем все вхождения ссылок <a ...</a>
		preg_match_all("/<a\\s.*?<\/a>/sui", $txt, $hdr, PREG_OFFSET_CAPTURE);
		$start_time = microtime(true);

		$cnt = count($hdr[0]);
		$j=0;

		for ($i = 0; $i < $cnt; $i++) {
			if (strstr ( $hdr[0][$i][0] ,'bg_hlnames' )) {
				$start = strpos ( $hdr[0][$i][0], '>', 1 )+1;
				$finish = strrpos ( $hdr[0][$i][0], '<', 1 );
				$newhdr = substr ( $hdr[0][$i][0], $start, $finish-$start );
				$txt = str_replace ( $hdr[0][$i][0], $newhdr, $txt );
				$j++;
			}
		}
		$time = microtime(true) - $start_time;
		if ( !empty($_GET['parseallposts'])) error_log(sprintf('Successfully removed %1$d of %2$d links in %3$.1f seconds. ', $j, $cnt, $time), 3, $bg_hlnames_debug_file);
		return $txt;
	}
	/*******************************************************************************
	// Функция добавляет ссылку к имени персоны
	*******************************************************************************/  
	private function add_link ($txt, $template, $the_person) {
		
		global $bg_hlnames_maxlinks, $bg_hlnames_distance;
		static $pers = "";
		static $num_links=0;
		static $last_position = 0;
		if ($pers != $the_person['link']) {
			$pers = $the_person['link'];
			$num_links = 0;
			$last_position = 0;
		}
		
		// Ищем все вхождения ссылок <a ...</a>
		preg_match_all("/<a\\s.*?<\/a>/sui", $txt, $hdr_a, PREG_OFFSET_CAPTURE);
		preg_match_all("/\[nonames.*?\[\/nonames\]/sui", $txt, $hdr_nonames, PREG_OFFSET_CAPTURE);
		preg_match_all("/<.*?>/sui", $txt, $hdr_tag, PREG_OFFSET_CAPTURE);
		
		preg_match_all($template, $txt, $matches, PREG_OFFSET_CAPTURE);
		$cnt = count($matches[0]);

		$target = get_option('bg_hlnames_target');
		if (get_option('bg_hlnames_classname')) $classname = 'bg_hlnames '.get_option('bg_hlnames_classname');
		else $classname = 'bg_hlnames';
		$text = "";
		$start = 0;
		$title = $the_person['discription'];
		for ($i = 0; ($i < $cnt) && (!$bg_hlnames_maxlinks || ($num_links < $bg_hlnames_maxlinks)); $i++) {
			
		// Обработка по каждому паттерну, если он не находится внутри тега <a ...</a>
			if ($this->check_tag($hdr_a, $matches[0][$i][1])
			&&  $this->check_tag($hdr_nonames, $matches[0][$i][1]) 
			&&  $this->check_tag($hdr_tag, $matches[0][$i][1]) ) {
				if (!$num_links || ($matches[0][$i][1]-$last_position > $bg_hlnames_distance)) {	// Обрабатываем если растояние между ссылками больше заданного
					$newmt = "<a class='".$classname."' href='".$the_person['link']."' target='".$target."' title='".$title."'>".$matches[0][$i][0]."</a>";
					$text = $text.substr($txt, $start, $matches[0][$i][1]-$start).str_replace($matches[0][$i][0], $newmt, $matches[0][$i][0]);
					$start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
					$last_position = $start;
					$num_links++;
				}
			}
		}
		$txt = $text.substr($txt, $start);
		
		return $txt;
	}
	/*******************************************************************************
	// Функция для преобразования XML в PHP Array
	*******************************************************************************/  
	private function xml_array($xml){
		$result = json_decode(json_encode((array)simplexml_load_string($xml)),1);
		return $result;
	}
	/*******************************************************************************
	// Функция для шаблонов в регулярное выражение
	*******************************************************************************/  
	private function template ($pattern) {
		$pattern  = preg_replace("/\\$/ui", '\w',  $pattern);		// $ - строго 1 любая буква
		$pattern  = preg_replace("/\%/ui", '\w?',  $pattern);		// % - 0 или 1 любая буква
		$pattern  = preg_replace("/\*/ui", '\w*',  $pattern);		// * - 0 или несколько любых букв
		return $pattern;
	}
	/******************************************************************************************
		Проверяем находится ли указанная позиция текста внутри тега  tag1 ...tag2,
		если "да" - возвращаем false, "нет" - true 
	*******************************************************************************************/
	function check_tag($hdr, $pos) {

		$chrd = count($hdr[0]);

		for ($k = 0; $k < $chrd; $k++) {
			$start = $hdr[0][$k][1];
			$finish = $start + strlen($hdr[0][$k][0])-1;
			if ($pos >= $start && $pos <= $finish) return false;
		}
		return true; 
	}
}
/******************************************************************************************
	Параметры плагина
	
*******************************************************************************************/
// Задаем начальные значения параметров
function bg_hlnames_add_options () {
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

	add_option('bg_hlnames_datafile', "");
}
// Определяем количество опубликованных постов
function bg_hlnames_count_posts () {
	$count_posts = wp_count_posts()->publish;
	return $count_posts;
}
