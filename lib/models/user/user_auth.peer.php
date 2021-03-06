<?php

/**
 * Class user_auth_peer
 * @method user_auth_peer i()
 */
class user_auth_peer extends db_peer_postgre
{
    const JSON_SERIALIZABLE_FIELDS = ['functions'];

    const TYPE_POLITIC = 1;
    const TYPE_PERSON  = 100;

    const STATUS_TYPE_MAIN        = 'main';
    const STATUS_TYPE_FUNCTIONARY = 'functionary';
    const STATUS_TYPE_POLITICIAN  = 'politician';

    const UNDEFINED           = -1;
    const SYMPATHETIC         = 1;
    const POTENTIAL_SUPPORTER = 3;
    const SUPPORTER           = 5;
    const ACTIVE_SUPPORTER    = 7;
    const ACTIVIST            = 10;
    const CANDIDATE           = 15;
    const PARTY_MEMBER        = 25;

    public static $activate_types = [1, 3, 4, 5, 6, 7];
    public static $register_types = [1, 3, 4, 5, 6, 7];

    protected $table_name = 'user_auth';

    public static function get_type($id)
    {
        $list = self::get_types();
        if ($id > 0) {
            return $list[$id];
        } else {
            return '';
        }
    }

    public static function get_types()
    {
        return [
            1  => t('Соратник'),
            /*2 => t('Эксперт'),
            3 => t('Политолог'),*/
            4  => t('Активист'),
            5  => t('Сторонник'),
            7  => t('Наблюдатель'),
            8  => t('Функционер'),
            10 => t('Глава Оргкомитета'),
            0  => t('Без статуса'),
        ];
    }

    public static function get_type_s($id)
    {
        $list = self::get_typess();

        return $list[$id];
    }

    public static function get_typess()
    {
        return [
            1 => t('Соратники'),
            /*2 => t('Эксперт'),
            3 => t('Политолог'),*/
            4 => t('Активисты'),
            5 => t('Сторонники'),
            7 => t('Наблюдатели'),
            8 => t('Функционеры'),
        ];
    }

    public static function get_user_function($functionId)
    {
        $functions = self::get_user_functions();
        if (!isset($functions[$functionId])) {
            return;
        }

        return $functions[$functionId];
    }

    public static function get_user_functions()
    {
        return [
            1  => t('Эксперт').' '.t('н.у.'),
            3  => t('Эксперт').' '.t('р.у.'),
            5  => t('Эксперт').' '.t('м.у.'),
            10 => t('ЛОМ').' '.t('н.у.'),
            13 => t('ЛОМ').' '.t('р.у.'),
            15 => t('ЛОМ').' '.t('м.у.'),
            20 => t('Партнер').' '.t('н.у.'),
            23 => t('Партнер').' '.t('р.у.'),
            25 => t('Партнер').' '.t('м.у.'),
        ];
    }

    public static function get_user_positions()
    {
        return [
            1  => t('Координатор сектора'),
            3  => t('Координатор куста'),
            10 => t('Лидер п.к.'),
            15 => t('Агитатор'),
        ];
    }

    public static function get_status($id, $ban = 0)
    {
        if ($ban > 0) {
            $id = $ban;
        }
        $list = self::get_statuses();
        if ($id > 0) {
            return $list[$id];
        } else {
            return '';
        }
    }

    public static function get_statuses($statusType = self::STATUS_TYPE_MAIN, $restrictedMode = false)
    {
        switch ($statusType) {
            case self::STATUS_TYPE_MAIN:
                $map = [
                    self::POTENTIAL_SUPPORTER => t('Потенциальный сторонник'),
                    self::UNDEFINED           => t('Житель'),
                    self::SYMPATHETIC         => t('Симпатик'),
                    self::SUPPORTER           => t('Сторонник'),
                    self::ACTIVE_SUPPORTER    => t('Активний сторонник'),
                    self::ACTIVIST            => t('Активист'),
                    self::CANDIDATE           => t('Кандидат в члены партии'),
                    self::PARTY_MEMBER        => t('Член партии'),
                ];

                if (true === $restrictedMode) {
                    $map = array_filter(
                        $map,
                        static function ($key) {
                            return in_array(
                                $key,
                                [
                                    self::POTENTIAL_SUPPORTER,
                                    self::SUPPORTER,
                                    self::ACTIVIST,
                                    self::CANDIDATE,
                                ],
                                true
                            );
                        },
                        ARRAY_FILTER_USE_KEY
                    );
                }

                return $map;

            case self::STATUS_TYPE_FUNCTIONARY:
                return [
                    15 => t('Функционер').' '.t('н.у.'),
                    17 => t('Функционер').' '.t('р.у.'),
                    19 => t('Функционер').' '.t('м.у.'),
                ];

            case self::STATUS_TYPE_POLITICIAN:
                return [
                    25 => t('Политик').' '.t('н.у.'),
                    27 => t('Политик').' '.t('р.у.'),
                    29 => t('Политик').' '.t('м.у.'),
                ];

            default:
                return [];
        }
    }

