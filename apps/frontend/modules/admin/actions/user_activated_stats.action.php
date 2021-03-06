<?

load::app('modules/admin/controller');

class admin_user_activated_stats_action extends admin_controller
{
	public function execute()
	{
		$list = db::get_rows('
		SELECT count(id) as total, u.activated_ts
		FROM (
		SELECT
		s_u.id,
		to_char(s_u.activated_ts::abstime::timestamp, \'DD/MM/YY\') AS 
		activated_ts
		FROM user_auth s_u WHERE  active is true
		ORDER BY id
		) u
		GROUP BY activated_ts
		ORDER BY max(id) DESC
		LIMIT 30
		');

		$list = array_reverse($list);


		$min = $list[0]['total'];

		$min_date = $list[0]['activated_ts'];
		$min_date = explode('/', $min_date);
		$min_date = $min_date[2] . $min_date[1] . $min_date[0];

		$sql = 'SELECT count(id) as total FROM user_auth WHERE active is true and to_char(activated_ts::abstime::timestamp, \'YYMMDD\')::int < \'' . ((int)$min_date) . "'";
		$total = db::get_scalar($sql);

		foreach ($list as $row) {
			$values[] = $row['total'] + $total;
			$labels[] = $row['activated_ts'];
			$total += $row['total'];
		}

		$values_json = '[' . implode(',', $values) . ']';
		$labels_json = '["' . implode('","', $labels) . '"]';

		?>
		{"title":{"text":"Динаміка активацій користувачів meritokrat.org", "style":"font-size: 12px;
		font-family: Verdana; text-align: center;"},
		"x_axis":{"labels":{"rotate":"vertical","labels":<?= $labels_json ?>}},
		"y_axis":{"min": <?= $min ?>,"max": <?= $total ?>,"steps":<?= ceil(($total - $min) / 15) ?>},
		"bg_colour":"#ffffff", "elements":[{"type":"area_hollow",
		"values":<?= $values_json ?>,
		"dot-size":2, "fill-alpha":0.7}]}
		<?
		exit;
	}
}
