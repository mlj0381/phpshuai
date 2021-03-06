<?php 
namespace app\web\activities;

class EverydayNewActivity extends Activities{
	public function getActivity(){
		$host = \app\library\SettingHelper::get("shuaibo_image_url");

		$special = D("special_model")->getSpecial($this->type);
		if (empty($special)) {
			return ['errcode' => -201,'message' => '该活动不存在'];
		}
		foreach ($special as &$image) {
			if (!empty($image)) {
				$image['image'] = $host.$image['image'];
				$image['params'] = unserialize($image['params']);
			}
		}
		$goods = D("init_model")->getWebEveryNewGoods($this->page);

		return ['errcode' => 0,'message' => '请求成功','content' => ['special' => $special,'goods' => $goods]];
	}
}