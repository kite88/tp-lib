<?php
namespace app\thirdparty\controller;

/**
 * 微信应用
 */
class Weixin
{
	
	protected $appid  = "";//微信应用ID
	protected $secret = "";//微信应用私钥

	/**
	 * 获取微信用户信息（APP）
	 */
	public function getWeixinInfo_APP($access_token = "",$openid = "")
	{
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=". $access_token ."&openid=" . $openid;
		$json = $this->curlGet($url);
		return json_decode($json,true);
	}
	// curl GET请求
	private function curlGet($url = "")
	{
		$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);         
        // curl_setopt($curl, CURLOPT_HEADER, 1);        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设置获取的信息以文件流的形式返回，而不是直接输出
        $data = curl_exec($curl);                     //执行命令
        curl_close($curl);                            //关闭URL请求
        return $data;
	}

}