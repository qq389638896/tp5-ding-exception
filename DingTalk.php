<?php

class DingTalk
{
    static public $instance;

    static public function getInstance()
    {
        if (!self::$instance) self::$instance = new self();

        return self::$instance;
    }

    /**
     * 推送text
     * @param $text
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * User:Gover_chan
     * Date: 2019/3/26
     */
    public function sendText($text)
    {
        $params = ['msgtype' => 'text', 'text' => ['content' => $text]];

        return $this->curlApi($params);
    }


    /**
     * 推送markDown语法的内容
     * @param        $markDownContent
     * @param string $title
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * User:Gover_chan
     * Date: 2019/3/26
     */
    public function sendMarkDown($title = '--消息--', $markDownContent)
    {
        $params = ['msgtype' => 'markdown', 'markdown' => ['title' => $title, 'text' => $markDownContent]];

        return $this->curlApi($params);
    }


    /**
     * 推送钉钉ActionCard
     * @param $title
     * @param $text
     * @param $btns  array
     * @param $hideAvatar
     * @param $btnOrientation
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @User:Gover_chan
     * @Date: 2019/3/26
     */
    public function sendActionCard($title, $text, $btns, $hideAvatar = '1', $btnOrientation = '1')
    {
        $params = ['msgtype' => 'actionCard', 'actionCard' => ['title' => $title, 'text' => $text, 'hideAvatar' => $hideAvatar, 'btnOrientation' => $btnOrientation,'btns' => $btns]];

        return $this->curlApi($params);
    }

    /**
     * 请求钉钉API
     * @param $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * User:Gover_chan
     * Date: 2019/3/26
     */
    public function curlApi($params)
    {

        $apiUrl = "https://oapi.dingtalk.com/robot/send?access_token=" . env('dingtalk.access_token');
        $client = new \GuzzleHttp\Client(['timeout' => 2.0]);
        $rs     = $client->post($apiUrl, ['json' => $params]);

        return $rs;
    }


}
