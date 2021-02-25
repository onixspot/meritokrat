<?
load::app('modules/photo/controller');
class photo_comment_action extends photo_controller
{
	protected $authorized_access = true;
	public function execute()
	{
		$this->disable_layout();

		load::model('photo/photo_comments');

		if ( $text = trim(request::get('text')) )
		{
			load::action_helper('text', true);
			$text = text_helper::smart_trim($text, 4048);

			if ( !$photo = photo_peer::instance()->get_item(request::get_int('photo_id')) )
			{
				return;
			}

			$data = array(
				'user_id' => session::get_user_id(),
				'text' => $text,
				'created_ts' => time(),
				'photo_id' => $photo['id'],
				'parent_id' => request::get_int('parent_id')
			);

			$this->id = photo_comments_peer::instance()->insert($data);
                        //photo_comments_peer::instance()->rate($this->id, session::get_user_id());

			if ( $parent_id = request::get_int('parent_id') )
			{
				$this->child_id = $this->id;

				$comment = photo_comments_peer::instance()->get_item($parent_id);
				$comment['childs'] .= $this->id . ',';
				photo_comments_peer::instance()->update(array(
					'id' => $parent_id,
					'childs' => $comment['childs']
				));
			}
		}

		load::model('user/user_data');
		load::view_helper('user');
	}
}