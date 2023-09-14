<?php

namespace app\admin\model\activity;

use app\admin\model\CacheModel;
use think\Model;
use traits\model\SoftDelete;

class VipActivity extends CacheModel
{

    use SoftDelete;



    // 表名
    protected $name = 'vip_activity';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text'
    ];
    public $cache_prefix = 'new:vip_activity:';


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }


    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function Userlevel()
    {
        return $this->belongsTo('\app\admin\model\userlevel\UserLevel', 'level', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function Task()
    {
        return $this->belongsTo('\app\admin\model\activity\ActivityTask', 'task_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function Prize()
    {
        return $this->belongsTo('\app\admin\model\activity\ActivityPrize', 'prize_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
