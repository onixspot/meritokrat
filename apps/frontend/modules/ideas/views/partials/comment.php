<? $comment = ideas_comments_peer::instance()->get_item($id) ?>
<div class="mb10 comment_bg" id="comment<?=$id?>">
	<div class="left"><?=user_helper::photo($comment['user_id'], 's', array('class' => 'border1'))?></div>
	<div class="left ml10" style="width: 525px;">
		<div class="fs11 pb5">
			<div class="left quiet">
				<?=user_helper::full_name($comment['user_id'])?>
				<span class="quiet ml10"><?=user_helper::com_date(date($comment['created_ts']))?></span> &nbsp;
                                <?=($comment['edit'])?t('Отредактировано').': '.strip_tags(user_helper::full_name($comment['edit']),'<a>').(($comment['edit_ts'])?' '.user_helper::com_date(date($comment['edit_ts'])):''):''?>
			</div>
			<div class="clear"></div>
		</div>

		<div class="combody fs12"><?=user_helper::get_links($comment['text'])?></div>

		<div class="fs11 mb5 mt5">
			<? if ( session::is_authenticated() ) { ?>
				<a href="javascript:;" rel="<?=$comment['id']?>" class="dotted comment_reply"><?=t('Ответить')?></a>
			<? } ?>
			<? if ( session::has_credential('moderator') ||
				( ( $comment['user_id'] == session::get_user_id() ) && !$comment['childs'] ) ||
				( session::has_credential('selfmoderator') && $idea['user_id'] == session::get_user_id() ) ) { ?>
                                <a href="javascript:;" rel="<?=$comment['id']?>" class="dotted ml10 comment_update" onClick="Application.<?=($comment['user_id']==session::get_user_id()) ? 'initComUpdUser' : 'initComUpd'?>('<?=$comment['id']?>')"><?=t('Редактировать')?></a>
				<a href="javascript:;" onclick="Application.delCom('<?=$comment['id']?>','ideas/delete_comment',<?=($comment['user_id']==session::get_user_id())?1:0?>)" class="dotted ml10"><?=t('Удалить')?></a>
			<? } ?>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div id="child_comments_<?=$comment['id']?>" class="mb15">
        <? $childs = explode(',', $comment['childs']); foreach ( $childs as $child_id ) { if ( $child_id = (int)$child_id ) { ?>
                        <? include dirname(__FILE__) . '/child_comment.php'; ?>
        <? } } ?>
</div>
<div class="clear"></div>