仿TP3.2制作MVC框架(文档简介)

一、概况

      框架大概一年多以前（17年3月左右）自己模仿TP3.2写的，对于初中级的开发工程师可以很好的学习，因为它具备“麻雀虽小、五脏俱全”，可以很好的学习里面的mvc的开发编程思想。从而达到一个能力的明显提升，我先简单介绍以下几点吧，如果有兴趣可以留言，看到我会第一时间回复、后续再写其他的....

二、目录结构

|--MVC

	|---app(应用目录)

		|---controller(控制器，继承基础类Controller)

		|---model(模型，继承基础类Model)

		|---view(视图，用来显示页面)

	|---bootstrap

		|---alias.php(命名空间映射)

	|---cache(缓存文件)

	|---config（配置文件：数据库、定义常量）

		|---config.php

	|---public

		|---css（css样式）

		|---fonts(字体)

		|---images(图片)

		|---js（js特效）

	|---vendor

		|---lib（核心类库）

			|---Code.php(验证码类)

			|---Image.php（图像处理类）

			|---Model.php（模型类）

			|---Page.php（分页类）

			|---Tpl.php（模板引擎类）

			|---Upload.php（文件上传类）



	|---index.php(单一入口文件)

三、特色特点

1. 语法简单
2. 结构明显
3. 易于分离



四、运行流程

    MVC（即模型Model、控制器Controller、视图View），模型用于操控数据库，简化数据库操作，控制器用代码，通过模型类访问数据库并拿到数据，将其进行处理后传递给视图，视图用于显示网页，将控制器传递来的数据经过html、css美化后显示在网页中。



     1.当访问这个网址时，首先经过单入口文件index.php:
              index.php中将引入核心文件Start.php
     2.Start.php
              1.启动方法，即创建自动加载对象方法
              2.引入配置文件
              3.引入系统函数库
              4.引入系统核心执行类App
              5.执行Start类的router()函数
     3.Psr4AutoLoad类以
              1.aotoload方法
              	1.系统类映射
              2、检测命名空间映射
                 1.命名空间和路径以键值对形式存放到数组中
          
    4.实例化路由
              1. 实例化路由类后，会自动执行构造函数：
                  1.构造函数会执行URL解析函数，从当前URL中获得模块名，控制器名和方法名
                  2.解析后执行的一个函数，用于实例化控制器类，并调用解析出来的方法
              2.路由类中的其他函数：
                  1.url解析函数
                  2.控制器类实例化及调用函数

五、自我总结

	虽然现在正式工作已有两年工作之久、但是我们还是要正确的认识自己，我现在的工作日复一日，对数据的增删改看、数据的接口调用、微信的开发、以及产品的更新迭代、文档的编写，年复一年的工作早已没有了激情，看着朋友圈的朋友们晒出了诗和远方的照片，坐在写字楼的办公室中的自己，却觉得自己却身心疲惫。或许是因为工作压力，或许是因为上司过于苛刻，也或许是薪资待遇未能达到自己的预期，自己对工作越来越有挫折感、力不从心、心灰意冷，总想着摆脱束缚着自己的工作，摆脱这陌生、冷漠、枯燥的都市生活。

	但是我依然记得两年年前当年那个出顾茅庐的自己，每天学习不同的知识，对新鲜的事物充满着好奇感，就是想着怎么想着把它写下来，运用到自己的的脑中去，那股冲击劲使我一直坚持到现在，每天都会坚持学英语、看美剧、背单词、运动、学习看书，放假跟着女朋友一起去出去旅行，乐此不彼。生活不仅仅只是工作，还有诗和远方！虽然我对现在也并不是那么的满意，但我享受其中的过程，享受团队、享受公司的那个氛围

	此处不鸡汤，不做作，有温度，有力量！我的一篇小小的文章希望给你们带来一些帮助，也请大家正视怠倦的心理，这是人生在该经历的阶段就会经历的事情，相信自己！ 将来的自己一定会感谢现在勇于改变的自己！加油，刘凯文！加油，各位！
