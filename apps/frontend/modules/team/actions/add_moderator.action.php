<?

load::app('modules/ppo/controller');
class ppo_add_moderator_action extends ppo_controller
{
	protected $authorized_access = true;

	public function execute()
	{
		$this->disable_layout();

		$this->group = ppo_peer::instance()->get_item(request::get_int('group_id'));
		if ( ( $this->group['user_id'] != session::get_user_id() ) && !session::has_credential('admin') )
		{
			exit;
		}

		if ( preg_match('/profile-([0-9]+)/', request::get('id'), $matches) )
		{
			$this->moderator_id = $matches[1];
		}
		else
		{
			$this->moderator_id = request::get_int('id');
		}
		
		if ( user_data_peer::instance()->get_item($this->moderator_id) )
		{
			ppo_peer::instance()->add_moderator($this->group['id'], $this->moderator_id);
		}
		else
		{
			exit;
		}
	}
}
