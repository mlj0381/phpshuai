<?php
namespace app\app\activity;

//积分半价
class IntegralHalfPriceActivity extends Activities{
	public function getActivity(){
		$host = \app\library\SettingHelper::get("shuaibo_image_url");
		
		$special = D("special_model")->getSpecial($this->type);
		if (empty($special)){
			return ['errcode' => -101 ,'message' => '该活动不存在'];
		}
		foreach ($special as &$images){
			if (!empty($images['image']) && !empty($images['params'])){
				$images['image'] = $host.$images['image'];
				$images['params'] = unserialize($images['params']);
				$images['jump'] = $this->terminal == 1?$images['ios_param']:$images['android_param'];
			}
		}
		//获取精品店铺
		$seller = D("special_model")->getIntergralSeller();
		
		//获取精品优质单品
		$condition = "g.is_delete = 0 and g.on_sale = 1 and g.apply_status = 2 and g.max_integration >= g.shop_price/2";
		$fields = "g.name,g.shop_price,g.max_integration as integral,concat('$host',g.cover) as cover,g.goods_id";
		$goods = D("goods_model")->getGoodsList($condition,$fields,"",$this->page,8);
		
		return ['errcode' => 0, 'message' => '请求成功', 'content' => ['special' => $special, 'seller' => $seller, 'goods' => $goods]];
	}
}