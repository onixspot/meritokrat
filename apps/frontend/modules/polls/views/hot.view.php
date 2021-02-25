<? $sub_menu = '/polls'; ?>
<? include 'partials/sub_menu.php' ?>

<div class="left" style="width: 35%;"><? include 'partials/left.php' ?></div>

<div class="left ml10" style="width: 62%;">
	<? if ( $tag ) { ?>
		<h1 class="column_head"><a href="/polls"><?=t('Опросы')?></a> &rarr; <?=$tag?></h1>
	<? } else { ?>
		<h1 class="column_head"><?=t('Популярные вопросы')?></h1>
	<? } ?>

	<? foreach ( $list as $id ) { include 'partials/poll.php'; } ?>

	<div class="bottom_line_d mb10"></div>
	<div class="right pager"><?=pager_helper::get_short($pager)?></div>
</div>