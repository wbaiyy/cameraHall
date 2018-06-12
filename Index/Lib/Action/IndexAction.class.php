<?php
class IndexAction extends Action {

    
    
    public function __construct(){
        parent::__construct();
        $this->key = 'dcd6a713d49fb4432c57f8e20a88a6b1';
        $this->mch_id = "1342208701";
        $this->appid = 'wxd79f45dc2114c329';
        //网易云信分配的账号，请替换你在管理后台应用下申请的Appkey
        $this->AppKey = 'e573b030f1c51f5892a9d4ac41747423';
        //网易云信分配的账号，请替换你在管理后台应用下申请的appSecret
        $this->AppSecret = '9594ee68d5a5';
        //微信模版消息
        $this->template_id = '89NAdNmYO--O68Ye5WyraEu0zmMc-F1TrgPEpszH5wE';

        if(!$_SESSION['open_id']){
            $_SESSION['open_id'] =$this->openid = $this->get_openid($this->appid,$this->key);
        }else{
            $this->openid = $_SESSION['open_id'];
        }
        //$this->openid = 'os7D9wi5jTzRfe8PApIdL0vAnB0E';

        $openids = $this->openid;

        //var_dump($_GET["_URL_"][1]);exit;

        if($_GET["_URL_"][1]!="scan"){

            $token = S('atoken');

            if(!$token){
                $token =  $this->getToken();

                S('atoken',$token,3600,'File');
            }
            ;
            $gzxx =  S('gzxx');
            if(!$gzxx){
                $res  =$this->getInfo( $token ,$openids);

                if($res['errcode']=="40001"){

                    $token =  $this->getToken();
                    S('atoken',$token,3600,'File');
                    $reUrl ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    Header("Location: $reUrl");

                }

                if($res['errcode']=='40003'){
                    $reUrl ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                    if(strpos($reUrl,'code')){
                        //$reUrl = str_replace('code','nimeicode',$reUrl);
                        $arr = explode('?',$reUrl);
                        Header("Location: $arr[0]");
                        //Header("Location: $reUrl");
                    }
                }
                $gzxx = $res['subscribe'];

                if($gzxx!=0){
                    S('gzxx',$gzxx,3600,'File');
                }
            }

            //var_dump($token);die;
            if($gzxx == 0){
                $this->error('您还未关注我们！', U("Index/scan"));
            }
        }
}
    
    function getToken(){
        $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxd79f45dc2114c329&secret=dcd6a713d49fb4432c57f8e20a88a6b1";
        $access_msg = json_decode(file_get_contents($access_token));
        $token = $access_msg->access_token;  
        //dump($access_msg );die;
        return $token;
    }

    function getInfo($token,$openids){

         $subscribe_msg = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$openids";
         $subscribe = json_decode(file_get_contents($subscribe_msg),true);
         $gzxx1 = $subscribe;
         return  $gzxx1;  
    }

    public function scan(){
            $this->display('scan');
    }



    // 预约首页
    public function yuyue(){
              $anote = D('Admin/anote');
              $where ='published=1';
              $data = $anote->where($where)->find();
              $this->assign('anote',$data);
              $this->display('yuyue');

    }

    //城市选择 
    public function city(){
        $city = D('Admin/city');
        $note = D('Admin/cnote');
        $city1 = $city->where(array('published'=>1))->select();
        $city2 = $city->where(array('published'=>0))->select();

        $where =array('id'=>'12','published'=>'1');
        $data=$note->where($where)->find();
        
        $this->assign('url',U("Index/yuyue"));
        $this->assign('city1',$city1);
        $this->assign('city2',$city2);
        $this->assign('data',$data);
        $this->display('city');
    }
    //门店列表
    public function store(){
        // echo "<pre>";var_dump($_GET['id']);exit;
        $city = D('Admin/city');
        $store = D('Admin/store');
        $city1 = $city->where(array('published'=>1))->select();

        if(empty($_GET['id'])){
            $stores = $store->where(array('published'=>1))->order('lid asc')->select();
            $cityname = "城市";
        }else{
            $stores = $store->where(array('published'=>1,'cityid'=>$_GET['id']))->order('lid asc')->select();
            $city2 = $city->where(array('published'=>1,'id'=>$_GET['id']))->find();
            $cityname = $city2['name'];
        }
// echo "<pre>";var_dump($cityname);exit;
        $num = count($stores);
        $this->assign('city1',$city1);
        $this->assign('stores',$stores);
        $this->assign('cityname',$cityname);
        $this->assign('num',$num);
        $this->display('store');
    }


