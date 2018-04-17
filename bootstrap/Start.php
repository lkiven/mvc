<?php

class Start
{
	//用来保存自动加载对象
	static public $auto;
	//启动方法，即创建自动加载对象方法
	static function init()
	{
		self::$auto = new Psr4AutoLoad();
	}

	//路由方法
	static function router()
	{
		//从url中获取要执行的哪个控制器中的哪个方法
		//从get参数中获取，如果没有。默认都是index
		$m = empty($_GET['m']) ? 'index' : $_GET['m'];
		$a = empty($_GET['a']) ? 'index' : $_GET['a'];

		//始终保证get参数中有默认值
		$_GET['m'] = $m;
		$_GET['a'] = $a;

		//将index处理
		$m = ucfirst(strtolower($m));

		//拼接带有命名空间的类名
		$controller = 'controller\\'.$m.'Controller';
		//创建对象并且执行对应方法
		$obj = new $controller();

		call_user_func([$obj, $a]);
	}
}

Start::init();