<?php

namespace app\pay\model;

use fast\Http;
use function EasyWeChat\Kernel\Support\get_client_ip;

use app\api\model\Report;
use app\api\model\Usercash;
use app\api\model\Userrecharge;
use app\api\model\Usertotal;
use think\Cache;
use think\Model;
use think\Db;
use think\Log;
use think\Exception;


class Simpay extends Model
{
    //代付提单url(提现)
    public $dai_url = 'https://vip.Simpay.bio/gateway.php';
    //代收提交url(充值)
    public $pay_url = 'https://vip.Simpay.bio/gateway.php';
    //代付回调(提现)
    public $notify_dai = 'https://api.risingfund.org/pay/simpay/paydainotify';
    //代收回调(充值)
    public $notify_pay = 'https://api.risingfund.org/pay/simpay/paynotify';
    //支付成功跳转地址    
    public $callback_url = 'https://www.risecrowd.org/topupsuccess.html';
    //代收秘钥
    public $key = "5a7c341e86f632b39211c73906529a2f";
    //代付秘钥
    public $key3des = "5a7c341e86f632b39211c739";
    public function pay($order_id, $price, $userinfo, $channel_info)
    {
        $param = [
            'merorder' => $order_id,
            'merchantid' => $channel_info['merchantid'],
            'command' => $channel_info['busi_code'],
            'datasets' => "name|phone|email|order|user",
            'price' => (int)$price*100,
            'backurl' => $this->notify_pay,
            'key' => $this->key,
            'notes' => '1'
        ];
        $sign = $this->generateSign($param);
        $param['sign'] = $sign;
        $params = [
            'merchantid' => $channel_info['merchantid'],
            'action' => 'pay',
        ];
        $body = $this->generateSign2($param);
        $params['body'] = $this->en3des($body,$this->key3des);
        Log::mylog("提交参数", $params, "simpay");
        $return_json = Http::post($this->pay_url,json_encode($params));
        Log::mylog("返回参数", $return_json, "simpay");
        $return_array = json_decode($return_json, true);
        if ($return_array['code'] == 'success') {
            $return_array = [
                'code' => 1,
                'payurl' => !empty(($return_array['reason'])) ? ($return_array['reason']) : '',
            ];
        } else {
            $return_array = [
                'code' => 0,
                'msg' => $return_array['reason'],
            ];
        }
        return $return_array;
    }

    /**
     * 代收回调
     */
    public function paynotify($params)
    {
        $params = $this->de3des($params,$this->key3des);
        $params = $this->de3desarr($params);
        Log::mylog('解密3des', $params, 'simpayhd');
        if ($params['ordstate'] == 1) {
            $sign = $params['sign'];
            unset($params['sign']);
            $params['key'] = $this->key;
            $check = $this->generateSign($params);
            if ($sign != $check) {
                Log::mylog('验签失败', $params, 'simpayhd');
                return false;
            }
            $order_id = $params['merorder']; //商户订单号
            $order_num = $params['ordnum']; //平台订单号
            $amount = $params['realprice']/100; //支付金额
            (new Paycommon())->paynotify($order_id, $order_num, $amount, 'simpayhd');
        } else {
            //更新订单信息
            $upd = [
                'status' => 2,
                'order_id' => $params['merorder'],
                'updatetime' => time(),
            ];
            (new Userrecharge())->where('order_id', $params['merorder'])->where('status', 0)->update($upd);
            Log::mylog('支付回调失败！', $params, 'simpayhd');
        }
    }

