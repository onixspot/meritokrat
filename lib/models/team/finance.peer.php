<?

class team_finance_peer extends db_peer_postgre
{
	protected $table_name = 'team_finance';

	/**
	 * @return team_finance_peer
	 */
	public static function instance()
	{
		return parent::instance( 'team_finance_peer' );
	}

	public function get_by_region( $id )
	{
		//return $this->get_list(array('group_id' => $id), array(), $sort);
                return $this->get_list(array('region_id' => $id),array(),array('date DESC'));
	}

	public function delete_item($id)
	{
		db::exec('DELETE FROM ' . team_finance_log_peer::instance()->get_table_name() . ' WHERE finance_id = :id', array('id' => $id));
		parent::delete_item($id);
	}
}