<?

load::app('modules/admin/controller');

class admin_shop_delete_categories_action extends admin_controller
{
	public function execute()
	{
		load::model('shop/shop_categories');

		shop_categories_peer::instance()->delete_item(request::get_int("id", 0));
		$this->redirect("/admin/shop_categories");
	}
}
