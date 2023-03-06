<?php

namespace user\model;

date_default_timezone_set( 'Asia/Tokyo' );

require_once dirname(__FILE__, 3) . '/vendor/autoload.php';

class Bootstrap
{
    const DB_HOST = 'localhost';
    const DB_NAME = 'care_flow_db';
    const DB_USER = 'care_flow_user';
    const DB_PASS = 'care_flow_pass';
    const DB_TYPE = 'mysql';

    const APP_DIR = '/Applications/MAMP/htdocs/CF/';

    const TEMPLATE_DIR = self::APP_DIR;

    const CACHE_DIR = false;

    const APP_URL = 'http://localhost:8888/CF/';

    const ENTRY_URL = self::APP_URL . 'user/';

    public static function loadClass($class)
    {
        $path = str_replace( '\\', '/', self::APP_DIR . $class . '.class.php' );
        require_once $path;
    }
}


//これを実行しないとオートローダーとして動かない
spl_autoload_register( [ 
    'user\model\Bootstrap',
    'loadClass'
] );

