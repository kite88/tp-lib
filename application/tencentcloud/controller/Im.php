<?php

namespace app\tencentcloud\controller;

use think\Db;
use think\Cache;
use think\Request;
use think\Config;
use think\Page;
use think\Loader;
use app\tencentcloud\controller\Gensig;

/**
 * 腾讯云IM模块 (返回值跟参数根据官网文档来的，可以看一下相关代码)
 */
class Im
{
	private $sdkappid = '';//IM appid
    private $identifier = 'admin';//im 管理员
    
    /**
     * curl post请求
     */
    private function curlPost($url = "", $postParam = "")
    {
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
     * 随机数
     */
    private function random()
    {
        $str = mt_rand(0, 4000000000);
        return $str;
    }

	public function _initialize()
	{
		parent::_initialize();
	}
    /**
     * 导入账号（创建账号）
     */
	public function accountImport($user_info)
	{
		$sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
		$gensig = new Gensig();
		$usersig = $gensig->genSig($identifier);
		//单个帐号导入接口
		$url = 'https://console.tim.qq.com/v4/im_open_login_svc/account_import?sdkappid=' . $sdkappid . '&identifier=' . $identifier . '&usersig=' . $usersig . '&random=' . $random . '&contenttype=json';
		$data['Identifier'] = (string) $user_info['user_id'];
		$data['Nick'] = $user_info['nickname'];
		$data['FaceUrl'] = $user_info['head_pic'];
		$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$res = $this->curlPost($url, $data);
		$res = json_decode($res, true);
		return $res;
    }
    /**
     * 创建群组
     */
    public function createGroup($info)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
		$gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/create_group?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'Owner_Account' => $info['Owner_Account'],// 群主的 UserId（选填）
            'Type' => $info['Type'],//群组类型：Private/Public/ChatRoom/AVChatRoom/BChatRoom（必填）
            'Name' => $info['Name'],// 群名称（必填）
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }
    /**
     * 获取app中所有群
     */
    public function getAllGroup($info)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/get_appid_group_list?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'Limit' => $info['Limit'],
            'Next'  => $info['Next'],
            'GroupType' => $info['GroupType']
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;       
    }
    /**
     * 获取群组详细信息
     */
    public function getGroupInfo($GroupIdList = [])
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/get_group_info?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            "GroupIdList" => $GroupIdList
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res; 
    }
    /**
     * 解散群组
     */
    public function destroyGroup($info)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/destroy_group?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'GroupId' => $info['GroupId'],//群组ID
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }
    /**
     * 拉取好友
     */
    public function friendGet($info)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/sns/friend_get?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'From_Account' => $info['From_Account'],
            'StartIndex' => $info['StartIndex'],
            'StandardSequence' => $info['StandardSequence'],
            'CustomSequence' => $info['CustomSequence'],
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }

    /**
     * 获取群漫游信息
     */
    public function getGroupMsg($info)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/group_msg_get_simple?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            "GroupId" => $info['GroupId'],
            "ReqMsgSeq" => $info['ReqMsgSeq'],//请求的消息最大 seq，返回 <=ReqMsgSeq 的消息
            "ReqMsgNumber" => $info['ReqMsgNumber']
        ];
        if(empty($info['ReqMsgSeq'])){
            unset($data['ReqMsgSeq']);
        }
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }
    /**
     * 删除IM账号
     * @param [type] $im_account im账号
     * @return void
     * @param
     */
    public function accountDelete($im_account)
	{
		$sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
		$gensig = new Gensig();
		$usersig = $gensig->genSig($identifier);
		//单个帐号导入接口
		$url = 'https://console.tim.qq.com/v4/im_open_login_svc/account_delete?sdkappid=' . $sdkappid . '&identifier=' . $identifier . '&usersig=' . $usersig . '&random=' . $random . '&contenttype=json';
        $data = [];
        $data['DeleteItem'] = [
            [
                'UserID' => $im_account,//im账号
            ],
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
        // if($res['ActionStatus'] == 'OK'){
        //     return $res['ErrorCode'];
        // }elseif($res['ActionStatus'] == 'FAIL' && $res['ErrorCode'] == 71000){//仅支持删除体验版帐号，您当前为专业版，暂不支持删除
        //     return $res['ErrorCode'];
        // }elseif($res['ActionStatus'] == 'FAIL' && $res['ErrorCode'] == 70107){//账号不存在
        //     return $res['ErrorCode'];
        // }else{
        //     return 'FAIL';
        // }
        return $res;
    }

    /**
     * 设置用户资料
     * @param   im_number           IM账号
     * @param   name                名称
     * @param   user_pic            头像
     */
    public function setImUserData($im_number = '', $name = '', $user_pic = '')
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
		$gensig = new Gensig();
		$usersig = $gensig->genSig($identifier);
		$url = 'https://console.tim.qq.com/v4/profile/portrait_set?sdkappid=' . $sdkappid . '&identifier=' . $identifier . '&usersig=' . $usersig . '&random=' . $random . '&contenttype=json';
		$data['From_Account'] = $im_number; // IM的ID
		$data['ProfileItem'] = [
            [
                'Tag' => 'Tag_Profile_IM_Nick',
                'Value' => $name,
            ],
            [
                'Tag' => 'Tag_Profile_IM_Image',
                'Value' => domainImg($user_pic),
            ],
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$res = $this->curlPost($url, $data);
		$res = json_decode($res, true);
        return $res;
    }

    /**
     * 获取用户资料
     * @param To_Account Array 用户
     * @param TagList Array 资料字段
     */
    public function getImUserData($To_Account,$TagList = ['Tag_Profile_IM_Nick','Tag_Profile_IM_Image'])
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
		$gensig = new Gensig();
		$usersig = $gensig->genSig($identifier);
		$url = 'https://console.tim.qq.com/v4/profile/portrait_get?sdkappid=' . $sdkappid . '&identifier=' . $identifier . '&usersig=' . $usersig . '&random=' . $random . '&contenttype=json';
		$data = [
            'To_Account' => $To_Account,
            'TagList'    => $TagList
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
		$res = $this->curlPost($url, $data);
		$res = json_decode($res, true);
        return $res;
    }

    /**
     * 发送普通群消息
     * GroupId 群组ID
     * msg 消息
     */
    public function sendGroupMsg($GroupId = '',$msg = '')
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'GroupId' => $GroupId,
            'Random'  => mt_rand(1000000,9999999),
            'MsgBody' => [
                [
                    'MsgType'     => 'TIMTextElem',
                    'MsgContent'  => [
                        // 'Text' => '{"content":"主播讲得真好","level":1,"name":"淘友_0594"}'
                        'Text' => $msg
                    ]
                ]
            ],
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }
    /**
     * 发送群系统通知
     * @param GroupId string 群组ID
     * @param Content string 通知内容
     */
    public function sendGroupSysNotify($GroupId,$Content)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/send_group_system_notification?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'GroupId' => $GroupId,
            'Content' => $Content
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }

    /**
     * 获取运营数据 (查询 SDKAppID 的最近30天的运营数据)
        AppName	应用名称
        AppId	应用 SDKAppID
        Company	所属客户名称
        ActiveUserNum	活跃用户数
        RegistUserNumOneDay	新增注册人数
        RegistUserNumTotal	累计注册人数
        LoginTimes	登录次数
        LoginUserNum	登录人数
        UpMsgNum	上行消息数
        DownMsgNum	下行消息数
        SendMsgUserNum	发消息人数
        APNSMsgNum	APNs 推送数
        C2CUpMsgNum	上行消息数（C2C）
        C2CDownMsgNum	下行消息数（C2C）
        C2CSendMsgUserNum	发消息人数（C2C）
        C2CAPNSMsgNum	APNs 推送数（C2C）
        MaxOnlineNum	最高在线人数
        ChainIncrease	关系链对数增加量
        ChainDecrease	关系链对数删除量
        GroupUpMsgNum	上行消息数（群）
        GroupDownMsgNum	下行消息数（群）
        GroupSendMsgUserNum	发消息人数（群）
        GroupAPNSMsgNum	APNs 推送数（群）
        GroupSendMsgGroupNum	发消息群组数
        GroupJoinGroupTimes	入群总数
        GroupQuitGroupTimes	退群总数
        GroupNewGroupNum	新增群组数
        GroupAllGroupNum	累计群组数
        GroupDestroyGroupNum	解散群个数
        CallBackReq	回调请求数
        CallBackRsp	回调应答数
        Date 日期
     */
    public function getAppInfo()
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/openconfigsvr/getappinfo?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = (object)[];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }
    /**
     * 下载消息记录(某天某小时的所有单发或群组消息记录)
     * @param ChatType string 消息类型，C2C 表示单发消息 Group 表示群组消息
     * @param MsgTime string 需要下载的消息记录的时间段，
     * 2015120121表示获取2015年12月1日21:00 - 21:59的消息的下载地址。
     * 该字段需精确到小时。
     * 每次请求只能获取某天某小时的所有单发或群组消息记录
     */
    public function getHistory($ChatType,$MsgTime)
    {
        $sdkappid = $this->sdkappid;
		$random = $this->random();
		$identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/open_msg_svc/get_history?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        $data = [
            'ChatType' => $ChatType,
            'MsgTime'  => $MsgTime,
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
		return $res;
    }
    //局部禁言，局部的接禁言
    public function forbid_send_msg($info){

        $sdkappid = $this->sdkappid;
        $random = $this->random();
        $identifier = $this->identifier;
        $gensig = new Gensig();
        $usersig = $gensig->genSig($identifier);
        $url = "https://console.tim.qq.com/v4/group_open_http_svc/forbid_send_msg?sdkappid=". $sdkappid ."&identifier=". $identifier ."&usersig=". $usersig ."&random=". $random ."&contenttype=json";
        
        $data = [
            "GroupId"=>$info['GroupId'],
            "ShutUpTime"=>$info['ShutUpTime'],//0就是解除禁言，// 60禁言时间，单位为秒，除了0就是无解禁
            "Members_Account"=>[
                    $info['Members_Account'], // 群成员 ID（必填）
                 ]// 一次最多添加500个成员) 
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = $this->curlPost($url, $data);
        $res = json_decode($res, true);
        // if($res['ActionStatus'] == 'OK'){
        //     return $res['ErrorCode'];
        // }elseif($res['ActionStatus'] == 'FAIL' && $res['ErrorCode'] == 71000){//仅支持删除体验版帐号，您当前为专业版，暂不支持删除
        //     return $res['ErrorCode'];
        // }elseif($res['ActionStatus'] == 'FAIL' && $res['ErrorCode'] == 70107){//账号不存在
        //     return $res['ErrorCode'];
        // }else{
        //     return 'FAIL';
        // }
        return $res;
    }
}