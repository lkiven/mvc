<?php

namespace controller;

class IndexController extends Controller
{
	function index()
	{
		//echo '这是index方法<br />';
		$this->display();
	}
	function demo()
	{
		echo '这是demo方法<br />';
	}
}