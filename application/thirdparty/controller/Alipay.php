<?php
namespace app\thirdparty\controller;

/**
 * 支付宝
 */
class Alipay
{
    protected $appid = ''; //应用ID
    protected $rsaPrivateKey = ''; //应用私钥(去头去尾)
    protected $alipayrsaPublicKey = ''; //支付宝公钥(去头去尾)
    
    //支付宝支付 订单号：$order_num,金额：$total_fee
    public function pay($order_sn,$order_amount,$notify_url=""){

        vendor('alipay.aop.AopClient');
        vendor('alipay.aop.request.AlipayTradeAppPayRequest');
        
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        
        $aop->appId = $this->appid;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        
        $bizcontent = "{\"body\":\"%s\"," 
                        . "\"subject\": \"%s\","
                        . "\"out_trade_no\": \"%s\","
                        . "\"timeout_express\": \"30m\"," 
                        . "\"total_amount\": \"%s\","
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}";
        $total_fee = floor($order_amount * 100) / 100;
        $bizcontent = sprintf($bizcontent, "支付宝订单", "支付宝订单", $order_sn, $total_fee);
        // var_dump($bizcontent);die;
        
        $request->setNotifyUrl($notify_url);
        
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        // echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理
        return ['order_sn'=>$order_sn,'code' => $response];
    }

    /**
     * 支付宝退款
     */
    public function refund($out_trade_no,$refund_amount)
    {
        vendor('alipay.aop.AopClient');
        vendor('alipay.aop.request.AlipayTradeRefundRequest');

        $aop = new \AopClient;
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->appid;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest ();
        $out_request_no = $out_trade_no.mt_rand(1000,9999);//TODO 方便多次退款的设置

        $request->setBizContent("{" .
            //订单支付时传入的商户订单号,不能和 trade_no同时为空。
            "\"out_trade_no\":\"$out_trade_no\"," .
            //支付宝交易号，和商户订单号不能同时为空
            //"\"trade_no\":\"2019060622001445xxxx\"," .
            //需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
            "\"refund_amount\":$refund_amount," .
            //标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
            "\"out_request_no\":\"$out_request_no\"" .
            "  }");
        $result = $aop->execute($request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode) && $resultCode == 10000){
            return array('status' => true, 'msg' => '成功' );
        } else {
        	return array('status' => false, 'msg' => '失败:' . $result->$responseNode->sub_msg);
        }
    }
}