    public static function getStatus($value, $type = self::STATUS_TYPE_MAIN)
    {
        $collection = self::get_statuses($type);

        if (!array_key_exists($value, $collection)) {
            return null;
        }

        return $collection[$value];
    }

    public static function get_status_s($id)
    {
        $list = self::get_statucess();

        return $list[$id];
    }

    public static function get_statucess()
    {
        return self::get_statuses();
    }

    public static function get_from($id)
    {
        $list = self::get_froms();

        return $list[$id];
    }

    public static function get_froms()
    {
        return [
            1 => t('shevchenko.ua'),
            2 => t('novasystema.shevchenko.ua'),
            3 => t('shevchenko.ua/feedback'),
        ];
    }

    public static function get_invited_by_id($id, $hide_inviter = false, $active = false)
    {
        $sql        = <<<SQL
select id, active, email, last_invite
from user_auth
where invited_by = :invited_by
order by last_invite desc
SQL;
        $parameters = [
            'invited_by' => $id,
        ];

        return db::get_rows($sql, $parameters);
    }

    public static function getFilteredInvitationsOfUser($id, $filter = [])
    {
        $extraSql   = '';
        $parameters = [
            'invited_by' => $id,
        ];


        if (array_key_exists('status', $filter)) {
            $extraSql             .= 'and ua.status = :status';
            $parameters['status'] = $filter['status'];
        }

        $sql = <<<SQL
select ua.id, ua.active, ua.email, ua.last_invite
from user_auth ua
where ua.status not in (-1, 1, 3) and ua.invited_by = :invited_by {$extraSql}
order by ua.last_invite desc
SQL;


        return db::get_rows($sql, $parameters);
    }

    public static function get_rights($user_id = 0, $minimal = 1)
    {
        if (!$minimal) {
            return true;
        }
        if (!$user_id) {
            return false;
        }
        $user_auth = self::instance()->get_item($user_id);
        $user_data = user_data_peer::instance()->get_item($user_auth['id']);
        if ($user_auth && $user_data) {
            if (in_array(
                $user_data['region_id'],
                user_desktop_peer::instance()->is_regional_coordinator($user_id, true)
            )) {
                return true;
            }
            if (in_array($user_data['city_id'], user_desktop_peer::instance()->is_raion_coordinator($user_id, true))) {
                return true;
            }
        }
        if (session::has_credential('admin')) {
            return true;
        }
        $ustatus = db::get_scalar('SELECT status FROM user_auth WHERE id=:uid', ['uid' => $user_id]);
        if ($ustatus === false && $minimal === 0) {
            return true;
        }

        return ($ustatus >= $minimal);
    }

    /**
     * @param string $peer
     * @return user_auth_peer
     */
    public static function instance($peer = 'user_auth_peer')
    {
        return parent::instance($peer);
    }

    public static function get_target($id)
    {
        $list = self::get_targets();

        return $list[$id];
    }

    public static function get_targets()
    {
        return [
            1  => t('Студент'),
            2  => t('Учитель'),
            3  => t('Преподаватель'),
            4  => t('Ученый'),
            5  => t('Врач'),
            6  => t('Другой медицинский работник'),
            7  => t('Работник органов местного самоуправления'),
            8  => t('На государственной службе'),
            9  => t('На государственной выборной должности'),
            10 => t('Пенсионер'),
            11 => t('Военный'),
            12 => t('Военный пенсионер'),
            13 => t('Крестьянин'),
            14 => t('Рабочий'),
            15 => t('Работник сферы услуг'),
            16 => t('Профессионал'),
            17 => t('Топ-менеджер'),
            18 => t('Руководитель среднего звена'),
            19 => t('Офисный работник'),
            20 => t('Предприниматель'),
            21 => t('Журналист'),
            22 => t('Редактор СМИ'),
            23 => t('Ведущий на ТВ'),
            24 => t('Эксперт'),
        ];
    }

    /**
     * @param $functionId
     *
     * @return null|string
     */
    public static function get_function($functionId)
    {
        $functions = self::get_functions();
        if (!isset($functions[$functionId])) {
            return null;
        }

        return $functions[$functionId];
    }

    public static function get_functions($ppo = true)
    {
        $list = [
            1 => t('Член Политсовета'),
            2 => t('Член ЦКРК'),
            5 => t('Координатор развития региона'),
            6 => t('Координатор развития района'),
        ];
        //9 => t('Обл. координатор сбора подписей'),
        //10 => t('Рай. координатор сбора подписей'),
        if ($ppo) {
            $list[113] = t('Глава РПО');
            $list[112] = t('Глава МПО');
            $list[111] = t('Глава ППО');
            $list[123] = t('Секретарь РПО');
            $list[122] = t('Секретарь МПО');
            $list[121] = t('Секретарь ППО');
        }
        $list[14] = t('Представитель в ВУЗах');
        $list[18] = t('Логистический координатор');
        $list[22] = t('Работник Секретариата');
        $list[23] = t('Член Президии Политического Совета');
        $list[24] = t('Член Редколлегии');

        return $list;
    }

    public static function get_segment($id)
    {
        $list = self::get_segments();

        return $list[$id];
    }

