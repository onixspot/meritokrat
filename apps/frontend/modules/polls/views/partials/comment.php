<? $comment = polls_comments_peer::instance()->get_item($id) ?>
<div class="mb10 comment_bg" id="comment<?=$id?>">
	<? //$do_hide_comment = $comment['rate'] <= -5; ?>

	<div class="comment_hdata<?=$comment['id']?> left <?=$do_hide_comment ? 'hidden' : ''?>">
		<?=user_helper::photo($comment['user_id'], 's', array('class' => 'border1'))?>
	</div>

	<div class="left ml10" style="width: 610px;">
		<div class="fs11 pb5">
			<div class="left quiet">
				<?=user_helper::full_name($comment['user_id'])?>
				<span class="quiet ml10"><?=user_helper::com_date(date($comment['created_ts']))?></span> &nbsp;
                                <?=($comment['edit'])?t('Отредактировано').': '.strip_tags(user_helper::full_name($comment['edit']),'<a>').(($comment['edit_ts'])?' '.user_helper::com_date(date($comment['edit_ts'])):''):''?>
				<? if ( $do_hide_comment ) { ?>
					<a href="javascript:;" onclick="$('.comment_hdata<?=$comment['id']?>').show();" class="dotted ml10"><?=t('Показать')?></a>
				<? } ?>
			</div>
			<!--div class="right">
				<? if ( session::is_authenticated() && !polls_comments_peer::instance()->has_rated($id, session::get_user_id()) ) { ?>
					<span>
						<a href="javascript:;" onclick="pollsController.rateComment(this, <?=$comment['id']?>, true);"><?=tag_helper::image('common/up.gif', array('height' => 16, 'class' => 'vcenter'))?></a>
						<a href="javascript:;" onclick="pollsController.rateComment(this, <?=$comment['id']?>, false);"><?=tag_helper::image('common/down.gif', array('height' => 16, 'class' => 'vcenter'))?></a>
					</span>
				<? } ?>
				<span class="bold mr10" style="color:<?=$comment['rate'] >= 0 ? $comment['rate'] > 0 ? 'green' : '#999' : 'red' ?>"><?=$comment['rate'] > 0 ? '+' : ''?><?=$comment['rate']?></span>
			</div-->
			<div class="clear"></div>
		</div>

		<div class="combody comment_hdata<?=$comment['id']?> fs12 <?=$do_hide_comment ? 'hidden' : ''?>"><?=user_helper::get_links($comment['text'])?></div>

		<div class="comment_hdata<?=$comment['id']?> fs11 mb5 mt5 <?=$do_hide_comment ? 'hidden' : ''?>">
			<? if ( session::is_authenticated() ) { ?>
				<a href="javascript:;" rel="<?=$comment['id']?>" class="dotted comment_reply"><?=t('Ответить')?></a>
			<? } ?>
			<? if ( session::has_credential('moderator') || ( ( $comment['user_id'] == session::get_user_id() ) && !$comment['childs'] ) ) { ?>
				<a href="javascript:;" rel="<?=$comment['id']?>" class="dotted ml10 comment_update" onClick="Application.<?=($comment['user_id']==session::get_user_id()) ? 'initComUpdUser' : 'initComUpd'?>('<?=$comment['id']?>')"><?=t('Редактировать')?></a>
                                <a href="javascript:;" onclick="Application.delCom('<?=$comment['id']?>','polls/delete_comment',<?=($comment['user_id']==session::get_user_id())?1:0?>)" class="dotted ml10"><?=t('Удалить')?></a>
			<? } ?>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div id="child_comments_<?=$comment['id']?>">
        <? $childs = explode(',', $comment['childs']); foreach ( $childs as $child_id ) { if ( $child_id = (int)$child_id ) { ?>
                        <? include dirname(__FILE__) . '/child_comment.php'; ?>
        <? } } ?>
</div>
<div class="clear"></div>