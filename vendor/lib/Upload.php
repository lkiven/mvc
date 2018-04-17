<?php
namespace framework;
class Upload
{
    //文件保存的路径
    protected $path = './public/upload/';
    //允许的mime类型
    protected $allowMime = ['image/png','image/jpeg','image/gif','image/wbmp'];
    //允许的后缀类型
    protected $allowSuffix = ['png','jpg','jpeg','jpe','gif','wbmp'];
    //允许文件上传的大小
    protected $maxSize = 20000000;
    //是否启用随机的名字
    protected $isRandName = true;
    //是否启用日期目录
    protected $isDatePath = true;
    //加上文件的前缀
    protected $prefix = "up_";
    //自己定义错误代号和错误信息，方便出错时，查看
    protected $errorNumber;
    protected $errorInfo;

    //需要将上传过来的文件相关信息保存到指定的成员属性中，因为后续操作需要用到这些参数
    //文件名
    protected $oldName;
    //文件的后缀
    protected  $suffix;
    //文件的mime类型
    protected $mime;
    //文件的大小
    protected $size;
    //文件的临时路径
    protected $tmpName;

    //文件的新名字和移动到的新的路径
    public $newName;
    protected $newPath;

    public function __construct($arr = [])
    {
        foreach ($arr as $key=>$value) {
            $this->setOption($key,$value);
        }
    }

