<?
load::app('modules/groups/controller');
class groups_posts_action extends groups_controller
{
	public function execute()
	{
            $group_id=request::get_int('group_id');
            $this->allowed_groups=groups_peer::instance()->get_new();
            if (!in_array($group_id, $this->allowed_groups) && !session::has_credential('admin'))
            {
                    throw new public_exception(t('У вас недостаточно прав'));
                    return;
            }
            
            $this->group = groups_peer::instance()->get_item($group_id);
            if (!$this->group or(!session::has_credential('admin') and $this->group['active']!=1)) $this->redirect('/groups');
            
            client_helper::set_title(t("Мысли").' '.stripslashes(htmlspecialchars($this->group['title'])) . ' | ' . conf::get('project_name'));
            
            if(!$this->user_id = request::get_int('id'))
                $this->user_id = session::get_user_id ();

            load::model('blogs/posts');
            load::model('blogs/comments');
            
            if ( $this->lists =  blogs_posts_peer::instance()->get_by_group($group_id) )
            {
                    //$this->lists = array_slice($posts, 0, 10);
                    load::action_helper('pager', true);
                    $this->pager = pager_helper::get_pager($this->lists, request::get_int('page'), 20);
                    $this->posts = $this->pager->get_list();
            }

        }
}
