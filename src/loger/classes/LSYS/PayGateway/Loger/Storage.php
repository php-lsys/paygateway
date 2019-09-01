<?php
/**
 * lsys pay
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\PayGateway\Loger;
use LSYS\PayGateway\Result;
interface Storage{
    public function getFilter();
    public function getFilterLevel($filter_mark);
    public function ignoreFailSign();
    public function loger($token,Result $result);
}