<?php
namespace framework;
class Page
{
    //每页显示数
    protected $number;
    //数据的总条数
    protected $totalCount;
    //总页数
    protected $totalPage;
    //页码数
    protected $page;
    //url地址
    protected $url;

    public function __construct($number = 10,$totalCount = 32)
    {
        $this->number = $number;
        $this->totalCount = $totalCount;
        //总页数
        $this->totalPage = $this->getTotalPage();
        //页码数
        $this->page = $this->getPage();
        //获取url地址
        $this->url = $this->getUrl();

    }
    //获取总页数的函数
    protected function getTotalPage()
    {
        return ceil($this->totalCount / $this->number);
    }
    //获取页码数的函数
    protected function getPage()
    {
        if (empty($_GET['page'])) {
            $page = 1;
        } else {
            $page = $_GET['page'];
        }
        return $page;
    }
    //获取url地址的函数
    protected function getUrl()
    {
        //得到协议名
        $scheme = $_SERVER['REQUEST_SCHEME'];
        //得到主机名
        $host = $_SERVER['SERVER_NAME'];
        //得到端口号
        $port = $_SERVER['SERVER_PORT'];
        //文件名和参数
        $pathData = $_SERVER['REQUEST_URI'];

        //'test.php?name=hua&page=1'
        //对文件名和参数进行处理，将page参数给干掉,首先处理成数组的形式
        $data = parse_url($pathData);
        //得到文件名
        $path = $data['path'];

        //判断有没有query参数，若有将里面的page参数干掉
        if (!empty($data['query'])) {
            //将参数处理成关联数组的形式，方便干掉里面的page参数
            parse_str($data['query'],$arr);
            //将$arr数组中的page参数干掉
            unset($arr['page']);
            //将剩余的参数在转换为字符串的形式
           $query = http_build_query($arr);
            //需要将参数拼接到文件的后面，将page参数干掉之后，分为了两种情况，一种是参数只有page，干掉之后，参数为空，不需要拼接了， 另一种是当干掉page参数之后，还剩下其余的参数，这时候需要将剩余的参数拼接回去。
            if (!empty($query)) {
                $path = $path.'?'.$query;
            }
        }
        //根据上面得到的信息拼接完整的url地址
        $url = $scheme.'://'.$host.':'.$port.$path;
        return $url;
    }

    //对url后面的页码数拼接封装成一个函数
    protected function setUrl($page)
    {
        if (strstr($this->url,'?')) {
            return $this->url.'&'.$page;
        } else {
            return $this->url.'?'.$page;
        }
    }
    //首页
    protected function first()
    {
        return $this->setUrl('page=1');
    }
    //上一页
    protected function prev()
    {
        if ($this->page - 1 < 1) {
            $page = 1;
        } else if ($this->page - 1 >= $this->totalPage) {
            $page = $this->totalPage - 1;
        } else {
            $page = $this->page - 1;
        }
        return $this->setUrl('page='.$page);
    }
    //下一页
    protected function next()
    {
        if ($this->page + 1 > $this->totalPage) {
            $page = $this->totalPage;
        } else {
            $page = $this->page + 1;
        }
        return $this->setUrl('page='.$page);
    }
    //尾页
    protected function end()
    {
        return $this->setUrl('page='.$this->totalPage);
    }

    public function allPage()
    {
        return [
            'first'=>$this->first(),
            'prev'=>$this->prev(),
            'next'=>$this->next(),
            'end'=>$this->end(),
        ];
    }
    //
    public function limit()
    {
        $offset = ($this->page - 1) * $this->number;
        return $offset.','.$this->number;
    }
}