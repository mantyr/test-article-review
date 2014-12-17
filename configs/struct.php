<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
    include_once('../systems/core.php');

    import('systems.db');
    import('systems.config');

    import('systems.structurs');

    list(, $_command, $_db, $_table) = $argv;

    $db_array = ['default'];

    if (!$_command) die("php struct.php start|diff|info db table\r\n");

    if (!in_array($_db, $db_array)) die("не верная база данных\r\n");

    $struct = new structurs($_db, 'structurs');

    if ($_command == 'start') {
        $struct->start($_table);
    } elseif ($_command == 'diff') {
        // в разработке
        $struct->diff($_table);
    } elseif ($_command == 'info') {
        // в разработке
        $struct->info($_table);
    }