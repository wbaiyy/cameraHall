<?php
// +----------------------------------------------------------------------
// | 17Joys [ 让我们一起开发内容管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://www.17joys.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 马明 <alex0018@126.com>
// +----------------------------------------------------------------------
//
class AutoCheckAction extends Action
{

    function index()
    {
        //echo 99999;die;
        echo 'start';
        set_time_limit(0);
        $m = D("Admin/YuyueOrders");
        $yyt = D('Admin/YuyueTime');
        $log_name = 'OrderCancle.log';//log文件路径
        $this->log_result($log_name, '定时任务');
        $time = time() - 5 * 60;
        $bgtime = $time - 3600 * 24 * 30;
        $where = array();
        //查找出10分钟以前的（一个月以内）的所有订单
        $where['createtime'] = array('between', array($bgtime, $time));
        $where['status'] = '0';
        $data = $m->where($where)->select();
        //dump($where);die();
        if (empty($data)) {
            echo 'no order to cancel';
            return;
        }
        foreach ($data as $val) {
            $arr['id'] = $val['id'];
            $arr['status'] = '-1';
            //$tmdata = $yyt->where('id=' . $val['tp_id'])->find();
            $tmdata = $yyt->where('id in (' . $val['tp_id'] . ')')->select();

            $m->startTrans();
            //开始事务

            $re = $m->save($arr);
            if ($re) {
                //加库存
                foreach ($tmdata as $tm) {
                    $arrss1['time_store'] = $tm['time_store'] + 1;
                    $arrss1['id'] = $tm['id'];
                    $res = $yyt->save($arrss1);
                }

                if ($res) {
                    $yyt->commit();
                    $this->log_result($log_name, '订单取消成功：' . $val['order_id']);
                } else {
                    $yyt->rollback();
                    $this->log_result($log_name, '订单取消失败：' . $val['order_id']);
                }

            } else {
                $yyt->rollback();
                $this->log_result($log_name, '订单取消失败：' . $val['order_id']);
            }
            //end

        }
        echo 'end';
    }

    // 打印log
    public function log_result($file, $word)
    {
        $fp = fopen($file, "a");
        flock($fp, LOCK_EX);
        $a = fwrite($fp, "执行日期：" . strftime("%Y-%m-%d-%H:%M:%S", time()) . "\n" . $word . "\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

?>