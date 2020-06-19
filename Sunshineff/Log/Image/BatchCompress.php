<?php
namespace Sunshineff\Log\Image;

use Sunshineff\Log\Image\Compress;

/** 批量压缩图片
 * Class BatchCompress
 * @package Sunshineff\Log\Image
 */
class BatchCompress 
{
    protected $compreModel;
    
    public function __construct()
    {
        $this->compreModel = new Compress();
    }

    /** 执行
     * @param $path string  压缩图片所在目录
     * @param $newPath string 目标目录
     */
    public function run($path,$newPath){
        if (!is_dir($path)){
            throw \Exception('错误的文件路径');
        }
        if ($newPath && !is_dir($newPath)){
            @mkdir($newPath,0777,true);
        }
        $fileList = scandir($path);
        foreach ($fileList as $fileName){
            if (strlen($fileName) < 3) continue;
            $filePath = $path . '/' .$fileName;
            if (is_dir($filePath)){
                readPath($path);
            }
            $newFile = $newPath. '/' .$fileName;
            /**** @var $imageModel Compress *** */
            $imageModel->resize($filePath)->saveImage($filePath);
        }
    }
    
    public function getCompressModel(){
        return $this->compreModel;
    }
}