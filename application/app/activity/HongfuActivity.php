<?php
namespace app\app\activity;

class HongfuActivity extends Activities{
	public function getActivity(){
		$host = \app\library\SettingHelper::get("shuaibo_image_url");
		$special = D("special_model")->getSpecial($this->type);
		if (empty($special)) {
			return ['errcode' => -201,'message' => '该活动不存在'];
		}
		foreach ($special as &$image) {
			if (!empty($image['image']) && !empty($image['params'])) {
				$image['image'] = $host.$image['image'];
				$image['params'] = unserialize($image['params']);
			}
		}
		$goods = D("goods_model")->getHongfuGoods($this->page,60);

		return ['errcode' => 0,'message' => '请求成功','content' => ['special' => $special,'goods' => $goods]];
	}
}