<?php

    /**
     * Config предназначен для простого чтения ini файлов
     * @author Oleg Shevelev|mantyr@gmail.com
     */

    class Config {
        private static $configs = array();
        private static $path = 'configs';

        /**
         * Для переопределения каталога с конфигурационными файлами
         *
         * @param string адрес каталога с конфигурационными файлами (данный адрес должен быть относительным от корневого каталога сайта)
         */
        public static function path($address) {
            self::$path = str_replace('.','/',$address);
        }

        /**
         * Для получения значения из конфигурационного файла
         *
         * @param string название переменной
         * @param string название конфигцрационного файла (без указания ini расширения)
         * @return mixed в качестве результата может быть массив, значение или NULL (который свидетельствует об отсутствии файла или значения в нём)
         */
        public static function get($param, $file = 'properties') {
            if (!isset(self::$configs[$file])) self::load($file);
            if ($param === false) return self::$configs[$file];
            if (isset(self::$configs[$file][$param])) return self::$configs[$file][$param];
            return NULL;
        }

        /**
         * Внутренняя функция для загрузки конфигурационного файла в память
         *
         * @paran string название конфигцрационного файла (без указания ini расширения)
         */
        private static function load($file) {
            $file_address = ROOT_DIR.self::$path.'/'.str_replace('.','/',$file).'.ini';
            if (is_file($file_address)) self::$configs[$file] = parse_ini_file($file_address, true, INI_SCANNER_RAW);
        }
    }