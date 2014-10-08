spp 简介
===

Spp 专为SNS游戏，SNS社区开发而生的 PHP MVC框架。轻量，简洁，使用方便，扩展容易，轻松支持千万级别的用户量，model 层支持常用的存储（mysql,mongodb,redis,memcached）,支持主从读写分离，平行扩展，对于游戏大量写操作做了延迟缓写，吞吐量大，支持数据热迁移，配合强大的配置中心，自动装载功能，能够快速，方便，开发自己的APP

依赖
===
* php5.3以上版本，依赖shmop扩展，需打开php 短标签功能

框架目录说明：
===

* spp/model 框架模型实现
* spp/base 框架核心文件目录
* spp/component 框架组件目录

demo app 目录说明：
===
* app/public目录为网站根目录
* app/gconfig 配置中心配置文件
* app/config 框架配置
* app/script 业务常用的一些脚本，比如配置数据加载脚本
* app/controller 控制器目录
* app/model 数据模型
* app/template 视图模版目录

框架介绍
===
* 见doc/introduce.ppt
框架命名规范t
===
*  doc/spp框架命名规范.tx
