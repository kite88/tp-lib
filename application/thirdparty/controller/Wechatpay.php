<?php
namespace app\thirdparty\controller;

/**
 * 微信支付
 */
class Wechatpay
{

    protected $appid = "";//应用ID
    protected $mch_id = "";//商户号
    protected $key = "";//秘钥


    /**
     * 退款入口
     * 订单号 order_sn 
     * 退款单号 refund_sn
     * 退款金额 refund_fee
     * 回调地址 notify_url
     */
    public function refund($order_sn,$refund_sn,$refund_fee,$notify_url = '')
    {
        //生成预退款单的必选参数
        $newPara = [];
        $newPara['appid'] = $this->appid;//应用ID
        $newPara['mch_id'] = $this->mch_id;//商户ID
        $newPara['nonce_str'] = $this->createNoncestrWechat();////随机字符串
        $newPara['out_trade_no'] = $order_sn;//订单号
        $newPara['out_refund_no'] = $refund_sn;//退款单号
        $newPara['total_fee'] = $refund_fee;//订单金额(全额退款)
        $newPara['refund_fee'] = $refund_fee;//退款金额(全额退款)
        if( '' !== $notify_url){
            //如果参数中传了notify_url，则商户平台上配置的回调地址将不会生效。
            $newPara['notify_url'] = $notify_url;//回调地址 
        }
        $key = $this->key;
        //第一次签名
        $newPara["sign"] = $this->appgetSign($newPara, $key);
        //把数组转化成xml格式
        $xmlData = $this->arrayToXml($newPara);
        $get_data = $this->sendRefundCurl($xmlData);
        if($get_data['return_code'] == 'SUCCESS' && $get_data['result_code'] == 'SUCCESS'){
            $return['success'] = 1;
            $return['error_msg'] = '';
            return $return;
        }else{
            $return['success'] = 0;
            $return['error_msg'] = $get_data['return_msg'];
            $return['info']  = $get_data;
            return $return;
        }
    }
    /**
     * 发送退款请求
     */
    private function sendRefundCurl($xml)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT,'cert/apiclient_cert.pem');

        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, 'cert/apiclient_key.pem');

        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        $data_xml_arr = $this->XMLDataParse($data);
        return $data_xml_arr;
    }
    /**
     * 订单号 order_num
     * 价格 price
     */
    public function pay($order_num, $price, $notify_url = "")
    {
        $json = array();
        //生成预支付交易单的必选参数:
        $newPara = array();
        //应用ID
        $newPara["appid"] = $this->appid;
        //商户号
        $newPara["mch_id"] = $this->mch_id;
        //设备号
        $newPara["device_info"] = "WEB";
        //随机字符串,这里推荐使用函数生成
        $newPara["nonce_str"] = $this->createNoncestrWechat();
        //商品描述
        $newPara["body"] = "APP支付";
        //商户订单号,这里是商户自己的内部的订单号
        $newPara["out_trade_no"] = $order_num;
        //总金额
        $newPara["total_fee"] = $price * 100;
        //终端IP
        $newPara["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        //通知地址，注意，这里的url里面不要加参数
        $newPara["notify_url"] = $notify_url;
        //交易类型
        $newPara["trade_type"] = "APP";
        $key = $this->key;
        //第一次签名
        $newPara["sign"] = $this->appgetSign($newPara, $key);
        //把数组转化成xml格式
        $xmlData = $this->arrayToXml($newPara);
        $get_data = $this->sendPrePayCurl($xmlData);
        //返回的结果进行判断。
        if ($get_data['return_code'] == "SUCCESS" && $get_data['result_code'] == "SUCCESS") {
            //根据微信支付返回的结果进行二次签名
            //二次签名所需的随机字符串
            $newPara["nonce_str"] = $this->createNoncestrWechat();
            //二次签名所需的时间戳
            $newPara['timeStamp'] = time() . "";
            //二次签名剩余参数的补充
            $secondSignArray = array(
                "appid" => $newPara['appid'],
                "noncestr" => $newPara['nonce_str'],
                "package" => "Sign=WXPay",
                "prepayid" => $get_data['prepay_id'],
                "partnerid" => $newPara['mch_id'],
                "timestamp" => $newPara['timeStamp'],
            );
            $json['success'] = 1;
            $json['ordersn'] = $newPara["out_trade_no"]; //订单号
            $json['order_arr'] = $secondSignArray; //返给前台APP的预支付订单信息
            $json['order_arr']['sign'] = $this->appgetSign($secondSignArray, $key); //预支付订单签名
            $json['data'] = "预支付完成";
            //预支付完成,在下方进行自己内部的业务逻辑
            /*****************************/
            return json_encode($json);
        } else {
            $json['success'] = 0;
            $json['error'] = $get_data['return_msg'];
            return json_encode($json);
        }
    }

    //将数组转换为xml格式
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //发送请求
    public function sendPrePayCurl($xml, $second = 30)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, false);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        $data_xml_arr = $this->XMLDataParse($data);
        if ($data_xml_arr) {
            return $data_xml_arr;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }
    //xml格式数据解析函数
    public function XMLDataParse($data)
    {
        $xml = simplexml_load_string($data, null, LIBXML_NOCDATA);
        $array = json_decode(json_encode($xml), true);
        return $array;
    }
    //随机字符串
    public function createNoncestrWechat($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /*
     * 格式化参数格式化成url参数  生成签名sign
     */
    public function appgetSign($Obj, $appwxpay_key)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        if ($appwxpay_key) {
            $String = $String . "&key=" . $appwxpay_key;
        }
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    //按字典序排序参数
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }
}
