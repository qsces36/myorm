<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/30
 * Time: 21:02
 */

include_once 'Libs/Common/functions.php';

define('EXT', '.class.php'); //类文件后缀
define('CUMIN_PATH', dirname(__FILE__) . '/Libs/');
define('COMMON_PATH', CUMIN_PATH . '/Common/');
define('CONF_PATH', CUMIN_PATH . '/Conf/');
define('CORE_PATH', CUMIN_PATH . '/Core/');

include_once CORE_PATH . 'Db.class.php';
include_once CORE_PATH . 'AppException.class.php';