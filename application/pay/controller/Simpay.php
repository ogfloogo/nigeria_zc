<?php

namespace app\pay\controller;

use app\api\controller\Controller;
use app\pay\model\Simpay as ModelSimpay;
use think\Log;

/**
 * Wowpay
 */
class Simpay extends Controller
{

    /**
     * 代收回调
     */
    public function paynotify()
    {
        $data = file_get_contents("php://input");
        Log::mylog('支付回调_data', $data, 'simpayhd');
        (new ModelSimpay())->paynotify($data);
        exit('success');
    }

    /**
     * 代付回调
     */
    public function paydainotify()
    {
        $data = file_get_contents("php://input");
        Log::mylog('提现回调_data', $data, 'simpaydfhd');
        (new ModelSimpay())->paydainotify(json_decode($data,true));
        exit('success');
    } 
}
