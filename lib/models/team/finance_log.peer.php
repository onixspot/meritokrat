<?

class team_finance_log_peer extends db_peer_postgre
{
	protected $table_name = 'team_finance_log';

	/**
	 * @return team_finance_log_peer
	 */
	public static function instance()
	{
		return parent::instance( 'team_finance_log_peer' );
	}

	public function get_by_finance( $id )
	{
		return $this->get_list(array('finance_id' => $id),array(),array('date ASC'));
	}

}