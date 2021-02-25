<?

load::app('modules/team/controller');

class team_share_user_action extends team_controller
{
    public function execute()
    {
        load::view_helper('tag', true);
        $this->disable_layout();
        $this->q = request::get('q');

        if ($key = request::get('q')) {
            if (strlen($key) > 2) $this->users = user_data_peer::instance()->get_by_name_ppo($key, $where);
        } else
            $this->users = db::get_cols("SELECT id FROM user_auth " . $where);

        if (request::get_int('team_id') > 0 && $this->users) {
            $this->group = team_peer::instance()->get_item(request::get_int('team_id'));
            $ppo_users = team_members_peer::instance()->get_members(request::get_int('team_id'), false, $this->group);
            $this->users = array_intersect($this->users, $ppo_users);
        }

        load::action_helper('pager', true);
        if (count($this->users) > 0) {
            $this->pager = pager_helper::get_pager($this->users, request::get_int('page'), 12);
            $this->users = $this->pager->get_list();
        } else {
            $this->users = array();
        }
    }
}