    public static function get_segments()
    {
        $cache_key = 'all_segments';
        if (!mem_cache::i()->exists($cache_key)) {
            $rows = db::get_rows('SELECT * FROM user_segments ORDER BY id ASC');
            foreach ($rows as $row) {
                $segments[$row['id']] = $row['name_'.session::get('language')];
            }
            mem_cache::i()->set($cache_key, $segments);
        } else {
            $segments = mem_cache::i()->get($cache_key);
        };

        return $segments;
        /*return array(
                1=>t('Благотворительность'),
                2=>t('Строительство / Архитектура / Дизайн'),
                3=>t('Власть'),
                4=>t('Общественная деятельность'),
                5=>t('Общественное питание'),
                6=>t('Госслужба'),
                7=>t('Экология'),
                8=>t('Вооруженные силы'),
                9=>t('Связь'),
                10=>t('СМИ'),
                11=>t('Информационные технологии'),
                12=>t('Кадры (HR)'),
                13=>t('Культура / Искусство'),
                14=>t('Медицина'),
                15=>t('Местное самоуправление'),
                16=>t('Мода / Одежда'),
                17=>t('Недвижимость'),
                18=>t('Образование / Наука'),
                19=>t('Полиграфия'),
                20=>t('Политика'),
                21=>t('Юриспруденция'),
                22=>t('Правоохранительные органы'),
                23=>t('Реклама / PR / Маркетинг'),
                24=>t('Сельское хозяйство'),
                25=>t('Спорт'),
                26=>t('Транспорт'),
                27=>t('Туризм'),
                28=>t('Финансы / инвестиции / страхование'),
                29=>t('Производство')

                );*/
    }

    public static function get_hidden_type($id = 1, $type = 0)
    {
        $list = self::get_hidden_types();

        /*if (session::has_credential('admin') && !in_array($type,array(1,8,0))) return ", *".$list[$id];
                else*/

        return '';
    }

    public static function get_hidden_types()
    {
        return [
            1 => t('Новичок'),
            //2 => t('Эксперт'),
            3 => t('Потенциальный активист'),
            4 => t('Активист'),
            5 => t('Потенциальный лидер'),
            6 => t('Лидер'),
        ];
    }

    public static function get_hidden_type_s($id = 1, $type = 0)
    {
        $list = self::get_hidden_typess();
        if (session::has_credential('admin') && !in_array($type, [1, 8, 0])) {
            return ', *'.$list[$id];
        } else {
            return '';
        }
    }

    public static function get_hidden_typess()
    {
        return [
            1 => t('Новички'),
            //2 => t('Эксперт'),
            3 => t('Потенциальные активисты'),
            4 => t('Активисты'),
            5 => t('Потенциальные лидеры'),
            6 => t('Лидеры'),
        ];
    }

    public static function compare_by_debt($a, $b)
    {
        if ($a['debt'] == $b['debt']) {
            return 0;
        }

        return ($a['debt'] < $b['debt']) ? -1 : 1;
    }

    public static function get_expert_type($id)
    {
        $list = self::get_expert_types();

        return $list[$id];
    }

    public function get_expert_types()
    {
        return [//''=>'&mdash;',
                99 => 'Будь яка сфера',
                1  => 'Державного управління',
                2  => 'Державних фінансів',
                3  => 'Управління державним майном',
                4  => 'Регіональної політики',
                5  => 'Місцевого самоврядування',
                6  => 'Міжнародних відносин та дипломатії',
                7  => 'Міжетнічних стосунків',
                8  => 'Національної безпеки',
                9  => 'Збройних сил, армії',
                10 => 'Конституційного права',
                11 => 'Виборчого права',
                12 => 'Права',
                13 => 'Судової системи',
                14 => 'Правоохоронної діяльності',
                15 => 'Економіки',
                16 => 'Підприємництва ',
                17 => 'Податкової системи',
                18 => 'Фінансів та інвестицій',
                19 => 'Страхування',
                20 => 'Банківської справи',
                21 => 'Інновацій та інвестицій у розвиток',
                22 => 'Інформаційних технологій',
                23 => 'Енергетики',
                24 => 'ЖКГ',
                25 => 'Транспортної системи',
                26 => 'Зв’язку ',
                27 => 'Містобудування та архітектури',
                28 => 'Будівництва',
                29 => 'Легкої промисловості',
                30 => 'Важкої промисловості',
                31 => 'Сільського господарства',
                32 => 'Розвитку села',
                33 => 'Земельних питань',
                34 => 'Управління природними ресурсами',
                35 => 'Екології',
                36 => 'Здоров’я та рекреації',
                37 => 'Безпеки продуктів та товарів',
                38 => 'Медицини та фармакології',
                39 => 'Молодіжної політики',
                40 => 'Сім’ї, материнства, дитинства',
                41 => 'Соціального забезпечення ',
                42 => 'Пенсійного забезпечення',
                43 => 'ЗМІ',
                44 => 'Науки',
                45 => 'Освіти',
                46 => 'Спорту',
                47 => 'Туризму ',
                48 => 'Культури',
                49 => 'Суспільної моралі',
                50 => 'Релігії ',
                51 => 'Історії',
                52 => 'Громадянського суспільства',
        ];
    }

