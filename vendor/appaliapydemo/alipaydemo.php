<?php

class Alipay{
	public $sign_type = 'RSA';
	public $private_key_path;
	public $appId = '2017102609531290';
  public $service;
  public $partner;
  public $payment_type;
  public $notify_url;
  public $seller_id;
  public $out_trade_no;
  public $subject;
  public $total_fee;
  public $body;
  public $_input_charset;
// 等待签名的数据
  public $data;
  public $sign;
    /**
     * 取得支付链接参数
     */
    public function getPayPara()
    {
        $parameter = array(
            'service' => $this->service,
            'partner' => $this->partner,
            'payment_type' => $this->payment_type,
            'notify_url' => $this->notify_url,
            'seller_id' => $this->seller_id,
            'out_trade_no' => $this->out_trade_no,
            'subject' => $this->subject,
            'total_fee' => $this->total_fee,
            'body' => $this->body,
            '_input_charset' => trim(strtolower($this->_input_charset))
        );

        // $parameter = array(
        //     'service' => 'mobile.securitypay.pay',
        //     'partner' => trim('2088812142334044'),
        //     'payment_type' => '1',
        //     'notify_url' => 'http://'.$_SERVER["SERVER_NAME"].'/index.php/App/Api/alipay_callback',
        //     'seller_id' => '13600182186',
        //     'out_trade_no' => '0819145412-6177',
        //     'subject' => '测试商品',
        //     'total_fee' => '0.01',
        //     'body' => "测试商品 测试商品",
        //     '_input_charset' => trim(strtolower('UTF-8'))
        // );

        // $this->sign_type
        // $this->private_key_path = 'http://'.$_SERVER["SERVER_NAME"].'/public/rsa_private_key.pem';

		// $order_buf['method'] = 'alipay.trade.app.pay';
		// $order_buf['charset'] = 'UTF-8';

		// $order_buf['app_id'] = $this->appId;
  //       $order_buf['partner'] = '2088812142334044';
  //       // $order_buf['seller_id'] = '13600182186';
  //       $order_buf['out_trade_no'] =  '3434356565566';
  //       $order_buf['subject'] =  '在线充值';
  //       $order_buf['body'] =  '在线充值';
  //       $order_buf['total_fee'] =  '0.01';
  //       $order_buf['notify_url'] =  'http://'.$_SERVER["SERVER_NAME"].'/index.php/App/Api/alipay_callback';
  //       $order_buf['service'] = 'mobile.securitypay.pay';
  //       $order_buf['payment_type'] = 1;
  //       $order_buf['_input_charset'] = 'UTF-8';
        // $order_buf['it_b_pay'] = '30m';

        // $parameter = $order_buf;
        // dd($parameter);

        $para = $this->buildRequestPara($parameter);
        $sign = $this->createLinkstringUrlencode($para);

        // 验签名
        // $result = $this->rsaVerify($this->data,'http://'.$_SERVER["SERVER_NAME"].'/public/rsa_public_key.pem',$this->sign);
        // var_dump($result);die;
        return $sign;

    }
    /**
     * 生成要请求给支付宝的参数数组
     * @param $para_temp 请求前的参数数组
     * @return 要请求的参数数组
     */
    private function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        // var_dump($para_sort);die;

        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);

        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim('RSA'));

        return $para_sort;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param $para 签名参数组
     * return 去掉空值与签名参数后的新签名参数组
     */
    private function paraFilter($para)
    {
        $para_filter = array();
        while ((list ($key, $val) = each($para)) == true) {
            if ($key == 'sign' || $key == 'sign_type' || $val == '') {
                continue;
            } else {
                $para_filter[$key] = $para[$key];
            }
        }
        return $para_filter;
    }

    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 生成签名结果
     * @param $para_sort 已排序要签名的数组
     * return 签名结果字符串
     */
    private function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $mysign = '';
        switch (strtoupper(trim($this->sign_type))) {
            case 'MD5':
                $mysign = $this->md5Sign($prestr, $this->key);
                break;
            case 'RSA':
                $mysign = $this->rsaSign($prestr, trim($this->private_key_path));
                break;
            default:
                $mysign = '';
        }

        return $mysign;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串，并对字符串做urlencode编码
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstringUrlencode($para)
    {
        $arg = '';
        while ((list ($key, $val) = each($para)) == true) {
            $arg .= $key . '=' . urlencode($val) . '&';
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	private function createLinkstring($para)
	{
		$arg = '';
		while ((list ($key, $val) = each($para)) == true) {
			$arg .= $key . '=' . $val . '&';
		}
		//去掉最后一个&字符
		$arg = substr($arg, 0, count($arg) - 2);

		//如果存在转义字符，那么去掉转义
		if (get_magic_quotes_gpc()) {
			$arg = stripslashes($arg);
		}

		return $arg;
	}

	/**
	 * RSA签名
	 * @param $data 待签名数据
	 * @param $private_key_path 商户私钥文件路径
	 * return 签名结果
	 */
	private function rsaSign($data, $private_key_path)
	{
    $this->data = $data;
		$priKey = file_get_contents($private_key_path);
		// print_r($priKey);die;
		$res = openssl_get_privatekey($priKey);
		// print_r($res);die;
		openssl_sign($data, $sign, $res);
		openssl_free_key($res);
		//base64编码
		$this->sign = $sign = base64_encode($sign);
		return $sign;
	}


      /**
     * RSA验签
     * @param $data 待签名数据
     * @param $ali_public_key_path 支付宝的公钥文件路径
     * @param $sign 要校对的的签名结果
     * return 验证结果
     */
    function rsaVerify($data, $ali_public_key_path, $sign)  {


      $pubKey= 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';
      $res = "-----BEGIN PUBLIC KEY-----\n" .
        wordwrap($pubKey, 64, "\n", true) .
        "\n-----END PUBLIC KEY-----";

      // $pubKey = file_get_contents($ali_public_key_path);
      //   $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        
        // openssl_free_key($res);

        return $result;
    }


  /**
   * 获取返回时的签名验证结果
   * @param $para_temp 通知返回来的参数数组
   * @param $sign 返回的签名结果
   * @return 签名验证结果
   */
  function getSignVeryfy($para_temp, $sign)
  {
    //除去待签名参数数组中的空值和签名参数
    $para_filter = $this->paraFilter($para_temp);

    //对待签名参数数组排序
    $para_sort = $this->argSort($para_filter);

    //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    $prestr = $this->createLinkstring($para_sort);

    $is_sgin = false;
    switch (strtoupper(trim('RSA'))) {
      case 'MD5':
        $is_sgin = $this->md5Verify($prestr, $sign, $this->key);
        break;
      case 'RSA':
        $is_sgin = $this->rsaVerify($prestr, 'http://'.$_SERVER["SERVER_NAME"].'/public/rsa_public_key.pem', $sign);
        break;
      default:
        $is_sgin = false;
    }
    return $is_sgin;
  }


  /**
   * 获取远程服务器ATN结果,验证返回URL
   * @param $notify_id 通知校验ID
   * @return 服务器ATN结果
   * 验证结果集：
   * invalid命令参数不对 出现这个错误，请检测返回处理中partner和key是否为空
   * true 返回正确信息
   * false 请检查防火墙或者是服务器阻止端口问题以及验证时间是否超过一分钟
   */
  public function getResponse($notify_id)
  {
    $transport = strtolower(trim('http'));
    $partner = trim('2088812142334044');
    $veryfy_url = '';
    if ($transport == 'https') {
      $veryfy_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
    } else {
      $veryfy_url = 'http://notify.alipay.com/trade/notify_query.do?';
    }
    $cacert = getcwd().'\\cacert.pem';
    $veryfy_url = $veryfy_url . 'partner=' . $partner . '&notify_id=' . $notify_id;
    $response_txt = $this->getHttpResponseGET($veryfy_url, $cacert);

    return $response_txt;
  }


  /**
   * 远程获取数据，GET模式
   * 注意：
   * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
   * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
   * @param $url 指定URL完整路径地址
   * @param $cacert_url 指定当前工作目录绝对路径
   * return 远程输出的数据
   */
  private function getHttpResponseGET($url, $cacert_url)
  {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 显示输出结果
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); //SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); //严格认证
    curl_setopt($curl, CURLOPT_CAINFO, $cacert_url); //证书地址
    $responseText = curl_exec($curl);
    // var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    
            // \Think\Log::record('验签名print:'.print_r($is_sign));
    curl_close($curl);

    return $responseText;
  }

}