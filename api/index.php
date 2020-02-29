<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
require_once 'functions.php';

$method = $_GET['f'];
switch ($method) {
    case 'signin': {
        signin();
    }; break;

    case 'td': {
        td();
    }; break;

    default: {
        ajax([], 400, '我劝你什么都别想');
    }; break;
}

function signin()
{
    $params = $_GET;
    if (empty($params['username'])) {
        ajax([], 400, '没有用户名，就是学号');
    }
    if (empty($params['password'])) {
        ajax([], 400, '没有密码');
    }

    $sysConf = include_once('../data/sysconfig/system_config.php');
    $dxeverLogin = curl_post($sysConf['apis']['login'], [
        'studno' => $params['username'],
        'password' => $params['password']
    ]);
    $res = json_decode($dxeverLogin, true);
    if (is_array($res)) {
        if ($res['meta']['code'] != 200) {
            ajax([], 500, "大学印象服务器返回错误：{$res['meta']['message']}");
        } else {
            $dxeverMyInfo = json_decode(curl_post($sysConf['apis']['myInfo'], [
                'token' => $res['data']
            ]), true);
            if (is_array($dxeverMyInfo)) {
                if ($dxeverMyInfo['meta']['code'] != 200) {
                    ajax([], 500, "大学印象服务器返回错误：{$res['meta']['message']}");
                }
                $name = $dxeverMyInfo['data']['name'];

                if (empty($params['mobile'])) {
                    ajax([
                        'name' => $name
                    ], 201, '留个手机号，弄成了通知你');
                }
                if(!isset($params['curlocation'])) {
                    ajax([
                        'name' => $name
                    ], 201, '缺少当前位置');
                }
                $params['goout'] = 0;
                $params['hp'] = 0;
                $params['ncp'] = 0;
                $params['isncp'] = 0;
                $params['touchncp'] = 0;
                $params['hubei'] = 0;

                $sendSms = sendSms_SubscribeSuccess($params['mobile'], $name);
                $userConfigFilename = '../data/userconfig/user_config_'.$params['username'].'.php';
                if (!file_exists($userConfigFilename)) {
                    touch($userConfigFilename);
                }
                $file = fopen($userConfigFilename, "w") or ajax([], 500, '文件打开失败');
                $saveUserConfig = fwrite($file, '<?php return ["mobile" => '.$params['mobile'].',"name"=>"'.$name.'","username" => '.$params['username'].',"idcard" => '.$params['password'].',"curlocation"=>"'.$params['curlocation'].'","goout"=>'.$params['goout'].',"hp"=>'.$params['hp'].',"ncp"=>'.$params['ncp'].',"isncp"=>'.$params['isncp'].',"touchncp"=>'.$params['touchncp'].',"hubei"=>'.$params['hubei'].',"valid"=>1];');
                $userDir = include_once('../data/userconfig/user_configdir.php');
                $usernameArr = [];
                foreach($userDir as $k => $v) {
                    if ($v == $params['username']) continue;
                    $usernameArr[] = '"'.$v.'"';
                }
                $usernameArr[] = '"'.$params['username'].'"';
                $file2 = fopen('../data/userconfig/user_configdir.php', "w") or ajax([], 500, '文件2打开失败');
                $saveUserList = fwrite($file2, "<?php return [".implode(',', $usernameArr)."];");

                ajax([
                    'login' => $res,
                    'userinfo' => $dxeverMyInfo,
                    'sendSms' => $sendSms,
                    'saveUserConfig' => $saveUserConfig,
                    'saveUserList' => $saveUserList
                ], 200, 'suc');
            } else {
                ajax([], 500, '大学印象服务器出错');
            }
        }
    } else {
        ajax([], 500, '大学印象服务器出错');
    }
}

function td()
{
    $params = $_GET;
    if (empty($params['username'])) {
        ajax([], 400, '没有用户名，就是学号');
    }
    if (empty($params['password'])) {
        ajax([], 400, '没有密码');
    }

    $sysConf = include_once('../data/sysconfig/system_config.php');
    $dxeverLogin = curl_post($sysConf['apis']['login'], [
        'studno' => $params['username'],
        'password' => $params['password']
    ]);
    $res = json_decode($dxeverLogin, true);
    if (is_array($res)) {
        if ($res['meta']['code'] != 200) {
            ajax([], 500, "大学印象服务器返回错误：{$res['meta']['message']}");
        } else {
            $userConfigFilename = '../data/userconfig/user_config_'.$params['username'].'.php';
            if (file_exists($userConfigFilename)) {
                $userConfig = include_once($userConfigFilename);
                $file = fopen($userConfigFilename, "w") or ajax([], 500, '文件打开失败');
                $saveUserConfig = fwrite($file, '<?php return ["mobile"=>'.$userConfig['mobile'].',"name"=>"'.$userConfig['name'].'","username" => '.$userConfig['username'].',"idcard" => '.$userConfig['idcard'].',"curlocation"=>"'.$userConfig['curlocation'].'","goout"=>'.$userConfig['goout'].',"hp"=>'.$userConfig['hp'].',"ncp"=>'.$userConfig['ncp'].',"isncp"=>'.$userConfig['isncp'].',"touchncp"=>'.$userConfig['touchncp'].',"hubei"=>'.$userConfig['hubei'].',"valid"=>0];');
                ajax([
                    'login' => $res,
                    'saveUserConfig' => $saveUserConfig
                ], 200, 'suc');
            }
        }
    } else {
        ajax([], 500, '大学印象服务器出错');
    }
}