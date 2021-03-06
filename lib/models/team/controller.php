<?

abstract class team_controller extends frontend_controller
{
	public function init()
	{
		parent::init();

		load::model('team/team');
		load::model('team/members');
		load::model('team/news');
		load::model('team/topics');

		load::view_helper('group');

		load::action_helper('pager', true);
		$this->set_slot('context', 'partials/about');
	}

	public function post_action()
	{
		parent::post_action();

		$this->selected_menu = '/team';
	}
}
