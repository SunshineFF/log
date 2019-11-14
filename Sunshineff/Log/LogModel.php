<?php

namespace LiuShuKai\Log;

class LogModel extends \Psr\Log\NullLogger{

    /**
     *  默认日志路径
     */
    const DIR = 'data/log/';

    /**
     *  默认最大日志大小 2M
     */
    const MAX_SIZE = 1024 * 1024 * 2;

    /**
     *  默认日志路径
     */
    const DIR_TYPE = 'Y_M/d';

    /** 重构 log 方法
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return bool|int|void
     * @throws Exception
     */
    public function log($level, $message, array $context = array())
    {
        $fileName = $this->getFileName();
        return file_put_contents($fileName,$this->_initMessage($level,$message,$context),FILE_APPEND);
    }

    /** 组合 字符串
     * @param $level
     * @param $message
     * @param array $content
     * @return string
     */
    protected function _initMessage($level,$message,$content = []){
        $str = date('Y-m-d H:i:s');
        $str .= '   Level: '.$level.PHP_EOL;
        $str .= 'Message: ';
        $str .= $message.PHP_EOL;
        if (!empty($content)){
            $str .= 'Content: ';
            if (is_array($content)){
                $str .= serialize($content);
            }elseif (is_string($content)){
                $str.= $content;
            }elseif(is_object($content)){
                $str.= serialize($content);
            }
            $str .= PHP_EOL;
        }
        return $str;
    }

    /** 获取当前文件名
     * @return string
     * @throws Exception
     */
    public function getFileName(){
        $dir = $this->getTodayDir();
        $files = scandir($dir);
        unset($files[0],$files[1]);
        if (empty($files)){
            return $dir.DIRECTORY_SEPARATOR.'log.log';
        }
        $i = 1;
        $realFileName = '';
        foreach ($files as $fileName){
            $fileSize = filesize($dir.DIRECTORY_SEPARATOR.$fileName);
            if ($fileSize < self::MAX_SIZE){
                $realFileName = $dir.DIRECTORY_SEPARATOR.$fileName;
                break;
            }
            if ($fileSize > self::MAX_SIZE && $i == count($files)){
                $realFileName = $dir.DIRECTORY_SEPARATOR.'log-'.$i.'.log';
                break;
            }
            $i ++;
        }
        return $realFileName;
    }


    /** 获取当天的文件路径
     * @return string
     * @throws Exception
     */
    public function getTodayDir(){
        $time = time();
        $dir = self::DIR;

        $dir .= date('Y_m',$time);
        $dir .= '/'.date('d',$time);
        if (!$this->mkdirs($dir)){
            throw new Exception("创建文件 $dir 失败(请检查相关权限)");
        };
        return $dir;
    }

    /** 创建文件夹
     * @param $dir
     * @param int $mode
     * @return bool
     */
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode,true)) return TRUE;
        return false;
    }
}

$aa = ['aa' => 1 ,'bb' => 2];

try{
    $logModel = new LogModel();
    $logModel->error('只能转数组',$aa);
}catch (\Exception $exception){
    var_dump($exception->getMessage());
}