    public function insert(
        $email,
        $password,
        $status = 1,
        $active = false,
        $shevchenko = 0,
        $identification = 0,
        $from = 0,
        $recomended_by = 0,
        $invited_by = 0,
        $offline = 0,
        $suslik = 0,
        $activated_ts = 0,
        $gplus = false,
        $fb = false
    ) {
        $data = [
            'email'          => $email,
            'activated_ts'   => $activated_ts,
            'password'       => md5($password),
            'security_code'  => $this->generate_security_code(),
            'active'         => $active,
            'shevchenko'     => $shevchenko,
            'status'         => $status,
            'created_ts'     => time(),
            'last_invite'    => time(),
            'ip'             => $_SERVER['REMOTE_ADDR'],
            'identification' => $identification,
            'from'           => $from,
            'recomended_by'  => $recomended_by,
            'invited_by'     => $invited_by,
            'offline'        => $offline,
            'suslik'         => $suslik,
            'gplus'          => $gplus,
            'fb'             => $fb,
        ];

        $return = parent::insert($data);

        $sql = sprintf('select id from user_auth where id != %s and email ilike \'%s%%\' limit 1', $return, $email);
        if ($email && $duplicant = db::get_scalar($sql)) {
            load::action_helper('user_email', false);
            foreach (self::get_admins() as $receiver) {
                user_email_helper::send(
                    $receiver,
                    session::get_user_id(),
                    [
                        'subject' => 'Повторна реєстрація у мережі з е-мейлом '.$email,
                        'body'    => 'Користувач https://meritokrat.org/profile-'.$return.' з е-мейлом '.$email.'  повторно зареєструвався у мережі. Видалений профіль учасника https://meritokrat.org/profile-'.$duplicant,
                    ],
                    true
                );
            }
        }

        return $return;
    }

    public function generate_security_code()
    {
        return md5(microtime(true)).md5(rand(100, 999));
    }

    public static function get_admins()
    {
        return db::get_cols("SELECT id FROM user_auth WHERE credentials LIKE '%admin%'");
    }

    public function create($context)
    {
        $context = array_merge(
            $context,
            [
                'password'      => md5($context['password']),
                'activated_ts'  => $context['activated_ts'] ?: 0,
                'security_code' => $this->generate_security_code(),
                'created_ts'    => time(),
                'last_invite'   => time(),
                'ip'            => $_SERVER['REMOTE_ADDR'],
                'gplus'         => $context['gplus'] ?: false,
                'fb'            => $context['fb'] ?: false,
            ]
        );

        $return = parent::insert($context);

        $sql = sprintf('select id from user_auth where id != %s and email ilike \'%s%%\' limit 1', $return, $email);
        if ($email && $duplicant = db::get_scalar($sql)) {
            load::action_helper('user_email', false);
            foreach (self::get_admins() as $receiver) {
                user_email_helper::send(
                    $receiver,
                    session::get_user_id(),
                    [
                        'subject' => 'Повторна реєстрація у мережі з е-мейлом '.$email,
                        'body'    => 'Користувач https://meritokrat.org/profile-'.$return.' з е-мейлом '.$email.'  повторно зареєструвався у мережі. Видалений профіль учасника https://meritokrat.org/profile-'.$duplicant,
                    ],
                    true
                );
            }
        }

        return $return;
    }

    public function get_by_email($value, $del = true)
    {
        if ($del) {
            $sqladd = ' AND del = 0 ';
        }

        return db::get_row(
            'SELECT * FROM '.$this->table_name.' WHERE email = :value '.$sqladd.' LIMIT 1',
            ['value' => strtolower($value)]
        );
    }

    /*
public function get_by_type( $type, $order = null )
{
    if ( !$order ) $order = 'u.rate DESC';

    $sql = '
        SELECT user_id
        FROM ' . $this->table_name . ' a
        JOIN ' . user_data_peer::instance()->get_table_name() . ' u ON (u.user_id = a.id)
        WHERE type = :type AND active = true
        ORDER BY ' . $order . '
        LIMIT 500
    ';

    return db::get_cols($sql, array('type' => $type), $this->connection_name);
}
    */

    public function get_by_security_code($value)
    {
        return db::get_row(
            'SELECT * FROM '.$this->table_name.' WHERE security_code = :value LIMIT 1',
            ['value' => $value]
        );
    }

    public function activate($user_id)
    {
        $activated_user = $this->get_item($user_id);
        load::model('user/user_data');
        load::action_helper('user_email', false);
        $activated_user_data = user_data_peer::instance()->get_item($user_id);
        $activated_user['recomended_by'] ? $inviter_id = $activated_user['recomended_by'] : $inviter_id = $activated_user['invited_by'];
        $this->update(
            [
                'id'            => $user_id,
                'active'        => 1,
                'offline'       => 0,
                'activated_ts'  => time(),
                'security_code' => $this->generate_security_code(),
            ]
        );
        $options = ['%name%' => $activated_user_data['first_name'].' '.$activated_user_data['last_name']];
        user_email_helper::send_sys('sign_up', $user_id, false, $options);

        if (is_null($activated_user_data['phone']) || $activated_user_data['phone'] == '') {
            user_email_helper::send_sys('registration_phone', $user_id, false, $options);
        }
    }

