<?php
class WxPay{
    protected $config;
    
    function __construct($config)
    {
        $this->config = $config;
    }
    //统一下单接口
    function native($xml)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        return $this->postXmlCurl($xml, $url);
    }
    
    //设置签名
    function getSign($param){
        if(is_array($param))
        {
            //1：所有参数从小到大的排序
            ksort($param);
            //print_r($param);
            //$param = array_filter($param);
            //将参数按照appid=1231&body=yanghuan这种形式拼接起来
            $str = $this->ToUrlParams($param);
            //在拼接商户的key
            $str .= '&key='.$this->config['mch_key'];
            //最后加密生成签名
            if($this->config['sign_type'] == 'HMAC-SHA256')
            {
                $sign = hash_hmac("sha256",$str ,$this->config['mch_key']);
            }else{
                $sign = md5($str);
            }
            //转大写
            $sign = strtoupper($sign);
            return $sign;
        }
        
    }
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($param)
    {
        $buff = "";
        foreach ($param as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
    
        $buff = trim($buff, "&");
        return $buff;
    }
    //请求
    private static function postXmlCurl( $xml, $url, $second = 30)
    {
        $ch = curl_init();
        $curlVersion = curl_version();
    
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
    
        $proxyHost = "0.0.0.0";
        $proxyPort = 0;
        
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);//严格校验
        //curl_setopt($ch,CURLOPT_USERAGENT, $ua);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    
//         if($useCert == true){
//             //设置证书
//             //使用证书：cert 与 key 分别属于两个.pem文件
//             //证书文件请放入服务器的非web目录下
//             $sslCertPath = "";
//             $sslKeyPath = "";
//             $config->GetSSLCertPath($sslCertPath, $sslKeyPath);
//             curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//             curl_setopt($ch,CURLOPT_SSLCERT, $sslCertPath);
//             curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//             curl_setopt($ch,CURLOPT_SSLKEY, $sslKeyPath);
//         }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WxPayException("curl出错，错误码:$error");
        }
    }
    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    //转XML为数组
    function xmlToArray($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)),1);
    }
    
    
    
    
    
    
    
    
    
    
    
    
}