    public function de3desarr($value){

        $paramArr = explode('&',$value);
        foreach ($paramArr as $param) {
            $paramParts = explode("=", $param);
            $params[$paramParts[0]] = $paramParts[1];
        }
        return $params;
    }
    /**
     *提现 
     */
    public function withdraw($data, $channel)
    {
        $bankname = '';
        if($data['bankname'] == 'Access Bank'){
            $bankname = 'Access Bank';
        }

        if($data['bankname'] == 'Ecobank Nigeria'){
            $bankname = 'Ecobank';
        }

        if($data['bankname'] == 'First City Monument Bank'){
            $bankname = 'FCMB';
        }

        if($data['bankname'] == 'Fidelity Bank'){
            $bankname = 'Fidelity Bank';
        }

        if($data['bankname'] == 'First Bank of Nigeria'){
            $bankname = 'First Bank of Nigeria';
        }

        if($data['bankname'] == 'Guaranty Trust Bank'){
            $bankname = 'GTB';
        }

        if($data['bankname'] == 'Heritage Bank'){
            $bankname = 'Heritage Bank';
        }

        if($data['bankname'] == 'Jaiz Bank'){
            $bankname = 'JAIZ BANK';
        }

        if($data['bankname'] == 'Keystone Bank'){
            $bankname = 'keystone Bank';
        }

        if($data['bankname'] == 'Opay'){
            $bankname = 'OPay';
        }

        if($data['bankname'] == 'PalmPay'){
            $bankname = 'Palmpay';
        }

        if($data['bankname'] == 'Polaris Bank'){
            $bankname = 'Polaris Bank';
        }

        if($data['bankname'] == 'Providus Bank'){
            $bankname = 'Providus Bank';
        }

        if($data['bankname'] == 'Stanbic IBTC Bank'){
            $bankname = 'Stanbic IBTC';
        }

        if($data['bankname'] == 'Standard Chartered Bank'){
            $bankname = 'Standard Chartered Bank';
        }

        if($data['bankname'] == 'Sterling Bank'){
            $bankname = 'Sterling Bank';
        }

        if($data['bankname'] == 'Suntrust Bank'){
            $bankname = 'Suntrust Bank';
        }

        if($data['bankname'] == 'United Bank For Africa'){
            $bankname = 'UBA';
        }

        if($data['bankname'] == 'Union Bank of Nigeria'){
            $bankname = 'Union Bank of Nigeria';
        }

        if($data['bankname'] == 'Unity Bank'){
            $bankname = 'UNITY BANK';
        }

        if($data['bankname'] == 'Wema Bank'){
            $bankname = 'Wema Bank';
        }

        if($data['bankname'] == 'Zenith Bank'){
            $bankname = 'Zenith Bank';
        }

        if($data['bankname'] == 'Eyowo'){
            $bankname = 'Eyowo';
        }

        if($data['bankname'] == 'Kuda Bank'){
            $bankname = 'Kuda';
        }

        if(empty($bankname)){
            return ['code'=>'fail','reason'=>'不支持的银行'];
        }
        $param = array(
            'merorder' => $data['order_id'],
            'merchantid' => $channel['merchantid'],
            'command' => 'NGN2',
            'datasets' => "{$data['username']}|{$data['bankcard']}|{$bankname}|paid",
            'price' => (int)$data['trueprice']*100,
            'backurl' => $this->notify_dai,
            'notes' => 1,
            'key' => $this->key
        );
        $sign = $this->generateSign($param);
        $param['sign'] = $sign;

        $params = [
            'merchantid' => $channel['merchantid'],
            'action' => 'paid',
        ];
        $body = $this->generateSign2($param);
        $params['body'] = $this->en3des($body,$this->key3des);
        Log::mylog("提交参数", $params, "simpaydf");
        $return_json = Http::post($this->pay_url,json_encode($params));
        Log::mylog("返回参数", $return_json, "simpaydf");
        return $return_json;
    }