    public function regenerate_security_code($id)
    {
        $this->update(
            [
                'id'            => $id,
                'security_code' => $this->generate_security_code(),
            ]
        );
    }

    public function get_by_type($type = 0, $order = null)
    {
        if ($type >= 0) {
            $stype     = 'type = :type';
            $where_arr = ['type' => $type];
        } else {
            $stype     = '1=1';
            $where_arr = [];
        }
        if (!$order) {
            $order = 'u.rate DESC';
        }

        $sql = '
			SELECT u.user_id
			FROM '.$this->table_name.' a
			JOIN '.user_data_peer::instance()->get_table_name().' u ON (u.user_id = a.id)
			JOIN '.user_sessions_peer::instance()->get_table_name().' s ON (s.user_id = a.id)
			WHERE '.$stype.' AND active = TRUE
			ORDER BY '.$order.'
			LIMIT 500
		';

        //die("<!--".$sql."-->");
        return db::get_cols($sql, $where_arr, $this->connection_name);
    }

    public function get_by_status($status = 0, $order = null, $limit = 0, $chronology = false)
    {
        if (session::has_credential('admin')) {
            $active         = '(active = true OR active = false OR suslik = 1)';
            $activeInjoined = '(a.active = true OR a.active = false OR a.suslik = 1)';
        } else {
            $active         = 'active = true';
            $activeInjoined = 'a.active = true';
        }

        if ($status == -1) {
            if (request::get('activate')) {
                return db::get_cols(
                    'SELECT id FROM '.$this->table_name.' WHERE activated_ts IS NOT NULL AND del=0 AND '.$active.' ORDER BY activated_ts DESC',
                    [],
                    $this->connection_name
                );
            } else {
                if ($chronology == 'old_to_recent') {
                    $order = 'ORDER BY s.visit_ts DESC';
                } else {
                    if ($chronology == 'recent_to_old') {
                        $order = 'ORDER BY s.visit_ts ASC';
                    } else {
                        if ($chronology == 'none' or !$chronology) {
                            $order = 'AND a.priority > 0 ORDER BY a.priority ASC';
                        }
                    }
                }

                $sessusers = db::get_cols(
                    'SELECT a.id FROM '.$this->table_name.' a, '.user_sessions_peer::instance()->get_table_name(
                    ).' s WHERE s.user_id=a.id AND a.del=0 AND '.$activeInjoined.' '.$order,
                    [],
                    $this->connection_name
                );

                $allusers = db::get_cols(
                    'SELECT id FROM '.$this->table_name.' WHERE del=0 AND '.$active,
                    [],
                    $this->connection_name
                );

                $user = user_auth_peer::instance()->get_item(session::get_user_id());

                if (
                    ($user['status'] < 10)
                    || (!$user)
                ) {
                    return $sessusers;
                } else {
                    return array_merge($sessusers, array_diff($allusers, $sessusers));
                }
            }
        } else {
            if ($status >= 0) {
                $stype     = '(a.status = :status OR a.ban = :ban) AND a.del=0 AND a.active=true';
                $where_arr = ['status' => $status, 'ban' => $status];
            } else {
                $stype     = '1=1';
                $where_arr = [];
            }
        }


        $sql = '
			SELECT a.id
			FROM '.$this->table_name.' a, '.user_data_peer::instance()->get_table_name().' u
			WHERE '.$stype.' AND a.id=u.user_id
			ORDER BY u.rate DESC';

        if ($limit) {
            $sql .= ' LIMIT '.$limit;
        }
        //' . ($status==0 ? '' : 'LEFT ') .
        //if (session::get_user_id()==29) die("<!--".$sql."-->");
        return db::get_cols($sql, $where_arr, $this->connection_name);
    }

    public function analyze_debt(&$list, $sortByDebt = false)
    {
        $r = [];

        foreach ($list as $id) {
            $user_membership = user_membership_peer::instance()->get_user($id);
            $user_payments   = [];
            foreach (user_payments_peer::instance()->get_user($id) as $user_payment_id) {
                $user_payment = user_payments_peer::instance()->get_item($user_payment_id);

                if ($user_payment['type'] != 2) {
                    continue;
                }

                $user_payments[] = $user_payment;
            }

            if (date('j', $user_membership['invdate']) <= 15) {
                $date = mktime(
                    0,
                    0,
                    0,
                    date('n', $user_membership['invdate']),
                    1,
                    date('Y', $user_membership['invdate'])
                );
            } else {
                $date = $this->_nextmonth($user_membership['invdate']);
            }

            $curdate = mktime(0, 0, 0, date('n'), 1, date('Y'));

            $debt = 0;
            for ($i = $date; $i <= $curdate; $i = $this->_nextmonth($i)) {
                $approve = 0;
                foreach ($user_payments as $user_payment) {
                    if ($user_payment['period'] == $i) {
                        $approve = $user_payment['approve'];
                    }
                }

                if ($approve != 2) {
                    $debt++;
                }
            }

            $r[$id] = [
                'debt' => $debt,
            ];
        }

        if ($sortByDebt) {
            uasort($r, ['user_auth_peer', 'compare_by_debt']);
            $list = [];
            foreach ($r as $id => $val) {
                $list[] = $id;
            }
        }

        return $r;
    }

