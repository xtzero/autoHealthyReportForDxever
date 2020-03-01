<?php
date_default_timezone_set("Asia/Shanghai");
$baseDir = "/Volumes/Code/autoHealthyReportForDxever";
require_once $baseDir.'/api/functions.php';
$usernameArr = include_once($baseDir.'/data/userconfig/user_configdir.php');
$systemConfig = include_once($baseDir.'/data/sysconfig/system_config.php');
$logFileName = $baseDir."/data/log/log_".date('Ymd').".log";
if (!file_exists($logFileName)) {
    touch($logFileName);
}
$logFile = fopen($logFileName, 'w');
fwrite($logFile, '');
$resEcho = [];
foreach($usernameArr as $k => $v) {
    $resEcho = [];
    $userConfigFilename = $baseDir."/data/userconfig/user_config_{$v}.php";
    if (!file_exists($userConfigFilename)) continue;
    $userConfig = include_once($userConfigFilename);
    if ($userConfig['valid'] == 0) continue;
    $dxeverLogin = json_decode(curl_post($systemConfig['apis']['login'], [
        'studno' => $userConfig['username'],
        'password' => $userConfig['idcard']
    ]), true);
    $resEcho = array_merge($resEcho, [
        "dxeverLogin:",
        json_encode($dxeverLogin),
        ".\n"
    ]);
    if (is_array($dxeverLogin) && $dxeverLogin['meta']['code'] == 200) {
        $token = $dxeverLogin['data'];
        $dxeverCommit = json_decode(curl_post($systemConfig['apis']['commit'],[
            'token' => $token,
            'curlocation' => $userConfig['curlocation'],
            'goout' => $userConfig['goout'],
            'hp' => $userConfig['hp'],
            'ncp' => $userConfig['ncp'],
            'isncp' => $userConfig['isncp'],
            'touchncp' => $userConfig['touchncp'],
            'hubei' => $userConfig['hubei']
        ]), true);
        $resEcho = array_merge($resEcho, [
            "dxeverCommit:",
            json_encode($dxeverCommit),
            ".\n"
        ]);
        if (is_array($dxeverCommit) && $dxeverCommit['meta']['code'] == 200) {
            $resEcho = array_merge($resEcho, [
                $userConfig['name'],
                "在",
                date('Y-m-d H:i:s'),
                "签到完毕！\n"
            ]);
            $sendSms = sendSms_reportSuccess($userConfig['mobile'], $userConfig['name']);
        } else {
            $resEcho = array_merge($resEcho, [
                "大学印象服务器返回错误！\n"
            ]);
        }
    } else {
        $resEcho = array_merge($resEcho, [
            "大学印象服务器返回错误!\n"
        ]);
    }

    $hisLogFilename = $baseDir."/data/log/checkin_{$userConfig['username']}_".date('Ymd').".log";
    if (!file_exists($hisLogFilename)) {
        touch($hisLogFilename);
    }
    $hisLogFile = fopen($hisLogFilename, 'w');
    $writeHisLogFile = fwrite($hisLogFile, implode('', $resEcho));
    $totalLog = json_encode([
        "userconfig" => $userConfig ?? [],
        "resEcho" => $resEcho,
        "writeHisLogFile" => $writeHisLogFile
    ]);
    fputs($logFile, $totalLog."\n");
    fclose($hisLogFile);
    echo $totalLog;
}