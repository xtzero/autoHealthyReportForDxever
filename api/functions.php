<?php
function ajax($data = [], $code = 200, $msg = "")
{
    echo json_encode([
        'code' => $code,
        'msg' => $msg,
        'data' => $data
    ]);
    die();
}

function curl_post($url, $data = []){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function sms_post($url, $data) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen(json_encode($data))
        )
    );
    // POST数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // 把post的变量加上
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function sendSms_SubscribeSuccess($mobile, $name)
{
    $random = rand(100000, 999999);
    $time = time();
    $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400310523&random=".$random;
    $sig = hash('sha256', "appkey={$APPKEY}&random={$random}&time={$time}&mobile={$mobile}");
    $smsData = [
        'params' => [
            $name
        ],
        'sig' => $sig,
        'sign' => 'Zero的个人主页',
        'tel' => [
            'mobile' => $mobile,
            'nationcode' => '86'
        ],
        'time' => $time,
        'tpl_id' => 544494
    ];
    $send = json_decode(sms_post($url, $smsData), true);
    return $send;
}

function sendSms_reportSuccess($mobile, $name)
{
    $random = rand(100000, 999999);
    $time = time();
    $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400310523&random=".$random;
    $sig = hash('sha256', "appkey={$APPKEY}&random={$random}&time={$time}&mobile={$mobile}");
    $smsData = [
        'params' => [
            $name
        ],
        'sig' => $sig,
        'sign' => 'Zero的个人主页',
        'tel' => [
            'mobile' => $mobile,
            'nationcode' => '86'
        ],
        'time' => $time,
        'tpl_id' => 544478
    ];
    $send = json_decode(sms_post($url, $smsData), true);
    return $send;
}

function sendSms_reportFailed($mobile, $name, $reason)
{
    $random = rand(100000, 999999);
    $time = time();
    $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400310523&random=".$random;
    $sig = hash('sha256', "appkey=5fdbf07575749d4cba77e91d999a833d&random={$random}&time={$time}&mobile={$mobile}");
    $smsData = [
        'params' => [
            $name,
            date('H:i'),
            $reason
        ],
        'sig' => $sig,
        'sign' => 'Zero的个人主页',
        'tel' => [
            'mobile' => $mobile,
            'nationcode' => '86'
        ],
        'time' => $time,
        'tpl_id' => 545432
    ];
    $send = json_decode(sms_post($url, $smsData), true);
    return $send;
}
