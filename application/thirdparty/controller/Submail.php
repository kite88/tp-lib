<?php
namespace app\thirdparty\controller;

/**
 * 赛邮短信
 */
class Submail{
    protected $appid = '';//APPID
    protected $appkey = '';//APPKEY

    public function send_message($mobile,$content)
    {
        $url = 'https://api.mysubmail.com/message/send.json';
        $postParam = [
            'appid'     => $this->appid,
            'signature' => $this->appkey,
            'to'        => $mobile, //合法的手机号
            'content'   => $content //发送的短信内容
        ];
        $json = $this->curlPost($url,$postParam);
        $result = json_decode($json,true);
        if($result['status'] == 'success'){
            $data['status'] = 1;
            $data['msg'] = '发送成功';
        }else{
            $data['status'] = 0;
            $data['msg'] = '发送失败';
        }
        $data['data'] = $result;
        return $data;
    }
    /**
     * curl post请求
     */
    private function curlPost($url = "", $postParam = array()){
        $postFields = http_build_query($postParam);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}