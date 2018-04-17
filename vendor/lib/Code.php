<?php
namespace framework;
class Code
{
    //验证码的个数
    protected $num;
    //验证码的类型
    protected $codeType;
    //验证码的宽度
    protected $width;
    //验证码的高度
    protected $height;
    //图片的类型
    protected $imageType;
    //保存生成的验证码
    protected $code;
    //图形资源
    protected $image;


    //初始化成员属性
    public function __construct($num = 4,$codeType = 2,$width = 100,$height = 46,$imageType = 'png')
    {
        $this->num = $num;
        $this->codeType = $codeType;
        $this->width = $width;
        $this->height = $height;
        $this->imageType = $imageType;

        //调用生成验证码的函数
        $this->code = $this->getCode();
    }
    //从外边查看生成的验证码
    public function __get($data)
    {
        if ($data == 'code') {
            return $this->code;
        }
    }
    //生成验证码的函数
    protected function getCode()
    {
        switch ($this->codeType) {
            //纯数字的验证码
            case 0:
                $code = $this->getNumberCode();
                break;
            //纯字母的验证码
            case 1:
                $code = $this->getCharCode();
                break;
            //字母和数字混合的验证码
            case 2:
                $code = $this->getMixCode();
                break;
            default:
                exit('不支持此种类型');
        }
        return $code;
    }
    //生成纯数字的验证码
    protected function getNumberCode()
    {
        $str = '0123456789';
       return  substr(str_shuffle($str),0,$this->num);
    }
    //生成纯字母的验证码
    protected function getCharCode()
    {
        $str = range('a','z');
        $str1 = range('A','Z');
        $arr = array_merge($str,$str1);
        return  substr( str_shuffle(join('',$arr)),0,$this->num);
    }
    //生成数字和字母混合的验证码
    protected function getMixCode()
    {
        $str = join('',range(0,9));
        $str .= join('',range('a','z'));
        $str .= join('',range('A','Z'));
        return substr(str_shuffle($str),0,$this->num);
    }
    //输出验证码的函数
    public function outImage()
    {
        //生成画布
        $this->image = $this->createImage();
        //给画布填充颜色
        $this->fillBackground();
        //将验证码画到画布上面去
        $this->drawChar();
        //添加干扰元素
        $this->drawDisturb();
        //输出显示验证码
        $this->show();
    }

    //生成画布的函数
    protected function createImage()
    {
        return imagecreatetruecolor($this->width,$this->height);
    }
    //给画布填充背景色，  画布是浅颜色，验证码是深颜色
    protected function fillBackground()
    {
        imagefill($this->image,0,0,$this->lightColor());
    }
    //浅颜色的函数
    protected function lightColor()
    {
        return imagecolorallocate($this->image,mt_rand(130,255),mt_rand(130,255),mt_rand(130,255));
    }
    //深颜色的函数
    protected function darkColor()
    {
        return imagecolorallocate($this->image,mt_rand(0,130),mt_rand(0,130),mt_rand(0,130));
    }

    //将验证码画到画布上面去
    protected function drawChar()
    {
        for ($i = 0; $i < $this->num;$i++) {
           //得到每次向画布上面画的字符串
            $c = $this->code[$i];
            $width  = ceil($this->width / $this->num);
            $x = mt_rand($i * $width + 10,($i + 1)*$width - 10);
            $y = mt_rand(0,$this->height - 15);
            imagechar($this->image,5,$x,$y,$c,$this->darkColor());
        }
    }
    //给验证码添加干扰元素
    protected function drawDisturb()
    {
        //添加干扰点
        for ($i = 0; $i < 100; $i++) {
            $x = mt_rand(0,$this->width);
            $y = mt_rand(0,$this->height);
            imagesetpixel($this->image,$x,$y,$this->darkColor());
        }
        //添加干扰线
        for($i = 0; $i < 3;$i++)
        {
            imagearc($this->image,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(30,180),mt_rand(240,360),$this->lightColor());
        }
    }

    //将验证码显示出来
    protected function show()
    {
        header('content-type:image/'.$this->imageType);
        //拼接函数名
        $func = 'image'.$this->imageType;
        $func($this->image);
    }
    //在析构方法中将图像资源销毁
    public function __destruct()
    {
        imagedestroy($this->image);
    }


}