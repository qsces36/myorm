<?php
/**
 * Created by PhpStorm.
 * User: Cumin
 * Date: 2016/7/30
 * Time: 17:06
 */
namespace Cumin;

class AppException extends \Exception
{
    public function getError()
    {
        return $this->getMessage();
    }

    public function getErorInfo()
    {
        return $this->getCode() . ':' . $this->getMessage();
    }
}