    protected function setOption($key,$value)
    {
        //得到当前类所有的成员属性
       $keys = array_keys(get_class_vars(__CLASS__));
        //判断传递进来的元素的键是否在类的属性中
        if (in_array($key,$keys)) {
            $this->$key = $value;
        }
    }
    //文件上传的函数，  当调用此函数，实现文件上传的操作
    //参数：$key表示的就是input输入框的name属性值。
    public function uploadFile($key)
    {
        //判断有没有设置文件上传后保存的路径
        if (empty($this->path))
        {
            //设为-1，表示未设置上传文件的保存路径
            $this->setOption('errorNumber',-1);
            return false;
        }
        //判断保存的路径有没有权限写入或者执行
        if (!$this->checkDir()) {
            //上传文件的保存的路径不存在或者没有相应的权限，值为-2
            $this->setOption('errorNumber',-2);
            return false;
        }
        //判断$_FILES数组中的error信息是否为0 ，若为0 表示上传的文件没有问题，
        $error = $_FILES[$key]['error'];
        if ($error) {
            $this->setOption('errorNumber',$error);
            return false;
        } else {
            //表示上传的文件没有错误，提取上传文件的相关信息，保存到对应的成员属性中
            $this->getFileInfo($key);
        }
        //判断上传文件的大小、mime类型、后缀类型是否符合要求
        if ((!$this->checkSize())||(!$this->checkMime())||(!$this->checkSuffix())) {
            return false;
        }
        //得到新的文件名和新的文件保存的路径
        $this->newName = $this->createNewName();
        $this->newPath = $this->createNewPath();
    //判断是否是上传文件，若是，移动到新的路径中去
        if (is_uploaded_file($this->tmpName)) {
            if (move_uploaded_file($this->tmpName,$this->newPath.$this->newName)) {
                //0表示上传文件移动成功
                $this->setOption('errorNumber',0);
                return $this->newPath;
            } else {
                //表示上传文件移动失败
                $this->setOption('errorNumber',-7);
                return false;
            }
        } else {
            //不是上传文件
            $this->setOption('errorNumber',-6);
            return false;
        }
    }
    //判断上传文件保存的路径是否存在或者具有相应的权限
    protected function checkDir()
    {
        //判断文件夹是否存在，不存在创建之
        if(!file_exists($this->path))
        {
            return mkdir($this->path,0755,true);
        }
        //判断文件夹是否可写
        if (!is_writeable($this->path))
        {
           return chmod($this->path,0755);
        }
        return true;
    }
    //提取上传文件相关信息的函数
    protected function getFileInfo($key)
    {
        //得到上传文件的名字
        $this->oldName = $_FILES[$key]['name'];
        //得到上传文件的mime类型
        $this->mime = $_FILES[$key]['type'];
        //得到上传文件的临时路径
        $this->tmpName = $_FILES[$key]['tmp_name'];
        //得到上传文件的大小
        $this->size = $_FILES[$key]['size'];
        //得到上传文件的后缀
        $this->suffix = pathinfo($this->oldName)['extension'];
    }
    //判断上传文件大小是否符合要求的函数
    protected function checkSize()
    {
        if($this->size > $this->maxSize) {
            //上传文件大小超过了指定的范围
            $this->setOption('errorNumber','-3');
            return false;
        }
        return true;
    }
    //判断上传文件的mime类型是否符合要求
    protected function checkMime()
    {
        if (!in_array($this->mime,$this->allowMime)) {
            //上传文件的mime类型不符合要求
            $this->setOption('errorNumber','-4');
            return false;
        }
        return true;
    }
    //判断上传文件的后缀类型是否符合要求
    protected function checkSuffix()
    {
        if (!in_array($this->suffix,$this->allowSuffix)) {
            //上传文件的后缀类型不符合要求
            $this->setOption('errorNumber','-5');
            return false;
        }
        return true;
    }
    //得到上传文件的新名字
    protected function createNewName()
    {
        //启用随机的名字，是通过前缀拼接一个随机的id值，在拼接后缀。
        if ($this->isRandName) {
            $name = $this->prefix.uniqid().'.'.$this->suffix;
        } else {
            //启用原来的名字，是通过前缀拼接原来的名字
            $name = $this->prefix.$this->oldName;
        }
        return $name;
    }
    //得到上传文件的新的保存的路径
    protected function createNewPath()
    {
        if ($this->isDatePath) {
            $path = $this->path.date('y/m/d/');
            if (!file_exists($path)) {
                mkdir($path,0755,true);
            }
            return $path;
        } else {
            return $this->path;
        }
    }
    //从外面获取错误代号或者错误信息的方法
    public function __get($name)
    {
        if ('name' == 'errorNumber') {
            return $this->errorNumber;
        } else if ('name' == 'errorInfo') {
            return $this->getErrorInfo();
        }
    }
    //实现错代号和错误信息的一一对应
    protected function getErrorInfo()
    {
        /*
         * -1：表示未设置上传文件的保存路径
         *-2：上传文件的保存的路径不存在或者没有相应的权限，
         *-3：上传文件大小超过了指定的范围
         * -4：上传文件的mime类型不符合要求
         * -5：上传文件的后缀类型不符合要求
         *-6：不是上传文件
         * -7：表示上传文件移动失败
         *0：上传文件成功
         * 1：文件大小超过了php.ini的限制
         * 2:文件大小超过了html的限制
         * 3:部分文件上传
         * 4:没有文件上传
         * 6：找不到临时文件
         * 7：文件写入失败
         * */

        switch($this->errorNumber) {
            case 0:
                $str = '上传文件成功过';
                break;
            case 1:
                $str = '文件大小超过了php.ini的限制';
                break;
            case 2:
                $str = '文件大小超过了html的限制';
                break;
            case 3:
                $str = '部分文件上传';
                break;
            case 4:
                $str = '没有文件上传';
                break;
            case 6:
                $str = '找不到临时文件';
                break;
            case 7:
                $str = '文件写入失败';
                break;
            case -1:
                $str = '表示未设置上传文件的保存路径';
                break;
            case -2:
                $str = '上传文件的保存的路径不存在或者没有相应的权限，';
                break;
            case -3:
                $str = '上传文件大小超过了指定的范围';
                break;
            case -4:
                $str = '上传文件的mime类型不符合要求';
                break;
            case -5:
                $str = '上传文件的后缀类型不符合要求';
                break;
            case -6:
                $str = '不是上传文件';
                break;
            case -7:
                $str = '表示上传文件移动失败';
                break;
        }
        return $str;
    }
}