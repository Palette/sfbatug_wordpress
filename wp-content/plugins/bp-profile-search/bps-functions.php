<?php
include 'bps-search.php';
include 'bps-form.php';

function bps_help ()
{
    $screen = get_current_screen ();

	$title_00 = __('Overview', 'bps');
	$content_00 = '
<p>'.
__('Configure the search form for your Members Directory, then display it:', 'bps'). '
<ul>
<li>'. sprintf (__('In your Members Directory page, selecting the option %s', 'bps'), '<em>'. __('Add to Directory', 'bps'). '</em>'). '</li>
<li>'. sprintf (__('In a sidebar or widget area, using the widget %s', 'bps'), '<em>'. __('Profile Search', 'bps'). '</em>'). '</li>
<li>'. sprintf (__('In a post or page, using the shortcode %s', 'bps'), '<strong>[bp_profile_search_form]</strong>'). '</li>
<li>'. sprintf (__('Anywhere in your theme, using the PHP code %s', 'bps'), "<strong>&lt;?php do_action ('bp_profile_search_form'); ?&gt;</strong>"). '</li>
</ul>
</p>';

	$title_01 = __('Form Fields', 'bps');
	$content_01 = '
<p>'.
__('Select the profile fields to show in your search form.', 'bps'). '
<ul>
<li>'. __('Customize the field label and description, or leave them empty to use the default', 'bps'). '</li>
<li>'. __('Tick <em>Range</em> to enable the <em>Value Range Search</em> for numeric fields, or the <em>Age Range Search</em> for date fields', 'bps'). '</li>
<li>'. __('To reorder the fields in the form, drag them up or down by the handle on the left', 'bps'). '</li>
<li>'. __('To remove a field from the form, click the [x] on the right', 'bps'). '</li>
</ul>'.
__('Please note:', 'bps'). '
<ul>
<li>'. __('To leave a label or description blank, enter a single blank character', 'bps'). '</li>
<li>'. __('The <em>Age Range Search</em> option is mandatory for date fields', 'bps'). '</li>
<li>'. __('The <em>Value Range Search</em> works for numeric fields only', 'bps'). '</li>
<li>'. __('The <em>Value Range Search</em> is not supported for <em>Multi Select Box</em> and <em>Checkboxes</em> fields', 'bps'). '</li>
</ul>
</p>';

	$title_02 = __('Add to Directory', 'bps');
	$content_02 = '
<p>'.
__('Insert your search form in your Members Directory page.', 'bps'). '
<ul>
<li>'. __('Specify the optional form header', 'bps'). '</li>
<li>'. __('Enable the <em>Toggle Form</em> feature', 'bps'). '</li>
<li>'. __('Enter the text for the <em>Toggle Form</em> button', 'bps'). '</li>
</ul>'.
__('If you select <em>Add to Directory: No</em>, the above options are ignored.', 'bps'). '
</p>';

	$title_03 = __('Text Search Mode', 'bps');
	$content_03 = '
<p>'.
__('Select your text search mode.', 'bps'). '
<ul>
<li>'. __('<em>Partial Match</em>: a search for <em>John</em> finds <em>John</em>, <em>Johnson</em>, <em>Long John Silver</em>, and so on', 'bps'). '</li>
<li>'. __('<em>Exact Match</em>: a search for <em>John</em> finds <em>John</em> only', 'bps'). '</li>
</ul>'.
__('In both modes, two wildcard characters are available:', 'bps'). '
<ul>
<li>'. __('<em>% (percent sign)</em> matches any text, or no text at all', 'bps'). '</li>
<li>'. __('<em>_ (underscore)</em> matches any single character', 'bps'). '</li>
</ul>
</p>';

	$sidebar = '
<p><strong>'. __('For more information:', 'bps'). '</strong></p>
<p><a href="http://dontdream.it/bp-profile-search/" target="_blank">'. __('Documentation', 'bps'). '</a></p>
<p><a href="http://dontdream.it/bp-profile-search/questions-and-answers/" target="_blank">'. __('Questions and Answers', 'bps'). '</a></p>
<p><a href="http://dontdream.it/bp-profile-search/incompatible-plugins/" target="_blank">'. __('Incompatible plugins', 'bps'). '</a></p>
<p><a href="http://dontdream.it/forums/support/bp-profile-search-forum/" target="_blank">'. __('Support Forum', 'bps'). '</a></p>
<br><br>';

	$screen->add_help_tab (array ('id' => 'bps_00', 'title' => $title_00, 'content' => $content_00));
	$screen->add_help_tab (array ('id' => 'bps_01', 'title' => $title_01, 'content' => $content_01));
	$screen->add_help_tab (array ('id' => 'bps_02', 'title' => $title_02, 'content' => $content_02));
	$screen->add_help_tab (array ('id' => 'bps_03', 'title' => $title_03, 'content' => $content_03));

	$screen->set_help_sidebar ($sidebar);

	return true;
}

