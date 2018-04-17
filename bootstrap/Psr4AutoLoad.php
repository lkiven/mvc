<?php

class Psr4AutoLoad
{
	//这里面存放命名空间映射
	protected $maps = [];

	function __construct()
	{
		spl_autoload_register([$this, 'autoload']);
	}

	//自己写的自动加载函数
	function autoload($className)
	{
		//完整的类名由命名空间名和类名组成
		//得到命名空间名,根据命名空间名得到其目录路径
		$pos = strrpos($className, '\\');
		$namespace = substr($className, 0, $pos);
		//得到类名
		$realClass = substr($className, $pos + 1);
		//找到文件并且包含进来
		$this->mapLoad($namespace, $realClass);
	}

	//根据命名空间名得到目录路径并且拼接真正的文件全路径
	protected function mapLoad($namespace, $realClass)
	{
		if (array_key_exists($namespace, $this->maps)) {
			$namespace = $this->maps[$namespace];
		}

		//处理路径
		$namespace = rtrim(str_replace('\\/', '/', $namespace), '/').'/';
		//拼接文件全路径
		$filePath = $namespace.$realClass.'.php';
		//将该文件包含进来即可
		if (file_exists($filePath)) {
			include $filePath;
		} else {
			die('该文件不存在');
		}
	}

	//给一个命名空间，给一个路径，将命名空间和路径保存到映射数组中
	function addMaps($namespace, $path)
	{
		if (array_key_exists($namespace, $this->maps)) {
			die('此命名空间已经映射过');
		}
		//将命名空间和路径以键值对形式存放到数组中
		$this->maps[$namespace] = $path;
	}
}