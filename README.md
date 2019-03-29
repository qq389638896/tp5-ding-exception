# tp5-ding-exception

## composer 安装命令
composer require gover/tp5-ding-exception

## 部署流程
#### 第一步：引入DingTalk.php和ExceptionFormat.php两个文件
#### 第二步：放置好think_exception.tpl
#### 第三步：参考demo代码，发送钉钉异常警报（tpl路径可以在ExceptionFormat里showTpl方法中修改）

## 配置 .env
[dingtalk]
access_token = xxxxxxx

## demo代码
          //发送钉钉报警,$e是Exception异常对象
          $e_info = ExceptionFormat::getDingActionCard($e);
          $btn = [['title' => '详情', 'actionURL' => $e_info['url']],];
          DingTalk::getInstance()->sendActionCard('异常消息' . date('H:i:s'), $e_info['text'], $btn, 1, 1);

备注：
[钉钉token获取链接](https://open-doc.dingtalk.com/docs/doc.htm?spm=a219a.7629140.0.0.karFPe&treeId=257&articleId=105735&docType=1)
