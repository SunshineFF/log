<?php
namespace Sunshineff\Log\Test;

use Sunshineff\Log\LogModel;

/**
 *  测试代码
 */
$logModel = new LogModel();
$logModel->error('This is test',['a' =>1,'b' => 2]);