    private function _nextmonth($data)
    {
        $next = date('n', $data) + 1;
        $year = date('Y', $data);

        if ($next > 12) {
            $next = 1;
            $year += 1;
        }

        return mktime(0, 0, 0, $next, 1, $year);
    }

    public function get_reestr($region = false, $city = false, $ppo = false, $invdate = 0)
    {
        if ($region && is_array($region) && count($region) > 0) {
            $sqladd = ' AND a.id IN (SELECT user_id FROM user_data WHERE region_id IN ('.implode(',', $region).')) ';
        } else {
            if ($city && is_array($city) && count($city) > 0) {
                $sqladd = ' AND a.id IN (SELECT user_id FROM user_data WHERE city_id IN ('.implode(',', $city).')) ';
            } else {
                if ($ppo && is_array($ppo) && count($ppo) > 0) {
                    $sqladd = ' AND a.id IN (SELECT user_id FROM ppo_members WHERE group_id IN ('.implode(
                            ',',
                            $ppo
                        ).')) ';
                }
            }
        }

        if (request::get_int('to')) {
            $desc = ' desc';
        } else {
            $desc = ' asc';
        }

        if (request::get('submit') && request::get_string('req')) {
            $where = " AND u.last_name ILIKE '".addslashes(strip_tags(request::get_string('req')))."%'";
        }

        if (request::get_string('order') == 'name') {
            $order = 'ORDER BY u.last_name'.$desc;
        } else {
            if (request::get_string('order') == 'region') {
                $order = 'ORDER BY u.region_id'.$desc;
            } else {
                if (request::get_string('order') == 'payments' && request::get_int('type')) {
                    $order = 'ORDER BY SUM(p.summ)'.$desc;
                    $where = 'AND p.type = '.request::get_int('type');
                } else {
                    if (request::get_string('order') == 'rishenna') {
                        $order = 'ORDER BY invdate'.$desc.', invnumber'.$desc;
                    } else {
                        if (request::get_string('order') == 'number') {
                            $order = 'ORDER BY kvnumber'.$desc;
                        } else {
                            if (request::get_string('order') == 'dolg') {
                                $order = 'ORDER BY debt'.$desc;
                            }
                        }
                    }
                }
            }
        }

        if (!$where && !$order) {
            $order             = 'ORDER BY invdate DESC, invnumber DESC';
            $_REQUEST['order'] = 'rishenna';
            $_REQUEST['to']    = '1';
        }
        if ($invdate > 0) {
            $where .= ' AND date>'.$invdate.' ';
        }
        if (in_array(request::get_string('order'), ['rishenna', 'number', 'dolg'])) {
            $sql    = '
                            SELECT a.id
                            FROM '.$this->table_name.' a
                            JOIN membership m ON (m.user_id = a.id)
                            WHERE (a.status = 20 OR a.ban=20) AND a.del = 0 '.$where.$sqladd.$order.'
                            ';
            $query1 = db::get_cols($sql);
            if ($invdate > 0) {
                return $query1;
            }
            $sql    = '
                            SELECT a.id
                            FROM user_auth a
                            WHERE (a.status = 20 OR a.ban=20) AND a.del = 0 '.$sqladd.'
                            ';
            $query2 = db::get_cols($sql);

            return $this->merge_results($query1, $query2);
        } else {
            if (in_array(request::get_string('order'), ['payments'])) {
                $sql    = '
                            SELECT a.id 
                            FROM user_auth a 
                            LEFT JOIN user_payments p ON p.user_id=a.id 
                            WHERE (a.status = 20 OR a.ban=20) AND a.del = 0 AND p.approve = 2 AND p.del = 0 '.$sqladd.$where.'
                            GROUP BY a.id '.$order.'
                            ';
                $query1 = db::get_cols($sql);
                $sql    = '
                            SELECT a.id
                            FROM user_auth a
                            WHERE (a.status = 20 OR a.ban=20) '.$sqladd.'
                            ';
                $query2 = db::get_cols($sql);

                return $this->merge_results($query1, $query2);
            } else {
                $sql = '
                            SELECT a.id
                            FROM '.$this->table_name.' a
                            JOIN '.user_data_peer::instance()->get_table_name().' u ON (u.user_id = a.id)
                            WHERE (a.status = 20 OR a.ban=20)  AND a.del = 0 '.$sqladd.$where.$order.'
                    ';

                return db::get_cols($sql);
            }
        }
    }

    private function merge_results($query1, $query2)
    {
        $query1 = array_unique($query1);
        if (request::get_int('to')) {
            return array_merge($query1, array_diff($query2, $query1));
        } else {
            return array_merge(array_diff($query2, $query1), $query1);
        }
    }

