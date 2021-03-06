<?

class ppo_positions_peer extends db_peer_postgre
{
	protected $table_name = 'ppo_position';

	/**
	 * @return ppo_topics_peer
	 */
	public static function instance()
	{
		return parent::instance( 'ppo_positions_peer' );
	}

	public function get_by_group( $id, $sort = array('id DESC') )
	{
		return $this->get_list(array('group_id' => $id), array(), $sort);
	}

	public function has_rated( $id, $user_id )
	{
		return db_key::i()->exists('group_position_rate:' . $id . ':' . $user_id);
	}

	public function rate( $id, $user_id )
	{
		db_key::i()->set('group_position_rate:' . $id . ':' . $user_id, true);
	}

	public function delete_item($id)
	{
		db::exec('DELETE FROM ' . ppo_positions_messages_peer::instance()->get_table_name() . ' WHERE topic_id = :id', array('id' => $id));
		parent::delete_item($id);
	}
}