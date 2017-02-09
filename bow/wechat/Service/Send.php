<?php
namespace Bow\Wechat\Service;

use Bow\Wechat\Core\AbstractAPI;
use EasyWeChat\Message\Text;
use EasyWeChat\Broadcast\MessageBuilder;
use Bow\Wechat\SimpleHTTP;

class Send extends AbstractAPI
{
	const MSG_TYPE_TEXT = 'text'; // 文本
    const MSG_TYPE_NEWS = 'news'; // 图文
    const MSG_TYPE_VOICE = 'voice'; // 语音
    const MSG_TYPE_IMAGE = 'image'; // 图片
    const MSG_TYPE_VIDEO = 'video'; // 视频
    const MSG_TYPE_CARD = 'card'; // 卡券

	protected $container;

    protected $http;

	function __construct($container)
	{
		$this->container = $container;
        $this->http = new SimpleHTTP();
	}

	/**
     * Send a message.
     *
     * @param string $msgType message type
     * @param mixed  $message message
     * @param mixed  $to
     *
     * @return mixed
     */
    public function send($corpid , $message)
    {
    	$login = new Login($this->container);
    	$access_token = $login->get_corp_token($corpid);

        $api = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $access_token;

        // $logger = $this->container->get('logger');
        // $logger->info($api);
        // $logger->info(json_encode($message));

        return $this->http->parseJSON('post' , [$api, json_encode($message , JSON_UNESCAPED_UNICODE)]);
    }

    /**
     * Send a text message.
     *
     * @param mixed $message message
     * @param mixed $to
     *
     * @return mixed
     */
    public function sendText($corpid , $agentid , $message , $touser = null , $toparty = null , $safe = 0)
    {
    	$messaged = [
            'touser' => $touser,
            'toparty' => $toparty,
            'totag' => '',
            'msgtype' => self::MSG_TYPE_TEXT,
            'agentid' => $agentid,
            'text' => [
                'content' => $message,
            ],
            'safe' => $safe,
        ];
        return $this->send($corpid , $messaged);
    }

    /**
     * Send a news message.
     *
     * @param mixed $message message
     * @param mixed $to
     *
     * @return mixed
     */
    public function sendNews($corpid , $agentid , $message , $touser = null , $toparty = null)
    {
        $messaged = [
            'touser' => $touser,
            'toparty' => $toparty,
            'totag' => '',
            'msgtype' => self::MSG_TYPE_NEWS,
            'agentid' => $agentid,
            'news' => [
                'articles' => $message,
            ],
        ];

        return $this->send($corpid , $messaged);
    }

    /**
     * post request.
     *
     * @param string       $url
     * @param array|string $options
     *
     * @return array|bool
     *
     * @throws HttpException
     */
    private function post($url, $options)
    {
        return $this->parseJSON('json', [$url, $options]);
    }
}