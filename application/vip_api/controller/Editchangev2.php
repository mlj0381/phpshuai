<?php
/**
 * Created by PhpStorm.
 * User: sunhandong
 * Date: 2017/7/21
 * Time: 上午11:04
 */
namespace app\vip_api\controller;
class Editchangev2 {
    public function index() {
        $nickname = I('user')."lg";
        $customer = M('customer')->where(['nickname' => $nickname])->find();
        if (empty($customer)) {
            return ['no' => 0, 'info' => '用户不存在'];
        }
        if ($customer['active'] != 1) {
            return ['no' => 0, 'info' => '用户不可用'];
        }
        $amount = round(I('amount'),2);
        if ($amount <= 0) {
            $amount = 0;
        }
        M('customer')->where(['nickname' => $nickname])->setInc('shopping_coin',$amount);

        M('finance')->add([
            'customer_id' => $customer['customer_id'],
            'finance_type_id' => 2,
            'type' => 3,
            'amount' => $amount,
            'date_add' => time(),
            'is_minus' => 2,
            'order_sn' => "",
            'comments' => 'LG转入会员积分',
            'title' => '+'.$amount,
        ]);

        return ['no' => 1, 'info' => 'ok'];
    }
}