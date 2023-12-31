<?php

namespace app\admin\controller\sys;

use app\admin\model\finance\UserCash;
use app\admin\model\finance\UserRecharge;
use app\admin\model\financebuy\FinanceOrder;
use app\admin\model\User;
use app\common\controller\Backend;

/**
 * 用户统计管理
 *
 * @icon fa fa-circle-o
 */
class Report extends Backend
{

    /**
     * Report模型对象
     * @var \app\admin\model\sys\Report
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\sys\Report;
        $this->view->assign("nowtime", date("Y-m-d H:i:s",time()));
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function index2()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach ($list as &$value){
            $start = strtotime("{$value['date']} 00:00:00") + 25200;
            $end = strtotime("{$value['date']} 23:59:59") + 25200;
            $newuser = (new User())->where(['sid'=>0])->where(['createtime'=>['between',[$start,$end]]])->column('id');
            $value['newuser'] = count($newuser);
            $value['neworder'] = (new FinanceOrder())->where(['is_robot'=>0,'user_id'=>['in',$newuser]])->group('user_id')->count();
            $value['newrechargenum'] = (new UserRecharge())->where(['user_id'=>['in',$newuser],'status'=>1])->group('user_id')->count();
            $value['newcashnum'] = (new UserCash())->where(['user_id'=>['in',$newuser],'status'=>3])->group('user_id')->count();
            $value['newrecharge'] = (new UserRecharge())->where(['user_id'=>['in',$newuser],'status'=>1])->sum('price');
            $value['newcash'] = (new UserCash())->where(['user_id'=>['in',$newuser],'status'=>3])->sum('price');
        }
//        var_dump($list);exit;
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }

    public function index3()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if (false === $this->request->isAjax()) {
            return $this->view->fetch();
        }
        //如果发送的来源是 Selectpage，则转发到 Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        [$where, $sort, $order, $offset, $limit] = $this->buildparams();
        $list = $this->model
            ->where($where)
            ->order($sort, $order)
            ->paginate($limit);
        foreach ($list as &$value){
            $start = strtotime("{$value['date']} 00:00:00") + 25200;
            $end = strtotime("{$value['date']} 23:59:59") + 25200;
            $newuser = (new User())->where(['sid'=>['<>',0]])->where(['createtime'=>['between',[$start,$end]]])->column('id');
            $value['newuser'] = count($newuser);
            $value['neworder'] = (new FinanceOrder())->where(['is_robot'=>0,'user_id'=>['in',$newuser]])->group('user_id')->count();
            $value['newrechargenum'] = (new UserRecharge())->where(['user_id'=>['in',$newuser],'status'=>1])->group('user_id')->count();
            $value['newcashnum'] = (new UserCash())->where(['user_id'=>['in',$newuser],'status'=>3])->group('user_id')->count();
            $value['newrecharge'] = (new UserRecharge())->where(['user_id'=>['in',$newuser],'status'=>1])->sum('price');
            $value['newcash'] = (new UserCash())->where(['user_id'=>['in',$newuser],'status'=>3])->sum('price');

        }
//        var_dump($list);exit;
        $result = ['total' => $list->total(), 'rows' => $list->items()];
        return json($result);
    }
}
