<?=form_open($form_action); ?>
<div id="usher">

<div class="info">
    <?=lang('info_settings'); ?>
</div>

<table class="mainTable padTable" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th width="30%"><?=lang('thd_member_group'); ?></th>
			<th><?=lang('thd_target_url'); ?></th>
			<th>&nbsp;</th>
		</tr>
	</thead>

	<tbody class="roland">
	<?php
		if ( ! $settings):
	?>
		<tr class="row">
            <td><?=form_dropdown('usher_settings[0][group_id]', $groups_dd); ?></td>
            <td><span class="field_prefix">&hellip;&amp;D=cp&amp;&nbsp;</span><input type="text" name="usher_settings[0][target_url]"></td>
			<td class="act">
                <a class="remove_row btn" href="#"><img height="17" src="<?=$theme_url; ?>img/minus.png" width="16"></a>
                <a class="add_row btn" href="#"><img height="17" src="<?=$theme_url; ?>img/plus.png" width="16"></a>
			</td>
		</tr>
	<?php
		else:
		foreach ($settings AS $group_settings):
	?>
		<tr class="row">
            <td><?=form_dropdown('usher_settings[0][group_id]', $groups_dd, $group_settings->get_group_id()); ?></td>
            <td><span class="field_prefix">&hellip;&amp;D=cp&amp;&nbsp;</span><input type="text" name="usher_settings[0][target_url]" value="<?=$group_settings->get_target_url(); ?>"></td>
			<td class="act">
				<a class="remove_row btn" href="#"><img height="17" src="/themes/third_party/usher/img/minus.png" width="16"></a>
				<a class="add_row btn" href="#"><img height="17" src="/themes/third_party/usher/img/plus.png" width="16"></a>
			</td>
		</tr>
	<?php
		endforeach;
		endif;
	?>
	</tbody>
</table>

</div><!-- /#usher -->

<div class="submit_wrapper"><?=form_submit(array('name' => 'submit', 'value' => lang('lbl_save_settings'), 'class' => 'submit')); ?></div>

<?=form_close(); ?>