    public function get_reestr_payments($region = false, $city = false, $ppo = false, $date = 0, $metod = 0, $way = 0)
    {
        if ($region && is_array($region) && count($region) > 0) {
            $sqladd = ' AND a.id IN (SELECT user_id FROM user_data WHERE region_id IN ('.implode(',', $region).')) ';
        } else {
            if ($city && is_array($city) && count($city) > 0) {
                $sqladd = ' AND a.id IN (SELECT user_id FROM user_data WHERE city_id IN ('.implode(',', $city).')) ';
            } else {
                if ($ppo && is_array($ppo) && count($ppo) > 0) {
                    $sqladd = ' AND a.id IN (SELECT user_id FROM ppo_members WHERE group_id IN ('.implode(
                            ',',
                            $ppo
                        ).')) ';
                }
            }
        }
        if ($metod > 0) {
            $sqladd .= ' AND p.method='.$metod;
        }
        if ($way > 0) {
            $sqladd .= ' AND p.way='.$way;
        }
        if ($date > 0) {
            $sqladd .= ' AND p.date>'.$date;
        }
        /*$sql = '
                SELECT a.id,p.date
                FROM user_auth a
                LEFT JOIN user_payments p ON p.user_id=a.id
                WHERE a.status >= 10 AND p.approve != 2 AND p.del = 0 '.$sqladd.'
                GROUP BY p.date,a.id ORDER BY p.date DESC
                ';*/
        $sql = '
                        SELECT a.id,p.date
                        FROM user_auth a
                        LEFT JOIN user_payments p ON p.user_id=a.id
                        WHERE p.approve != 2 AND p.del = 0 '.$sqladd.'
                        GROUP BY p.date,a.id ORDER BY p.date DESC
                        ';

        return db::get_cols($sql);
    }

    public function get_reestr_search()
    {
        $where = 'u.user_id IN (SELECT id FROM user_auth WHERE status = 20)';
        if (request::get_string('first_name')) {
            $where .= " AND u.first_name ILIKE '".addslashes(strip_tags(request::get_string('first_name')))."%'";
        }
        if (request::get_string('last_name')) {
            $where .= " AND u.last_name ILIKE '".addslashes(strip_tags(request::get_string('last_name')))."%'";
        }
        if (request::get_int('region')) {
            $where .= ' AND u.region_id = '.request::get_int('region');
        }
        if (request::get_int('city')) {
            $where .= ' AND u.city_id = '.request::get_int('city');
        }
        if (request::get_int('hasphoto')) {
            if (request::get_int('hasphoto') == 2) {
                $where .= " AND u.photo_salt != '' AND u.photo_salt IS NOT NULL ";
            } else {
                $where .= " AND (u.photo_salt = '' OR u.photo_salt IS NULL) ";
            }
        }

        if ($vv = request::get_int('vv')) {
            if ($vv == 1) {
                $where .= ' AND (u.user_id IN (SELECT user_id FROM user_zayava WHERE kvitok = 0) OR u.user_id IN (SELECT user_id FROM user_payments WHERE type = 1 AND approve = 2))';
            } else {
                $where .= ' AND (u.user_id IN (SELECT user_id FROM user_zayava WHERE kvitok > 0) AND u.user_id NOT IN (SELECT user_id FROM user_payments WHERE type = 1 AND approve = 2))';
            }
        }

        if ($kvnumber = trim(addslashes(htmlspecialchars(request::get_string('kvnumber'))))) {
            $where .= " AND u.user_id IN (SELECT user_id FROM membership WHERE kvnumber = $kvnumber)";
        }
        if ($invnumber = trim(addslashes(htmlspecialchars(request::get_string('invnumber'))))) {
            $where .= " AND u.user_id IN (SELECT user_id FROM membership WHERE invnumber ILIKE '$invnumber')";
        }
        if (session::has_credential('admin') && in_array(request::get('real_app'), [1, 2])) {
            load::model('user/zayava');
            $where .= ' AND u.user_id IN (SELECT user_id FROM user_zayava WHERE real_app = '.(request::get_int(
                        'real_app'
                    ) - 1).')';
        }
        if ($kvm = request::get_int('kvm') && $kvg = request::get_int('kvg')) {
            if ($kvm > 1) {
                $kvm = 0;
            }
            if ($kvg > 1) {
                $kvg = 0;
            }
            $where .= " AND u.user_id IN (SELECT user_id FROM membership WHERE kvmake = $kvm AND kvgive = $kvg)";
        } else {
            if ($kvm = request::get_int('kvm')) {
                if ($kvm > 1) {
                    $kvm = 0;
                }
                $where .= " AND u.user_id IN (SELECT user_id FROM membership WHERE kvmake = $kvm)";
            } else {
                if ($kvg = request::get_int('kvg')) {
                    if ($kvg > 1) {
                        $kvg = 0;
                    }
                    $where .= " AND u.user_id IN (SELECT user_id FROM membership WHERE kvgive = $kvg)";
                }
            }
        }

        if ($offline = request::get_int('offline')) {
            if ($offline == 1) {
                $where .= ' AND u.user_id IN (SELECT id FROM user_auth WHERE offline > 0)';
            } else {
                if ($offline == 2) {
                    $where .= ' AND u.user_id IN (SELECT id FROM user_auth WHERE offline = 0)';
                }
            }
        }

        $sql = '
                        SELECT u.user_id
                        FROM user_data u
                        WHERE '.$where.'
                        ORDER BY last_name
                        ';

        return db::get_cols($sql);
    }

