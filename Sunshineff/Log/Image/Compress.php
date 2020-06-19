<?php
namespace Sunshineff\Log\Image;

class Compress
{
    protected $src;

    protected $image;

    protected $imageinfo;

    protected $isLoad = false;

    const MAX_WIDTH = 750;

    const ALLOW_TYPE = ['.jpg', '.jpeg', '.png'];
    
    /**
     * 加载图片
     * @param $path string 文件路径
     */
    public function load($path)
    {
        if($this->isLoad){
            return;
        }
        $this->src = $path;
        $imageInfo = getimagesize($this->src);
        list($width, $height, $type, $attr) = $imageInfo;
        $this->imageinfo = array(
            'width'=>$width,
            'height'=>$height,
            'type'=>image_type_to_extension($type,false),
            'attr'=>$attr,
            'size' => $imageInfo['bits']
        );
        $fun = "imagecreatefrom".$this->imageinfo['type'];
        $this->image = $fun($this->src);
        $this->isLoad = true;
    }

    /**
     * 有画质缩略图 资源 
     * @param $path string 文件路径 
     * @param $newPath string 保存路径，默认覆盖
     * @param $level float 1 - 0.01  推荐 0.75+
     */
    public function compress($path,$newPath = '',$level = 1)
    {
        $this->load($path);
        $type = $this->imageinfo['type'];
        empty($newPath) ? $newPath = $path : '';
        $this->_needResize($path); // 先大小压缩，再品质压缩
        if($type == 'jpeg'){
            imagejpeg($this->image, $newPath, $level * 10 );
        }else if($type == 'png'){
            if($this->_ifTransparent($this->image)) {
                imageAlphaBlending($this->image, true);
                imageSaveAlpha($this->image, true);
                imagepng($this->image, $newPath, $level);
            } else
                imagepng($this->image, $newPath, $level);
        }
        $this->_imageDestory();  //销毁图片
        return $newPath;
    }

    /** 如果图片大小超过 最大限制，进行缩小处理
     * @param $path
     */
    protected function _needResize($path){
        if ($this->imageinfo['width'] > self::MAX_WIDTH){
            $width = self::MAX_WIDTH;
            $height = round($width/$this->imageinfo['width'] * $this->imageinfo['height']);
            $this->resize($path,$width,$height);
        }
    }

    /**
     * 判断图片是否为 透明 图片
     *
     * @param $image
     * @return bool
     * @author 19/1/16 CLZ.
     */
    private function _ifTransparent($image) {
        for($x = 0; $x < imagesx($image); $x++)
            for($y = 0; $y < imagesy($image); $y++)
                if((imagecolorat($image, $x, $y) & 0x7F000000) >> 24) return true;
        return false;
    }

    /**
     * 无损画质缩略图 资源
     * @param $new_height
     * @param $new_width
     */
    public function resize($path,$new_width = null,$new_height = null)
    {
        $this->load($path);
        // 像素判断，缩小图片分辨率
        if (empty($new_width) && $this->imageinfo['width'] > self::MAX_WIDTH){
            $new_width = self::MAX_WIDTH;
            $new_height = round($new_width/$this->imageinfo['width'] * $this->imageinfo['height']);
        }
        empty($new_width) ? $new_width = $this->imageinfo['width'] : '';
        empty($new_height) ? $new_height = $this->imageinfo['height'] : '';
        $image_thump = imagecreatetruecolor($new_width,$new_height);
        //将原图复制带图片载体上面，并且按照一定比例压缩,极大的保持了清晰度
        imagecopyresampled($image_thump,$this->image,0,0,0,0,$new_width,$new_height,$this->imageinfo['width'],$this->imageinfo['height']);
        $this->_imageDestory();
        $this->image = $image_thump;
        return $this;
    }
    /**
     * 输出图片
     */
    public function showImage()
    {
        header('Content-Type: image/'.$this->imageinfo['type']);
        $funcs = "image".$this->imageinfo['type'];
        $funcs($this->image);
    }
    /**
     * 保存图片到硬盘
     * @param  string $dstImgName  推荐相对路径
     */
    public function saveImage($pathName)
    {
        if(empty($pathName) && !$this->src) return false;
        $allowImgs = ['.jpg', '.jpeg', '.png', '.bmp', '.wbmp','.gif'];   //如果目标图片名有后缀就用目标图片扩展名 后缀，如果没有，则用源图的扩展名
        $dstExt =  strrchr($pathName ,".");
        $sourseExt = strrchr($this->src ,".");
        if(!empty($dstExt)) $dstExt =strtolower($dstExt);
        if(!empty($sourseExt)) $sourseExt =strtolower($sourseExt);
        //有指定目标名扩展名
        if(!empty($dstExt) && in_array($dstExt,$allowImgs)){
            $dstName = $pathName;
        }elseif(!empty($sourseExt) && in_array($sourseExt,$allowImgs)){
            $dstName = $pathName.$sourseExt;
        }else{
            $dstName = $pathName.$this->imageinfo['type'];
        }
        $funcs = "image".$this->imageinfo['type'];
        $funcs($this->image,$dstName);
        return $pathName;
    }

    /**
     * 销毁图片
     */
    protected function _imageDestory(){
        $this->isLoad = false;
        if (empty($this->image)) return;
        imagedestroy($this->image);
        $this->image = null;
    }

    public function __destruct()
    {
        $this->_imageDestory();
    }

}