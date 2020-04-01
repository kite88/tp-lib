<?php
namespace app\thirdparty\controller;

require '../vendor/jpush-api-php-client/autoload.php';
/**
 * 极光推送
 */
class Jpushsend{

    protected $app_key = "";//公匙
    protected $master_secret = "";//私钥

    //推送
    /**
     * message 消息体
     * user 用户
     */
    public function pushsend($message = [], $user)
    {
        $app_key        = $this->app_key;
        $master_secret  = $this->master_secret;
        $path="./log/jpush";
        if(!is_dir($path)){
            @mkdir($path,0777,true);
        }
        $log_file = $path . '/' . date('Ymd') . '.log';
        $client = new \JPush\Client($app_key, $master_secret , $log_file);
        // return $user_s;
        $json_data = json_encode($message,JSON_UNESCAPED_UNICODE);
        $pusher = $client->push();
        $pusher->setPlatform('all');
        $pusher->addAlias($user);
        // $pusher->addAllAudience();
        $pusher->message($json_data);
        // $pusher->setNotificationAlert($json_data);
//        $pusher->setNotificationAlert($message['title']);
        try {
            return $pusher->send();
        } catch (\JPush\Exceptions\JPushException $e) {
            return $e;
        }
    }
}
