# tp5-ding-exception

## 部署流程
#### 第一步：引入DingTalk.php和ExceptionFormat.php两个文件
#### 第二步：composer安装guzzlehttp/guzzle依赖
#### 第三步：参考demo代码，发送钉钉异常警报

## 配置 .env
[dingtalk]
#access_token = xxxxxxx

[钉钉token获取链接](https://open-doc.dingtalk.com/docs/doc.htm?spm=a219a.7629140.0.0.karFPe&treeId=257&articleId=105735&docType=1)

## demo代码
         //发送钉钉报警
          $e_info = ExceptionFormat::getDingActionCard($e);
          $btn = [['title' => '详情', 'actionURL' => $e_info['url']],];
          DingTalk::getInstance()->sendActionCard('异常消息' . date('H:i:s'), $e_info['text'], $btn, 1, 1);