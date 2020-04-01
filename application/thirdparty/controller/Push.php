<?php
namespace app\thirdparty\controller;

/**
 * workerman 推送 （前提先在服务器启动 public/webpush）
 */
class Push
{

	/**
	 * @param $to_uid string 指明给谁推送，为空表示向所有在线用户推送
	 * @param $content string 发送的内容
	 * @param $type string 类型
	 */
    public function send($to_uid = "",$content = "",$type = "publish")
    {
		// 推送的url地址，使用自己的服务器地址
		$push_api_url = $this->get_domain() . ":8121/";
		$post_data = array(
		   "type"    => $type,
		   "content" => $content,
		   "to"      => $to_uid,
		);
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array("Expect:"));
		$return = curl_exec ( $ch );
		curl_close ( $ch );
		return $return;
    }
    /**
    * 域名（根据启动的服务器绑定的域名也可以直接返回IP）
    */
    private function get_domain()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type . $_SERVER['HTTP_HOST'];
    }

}
