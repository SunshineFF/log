<?php
namespace Sunshineff\Log\Test;

use Sunshineff\Log\LogModel;
use Sunshineff\Log\Image\Compress;
use Sunshineff\Log\Image\BatchCompress;

/**
 *  测试代码
 */
$logModel = new LogModel();
$logModel->error('This is test',['a' =>1,'b' => 2]);

/**
 * 单个图片压缩（无损画质）
 */

$path = './a.jpg';
$compressModel = new Compress();
$compressModel->resize($path)->saveImage($path);

/**
 * 有损画质
 */

$compressModel->compress($path,$path,0.75);

/**
 * 批量压缩
 */
$path = './';
$newPath = './a/'; //可选，默认覆盖
$batchCompressModel = new BatchCompress();
$batchCompressModel->run($path,$newPath);
