<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/23
 * Time: 17:03
 */
namespace Cumin;
include_once 'Cumin/Cumin.php';

$config = [
    'driver'   => 'mysql',
    'dbname'   => 'test',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8',
];

try {
    $result = DB::M($config)
        ->debug()
        ->table('dd')
        ->select();
} catch (AppException $e) {
    echo $e->getMessage();
}
var_dump($result);