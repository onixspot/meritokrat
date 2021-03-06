<?
load::app('modules/ppo/controller');
class ppo_add_news_action extends ppo_controller
{
	protected $authorized_access = true;

	public function execute()
	{
                if (request::get('text') and request::get('id') and ppo_peer::instance()->is_moderator(request::get_int('id'), session::get_user_id()) )
                {
                        $id = request::get_int('id');
                        load::model('blogs/posts');
                        $clean_text = blogs_posts_peer::instance()->clean_text(stripslashes(trim(request::get('text'))));
                        $this->id = ppo_news_peer::instance()->insert(array(
				'group_id' => $id,
                                'title' => trim(mb_substr(request::get_string('title'),0,250)),
				'created_ts' => time(),
                                'text' => $clean_text
                        ));
                $this->redirect('/ppo/news?id='.$id);
                }
                
	}
}