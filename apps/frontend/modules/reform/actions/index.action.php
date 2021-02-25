<?

load::app('modules/reform/controller');

class reform_index_action extends reform_controller
{
	protected $authorized_access = true;

	public function execute()
	{
		if (request::get('bookmark')) {
			load::model('bookmarks/bookmarks');
		}
		$this->cur_type = request::get_int('type');
		$this->cur_ptype = request::get_int('ptype');
		$this->cur_status = request::get_int('status');
		$this->cur_region = request::get_int('region');
		$this->cur_citi = request::get_int('city');
		if (!session::has_credential('admin')) $where = array_merge(array('active' => 1));
		else
			$where = array();
		if (!request::get_int('category')) $_REQUEST['category'] = 3;
		$this->cur_category = request::get_int('category');

		if (request::get_int('user_id') > 0) {
			$this->hot = reform_members_peer::instance()->get_ppo(request::get_int('user_id'));
		} elseif (request::get_int('app')) {
			if (session::has_credential('admin')) {
				$applicants = db::get_cols('SELECT DISTINCT(group_id) FROM reform_applicants');
			} else {
				load::model('reform/applicants');
				$applicants = array();
				$creator = db::get_cols('SELECT id FROM reform WHERE user_id=' . session::get_user_id(), array(), null, true);
				$ppo_artem_negodyai = db::get_cols('SELECT DISTINCT(group_id) FROM reform_applicants');
				foreach ($ppo_artem_negodyai as $id) {
					if (reform_peer::instance()->is_moderator($id, session::get_user_id()) || in_array($id, $creator))
						$applicants = array_merge($applicants, reform_applicants_peer::instance()->get_by_group($id));
				}
			}
			$this->hot = $applicants;
		}

		if (request::get('hot') || request::get('type') || request::get('teritory') || request::get('level') || request::get('category') || request::get_int('status')) {
			$this->hot = reform_peer::instance()->get_hot(
				$this->cur_category,
				$this->cur_ptype,
				$this->cur_status,
				$this->cur_region,
				$this->cur_citi
			);
		}

		$all_regions = geo_peer::instance()->get_regions(1);
		$bind = reform_peer::instance()->hot_where;


		$sql = str_replace("AND city_id = :city_id", "", reform_peer::instance()->hot_sql);
		$sql = str_replace("AND region_id = :region_id", "", $sql);


		foreach ($all_regions as $region_id => $title) {
			$bind['region_id'] = $region_id;
			unset($bind['city_id']);
			$count_users = db::get_scalar('SELECT count(*)
                FROM reform WHERE region_id=:region_id AND
                ' . $sql, $bind);
			$this->az_regions[] = array('id' => $region_id, 'title' => $title, 'count' => $count_users);
			$this->rate_regions[$count_users . ($region_id)] = array('id' => $region_id, 'title' => $title, 'count' => $count_users);
			ksort($this->rate_regions);
		}

		if (request::get_int('region')) {
			$all_regions = geo_peer::instance()->get_cities(request::get_int('region'));
			$bind['region_id'] = request::get_int('region');
			foreach ($all_regions as $region_id => $title) {
				$bind['city_id'] = $region_id;

				$count_users = db::get_scalar('SELECT count(*)
                FROM reform WHERE city_id=:city_id AND region_id=:region_id AND
                ' . $sql, $bind);
				$this->az_cities[] = array('id' => $region_id, 'title' => $title, 'count' => $count_users);
				$this->rate_cities[$count_users . ($region_id)] = array('id' => $region_id, 'title' => $title, 'count' => $count_users);
				ksort($this->rate_cities);
			}
		}

		foreach ($this->hot as $ppoid) {
			$cntarr[$ppoid] = count(reform_members_peer::instance()->get_members($ppoid, false, reform_peer::instance()->get_item($ppoid)));
		}
		$this->cntarr = $cntarr;
		if (is_array($cntarr)) {
			arsort($cntarr);
			foreach ($cntarr as $ak => $a)
				$tlist[] = $ak;
		}
		$this->hot = (array)$tlist;
		$this->count = count($this->hot);


		load::action_helper('pager');
		$this->pager = pager_helper::get_pager($this->hot, request::get_int('page'), 15);
		$this->hot = $this->pager->get_list();
	}
}