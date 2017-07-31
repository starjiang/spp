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


框架介绍
===
* 见doc/introduce.ppt

框架命名规范t
===
*  [doc/spp框架命名规范.txt]
*  https://github.com/starjiang/spp/blob/master/doc/spp%E6%A1%86%E6%9E%B6%E5%91%BD%E5%90%8D%E8%A7%84%E8%8C%83.txt
