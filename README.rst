Onepass Php SDK
===============

开发环境
----------------

 - php5.2+ 及php7


文件说明
---------------
 - config/config.php Onepass的ID和KEY配置文件,请在 `极验后台 <http://account.geetest.com>`__ 申请,进行替换
 - lib/class.gmessagelib.php 极验库文件(请不要随意改动)
 - web/check_gateway.php 网关校验接口
 - web/check_message.php 根短信校验接口



部署说明
----------------
 - 在将整个项目拷贝到apache的文件目录下，即可访问对应的接口