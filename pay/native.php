<?php
include_once 'WxPay.class.php';
include_once 'phpqrcode/phpqrcode.php';
$config = include_once 'config.php';
$wxpay = new WxPay($config);
//扫码支付需要必填的参数
$param['appid'] = $config['appid'];
$param['mch_id'] = $config['mchid'];
$param['nonce_str']  = $wxpay->getNonceStr();//这随机字符串，可以随机生成，32位以内
$param['body'] = '测试';
$param['out_trade_no'] = time().mt_rand(0, 1000);//20181231345641
$param['total_fee'] = 1;
$param['spbill_create_ip'] = '127.0.0.1';
$param['notify_url'] = 'http://paysdk.weixin.qq.com/notify.php';//这地址是需要先在公众号后台设置合法域名的
$param['trade_type'] = 'NATIVE';
//$param['product_id'] = 123456789;
$param['sign_type'] = 'HMAC-SHA256';
//生成签名
$param['sign'] = $wxpay->getSign($param);
//拼接xml,这里的参数要包括sign，但是不用排序,排序只是在生成签名的时候需要排序
$xml = $wxpay->ToXml($param);
//请求微信支付
$res = $wxpay->native($xml);
//将xml转为数组
$res = $wxpay->xmlToArray($res);
if($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS')
{
    $qr = rand(100,10000).'.'.'png';
    QRcode::png($res['code_url'],'qrcode/'.$qr);
    echo '<img src="qrcode/'.$qr.'"/>';
}else{
    die($res['return_msg']);
}



















