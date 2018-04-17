<?php
namespace framework;
class Tpl
{
	//模板文件的路径
	protected $viewDir = './view/';
	//生成的缓存文件的路径
	protected $cacheDir = './cache/';
	//过期时间
	protected $lifeTime = 3600;
	//用来存放显示变量的数组
	protected $vars = [];

	//构造方法对成员变量进行初始化
	function __construct($viewDir = null, $cacheDir = null, $lifeTime = null)
	{
		//判断是否为空，如果为空，使用默认值，如果不为空，判断并且设置
		if (!empty($viewDir)) {
			//判断所传递路径是否是目录
			if ($this->checkDir($viewDir)) {
				$this->viewDir = $viewDir;
			}
		}
		if (!empty($cacheDir)) {
			if ($this->checkDir($cacheDir)) {
				$this->cacheDir = $cacheDir;
			}
		}
		if (!empty($lifeTime)) {
			$this->lifeTime = $lifeTime;
		}
	}	

	//判断目录路径是否正确
	protected function checkDir($dirPath)
	{
		//如果目录不存在或者不是目录那么创建该目录
		if (!file_exists($dirPath) || !is_dir($dirPath)) {
			return mkdir($dirPath, 0755, true);
		}
		//判断该目录是否可读写
		if (!is_writable($dirPath) || !is_readable($dirPath)) {
			return chmod($dirPath, 0755);
		}
		return true;
	}
	
	//需要对外公开的方法
	//分配变量方法
	//$title = '中国';  $tpl->assign('title', $title);
	function assign($name, $value)
	{
		$this->vars[$name] = $value;
	}

	//展示缓存文件方法
	//$viewName:模板文件名
	//$isInclude:模板文件是仅仅需要编译，还是先编译再包含进来
	//$uri:index.php?page=1,为了让缓存的文件名不重复，将文件名和uri拼接起来再md5一下，生成缓存的文件名
	function display($viewName, $isInclude = true, $uri = null)
	{
		//拼接模板文件的全路径
		$viewPath = rtrim($this->viewDir, '/').'/'.$viewName;
		if (!file_exists($viewPath)) {
			die('模板文件不存在');
		}
		//拼接缓存文件的全路径
		$cacheName = md5($viewName.$uri).'.php';
		$cachePath = rtrim($this->cacheDir, '/').'/'.$cacheName;
		//根据缓存文件全路径，判断缓存文件是否存在
		if (!file_exists($cachePath)) {
			//编译模板文件
			$php = $this->compile($viewPath);
			//写入文件，生成缓存文件
			file_put_contents($cachePath, $php);
		} else {
			//如果缓存文件不存在，编译模板文件，生成缓存文件
			//如果缓存文件存在，第一个，判断缓存文件是否过期；第二个，判断模板文件是否被修改过，如果模板文件被修改过，缓存文件需要重新生成
			$isTimeout = (filectime($cachePath) + $this->lifeTime) > time() ? false : true;
			$isChange = filemtime($viewPath) > filemtime($cachePath) ? true : false;
			//缓存文件重新生成
			if ($isTimeout || $isChange) {
				$php = $this->compile($viewPath);
				file_put_contents($cachePath, $php);
			}
		}
		
		//判断缓存文件是否需要包含进来
		if ($isInclude) {
			//将变量解析出来
			extract($this->vars);
			//展示缓存文件
			include $cachePath;
		}
	}

	//compile方法，编译html文件
	protected function compile($filePath)
	{
		//读取文件内容
		$html = file_get_contents($filePath);
		//正则替换
		$array = [
			'{$%%}' => '<?=$\1; ?>',
			'{foreach %%}' => '<?php foreach (\1): ?>',
			'{/foreach}' => '<?php endforeach?>',
			'{include %%}' => '',
			'{if %%}' => '<?php if (\1): ?>',
		];
		//遍历数组，将%%全部修改为 .+ ，然后执行正则替换
		foreach ($array as $key => $value) {
			//生成正则表达式
			$pattern = '#'.str_replace('%%', '(.+?)', preg_quote($key, '#')).'#';
			//实现正则替换
			if (strstr($pattern, 'include')) {
				$html = preg_replace_callback($pattern, [$this, 'parseInclude'], $html);
			} else {
				//执行替换
				$html = preg_replace($pattern, $value, $html);
			}
		}
		//返回替换后的内容
		return $html;
	}

	//处理include正则表达式，$data就是匹配到的内容
	protected function parseInclude($data)
	{
		//将文件名两边的引号去除掉
		$fileName = trim($data[1], '\'"');
		//然后不包含文件生成缓存
		$this->display($fileName, false);
		//拼接缓存文件全路径
		$cacheName = md5($fileName).'.php';
		$cachePath = rtrim($this->cacheDir, '/').'/'.$cacheName;
		return '<?php include "'.$cachePath.'"?>';
	}
}