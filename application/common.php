<?php
// 应用公共文件
use think\Cache;
use think\Config;
use think\Db;
use think\Loader;
use think\Url;
use think\View;

// 应用公共文件
if (!function_exists('M')) {
    /**
     * 兼容以前3.2的单字母单数 M
     * @param string $name 表名
     * @return DB对象
     */
    function M($name = '')
    {
        if (!empty($name)) {
            return Db::name($name);
        }
    }
}

if (!function_exists('D')) {
    /**
     * 兼容以前3.2的单字母单数 D
     * @param string $name 表名
     * @return DB对象
     */
    function D($name = '')
    {
        $name = Loader::parseName($name, 1); // 转换驼峰式命名
        if (is_file(APP_PATH . "/" . MODULE_NAME . "/model/$name.php")) {
            $class = '\app\\' . MODULE_NAME . '\model\\' . $name;
        } elseif (is_file(APP_PATH . "/home/model/$name.php")) {
            $class = '\app\home\model\\' . $name;
        } elseif (is_file(APP_PATH . "/mobile/model/$name.php")) {
            $class = '\app\mobile\model\\' . $name;
        } elseif (is_file(APP_PATH . "/api/model/$name.php")) {
            $class = '\app\api\model\\' . $name;
        } elseif (is_file(APP_PATH . "/admin/model/$name.php")) {
            $class = '\app\admin\model\\' . $name;
        } elseif (is_file(APP_PATH . "/seller/model/$name.php")) {
            $class = '\app\seller\model\\' . $name;
        }
        if ($class) {
            return new $class();
        } elseif (!empty($name)) {
            return Db::name($name);
        }
    }
}

if (!function_exists('U')) {
    /**
     * 兼容以前3.2的单字母单数 M
     * URL组装 支持不同URL模式
     * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
     * @param string|array $vars 传入的参数，支持数组和字符串
     * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
     * @param boolean $domain 是否显示域名
     * @return string
     */
    function U($url = '', $vars = '', $suffix = true, $domain = false)
    {
        return Url::build($url, $vars, $suffix, $domain);
    }
}

if (!function_exists('S')) {
    /**
     * 兼容以前3.2的单字母单数 S
     * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
     * @param mixed $value 缓存值
     * @param mixed $options 缓存参数
     * @return mixed
     */
    function S($name, $value = '', $options = null)
    {
        if (!empty($value)) {
            Cache::set($name, $value, $options);
        } else {
            return Cache::get($name);
        }

    }
}

if (!function_exists('C')) {
/**
 * 兼容以前3.2的单字母单数 S
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
    function C($name = null, $value = null, $default = null)
    {
        return config($name);
    }
}

if (!function_exists('I')) {
    /**
     * 兼容以前3.2的单字母单数 I
     * 获取输入参数 支持过滤和默认值
     * 使用方法:
     * <code>
     * I('id',0); 获取id参数 自动判断get或者post
     * I('post.name','','htmlspecialchars'); 获取$_POST['name']
     * I('get.'); 获取$_GET
     * </code>
     * @param string $name 变量的名称 支持指定类型
     * @param mixed $default 不存在的时候默认值
     * @param mixed $filter 参数过滤方法
     * @param mixed $datas 要获取的额外数据源
     * @return mixed
     */
    function I($name, $default = '', $filter = 'htmlspecialchars', $datas = null)
    {

        $value = input($name, '', $filter);
        if ($value !== null && $value !== '') {
            return $value;
        }
        if (strstr($name, '.')) {
            $name = explode('.', $name);
            $value = input(end($name), '', $filter);
            if ($value !== null && $value !== '') {
                return $value;
            }

        }
        return $default;
    }
}

if (!function_exists('F')) {
    /**
     * 兼容以前3.2的单字母单数 F
     * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
     * @param mixed $value 缓存值
     * @param mixed $path 缓存参数
     * @return mixed
     */
    function F($name, $value = '', $path = '')
    {
        if (!empty($value)) {
            Cache::set($name, $value);
        } else {
            return Cache::get($name);
        }

    }
}

/**
 * 获取缓存或者更新缓存
 * @param string $config_key 缓存文件名称
 * @param array $data 缓存数据  array('k1'=>'v1','k2'=>'v3')
 * @return array or string or bool
 */
function tpCache($config_key, $data = array())
{
    $param = explode('.', $config_key);
    if (empty($data)) {
        //如$config_key=shop_info则获取网站信息数组
        //如$config_key=shop_info.logo则获取网站logo字符串
        $config = F($param[0], '', TEMP_PATH); //直接获取缓存文件
        if (empty($config)) {
            //缓存文件不存在就读取数据库
            $res = D('config')->where("inc_type", $param[0])->select();
            if ($res) {
                foreach ($res as $k => $val) {
                    $config[$val['name']] = $val['value'];
                }
                F($param[0], $config, TEMP_PATH);
            }
        }
        if (count($param) > 1) {
            return $config[$param[1]];
        } else {
            return $config;
        }
    } else {
        //更新缓存
        $result = D('config')->where("inc_type", $param[0])->select();
        if ($result) {
            foreach ($result as $val) {
                $temp[$val['name']] = $val['value'];
            }
            foreach ($data as $k => $v) {
                $newArr = array('name' => $k, 'value' => trim($v), 'inc_type' => $param[0]);
                if (!isset($temp[$k])) {
                    M('config')->add($newArr); //新key数据插入数据库
                } else {
                    if ($v != $temp[$k]) {
                        M('config')->where("name", $k)->save($newArr);
                    }
//缓存key存在且值有变更新此项
                }
            }
            //更新后的数据库记录
            $newRes = D('config')->where("inc_type", $param[0])->select();
            foreach ($newRes as $rs) {
                $newData[$rs['name']] = $rs['value'];
            }
        } else {
            foreach ($data as $k => $v) {
                $newArr[] = array('name' => $k, 'value' => trim($v), 'inc_type' => $param[0]);
            }
            M('config')->insertAll($newArr);
            $newData = $data;
        }
        return F($param[0], $newData, TEMP_PATH);
    }
}
