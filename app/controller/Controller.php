<?php

namespace controller;
use \framework\Tpl;

class Controller extends Tpl
{
	function __construct()
	{
		//得到配置信息
		$config = $GLOBALS['config'];
		parent::__construct($config['TPL_VIEW'], $config['TPL_CACHE']);
	}

	//重写父类的display方法。当调用display函数时，如果没有指定模板，那么使用默认模块名和函数名拼接模板
	function display($viewName = null, $isInclude = true, $uri = null)
	{
		if (empty($viewName)) {
			$viewName = $_GET['m'].'/'.$_GET['a'].'.html';
		}
		parent::display($viewName, $isInclude, $uri);
	}
}