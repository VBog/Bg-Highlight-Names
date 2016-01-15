<?php
/*
Plugin Name: Bg Highlight Names
Plugin URI: https://bogaiskov.ru/highlight-names/
Description: Highlight Russian names in text of posts and pages.
Version: 0.4.1
Author: VBog
Author URI: http://bogaiskov.ru
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
define('BG_HLNAMES_VERSION', '0.4.1');

// Загрузка интернационализации
add_action( 'plugins_loaded', 'bg_highlight_load_textdomain' );
function bg_highlight_load_textdomain() {
  load_plugin_textdomain( 'bg-highlight-names', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
}

// Подключаем дополнительные модули
include_once('includes/settings.php' );


if ( defined('ABSPATH') && defined('WPINC') ) {
	$plugin_mode = get_option('bg_hlnames_mode');
// Регистрируем крючок для обработки контента при его загрузке
	if ($plugin_mode == "online") add_filter( 'the_content', 'bg_hlnames_proc' );
// Регистрируем крючок для обработки контента при его сохранении в БД
	elseif ($plugin_mode == "offline") add_action('wp_insert_post_data', 'bg_hlnames_post_save', 20, 2 );
}

$bg_hlnames_maxlinks = (int) get_option('bg_hlnames_maxlinks');

/*****************************************************************************************
	Функции запуска плагина
	
******************************************************************************************/
 
// Функция обработки списка имён
function bg_hlnames_proc($content) {
	$maxtime = get_option('bg_hlnames_maxtime');
	if (!set_time_limit ($maxtime)) {
		$systemtime = ini_get('max_execution_time'); 
		if (!$systemtime) $systemtime = 30;
		if (get_option('bg_hlnames_debug')) {
			$content .= '<p class="bg_hlnames_debug">'.sprintf(__( 'The maximum execution time (%1$s sec.) could not be set. System limits the maximum execution time of %2$s sec.', 'bg-highlight-names'), $maxtime, $systemtime).'</p>';
		}
		$maxtime = $systemtime - 2;
	}
	$bg_hlnames = new BgHighlightNames();
	$content = $bg_hlnames->proc($content, $maxtime);
	return $content;
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

function bg_hlnames_post_save( $data, $postarr ){
	if( isset($_POST['post_type']) && ($_POST['post_type'] == 'post' || $_POST['post_type'] == 'page') ) { 	// убедимся что мы редактируем нужный тип поста
		if( get_current_screen()->id != 'post' && get_current_screen()->id != 'post') return $data; 		// убедимся что мы на нужной странице админки
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return $data; 					// пропустим если это автосохранение
		if ( ! current_user_can('edit_post', $postarr['ID'] ) ) return $data; 				// убедимся что пользователь может редактировать запись

		// Все ОК! обрабатываем
		$data['post_content'] = bg_hlnames_proc($data['post_content']);
	}
	return $data;
}


/*****************************************************************************************
	Класс плагина
	
******************************************************************************************/
class BgHighlightNames
{
	public function proc ($txt, $maxtime) {

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

			$curacy = $the_person['curacy'];
			$name = $the_person['name'];
			$num = $the_person['num'];
			$middlename = $the_person['middlename'];
			$nick = $the_person['nick'];
			$surname = $the_person['surname'];
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
			if ($time-$time0 > $cicle_time) $cicle_time = $time-$time0;
			$time0 = $time;
			if ($maxtime && $time > $maxtime-$cicle_time) {
				if (get_option('bg_hlnames_debug')) {
					$txt .= '<p class="bg_hlnames_debug">'.sprintf(__('Tested %1$d of %2$d names in %3$.1f seconds.', 'bg-highlight-names'), ($i+1), $cnt, $time).'</p>';
				}
				return $txt;
			}
		}
		if (get_option('bg_hlnames_debug')) {
			$txt .= '<p class="bg_hlnames_debug">'.sprintf(__('Successfully tested all %1$d names in %2$.1f seconds.', 'bg-highlight-names'), $cnt, $time).'</p>';
		}
		return $txt;
	}
		
	
	/*******************************************************************************
	// Функция добавляет ссылку к имени персоны
	*******************************************************************************/  
	private function add_link ($txt, $template, $the_person) {
		
		global $bg_hlnames_maxlinks;
		static $pers = "";
		static $num_links=0;
		if ($pers != $the_person['link']) {
			$pers = $the_person['link'];
			$num_links = 0;
		}
		
		// Ищем все вхождения ссылок <a ...</a>
		preg_match_all("/<a\\s.*?<\/a>/sui", $txt, $hdr_a, PREG_OFFSET_CAPTURE);
		
		preg_match_all($template, $txt, $matches, PREG_OFFSET_CAPTURE);
		$cnt = count($matches[0]);

		$target = get_option('bg_hlnames_target');
		$text = "";
		$start = 0;
		$title = $the_person['discription']."\n".$the_person['lifedates'];
		for ($i = 0; ($i < $cnt) && (!$bg_hlnames_maxlinks || ($num_links < $bg_hlnames_maxlinks)); $i++) {
		// Обработка по каждому паттерну, если он не находится внутри тега <a ...</a>
			if ($this->check_tag($hdr_a, $matches[0][$i][1])) {		
				$newmt = "<a class='bg_hlnames' href='".$the_person['link']."' target='".$target."' title='".$title."'>".$matches[0][$i][0]."</a>";
				$text = $text.substr($txt, $start, $matches[0][$i][1]-$start).str_replace($matches[0][$i][0], $newmt, $matches[0][$i][0]);
				$start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
				$num_links++;
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