    //门店详情
    public function orderlist(){
		
        if(!empty($_GET['status'])){
            $this->log_result('data-str.log',"[data]".$_GET['phone'].$_GET['time'].$_GET['store'].$_GET['goods'].$_GET['area'].$_GET['phone'].$_GET['tel']."\n");
            $phone = $_GET['phone'];
            $time = $_GET['time'];
            $store = iconv('GB2312', 'UTF-8', $_GET['store']);
            $goods = iconv('GB2312', 'UTF-8', $_GET['goods']);
            $area = iconv('GB2312', 'UTF-8', $_GET['area']);
            $order = $_GET['id'];
            $tel = $_GET['tel'];
            import('ORG.ServerAPI.ServerAPI');
            $p = new ServerAPI($this->AppKey,$this->AppSecret,'fsockopen'); 
           //发送模板短信
           $re = $p->sendSMSTemplate('3054859',array($phone),array($time,$store,$goods,$area,$tel));

           $data=array(
                'first'=>array('value'=>"您好,感谢您选择了我们【有一佳】您的拍摄已预约成功",'color'=>"#743A3A"),
                'keyword1'=>array('value'=>$time,'color'=>'#0099FF'),
                'keyword2'=>array('value'=>$goods,'color'=>'#0099FF'),
                'keyword3'=>array('value'=>$store."-".$area."-电话:".$tel,'color'=>'#0099FF'),
                'keyword4'=>array('value'=>$order,'color'=>'#0099FF'),
                'remark'=>array('value'=>"请在拍摄前保持头发清洁，并在拍摄前一晚避免过度饮水导致水肿。请提前10分钟到店，切勿迟到，期待您的光临。",'color'=>'#0099FF'),
            );
           $touser = $this->openid;
           $template_id = $this->template_id;
           $this->doSend($touser, $template_id, $data, $topcolor = '#7B68EE');

            if($re['code'] == 200){
                    $this->log_result('ture.log',"[发送成功]".$_GET['phone'].$_GET['goods']."\n");
                }else{
                    $this->log_result('false.log',"[发送失败]".$re['code'].$re['msg'].$phone.'=='.iconv('GB2312', 'UTF-8', $time).'=='.iconv('GB2312', 'UTF-8', $store).'=='.iconv('GB2312', 'UTF-8', $goods).'=='.iconv('GB2312', 'UTF-8', $area).'=='.$tel."\n");
                }
        } else{
            $phone = $_GET['phone'];
            $time = $_GET['time'];
            $store = iconv('GB2312', 'UTF-8', $_GET['store']);
            $goods = iconv('GB2312', 'UTF-8', $_GET['goods']);
            $area = iconv('GB2312', 'UTF-8', $_GET['area']);
            $order = $_GET['id'];
            $tel = $_GET['tel'];

           $data=array(
                'first'=>array('value'=>"您好,感谢您选择了我们【有一佳】您的拍摄已取消成功",'color'=>"#743A3A"),
                'keyword1'=>array('value'=>$time,'color'=>'#0099FF'),
                'keyword2'=>array('value'=>$goods,'color'=>'#0099FF'),
                'keyword3'=>array('value'=>$store,'color'=>'#0099FF'),
                'keyword4'=>array('value'=>$order,'color'=>'#0099FF'),
                'remark'=>array('value'=>"期待您下次光临。",'color'=>'#0099FF'),
            );
           $touser = $this->openid;
           $template_id = $this->template_id;
           //$this->doSend($touser, $template_id, $data, $topcolor = '#7B68EE');
		}
        $order_id = $_GET['id'];
        $order = new OrderViewModel();
        $myorder = $order->where(array('order_id'=>$order_id))->select();
        $weeks =array('日','一','二','三','四','五','六');
        $w = date('w',strtotime($myorder[0]['yuyue_date']));
        $myorder[0]['createtime'] = date('Y-m-d H:i:s',$myorder[0]['createtime']);
        //$myorder[0]['return_fee'] = $myorder[0]['return_fee']/100;
        //var_dump($myorder[0]['return_fee']);die;
        //$myorder[0]['yuyue_date'] = date('Y-m-d H:i',strtotime($myorder[0]['yuyue_date']))."(周$weeks[$w])";
        $this->assign('myorder',$myorder[0]);
        $this->assign('url',U("Index/myorder"));
        $this->display('orderlist');
    }


    //我的订单
    public function myorder(){

        $myorder = array();
        if ($this->openid)  {
            $order = new OrderViewModel();
            $myorder = $order->where(array('YuyueOrders.open_id'=>$this->openid))->select();
        }

        $this->assign('myorder',$myorder);
        $this->assign('url',U("Index/yuyue"));
        $this->display('myorder');
    }
    public function type(){
        
        $id= (int)$_GET['cid'];
        if(!$id){
            $this->error('请选择要拍摄的分店！',U('Index/city'));
        }
        
        $article=D('Admin/Article');
         $where ='article.sectionid=section.id AND article.published=1 AND article.store_id='.$id; 

        //var_dump($where);die;
        $data=$article->table('joys_article article ,joys_section section')->field('article.*,section.title as stitle')->where($where)->order('article.order desc')->select();
        //dump($data);die;
        
        if(empty($data)){
            $this->error('当前产品未开放,敬请期待！',U('Index/yuyue'));
        }
        
        $list1 = array();
        $list2 = array();
        foreach ($data as $value) {
            if(strstr($value['stitle'],'电话')){
                $list2[] =$value;
            }else{
                $list1[] =$value;
            }
        }
        unset($data);
        $this->assign('list1',$list1);
        $this->assign('list2',$list2);
        $this->assign('cid',$id);

        $st = D('Admin/store');
        $ss = $st->where('id='.$id)->find();
        //$article = new Admin\Model\ArticleViewModel();
        //dump($ss);die;
        $this->assign('url',U("Index/selectstore",array('cid'=>$ss['cityid'])));
        
        $this->display('type');
    }
    
    
    //拍摄类型详情页
    public function typelist(){
        //var_dump(c('HTML_PATH'));die;
        $id= (int)$_GET['id'];
        if(!$id){
            $this->error('请选择要拍摄类型！',U('Index/store'));
        }
        //$article=M('ArticleView','Admin');
        $article=D('Admin/Article');
        $section=D('Admin/Section');
        $where =array('id'=>$id,'published'=>'1');
        $data=$article->where($where)->find();
        
        if(empty($data)){
            $this->error('当前产品未开放！',U('Index/store'));
        }
        
        $secdata = $section->where(array('id'=>$data['sectionid']))->find();
        if(strstr($secdata['title'],'电话')){
            $secdata= '0';
        }else{
            $secdata= '1';
        }
        
        $cid = (int)$_GET['cid'];
        $sm = D("Admin/Store");
        $where =array('id'=>$cid,'published'=>1);
        $place = $sm->where($where)->select();
        
        $this->assign('alist',$data);
        $this->assign('cid',$cid);
        $this->assign('place',$place);
        $this->assign('secdata',$secdata);
        $this->assign('url',U("Index/type",array('cid'=>$cid)));
        
        //dump($secdata);die;
        $this->display('typelist');
    }
    
