<?php
/**
 * Author: huanglele
 * Date: 2016/4/1
 * Time: 下午 02:00
 * Description:
 */

namespace Admin\Controller;


class OrdersController extends CommonController
{

    /**
     * 订单列表
     */
    public function index(){
        $map = array();
        if($this->role==2){
            $map['aid'] = $this->aid;
        }
        $oid = I('get.oid');
        if($oid){
            $map['oid'] = $oid;
        }
        $this->assign('oid',$oid);

        $status = I('get.status',0,'number_int');
        if($status){
            $map['status'] = $status;
        }
        $this->assign('status',$status);

        $tel = I('get.tel');
        if($tel){
            $map['buy_tel'] = $tel;
        }
        $this->assign('tel',$tel);

        $name = I('get.name');
        if($name){
            $map['buy_name'] = array('like','%'.$name.'%');
        }
        $this->assign('name',$name);
        $map['type'] = 1;

        $M = M('orders');
        $field = 'oid,create_time,status,uid,gid';
        $list = $this->getData($M,$map,'oid desc',$field);
        $uidArr = array('0');
        $gidArr = array('0');
        foreach($list as $v){
            $uidArr[] = $v['uid'];
            $gidArr[] = $v['gid'];
        }

        $uidInfo = M('user')->where(array('uid'=>array('in',$uidArr)))->getField('uid,nickname,headimgurl');
        $goodsInfo = M('goods')->where(array('gid'=>array('in',$gidArr)))->getField('gid,name,status as gstatus');

        $data = array();
        foreach($list as $v){
            $data[] = array_merge($v,$uidInfo[$v['uid']],$goodsInfo[$v['gid']]);
        }

        $this->assign('list',$data);
        $this->assign('OrdersStatus',C('OrdersStatus'));
        $this->assign('GoodsStatus',C('GoodsStatus'));
        $this->display('index');
    }


    /**
     * 订单详情
     */
    public function detail(){
        $id = I('get.id');
        $M = M('orders');
        $info = $M->find($id);
        if(!$info) $this->error('页面不存在',U('index'));
        $user = M('user')->field('nickname')->find($info['uid']);
        if($info['type']==1){
            $goods = M('goods')->field('name as tname')->find($info['gid']);
        }else{
            $goods = M('product')->field('name as tname')->find($info['gid']);
        }

        $this->assign('info',array_merge($user,$goods,$info));
        $this->assign('OrdersStatus',C('OrdersStatus'));
        $this->display('detail');
    }

    /**
     * 处理兑奖
     */
    public function update(){
        if(isset($_POST['submit'])){
            $data = $_POST;
            $data['ex_time'] = time();
            $M = M('orders');
            if($M->save($data)){
                $this->success('处理成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            $this->error('参数错误',U('index'));
        }
    }

    /**
     * 取消订单操作
     */
    public function undo(){
        $this->checkRole(1);
        $oid = I('get.id');
        $Orders = M('orders');
        $info = $Orders->field('oid,gid,uid,aid,status,buy_price,money')->find($oid);
        if($info['status']==1){
            //修改商品信息[库存、销量]，从商家账户扣钱，退款到买家账户

        }else{
            $this->error('订单当前状态不可取消');
        }
    }

    public function delCommon(){
        $this->checkRole(1);
        $oid = I('get.oid');
        $Orders = M('orders');
        $Orders->where(array('oid'=>$oid))->setField('common','   ');
        $this->success('操作成功');
    }

}