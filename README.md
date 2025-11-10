# wecenter_anwsion 问答系统简介

---

由 anwsion 原作者修复维护 版本 , 基于 wecenter(anwsion) github 最后版本，使用AI 辅助编程

wecenter_anwsion 问答系统是一套开源的社交化问答软件系统, 国内首个推出基于 PHP 的社交化问答系统。



### WeCenter 问答系统的环境需求

 1. 可用的 www 服务器，如 Apache、IIS、nginx, 推荐使用性能高效的 Apache 或 nginx.
 2. PHP 7.4 及以上
 3. MySQL 5.0 及以上, 服务器需要支持 MySQLi 或 PDO_MySQL
 4. GD 图形库支持或 ImageMagick 支持, 推荐使用 ImageMagick, 在处理大文件的时候表现良好

### WeCenter 问答系统的安装

 1. 上传 upload 目录中的文件到服务器
 2. 设置目录属性（windows 服务器可忽略这一步），以下这些目录需要可读写权限

    ./
    ./system
    ./system/config 含子目录

 3. 访问站点开始安装
 4. 参照页面提示，进行安装，直至安装完毕