    //选择拍摄日期
    public function selectdate(){
        

        $goods_id= (int)$_GET['gid'];
        $place_id= (int)$_GET['pid'];
        
        $yyd = D('Admin/YuyueDate');
        $yyt = D('Admin/YuyueTime');
        
        $date = date('Y-m-d',time());
        $t = strtotime($date);
        
        $sm = D("Admin/Store");
        $where =array('id'=>$place_id);
        $place = $sm->where($where)->find();
        
        $where ='yyd.id=yyt.date_id AND yyd.yuyue_timestamp >= '.$t.' AND yyd.place_id = '.$place_id  ;
        //dump($place);die;
        $list=$yyd->table('joys_yuyue_date yyd,joys_yuyue_time yyt')->where($where)->field('yyd.*,yyt.time_store')->select();
        

        
        if(empty($list)){
            $this->error('当前门店暂无预约日期，请联系客服人员！',U('Index/typelist',array('id'=>$goods_id,'cid'=>$place['cityid'])));
        }
        
        $count = count($list);
        $weeks =array('日','一','二','三','四','五','六');
        $arr=array();
        foreach ($list as $key=>$value) {
            
            $arr[$value['id']][] = $value;  
            unset($list[$key]);
        }
        
        foreach ($arr as $k=>&$vs) {
            
            //统计天数
            $sum = 0;
            foreach ($vs as $v) {
                $sum +=$v['time_store'];
                $da = $v['yuyue_date'];
                $date_id = $v['id'];
            }
            
            $vs['counts'] =$sum;
            //转换日期和星期
            $days = explode('-',$da);
            $vs['mou'] = $days[1];
            $vs['day'] = $days[2];
            $w = date('w',strtotime($da));
            $vs['week'] = $weeks[$w];
            $vs['id'] = $date_id;
            $vs['gid'] = $goods_id;
            $vs['pid'] = $place_id;
            
        }
        //dump($arr);die;
        $this->assign('url',U("Index/typelist",array('id'=>$goods_id,'cid'=>$place['id'])));
        $this->assign('list',$arr);
        $this->assign('datecount',count($arr));
        $this->display('selectdate');
    }
    //
    public function order(){
        $get = $_GET;
        if(!$get['gid']){
            
            $this->error('请选择产品！',U('Index/city'));
        }
        
        if(!$get['pid']){
            
            $this->error('请选择门店！',U('Index/city'));
        }
        
        if(!$get['tpid']){
            
            $this->error('请选择日期！',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }
        
        $yyt = D('Admin/YuyueTime');
        $yyd = D('Admin/YuyueDate');
        $article=D('Admin/Article');
        
        $time  = $yyt->where('id='.$get['tpid'])->find();
        if(empty($time)|| $time['time_store']<=0){
            $this->error('当前时间已经被预约完了，请重选日期！',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }
        $this->assign('time',substr($time['time_point'],0,5));
    
        $date =  $yyd->where('id='.$time['date_id'])->find();
   
        if(empty($date)){
            
            $this->error('当前日期已经被预约完了，请重选日期！', U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }
        $weeks =array('日','一','二','三','四','五','六');
        $w = date('w',strtotime($date['yuyue_date']));
        $week = $weeks[$w];
        $this->assign('date',$date['yuyue_date']);
        $this->assign('week',$week);
        
        
        $goods = $article->where('id='.$get['gid'])->find();
        if(empty($goods)){
            
            $this->error('当前产品不存在，请重选产品！',U('Index/city'));
        }
        //$goods['real_price'] = round($goods['price']*0.3,2);
        $goods['real_price'] = round($goods['pay_price'],2);
        $this->assign('goods',$goods);
        
        //门店
        //获取门店信息
        $sm = D("Admin/Store");
        $where =array('id'=>(int)$get['pid']);
        $place = $sm->where($where)->find();
        $this->assign('place',$place);
        

        $data = $this->timeJudge($get['gid'], $time['date_id'], $get['tpid'], 2);
        if (0 != $data['code']) {
            $this->error($data['message'], U('Index/selectdate',array('gid' => $get['gid'],'pid' => $get['pid'])));
        }

        $this->assign('gid',$get['gid']);
        $this->assign('pid',$get['pid']);
        $this->assign('tpid',$get['tpid']);
        $this->assign('serviceTime', $goods['time_type'] * 0.5);
        $this->assign('serviceTimes', $data['data']['date']);
        $this->assign('url',U("Index/selecttime",array('gid'=>$get['gid'],'pid'=>$get['pid'],'date_id'=>$time['date_id'])));
        
        $this->display('order');
    }

    public  function checkout(){

        $get = $_POST;
        if(empty($get['gid'])){
            $this->error('请选择产品！',U('Index/store'));
        }
        
        if(empty($get['pid'])){
            
            $this->error('请选择门店！',U('Index/store'));
        }
        
        if(empty($get['tpid'])){
            
            $this->error('请选择日期！',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }

        if(empty($get['name'])){
            $this->error('请填写预约的姓名！',U('Index/order',array('gid'=>$get['gid'],'pid'=>$get['pid'],'tpid'=>$get['tpid'])));
        }
        if(empty($get['phone'])){
            $this->error('请填写预约的手机号码！',U('Index/order',array('gid'=>$get['gid'],'pid'=>$get['pid'],'tpid'=>$get['tpid'])));
        }

        $yyt = D('Admin/YuyueTime');
        $yyd = D('Admin/YuyueDate');
        $yyo = D('Admin/YuyueOrders');
        $article=D('Admin/Article');
        $order =array();
        
        $time  = $yyt->where('id='.$get['tpid'])->find();
        $date  = $yyd->where('id='.$time['date_id'])->find();

        $sm = D("Admin/Store");
        $where =array('id'=>$get['pid']);
        $place = $sm->where($where)->find();
        if(empty($time)|| $time['time_store']<=0){
            $this->error('当前时间已经被预约完了，请重选日期！',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }
        $order['tp_id'] = (int)$get['tpid'];
        
        $goods = $article->where('id='. $get['gid'])->find();
        if(empty($goods)){
            
            $this->error('当前产品不存在，请重选产品！',U('Index/city'));
        }

        $data = $this->timeJudge($get['gid'], $time['date_id'], $get['tpid'], 2);
        if (0 != $data['code']) {
            $this->error($data['msg'], U('Index/city'));
        }
        $tpIds = $data['data']['tpids'];

        $order['tp_id'] = implode(',', $tpIds);
        $order['goods_id'] = $get['gid'];
        //$order['price'] = round($goods['price']*0.3,2);
        $order['price'] = round($goods['price'],2);
        $order['pay_price'] = round($goods['pay_price'],2);
        //门店信息
        $order['place_id'] = $get['pid'];
        
        //顾客信息
        $order['name'] = replaceSpecialChar($get['name']);
        $order['tel'] = replaceSpecialChar($get['phone']);
        $order['gender'] = $get['gender']== "男" ? '0':'1';
        //$order['email'] = replaceSpecialChar($get['email']);
        $order['email'] = $get['email'];
        $order['note'] = replaceSpecialChar($get['intro']);
        $order['open_id'] = $this->openid;
        $order['createtime'] = time();
        //dump($yyo);die;
        //开始事务
        $yyo->startTrans(); 
        $order['order_id'] = $this->gen_id();
    
        $re = $yyo->add($order);
        //dump($order);die;
        
        if($re){
            foreach ($tpIds as $tpid) {
                //扣库存
                $time  = $yyt->where('id=' . (int) $tpid)->find();
                $arr['time_store'] = $time['time_store'] - 1;
                $arr['id'] =  $tpid;
                //加日志
                $log_name = 'time_store.log';//log文件路径
                $log_time_store = $time['time_store'];//扣库存前的总数
                $log_time_store1 = $time['time_store']-1;//扣库存后的总数
                $log_openid = $this->openid;//用户openid
                $log_orderid = $order['order_id'];//订单id
                $res = $yyt->save($arr);
                if($res){
                    $this->log_result($log_name,"【start】:\n"."-----------------------------------"."\n");
                    $this->log_result($log_name,"【扣库存前的总数】:\n".$log_time_store."\n");
                    $this->log_result($log_name,"【扣库存后的总数】:\n".$log_time_store1."\n");
                    $this->log_result($log_name,"【用户openid】:\n".$log_openid."\n");
                    $this->log_result($log_name,"【订单id】:\n".$log_orderid."\n");
                    $this->log_result($log_name,"【end】:\n"."-----------------------------------"."\n");
                }else{
                    $this->log_result($log_name,"【###start】:\n"."-----------------------------------"."\n");
                    $this->log_result($log_name,"【###扣库存前的总数】:\n".$log_time_store."\n");
                    $this->log_result($log_name,"【###保存数据失败】:\n".$res."\n");
                    $this->log_result($log_name,"【###订单id】:\n".$log_orderid."\n");
                    $this->log_result($log_name,"【###end】:\n"."-----------------------------------"."\n");
                    $yyo->rollback();
                    $this->error('订单保存失败1',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
                }
            }

            $yyo->commit();
        }else{
                $this->log_result($log_name,"【@@@start】:\n"."-----------------------------------"."\n");
                $this->log_result($log_name,"【@@@扣库存前的总数】:\n".$log_time_store."\n");
                $this->log_result($log_name,"【@@@订单保存失败】:\n".$re."\n");
                $this->log_result($log_name,"【@@@订单id】:\n".$log_orderid."\n");
                $this->log_result($log_name,"【@@@end】:\n"."-----------------------------------"."\n");
            $yyo->rollback();
            $this->error('订单保存失败2',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }

        //$_SESSION['pay_price']  = $order['price'];
        $this->success('预约成功，请及时支付定金！',U('Index/orderpay',array('orderid'=>$order['order_id'],'name'=>$order['name'],'area'=>$place['area'],'tel'=>$place['tel'],'time'=>$date['yuyue_date'].'-'.$time['time_point'],'phone'=>$order['tel'],'store'=>$place['name'])));
    }
    
    public function orderpay(){
        
        if($_POST){
            if($_POST['pay_price'] !=$_SESSION['pay_price']){
                $this->error('支付金额不符',U('Index/orderpay',array()));
            }
            $goods = replaceSpecialChar($_POST['goods']);
            $name = $_POST['name'];
            $time = $_POST['time'];
            $phone = replaceSpecialChar($_POST['phone']);
            $store = $_POST['store'];
            $area = $_POST['area'];
            $tel = $_POST['tel'];
            
            $this->log_result('orderpay-str.log',"[data]".$phone.'--'.$name.'--'.$time.'--'.$store.'--'.$goods.'--'.$area.'--'.$tel."\n");
            
            import('ORG.WxPay.Wxpay');      
            $jsApi = new JsApi_pub($this->appid,$this->key);
            
            $openid = $this->openid;
            
            $oid = $_POST['order_id'] ;
            $fee = intval($_SESSION['pay_price']*100);
            
            $unifiedOrder = new UnifiedOrder_pub($this->appid,$this->mch_id);
            
            //设置统一支付接口参数
            //设置必填参数
            //appid已填,商户无需重复填写
            //mch_id已填,商户无需重复填写
            //noncestr已填,商户无需重复填写
            //spbill_create_ip已填,商户无需重复填写
            //sign已填,商户无需重复填写
            $unifiedOrder->setParameter("openid","$openid");//用户标识
            $unifiedOrder->setParameter("body","有一佳照相馆预约单微信支付");//商品描述
            //自定义订单号，此处仅作举例
            $timeStamp = time();
            //$out_trade_no = WxPayConf_pub::APPID."$timeStamp";
            $unifiedOrder->setParameter("out_trade_no","$oid");//商户订单号 
            $unifiedOrder->setParameter("total_fee","$fee");//总金额
            $unifiedOrder->setParameter("notify_url",'http://youyijia.angougou.cn'.U('Index/notify'));//通知地址 
            $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
            //非必填参数，商户可根据实际情况选填
            //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
            $unifiedOrder->setParameter("device_info","WEB");//设备号 
            $unifiedOrder->setParameter("attach","$goods");//附加数据 
            //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
            //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
            //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
            //$unifiedOrder->setParameter("openid","XXXX");//用户标识
            //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
            //dump($unifiedOrder);die;
            $prepay = $unifiedOrder->getPrepayId();
            if($prepay['return_code']=='0'){
                $prepay_id = $prepay['prepay_id'];

            }else{
                //$this->error('支付失败',U('Index/payerr',array('flag'=>'1','msg'=>$prepay['return_msg'],'order_id'=>$_POST['order_id'])));
                $this->error('支付失败',U('Index/myorder'));
            }
            
            $jsApi->setPrepayId($prepay_id);

            $jsApiParameters = $jsApi->getParameters();
            
            $this->assign('jsApiParameters',$jsApiParameters);
            $this->assign('order_id',$_POST['order_id']);
            $this->assign('phone',$phone);
            $this->assign('time',$time);
            $this->assign('store',$store);
            $this->assign('name',$name);
            $this->assign('goods',$goods);
            $this->assign('area',$area);
            $this->assign('tel',$tel);
            $this->display('to_orderpay');
            
        }else{
            if(!$_SESSION['pay_price']){
                //$this->error('支付金额错误',U('Index/yuyue'));
            }
            if(!$_GET['orderid']){
                $this->error('订单号为空！',U('Index/yuyue'));
            }
            
            $yyo = D('Admin/YuyueOrders');
            
            $where = array('order_id'=>$_GET['orderid'],'status'=>'0');
            $order = $yyo->where($where)->find();
            
            if(empty($order)){
                $this->error('无效的订单号或已支付的订单号！',U('Index/yuyue'));   
            }
            $_SESSION['pay_price']  = $order['pay_price'];
            $t = time();
            $endt = $order['createtime']+5*60;
            
            $goods = D('Admin/Article')->where('id='.$order['goods_id'])->find();
            
            //直接取消订单
            if($t>$endt){
                $yyt = D('Admin/YuyueTime');
                //$tmdata =$yyt->where('id='.$order['tp_id'])->find();
                $tmdata = $yyt->where('id in (' . $order['tp_id'] . ')')->select();
                
                //开始事务
                $yyt->startTrans(); 
                
                //end
                $re = $yyo->save(array('id'=>$order['id'],'status'=>'-1'));
                if($re){
                    //加库存
                    foreach ($tmdata as $tm) {
                        $arrss1['time_store'] = $tm['time_store']+1;
                        $arrss1['id'] = $tm['id'];
                        $res = $yyt->save($arrss1);
                    }

                    if($res){
                        $yyt->commit();
                    }else{
                        $yyt->rollback();
                    }   
                    
                }else{
                       $yyt->rollback();
                }
                //end
                $this->error('订单已超过支付时间！',U('Index/orderlist',array('id'=>$_GET['orderid'])));
            }
            
            $alltime =$endt-$t;
            $m = (int)($alltime/60);
            $s = $alltime%60;
            
            if($m<10){
                $ms='0'.$m.":";
            }else{
                $ms=$m.":";
            }
            if($s<10){
                $ms.='0'.$s;
            }else{
                $ms.=$s;
            }
            
            $this->assign('m',$m);
            $this->assign('s',$s);
            $this->assign('ms',$ms);
            
            $this->assign('goods',$goods['title']);
            $this->assign('order_id',$_GET['orderid']);
            $this->assign('name',iconv('GB2312', 'UTF-8', $_GET['name']));
            $this->assign('phone',$_GET['phone']);
            $this->assign('time',$_GET['time']);
            $this->assign('store',iconv('GB2312', 'UTF-8', $_GET['store']));
            $this->assign('area',iconv('GB2312', 'UTF-8', $_GET['area']));
            $this->assign('pay_price',$_SESSION['pay_price']);
            $this->assign('tel',$_GET['tel']);
            $this->assign('url',U("Index/orderlist",array('id'=>$_GET['orderid'])));
            $this->display();
        }
        
    }
    function payerr(){
        
        if($_GET['flag']=='0'){
            $this->assign('flag','0');
        }else{
            $this->assign('flag','1');
            $this->assign('info',$_GET["msg"]);
        }
        $this->assign('fee',$_SESSION['pay_price']);
        $this->assign('order_id',$_GET['order_id']);
        
        $this->display('');
    }
    
    public function notify(){
        import('ORG.WxPay.Wxpay');
        //使用通用通知接口
        $notify = new Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
        
        //以log文件形式记录回调信息
        $log_name = 'Wxpay.log';//log文件路径
        $this->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");     
        
        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                $this->log_result($log_name,"【通信出错】:\n".$xml."\n");
            }
            elseif($notify->data["result_code"] == "FAIL"){
                $this->log_result($log_name,"【业务出错】:\n".$xml."\n");
            }
            else{
                $this->log_result($log_name,"【支付成功】:\n".$xml."\n");
                
                $result = $notify->data;
                //商户订单号
                $out_trade_no = $result['out_trade_no'];
                //微信支付订单号
                $trade_no = $result['transaction_id'];
                
                $msg = "订单：".$result['out_trade_no']."成功支付：".($result['total_fee']/100)."元";
                //$state = send_phone_msg('15279105432',$msg);
                
                //修改订单的状态为成功支付
                $yyo = D('Admin/YuyueOrders');
                $order = $yyo->where(array('order_id'=>$out_trade_no))->find();
                $yyo->save(array('id'=>$order['id'],'status'=>'1'));
                //$yyo->whrere(array('order_id'=>$out_trade_no))->save(array('status'=>'1'));
                unset($_SESSION['pay_price']);
            }

        }
        
    }
    function get_openid($appid,$key){
        import('ORG.WxPay.Wxpay');   
        $jsApi = new JsApi_pub($appid,$key);
        //通过code获得openid
        if (!isset($_GET['code']))
        {
            //触发微信返回code码
            $reUrl ='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $url = $jsApi->createOauthUrlForCode($reUrl);
            Header("Location: $url");
        }else
        {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $jsApi->setCode($code);

            $openid = $jsApi->getOpenid();
        }
        $_SESSION['openid'] = $openid;

        return $openid;
    }
    
    public function gen_id()
    {   
        
        $yyo = D('Admin/YuyueOrder');
        
        $i = rand(0,99999);
        do{
            if(99999==$i){
                $i=0;
            }
            $i++;
            $order_id = date('YmdH').str_pad($i,4,'0',STR_PAD_LEFT);
            $row =$yyo->where('order_id ='.$order_id)->find();
        }while($row);
        
        return $order_id;
    }
    
    //选择拍摄日期
   
    //选择拍摄时间
    public function selecttime()
    {
        $get = $_GET;
        if(!$get['gid']){
            
            $this->error('请选择产品！',U('Index/type'));
        }
        
        if(!$get['pid']){
            
            $this->error('请选择门店！',U('Index/type'));
        }
        
        if(!$get['date_id']){
            
            $this->error('请选择日期！',U('Index/selectdate',array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        }
        
        
        $yyd = D('Admin/YuyueDate');
        $yyt = D('Admin/YuyueTime');
        
    
        $t = strtotime($date);
        
        $where ='yyd.id=yyt.date_id AND yyt.date_id= '.$get['date_id'] ;
        
        $arr=$yyd->table('joys_yuyue_date yyd,joys_yuyue_time yyt')->field('yyd.*,yyt.time_store,left(yyt.time_point,5) as time_point,yyt.id as tpid')->where($where)->order('yyt.id asc')->select();
        
        $count = count($list);
        $weeks =array('日','一','二','三','四','五','六');
        
        
        $current_time =  date("H:i",time());  
        $current_time =  strtotime($current_time);
        $flag =false;
        
        foreach ($arr as $k=>&$vs) {
            
            //转换日期和星期
            if(!$flag){
                $date = $vs['yuyue_date'];
                $w = date('w',strtotime($date));
                $week = $weeks[$w];
                $id = $vs['id'];
                $flag = true;
            }
            if(strtotime($vs['time_point'])<= $current_time&&time()>$vs['yuyue_timestamp']){
                $vs['time_store'] = '0';
            }
            
        }
        $article = D('Admin/Article');
        $where = array('id' => (int) $get['gid'], 'published'=>'1');
        $articleData = $article->where($where)->find();

        if (!$articleData) {
            $this->error('请选择产品！',U('Index/type'));
        }

        $timeType = isset($articleData['time_type']) ? $articleData['time_type'] : 1;


        //dump($arr);die;
        //获取门店信息
        $sm = D("Admin/Store");
        $where =array('id'=>(int)$get['pid']);
        $place = $sm->where($where)->find();
        
        $this->assign('date',$date);
        $this->assign('timeType', $timeType);
        $this->assign('date_id', (int) $get['date_id'] );
        $this->assign('week',$week);
        $this->assign('goods_id',$get['gid']);
        $this->assign('place_id',$get['pid']);
        $this->assign('id',$id);
        $this->assign('list',$arr);
        $this->assign('datecount',count($arr));
        $this->assign('place',$place);
        $this->assign('url',U("Index/selectdate",array('gid'=>$get['gid'],'pid'=>$get['pid'])));
        
        $this->display('selecttime');
    }

    //拍摄须知
    public function note(){
        $note = D('Admin/cnote');
        $where =array('id'=>'11','published'=>'1');
        $data=$note->where($where)->find();
        $this->assign('note',$data);
        $this->display('note');
    }
    //选择拍摄门店
    public function selectstore(){

        $cid = (int)$_GET['cid'];
        $sm = D("Admin/Store");
        $where =array('cityid'=>$cid,'published'=>1);
        $place = $sm->where($where)->order('lid asc')->select();
        // return false;
        $this->assign('store',$place);

        $this->assign('url',U("Index/city"));
         
        $this->display('selectstore');
    }

    
    // 打印log
    public function  log_result($file,$word)
    {
        $fp = fopen($file,"a");
        flock($fp, LOCK_EX) ;
        $a = fwrite($fp,"执行日期：".strftime("%Y-%m-%d-%H:%M:%S",time())."\n".$word."\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }







     public function refund(){
        $order_id = $_POST['id'];
        $orders = new OrderViewModel();
        $redata = $orders->where(array('order_id='.$order_id))->find();

        $createtime = $redata['createtime'];//创建时间
        $time = time();
        $yuyuetime = strtotime($redata['yuyue_date']." ".$redata['time_point']);
        $hours = floor(($yuyuetime-$time) / 3600);
        $price = $redata['pay_price']*100;
        $order_id =$redata['order_id'];
        if($redata['status']==1){
        // 不足24小时不退，24小时到72小时之间的退50%，72小时以上的退99%
        if($hours<24){
              //不退款
              //不退款，并且更新状态
            $repay = $orders->where(array('order_id'=>$order_id))->save(array('return_fee'=>$reprice,'status'=>'2'));
            //加日志
            if($repay){
                $log_name = 'refund_24.log';//log文件路径
                $log_openid = $this->openid;//用户openid
                $log_orderid = $this->gen_id();//订单id
                $this->log_result($log_name,"【start】:\n"."-----------------------------------"."\n");
                $this->log_result($log_name,"【退款用户openid】:\n".$log_openid."\n");
                $this->log_result($log_name,"【退款订单】:\n".$log_orderid."\n");
                $this->log_result($log_name,"【订单金额】:\n"."不足24小时无法退款"."\n");
                $this->log_result($log_name,"【end】:\n"."-----------------------------------<br>"."\n");
            }else{
                $log_name = 'refund_24.log';//log文件路径
                $log_openid = $this->openid;//用户openid
                $log_orderid = $this->gen_id();//订单id
                $this->log_result($log_name,"【start】:\n"."-----------------------------------"."\n");
                $this->log_result($log_name,"【退款用户openid】:\n".$log_openid."\n");
                $this->log_result($log_name,"【退款订单】:\n".$log_orderid."\n");
                $this->log_result($log_name,"【订单金额】:\n".$total_fee."\n");
                $this->log_result($log_name,"【退款金额】:\n"."不足24小时无法退款"."\n");
                $this->log_result($log_name,"【end】:\n"."-----------------------------------<br>"."\n");
            }
              $this->ajaxReturn($hours,"距预约时间低于24小时，不予退款",-1);
        }elseif ($hours>=24 && $hours<72) {
            //退款50%   
              $state = '-2';     
              $reprice = round(0.5*$price);
              // $this->ajaxReturn($hours,"距预约时间大于24小时且小于72小时，退50%",-2);
        }else{
            //退款99%
              $state = '-3';
              $reprice = round(0.99*$price);
              // $rorders->save(array('order_id'=>$order_id,'status'=>'-2'));
            // $this->ajaxReturn($hours,"距预约时间大于72小时，退99%",-3);
        }
            import('ORG.WxpayAPI.WxPayApi');   
            $out_trade_no = $order_id;
            $total_fee = $price;
            $refund_fee = $reprice;
            $input = new WxPayRefund();
            $input->SetOut_trade_no($out_trade_no);//商户订单号
            $input->SetTotal_fee($total_fee);//交易总金额
            $input->SetRefund_fee($refund_fee);//退款总金额
            $input->SetOut_refund_no(WxPayConfig::MCHID.$order_id.'2');//退款单号，唯一
            $input->SetOp_user_id(WxPayConfig::MCHID);
            //var_dump(WxPayApi::refund($input));
            $result = WxPayApi::refund($input);
            //$this->ajaxReturn($hours,$redata['status'],-1);           
            //加日志
            if(($result['return_code']!='SUCCESS') || ($result['result_code']!='SUCCESS')){
                $log_name = 'refund_error.log';//log文件路径
                $log_openid = $this->openid;//用户openid
                $log_orderid = $this->gen_id();//订单id
                $this->log_result($log_name,"【start】:\n"."-----------------------------------"."\n");
                $this->log_result($log_name,"【退款失败日志】:\n".$result['err_code_des']."\n");
                $this->log_result($log_name,"【退款用户openid】:\n".$log_openid."\n");
                $this->log_result($log_name,"【退款订单】:\n".$log_orderid."\n");
                $this->log_result($log_name,"【订单金额】:\n".$total_fee."\n");
                $this->log_result($log_name,"【退款金额】:\n".$refund_fee."\n");
                $this->log_result($log_name,"【end】:\n"."-----------------------------------<br>"."\n");
            }else{
                $log_name = 'refund_success.log';//log文件路径
                $log_openid = $this->openid;//用户openid
                $log_orderid = $this->gen_id();//订单id
                $this->log_result($log_name,"【start】:\n"."-----------------------------------"."\n");
                $this->log_result($log_name,"【退款成功日志】:\n".$result['err_code_des']."\n");
                $this->log_result($log_name,"【退款用户openid】:\n".$log_openid."\n");
                $this->log_result($log_name,"【退款订单】:\n".$log_orderid."\n");
                $this->log_result($log_name,"【订单金额】:\n".$total_fee."\n");
                $this->log_result($log_name,"【退款金额】:\n".$refund_fee."\n");
                $this->log_result($log_name,"【end】:\n"."-----------------------------------<br>"."\n");
            }

            if($result['return_code']=='SUCCESS'){
                if($result['result_code']=='SUCCESS'){ 
                    $re = $orders->where(array('order_id'=>$order_id))->save(array('return_fee'=>$reprice,'status'=>'-2'));
                    //var_dump($re);die;
                    $yyt = D('Admin/YuyueTime');
                    //开始事务
                    $yyt->startTrans(); 
                    //$tmdata =$yyt->where('id='.$redata['tp_id'])->find();
                    $tmdata = $yyt->where('id in (' . $redata['tp_id'] . ')')->select();


                    if($re){
                        //加库存
                        foreach ($tmdata as $tm) {
                            $arrss1['time_store'] = $tm['time_store']+1;
                            $arrss1['id'] = $tm['id'];
                            $res = $yyt->save($arrss1);
                        }

                        if($res){
                            $yyt->commit();
                        }else{
                            $yyt->rollback();
                        }   
                    
                    }else{
                           $yyt->rollback();
                    }
                //end   
                    $this->ajaxReturn($hours,"退款成功，退款可能有延时，请留意微信通知",$state);
                }else{
                    $this->ajaxReturn($result['err_code_des'],"退款失败，请联系商家进行退款。",-8);
                }
            }else{
                    $this->ajaxReturn($result['err_code_des'],"退款失败，请联系商家进行退款。",-8);
            }
        }else{

             $this->ajaxReturn($redata['status'],"该订单已完成退款，请勿重新退款",-9);
        }
       
            // var_dump($result);
            // $this->assign('price',$price);
            // $this->assign('reprice',$reprice);
            // $this->assign('re',$result['err_code_des']);
            $this->display();
       
        }



        public function user(){
             $order = new OrderViewModel();
             $user = $order->where(array('YuyueOrders.open_id'=>$this->openid))->select();
             $this->assign('user',$user);
             $this->display('user');

        }

    public function doSend($touser, $template_id, $data, $topcolor = '#7B68EE')
    { 
        $template = array(
            'touser' => $touser,
            'template_id' => $template_id,
            'topcolor' => $topcolor,
            'data' => $data
        );
        $json_template = json_encode($template);
        $token = S('atoken');
        if(!$token){
            $token =  $this->getToken();
                S('atoken',$token,3600,'File');
        } 
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" .$token;
        $param = urldecode($json_template);
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();
        //设置超时
        curl_setopt($ch,CURLOPT_URL, $postUrl);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $dataRes = curl_exec($ch); //运行curl
        if($dataRes){  
            curl_close($ch);  
            if ($dataRes['errcode'] == 0) {
            $this->log_result('sendToweixinTrue.log',"[发送成功]".var_export($dataRes,true).'--'.var_export($data,TRUE).'--'.$error."\n");
            return true;
        } else {
            $this->log_result('sendToweixinFalse.log',"[发送失败]".var_export($dataRes,true).'--'.var_export($data,TRUE).'--'.$error."\n");
            return false;
        }  
        }  
        else {  
            $error = curl_errno($ch);  
            curl_close($ch);  
            $this->log_result('sendToweixinTrue.log',"[发送失败]".var_export($error,true).'--'.var_export($data,true).'--'.$error."\n");
            return false;
        }
	}

    /**
     * 发送post请求
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    function request_post($url = '', $param = '')
    {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }
    /**
     * 发送get请求
     * @param string $url
     * @return bool|mixed
     */
    function request_get($url = '')
    {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 判断时间，选择时间
     *
     */
    public function timeJudge($gid, $date_id, $tpid, $type = 1)
    {
        $result = array();

        $article = D('Admin/Article');
        $where = array('id' => (int) $gid, 'published'=>'1');
        $articleData = $article->where($where)->find();

        if (!$articleData) {
            $result =  array(
                'code' => '-1',
                'data' => array(),
                'msg' => '请选择正确的产品',
            );
            $this->jsonOrArray($result, $type);
        }

        $timeType = isset($articleData['time_type']) ? $articleData['time_type'] : 1;

        $yyd = D('Admin/YuyueDate');
        //$yyt = D('Admin/YuyueTime');


        $where ='yyd.id=yyt.date_id AND yyt.date_id= ' . $date_id ;

        $arr = $yyd->table('joys_yuyue_date yyd,joys_yuyue_time yyt')->field('yyd.*,yyt.time_store,left(yyt.time_point,5) as time_point,yyt.id as tpid')->where($where)->order('yyt.id asc')->select();

        //$arr = array_combine(array_column($arr, 'tpid'), $arr);
        //dump($arr);
        $unitTime = 30 * 60;
        $minTime = 0;
        foreach ($arr as $item) {
            $timePoint = strtotime($item['time_point']);

            if (!$minTime) {
                $minTime = $timePoint;
            }
            if ($minTime && $minTime != $timePoint) {
                $unitTime =  $timePoint - $minTime;
                break;
            }
        }

        $arr = array_filter(array_map(function($item) use ($tpid) {
            $timePoint = strtotime($item['time_point']);
            if ($tpid <= $item['tpid'] && $item['time_store'] > 0) {
                $item['time_point'] = $timePoint;
                return $item;
            }
        }, $arr));

        //dump($arr);
        $tmp = array();
        foreach ($arr as $item) {
            if (!in_array($item['time_point'], $result)) {
                $tmp[$item['tpid']] = $item['time_point'];
            }
        }

        if (empty($tmp[$tpid])) {
            $result =  array(
                'code' => '-1',
                'data' => array(),
                'msg' => '当前时间被其他人选择了',
            );
            $this->jsonOrArray($result, $type);
        }

        $selectTime = $tmp[$tpid];
        $tmpFlip = array_flip($tmp);

        for ($i = 0; $i < $timeType; $i++) {
            $key = $selectTime + $i * $unitTime;

            if (array_key_exists($key, $tmpFlip)) {
                $result['tpids'][] = $tmpFlip[$key];
            } else {
                $result = array(
                    'code' => '-1',
                    'data' => array(),
                    'msg' => "{$articleData['title']},服务时间不满足，请选择其他时间",
                );
                $this->jsonOrArray($result, $type);
            }
        }

        $count = count($result['tpids']);

        $result['date'] = $count == 1 ? date('H:i', $tmp[$result['tpids'][0]]) . '-' . date('H:i', $tmp[$result['tpids'][0]] + $unitTime)
            : date('H:i', $tmp[$result['tpids'][0]]) . '-' . date('H:i', $tmp[$result['tpids'][$count- 1]] + $unitTime);
        $result['productName'] = $articleData['title'];


        $result = array (
            'code' => '0',
            'data' => $result,
            'msg' => 'success',
        );

        return $this->jsonOrArray($result, $type);
    }

    /**
     * 返回json 或者 array
     * @param  array $data
     * @param int $type
     * @return  array|void
     */
    public function jsonOrArray($data, $type = 1)
    {
        if ($type == 1) {
            echo json_encode($data);exit;
        }
        return $data;
    }
}