    /**
     * 提现回调
     */
    public function paydainotify($params)
    {
        $params = $this->de3des($params,$this->key3des);
        $params = $this->de3desarr($params);
        Log::mylog('解密3des', $params, 'simpaydfhd');
        $sign = $params['sign'];
        unset($params['sign']);
        $params['key'] = $this->key;
        $check = $this->generateSign($params);
        if ($sign != $check) {
            Log::mylog('验签失败', $params, 'simpaydfhd');
            return false;
        }
        $usercash = new Usercash();
        if ($params['ordstate'] != 1) {
            try {
                $r = $usercash->where('order_id', $params['merorder'])->find()->toArray();;
                if ($r['status'] == 5) {
                    return false;
                }
                $upd = [
                    'status'  => 4, //新增状态 '代付失败'
                    'updatetime'  => time(),
                ];
                $res = $usercash->where('id', $r['id'])->update($upd);
                if (!$res) {
                    return false;
                }
                Log::mylog('代付失败,订单号:' . $params, 'simpaydfhd');
            } catch (Exception $e) {
                Log::mylog('代付失败,订单号:' . $params['merorder'], $e, 'simpaydfhd');
            }
        } else {
            try {
                $r = $usercash->where('order_id', $params['merorder'])->find()->toArray();
                $upd = [
                    'order_no'  => $params['ordnum'],
                    'updatetime'  => time(),
                    'status' => 3, //新增状态 '代付成功'
                    'paytime' => time(),
                ];
                $res = $usercash->where('status', 'lt', 3)->where('id', $r['id'])->update($upd);
                if (!$res) {
                    return false;
                }
                //统计当日提现金额
                $report = new Report();
                $report->where('date', date("Y-m-d", time()))->setInc('cash', $r['price']);
                //用户提现金额
                (new Usertotal())->where('user_id', $r['user_id'])->setInc('total_withdrawals', $r['price']);
                Log::mylog('提现成功', $params, 'simpaydfhd');
            } catch (Exception $e) {
                Log::mylog('代付失败,订单号:' . $params['merorder'], $e, 'simpaydfhd');
            }
        }
    }

    /**
     * 生成签名   sign = Md5(key1=vaIue1&key2=vaIue2…商户密钥);
     *  @$params 请求参数
     *  @$secretkey   密钥
     */
    public function generateSign(array $params)
    {
        ksort($params);
        $params_str = '';
        foreach ($params as $k => $v) {
            $params_str = $params_str.$v;
        }
        Log::mylog('验签串', $params_str, 'simpay');
        return strtolower(md5($params_str));
    }

    public function generateSign2(array $params)
    {
        $params_str = "merorder={$params['merorder']}&merchantid={$params['merchantid']}&command={$params['command']}&datasets={$params['datasets']}&price={$params['price']}&backurl={$params['backurl']}&notes={$params['notes']}&sign={$params['sign']}";
        Log::mylog('generateSign2', $params_str, 'simpay');
        return $params_str;
    }

    public function curl($postdata)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://pay6de1c7.wowpayglb.com/pay/web"); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function curls($postdata)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://pay6de1c7.wowpayglb.com/pay/transfer"); //支付请求地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    //3des加密
    function en3des($value,$deskey){
        $result = openssl_encrypt($value, 'DES-EDE3', $deskey, OPENSSL_RAW_DATA);
        $result = bin2hex($result);
        return $result;
    }
    //解密
    function de3des($value,$deskey){
        $result = hex2bin($value);
        $result = openssl_decrypt($result, 'DES-EDE3', $deskey, OPENSSL_RAW_DATA);
        return $result;
    }

    function httpPost($url, $data)
    {

        $postData = http_build_query($data); //重要！！！
        $ch = curl_init();
        // 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        $header = array();
        $header[] = 'User-Agent: ozilla/5.0 (X11; Linux i686) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1';
        $header[] = 'Accept-Charset: UTF-8,utf-8;q=0.7,*;q=0.3';
        $header[] = 'Content-Type:application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    // 对证书来源的检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // 从证书中检查SSL加密算法是否存在
        //curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);    // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);       // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, 1);      // 发送一个 常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    // Post提交的数据包
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);      // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_HEADER, 0);        // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    //获取的信息以文件流的形式返回

        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "Errno" . curl_error($ch);   // 捕抓异常
        }
        curl_close($ch);    // 关闭CURL
        return $output;
    }
}
