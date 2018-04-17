<?php
namespace framework;
class Image
{
    //新生成的水印图片保存的路径
    protected $path;
    //是否启用随机的名字
    protected $isRandName;
    //图片的类型
    protected $type;

    //通过构造方法初始化成员属性
    public function __construct($path = './upload/',$isRandName = true,$type = 'png')
    {
        $this->path = $path;
        $this->isRandName = $isRandName;
        $this->type = $type;
    }
    //封装实现水印的方法
    public function water($image,$water,$position,$tmd = 100,$prefix = "water_")
    {
        //判断图片是否存在
        if((!file_exists($image))||(!file_exists($water))) {
            exit('图像资源不存在');
        }
        //判断水印图片的大小是否合适
        $imageInfo = self::getImageInfo($image);
        $waterInfo = self::getImageInfo($water);

        if (!$this->checkImage($imageInfo,$waterInfo)) {
            exit('水印图片太大了');
        }
        //打开图片
        $imageRes = self::openAnyImage($image);
        $waterRes = self::openAnyImage($water);
        //计算位置
        $pos = $this->getPosition($position,$imageInfo,$waterInfo);
        //将水印图片贴上去
        imagecopymerge($imageRes,$waterRes,$pos['x'],$pos['y'],0,0,$waterInfo['width'],$waterInfo['height'],$tmd);
        //得到新的文件名
        $newName = $this->createNewName($image,$prefix);
        //得到新的路径
        $newPath = rtrim($this->path,'/').'/'.$newName;
        //保存文件
        $this->saveImage($imageRes,$newPath);
        //销毁文件
        imagedestroy($imageRes);
        imagedestroy($waterRes);
        return $newPath;

    }
    //得到图片相关信息的函数
    static public function getImageInfo($imagePath)
    {
        $info = getimagesize($imagePath);
        $data['width'] = $info[0];
        $data['height'] = $info[1];
        $data['mime'] = $info['mime'];
        return $data;
    }
    //检测图片大小的函数
    protected function checkImage($imageInfo,$waterInfo)
    {
        if (($waterInfo['width'] > $imageInfo['width']) || ($waterInfo['height'] > $imageInfo['height'])) {
            return false;
        }
        return true;
    }
    //万能打开图片的函数
    static public function openAnyImage($imagePath)
    {
        $mime = self::getImageInfo($imagePath)['mime'];
        switch($mime) {
            case 'image/png':
               $image = imagecreatefrompng($imagePath);
                break;
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
                break;
            case 'image/wbmp':
                $image = imagecreatefromwbmp($imagePath);
                break;
        }
        return $image;
    }
    //得到水印图片放置的位置坐标
    protected function getPosition($position,$imageInfo,$waterInfo)
    {
        switch($position) {
            case 1:
                $pos['x'] = 0;
                $pos['y'] = 0;
                break;
            case 2:
                $pos['x'] = ($imageInfo['width'] - $waterInfo['width']) / 2;
                $pos['y'] = 0;
                break;
            case 3:
                $pos['x'] = $imageInfo['width'] - $waterInfo['width'];
                $pos['y'] = 0;
                break;
            case 4:
                $pos['x'] = 0;
                $pos['y'] = ($imageInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 5:
                $pos['x'] = ($imageInfo['width'] - $waterInfo['width']) / 2;
                $pos['y'] = ($imageInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 6:
                $pos['x'] = $imageInfo['width'] - $waterInfo['width'];
                $pos['y'] = ($imageInfo['height'] - $waterInfo['height']) / 2;
                break;
            case 7:
                $pos['x'] = 0;
                $pos['y'] = $imageInfo['height'] - $waterInfo['height'];
                break;
            case 8:
                $pos['x'] = ($imageInfo['width'] - $waterInfo['width']) / 2;
                $pos['y'] = $imageInfo['height'] - $waterInfo['height'];
                break;
            case 9:
                $pos['x'] = $imageInfo['width'] - $waterInfo['width'];
                $pos['y'] = $imageInfo['height'] - $waterInfo['height'];
                break;
            case 0:
                $pos['x'] = mt_rand(0,$imageInfo['width'] - $waterInfo['width']);
                $pos['y'] = mt_rand(0,$imageInfo['height'] - $waterInfo['height']);
                break;
        }
        return $pos;
    }
    //得到新文件名字的方法
    protected function createNewName($image,$prefix)
    {
        if ($this->isRandName) {
            $name = $prefix.uniqid().'.'.$this->type;
        } else {
            $name = $prefix.pathinfo($image)['filename'].$this->type;
        }
        return $name;
    }
    //保存水印图片的函数
    protected function saveImage($imageRes,$newPath)
    {
        //得到保存图片的函数名字
        $func = 'image'.$this->type;
        $func($imageRes,$newPath);
    }
}