<?php
namespace app\thirdparty\controller;

/**
 * 快递100
 */
class Express
{
    protected $key = ""; // 这是申请下来的私钥
    protected $customer  = ""; //这是申请下来的公钥
    /**
     * 查询快递
     * @param $postcom  快递公司编码
     * @param $getNu  快递单号
     * @return array  物流跟踪信息数组
     */
    public function index($postcom,$getNu)
    {
        //参数设置
        $key = $this->key;
        $customer = $this->customer;
        $param = array (
            'com' => $postcom, //快递公司编码
            'num' => $getNu,   //快递单号
            'phone' => '',              //手机号
            'from' => '',               //出发地城市
            'to' => '',                 //目的地城市
            'resultv2' => '1'           //开启行政区域解析
        );
        //请求参数
        $post_data = array();
        $post_data["customer"] = $customer;
        $post_data["param"] = json_encode($param);
        $sign = md5($post_data["param"].$key.$post_data["customer"]);
        $post_data["sign"] = strtoupper($sign);
        $url = 'http://poll.kuaidi100.com/poll/query.do';   //实时查询请求地址
        $params = "";
        foreach ($post_data as $k=>$v) {
            $params .= "$k=".urlencode($v)."&";     //默认UTF-8编码格式
        }
        $post_data = substr($params, 0, -1);
        return $this->curlPost($url,$post_data);
    }
    private function curlPost($url,$post_data)
    {
        //发送post请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $data = str_replace("\"", '"', $result );
        // $data = json_decode($data);
        return json_decode($data, true);
    }
}