function bps_admin_js ()
{
	$translations = array (
		'field' => __('Field', 'bps'),
		'label' => __('Label', 'bps'),
		'description' => __('Description', 'bps'),
		'range' => __('Range', 'bps'),
		'member_type' => __('Member Type', 'bps'),
	);
	wp_enqueue_script ('bps-admin', plugins_url ('bps-admin.js', __FILE__), array ('jquery-ui-sortable'), BPS_VERSION);
	wp_localize_script ('bps-admin', 'bps_strings', $translations);
}

function bps_update_fields ()
{
	global $bps_options;

	list ($x, $fields) = bps_get_fields ();

	$bps_options['field_name'] = array ();
	$bps_options['field_label'] = array ();
	$bps_options['field_desc'] = array ();
	$bps_options['field_range'] = array ();
	$bps_options['field_member_type'] = array ();

	$j = 0;
	$posted = $_POST['bps_options'];
	if (isset ($posted['field_name']))  foreach ($posted['field_name'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$type = $fields[$id]['type'];

		$label = stripslashes ($posted['field_label'][$k]);
		if (empty ($label))  $label = $fields[$id]['name'];

		$desc = stripslashes ($posted['field_desc'][$k]);
		if (empty ($desc))  $desc = $fields[$id]['description'];

		$bps_options['field_name'][$j] = $id;
		$bps_options['field_label'][$j] = $label;
		$bps_options['field_desc'][$j] = $desc;
		if (isset ($posted['field_range'][$k]) && $type != 'checkbox' && $type != 'multiselectbox')
			$bps_options['field_range'][$j] = $j;
		$bps_options['field_member_type'][$j] = $desc = stripslashes ($posted['field_member_type'][$k]);;

		if ($type == 'datebox')
			$bps_options['field_range'][$j] = $j;

		$j = $j + 1;
	}
}

function bps_form_fields ()
{
	global $bps_options;

	list ($groups, $fields) = bps_get_fields ();
	echo '<script>var bps_groups = ['. json_encode ($groups). '];</script>';
	echo '<script>var bps_member_types = '. json_encode (xprofile_get_field(18)->get_children_array()). ';</script>';
?>

	<table id="field_box" class="field_box">
		<tr>
			<th>
				Name
			</th>
			<th>
				Label
			</th>
			<th>
				Description
			</th>
			<th>
				Range
			</th>
			<th>
				Available To
			</th>
		</tr>
	<?php
	foreach ($bps_options['field_name'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$label = $bps_options['field_label'][$k];
		$desc = $bps_options['field_desc'][$k];
		$member_type = $bps_options['field_member_type'][$k]; 
		?>
		<tr id='field_tr<?php echo $k; ?>'>
			<td>	
				<?php bps_profile_fields ("bps_options[field_name][$k]", "field_name$k", $id); ?>
			</td>
			<td>
				<input type="text" name="bps_options[field_label][<?php echo $k; ?>]" id="field_label<?php echo $k; ?>" value="<?php echo $label; ?>" />
			</td>
			<td>
				<input type="text" name="bps_options[field_desc][<?php echo $k; ?>]" id="field_desc<?php echo $k; ?>" value="<?php echo $desc; ?>" />
			</td>
			<td>
				<input type="checkbox" name="bps_options[field_range][<?php echo $k; ?>]" id="field_range<?php echo $k; ?>" value="<?php echo $k; ?>"<?php if (isset ($bps_options['field_range'][$k])) echo ' checked="checked"'; ?> />
			</td>
			<td>
				<?php echo xprofile_get_field(18)->get_select_html($k, $member_type); ?>
			</td>
			<td>
				<a href="javascript:hide('field_tr<?php echo $k; ?>')" class="delete">[x]</a>
			</td>
		</tr>
	<?php } ?>
	<input type="hidden" id="field_next" value="<?php echo count ($bps_options['field_label']); ?>" />
	</table>
	<p><a href="javascript:add_field()"><?php _e('Add Field', 'bps'); ?></a></p>
<?php
}

function bps_profile_fields ($name, $id, $value)
{
	list ($groups, $x) = bps_get_fields ();

	echo "<select name='$name' id='$id'>\n";
	foreach ($groups as $group => $fields)
	{
		echo "<optgroup label='$group'>\n";
		foreach ($fields as $field)
		{
			$selected = $field['id'] == $value? " selected='selected'": '';
			echo "<option value='$field[id]'$selected>$field[name]</option>\n";
		}
		echo "</optgroup>\n";
	}
	echo "</select>\n";

	return true;
}

function bps_get_fields ()
{
	global $group, $field;

	static $groups = array ();
	static $fields = array ();

	if (count ($groups))  return array ($groups, $fields);

	if (bp_has_profile ('hide_empty_fields=0'))
	{
		while (bp_profile_groups ())
		{
			bp_the_profile_group (); 
			$groups[$group->name] = array ();

			while (bp_profile_fields ())
			{
				bp_the_profile_field ();
				$groups[$group->name][] = array ('id' => $field->id, 'name' => $field->name);
				$fields[$field->id] = array ('name' => $field->name, 'description' => $field->description, 'type' => $field->type);
			}
		}
	}

	return array ($groups, $fields);
}
?>
