<?php

load::app('modules/admin/controller');
load::view_helper('context');
load::view_helper('user');
load::view_helper('date');
load::action_helper('text', true);
load::action_helper('tags', true);
load::system('email/email');
load::model('messages/messages');
load::model('internal_mailing');
load::model('user/user_data');
load::model('groups/members');
load::model('ppo/ppo');
load::model('ppo/members');
load::model('user/user_desktop');
load::model('lists/lists_users');
load::model('political_views');
load::action_helper('user_email', false);

class admin_cron_internallist_action extends basic_controller
{
    public function execute()
    {
        $this->disable_layout();
        $this->set_renderer(false);

        $this->send();
    }

    public function send()
    {
        mem_cache::i()->disable_inner_cache();
        $list = [];

        $not_sended_ids = internal_mailing_peer::instance()->get_list(['active' => 0]);

        if (!empty($not_sended_ids)) {
            foreach ($not_sended_ids as $key => $value) {
                $current_record   = internal_mailing_peer::instance()->get_item($value);
                $this->mailing_id = $value;

                $filters = $this->resolveFilters($current_record['filters']);

                $filter_name   = $filters['name'];
                $filter_values = $filters['values'];

                $receiver_ids = $this->getUserIds($filter_name, $filter_values);
                $this->sendMails($current_record['sender_id'], $receiver_ids, $current_record['body']);
            }
        }

    }

    private function resolveFilters($expression)
    {
        if (!preg_match('/(\w+):([\d,]+)/', $expression, $matches)) {
            return null;
        }

        return [
            'name'     => $matches[1],
            'values' => explode(',', $matches[2]),
        ];
    }

    private function getUserIds($filter_name, $filter_values)
    {
        //$sql_ff=" and user_id not in (SELECT id FROM user_auth WHERE del=1)";
        switch ($filter_name) {
            case 'common':
                $list = user_auth_peer::instance()->get_list(['del' => 0], [], ['id ASC']);
                break;

            case 'group':

                $list = db::get_cols(
                    'SELECT user_id FROM '.groups_members_peer::instance()->get_table_name(
                    ).' WHERE group_id IN ('.implode(',', $filter_values).')'
                );
                break;

            case 'ppo':


                $ppo  = ppo_peer::instance()->get_item($filter_values[0]);
                $list = ppo_members_peer::instance()->get_members($ppo['id'], false, $ppo);
                break;

            case 'status':
                $list = db::get_cols(
                    'SELECT id as user_id FROM '.user_auth_peer::instance()->get_table_name(
                    ).' WHERE status IN ('.implode(',', $filter_values).')'
                );
                break;

            case 'func':

                foreach ($filter_values as $id => $a) {
                    $where[] = "functions && '{".$a."}'";
                }
                $list = db::get_cols(
                    'SELECT user_id FROM '.user_desktop_peer::instance()->get_table_name().' WHERE '.implode(
                        ' OR ',
                        $where
                    )
                );
                break;

            case 'region':
                $list = db::get_cols(
                    'SELECT user_id FROM '.user_data_peer::instance()->get_table_name().' WHERE region_id IN ('.implode(
                        ',',
                        $filter_values
                    ).')'
                );
                break;

            case 'lists':

                $list = db::get_cols(
                    'SELECT user_id FROM '.lists_users_peer::instance()->get_table_name().' WHERE list_id IN ('.implode(
                        ',',
                        $filter_values
                    ).') AND type = 0'
                );
                break;

            case 'district':
                $list = db::get_cols(
                    'SELECT user_id FROM '.user_data_peer::instance()->get_table_name().' WHERE city_id IN ('.implode(
                        ',',
                        $filter_values
                    ).')'
                );
                break;

            case 'sferas':
                $list = db::get_cols(
                    'SELECT user_id FROM '.user_data_peer::instance()->get_table_name().' WHERE segment IN ('.implode(
                        ',',
                        $filter_values
                    ).')'
                );
                break;

            case 'political_views':

                $list = db::get_cols(
                    'SELECT user_id FROM '.user_data_peer::instance()->get_table_name(
                    ).' WHERE political_views = '.$filter_values
                );
                break;

            case 'targets':
                $i = 0;
                foreach ($filter_values as $fv) {
                    $sqladd .= 'admin_target && \'{'.$fv.'}\' ';
                    if ($i < count($filter_values) - 1) {
                        $sqladd .= ' OR ';
                    }
                    $i++;
                }
                $list = db::get_cols(
                    'SELECT user_id
                                FROM user_data WHERE '.$sqladd
                );
                break;

            case 'visit':
                $name  = 'visit_ts';
                $value = $filter_values;
                $time  = time() - abs($value * 24 * 60 * 60);
                if ($value > 0) {
                    $where = "user_id in (SELECT user_id FROM user_sessions WHERE $name > $time)";
                } elseif ($value < 0) {
                    $where = "user_id in (SELECT user_id FROM user_sessions WHERE $name < $time)";
                }
                $list = db::get_cols(
                    'SELECT user_id FROM '.user_sessions_peer::instance()->get_table_name().' WHERE '.$where
                );
                break;
        }


        foreach ($list as $key => $value) {
            $usr = user_auth_peer::instance()->get_item($value);
            if (1 === $usr['del'] || 0 === (int)$usr['active'] || 1 === (int)$usr['offline']) {
                unset($list[$key]);
            }
        }

        internal_mailing_peer::instance()->update(
            [
                'id'    => $this->mailing_id,
                'count' => count($list),
            ]
        );

        return $list;
    }

    private function sendMails($sender_id, $user_list, $body_tpl)
    {
        $counter     = -1;
        $this->count = count($user_list);

        $tmp   = internal_mailing_peer::instance()->get_item($this->mailing_id);
        $limit = $tmp['senden'] + 250;

        foreach ($user_list as $lid => $user_id) {
            $this->sended = db::get_scalar(
                sprintf('SELECT sended FROM internallist_archive WHERE id = %s', $this->mailing_id)
            );

            $counter++;
            if ($this->sended > $counter) {
                continue;
            }

            $user      = user_auth_peer::instance()->get_item($user_id);
            $user_data = user_data_peer::instance()->get_item($user_id);

            $name = $user_data['first_name'];
            $body = str_replace('NAME', $name, $body_tpl);

            $insert_data = [
                'sender_id'   => $sender_id,
                'receiver_id' => $user_id,
                'body'        => $body,
            ];

            //if(!db::get_scalar("SELECT count(*) FROM messages WHERE sender_id=". $sender_id." AND receiver_id=". $user_id." and body='".$body."'"))
            //{
            $id = messages_peer::instance()->add($insert_data, false);


            $options = [
                '%body%' => tag_helper::get_short($body, 180),
                '%link%' => 'https://'.context::get('host').'/messages/view?id='.$id,
            ];

            user_email_helper::send_sys('user_internallist', $user['id'], $sender_id, $options);

            $this->sended++;
            internal_mailing_peer::instance()->update(
                [
                    'id'     => $this->mailing_id,
                    'sended' => $this->sended,
                    'date'   => time(),
                ]
            );
            //}
            if ($this->sended === $limit) {
                die();
            }
        }

        if ($this->sended === $this->count) {
            $update_data = [
                'id'     => $this->mailing_id,
                'active' => '1',
            ];
            internal_mailing_peer::instance()->update($update_data);
        }
    }
}
