<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/23
 * Time: 17:03
 */
namespace Cumin;
include_once 'Cumin/Cumin.php';
$t1 = microtime(true);

$config = [
    'driver'   => 'mysql',
    'dbname'   => 'test',
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'username' => 'root',
    'password' => 'leney',
    'charset'  => 'utf8',
];
try {
    $result = DB::M($config)
        ->field('name')
        ->table('class')
        ->count();
} catch (AppException $e) {
    echo $e->getMessage();
}

$t2 = microtime(true);

echo '<pre>';
echo $t2 - $t1 . PHP_EOL;

var_dump($result);