<?php
class OrderViewModel extends ViewModel {
	public $viewFields=array(
		'Article'=>array('id'=>'gid','price'=>'price','photo'=>'photo','title'=>'title'),
		'YuyueOrders'=>array('id'=>'id','order_id'=>'order_id','name','price','email','note','goods_id','tp_id','gender','place_id','tel','status','state','open_id','createtime','_on'=>'Article.id=YuyueOrders.goods_id'),
		'YuyueTime'=>array('time_point','_on'=>'YuyueTime.id=YuyueOrders.tp_id'),
		'YuyueDate'=>array('yuyue_date','_on'=>'YuyueTime.date_id =YuyueDate.id'),
		'Store'=>array('name'=>'store_name','area'=>'store_area','tel'=>'store_tel','email'=>'store_email','bus'=>'store_bus','_on'=>'Store.id=YuyueOrders.place_id'),
		//'Category'=>array('title'=>'ctitle','_on'=>'Category.id=Article.catid'),
		//'place'=>array('username','name','_on'=>'User.id=Article.created_by'),
	);
}
?>