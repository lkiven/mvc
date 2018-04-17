<?php
namespace framework;
class Model
{
//完成数据库的增删改查

	//主机名
	protected $host;
	//用户名
	protected $user;
	//密码
	protected $pwd;
	//数据库名
	protected $dbname;
	//字符集
	protected $charset;
	//表前缀
	protected $prefix;
	//数据库连接资源
	public $link;
	//数据表的名字
	protected $tableName = "user";
	//sql语句
	protected $sql;
	//缓存字段数组，当你没有传递字段的时候，使用缓存字段
	protected $fields;
	//options数组    来存放查询时候条件的数组
	protected $options;

	//构造方法
	function __construct($config = null)
	{
		if (empty($config)) {
			if (empty($GLOBALS['config'])) {
                $config = include 'config/config.php';
            } else {
                $config = $GLOBALS['config'];
            }
		}
		//初始化这些属性
		$this->host = $config['DB_HOST'];
		$this->user = $config['DB_USER'];
		$this->pwd = $config['DB_PWD'];
		$this->dbname = $config['DB_NAME'];
		$this->charset = $config['DB_CHARSET'];
		$this->prefix = $config['DB_PREFIX'];

		//连接数据库，将连接成功后的资源保存起来
		$this->link = $this->connect();
		//获取数据表名字的函数
		$this->tableName = $this->getTableName();
		//得到缓存字段的数组，将他保存到$this->fields;
		$this->fields = $this->getCacheFields();
		//初始化options数组,这里面存放的是查询时候的字段
		$this->initOptions();
	}
	//连接数据库的方法
	protected function connect()
	{
		$link = mysqli_connect($this->host,$this->user,$this->pwd);

		if (!$link) {
			exit('数据库连接失败');
		}
		mysqli_set_charset($link,$this->charset);
		mysqli_select_db($link,$this->dbname);
		return $link;
	}

	//获得数据表名字的函数 分两种：一种是默认表名  一种通过类名获取
	protected function getTableName()
	{
		//如果有默认的表名，采用默认的表名
		if (!empty($this->tableName)) {
			return $this->prefix.$this->tableName;
		}
		//获取类名
		$className = get_class($this);
		//获取的类名是  UserModel  PhoneModel   GoodsModel
		$table = strtolower(substr($className,0,-5));
		return $this->prefix.$table;
	}

	//获取缓存字段的函数
	protected function getCacheFields()
	{
		//拼接缓存字段文件所在的路径
		$cacheFile = './cache/'.$this->tableName.'.php';
		if (file_exists($cacheFile)) {
			return include $cacheFile;
		}
		
		//准备sql语句
		$sql = "desc ".$this->tableName;
		//调用结果集查询函数，得到结果
		$result = $this->query($sql);
		//var_dump($result);
		foreach ($result as $value) {
			$field[] = $value['Field'];
			//得到主键，并且将其保存起来
			if ($value['Key'] == 'PRI') {
				$field['PRI'] = $value['Field'];
			}
		}
		//var_dump($field);
		//将上面得到的字段的数组保存到文件中
		$str = var_export($field,true);
		$str = "<?php \n return ".$str.';';
		//写到指定的文件中
		file_put_contents($cacheFile, $str);
		return $field;
	}