    public function get_by_identification($order = null)
    {
        if (!$order) {
            $order = 'u.rate DESC';
        }

        $stype     = 'identification = :identification';
        $where_arr = ['identification' => 0];
        $sql       = '
			SELECT user_id
			FROM '.$this->table_name.' a
			JOIN '.user_data_peer::instance()->get_table_name().' u ON (u.user_id = a.id)
			WHERE '.$stype.' AND active = TRUE
			ORDER BY '.$order.'
			LIMIT 500
		';

        return db::get_cols($sql, $where_arr, $this->connection_name);
    }

    public function get_new_people()
    {
        $sql = 'SELECT user_id FROM user_data WHERE user_id IN (SELECT id FROM '.$this->table_name.' WHERE active=:active) AND photo_salt!=:photo_salt  ORDER BY user_id DESC LIMIT 20';

        return db::get_cols($sql, ['active' => 'true', 'photo_salt' => 'NULL'], $this->connection_name);
    }

    public function get_famous_people()
    {
        $sql = 'SELECT id FROM '.$this->table_name.' WHERE famous=:famous AND del=0';

        return db::get_cols($sql, ['famous' => '1']);
    }

    public function get_suslik_people()
    {
        $sql = 'SELECT id FROM '.$this->table_name.' WHERE suslik=:suslik AND del=0';

        return db::get_cols($sql, ['suslik' => '1']);
    }

    public function is_inviter($user_id)
    {
        $user = parent::get_item($user_id);
        $user['inviter'] == 1 ? $inviter = true : $inviter = false;

        return $inviter;
    }

    public function is_user_inviter($uId)
    {
        $user = parent::get_item($uId);
        if (($user['invited_by'] == session::get_user_id() || $user['recomended_by'] == session::get_user_id(
                )) and ($user['active']) == false) {
            return true;
        } else {
            return false;
        }
    }

    public function get_all_recomended_by_user($user_id, $active = false, $unactive = false, $order = '')
    {
        $sql  = 'SELECT id FROM user_auth WHERE (invited_by=:invited_by OR recomended_by=:recomended_by) AND hide_inviter = FALSE';
        $bind = ['invited_by' => $user_id, 'recomended_by' => $user_id];
        if ($active) {
            $sql .= ' AND active is TRUE';
        }
        if ($unactive) {
            $sql .= ' AND active is FALSE';
        }
        if ($order != '') {
            $sql .= ' ORDER BY '.$order;
        }

        return db::get_cols($sql, $bind);
    }

    public function get_invited_by_user($user_id)
    {
        return $this->get_list(['invited_by' => $user_id]);
    }

    public function get_recomended_by_user($user_id)
    {
        return $this->get_list(['recomended_by' => $user_id]);
    }

    public function getContactsAccess($user_id, $access)
    {
        $guest = (session::get_user_id()) ? session::get_user_id() : 0;

        if ($user_id == $guest) {
            return true;
        }
        if ($access == 0) {
            return false;
        }
        if (($access == 3 && session::get('type', 0) > 4) or $access == 0) {
            return false;
        }
        if ($access == 1) {
            $friends = db::get_cols('SELECT friend_id FROM friends WHERE user_id = '.$user_id);
            if (!in_array($guest, $friends)) {
                return false;
            }
        }
        if ($access == 2) {
            $friends = db::get_cols('SELECT friend_id FROM friends WHERE user_id = '.$user_id);
            $all     = [];

            if (!in_array($guest, $friends)) {
                foreach ($friends as $f) {
                    $ffrends = db::get_cols('SELECT friend_id FROM friends WHERE user_id = '.$f);
                    if (in_array($guest, $ffrends)) {
                        return true;
                    }
                }
            } else {
                return true;
            }

            return false;
        }

        return true;
    }

    public function get_profile_access($suslik = 0, $this_user = 0, $share_users = [])
    {
        if (!$suslik || $this_user == session::get_user_id()
            || in_array(
                $this_user,
                db::get_cols('SELECT id FROM user_auth WHERE offline=:uid', ['uid' => session::get_user_id()])
            )) {
            return true;
        }
        $share_users = explode(',', str_replace(['{', '}'], ['', ''], $share_users));
        if (in_array(session::get_user_id(), (array) $share_users)) {
            return true;
        }

        return false;
    }

    public function get_sortable_list()
    {
        return db::get_cols('SELECT id FROM user_auth WHERE priority > 0 ORDER BY priority ASC');
    }

    public function clear_sort()
    {
        $__list = db::get_cols('SELECT id FROM user_auth WHERE priority > 0 ORDER BY priority ASC');

        foreach ($__list as $__id) {
            $__data = [
                'id'       => $__id,
                'priority' => 0,
            ];

            user_auth_peer::instance()->update($__data);
        }
    }
}
