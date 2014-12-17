<?php

    /**
     * Основные функции, использующиеся во всём проекте повсеместно, не имеют зависимостей
     * @author Oleg Shevelev|mantyr@gmail.com
     */

    define('ROOT_DIR', dirname(__FILE__).'/../');
    mb_internal_encoding("UTF-8");

    /**
     * Служит для импорта php файлов
     *
     * @param string в качестве адреса указывается название файла и каталога, например "systems.config" или "systems.other.config", каталоги перечисляются через запятую, вместо точки можно использовать слеш, например "systens/config"
     * @return mixed название файла или false
     */
    function import($param = false, $is_return_data = false){
        if (!$param) return false;

        $class_file = ROOT_DIR.str_replace('.', '/', $param).'.php';
        if (is_file($class_file) && include_once($class_file)) {
            if ($is_return_data) {
                return $data;
            }
            return basename(str_replace('.', '/', $param));
        }
        return false;
    }

    function import_address($param = false){
        if (!$param) return false;
        return ROOT_DIR.str_replace('.', '/', $param);
    }



    /**
     * Служит для отображения служебных сообщений в браузере в окуратной рамке
     *
     * Tекст выводится в echo, в качестве зашиты от вредоносного кода используется htmlspecialchars
     * @param string любой текст
     */
    function eEcho($param,$is_border = true){
        echo '<div style="'.(($is_border)?'border:1px solid #222222; ':'color:#CC0000;').'padding:5px; margin-bottom:10px;">'.htmlspecialchars($param).'</div>';
    }
    function cEcho($param){
        echo "$param\n";
    }

    function gGet($param){
        if (isset($_GET[$param])) return $_GET[$param];
        if (isset($_POST[$param])) return $_POST[$param];
        return null;
    }

    function globals_params($prefix = ''){
        foreach ($_GET as $var => $value) {
            if (!$GLOBALS[$prefix.'_'.$var]) $return[$prefix.'_'.$var] = $value;
        }
        foreach ($_POST as $var => $value) {
            if (!$GLOBALS[$prefix.'_'.$var]) $return[$prefix.'_'.$var] = $value;
        }
        return $return;
    }

    function parse_filter($str) {
        return trim((string)$str);
    }

    function parse_filter_url($str) {
        $str = trim($str, '/');
        return substr($str, strrpos($str, '/')+1);
    }

    function progress($text) {
        $text = "\r$text#default#                  \r";
        $text = str_replace(
            array('#default#', '#green#'),
            array("\033[0;37m", "\033[0;32m"),
            $text
        );
        echo $text;
    }
    // сделать остановку курсора после прогресс бара, а не на первой строке...
    function progress_multi($arr = array()) {
        $arr = (array)$arr;
        $count_lines = count($arr);
        $text = '';
        foreach ($arr as $name => $item) {
            $params = '';
            if (is_array($item)) {
                foreach ($item as $a_name => $a_value) {
                    $params .= "${a_value} #green#${a_name}#default# ";
                }
            } else {
                $params = $item;
            }
            $text .= "#green#$name:#default# $params                  \r\n";
        }
        $text = str_replace(
            array('#default#', '#green#'),
            array("\033[0;37m", "\033[0;32m"),
            $text
        );
        echo $text;
        echo "\033[{$count_lines}A\r";
    }

    function echo_is_progress_bar($text) {
        if (site::is_progress_bar()) echo $text;
    }

    function set_console_title($title = false) {
        echo "\033]2;{$title}\007";
    }





