<? //$sub_menu = '/blogs/comments'; include 'partials/sub_menu.php' ?>

<div class="left" style="width: 35%;"><? include 'partials/left.php' ?></div>

<div class="left ml10" style="width: 62%;">

		<h1 class="column_head"> <?=t('Свежие комментарии')?></h1>


	<? foreach ( $list as $id ) { ?>
		<? $post_data = blogs_posts_peer::instance()->get_item($id) ?>
		<? include 'partials/post.comment.php'; ?>
	<? } ?>

	<div class="right pager"><?=pager_helper::get_short($pager)?></div>

</div>