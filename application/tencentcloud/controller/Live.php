<?php
namespace app\tencentcloud\controller;

require_once '../vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Live\V20180801\LiveClient;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamStateRequest;
use TencentCloud\Live\V20180801\Models\DescribeLiveStreamOnlineListRequest;
use TencentCloud\Live\V20180801\Models\DropLiveStreamRequest;
use TencentCloud\Live\V20180801\Models\DescribeStreamPlayInfoListRequest;

class Live
{
    protected $secretId = ""; //云直播的 secretId
    protected $secretKey = ""; //云直播的 secretKey
    protected $domain = "xxxx.xxxxx.myqcloud.com";//腾讯云配置的云直播域名 推流的
    protected $key = "";//腾讯云配置的云直播域名的key 推流的
    protected $appname = "live";//应用名称
    protected $palyDomain = "xxx.xxxxxx.com";//播放域名
    
    /**
      * 获取推流地址
      * 如果不传key和过期时间，将返回不含防盗链的url
      * @param domain 您用来推流的域名
      *        streamName 您用来区别不同推流地址的唯一流名称
      *        key 安全密钥
      *        time 过期时间 sample 2016-11-12 12:00:00
      * @return String url
      * $this->getPushUrl("123.test.com","123456","69e0daf7234b01f257a7adb9f807ae9f","2016-09-11 20:08:07"); 
    */
    public function getPushUrl($streamName)
    {
        $domain = $this->domain;
        $appname = $this->appname;
        $key = $this->key;
        $time = date('Y-m-d H:i:s',time()+ 86400 );
        if ($key && $time) {
            $txTime = strtoupper(base_convert(strtotime($time), 10, 16));
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str = "?" . http_build_query(array(
                "txSecret" => $txSecret,
                "txTime" => $txTime,
            ));
        }
        $list['live_room'] = $streamName;
        $list['expire_time'] = $time;
        $list['push_url'] = "rtmp://".$domain."/". $appname ."/".$streamName . (isset($ext_str) ? $ext_str : "");
        return $list;
    }
    /**
     * 获取播放地址
     */
    public function getPlayUrl($streamName)
    {
        $url['RTMP'] = "rtmp://". $this->palyDomain ."/". $this->appname ."/".$streamName;
        $url['FLV'] = "http://". $this->palyDomain ."/". $this->appname ."/".$streamName . ".flv";
        $url['HLS'] = "http://". $this->palyDomain ."/". $this->appname ."/".$streamName . ".m3u8";
        return $url;
    }
    /**
     * 查询流状态
     */
    public function findStreamState($streamName)
    {
        try {
            $cred = new Credential( $this->secretId , $this->secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);
            $req = new DescribeLiveStreamStateRequest();
            $params = '{"AppName":"'. $this->appname .'","DomainName":"'. $this->domain .'","StreamName":"'. $streamName .'"}';
            $req->fromJsonString($params);
            $resp = $client->DescribeLiveStreamState($req);
            return json_decode($resp->toJsonString(),true);
        }
        catch(TencentCloudSDKException $e) {
            return $e;
        }
    }
    /**
     * 查询直播中的流
     */
    public function findLiveStreamList($PageNum = 1,$PageSize = 10)
    {
        try {
            $cred = new Credential($this->secretId , $this->secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);
            $req = new DescribeLiveStreamOnlineListRequest();
            $params = '{"DomainName":"'. $this->domain .'","AppName":"'. $this->appname .'","PageNum":'. $PageNum .',"PageSize":'. $PageSize .'}';
            $req->fromJsonString($params);
            $resp = $client->DescribeLiveStreamOnlineList($req);
            return json_decode($resp->toJsonString(),true);
        }
        catch(TencentCloudSDKException $e) {
            return $e;
        }
    }
    /**
     * 断开流
     */
    public function dropLiveStream($streamName)
    {
        try {
            $cred = new Credential( $this->secretId, $this->secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);
            $req = new DropLiveStreamRequest();
            $params = '{"StreamName":"'. $streamName .'","DomainName":"'. $this->domain .'","AppName":"'. $this->appname .'"}';
            $req->fromJsonString($params);
            $resp = $client->DropLiveStream($req);
            return json_decode($resp->toJsonString(),true);
        }
        catch(TencentCloudSDKException $e) {
            return $e;
        }
    }
    /**
     * 查询流的播放信息列表
     */
    public function findStreamPlayInfoList($StreamName)
    {
        try {
            $cred = new Credential( $this->secretId, $this->secretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("live.tencentcloudapi.com");
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new LiveClient($cred, "", $clientProfile);
            $req = new DescribeStreamPlayInfoListRequest();
            $time = time();
            $params = '{"PlayDomain":"'. $this->palyDomain .'","StreamName":"'. $StreamName .'","StartTime":"'. date('Y-m-d H:i:s',$time-60) .'","EndTime":"'. date('Y-m-d H:i:s',$time) .'"}';
            $req->fromJsonString($params);
            $resp = $client->DescribeStreamPlayInfoList($req);
            return json_decode($resp->toJsonString(),true);            
        }
        catch(TencentCloudSDKException $e) {
            return $e;
        }
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postParam);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    /**
     * curl get
     */
    private function curlGet($url = "",$param = array()){
        $s_url = $url . '?' . http_build_query($param);
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $s_url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        return $data;
    }
}