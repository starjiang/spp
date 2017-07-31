spp 简介
===

Spp  PHP MVC框架。轻量，简洁，使用方便，扩展容易，model层支持常用的存储（mysql,mongodb,redis,memcached），配合强大的配置中心，自动装载功能，能够快速，方便，开发自己的APP

依赖
===
* php5.6以上版本，依赖shmop扩展，需打开php 短标签功能

框架目录说明：
===

* spp/model 框架模型实现
* spp/base 框架核心文件目录
* spp/component 框架组件目录

demo app 目录说明：
===
* web/public目录为网站根目录web/public/index.php 入口文件
* config 框架配置文件
* script 业务常用的一些脚本，比如配置数据加载脚本
* template 视图模版目录
* logs 系统日志目录，必须配置写权限
* modules/模块名/controller 控制器目录
* modules/模块名/model 数据模型

路由说明
===
* app/index/get_user
* app为模块名
* index 为controller 名，对应controller 为CIndexController
* get_user 为action 名，对应 getUserAction()

例子安装说明
===
* 按照 doc/nginx_rewrite.txt 配置 url rewrite
* 导入 doc/test.sql 到mysql 
* 配置 nginx htdocs 目录到 web/public 目录
* 项目目录下 新建 logs 目录，chmod 777 logs
* http://domian/ 访问

注意点
===
* CMongoMapper 目前处于测试阶段,慎用