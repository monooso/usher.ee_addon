<?php

echo form_open($action_url, '', $hidden_fields);

$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(
	lang('thd_member_group'),
	lang('thd_redirect_on_login'),
	lang('thd_redirect_url')
);

if ($member_group_settings):

	foreach ($member_group_settings AS $group_id => $group_settings)
	{
		$this->table->add_row(array(
			$member_groups[$group_id] ? $member_groups[$group_id] : lang('unknown_member_group') .$group_id,
			form_dropdown(
				"member_groups[{$group_id}][redirect_on_login]",
				$redirect_options,
				$group_settings['redirect_on_login']
			),
			$default_cp_path .'&nbsp;'
			.form_input(array(
				'id'		=> "member_groups[{$group_id}][redirect_url]",
				'maxlength'	=> '128',
				'name'		=> "member_groups[{$group_id}][redirect_url]",
				'style'		=> 'width : 75%',
				'value'		=> $group_settings['redirect_url']
			))
		));
	}

else:

	$this->table->add_row(array('colspan' => '3', 'data' => lang('no_member_groups')));

endif;

echo $this->table->generate();
$this->table->clear();

?>

<!-- Submit Button -->
<div class="tableFooter"><div class="tableSubmit">
<?=form_submit(array('name' => 'submit', 'value' => lang('save_settings'), 'class' => 'submit')); ?>
</div></div>