	//查询结果集函数，返回的是一个二维数组
	function query($sql)
	{
		//将options数组中的值清空，恢复为初始状态
		$this->initOptions();
		$result = mysqli_query($this->link,$sql);
		if ($result && mysqli_affected_rows($this->link)) {
			while($data = mysqli_fetch_assoc($result)) {
				$newData[] = $data;
			}
			return $newData;
		}
		return false;
	}
	//初始化查询时 的options数组,这里面存放的是查询时候的字段
	protected function initOptions()
	{
		$arr = ['where','order','having','group','limit','field','table'];
		foreach ($arr as $value) {
			//将$this->options数组的值设置为空，键为$arr数组的值
			$this->options[$value] = '';
			
			//需要设置field为缓存的字段的值，table 为tableName.
			if ($value == 'field') {
				$this->options[$value] = join(',',array_unique($this->fields));
			} else if($value == 'table') {
				$this->options[$value] = $this->tableName;
			}
		}
	}
	//查询函数
	function select()
	{
		//带有占位符的sql语句
		$sql = 'select %FIELD% from %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%';
		//将$this->options里面的值来依次替换上面的占位符

		$sql = str_replace(
			['%FIELD%','%TABLE%','%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'],
			[$this->options['field'],
			$this->options['table'],
			$this->options['where'],
			$this->options['group'],
			$this->options['having'],
			$this->options['order'],
			$this->options['limit'],
			],
			$sql
		);


		//保存一份sql语句，方便排错
		$this->sql = $sql;
		//执行SQL 语句
		return $this->query($sql);
	}
	//where函数
	function where($where)
	{
		if (!empty($where)) {
			$this->options['where'] = 'where '.$where;
		}
		return $this;
	}
	//table函数
	function table($table)
	{
		if (!empty($table)) {
			$this->options['table'] = $table;
		}
		return $this;
	}
	//field函数  'id,name,password'   或者 [id,name,password]
	function field($field)
	{
		if (!empty($field)) {
			if(is_string($field)) {
				$this->options['field'] = $field;	
			} elseif(is_array($field)) {
				$this->options['field'] = join(',',$field);
			}
		}
		return $this;
	}
	//group函数
	function group($group)
	{
		if (!empty($group)) {
			$this->options['group'] = 'group by '.$group;
		}
		return $this;
	}
	//having函数
	function having($having) 
	{
		if (!empty($having)) {
			$this->options['having'] = 'having '.$having;
		}
		return $this;
	}
	//order函数
	function order($order)
	{
		if (!empty($order)) {
			$this->options['order'] = 'order by '.$order;
		}
		return $this;
	}
	//limit函数   '1,3'  [1,3]
	function limit($limit)
	{
		if (!empty($limit)) {
			if(is_string($limit)) {
				$this->options['limit'] ='limit '.$limit;	
			} elseif(is_array($limit)) {
				$this->options['limit'] ='limit '.join(',',$limit);
			}
		}
		return $this;
	}
	//获取sql语句
	function __get($name)
	{
		if ($name == 'sql') {
			return $this->sql;
		}
		return false;
	}
	//添加数据的函数    $data是一个关联数组
	function insert($data)
	{
		//处理关联数组中的值如果是字符串的，两边添加上引号
		$data = $this->parseValue($data);
		//先提取data数组中的键
		$keys = array_keys($data);
		//提取数组中所有的值
		$values = array_values($data);
		//带占位符的sql语句
		$sql = 'insert into %TABLE%(%FIELD%) values(%VALUE%)';
		$sql = str_replace(
			['%TABLE%','%FIELD%','%VALUE%'],
			[$this->options['table'],join(',',$keys),join(',',$values)],
			$sql
		);

		//将sql语句保存起来
		$this->sql = $sql;

		//执行SQL 语句
		return $this->exec($sql,true);
	}
	//增删改语句执行的函数
	function exec($sql,$insertId = false)
	{
		//将options数组中的值清空，恢复为初始状态
		$this->initOptions();
		$result = mysqli_query($this->link,$sql);
		if ($result && mysqli_affected_rows($this->link)) {
			if ($insertId) {
				return mysqli_insert_id($this->link);
			} else {
				return mysqli_affected_rows($this->link);
			}
		}
		return false;
	}	
	//对关联数组中值是字符串的参数，两边加上引号
	protected function parseValue($data)
	{
		foreach($data as $key=>$value) {
			if (is_string($value)) {
				$value = '"'.$value.'"';
			}
			$newData[$key] = $value;
		}
		return $newData;
	}
	//删除函数
	function delete()
	{
		//带占位符的sql语句
		$sql = 'delete from %TABLE% %WHERE%';
		$sql = str_replace(
			['%TABLE%','%WHERE%'],
			[$this->options['table'],$this->options['where']],
			$sql
		);

		//保存sql语句
		$this->sql = $sql;
		//执行sql语句
		return $this->exec($sql);
	}
	//修改数据的函数   传递的参数以关联数组的形式传递
	function update($data)
	{
		//给修改的数据中有字符串的值，两边给添加引号
		$data = $this->parseValue($data);
		//将$data处理成固定的形式  键1=值1   键2=值2
		$value = $this->parseUpdate($data);
		//带占位符的sql语句
		$sql = 'update %TABLE% set %VALUE% %WHERE%';
		$sql = str_replace(
			['%TABLE%','%VALUE%','%WHERE%'],
			[$this->options['table'],$value,$this->options['where']],
			$sql
		);	
		//保存sql语句
		$this->sql = $sql;
		//执行sql语句
		return $this->exec($sql);	
	}
	//将关联数组拼接为修改格式的字符串  键1=值1   键2=值2
	protected function parseUpdate($data)
	{
		foreach($data as $key=>$value) {
			$new[] = $key.'='.$value;
		}
		return join(',',$new);
	}
	//统计数据个数的函数 count
	function count($field = null)
	{
		if (empty($field)) {
			$field = $this->fields['PRI'];
		}
		$result = $this->field('count('.$field.') as count')->table()->select();
		return $result[0]['count'];
	}
	//求最大值的函数 max
	function max($field = null)
	{
		if (empty($field)) {
			$field = $this->fields['PRI'];
		}
		$result = $this->field('max('.$field.') as max')->table()->select();
		return $result[0]['max'];
	}
	//求最小值的函数 min
	function min($field = null)
	{
		if (empty($field)) {
			$field = $this->fields['PRI'];
		}
		$result = $this->field('min('.$field.') as min')->table()->select();
		return $result[0]['min'];
	}
	//求和的函数 min
	function sum($field = null)
	{
		if (empty($field)) {
			$field = $this->fields['PRI'];
		}
		$result = $this->field('sum('.$field.') as sum')->table()->select();
		return $result[0]['sum'];
	}

	//
	
	//关闭数据库连接
	function __destruct()
	{
		mysqli_close($this->link);
	}
	function __call($name,$args)
	{
		//获取前面5个字符，是否是getBy
		$str = substr($name,0,5);
		//获取后面的字符，就是字段名
		$field = strtolower(substr($name,5));
		if ($str == 'getBy') {
			return $this->where($field.'="'.$args[0].'"')->select();
		}
		return false;
	}	
}

//var_dump($model->getByname('刘凯文'));


