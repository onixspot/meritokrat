<script src="/static/javascript/jquery/jquery.ui.datepicker.js"></script>
<script src="/static/javascript/jquery/jquery.ui.datepicker-uk.js"></script>
<script src="/static/javascript/jquery/jquery.ui.mouse.js"></script>
<script>jQuery.noConflict();</script>
<script>
    jQuery(document).ready(function ($) {
        $.datepicker.setDefaults($.extend(
                $.datepicker.regional['uk']),
        );
        $('#birthday').datepicker({
            changeMonth: true,
            changeYear: true,
            autoSize: true,
            showOptions: {direction: 'left'},
            dateFormat: 'yy-mm-dd',
            shortYearCutoff: 90,
            yearRange: '1930:2010',
            firstDay: true,
            minDate: new Date(1930, 1 - 1, 1),
        });
    });
</script>
<style type="text/css">
    .ui-datepicker {
        margin-left: 160px;
    }

    .tab_pane_gray a {
        margin-left: 1px;
    }

    #ui-datepicker-div {
        display: none;
    }
</style>

<?php if (session::has_credential('designer') && $user['id'] !== session::get_user_id()) {
    $is_designer = 1;
} ?>

<div class="form_bg" style="margin-right:0">
    <h1 class="column_head"><?= user_helper::full_name($user['id']) ?> &rarr; <a
                style="color:#FFCC66;font-weight: bold;text-decoration: none;"
                href="/profile-<?= $user['id'] ?>"><?= t('Профиль') ?></a> &rarr; <?= t('Редактирование') ?>
    </h1>

    <div class="tab_pane_gray mb10" style="padding-top:5px">
        <?php if (!$is_designer) { ?>
            <a href="javascript:" id="tab_political" class="tab_menu selected" rel="political"><?= t('Политическая деятельность') ?></a>
            <a href="javascript:" id="tab_common" class="tab_menu" rel="common"><?= t('Основные') ?></a>
            <a href="javascript:" id="tab_contacts" class="tab_menu" rel="contacts"><?= t('Контакты') ?></a>
            <a href="javascript:" id="tab_education" class="tab_menu" rel="education"><?= t('Образование') ?></a>
            <a href="javascript:" id="tab_work" class="tab_menu" rel="work"><?= t('Работа') ?></a>
            <?php if (6 === $user['status']) { ?>
                <a href="javascript:" id="tab_volonteer" class="tab_menu" rel="volonteer"><?= t('Волонтерство') ?></a>
            <?php } ?>
            <a href="javascript:" id="tab_intersts" class="tab_menu" rel="interests"><?= t('Интересы') ?></a>
            <a href="javascript:" id="tab_bio" class="tab_menu" rel="bio"><?= t('Биография') ?></a>
            <?php /* <!--a href="javascript:;" id="tab_work_space" class="tab_menu" rel="work_space"><?=t('Рабочий стол')?></a--> */ ?>
            <?php /* if ( $is_candidate ) { ?>
			<a href="javascript:;" id="tab_program" class="tab_menu" rel="program"><?=t('Программа')?></a>
		<? } */ ?>
        <?php } ?>
        <a href="javascript:" id="tab_photo" class="tab_menu <?= $is_designer ? 'selected' : '' ?>"
           rel="photo"><?= t('Фото') ?></a>
        <?php if ($user['id'] === session::get_user_id() || session::has_credential('admin')) { ?>
            <a href="javascript:" id="tab_settings" class="tab_menu" rel="settings"><?= t('Настройки') ?></a>
            <!--a href="javascript:;" id="tab_blacklist" class="tab_menu" rel="blacklist"><?= t(
                    'Черный список'
            ) ?></a-->
        <?php } ?>
        <a href="<?= "map" === request::get_string("tab") ? 'javascript:;' : sprintf(
                '/profile/edit?id=%s&tab=map',
                (session::has_credential('admin') && request::get_int('id')) ? request::get(
                        'id'
                ) : session::get_user_id()
        ) ?>"
           id="tab_map" class="tab_menu" rel="map"><?= t('Я на карте') ?></a>

        <?php if (session::has_credential('admin')) { ?>
            <a href="javascript:" id="tab_admin_info" class="tab_menu" rel="admin_info">*<?= t('Админ') ?></a>
            <a href="javascript:" id="tab_admin_contact" class="tab_menu"
               rel="admin_contact">*<?= t('Контакт.инфо') ?></a>
            <!--<a href="javascript:;" id="tab_contact_info" class="tab_menu" rel="contact_info">*<?= t(
                    'Контакт'
            ) ?></a>-->
        <?php } ?>
        <div class="clear"></div>
    </div>

    <!-- common -->
    <?php include 'partials/edit/common.php'; ?>

    <!-- contacts -->
    <?php include 'partials/edit/contacts.php'; ?>

    <?php ?>
    <!-- EDUCATION -->
    <?php include 'partials/edit/education.php'; ?>

    <!-- WORK -->
    <?php include 'partials/edit/work.php'; ?>

    <!-- Volunteer -->
    <?php include 'partials/edit/volunteer.php'; ?>


    <!-- INTERESTS -->
    <?php include 'partials/edit/interests.php'; ?>

    <!-- WORK_SPACE -->
    <?php /*
        <form id="work_space_form" class="form mt10">
                <? if ( session::has_credential('admin') ) { ?>
                        <input type="hidden" name="id" value="<?=$user_data['user_id']?>">
                <? } ?>
                <input type="hidden" name="type" value="work_space">
                <table width="100%" class="fs12">

                <? if ( session::has_credential('admin') ) { ?>
                        <tr>
                                <td class="aright"><?=t('Логистический координатор')?></td>
                                <td><input type="checkbox" name="koordinator" value="1" <?=$user_data['koordinator']==1 ? 'checked="checked"' : ''?>></td>
                        </tr>
                        <tr>
                                <td class="aright"><?=t('Кол-во переданных брошюр')?></td>
                                <td><input name="brochure_given" class="text" type="text" value="<?=intval($user_data['brochure_given'])?>" /></td>
                        </tr>
                <? } ?>
                <? if ($user_data['koordinator']==1) { ?>
                        <tr>
                                <td class="aright"><?=t('Кол-во брошюр')?></td>
                                <td><input name="brochure_remaining" class="text" type="text" value="<?=intval($user_data['brochure_remaining'])?>" /></td>
                        </tr>
                <? } ?>
                        <tr>
                                <td></td>
                                <td>
                                        <input type="submit" name="submit" class="button" value=" <?=t('Сохранить')?> ">
                                        <input onclick="history.go(-1);" type="button" name="cancel" class="button_gray" value=" <?=t('Отмена')?> ">
                                        <?=tag_helper::wait_panel('work_space_wait') ?>
                                        <div class="success hidden mr10 mt10"><?=t('Изменения сохранены')?></div>
                                </td>
                        </tr>
                </table>
        </form>
        */ ?>

    <!-- BIO -->
    <?php include 'partials/edit/bio.php'; ?>

    <!-- PHOTO -->
    <?php include 'partials/edit/photo.php'; ?>

    <?php /*if ( $is_candidate ) { ?>
                <form id="program_form" class="hidden form mt10" method="post">
                        <? if ( session::has_credential('admin') ) { ?>
                                <input type="hidden" name="id" value="<?=$user_data['user_id']?>">
                        <? } ?>
                        <input type="hidden" name="type" value="program">
                        <table width="100%" class="fs12">
                                <tr>
                                        <td width="30%" class="aright"><?=t('Текст программы')?></td>
                                        <td>
                                                <textarea name="program" class="text"><?=stripslashes(htmlspecialchars($candidate['program'])?></textarea>
                                        </td>
                                </tr>

                                <tr>
                                        <td></td>
                                        <td>
                                                <div class="mt10"></div>
                                                <input type="submit" name="submit" class="button" value=" <?=t('Сохранить')?> ">
                                                <input onclick="history.go(-1);" type="button" name="cancel" class="button_gray" value=" <?=t('Отмена')?> ">
                                                <?=tag_helper::wait_panel('program_wait') ?>
                                                <div class="success hidden mr10 mt10"><?=t('Изменения сохранены')?></div>
                                        </td>
                                </tr>

                        </table>

                        <script src="/static/javascript/library/tinymce/tiny_mce.js"></script>
                        <script type="text/javascript">
                        // O2k7 skin
                        tinyMCE.init({
                                mode : "exact",
                                language : '<?=translate::get_lang() == 'ru' ? 'ru' : 'uk'?>',
                                elements : "program",
                                theme : "advanced",
                                skin : "o2k7",
                                plugins : "insertdatetime,contextmenu,paste,directionality,visualchars,xhtmlxtras,table,media,youtube",

                                theme_advanced_buttons1 : "bold,italic,underline,blockquote,|,forecolor,|,bullist,numlist,|,link,image,youtube,|,tablecontrols",
                                theme_advanced_buttons2 : "",
                                theme_advanced_buttons3 : "",
                                theme_advanced_buttons4 : "",
                                theme_advanced_toolbar_location : "top",
                                theme_advanced_toolbar_align : "left",

                                content_css: '/static/css/typography.css'
                        });
                        </script>

                </form>
        <? } */ ?>

    <!-- settings -->
    <?php include 'partials/edit/settings.php'; ?>

    <!-- adminInfo -->
    <?php if (session::has_credential('admin')) { ?>
        <?php include 'partials/edit/admin_info.php'; ?>
    <?php } ?>

    <!-- adminContact -->
    <?php if (session::has_credential('admin')) { ?>
        <?php include 'partials/edit/admin_contact.php'; ?>
    <?php } ?>

    <!-- contactInfo -->
    <?php if (session::has_credential('admin')) { ?>
        <?php //include 'partials/edit/contact_info.php'; ?>
    <?php } ?>

    <!-- politicalInfo -->
    <?php if (session::has_credential('admin')) { ?>
        <?php include 'partials/edit/political.php'; ?>
    <?php } ?>

    <!-- map -->

    <?php //include 'partials/edit/map.php'; ?>


    <!--div id="blacklist_form" class="hidden form mt10 p10 fs12">
                <?= t('Черный список поможет избежать неконструктивных обсуждений.') ?>
                <?= t(
            'Если Вы добавили пользователя в свой черный список, то он не сможет комментировать Ваши посты и ставить им оценки.'
    ) ?>
                <br/><br/>

                <?php if (!$blacklist) { ?>
                        <div class="screen_message acenter"><?= t('В Вашем списке пока пусто') ?></div>
                <?php } ?>

                <ul>
                <?php foreach ($blacklist as $id) { ?>
                        <li id="banned_<?= $id ?>" class="mb5">
                                <?= user_helper::full_name($id) ?>
                                <a class="ml10 fs10 maroon" href="javascript:;" onclick="profileController.unBan(<?= $id ?>);"><?= t(
            'удалить'
    ) ?></a>
                        </li>
                <?php } ?>
                </ul>
        </div-->

</div>
<script>

    jQuery(document).ready(function ($) {
        $('#party_region').change(function () {
            var region_id = $(this).val();
            var region_attr_id = $(this).attr('id');
            if (region_id == '0') {
                $('#party_city').html('');
                $('#party_city').attr('disabled', true);
                return (false);
            }
            $('#party_city').attr('disabled', true);
            $('#party_city').html('<option>завантаження...</option>');

            var url = '/profile/get_select';
            $.post(url, {'region': region_id},
                    function (result) {
                        if (result.type == 'error') {
                            alert('error');
                            return (false);
                        } else {
                            var options = '<option value="">- оберіть місто/район -</option>';
                            $(result.cities).each(function () {
                                options += '<option value="' + $(this).attr('id') + '">' + $(this).attr('title') + '</option>';
                            });
                            $('#party_city').html(options);
                            $('#party_city').attr('disabled', false);
                        }
                    },
                    'json',
            );
        });
    });
</script>