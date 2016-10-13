<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/24
 * Time: 20:56
 */

/**
 * 自动加载
 *
 * @param $className
 */
function __autoload($className)
{
    $classes  = explode('\\', $className);
    $argCount = count($classes);
    $files    = [];
    if ($argCount == 2) {
        if ($classes[1] == 'Db') {
            $files[] = CUMIN_PATH . 'Classes/Db/' . $classes[1] . EXT;
        } else {
            $files[] = CUMIN_PATH . 'Classes/' . $classes[1] . EXT;
        }
    } elseif ($argCount > 2) {
        $files[] = CUMIN_PATH . 'Classes/' . str_replace('\\', '/', substr($className, 6)) . EXT;
    } else {
        return;
    }

    foreach ($files as $file) {
        if (is_readable($file)) {
            require_once $file;
        }
    }
}

function C($name = null)
{
    static $_config = [];
    if (empty($name)) {
        return $_config;
    }
    
}