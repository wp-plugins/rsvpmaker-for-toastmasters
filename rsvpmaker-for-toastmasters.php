<?php
/*
Plugin Name: RSVPMaker for Toastmasters
Plugin URI: http://wp4toastmasters.com
Description: This Toastmasters-specific extension to the RSVPMaker events plugin adds role signups and member performance tracking. Better Toastmasters websites!
Author: David F. Carr
Version: 1.5.3
Author URI: http://www.carrcommunications.com
*/

$locale = get_locale();

$mofile = WP_PLUGIN_DIR . '/rsvpmaker-for-toastmasters/translations/rsvptoast-' . $locale . '.mo';

load_textdomain('rsvptoast',$mofile);

include "tm-reports.php";

function awesome_dashboard_widget_function() {

global $current_user;
global $wpdb;

?>
<p><?php _e("You're viewing the private members-only area of the website.",'rsvptoast'); ?>
<br /></p>

<table>
<?php

// lookup next meeting
$sql = "SELECT *, $wpdb->posts.ID as postID
FROM `".$wpdb->prefix."rsvp_dates`
JOIN $wpdb->posts ON ".$wpdb->prefix."rsvp_dates.postID = $wpdb->posts.ID
WHERE datetime > DATE_SUB(NOW( ), INTERVAL 4 HOUR) AND $wpdb->posts.post_status = 'publish'
ORDER BY datetime LIMIT 0, 10";
			  $count = 0;
			  $results = $wpdb->get_results($sql,ARRAY_A);
			  if($results)
			  {
			  foreach($results as $index => $row)
			  	{
					$t = strtotime($row["datetime"]);
					$title = $row["post_title"] . ' '.date('F jS',$t );
					$permalink = rsvpmaker_permalink_query($row["postID"]);
					if(strpos($row["post_content"],'role'))
					{
						if($count == 3)
							continue;
						$count++;
						printf('<tr><td>%s :</td><td> <a href="%s">'.__('Signup','rsvptoast').'</a> | <a href="%sedit_roles=1">'.__('Edit Signups','rsvptoast').'</a> | <a target="_blank" href="%semail_agenda=1">'.__('Email Roster','rsvptoast').'</a></td></tr>', $title, login_redirect($permalink), login_redirect($permalink),$permalink);
						if($index == 0)
						printf('<tr><td>&nbsp;</td><td> <a target="_blank" href="%sprint_agenda=1">'.__('Print Agenda','rsvptoast').'</a> | <a target="_blank" href="%sprint_agenda=1&word_agenda=1">'.__('Download to Word','rsvptoast').'</a></td></tr>', $permalink,$permalink);
					}
				}
			  }

$wp4toastmasters_mailman = get_option('wp4toastmasters_mailman');
$wp4toastmasters_member_message = get_option('wp4toastmasters_member_message');
if(!empty($wp4toastmasters_member_message))
	$wp4toastmasters_member_message = wpautop($wp4toastmasters_member_message);

?>
</table>
<p><a href="<?php echo site_url('/?signup2=1'); ?>" target="_blank"><?php _e("Print Signup Sheet",'rsvptoast');?></a>
<br /></p>
<p><a href="./profile.php#user_login"><?php _e("Edit My Member Profile",'rsvptoast');?></a>
<br /></p>
<!-- p><a href="< ?php echo site_url('/members/'); ?>">Member Directory</a>
<br /></p -->
<p><a href="<?php echo site_url('/?print_contacts=1'); ?>" target="_blank"><?php _e("Print Contacts List",'rsvptoast');?></a>
<br /></p>
<p><a href="<?php echo site_url(); ?>"><?php _e("Home Page",'rsvptoast');?></a>
<br /></p>

<?php

if(isset($wp4toastmasters_mailman["members"]) && !empty($wp4toastmasters_mailman["members"]) )
	printf('<p>'.__("Email all members",'rsvptoast').': <a href="mailto:%s">%s</a> ('.__('for club business or social invitations, no spam please','rsvptoast').')<br /></p>',$wp4toastmasters_mailman["members"],$wp4toastmasters_mailman["members"]);

echo $wp4toastmasters_member_message;

if(current_user_can('edit_others_posts'))
{
$wp4toastmasters_officer_message = get_option('wp4toastmasters_officer_message');
if(!empty($wp4toastmasters_officer_message))
	$wp4toastmasters_officer_message = wpautop($wp4toastmasters_officer_message);
?>
<p><strong><?php _e("Administration",'rsvptoast');?>:</strong></p>
<p><a href="./users.php?page=add_awesome_member"><?php _e("Add Member",'rsvptoast');?></a>
<br /></p>
<p><a href="./users.php?page=edit_members"><?php _e("Edit Members",'rsvptoast');?></a>
<br /></p>
<p><a href="./users.php?page=extended_list"><?php _e("Extended Members List",'rsvptoast');?></a> - <?php _e("includes former members",'rsvptoast');?>
<br /></p>

<?php

$sql = "SELECT count(*)
FROM `".$wpdb->prefix."rsvp_dates`
JOIN $wpdb->posts ON ".$wpdb->prefix."rsvp_dates.postID = $wpdb->posts.ID
WHERE datetime > NOW( ) AND $wpdb->posts.post_status = 'publish'";
$count = $wpdb->get_var($sql);

$args = array('post_type' => 'rsvpmaker','post_status' => 'publish', 'meta_key' => '_sked');
$templates = get_posts($args);
if($count == 0)
	{

?>
<h3><?php _e("You have no published events based on your club meeting template",'rsvptoast');?>.</h3>
<?php
	}
elseif($count < 10)
	{
		printf('<p><strong>'.__("Future events scheduled",'rsvptoast').': %s</strong></p>',$count);
	}

if($templates)
{
echo "<p><strong>Toastmasters ".__('Event Templates','rsvptoast').'</strong></p>';
foreach($templates as $template) 
	{
	if( strpos($template->post_content,'[toastmaster') === false )
		continue;
	$permalink = rsvpmaker_permalink_query($template->ID);
	printf('<p>%s<br /><a href="%s">%s</a><br /><a href="%s">%s</a>', $template->post_title, add_from_template_url($template->ID), __("Add Events (based on template)",'rsvptoast'),agenda_setup_url($template->ID), __("Agenda Setup (drag-and-drop editor)",'rsvptoast'));
	}		
}

if(!empty($wp4toastmasters_mailman["mpass"]))
echo '<p><a href="'.trailingslashit($wp4toastmasters_mailman["mpath"]).'members" target="_blank">'.__("Members Email List",'rsvptoast').'</a> password: '.$wp4toastmasters_mailman["mpass"].'<br /></p>';

if(!empty($wp4toastmasters_mailman["opass"]))
echo'<p><a href="'.trailingslashit($wp4toastmasters_mailman["opath"]).'members" target="_blank">'.__("Officers Email List",'rsvptoast').'</a> password: '.$wp4toastmasters_mailman["opass"].'</p>';

echo $wp4toastmasters_officer_message;

} // end editor functions

?>
<h3>Web Developer's Tip Jar</h3>
<p>WordPress for Toastmasters is offered as a free service, but tips and non-monetary thank yous are appreciated. Particularly if you find the site easier to use or more effective for marketing, consider helping me out!</p>
<p>Monetary contributions help keep me motivated and offset the costs of web hosting. There are some upgrades to the site I will only be able to make if I get some funding.</p>
<p>Non-montary rewards might include sending me leads on paying work (web development or writing and editing) or giving me a plug on social media.</p>
<p>&mdash; David F. Carr, currently an Area Governor in District 47, doing business as <a href="http://www.carrcommunications.com">Carr Communications Inc.</a> Follow me on <a href="https://twitter.com/davidfcarr">Twitter</a>, connect on <a href="http://www.linkedin.com/in/davidfcarr">LinkedIn</a> and <a href="https://www.facebook.com/carrcomm">Facebook</a>. &quot;Like&quot; the <a href="https://www.facebook.com/wp4toastmasters">WordPress for Toastmasters Facebook page</a>.</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="CF5QHBWBNG7AY">
<table>
<tr><td><input type="hidden" name="on0" value="Happiness Scale">Happiness Scale</td></tr><tr><td><select name="os0">
	<option value="Happy">Happy $50.00 USD</option>
	<option value="Very Happy">Very Happy $100.00 USD</option>
	<option value="Extremely Happy">Extremely Happy $200.00 USD</option>
	<option value="Project Sponsor">Project Sponsor $500.00 USD</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<h3>Buy My Book</h3>
<iframe src="http://www.wiley.com:80/WileyCDA/wiley_widget/widget.jsp?isbn=9781118658543&selectSize=2" frameborder="0" width="120" height="200" scrolling="no" allowtransparency="true"></iframe>

<?php

}

add_action('init','wp4toast_reminders');

function wp4toast_reminders () {
if(!$_GET["cron_reminder"])
	return;
global $wpdb;
$wp4toast_reminder = get_option('wp4toast_reminder');
if(!$wp4toast_reminder)
	die("no reminder set");

$sql = "SELECT *, $wpdb->posts.ID as postID
FROM `".$wpdb->prefix."rsvp_dates`
JOIN $wpdb->posts ON ".$wpdb->prefix."rsvp_dates.postID = $wpdb->posts.ID
WHERE datetime > DATE_SUB(NOW( ), INTERVAL 4 HOUR) AND $wpdb->posts.post_status = 'publish'  AND $wpdb->posts.post_content LIKE '%[toast%' 
ORDER BY datetime";
$next = $wpdb->get_row($sql);
if(!$next)
	die('no event scehduled');

echo __("Next meeting",'rsvptoast')." $next->datetime <br />";	

$nexttime = $next->datetime;

$t = strtotime($nexttime.' -'.$wp4toast_reminder);
echo date('l jS \of F Y h:i:s A',$t);
$now = mktime();
if($now > $t)
	{
	echo "<div>".__('Reminder time is past','rsvptoast')."</div>";
	$reminder_run = (int) get_option('reminder_run');
	if($reminder_run == $t)
		echo "<div>".__('Reminder already ran','rsvptoast')."</div>";
	else
		{
		echo "<div>".__('Run reminder now','rsvptoast')." </div>";
		wp4_speech_prompt($next, strtotime($next->datetime));
		update_option('reminder_run',$t);
		}
	}
else
	echo "<br />".__("Reminder time is NOT past",'rsvptoast');

die();
}

function wp4toast_setup() {
global $wpdb;
$wpdb->show_errors();
$setup = get_option('wp4toast_setup');
if( empty( $setup ) )
	{
$success = $total = 0;
echo "<ul>";
// AND post_status='publish'
$total++;
		if($wpdb->get_var("SELECT post_title from $wpdb->posts WHERE post_type='page' AND post_content LIKE '%rsvpmaker_upcoming%' AND post_status='publish' ") )
			{
			echo "<li>(&#10004;) ".__("Calendar page created",'rsvptoast')."</li>";
			$success++;
			}
		else
			echo "<li>(<b>X</b>) ".__("To Do: Create a Calendar page including the shortcode/placeholder",'rsvptoast')." [rsvpmaker_upcoming calendar=\"1\"]</li>";

$total++;
		if($wpdb->get_var("SELECT post_title from $wpdb->posts WHERE post_type='page' AND post_status='publish' AND post_content LIKE '%[awesome_members%' ") )
			{
			echo "<li>(&#10004;) ".__("Member page created",'rsvptoast')."</li>";
			$success++;
			}
		else
			echo "<li>(<b>X</b>) ".__("To Do: Create a Calendar page including the shortcode/placeholder",'rsvptoast')." [awesome_members]</li>";
		echo "<li>";

$total++;
		$args = array('post_type' => 'rsvpmaker','post_status' => 'publish', 'meta_key' => '_sked');
		$templates = get_posts($args);
		$tcount = sizeof($templates);
		if($tcount)
			{
			echo "(&#10004;) ".__("Templates created",'rsvptoast')." ($tcount)";
			$success++;
			}
		else
			echo "(<b>X</b>) ".__("To Do",'rsvptoast').": ".'<a href="'.admin_url('edit.php?post_type=rsvpmaker&page=role_setup').'">'.__("Create an event template for your regular meetings",'rsvptoast')."</a>.";
		//print_r($templates);
		echo "</li>";

$total++;
		$users = get_users();
		$count = sizeof($users);
		if($count > 5)
			{
			echo "<li>(&#10004;) ".__("Members imported",'rsvptoast').": $count</li>";
			$success++;
			}
		else
			echo '<li>(<b>X</b>) '.__("To Do: Import members. Current members:",'rsvptoast').' $count). <a href="'.admin_url('users.php?page=add_awesome_member').'">'.__("See add members screen",'rsvptoast').'</a></li>';

$total++;
		$officers = get_option('wp4toastmasters_officer_ids');
		if(is_array($officers) )
			{
			echo "<li>(&#10004;) ".__("Officer list recorded",'rsvptoast')."</li>";
			$success++;
			}
		else
			echo '<li>(<b>X</b>) '.__("To Do",'rsvptoast').': <a href="'.admin_url('options-general.php?page=wp4toastmasters_settings').'">'.__("Record list of officers on settings screen",'rsvptoast').'</a>.</li>';

		echo "</ul>";
echo "<p>$success of $total </p>";
	}
}

function awesome_add_dashboard_widgets() {
wp_add_dashboard_widget('awesome_dashboard_widget', 'WordPress for Toastmasters Dashboard', 'awesome_dashboard_widget_function');

// Globalize the metaboxes array, this holds all the widgets for wp-admin

global $wp_meta_boxes;

unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);

// Get the regular dashboard widgets array
// (which has our new widget already but at the end)

$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
$side_dashboard = $wp_meta_boxes['dashboard']['side']['core'];

// Backup and delete our new dashbaord widget from the end of the array

$awesome_widget_backup = array('awesome_dashboard_widget' =>
$normal_dashboard['awesome_dashboard_widget']);

unset($normal_dashboard['awesome_dashboard_widget']);

// Merge the two arrays together so our widget is at the beginning

$sorted_dashboard = array_merge($awesome_widget_backup, $normal_dashboard);

// Save the sorted array back into the original metaboxes

$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
//$wp_meta_boxes['dashboard']['side']['core'] = $awesome_widget_backup;

}

add_action('wp_dashboard_setup', 'awesome_add_dashboard_widgets',99 );

function agenda_note_edit($atts = array(), $content='' ) {
global $agenda_setup_item;
$content = trim(strip_tags($content,'<b><i><strong><em><img><div><span>'));
if(!isset($agenda_setup_item) )
	$agenda_setup_item = 0;
else
	$agenda_setup_item++;
$index = 'item_'.$agenda_setup_item;
ob_start();
echo '<li id="'.$index.'">';
?>
<div class="note">

<textarea name="agenda_note[<?php echo $index; ?>]" cols="80" rows="3" id="agenda_note_<?php echo $index; ?>" placeholder="<?php _e("Agenda Note",'rsvptoast');?>"/><?php echo $content; ?></textarea> <br />
<a class="show">Show Advanced Options</a><br />
<div class="advanced">
<?php _e("Display on",'rsvptoast');?>: <select name="atts[<?php echo $index; ?>][agenda_display]" ><?php if($atts["agenda_display"]) printf('<option value="%s">%s</option>',$atts["agenda_display"], $atts["agenda_display"]) ?><option value="agenda"><?php _e("agenda",'rsvptoast');?></option><option value="web"><?php _e("web",'rsvptoast');?></option><option value="both"><?php _e("both",'rsvptoast');?></option></select>
<input type="checkbox" name="atts[<?php echo $index; ?>][officers]" value="1" <?php if($atts["officers"]) echo ' checked="checked"'; ?> /> <?php _e("List Officers",'rsvptoast');?> <input type="text" name="atts[<?php echo $index; ?>][label]" size="40" id="field_<?php echo $index; ?>" placeholder="<?php _e("Label for Officers (default: Officers)",'rsvptoast');?>" value="<?php echo $atts["label"]; ?>" />

<br /><?php _e("Separator between officer names",'rsvptoast');?>: <select  name="atts[<?php echo $index; ?>][sep]" >
<option value=", " <?php if(empty($atts["sep"]) || ($atts["sep"] == ', ') ) echo 'checked="checked"'; ?>>comma</option>
<option value="br" <?php if(($atts["sep"] == 'br') ) echo 'checked="checked"'; ?>>line break</option></select>
<br /><?php _e("CSS (advanced option)",'rsvptoast');?>
<input type="text" name="atts[<?php echo $index; ?>][style]" value="<?php echo $atts["style"]; ?>" >
</div>
<br /><input type="checkbox" name="remove[<?php echo $index; ?>]" value="1" class="removex" /> <?php _e("Remove",'rsvptoast');?> 
</div>
</li>
<?php
return ob_get_clean();
}

function agenda_setup_shortcode($atts = array(), $content='' ) {
if(!empty($content))
	return agenda_note_edit($atts, $content );
global $agenda_setup_item;
if(!isset($agenda_setup_item) )
	$agenda_setup_item = 0;
else
	$agenda_setup_item++;
$index = 'item_'.$agenda_setup_item;
ob_start();
echo '<li id="'.$index.'">';

if(isset($atts["themewords"]))
{
?>
<div class="themewords">
<input type="hidden" name="atts[<?php echo $index; ?>][themewords]" value="1" /><?php _e("Block of text for meeting theme, words of the day, or other notes (can be edited along with role assignments).",'rsvptoast');?>
<br /><input type="checkbox" name="remove[<?php echo $index; ?>]" value="1" class="removex" /> <?php _e("Remove",'rsvptoast');?> </div>
<?php
}
elseif(isset($atts["officers"]))
{
?>
<div class="officers" >
<input type="hidden" name="atts[<?php echo $index; ?>][officers]" value="1" />
<input type="text" name="atts[<?php echo $index; ?>][label]" size="60" id="field_<?php echo $i; ?>" placeholder="<?php _e("Label for Officers (default: Officers)",'rsvptoast');?>" value="<?php echo $atts["label"]; ?>" /> <?php _e("Displays listing of officers on agenda",'rsvptoast');?>
<br /><?php _e("Separator between officer names",'rsvptoast');?>: <select  name="atts[<?php echo $index; ?>][sep]" >
<option value=", " <?php if(empty($atts["sep"]) || ($atts["sep"] == ', ') ) echo 'checked="checked"'; ?>>comma</option>
<option value="br" <?php if(($atts["sep"] == 'br') ) echo 'checked="checked"'; ?>>line break</option></select>
<br /><input type="checkbox" name="remove[<?php echo $index; ?>]" value="1" class="removex" /> <?php _e("Remove",'rsvptoast');?> 
</div>
<?php
}
else
{
$count = (isset($atts["count"])) ? $atts["count"] : 1;
?>
<div class="rolefield">
<input type="text" name="atts[<?php echo $index; ?>][role]" size="60" id="field_<?php echo $index; ?>" placeholder="<?php _e("Role",'rsvptoast');?>" value="<?php echo $atts["role"]; ?>" />
<select name="atts[<?php echo $index; ?>][count]"><option value="<?php echo $count; ?>"><?php echo $count; ?></option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select> <input type="checkbox" name="atts[<?php echo $index; ?>][indent]" value="1" <?php if(isset($atts["indent"]) && $atts["indent"]) echo 'checked="checked"'; ?> /> <?php _e("Indent",'rsvptoast');?><br />
<input type="text" name="atts[<?php echo $index; ?>][agenda_note]"  size="60" id="rolenotefield_<?php echo $index; ?>" placeholder="<?php _e("Role Note",'rsvptoast');?>" value="<?php echo htmlentities($atts["agenda_note"]); ?>" /> (<?php _e("note displayed on agenda only",'rsvptoast');?>)<br /><input type="text" name="atts[<?php echo $index; ?>][time]" size="60" id="rolenotefield_<?php echo $index; ?>" placeholder="<?php _e("Time (optional): 7:15 pm OR 7:15 pm, 7:30 pm, 7:45 pm",'rsvptoast');?>" value="<?php echo htmlentities($atts["time"]); ?>" /> <br />
<?php
if(isset($atts["leader"]) && $atts["leader"])
{
?>
<strong><?php _e("Leader field",'rsvptoast');?></strong> - <?php _e("can be renamed but should not be removed",'rsvptoast');?>
<input type="hidden" name="atts[<?php echo $index; ?>][leader]" value="1" />
<?php
}
else
{
?>
<br /><input type="checkbox" name="remove[<?php echo $index; ?>]" value="1" class="removex" /> <?php _e("Remove",'rsvptoast');
}
?>
<br />
</div>
<?php
}

echo '</li>';

return ob_get_clean();
}

function toastmaster_short($atts=array(),$content="") {

	if(isset($_GET["page"]) && ($_GET["page"] == 'agenda_setup'))
		return agenda_setup_shortcode($atts, $content);
	elseif(!empty($content))
		return agenda_note($atts, $content);
	elseif(isset($atts["themewords"]))
		return themewords($atts);	
	elseif(isset($atts["officers"]))
		return toastmaster_officers($atts);
	elseif($atts["special"])
		return '<div class="role-block role-agenda-item"><p><strong>'.$atts["special"].'</strong></p></div>';	
	elseif(empty($atts["role"]) )
		return;
	$count = (int) ($atts["count"]) ? $atts["count"] : 1;
	//$block = (int) ($atts["block"]) ? $atts["block"] : 0;
	global $post, $current_user, $open;
	$permalink = rsvpmaker_permalink_query($post->ID);
	$field_base = preg_replace('/[^a-zA-Z0-9]/','_',$atts["role"]);	
	
	// need to know what role to look up for notifications	
	if(isset($atts["leader"]) )
		update_post_meta($post->ID,'meeting_leader','_'.$field_base.'_1' );

	if($field_base == 'Speaker')
		pack_speakers($count);

	if($_GET["signup2"])
		{
		for($i = 1; $i <= $count; $i++)
			{
			$field = '_' . $field_base . '_' . $i;
			$assigned = (int) get_post_meta($post->ID, $field, true);
			if($assigned == '-1')
				{
				$assignedto = __('Not Available','rsvptoast');
				}
			elseif($assigned)
				{
					$member = get_userdata( $assigned );
					$assignedto = $member->first_name." ".$member->last_name;
				}
			else
				$assignedto = "&nbsp;";
			$output .= "\n".'<div class="signuprole">'.$atts["role"].$req.'<div class="assignedto">'.$assignedto.'</div></div>';
			}
		return $output;
		}

	if($_GET["print_agenda"] || $_GET["email_agenda"])
		{
		if($atts["time"])
			$time = explode(',',$atts["time"]);
		for($i = 1; $i <= $count; $i++)
			{
			$field = '_' . $field_base . '_' . $i;
			$assigned = get_post_meta($post->ID, $field, true);
			if($atts["indent"])
				$output  .= "\n".'<div class="role-agenda-item" style="margin-left: 15px;">';
			else
				$output  .= "\n".'<div class="role-agenda-item">';
			$output .= '<p>';
			if(is_array($time) && $time[$i - 1])
				$output .= '<em>'.$time[$i - 1].'</em> ';
			$output .= '<strong>'.$atts["role"].': </strong>';
			if($assigned == '-1')
				{
				$output .= __('Not Available','rsvptoast');
				}
			elseif($assigned)
				{
					$title = get_post_meta($post->ID, '_title'.$field, true);
					if(!empty($title))
						$title = ": ".$title;
					if(is_numeric($assigned))
						{
						$member = get_userdata( $assigned );
						$name = $member->first_name.' '.$member->last_name;
						}
					else
						$name = $assigned.' ('.__('guest','rsvptoast').')';
					$output .= sprintf('<span class="member-role">%s%s</span>', $name, $title);
				}
			else
				$open[$atts["role"]]++;
			if(isset($atts["agenda_note"]) && !empty($atts["agenda_note"]) )
				$output .=  "<br /><em>".$atts["agenda_note"]."</em>";
			$output .= '</p>';

			if($assigned && strpos($field,'Speaker') )
				{
				$output .= speaker_details_agenda($field);
				}
			$output .= '</div>';
			}
		return $output;
		}

	for($i = 1; $i <= $count; $i++)
		{
		
		$field = '_' . $field_base . '_' . $i;
		$assigned = get_post_meta($post->ID, $field, true);
		$output .= '<div class="role-block" id="'.$field.'"><div class="role-title" style="font-weight: bold;">';
		$output .= $atts["role"].': </div><div class="role-data"> ';
		if(is_user_member_of_blog() && !($_GET["edit_roles"] || $_GET["recommend_roles"] || ($_GET["page"] == 'toastmasters_reconcile' ) )  ) 
			$output .= sprintf(' <form id="%s_form" method="post" class="toastrole" action="%s" style="display: inline;"><input type="hidden" name="role" id="role" value="%s"><input type="hidden" name="post_id" id="post_id" value="%d">',$field,$permalink, $field, $post->ID);
				
		if($assigned == '-1')
				{
				$output .= __('Not Available','rsvptoast');
				}
		elseif($assigned  && !($_GET["edit_roles"] || ($_GET["page"] == 'toastmasters_reconcile' ) ) )
			{
			if(is_numeric($assigned))
				{
				$member = get_userdata( $assigned );
				$output .= sprintf('<div class="member-role">%s %s</div>',$member->first_name, $member->last_name);	
				}
			else
				$output .= sprintf('<div class="member-role">%s (%s)</div>',$assigned,__('guest','rsvptoast'));
			}
		
			if(is_user_member_of_blog() )
			{

			if(strpos($field,'Speaker') )
				{
				$detailsform = speaker_details($field, $assigned);
				}
			else
				$detailsform = '';

			if($_GET["edit_roles"] || ($_GET["page"] == 'toastmasters_reconcile' ) ) // && current_user_can('edit_posts') )
				{
					// editor admin options
					$awe_user_dropdown = awe_user_dropdown($field, $assigned);
					$output .= 'Member: '.$awe_user_dropdown;
					$output .= '<br />Or guest: <input type="text" name="edit_guest['.$field.']" />';
					if(strpos($field,'Speaker') )
						$output .= '<div><input type="checkbox" name="delete_speaker[]" value="'.$field.'" /> '.__('Delete','rsvptoast').'</div>'. $detailsform;
				}
			elseif($_GET["recommend_roles"]) // && current_user_can('edit_posts') )
				{
					// editor admin options
					if(!$assigned)
					{
					$awe_user_dropdown = awe_assign_dropdown($field);
					$output .= $awe_user_dropdown;
					$output .= sprintf('<p>%s:<br /><textarea rows="3" cols="40" name="editor_suggest_note[%s]"></textarea></p><input type="hidden" name="editor_suggest_count[%s]" value="%s" />',__('Add a personal note (optional)','rsvptoast'),$field, $field, $count);
					}
				}
			elseif(!$assigned)
				{
				if(strpos($field,'Speaker') )
					$output .= sprintf('<div class="update_form" id="update'.$field.'">%s</div>',$detailsform);
				$output .= '<button name="take_role" id="take_role" value="1">Take Role</button>';
				}

			elseif($assigned == $current_user->ID)
					{
				if(strpos($field,'Speaker') )
					$output .= sprintf('<div class="update_form" id="update'.$field.'">%s
					<button name="update_role" value="1">'.__('Update Role','rsvptoast').'</button>
					<br />
					<em>or</em>
					</div><div></div>',$detailsform);
					$output .= '<button name="delete_role" id="delete_role" value="1">'.__('Remove Me','rsvptoast').'</button>';
					}
			elseif(strpos($field,'Speaker') )
				$output .= '<div class="update_form" id="update'.$field.'">'.speech_public_details($field).'</div>';
			}
		if(is_user_member_of_blog() && !($_GET["edit_roles"] || $_GET["recommend_roles"] || ($_GET["page"] == 'toastmasters_reconcile' ) )  ) 
				$output .= '</form>';
			$output .= '</div></div><!-- end role block -->';			
			}
	return $output;
}

add_shortcode( 'toastmaster', 'toastmaster_short' );

//deprecated
function agenda_layout($atts) {
	if(isset($_GET["print_agenda"]))
	{
	global $agenda_layout_start;
	global $agenda_columns;
	$agenda_columns++;
	if($agenda_columns == 2)
		$output .= '</td>';
	if($agenda_columns == 3)
		{
		$output .= '</td></tr></table>';
		}
	if($agenda_columns > 2)
		return $output;
	if(!$agenda_layout_start)
		{
			$agenda_layout_start = true;
			$output .= '<table width="100%"><tr>';
		}
	if(isset($atts["sidebar"]) && $atts["sidebar"])
		$output .= '<td width="33%">';	
	else
		$output .= '<td width="*" style="padding-left: 10px; padding-right: 10px; ">';	
	return $output;
	}
	elseif(isset($_GET["page"]))
	{
	if($_GET["page"] == 'toastmasters_reconcile')
		return;
	global $agenda_setup_item;
	if(!isset($agenda_setup_item) )
		$agenda_setup_item = 0;
	else
		$agenda_setup_item++;
	$index = 'item_'.$agenda_setup_item;

	global $agenda_columns;
	$agenda_columns++;
	if($agenda_columns < 3)
		{
		$c = (isset($atts["sidebar"])) ? ' checked="checked" ' : '';
		$radio = sprintf('<br /><input type="radio" name="agenda_sidebar" value="%s" %s> Sidebar',$index, $c);			
		}
	else
		$radio = '';
	return sprintf('<li id="%s"><em>'.__('Agenda Layout: to divide the agenda into 2 columns, use 3 of these blocks, one at the beginning of the first column, one at the beginning of the second column, and a third at the end of the second column. One column may be designated the sidebar (skinnier column).','rsvptoast').'</em><input type="hidden" name="agenda_layout[%s]" value="1">%s <br /><input type="checkbox" name="remove[%s]" value="1" /> Remove</li>',$index, $index, $radio,$index);
	}
}

//deprecated
add_shortcode('agenda_layout','agenda_layout');

function agenda_note($atts, $content) {
	
if(isset($_GET["page"]) && $_GET["page"] == 'agenda_setup')
	return agenda_note_edit($atts, $content );

if($_GET["word_agenda"])
	{
		$atts["style"] = '';
		$atts["sep"] = ' ';
	}

if(isset($atts["officers"]))
	$content .= toastmaster_officers($atts);

$style = (isset($atts["style"])) ? ' style="'.$atts["style"].'" ' : '';

if($_GET["print_agenda"])
	{
	if($atts["agenda_display"] != 'web')
		return '<div class="agenda_note" '.$style.'>'."\n\n".$content."\n\n".'</div>';
	}
elseif(($atts["agenda_display"] == 'web') || ($atts["agenda_display"] == 'both') )
	return '<div class="agenda_note" '.$style.'>'."\n\n".$content."\n\n".'</div>';
else
	return '';
}

add_shortcode( 'agenda_note', 'agenda_note' );

function toastmaster_officers ($atts) {
if(!$_GET["print_agenda"])
	return;
$label = isset($atts["label"]) ? $atts["label"] : __('Officers','rsvptoast');
$sep = isset($atts["sep"]) ? html_entity_decode($atts["sep"]) : ' ';
if($sep == 'br')
	$sep = '<br />';

$wp4toastmasters_officer_ids = get_option('wp4toastmasters_officer_ids');
$wp4toastmasters_officer_titles = get_option('wp4toastmasters_officer_titles');

$buffer = "\n<div class=\"officers\"><strong>".$label."</strong>"; //.$label.": ";
if(is_array($wp4toastmasters_officer_ids))
{
foreach ($wp4toastmasters_officer_ids as $index => $officer_id)
	{
		if(!$officer_id)
			continue;
		$officer = get_userdata($officer_id);
		$title = str_replace(' ','&nbsp;',$wp4toastmasters_officer_titles[$index]);
		$buffer .= sprintf('%s<em>%s</em>&nbsp;%s&nbsp;%s',$sep,$title,$officer->first_name,$officer->last_name);
	}
}
else
	$buffer .= __('Officers list not yet set','rsvptoast');
$buffer .= "</div>\n";
return $buffer;
}

add_shortcode( 'toastmaster_officers', 'toastmaster_officers' );

function awe_user_dropdown ($role, $assigned, $settings = false) {
global $wpdb;
global $sortmember;
global $fnamesort;

$options = '<option value="0">'.__('Open','rsvptoast').'</option>';
if(!empty($assigned) && !is_numeric($assigned) )
	$options = sprintf('<option value="%s">%s (%s)</option>',$assigned,$assigned, __('guest','rsvptoast')).$options;

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {		

	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}

		$member = get_userdata($user->ID);
		$index = preg_replace('/[^a-zA-Z]/','',$member->last_name.$member->first_name.$member->user_login);
		$findex = preg_replace('/[^a-zA-Z]/','',$member->first_name.$member->last_name.$member->user_login);
		$sortmember[$index] = $member;
		$fnamesort[$findex] = $member;
	}	
	
	$member = new stdClass();
	$member->ID = -1;
	$member->last_name = __("Available",'rsvptoast');
	$member->first_name = __("Not",'rsvptoast');
	$member->display_name = __("Not Available",'rsvptoast');
	
	$fnamesort["AAA"] = $sortmember["AAA"] = $member;
	
	ksort($sortmember);
	ksort($fnamesort);

	$options .= '<optgroup label="First Name Sort">';

	foreach($fnamesort as $member)
		{
			if($member->ID == $assigned)
				$s = ' selected="selected" ';
			else
				$s = '';
			$options .= sprintf('<option %s value="%d">%s (%s)</option>',$s, $member->ID,$member->first_name.' '.$member->last_name, $member->user_login);
		}

	$options .= "</optgroup>";

	$options .= '<optgroup label="Last Name Sort">';
	foreach($sortmember as $member)
		{
			if($member->ID == $assigned)
				$s = ' selected="selected" ';
			else
				$s = '';
			$options .= sprintf('<option %s value="%d">%s (%s)</option>',$s, $member->ID,$member->first_name.' '.$member->last_name, $member->user_login);
		}
	$options .= "</optgroup>";

if($settings)
	return '<select name="'.$role.'">'.$options.'</select>';
else
	return '<select name="editor_assign['.$role.']">'.$options.'</select>';
}

function awe_assign_dropdown ($role) {
global $wpdb;
global $haverole;

global $haverole;

if(!is_array($haverole) )
	{
	$custom_fields = get_post_custom($post->ID);
	foreach ($custom_fields as $name => $arr)
	{
		if( preg_match('/^_[A-Z].+_[0-9]/',$name) )
		{
			//echo $name.": ".$arr[0]."<br />";
			$haverole[] = $arr[0];
		}
	}
	}

$options = '<option value="0">Open</option>';

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {		

	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}
	if(is_array($haverole) && in_array($user->ID, $haverole) )
		continue;
	
		$member = get_userdata($user->ID);
			
			if($member->first_name && $member->last_name)
				$member->display_name = $member->first_name.' '.$member->last_name;
			if(isset($member->status_expires) )
				{
				$exp = (int) $member->status_expires;
				
				if(mktime() > $exp)
					{
					delete_user_meta($member->ID,'status');
					delete_user_meta($member->ID,'status_expires');
					$expires = $default_expires;
					$member->status = '';
					}
				else
					$expires = date('Y-m-d',$exp);
				}
			if(!empty($member->status) )
				$member->display_name .= ' ('.$member->status.')';
			else
				{
				$last_filled = last_filled_role($member->ID, $role);
				$member->display_name .= ' (Last filled role: '.$last_filled.')';
				}
			
		$index = preg_replace('/[^a-zA-Z]/','',$member->last_name.$member->first_name.$member->user_login);
		$findex = preg_replace('/[^a-zA-Z]/','',$member->first_name.$member->last_name.$member->user_login);
		$sortmember[$index] = $member;
		$fnamesort[$findex] = $member;
	}	
	
	$member = new stdClass();
	$member->ID = -1;
	$member->last_name = "Available";
	$member->first_name = "Not";
	$member->display_name = "Not Available";
	
	$fnamesort["AAA"] = $sortmember["AAA"] = $member;
	
	ksort($sortmember);
	ksort($fnamesort);

	$options .= '<optgroup label="'.__('Sort by First Name','rsvptoast').'">';

	foreach($fnamesort as $member)
		{
			if($member->ID == $assigned)
				$s = ' selected="selected" ';
			else
				$s = '';
			$options .= sprintf('<option %s value="%d">%s</option>',$s, $member->ID,$member->display_name);
		}

	$options .= "</optgroup>";

	$options .= '<optgroup label="'.__('Sort by Last Name','rsvptoast').'">';
	foreach($sortmember as $member)
		{

			if($member->ID == $assigned)
				$s = ' selected="selected" ';
			else
				$s = '';
			$options .= sprintf('<option %s value="%d">%s</option>',$s, $member->ID,$member->display_name);
		}
	$options .= "</optgroup>";

if($settings)
	return '<select name="'.$role.'">'.$options.'</select>';
else
	return '<select name="editor_suggest['.$role.']">'.$options.'</select>';
}

function clean_role($role) {
$role = str_replace('_1','',$role);
$role = str_replace('_',' ',$role);
return trim($role);
}

function awesome_wall($comment_content, $post_id) {
//disable this for now

global $current_user;
global $wpdb;
$comment_content = "<strong>".$current_user->display_name.':</strong> '.$comment_content;
$stamp = '<small><em>(Posted: '.date('m/d/y H:i').')</em></small>';

$sql = "SELECT datetime FROM ".$wpdb->prefix."rsvp_dates WHERE postID =".$post_id;
$date = $wpdb->get_var($sql);
$ts = strtotime($date);
$comment_content .= ' for '.date('F jS',$ts). ' '.$stamp;

add_post_meta($post_id, '_activity', $comment_content, false);

$signup = get_post_custom($post_id);

$meeting_leader = get_post_meta($post_id, 'meeting_leader', true);
if(empty($meeting_leader))
	$meeting_leader = "_Toastmaster_of_the_Day_1";
$toastmaster = $signup[$meeting_leader][0];

if($toastmaster)
	{
	$userdata = get_userdata($toastmaster);
	$toastmaster_email = $userdata->user_email;
	$subject = $message = $comment_content;
	$url = rsvpmaker_permalink_query($post_id);
	$message .= "\n\nThis is an automated message. Replies will be sent to ".$current_user->user_email;
	$mail["subject"] = substr(strip_tags($subject),0, 100);
	$mail["replyto"] = $current_user->user_email;
	$mail["html"] = "<html>\n<body>\n".wpautop($message)."\n</body></html>";
	$mail["to"] = $toastmaster_email;
	$mail["from"] = $current_user->user_email;
	$mail["fromname"] = $current_user->display_name;
	awemailer($mail);
	}
}

function role_post() {

if(!is_user_member_of_blog()	|| !$_POST["post_id"])
	return;

global $current_user;
$post_id = (int) $_POST["post_id"];
$role = $_POST["role"];
//print_r($_REQUEST);

if($_POST["take_role"] || $_POST["update_speaker_details"])
	{
		update_post_meta($post_id,$role,$current_user->ID);
		awesome_wall("signed up for ".clean_role($role),$post_id);
		if(strpos($role,'peaker') )
			{
			// clean any previous speech data
			//echo '_manual'.$role;
			delete_post_meta($post_id,'_manual'.$role);
			delete_post_meta($post_id,'_title'.$role);
			delete_post_meta($post_id,'_intro'.$role);
			}
	}
/*
if($_POST["set_role"])
	{
		$user = (int) $_POST["userlist"];
		//echo "$post_id,$role,$user<br />";
		if(current_user_can('edit_others_posts') )
			update_post_meta($post_id,$role,$user);
	}
*/

if($_POST["editor_assign"] && current_user_can('edit_posts') )
	{
		foreach($_POST["editor_assign"] as $role => $user_id)
		{
			//echo '<br />'.$role . ' => '. $user_id;
			update_post_meta($post_id,$role,$user_id);
		}
		foreach($_POST["edit_guest"] as $role => $guest)
		{
			if(!empty($guest))
				update_post_meta($post_id,$role,$guest);			
		}
		
		//awesome_wall('Edited the roster',$post_id);
	awesome_wall("edited role signups ",$post_id);

	}

if($_POST["_manual"])
	{
		
		foreach($_POST["_manual"] as $basefield => $manual)
			{
			$title = $_POST["_title"][$basefield];
			$intro = $_POST["_intro"][$basefield];
			
			update_post_meta($post_id,'_manual'.$basefield,$manual);
			update_post_meta($post_id,'_title'.$basefield,$title);
			update_post_meta($post_id,'_intro'.$basefield,$intro);
			}
	}

if($_POST["delete_role"])
	{
		delete_post_meta($post_id,$role);
		if(strpos($role,'peaker') )
			{
			delete_post_meta($post_id,'_manual'.$role);
			delete_post_meta($post_id,'_title'.$role);
			delete_post_meta($post_id,'_intro'.$role);
			}
		awesome_wall("Withdrawn: ".clean_role($role),$post_id);
	}

if($_POST["delete_speaker"])
	{
		foreach($_POST["delete_speaker"] as $field)
			{
			delete_post_meta($post_id,$field);
			delete_post_meta($post_id,'_manual'.$field);
			delete_post_meta($post_id,'_title'.$field);
			delete_post_meta($post_id,'_intro'.$field);
			}
		awesome_wall("Deleted a speaker",$post_id);
	}

//Make sure visitors see current data / Purge a single post / page by passing it's ID:
if (function_exists('w3tc_pgcache_flush_post')) {
w3tc_pgcache_flush_post($post_id);
}

}


add_action('init','role_post');

function speaker_details_agenda ($field) {
	global $post;
	$manual = get_post_meta($post->ID, '_manual'.$field, true);
	$intro = get_post_meta($post->ID, '_intro'.$field, true);
	$output .= ($manual && !strpos($manual,'Manual /') ) ? '<div id="manual"><strong>'.$manual."</strong></div>" : "\n";
	if($output)
		$output = "\n".'<div class="speaker-details">'.$output.'</div>'."\n";
	return $output;
}

function speaker_details ($field, $assigned = 0) {
global $post;

		$manual = get_post_meta($post->ID, '_manual'.$field, true);
		if(!$manual)
			$manual = 'Choose Manual / Speech';
		$output .= '<div><label for="man">'.__('Manual / Speech / Time','rsvptoast').'</label>
		<input type="hidden" name="post_id" value="'.$post->ID.'" />
		<select class="speaker_details" name="_manual['.$field.']" style="width: 400px;"><option value="'.$manual.'">'.$manual.'
</option>'.get_toast_speech_options().'
</select><br>
		<div>'.__('Choose a speech from the current list of Toastmasters International manuals','rsvptoast').'</div>';
			$output .= '</div>';

		$title = get_post_meta($post->ID, '_title'.$field, true);
		
		$output .= '<div class="speech_title">Title: <input type="text" class="speaker_details" name="_title['.$field.']" value="'.$title.'" /></div>';
		
return $output;
}

function get_toast_speech_options() {

return '<option value="Choose Manual / Speech">Choose Manual / Speech</option>
<option value="COMPETENT COMMUNICATION (CC) MANUAL: The Ice Breaker (4 to 6 min)">COMPETENT COMMUNICATION (CC) MANUAL: The Ice Breaker (4 to 6 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Organize Your Speech (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Organize Your Speech (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Get to the Point (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Get to the Point (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: How to Say It (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: How to Say It (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Your Body Speaks (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Your Body Speaks (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Vocal Variety (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Vocal Variety (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Research Your Topic (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Research Your Topic (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Get Comfortable with Visual Aids (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Get Comfortable with Visual Aids (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Persuade with Power (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Persuade with Power (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Inspire Your Audience (8 to 10 min)">COMPETENT COMMUNICATION (CC) MANUAL: Inspire Your Audience (8 to 10 min)
</option><option value="COMMUNICATING ON TELEVISION: Straight Talk (3 min)">COMMUNICATING ON TELEVISION: Straight Talk (3 min)
</option><option value="COMMUNICATING ON TELEVISION: The Talk Show (10 min)">COMMUNICATING ON TELEVISION: The Talk Show (10 min)
</option><option value="COMMUNICATING ON TELEVISION: When You&#39;re the Host (10 min)">COMMUNICATING ON TELEVISION: When You Are the Host (10 min)
</option><option value="COMMUNICATING ON TELEVISION: The Press Conference (4 to 6 min presentation; 8 to 10 min with Q&amp;A)">COMMUNICATING ON TELEVISION: The Press Conference (4 to 6 min presentation; 8 to 10 min with Q&amp;A)
</option><option value="COMMUNICATING ON TELEVISION: Training On Television (5 to 7 min; 5 to 7 min video tape playback)">COMMUNICATING ON TELEVISION: Training On Television (5 to 7 min; 5 to 7 min video tape playback)
</option><option value="FACILITATING DISCUSSION: The Panel Moderator (20 to 30 min)">FACILITATING DISCUSSION: The Panel Moderator (20 to 30 min)
</option><option value="FACILITATING DISCUSSION: The Brainstorming Session (20 to 30 min)">FACILITATING DISCUSSION: The Brainstorming Session (20 to 30 min)
</option><option value="FACILITATING DISCUSSION: The Problem-Solving Session (30 to 40 min)">FACILITATING DISCUSSION: The Problem-Solving Session (30 to 40 min)
</option><option value="FACILITATING DISCUSSION: Handling Challenging Situations (Role Playing) (20 to 30 min)">FACILITATING DISCUSSION: Handling Challenging Situations (Role Playing) (20 to 30 min)
</option><option value="FACILITATING DISCUSSION: Reaching A Consensus (30 to 40 min)">FACILITATING DISCUSSION: Reaching A Consensus (30 to 40 min)
</option><option value="HIGH PERFORMANCE LEADERSHIP: Vision (5 to 7 min)">HIGH PERFORMANCE LEADERSHIP: Vision (5 to 7 min)
</option><option value="HIGH PERFORMANCE LEADERSHIP: Learning (5 to 7 min)">HIGH PERFORMANCE LEADERSHIP: Learning (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Warm Up Your Audience (5 to 7 min)">HUMOROUSLY SPEAKING: Warm Up Your Audience (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Leave Them With A Smile (5 to 7 min)">HUMOROUSLY SPEAKING: Leave Them With A Smile (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Make Them Laugh (5 to 7 min)">HUMOROUSLY SPEAKING: Make Them Laugh (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Keep Them Laughing (5 to 7 min)">HUMOROUSLY SPEAKING: Keep Them Laughing (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: The Humorous Speech (5 to 7 min)">HUMOROUSLY SPEAKING: The Humorous Speech (5 to 7 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: Conversing with Ease (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: Conversing with Ease (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: The Successful Negotiator (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: The Successful Negotiator (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: Diffusing Verbal Criticism (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: Diffusing Verbal Criticism (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: The Coach (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: The Coach (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: Asserting Yourself Effectively (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: Asserting Yourself Effectively (10 to 14 min)
</option><option value="INTERPRETIVE READING: Read A Story (8 to 10 min)">INTERPRETIVE READING: Read A Story (8 to 10 min)
</option><option value="INTERPRETIVE READING: Interpreting Poetry (6 to 8 min)">INTERPRETIVE READING: Interpreting Poetry (6 to 8 min)
</option><option value="INTERPRETIVE READING: The Monodrama (5 to 7 min)">INTERPRETIVE READING: The Monodrama (5 to 7 min)
</option><option value="INTERPRETIVE READING: The Play (12 to 15 min)">INTERPRETIVE READING: The Play (12 to 15 min)
</option><option value="INTERPRETIVE READING: The Oratorical Speech (10 to 12 min)">INTERPRETIVE READING: The Oratorical Speech (10 to 12 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (3 to 5 min)">Other Manual or Non Manual Speech: Custom Speech (3 to 5 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (5 to 7 min)">Other Manual or Non Manual Speech: Custom Speech (5 to 7 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (8 to 10 min)">Other Manual or Non Manual Speech: Custom Speech (8 to 10 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (10 to 12 min)">Other Manual or Non Manual Speech: Custom Speech (10 to 12 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (13 to 15 min)">Other Manual or Non Manual Speech: Custom Speech (13 to 15 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (18 to 20 min)">Other Manual or Non Manual Speech: Custom Speech (18 to 20 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (23 to 25 min)">Other Manual or Non Manual Speech: Custom Speech (23 to 25 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (28 to 30 min)">Other Manual or Non Manual Speech: Custom Speech (28 to 30 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (35 to 40 min)">Other Manual or Non Manual Speech: Custom Speech (35 to 40 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (40 to 45 min)">Other Manual or Non Manual Speech: Custom Speech (40 to 45 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (45 to 50 min)">Other Manual or Non Manual Speech: Custom Speech (45 to 50 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (55 to 60 min)">Other Manual or Non Manual Speech: Custom Speech (55 to 60 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (more than an hour)">Other Manual or Non Manual Speech: Custom Speech (more than an hour)
</option><option value="PERSUASIVE SPEAKING: The Effective Salesperson (3 to 4 min speech; 2 min intro; 3 to 5 min role play)">PERSUASIVE SPEAKING: The Effective Salesperson (3 to 4 min speech; 2 min intro; 3 to 5 min role play)
</option><option value="PERSUASIVE SPEAKING: Conquering the cold call(3 to 4 min speech;2 min intro, 5 to 7 min role play; 2 to 3 min discussion)">PERSUASIVE SPEAKING: Conquering the "Cold Call" (3 to 4 min speech;2 min intro, 5 to 7 min role play; 2 to 3 min discussion)
</option><option value="PERSUASIVE SPEAKING: The Winning Proposal (5 to 7 min)">PERSUASIVE SPEAKING: The Winning Proposal (5 to 7 min)
</option><option value="PERSUASIVE SPEAKING: Addressing the Opposition (7 to 9 min speech; 2 to 3 min Q&amp;A)">PERSUASIVE SPEAKING: Addressing the Opposition (7 to 9 min speech; 2 to 3 min Q&amp;A)
</option><option value="PERSUASIVE SPEAKING: The Persuasive Leader (6 to 8 min)">PERSUASIVE SPEAKING: The Persuasive Leader (6 to 8 min)
</option><option value="PUBLIC RELATIONS: The Persuasive Approach (8 to 10 min)">PUBLIC RELATIONS: The Persuasive Approach (8 to 10 min)
</option><option value="PUBLIC RELATIONS: Speaking Under Fire (6 to 8 min, 8 to 10 min with Q&amp;A)">PUBLIC RELATIONS: Speaking Under Fire (6 to 8 min, 8 to 10 min with Q&amp;A)
</option><option value="PUBLIC RELATIONS: The Goodwill Speech (5 to 7 min)">PUBLIC RELATIONS: The Goodwill Speech (5 to 7 min)
</option><option value="PUBLIC RELATIONS: The Radio Talk Show (8 to 10 min)">PUBLIC RELATIONS: The Radio Talk Show (8 to 10 min)
</option><option value="PUBLIC RELATIONS: The Crisis Management Speech (8 to 10 min, plus 30 seconds wth Q&amp;A)">PUBLIC RELATIONS: The Crisis Management Speech (8 to 10 min, plus 30 seconds wth Q&amp;A)
</option><option value="SPEAKING TO INFORM: The Speech to Inform (5 to 7 min)">SPEAKING TO INFORM: The Speech to Inform (5 to 7 min)
</option><option value="SPEAKING TO INFORM: Resources for Informing (8 to 10 min)">SPEAKING TO INFORM: Resources for Informing (8 to 10 min)
</option><option value="SPEAKING TO INFORM: The Demonstration Talk (10 to 12 min)">SPEAKING TO INFORM: The Demonstration Talk (10 to 12 min)
</option><option value="SPEAKING TO INFORM: A Fact-Finding Report (10 to 12 min)">SPEAKING TO INFORM: A Fact-Finding Report (10 to 12 min)
</option><option value="SPEAKING TO INFORM: The Abstract Concept (10 to 12 min)">SPEAKING TO INFORM: The Abstract Concept (10 to 12 min)
</option><option value="SPECIAL OCCASION SPEECHES: Mastering the Toast (2 to 3 min)">SPECIAL OCCASION SPEECHES: Mastering the Toast (2 to 3 min)
</option><option value="SPECIAL OCCASION SPEECHES: Speaking in Praise (5 to 7 min)">SPECIAL OCCASION SPEECHES: Speaking in Praise (5 to 7 min)
</option><option value="SPECIAL OCCASION SPEECHES: The Roast (3 to 5 min)">SPECIAL OCCASION SPEECHES: The Roast (3 to 5 min)
</option><option value="SPECIAL OCCASION SPEECHES: Presenting an Award (3 to 4 min)">SPECIAL OCCASION SPEECHES: Presenting an Award (3 to 4 min)
</option><option value="SPECIAL OCCASION SPEECHES: Accepting an Award (5 to 7 min)">SPECIAL OCCASION SPEECHES: Accepting an Award (5 to 7 min)
</option><option value="SPECIALTY SPEECHES: Speak Off The Cuff (5 to 7 min)">SPECIALTY SPEECHES: Speak Off The Cuff (5 to 7 min)
</option><option value="SPECIALTY SPEECHES: Uplift the Spirit (8 to 10 min)">SPECIALTY SPEECHES: Uplift the Spirit (8 to 10 min)
</option><option value="SPECIALTY SPEECHES: Sell a Product (10 to 12 min)">SPECIALTY SPEECHES: Sell a Product (10 to 12 min)
</option><option value="SPECIALTY SPEECHES: Read Out Loud (12 to 15 min)">SPECIALTY SPEECHES: Read Out Loud (12 to 15 min)
</option><option value="SPECIALTY SPEECHES: Introduce the Speaker (duration of a club meeting)">SPECIALTY SPEECHES: Introduce the Speaker (duration of a club meeting)
</option><option value="SPEECHES BY MANAGEMENT: The Briefing (8 to 10 min; plus 5 min with Q&amp;A)">SPEECHES BY MANAGEMENT: The Briefing (8 to 10 min; plus 5 min with Q&amp;A)
</option><option value="SPEECHES BY MANAGEMENT: The Technical Speech (8 to 10 min)">SPEECHES BY MANAGEMENT: The Technical Speech (8 to 10 min)
</option><option value="SPEECHES BY MANAGEMENT: Manage And Motivate (10 to 12 min)">SPEECHES BY MANAGEMENT: Manage And Motivate (10 to 12 min)
</option><option value="SPEECHES BY MANAGEMENT: The Status Report (10 to 12 min)">SPEECHES BY MANAGEMENT: The Status Report (10 to 12 min)
</option><option value="SPEECHES BY MANAGEMENT: Confrontation: The Adversary Relationship (5 min speech; plus 10 min with Q&amp;A)">SPEECHES BY MANAGEMENT: Confrontation: The Adversary Relationship (5 min speech; plus 10 min with Q&amp;A)
</option><option value="STORYTELLING: The Folk Tale (7 to 9 min)">STORYTELLING: The Folk Tale (7 to 9 min)
</option><option value="STORYTELLING: Let&#39;s Get Personal (6 to 8 min)">STORYTELLING: Let&rsquo;s Get Personal (6 to 8 min)
</option><option value="STORYTELLING: The Moral of the Story (4 to 6 min)">STORYTELLING: The Moral of the Story (4 to 6 min)
</option><option value="STORYTELLING: The Touching Story (6 to 8 min)">STORYTELLING: The Touching Story (6 to 8 min)
</option><option value="STORYTELLING: Bringing History to Life (7 to 9 min)">STORYTELLING: Bringing History to Life (7 to 9 min)
</option><option value="TECHNICAL PRESENTATIONS: The Technical Briefing (8 to 10 min)">TECHNICAL PRESENTATIONS: The Technical Briefing (8 to 10 min)
</option><option value="TECHNICAL PRESENTATIONS: The Proposal (8 to 10 min; 3 to 5 min with Q&amp;A)">TECHNICAL PRESENTATIONS: The Proposal (8 to 10 min; 3 to 5 min with Q&amp;A)
</option><option value="TECHNICAL PRESENTATIONS: The Nontechnical Audience (10 to 12 min)">TECHNICAL PRESENTATIONS: The Nontechnical Audience (10 to 12 min)
</option><option value="TECHNICAL PRESENTATIONS: Presenting a Technical Paper (10 to 12 min)">TECHNICAL PRESENTATIONS: Presenting a Technical Paper (10 to 12 min)
</option><option value="TECHNICAL PRESENTATIONS: Enhancing A Technical Talk With The Internet (12 to 15 min)">TECHNICAL PRESENTATIONS: Enhancing A Technical Talk With The Internet (12 to 15 min)
</option><option value="THE DISCUSSION LEADER: The Seminar Solution (20 to 30 min)">THE DISCUSSION LEADER: The Seminar Solution (20 to 30 min)
</option><option value="THE DISCUSSION LEADER: The Round Robin (20 to 30 min)">THE DISCUSSION LEADER: The Round Robin (20 to 30 min)
</option><option value="THE DISCUSSION LEADER: Pilot a Panel (30 to 40 min)">THE DISCUSSION LEADER: Pilot a Panel (30 to 40 min)
</option><option value="THE DISCUSSION LEADER: Make Believe (Role Playing) (20 to 30 min)">THE DISCUSSION LEADER: Make Believe (Role Playing) (20 to 30 min)
</option><option value="THE DISCUSSION LEADER: The Workshop Leader (30 to 40 min)">THE DISCUSSION LEADER: The Workshop Leader (30 to 40 min)
</option><option value="THE ENTERTAINING SPEAKER: The Entertaining Speech (5 to 7 min)">THE ENTERTAINING SPEAKER: The Entertaining Speech (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: Resources for Entertainment (5 to 7 min)">THE ENTERTAINING SPEAKER: Resources for Entertainment (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: Make Them Laugh (5 to 7 min)">THE ENTERTAINING SPEAKER: Make Them Laugh (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: A Dramatic Talk (5 to 7 min)">THE ENTERTAINING SPEAKER: A Dramatic Talk (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: Speaking After Dinner (8 to 10 min)">THE ENTERTAINING SPEAKER: Speaking After Dinner (8 to 10 min)
</option><option value="THE PROFESSIONAL SALESPERSON: The Winning Attitude (8 to 10 min)">THE PROFESSIONAL SALESPERSON: The Winning Attitude (8 to 10 min)
</option><option value="THE PROFESSIONAL SALESPERSON: Closing The Sale (10 to 12 min)">THE PROFESSIONAL SALESPERSON: Closing The Sale (10 to 12 min)
</option><option value="THE PROFESSIONAL SALESPERSON: Training The Sales Force (6 to 8 min speech; 8 to 10 min role play; 2 to 5 min discussion)">THE PROFESSIONAL SALESPERSON: Training The Sales Force (6 to 8 min speech; 8 to 10 min role play; 2 to 5 min discussion)
</option><option value="THE PROFESSIONAL SALESPERSON: The Sales Meeting (15 to 20 min)">THE PROFESSIONAL SALESPERSON: The Sales Meeting (15 to 20 min)
</option><option value="THE PROFESSIONAL SALESPERSON: The Team Sales Presentation (15 to 20 min plus 5 to 7 min per person for manual credit)">THE PROFESSIONAL SALESPERSON: The Team Sales Presentation (15 to 20 min plus 5 to 7 min per person for manual credit)
</option><option value="THE PROFESSIONAL SPEAKER: The Keynote Address (15 to 20 min)">THE PROFESSIONAL SPEAKER: The Keynote Address (15 to 20 min)
</option><option value="THE PROFESSIONAL SPEAKER: Speaking to Entertain (15 to 20 min)">THE PROFESSIONAL SPEAKER: Speaking to Entertain (15 to 20 min)
</option><option value="THE PROFESSIONAL SPEAKER: The Sales Training Speech (15 to 20 min)">THE PROFESSIONAL SPEAKER: The Sales Training Speech (15 to 20 min)
</option><option value="THE PROFESSIONAL SPEAKER: The Professional Seminar (20 to 40 min)">THE PROFESSIONAL SPEAKER: The Professional Seminar (20 to 40 min)
</option><option value="THE PROFESSIONAL SPEAKER: The Motivational Speech (15 to 20 min)">THE PROFESSIONAL SPEAKER: The Motivational Speech (15 to 20 min)
</option>';

}

function speech_public_details ($field) {
global $post;

		$manual = get_post_meta($post->ID, '_manual'.$field, true);
		$title = get_post_meta($post->ID, '_title'.$field, true);
		$intro = get_post_meta($post->ID, '_intro'.$field, true);
		
		if($manual)
			$output .= '<div class="manual">'.$manual."</div>";
		if($title)
			$output .= '<div class="speech_title">'.$title."</div>";
		if($intro)
			$output .= '<div class="speech_title">'.nl2br($intro)."</div>";

		return $output;
}

function speech_progress () {
global $wpdb;
global $current_user;

if($_GET["select_user"])
	{
	$user_id = $_GET["select_user"];
	$user = get_userdata($user_id);
	echo "<h2>".__('Progress Report for','rsvptoast'). " ".$user->display_name."</h2>";
	}

else
	{
	$user_id = $current_user->ID;
	echo "<h2>".__('Progress Report for You','rsvptoast'). "</h2>";
	}

echo '<p><form method="get" action="'.admin_url('edit.php').'"><input type="hidden" name="post_type" value="rsvpmaker"><input type="hidden" name="page" value="speech_progress">'.awe_user_dropdown('select_user',0,true).'<input type="submit" value="'.__('Get','rsvptoast').'" /></form></p>'."\n";

echo "<h2>".__('Speeches','rsvptoast')."</h2>";

$sql = "SELECT *
FROM `".$wpdb->prefix."postmeta`
JOIN ".$wpdb->prefix."rsvp_dates ON ".$wpdb->prefix."postmeta.post_id = ".$wpdb->prefix."rsvp_dates.postID
WHERE meta_key LIKE '_Speaker%'
AND meta_value = '$user_id' AND datetime < NOW()
ORDER BY `datetime` DESC";

$results = $wpdb->get_results($sql);
foreach($results as $row)
	{
		$manual = $wpdb->get_var("SELECT meta_value FROM ".$wpdb->prefix."postmeta WHERE meta_key = '_manual".$row->meta_key."' AND post_id=".$row->post_id);
		$date = date('M jS',strtotime($row->datetime));
		if(!$manual || strpos($manual,'Manual / Speech') )
			{
			$permalink = rsvpmaker_permalink_query($row->post_id);
			if($_GET["select_user"])
				$permalink .= 'edit_roles=1';
			$manual = 'Speech details not recorded (<a href="'.$permalink.'">set now?</a>)';
			}
		echo $manual . " - ".$date .'<br /><br />'; 
	}

echo "<h2>".__("Other Roles",'rsvptoast')."</h2>\n";

$sql = "SELECT *
FROM `".$wpdb->prefix."postmeta`
JOIN ".$wpdb->prefix."rsvp_dates ON ".$wpdb->prefix."postmeta.post_id = ".$wpdb->prefix."rsvp_dates.postID
WHERE meta_key NOT LIKE '%Speaker%' AND meta_key NOT LIKE '_edit_last'
AND meta_value = '$user_id' AND datetime < NOW()
ORDER BY `datetime` DESC";

$results = $wpdb->get_results($sql);
foreach($results as $row)
	{
		$date = date('M jS',strtotime($row->datetime));
		$role = str_replace('_',' ',$row->meta_key);
		$role = preg_replace('/ [1-9]/','',$role);
		printf('<p>%s - %s</p>',$role,$date);
	}

$wpdb->show_errors();

$sql = "SELECT * FROM wpcc_postmeta where meta_key LIKE '%Speaker%' AND concat('',meta_value * 1) = meta_value";

$sql = "SELECT *
FROM `".$wpdb->prefix."postmeta`
JOIN ".$wpdb->prefix."rsvp_dates ON ".$wpdb->prefix."postmeta.post_id = ".$wpdb->prefix."rsvp_dates.postID
WHERE meta_key LIKE '%Speaker%' AND concat('',meta_value * 1) = meta_value
AND datetime < NOW()
ORDER BY meta_value DESC";

$results = $wpdb->get_results($sql);
foreach($results as $row)
	{
		$role = str_replace('_',' ',$row->meta_key);
		$role = preg_replace('/ [1-9]/','',$role);
		$done[$row->meta_value][$role]++;
		//printf('<p>%s - %s</p>',$role,$date);
	}

foreach($done as $index => $roles)
	{
		echo "<p>".__("user",'rsvptoast').": ".$index;
		print_r($roles);
		echo "</p>\n";
	}

}

function extended_list () {

echo "<p><em>".__('This version of the member list includes inactive members','rsvptoast')."</em></p>";

global $wpdb;

if($_POST)
{
	$user = stripslashes_deep($_POST["user"]);
	$user["display_name"] = $user["first_name"].' '.$user["last_name"];
	$userdata = (object) $user;
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name);
	$sql = $wpdb->prepare("REPLACE INTO ".$wpdb->prefix."users_archive SET data=%s, sort=%s", serialize($userdata),$index);
	$wpdb->show_errors();
	$wpdb->query($sql);
}

$contactmethods['home_phone'] = __("Home Phone",'rsvptoast');
$contactmethods['work_phone'] = __("Work Phone",'rsvptoast');
$contactmethods['mobile_phone'] = __("Mobile Phone",'rsvptoast');
$contactmethods['facebook_url'] = __("Facebook Profile","rsvptoast");
$contactmethods['twitter_url'] = __("Twitter Profile",'rsvptoast');
$contactmethods['linkedin_url'] = __("LinkedIn Profile",'rsvptoast');
$contactmethods['business_url'] = __("Business Web Address",'rsvptoast');
$contactmethods['user_email'] = __("Email",'rsvptoast');
$former_list = $email_list = '';

$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."users_archive ORDER BY sort");
foreach($results as $row)
	{
		$userdata = unserialize($row->data);
		$email_list .= $userdata->user_email.", ";
		if(!get_user_by('email',$userdata->user_email))
			$former_list .= $userdata->user_email.", ";

?>	
<div class="member-entry" style="margin-bottom: 50px; clear: both;">
<p><strong><?php if($userdata->first_name) { echo $userdata->first_name.' '.$userdata->last_name;} else echo $userdata->display_name; ?></strong></p>
<?php

	foreach($contactmethods as $name => $value)
		{
		if(strpos($name,'phone'))
			{
			if( (!$public_context) && $userdata->$name )
				printf("<div>%s: %s</div>",$value,$userdata->$name);
			}
		if(strpos($name,'url'))
			{
			if( $userdata->$name && strpos($userdata->$name,'://') )
				printf('<div><a target="_blank" href="%s">%s</a></div>',$userdata->$name,$value);
			}
		}
		if(!$public_context || $userdata->public_email)
				{
				$clubemail[] = $userdata->user_email;
				printf('<div>'.__('Email','rsvptoast').': <a href="mailto:%s">%s</a></div>',$userdata->user_email,$userdata->user_email);
				}
		
		if($userdata->user_description)
			echo wpautop('<strong>'.__('About Me','rsvptoast').':</strong> '.$userdata->user_description);

?>
</div>
<?php

	}

echo "<p>".__('Combined email','rsvptoast').": $email_list</p>";
echo "<p>".__('Former member emails','rsvptoast').": $former_list</p>";

?>
<form action="<?php echo admin_url('users.php?page=extended_list'); ?>" method="post">
<?php _e("Add Entry",'rsvptoast');?>
<br /><?php _e("First",'rsvptoast');?> <input name="user[first_name]">
<br /><?php _e("Last",'rsvptoast');?> <input name="user[last_name]">
<br /><?php _e("Email",'rsvptoast');?> <input name="user[user_email]">
<br /><?php _e("Home",'rsvptoast');?> <input name="user[home_phone]">
<br /><?php _e("Work",'rsvptoast');?> <input name="user[work_phone]">
<br /><?php _e("Mobile",'rsvptoast');?> <input name="user[mobile_phone]">
<br /><input type="submit" value="<?php _e("Add Ex-Member Record",'rsvptoast');?>">
</form>
<?php

}

function edit_members() {

$blogusers = get_users('blog_id=1&orderby=nicename');
    foreach ($blogusers as $user) {		

	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}
		
	$userdata = get_userdata($user->ID);
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);
	$members[$index] = $userdata;
	}
	
	ksort($members);
	foreach($members as $userdata) {
	printf('<p><a href="'.admin_url('user-edit.php?user_id').'=%d">%s %s</p></p>',$userdata->ID, $userdata->first_name, $userdata->last_name);
	}
}

function awesome_menu() {
add_submenu_page('edit.php?post_type=rsvpmaker', __("Agenda Setup",'rsvptoast'), __("Agenda Setup",'rsvptoast'), 'edit_rsvpmakers', "agenda_setup", "agenda_setup");

add_submenu_page('profile.php', __("Add Member",'rsvptoast'), __("Add Member",'rsvptoast'), 'edit_others_posts', "add_awesome_member", "add_awesome_member" );

add_submenu_page('profile.php', __("Edit Members",'rsvptoast'), __("Edit Members",'rsvptoast'), 'edit_users', "edit_members", "edit_members" );

add_submenu_page('profile.php', __("Extended List",'rsvptoast'), __("Extended List",'rsvptoast'), 'edit_posts', "extended_list", "extended_list" );

$page_title = "Toastmasters";
$menu_title = $page_title;
$capability = "manage_options";
$menu_slug = "wp4toastmasters_settings";
$function = "wp4toastmasters_settings";
add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
}

add_action('admin_menu', 'awesome_menu');

function wp4toastmasters_settings() {
$officer = get_role('officer');
if(!$officer)
	add_awesome_roles();
?>
<div class="wrap">
<h2>Toastmasters <?php _e("Settings",'rsvptoast');?></h2>
<form method="post" action="options.php">
<?php settings_fields( 'wp4toastmasters-settings-group' );
$wp4toastmasters_officer_ids = get_option('wp4toastmasters_officer_ids');
$wp4toastmasters_officer_titles = get_option('wp4toastmasters_officer_titles');
if(!is_array($wp4toastmasters_officer_titles) )
	$wp4toastmasters_officer_titles = array(__("President",'rsvptoast'),__("VP of Education",'rsvptoast'),__("VP of Membership",'rsvptoast'),__("VP of Public Relations",'rsvptoast'),__("Treasurer",'rsvptoast'),__("Secretary",'rsvptoast'),__("Sgt. at Arms",'rsvptoast'),__("Immediate Past President",'rsvptoast'));
$wp4toastmasters_member_message = get_option('wp4toastmasters_member_message');
$wp4toastmasters_officer_message = get_option('wp4toastmasters_officer_message');
$wp4toastmasters_disable_email = get_option('wp4toastmasters_disable_email' );
if(empty($wp4toastmasters_disable_email))
	$wp4toastmasters_disable_email = 0;
$wp4toastmasters_agenda_layout = get_option('wp4toastmasters_agenda_layout');
$wp4toastmasters_welcome_message = get_option('wp4toastmasters_welcome_message');
	
$public = get_option('blog_public');

?>
<h3><?php _e("Make the Website Public",'rsvptoast');?></h3>
<p><input type="radio" name="blog_public" value="1" <?php if($public) echo ' checked="checked" '; ?> /> <?php _e("Yes, this website is open for business!",'rsvptoast');?></p>
<p><input type="radio" name="blog_public" value="0" <?php if(!$public) echo ' checked="checked" '; ?> /> <?php _e("No, I am still testing, so I don't want this site indexed by Google or other search engines",'rsvptoast');?>.</p>

<h3><?php _e("Enable Email Functions",'rsvptoast');?></h3>
<p><input type="radio" name="wp4toastmasters_disable_email" value="0" <?php if(!$wp4toastmasters_disable_email) echo ' checked="checked" '; ?> /> <?php _e("Yes, we email list is active",'rsvptoast');?></p>
<p><input type="radio" name="wp4toastmasters_disable_email" value="1" <?php if($wp4toastmasters_disable_email) echo ' checked="checked" '; ?> /> <?php _e("No, do not sent automated email from the website",'rsvptoast');?>.</p>

<?php
$tzstring = get_option('timezone_string');
if(empty($tzstring) )
	echo "<p>".__('Timezone not set - defaults to UTC 0 (UK time). Scroll to the top of the list for U.S. timezones','rsvptoast')."</p>";

$current_offset = get_option('gmt_offset');

$check_zone_info = true;

// Remove old Etc mappings. Fallback to gmt_offset.
if ( false !== strpos($tzstring,'Etc/GMT') )
	$tzstring = '';

if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if ( 0 == $current_offset )
		$tzstring = 'UTC+0';
	elseif ($current_offset < 0)
		$tzstring = 'UTC' . $current_offset;
	else
		$tzstring = 'UTC+' . $current_offset;
}

?>
<h3><?php _e("Timezone",'rsvptoast');?></h3>
<p><label for="timezone_string"><?php _e('Timezone') ?></label>
<select id="timezone_string" name="timezone_string">
<optgroup label="U.S. Mainland">
<option value="America/New_York">New York</option>
<option value="America/Chicago">Chicago</option>
<option value="America/Denver">Denver</option>
<option value="America/Los_Angeles">Los Angeles</option>
</optgroup>
<?php echo wp_timezone_choice($tzstring); ?>
</select>
<br /><?php _e('Choose a city in the same timezone as you.'); ?>
</p>

<h3><?php _e("Officer List",'rsvptoast');?></h3>
<?php

foreach($wp4toastmasters_officer_titles as $index => $title)
{
	if(empty($title))
		break;
	$dropdown = awe_user_dropdown ('wp4toastmasters_officer_ids['.$index.']', $wp4toastmasters_officer_ids[$index], true);
	printf('<p><input type="text" name="wp4toastmasters_officer_titles[%s]" value="%s" /> %s</p>', $index, $title, $dropdown);
}
$limit = $index + 3;
for($index = $index; $index < $limit; $index++)
	{
	$dropdown = awe_user_dropdown ('wp4toastmasters_officer_ids['.$index.']', 0, true);
	printf('<p><input type="text" name="wp4toastmasters_officer_titles[%s]" value="%s" /> %s</p>', $index, '', $dropdown);
	}
?>
<p><?php _e("Officers will be listed at the top of the members page, be assigned editing rights on the website, and have access to the screens for adding or editing member (user) records",'rsvptoast');?>.</p>
<?php
if(current_user_can('update_core'))
{
// restrict this to network admin on multisite
$wp4toastmasters_mailman = get_option('wp4toastmasters_mailman');
echo get_option('wp4toastmasters_mailman_default');

?>
<h3><?php _e("Member Email List",'rsvptoast');?></h3>
<p><?php _e("List email address",'rsvptoast');?>: <input type="text" name="wp4toastmasters_mailman[members]" value="<?php if(isset($wp4toastmasters_mailman["members"])) echo $wp4toastmasters_mailman["members"]; ?>" /></p>
<p><?php _e("Path",'rsvptoast');?>: <input type="text" name="wp4toastmasters_mailman[mpath]" value="<?php if(isset($wp4toastmasters_mailman["mpath"])) echo $wp4toastmasters_mailman["mpath"]; ?>" /> <?php _e("Password",'rsvptoast');?>: <input type="text" name="wp4toastmasters_mailman[mpass]" value="<?php if(isset($wp4toastmasters_mailman["mpass"])) echo $wp4toastmasters_mailman["mpass"]; ?>" /></p>
<?php if(isset($wp4toastmasters_mailman["mpass"])) {
	printf('<p><a href="%s&mailman_add_members=1">'.__('Add current members to mailing list','rsvptoast').'</a></p>',admin_url('options-general.php?page=wp4toastmasters_settings'));
	}

if($_GET["mailman_add_members"])
{
    $users = get_users();
	foreach ($users as $user) {
		add_to_mailman($user->ID);
	}
}

if($_GET["mailman_add_officers"])
{
    foreach ($wp4toastmasters_officer_ids as $user_id) {
		add_to_mailman($user_id);
	}
}

?>

<h3><?php _e("Officer Email List",'rsvptoast');?></h3>
<p><?php _e("List email address",'rsvptoast');?>: <input type="text" name="wp4toastmasters_mailman[officers]" value="<?php if(isset($wp4toastmasters_mailman["officers"])) echo $wp4toastmasters_mailman["officers"]; ?>" /></p>
<p><?php _e("Path",'rsvptoast');?>: <input type="text" name="wp4toastmasters_mailman[opath]" value="<?php if(isset($wp4toastmasters_mailman["opath"])) echo $wp4toastmasters_mailman["opath"]; ?>" /> <?php _e("Password",'rsvptoast');?>: <input type="text" name="wp4toastmasters_mailman[opass]" value="<?php if(isset($wp4toastmasters_mailman["opass"])) echo $wp4toastmasters_mailman["opass"]; ?>" />

<?php if(isset($wp4toastmasters_mailman["opass"])) {
	printf('<p><a href="%s&mailman_add_officers=1">'.__("Update officers mailing list",'rsvptoast').'</a></p>',admin_url('options-general.php?page=wp4toastmasters_settings'));
	}

if($_GET["mailman_add_officers"])
{
    foreach ($wp4toastmasters_officer_ids as $user_id) {

		$user = get_userdata($user_id);
		$email = $user->user_email;
		$url = trailingslashit($wp4toastmasters_mailman["opath"])."members?findmember=".$email."&setmemberopts_btn&adminpw=".$wp4toastmasters_mailman["opass"];
		$result = file_get_contents($url);
		if(!strpos($result, 'CHECKBOX') )
			{
			$url = trailingslashit($wp4toastmasters_mailman["opath"])."add?subscribe_or_invite=0&send_welcome_msg_to_this_batch=0&notification_to_list_owner=0&subscribees_upload=".$email."&adminpw=".$wp4toastmasters_mailman["opass"];;
		$result = file_get_contents($url);
		if(!strpos($result, 'Successfully') )
			echo "<div>".__('Error attempting to subscribe','rsvptoast')." $email</div>";
			}
	}
}

}

?>

<p><?php _e("Message To Members on Dashboard",'rsvptoast');?><br />
<textarea name="wp4toastmasters_member_message" rows="5" cols="80"><?php echo $wp4toastmasters_member_message; ?></textarea></p>

<p><?php _e("Message To Officers on Dashboard",'rsvptoast');?><br />
<textarea name="wp4toastmasters_officer_message" rows="5" cols="80"><?php echo $wp4toastmasters_officer_message; ?></textarea></p>

<?php 
$reminder_options = array('4 hours' => '4 hours before','8 hours' => '8 hours before','1 days' => '1 day before','2 days' => '2 days before','3 days' => '3 days');

$wp4toast_reminder = get_option('wp4toast_reminder');
$options = '';
foreach($reminder_options as $index => $value)
	{
	if($index == $wp4toast_reminder)
		$s = ' selected="selected" ';
	else
		$s = '';
	$options .= sprintf('<option value="%s" %s>%s</option>',$index, $s, $value);
	}

?>

<p><?php _e("Email Reminder",'rsvptoast');?> 
<select name="wp4toast_reminder">
<option value=""><?php _e("None",'rsvptoast');?></option>
<?php echo $options; ?>
</select>
</p>
<?php
$layout_options = array('plain','sidebar');
$options = '';
foreach($layout_options as $value)
	{
	if($value == $wp4toastmasters_agenda_layout)
		$s = ' selected="selected" ';
	else
		$s = '';
	$options .= sprintf('<option value="%s" %s>%s</option>',$value, $s, $value);
	}
?>
<p><?php _e("Agenda Layout",'rsvptoast');?> 
<select name="wp4toastmasters_agenda_layout">
<?php echo $options; ?>
</select>
</p>

<?php
$args = array('post_type' => 'page','orderby' => 'title','order' => 'ASC','posts_per_page' => 50);
$posts = get_posts($args);
$options = '<option value="">None</option>';
foreach($posts as $p)
	{
	if($p->ID == $wp4toastmasters_welcome_message)
		$s = ' selected="selected" ';
	else
		$s = '';
	$options .= sprintf('<option value="%s" %s>%s</option>',$p->ID, $s, $p->post_title);
	}
?>

<p><?php _e("Page Containing Welcome Message",'rsvptoast');?> 
<select name="wp4toastmasters_welcome_message">
<?php echo $options; ?>
</select>
</p>

<input type="submit" value="<?php _e("Submit",'rsvptoast');?>" />
</form>
</div>
<?php 

}

//call register settings function
add_action( 'admin_init', 'register_wp4toastmasters_settings' );

function register_wp4toastmasters_settings() {
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_officer_titles' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_officer_ids' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_mailman' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_member_message' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_officer_message' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_disable_email' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toast_reminder' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_agenda_layout' );
	register_setting( 'wp4toastmasters-settings-group', 'wp4toastmasters_welcome_message' );
	register_setting( 'wp4toastmasters-settings-group', 'timezone_string' );
	register_setting( 'wp4toastmasters-settings-group', 'blog_public' );
}

add_action('user_register','add_to_mailman');
add_action('profile_update', 'add_to_mailman');

function add_to_mailman($user_id, $olduser = NULL)
	{
		$wp4toastmasters_mailman = get_option('wp4toastmasters_mailman');
		if(!isset($wp4toastmasters_mailman["mpath"]) || empty($wp4toastmasters_mailman["mpath"]) || !isset($wp4toastmasters_mailman["mpass"]) || empty($wp4toastmasters_mailman["mpass"]) )
			return;
		$user = get_userdata($user_id);
		$email = $user->user_email;
		$url = trailingslashit($wp4toastmasters_mailman["mpath"])."members?findmember=".$email."&setmemberopts_btn&adminpw=".$wp4toastmasters_mailman["mpass"];
		$result = file_get_contents($url);
		if(!strpos($result, 'CHECKBOX') )
			{
			$url = trailingslashit($wp4toastmasters_mailman["mpath"])."add?subscribe_or_invite=0&send_welcome_msg_to_this_batch=0&notification_to_list_owner=0&subscribees_upload=".$email."&adminpw=".$wp4toastmasters_mailman["mpass"];;
		$result = file_get_contents($url);
		if(!strpos($result, 'Successfully') )
			echo "<div>".__('Error attempting to subscribe','rsvptoast')." $email</div>";
			}
}

add_filter('the_content','awesome_event_content');

function awesome_event_content($content) {

if(is_admin())
	return $content;

global $post;

if($_GET["recommendation"])
	{
		if($_GET["recommendation"] == 'success')
			$link = '<div style="border: thin solid #00F; padding: 10px; margin: 10px; background-color: #eee;">'.__('You have accepted a role for this meeting. Thanks!','rsvptoast').'</div>';
		elseif($_GET["recommendation"] == 'code_error')
			$link = '<div style="border: thin solid #F00; padding: 10px; margin: 10px; background-color: #eee;">'.__('Oops, something went wrong with the automatic sign up. Please sign in with your password to take a role','rsvptoast').'</div>';		
		else
			$link = '<div style="border: thin solid #F00; padding: 10px; margin: 10px; background-color: #eee;">'.__('Oops, someone else took that role first. Sign in to take any other open role listed below','rsvptoast').'</div>';
	}

if(($post->post_type != 'rsvpmaker') || !strpos($post->post_content,'role=') )
	return $content;
$permalink = rsvpmaker_permalink_query($post->ID);

if($_GET["email_agenda"] || $_GET["print_agenda"] )
	;
elseif( !is_user_member_of_blog() )
	$link .= sprintf('<div id="agendalogin"><a href="%s">'.__('Login to Sign Up for Roles','rsvptoast').'</a></div>',site_url().'/wp-login.php?redirect_to='.urlencode($permalink));
else
	{
	if(current_user_can('edit_rsvpmakers'))
		{
		$role_editor_url = admin_url('edit.php?post_type=rsvpmaker&page=agenda_setup&post_id='.$post->ID);
		$role_editor = ' <a href="'.$role_editor_url.'">'.__('Agenda Setup','rsvptoast').'</a>';
		}
	else
		$role_editor = '';
	$link .= '<div id="agenda_print"><a href="'.$permalink.'edit_roles=1">'.__('Edit Signups','rsvptoast').'</a> ';
	$link .= '<a href="'.$permalink.'recommend_roles=1">'.__('Recommend','rsvptoast').'</a> ';
	$link .= '<a  target="_blank" href="'.$permalink.'email_agenda=1">'.__('Email','rsvptoast').'</a> ';
	$link .= '<a  target="_blank" href="'.site_url('?signup2=1').'">'.__('Signup Sheet','rsvptoast').'</a> ';
	$link .= '<a target="_blank" href="'.$permalink.'print_agenda=1">'.__('Agenda','rsvptoast').'</a> ';
	$link .= '<a target="_blank" href="'.$permalink.'print_agenda=1&word_agenda=1">'.__('Export to Word','rsvptoast').'</a>'.$role_editor.'</div>';

if($_POST["editor_suggest"])
	{
		global $wpdb;
		global $current_user;
		$code = get_post_meta($post->ID,'suggest_code', true);
		if(!$code)
			{
				$code = wp_generate_password();
				update_post_meta($post->ID,'suggest_code',$code);
			}
		foreach($_POST["editor_suggest"] as $name => $value)
			{
			$count = (int) $_POST["editor_suggest_count"][$name];
			if($value < 1)
				continue;
			$invite_check = $value.":".$post->ID;
			if($_SESSION[$invite_check]) // prevent double notifications
				continue;
			$_SESSION[$invite_check] = 1;
			
			$date = $wpdb->get_var("SELECT DATE_FORMAT(datetime,'%M %d, %Y') FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post->ID. ' ORDER BY datetime');
			$neatname = preg_replace('/[_\-0-9]/',' ',$name);
			$user = get_userdata($current_user->ID);
			$msg = sprintf('<p>Toastmaster %s %s %s %s %s %s</p>',$user->first_name,$user->last_name,__('has recomended you for the role of','rsvptoast'),$neatname, __('for','rsvptoast'),$date);
			$member = get_userdata($value);
			$email = $member->user_email;
			$hash = recommend_hash($name, $value);
			$url = $permalink.sprintf('key=%s&you=%s&code=%s&count=%s',$name,$value,$hash,$count);
			$msg .= sprintf("\n\n".__('<p>Click here to <a href="%s">ACCEPT</a> (no password required if you act before someone else takes this role)</p>','rsvptoast'),$url);
			if($_POST["editor_suggest_note"][$name])
				$msg .= "\n\n<p><b>".__('Note from','rsvptoast')." ".$user->first_name.' '.$user->last_name.": </b>".$_POST["editor_suggest_note"][$name].'</p>';
			$mail["html"] = $msg;
			$mail["to"] = $email;
			$mail["from"] = $user->user_email;
			$mail["cc"] = $user->user_email;
			$mail["fromname"] = $user->first_name." ".$user->last_name;
			$mail["subject"] = "You have been recommended for the role of ".$neatname.' on '.$date;
			awemailer($mail);
			$output = '<div style="background-color: #eee; border: thin solid #000; padding: 5px; margin-5px;">'.$msg.'<p><em>'.__('Sent by email to','rsvptoast').' <b>'.$email."</b></em></p></div>";
			}
	}

if($_POST["editor_assign"] && current_user_can('edit_posts') )
	{
	global $wpdb;
	$wpdb->show_errors();
	$date = $wpdb->get_var("SELECT datetime FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post->ID);
	$sql = "SELECT *, DATE_FORMAT(datetime,'%M %D') as eventdate FROM ".$wpdb->prefix."rsvp_dates WHERE datetime > '".$date."' AND DATE_FORMAT(datetime,'%w') = 5 ORDER BY datetime LIMIT 0,3";
	$results = $wpdb->get_results($sql);
	foreach($results as $row)
		{
		$link .= sprintf('<div id="agenda_print"><a href="%s">'.__('Edit Agenda Roles for','rsvptoast').' %s</a></div>',rsvpmaker_permalink_query($row->postID).'edit_roles=1',$row->eventdate);
		}
	
	}

	}
return $output.$link.$content;

}

function awesome_members() {
ob_start();
global $wpdb;

if($_POST["status"] && is_user_member_of_blog() )
	{
		$id = (int) $_POST["member_id"];
		$result = update_user_meta($id,'status',stripslashes($_POST["status"]) );
		if($_POST["status_expires"])
			$result = update_user_meta($id,'status_expires',strtotime($_POST["status_expires"]) );
	}
if($_POST["remove_status"])
	{
		$id = (int) $_POST["member_id"];
		$result = delete_user_meta($id,'status');
		$result = delete_user_meta($id,'status_expires');
	}

$wp4toastmasters_officer_ids = get_option('wp4toastmasters_officer_ids');
$wp4toastmasters_officer_titles = get_option('wp4toastmasters_officer_titles');

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {		
	
	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}
	
	$userdata = get_userdata($user->ID);
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);

	if(is_array($wp4toastmasters_officer_ids) && in_array($user->ID,$wp4toastmasters_officer_ids))
		{
		$officers[array_search($user->ID,$wp4toastmasters_officer_ids) ] = $userdata;
		$officer_emails[] = $userdata->user_email;
		}
	else
		{
		$members[$index] = $userdata;
		$clubemails[] = $userdata->user_email;
		}
	}
	
	if($_GET["print_contacts"] && is_array($members))
	{
	ksort($members);
	foreach($members as $userdata)
	{	
	if($userdata->user_login != '0_NOT_AVAILABLE' )
		print_display_member($userdata);
	}
	return;
	}

	if(is_user_member_of_blog() )
		echo '<p><em>'.__('Contact details such as phone numbers and email are only displayed when you are logged into the website (and should only be used for Toastmasters business)','rsvptoast').'.</em><br />'.__('Related','rsvptoast').': <a href="'.site_url().'?print_contacts=1">'.__('Print Contact List','rsvptoast').'</a></p>';
	else
		printf( '<p><em>%s <a href="%s">%s</a>.</em></p>',__('These members have chosen to create public profiles. For an expanded listing, members may','rsvptoast'),login_redirect($_SERVER['REQUEST_URI']), __('login','rsvptoast'));
		
	if(is_array($officers))
	{
		ksort($officers);
		foreach($officers as $officer_index => $officer)
			display_member($officer,$wp4toastmasters_officer_titles[$officer_index]);
	}
	if(is_array($members))
	{
	ksort($members);
	foreach($members as $userdata)
		if($userdata->user_login != '0_NOT_AVAILABLE' )
		display_member($userdata);
	}

if(is_user_member_of_blog())
		{
			if(is_array($officer_emails) ) {
				$o = implode(',',$officer_emails);
			}
			if(is_array($clubemails) ) {
				$c = implode(',',$clubemails);
				if(isset($o))
					$c .= ','.$o;
				printf('<p><a href="mailto:%s?subject=Toastmasters">'.__('Email All','rsvptoast').'</a></p>',$c );
			}
			if(isset($o))
				printf('<p><a href="mailto:%s?subject=Toastmasters">'.__('Email Officers','rsvptoast').'</a></p>',$o );
		}

return ob_get_clean();
}

function display_member($userdata, $title='')
	 {
	global $post;

$contactmethods['home_phone'] = __("Home Phone",'rsvptoast');
$contactmethods['work_phone'] = __("Work Phone",'rsvptoast');
$contactmethods['mobile_phone'] = __("Mobile Phone",'rsvptoast');
$contactmethods['facebook_url'] = __("Facebook Profile","rsvptoast");
$contactmethods['twitter_url'] = __("Twitter Profile",'rsvptoast');
$contactmethods['linkedin_url'] = __("LinkedIn Profile",'rsvptoast');
$contactmethods['business_url'] = __("Business Web Address",'rsvptoast');
$contactmethods['user_email'] = __("Email",'rsvptoast');

$default_expires = date('Y-m-d',strtotime('+1 Month'));
	if(is_user_member_of_blog() )
		$public_context = false;
	elseif(!empty($title) || strtolower(trim($userdata->public_profile)) == 'yes')
		$public_context = true;
	else
		return;
?>
<div class="member-entry" style="margin-bottom: 50px; clear: both;">
<?php
if(function_exists('has_wp_user_avatar') && has_wp_user_avatar($userdata->ID))
{
?>	
<div style="float: right; margin-left: 15px; width: 200px;">
<img src="<?php echo get_wp_user_avatar_src($userdata->ID, 96); ?>" alt=""  />
</div>
<?php
}
elseif(function_exists('userphoto_exists') && userphoto_exists($userdata))
{
?>	
<div style="float: right; margin-left: 15px; width: 200px;">
<?php
		userphoto($userdata);
?>
</div>
<?php
}

if(!empty($title))
	printf('<h3 style="clear: none;">%s</h3>',$title);
?>
<p id="member_<?php echo $userdata->ID; ?>"><strong><?php echo $userdata->first_name.' '.$userdata->last_name?></strong> <?php if(!empty($userdata->education_awards)) echo '('.$userdata->education_awards.')'; ?></p>
<?php

	foreach($contactmethods as $name => $value)
		{
		if(strpos($name,'phone'))
			{
			if( (!$public_context) && $userdata->$name )
				printf("<div>%s: %s</div>",$value,$userdata->$name);
			}
		if(strpos($name,'url'))
			{
			if( $userdata->$name && strpos($userdata->$name,'://') )
				printf('<div><a target="_blank" href="%s">%s</a></div>',$userdata->$name,$value);
			}
		}
		
		if(!$public_context || $userdata->public_email)
				{
				$clubemail[] = $userdata->user_email;
				printf('<div>'.__('Email','rsvptoast').': <a href="mailto:%s">%s</a></div>',$userdata->user_email,$userdata->user_email);
				}
		
		if($userdata->user_description)
			echo wpautop('<strong>'.__('About Me','rsvptoast').':</strong> '. add_implicit_links($userdata->user_description));
		if( !$public_context && !$_GET["email_prompt"] )
			{
			//get_user_meta($id,'status',true );
			if(isset($userdata->status_expires) )
				{
				$exp = (int) $userdata->status_expires;
				
				if(mktime() > $exp)
					{
					delete_user_meta($userdata->ID,'status');
					delete_user_meta($userdata->ID,'status_expires');
					$expires = $default_expires;
					$userdata->status = '';
					}
				else
					$expires = date('Y-m-d',$exp);
				}
			else
				$expires = $default_expires;
			printf('<p> 
			<form action="'.get_permalink($post->ID).'" method="post"><input name="member_id" type="hidden" value="%d" />Status<br /><textarea name="status" cols="60" rows="1">%s</textarea>
			<br />'.__('Status Expires','rsvptoast').': <input type="" name="status_expires" value="%s" /> '.__('Year-Month-Day','rsvptoast').'<br />
			<input type="submit" name="submit" value="'.__('Submit','rsvptoast').'" /></form></p>',$userdata->ID,$userdata->status, $expires);
			if(isset($userdata->status))
			printf('<form action="'.get_permalink($post->ID).'" method="post"><input name="member_id" type="hidden" value="%d" /><input type="submit" name="remove_status" value="'.__('Clear Status','rsvptoast').'" /></form>',$userdata->ID,$userdata->status);			
			}

?>
</div>
<?php

}

add_shortcode('awesome_members','awesome_members');

function add_awesome_member() {

global $wpdb;
$blog_id = get_current_blog_id();

if($_POST["remove_user"]) {
	foreach($_POST["remove_user"] as $user_id)
		{
		remove_user_from_blog( $user_id, $blog_id );
		}
}

if($_POST["spreadsheet"]) {

$lines = explode("\n", $_POST["spreadsheet"]);
$label = array();
foreach($lines as $linenumber => $line)
	{
	$cells = explode("\t",$line);
	if($linenumber == 0)
		{
		foreach($cells as $index => $cell)
			{
				$label[trim($cell)] = $index;
			}
		}
	else
	{
	if(empty($cells[0]))
		break;
	$user = array();
	if(isset($label["First Name"]))
		{
		$user["first_name"] = $cells[$label["First Name"]];
		$user["last_name"] = $cells[$label["Last Name"]];
		}
	elseif(isset($label["First"]))
		{
		$user["first_name"] = $cells[$label["First"]];
		$user["last_name"] = $cells[$label["Last"]];
		}
	elseif(isset($label["Name"]))
		{
			$user = name2fields($cells[$label["Name"]]);
		}

	if($cells[$label["Edu."]])
		$user["education_awards"] = $cells[$label["Edu."]];		
		
	if(isset($label["E-mail"]))
		$user["user_email"] = $cells[$label["E-mail"]];
	elseif(isset($label["Email"]))
		$user["user_email"] = $cells[$label["Email"]];

	$user["user_login"] = preg_replace('/[^a-z]/','',strtolower($user["first_name"].$user["last_name"]));
	$user["nickname"] = $user["display_name"] = $user["first_name"].' '.$user["last_name"];

	if(isset($label["Home Phone"]))
		$user["home_phone"] = $cells[$label["Home Phone"]];
	elseif(isset($label["Home"]))
		$user["home_phone"] = $cells[$label["Home"]];

	if(isset($label["Work Phone"]))
		$user["work_phone"] = $cells[$label["Work Phone"]];
	elseif(isset($label["Work"]))
		$user["work_phone"] = $cells[$label["Work"]];

	if(isset($label["Cell"]))
		$user["mobile_phone"] = $cells[$label["Cell"]];
	elseif(isset($label["Cell Phone"]))
		$user["mobile_phone"] = $cells[$label["Cell Phone"]];
	elseif(isset($label["Mobile Phone"]))
		$user["mobile_phone"] = $cells[$label["Mobile Phone"]];
	elseif(isset($label["Mobile"]))
		$user["mobile_phone"] = $cells[$label["Mobile"]];

	if(isset($label["Member #"]))
		$user["toastmasters_id"] = $cells[$label["Member #"]];
	elseif(isset($label["Member ID"]))
		$user["toastmasters_id"] = $cells[$label["Member ID"]];
	elseif(isset($label["ID"]))
		$user["toastmasters_id"] = $cells[$label["ID"]];
	elseif(isset($label["CustomerID"]))
		$user["toastmasters_id"] = $cells[$label["CustomerID"]];
	
	if(isset($_POST["user_pass"]) && !empty($_POST["user_pass"]) )
		$user["user_pass"] = $_POST["user_pass"];
	else
		$user["user_pass"] = password_hurdle(wp_generate_password());
	
	$active_ids[] = add_member_user($user);
	}
	//break;
	}
if($_POST["check_missing"])
	no_member_match ($active_ids);
}

if($_POST["paste"])
{
$o = preg_split("/(Active|Graced|Grace|Status)/", stripslashes($_POST["paste"]));
echo "<pre>Lines\n";
print_r($o);
echo "</pre>";
$trow = 0;
foreach ($o as $line)
	{
		$cells = explode("\t",$line);
		echo "<pre>Cells\n";
		print_r($cells);
		echo "</pre>";
		$toastmasters_id = trim($cells[0]);
		if(!is_numeric($toastmasters_id))
				continue;
		$member_name = trim($cells[1]);
		$contact_text = trim($cells[2]);
		preg_match_all('/([A-Z][a-z]{3,4}): (.*)/',$contact_text, $matches);
		$namefields = name2fields($member_name);
		$contact = extract_fields_tm($matches);
		if(!$toastmasters_id)
			continue;
		$contact["toastmasters_id"] = $toastmasters_id;
		$contact["user_pass"] = $_POST["user_pass"];
		$user = array_merge($contact,$namefields);
		$active[] = add_member_user($user);
	}
if($_POST["check_missing"])
	no_member_match ($active_ids);
}

if($_POST["first_name"] && $_POST["last_name"] && $_POST["email"])
	{
	$user["user_login"] = trim($_POST["user_login"]);
	if(empty($_POST["email"]))
		{
		echo __("Both a user name and an email must be supplied",'rsvptoast');
		continue;
		}
	$user["user_email"] = trim($_POST["email"]);
		$user["user_pass"] = $_POST["user_pass"];
		$user["first_name"] = $_POST["first_name"];
		$user["last_name"] = $_POST["last_name"];
		$user["nickname"] = $user["display_name"] = $_POST["first_name"].' '.$_POST["last_name"];
		$user["home_phone"] = $_POST["home_phone"];
		$user["work_phone"] = $_POST["work_phone"];
		$user["mobile_phone"] = $_POST["mobile_phone"];
		$user["toastmasters_id"] = (int) $_POST["toastmasters_id"];
		add_member_user($user);
	}

$user_pass_default = password_hurdle(wp_generate_password());
?>

		<div class="wrap">
<div id="icon-users" class="icon32"><br /></div><h2 id="add-new-user"><?php _e("Add Member",'rsvptoast');?></h2>

<div id="ajax-response"></div>

<p><?php _e("Create a website user account for a new member",'rsvptoast');?>.</p>
<form action="<?php echo admin_url('users.php?page=add_awesome_member'); ?>" method="post" name="createuser" id="createuser" class="add:users: validate">
<input name="action" type="hidden" value="createuser" />
<input type="hidden" id="_wpnonce_create-user" name="_wpnonce_create-user" value="6f56987dd6" /><input type="hidden" name="_wp_http_referer" value="/wp-admin/user-new.php" /><table class="form-table">
	<tr class="form-field form-required">
		<th scope="row"><label for="user_login"><?php _e("Username",'rsvptoast');?> <span class="description"></span></label></th>
		<td><input name="user_login" type="text" id="user_login" value="" aria-required="true" />
        <br /><?php _e("Hint: try the part of the email before the @ sign. If you leave this blank, a username will be assigned based on first and last name.",'rsvptoast');?>
        </td>
	</tr>
	<tr class="form-field form-required">
		<th scope="row"><label for="email"><?php _e("Email",'rsvptoast');?> <span class="description">(<?php _e("required",'rsvptoast');?>)</span></label></th>
		<td><input name="email" type="text" id="email" value="" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="first_name"><?php _e("First Name",'rsvptoast');?> </label></th>
		<td><input name="first_name" type="text" id="first_name" value="" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="last_name"><?php _e("Last Name",'rsvptoast');?> </label></th>
		<td><input name="last_name" type="text" id="last_name" value="" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="home_phone"><?php _e("Home Phone",'rsvptoast');?> </label></th>
		<td><input name="home_phone" type="text" id="home_phone" value="" /></td>
	</tr>

	<tr class="form-field">
		<th scope="row"><label for="work_phone"><?php _e("Work Phone",'rsvptoast');?> </label></th>
		<td><input name="work_phone" type="text" id="work_phone" value="" /></td>
	</tr>

	<tr class="form-field">
		<th scope="row"><label for="mobile_phone"><?php _e("Mobile Phone",'rsvptoast');?> </label></th>
		<td><input name="mobile_phone" type="text" id="mobile_phone" value="" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="first_name"><?php _e("Toastmasters ID #",'rsvptoast');?> </label></th>
		<td><input name="toastmasters_id" type="text" id="toastmasters_id" value="" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="user_pass"><?php _e("Password",'rsvptoast');?> </label></th>
		<td><input name="user_pass" type="text" id="user_pass" value="<?php echo $user_pass_default; ?>" /></td>
	</tr>

	</table>
<p><input type="checkbox" name="no_email" value="1" /> <?php _e("Do not send email invites (for example, if you are still testing the site).",'rsvptoast');?></p> 

<p class="submit"><input type="submit" name="createuser" id="createusersub" class="button-primary" value="<?php _e("Add Member",'rsvptoast');?>"  /></p>
</form>

<?php

if(!$_POST)
{
?>

<h3><?php _e("Batch Import From Toastmasters.org spreadsheet",'rsvptoast');?></h3>
<p><?php _e("If you download the member spreadsheet from toastmasters.org, you should be able to copy the cells including member data (including the header row of column labels) and paste it here (use Ctrl-V on Windows).",'rsvptoast');?></p>
<form method="post" action="<?php echo admin_url('users.php?page=add_awesome_member'); ?>">
<textarea cols="80" rows="10" name="spreadsheet"></textarea>
<br />Default Password: <input name="user_pass" type="text" id="user_pass" value="<?php echo $user_pass_default; ?>" />
<br /><input type="checkbox" name="check_missing" value="1" /> <?php _e("Check for missing members (if you post a complete list of current members, this checkbox triggers a check of which website users are NOT currently on the toastmasters.org list and gives you an option to delete them).",'rsvptoast');?>
<br /><input type="checkbox" name="no_email" value="1" /> <?php _e("Do not send email invites (for example, if you are still testing the site).",'rsvptoast');?>
<br /><input type="submit" value="<?php _e("Post",'rsvptoast');?>" />
</form>
<p><img src="<?php echo plugins_url( 'spreadsheet.png' , __FILE__ ); ?>" width="500" height="169" /></p>
<?php
}

?>

<?php

if(!$_POST)
{
?>

<h3><?php _e("Batch Import / Reconcile With Toastmasters.org Roster",'rsvptoast');?></h3>
<p><?php _e("Copy roster from toastmasters.org (as shown below, Ctrl-C on Windows) and paste here (use Ctrl-V on Windows).",'rsvptoast');?></p>
<form method="post" action="<?php echo admin_url('users.php?page=add_awesome_member'); ?>"><textarea cols="80" rows="10" name="paste"></textarea>
<br /><?php _e("Default Password",'rsvptoast');?>: <input name="user_pass" type="text" id="user_pass" value="<?php echo $user_pass_default; ?>" />
<br /><input type="checkbox" name="check_missing" value="1" /> <?php _e("Check for missing members (if you post a complete list of current members, this checkbox triggers a check of which website users are NOT currently on the toastmasters.org list and gives you an option to delete them).",'rsvptoast');?>
<br /><input type="checkbox" name="no_email" value="1" /> <?php _e("Do not send email invites (for example, if you are still testing the site).",'rsvptoast');?>
<br /><input type="submit" value="<?php _e("Post",'rsvptoast');?>" />
</form>

<p><?php _e("Example",'rsvptoast');?>: <em><?php _e("Copy the entire roster from toastmasters.org from the first ID # to the last entry.",'rsvptoast');?></em></p>
<p><img src="<?php echo plugins_url( 'copy-roster.png' , __FILE__ ); ?>"  width="500" height="323" ></p>
<?php
}
?>

</div>

<?php
}

function extract_fields_tm($matches) {
// used with paste from HTML display on toastmasters.org
foreach ($matches[1] as $index => $webfield)
	{
		if($webfield == 'Email')
			{
			$contact['user_email'] = trim($matches[2][$index]);
			$ep = explode('@',$contact['user_email']);
			}
		else
			{
			$phone = trim($matches[2][$index]);
			$phone = str_replace(' ','',$phone);
			$phone = str_replace('1(','(',$phone);
			if($webfield == 'Home')
				$contact['home_phone'] = $phone;
			elseif($webfield == 'Work')
				$contact['work_phone'] = $phone;
			elseif($webfield == 'Cell')
				$contact['mobile_phone'] = $phone;
			}
	}
	return $contact;
}

function name2fields($name) {
$edpattern = "/, ([A-Z]{2,4})/";
preg_match_all($edpattern,$name,$matches);
$user["nickname"] = $user["display_name"] = preg_replace($edpattern,'',$name);
$np = explode(" ",$user["display_name"]);
$user["last_name"] = array_pop($np);
$user["first_name"] = implode(" ",$np);
if($matches[1][0])
	$user["education_awards"] = implode(", ",$matches[1]); 
return $user;
}

function add_member_user($user) {
	global $wpdb;
	$blog_id = get_current_blog_id();
	
	$welcome = '';
	$w = get_option('wp4toastmasters_welcome_message');
	if(!empty($w))
		{
		$p = get_post($w);
		$welcome = '<h1>'.$p->post_title."</h1>\n\n".$p->post_content;
		}

	foreach($user as $name => $value)
		$user[$name] = trim($value);	
			
	if(!isset($user["user_login"]) || empty($user["user_login"]) )
		$user["user_login"] = preg_replace('/[^a-z]/','',strtolower($user["first_name"].$user["last_name"]));

	if($exists = get_user_by('login',$user["user_login"] ) ) // if 2 people have the same name
		$user["user_login"] = $user["user_email"];
		
	$incpath = trailingslashit(str_replace('content','includes',WP_CONTENT_DIR));
	include_once $incpath.'registration.php';	

	//$user["user_email"] = trim($user["user_email"]);
	$user["toastmasters_id"] = (int) $user["toastmasters_id"]; // get rid of any zero padding
	if($user["toastmasters_id"] == 0)
		unset($user["toastmasters_id"]);
	
	if(!is_email($user["user_email"]) )
		 {
		echo '<h3 style="color: red;">'.__('Error: invalid email address','rsvptoast').' '.$user["user_email"].'</h3>';
		 return;
		 }
	elseif(isset($user["toastmasters_id"]) && ($user_id = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."usermeta WHERE meta_key='toastmasters_id' AND meta_value = '".$user["toastmasters_id"]."' ") ) )
		{
		 echo '<p style="color: red;">'.__('Account associated with Toastmasters ID','rsvpmaker').' '.$user["toastmasters_id"].' already in use by user '.$user_id.'</p>';
		$education_awards = get_user_meta($user_id,"education_awards", true);
		 if(isset($user["education_awards"]) && !empty($user["education_awards"]) )
		 	{
			update_user_meta($user_id,'education_awards',$user["education_awards"],$education_awards);
			echo "<p>".__('Updating education awards','rsvptoast').": ".$user["education_awards"]."</p>";
			}

		if(!is_user_member_of_blog( $user_id, $blog_id ) )
			{
			add_user_to_blog($blog_id, $user_id,'subscriber');
			echo '<p>'.__('Adding user to this site','rsvptoast').'.</p>';
			}
		return $user_id;
		}
	elseif($exists = get_user_by('login',$user["user_login"] ) )
		 {
		echo '<p style="color: red;">'.__('Username','rsvptoast').' '.$user["user_login"].' already in use by '.$exists->display_name.'</p>';
		 if(isset($user["toastmasters_id"]) && !empty($user["toastmasters_id"]) && empty($exists->toastmasters_id) )
		 	update_user_meta($exists->ID,'toastmasters_id',$user["toastmasters_id"]);

		 if(isset($user["education_awards"]) && !empty($user["education_awards"]) )
		 	{
		 	update_user_meta($exists->ID,'education_awards',$user["education_awards"],$exists->education_awards);
			echo "<p>".__('Updating education awards','rsvptoast').": ".$user["education_awards"]."</p>";
			}
		if(!is_user_member_of_blog( $exists->ID, $blog_id ) )
			{
			echo '<p>'.__('Adding','rsvptoast').' '.$exists->display_name.' '.__('to this site','rsvptoast').'.</p>';
			add_user_to_blog($blog_id, $exists->ID,'subscriber');
			}

		 return $exists->ID;
		 }
	elseif($exists = get_user_by('email',$user["user_email"] ) )
		{
		 echo '<p style="color: red;">'.__('Account associated with','rsvptoast').' '.$user["user_email"].' '.__('already in use by','rsvptoast').' '.$exists->display_name.'</p>';
		 if(isset($user["toastmasters_id"]) && !empty($user["toastmasters_id"]) )
		 	update_user_meta($exists->ID,'toastmasters_id',$user["toastmasters_id"]);

		 if(isset($user["education_awards"]) && !empty($user["education_awards"]) )
		 	{
		 	update_user_meta($exists->ID,'education_awards',$user["education_awards"],$exists->education_awards);
			echo "<p>".__('Updating education awards','rsvptoast').": ".$user["education_awards"]."</p>";
			}

		if(!is_user_member_of_blog( $exists->ID, $blog_id ) )
			{
			echo '<p>'.__('Adding','rsvptoast').' '.$exists->display_name.' '.__('to this site','rsvptoast').'.</p>';
			add_user_to_blog($blog_id, $exists->ID,'subscriber');
			}
		 return $exists->ID;
		}
	else
		{
		//register user

		if($user_id = wp_insert_user($user))
			{
				$profile_url = admin_url('profile.php#user_login');
				$message = sprintf(__('You have been registered at %s
	
Username: %s
Password: %s

Please <a href="%s">edit your member profile</a> to change your password and check that we have the correct contact information for you.','rsvptoast'),site_url(),$user["user_login"], $user["user_pass"], $profile_url );

			if($_POST["no_email"])
			{
			echo "<h3>".__('Email notification disabled','rsvptoast')."</h3><pre>".$message."</pre>";
			}
			else
			{
			$admin_email = get_bloginfo('admin_email');
			$mail["subject"] = 'Welcome to '.get_bloginfo('name');
			$mail["replyto"] = $admin_email;
			$mail["html"] = "<html>\n<body>\n".wpautop($message.$welcome)."\n</body></html>";
			$mail["to"] = $user["user_email"];
			$mail["cc"] = $admin_email;			
			$mail["from"] = $admin_email;
			$mail["fromname"] = get_bloginfo('name');
			awemailer($mail);
			echo "<h3>".__('Emailing to','rsvptoast')." ".$user["user_email"]."</h3><pre>".$message."</pre>";
			}
						
			}
		else
			 {
			echo '<h3 style="color: red;">WordPress '.__('registration error','rsvptoast').'</h3>';
			print_r($user);
			echo "<br />";
			 }
		}

return $user_id;
}

function no_member_match ($active_ids) {

?>
<form method="post" action="<?php echo site_url('users.php?page=add_awesome_member'); ?>">
<h3><?php _e("No Match",'rsvptoast');?></h3>
<p><?php _e("The members below don't show up on the current list. Check those who should be deleted.",'rsvptoast');?></p>
<?php
$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {		
	if(!in_array($user->ID, $active_ids) )
		{	
		if($user->user_login == '0_NOT_AVAILABLE')
			continue;
		$userdata = get_userdata($user->ID);
		printf('<p><input type="checkbox" name="remove_user[%d]" value="%d"> %s %s </p>',$user->ID, $user->ID, $userdata->user_login, $userdata->display_name);
		}
	}
?>
<input type="submit"  class="button-primary" value="<?php _e("Submit",'rsvptoast');?>" />
</form>
<?php
}


function awesome_contactmethod( $contactmethods ) {

$contactmethods['home_phone'] = __("Home Phone",'rsvptoast');
$contactmethods['work_phone'] = __("Work Phone",'rsvptoast');
$contactmethods['mobile_phone'] = __("Mobile Phone",'rsvptoast');
$contactmethods['facebook_url'] = __("Facebook Profile","rsvptoast");
$contactmethods['twitter_url'] = __("Twitter Profile",'rsvptoast');
$contactmethods['linkedin_url'] = __("LinkedIn Profile",'rsvptoast');
$contactmethods['business_url'] = __("Business Web Address",'rsvptoast');
$contactmethods['toastmasters_id'] = "Toastmasters ID";
$contactmethods['education_awards'] = "Educational Awards";
$contactmethods['user_email'] = __("Email",'rsvptoast');

  // Remove Yahoo IM
  unset($contactmethods['yim']);
  unset($contactmethods['aim']);
  unset($contactmethods['jabber']);
  unset($contactmethods['url']);
  return $contactmethods;
}
add_filter('user_contactmethods','awesome_contactmethod',10,1);

function login_redirect($link) {
if( is_user_member_of_blog() )
	return $link;
else
	return site_url().'/wp-login.php?redirect_to='.urlencode($link);
}


/**
 * CPEventsWidget Class
 */
class AwesomeWidget extends WP_Widget {
    /** constructor */
    function AwesomeWidget() {
        parent::WP_Widget(false, $name = 'Member Access');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
	  global $wpdb;
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
		$limit = ($instance["limit"]) ? $instance["limit"] : 10;
		$dateformat = ($instance["dateformat"]) ? $instance["dateformat"] : 'M. j';
		$activity_sql = "SELECT meta_value from $wpdb->postmeta WHERE meta_key='_activity' ORDER BY meta_id DESC LIMIT 0,5";
		$log = $wpdb->get_results($activity_sql);

        global $rsvp_options;
		;?>
              <?php echo $before_widget;?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title;?>
              <?php 
			  $dates = future_toastmaster_meetings($limit);
			  echo "\n<ul>\n";
			  if($dates)
			  {
			  foreach($dates as $row)
			  	{
				
				if(isset($ev[$row->postID]))
					$ev[$row->postID] .= ", ".date($dateformat,strtotime($row["datetime"]) );
				else
					{
					$t = strtotime($row->datetime);
					$title = $row->post_title .' '. date($dateformat,$t );
					$permalink = rsvpmaker_permalink_query($row->postID);
					if(!isset($signup))
						$signup = login_redirect($permalink);
					$ev[ $row->postID ] .= sprintf('<a href="%s">%s', login_redirect($permalink), $title);
					}
				}
			
			//pluggable function widgetlink can be overridden from custom.php
			echo "<li>".__('Sign up for meeting','rsvptoast').":</li>";			
			  foreach($ev as $id => $e)
			  	printf('<li>%s</a></li>',$e);
			  }
			echo "<li>".__('Your membership','rsvptoast').":</li>";			
			  printf('<li><a href="%s">'.__('Edit Member Profile','rsvptoast').'</a></li>',login_redirect(admin_url('profile.php#user_login')));
			  printf('<li><a href="%s">'.__('Member Dashboard','rsvptoast').'</a></li>',login_redirect(admin_url('index.php')) );

			  if(!is_user_member_of_blog() )
			  	printf('<li><a href="%s">'.__('Login','rsvptoast').'</a></li>',login_redirect(site_url()) );							
			  //echo "<li><br />";
			  
			 if(isset($log) && is_array($log) )
			 {
			  echo "<li><strong>".__('Activity','rsvptoast')."</strong><br />";
			  foreach($log as $row)
			  	echo "<p>".$row->meta_value . "</p>";
			  echo "</li>";
			  }
			do_action('awesome_widget_bottom');
			  echo "\n</ul>\n";
			
						  
			  echo $after_widget;?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags($new_instance['title']);
	$instance['dateformat'] = strip_tags($new_instance['dateformat']);
	$instance['limit'] = (int) $new_instance['limit'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
		$limit = ($instance["limit"]) ? $instance["limit"] : 10;
		$dateformat = ($instance["dateformat"]) ? $instance["dateformat"] : 'M. j';
        ;?>
            <p><label for="<?php echo $this->get_field_id('title');?>"><?php _e('Title:','rsvptoast');?> <input class="widefat" id="<?php echo $this->get_field_id('title');?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo $title;?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('limit');?>"><?php _e('Number to Show:','rsvptoast');?> <input class="widefat" id="<?php echo $this->get_field_id('limit');?>" name="<?php echo $this->get_field_name('limit');?>" type="text" value="<?php echo $limit;?>" /></label></p>

            <p><label for="<?php echo $this->get_field_id('dateformat');?>"><?php _e('Date Format:','rsvptoast');?> <input class="widefat" id="<?php echo $this->get_field_id('dateformat');?>" name="<?php echo $this->get_field_name('dateformat');?>" type="text" value="<?php echo $dateformat;?>" /></label> (<?php _e('PHP <a target="_blank" href="http://us2.php.net/manual/en/function.date.php">date</a> format string','rsvptoast');?>)</p>

        <?php 
    }

} // class AwesomeWidget

function awesome_roles() {
global $wp_roles;
$wp_roles->add_cap('contributor','upload_files');
}

add_action('admin_init','awesome_roles');

//translate
function edit_toast_roles( $content ) {
if(!$_GET["edit_roles"] || !current_user_can('edit_posts') )
	return $content;
global $post;
global $current_user;

return sprintf('<form id="edit_roles_form" method="post" action="%s"">
<p><em>'.__("Edit signups and click <b>Save Changes</b> as the bottom of the form.",'rsvptoast').'</em><p>
%s<button class="save_changes">'.__("Save Changes",'rsvptoast').'</button><input type="hidden" name="post_id" id="post_id" value="%d"></form>',rsvpmaker_permalink_query($post->ID),$content,$post->ID);
}
add_filter('the_content','edit_toast_roles',1);

function recommend_hash($role, $user) {
global $post;
return md5($role.$user.$post->ID);
}

function accept_recommended_role() {
// key=General_Evaluator-1&you=31&code=eZHuvRnuvb^(
global $post;
$permalink = rsvpmaker_permalink_query($post->ID);
$custom_fields = get_post_custom($post->ID);
if($_GET["key"] && $_GET["you"] && $_GET["code"])
	{
		$you = (int) $_GET["you"];
		$hash = recommend_hash($_GET["key"], $you);
		$count = (int) $_GET["count"];
		$key = preg_replace('/[0-9]/','',$_GET["key"]);
		if($hash != $_GET["code"])
			{
			header("Location: ".$permalink."recommendation=code_error");
			exit();
			}
		$success = false;
		for($i =1; $i <= $count; $i++)
			{
				$name = $key.$i;
				if($custom_fields[$name][0])
					; //echo "<p>Role is taken</p>";
				else
					{
					update_post_meta($post->ID, $name, $you);
					$success = true;
					break;
					}
			}
	if($success)
		header("Location: ".$permalink."recommendation=success");
	else
		header("Location: ".$permalink."recommendation=oops");
	exit();
	}
}

add_action('wp','accept_recommended_role');

function assign_toast_roles( $content ) {
if(!$_GET["recommend_roles"] || !current_user_can('edit_posts') )
	return $content;
global $post;
global $current_user;
global $wpdb;
global $rsvp_options;

$permalink = rsvpmaker_permalink_query($post->ID);

$sql = "SELECT * FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post->ID.' ORDER BY datetime';
$row = $wpdb->get_row($sql);
$date = date($rsvp_options["long_date"], strtotime($row->datetime) );

$output .= sprintf('<form id="edit_roles_form" method="post" action="%s">
<p><em>'.__("This form lets you recommend that an individual member take a specific speaking slot or other role (the member will get an email with a coded link for one-click role signup. Make your selections and click <b>Save Changes</b> at the bottom of the form.",'rsvptoast').'</em><p>
%s<button class="save_changes">'.__("Save Changes",'rsvptoast').'</button><input type="hidden" name="post_id" id="post_id" value="%d"></form>',$permalink,$content,$post->ID);

return $output;

}
add_filter('the_content','assign_toast_roles',1);

function signup_sheet() {

if(isset($_GET["signup"]) || isset($_GET["signup2"]))
	{
	global $wpdb;
	global $rsvp_options;
	global $post;

	$sql = "SELECT datetime
	FROM `".$wpdb->prefix."rsvp_dates`
	JOIN `".$wpdb->prefix."posts` ON postID = ".$wpdb->prefix."posts.ID
	WHERE post_type='rsvpmaker' AND post_status='publish' AND datetime > NOW() AND post_content LIKE '%[toastmaster %' ORDER BY datetime";

	$next = $wpdb->get_var($sql);
		
	$sql = "SELECT *, ".$wpdb->prefix."posts.ID as postID
	FROM `".$wpdb->prefix."rsvp_dates`
	JOIN `".$wpdb->prefix."posts` ON postID = ".$wpdb->prefix."posts.ID
	WHERE post_type='rsvpmaker' AND post_status='publish' AND datetime > '$next' AND post_content LIKE '%[toastmaster %' ORDER BY datetime";
	$datecount = 0;
	$wpdb->show_errors();
	$dates = $wpdb->get_results($sql);
	foreach($dates as $date)
		{
		$t = strtotime($date->datetime);

		$post = get_post($date->postID);
		$head .= "<th>".date("F j",$t)."</th>";
		$cells .= "<td>".do_shortcode($post->post_content)."</td>";
		$datecount++;
		if($datecount == 3)
			break;
		}
	
	echo "<html><head>
	<style>
	table {
	width: 100%;
	}
	th {
	font-size: 14px;
	font-weight: bold;
	text-align: center;
	}
	td, th {
	padding: 3px;
	margin: 2px;
	/* border: thin solid #000; */
	width: 33%;
	vertical-align: top;
	}
	.signuprole {
	font-size: 10px;
	border: thin solid #000;
	margin-bottom: 2px;
	font-weight: bold;
	}
	.assignedto {
	font-size: 14px;
	border-bottom: thin solid #000;
	padding-bottom: 10px;
	font-weight: normal;
	}
	</style>
	</head><body><table><tr>".$head."</tr><tr>".$cells."</tr></table></body></html>";
	exit();
	}

}

function future_toastmaster_meetings ($limit = 10) {
global $wpdb;
	$sql = "SELECT *, ".$wpdb->prefix."posts.ID as postID
	FROM `".$wpdb->prefix."rsvp_dates`
	JOIN `".$wpdb->prefix."posts` ON postID = ".$wpdb->prefix."posts.ID
	WHERE post_type='rsvpmaker' AND post_status='publish' AND datetime > NOW() AND post_content LIKE '%[toastmaster %' ORDER BY datetime LIMIT 0, $limit";
	return $wpdb->get_results($sql);
}

function awesome_open_roles($post_id = NULL) {

if(!is_user_member_of_blog())
	return;

if(!$_GET["open_roles"] && !$post_id)
	return;

global $wp_filter;
$corefilters = array('convert_chars','wpautop','wptexturize');
foreach($wp_filter["the_content"] as $priority => $filters)
	foreach($filters as $name => $details)
		{
		//keep only core text processing or shortcode
		if(!in_array($name,$corefilters) && !strpos($name,'hortcode'))
			{
			$r = remove_filter( 'the_excerpt', $name, $priority );
			$r = remove_filter( 'the_content', $name, $priority );
			}
		}

the_post();
ob_start();
the_content();
$content = ob_get_clean();
	
	global $wpdb;
	global $rsvp_options;
	global $current_user;
	global $open;
	if(!$post_id)
		$post_id = (int) $_GET["open_roles"];
	$permalink = rsvpmaker_permalink_query($post_id);//, true);

$sql = "SELECT * FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post_id.' ORDER BY datetime';
$row = $wpdb->get_row($sql);
$date = date($rsvp_options["long_date"], strtotime($row->datetime) );
	
	$header = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<body>
';
	
	if($open)
		{
		$output .= "<h3>".__("Open Roles",'rsvptoast')."</h3>\n<p>";

		foreach($open as $role => $count)
			{
			$output .=  $role;
			if($count > 1)
				$output .=  " (".$count.")";
			$output .=  "<br />\n";
			$openings += $count;
			}
		$output .= "</p>\n<p>".__("Sign up at",'rsvptoast')." <a href=\"" . $permalink. "\">" . $permalink. "</a></p>\n<p>".__("Forgot your password?",'rsvptoast')." <a href=\"".site_url('/wp-login.php?action=lostpassword')."\">".__("Reset your password here",'rsvptoast')."</a></p>\n<h3>".__("Roster",'rsvptoast')."</h3>\n";
		}
//	print_r($open);
	$output .=  $content;

$wp4toastmasters_mailman = get_option("wp4toastmasters_mailman");

	if($_POST)
	{
	
	if(!empty($wp4toastmasters_mailman["members"]))
		$mail["to"] = $wp4toastmasters_mailman["members"];
	else
		{
		$blogusers = get_users('blog_id='.get_current_blog_id() );
			foreach ($blogusers as $user) {
				//print_r($user);
				$emails[] = $user->user_email;
			}
		}
	
	if($_POST["note"])
		$output = nl2br(stripslashes($_POST["note"]))."\n".$output;		
	$mail["html"] = $header. $output . '</body></html>';
	$mail["from"] = $current_user->user_email;
	$mail["fromname"] = $current_user->display_name;
	$mail["subject"] = stripslashes($_POST["subject"]);
	if(is_array($emails))
	{
		foreach($emails as $e)
		{
		$mail["to"] = $e;
		echo awemailer($mail);
		}
	}
	else
		echo awemailer($mail);
	}
	else
	{
	$subject = __("Roster for ",'rsvptoast').' '.$date;
	if($openings)
		$subject .= " (".$openings." ".__("open roles",'rsvptoast').")";

	$mailform = '<h3>'.__("Add a Note",'rsvptoast').'</h3>
	<p>'.__("Your note, along with the roster details, will be sent to all members.",'rsvptoast').'</p>
	<form method="post" action="'.$permalink.'email_agenda=1">
Subject: <input type="text" name="subject" value="'.$subject.'" size="60"><br />
<textarea name="note" rows="5" cols="80"></textarea><br />
<input type="submit" value="Send" />
</form>';
	
	$output = $header. $mailform . $output . '</body></html>';
	}

	echo $output;

	exit();
}

add_action('init','awesome_open_roles');

function print_contacts( $cron = false ) {

if($cron)
	echo "cron is true<br />";
else {
	if(!$_GET["print_contacts"])
		return;
	if(!is_user_member_of_blog())
		die("You must log in first");
	}

echo '<html><body>';

$blogusers = get_users('blog_id='.get_current_blog_id());
    foreach ($blogusers as $user) {
	$userdata = get_userdata($user->ID);
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);
	$members[$index] = $userdata;
	}
	
	ksort($members);
	foreach($members as $userdata) {
?>	


<h3><?php echo $userdata->first_name.' '.$userdata->last_name?></h3>

<?php
$contactmethods['home_phone'] = __("Home Phone",'rsvptoast');
$contactmethods['work_phone'] = __("Work Phone",'rsvptoast');
$contactmethods['mobile_phone'] = __("Mobile Phone",'rsvptoast');
$contactmethods['user_email'] = __("Email",'rsvptoast');

	foreach($contactmethods as $name => $value)
		{
		if(strpos($name,'phone') && !empty($userdata->$name) )
			{
			printf("<div>%s: %s</div>\n",$value,$userdata->$name);
			}
		}
		printf('<div>'.__("Email",'rsvptoast').': <a href="mailto:%s">%s</a></div>'."\n",$userdata->user_email,$userdata->user_email);
		if(isset($userdata->status) && !empty($userdata->status) )
		printf('<div>'.__("Status",'rsvptoast').': %s</div>'."\n",$userdata->status);
?>
</div>
<?php

	}


echo '</body></html>';
exit();
}

add_action('init','signup_sheet');
add_action('init','print_contacts');

function detect_default_password() {

require_once( ABSPATH . WPINC . '/class-phpass.php');
require_once( ABSPATH . WPINC . '/registration.php');

$blogusers = get_users('blog_id=1&orderby=nicename');
    foreach ($blogusers as $user) {		
		$wp_hasher = new PasswordHash(8, TRUE);
		
	$password_hashed = $user->user_pass;
	$plain_password = 'someawe';
	if($wp_hasher->CheckPassword($plain_password, $password_hashed)) {
		wp_update_user(array('ID' => $user-ID, 'user_pass' => wp_generate_password() ) );
	   	echo $user->user_login." ".__("YES, Matched changing now",'rsvptoast')."<br />";
	}
	else {
	   echo $user->user_login." ".__("Password already reset",'rsvptoast')."<br />";
	}

	}
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<input type="submit" name="changepass" value="Change All Passwords" />
</form>
<?php
}

add_action( 'show_user_profile', 'awesome_user_profile_fields' );
add_action( 'edit_user_profile', 'awesome_user_profile_fields' );
 
function awesome_user_profile_fields( $user ) { ?>
<table class="form-table">
<tr>
<th><label for="public_profile"><?php _e("Public Profile",'rsvptoast');?></label></th>
<td>
<input type="checkbox" name="public_profile" id="public_profile" value="yes" <?php if( get_the_author_meta( 'public_profile', $user->ID ) ) echo ' checked="checked" '; ?> />
<span class="description"><?php _e("Check to allow name, social media links, photo, and the description you provided to be displayed publicly.<br /> Otherwise, your contact info will only be shown to other members who have logged in with a password. (Officer profiles are public by default)",'rsvptoast');?></span>
<blockquote>
<input type="checkbox" name="public_email" id="public_email" value="yes" <?php if( get_the_author_meta( 'public_email', $user->ID ) ) echo ' checked="checked" '; ?> /> <?php _e("Also show email publicly",'rsvptoast');?>
</blockquote>
</td>
</tr>
</table>
<?php }
 
add_action( 'personal_options_update', 'save_awesome_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_awesome_user_profile_fields' );
 
function save_awesome_user_profile_fields( $user_id ) {
 
if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
 
update_usermeta( $user_id, 'public_profile', $_POST['public_profile'] );
update_usermeta( $user_id, 'public_email', $_POST['public_email'] );
}

//deprecated
function awesome_add_details() {
global $wpdb;

if($_POST["event"])
	{
		$event = $_POST["event"];
		foreach($_POST as $name => $value)
			{
				if(strpos($name,'_Speaker'))
					add_post_meta($event, $name, $value);
			}
	$p = rsvpmaker_permalink_query($event);
	return sprintf(__('Updated your data for event listing','rsvptoast').' <a href="%s">%s</a>',$p,$p);
	}
elseif( ($speaker = $_GET["speaker"]) && ($event = $_GET["event"]) && ($count = $_GET["count"])) 
	{
		$signup = get_post_custom($event);
		if($speaker == $signup["_Speaker_".$count][0])
			{
			$userdata = get_userdata($speaker);
ob_start();
?>
<h3><?php _e("Update speech details for",'rsvptoast');?> <?php echo $userdata->display_name; ?></h3>
<p><?php _e("Please enter speech details. If you are not speaking from a manual, you can still enter a brief description including estimated time required.",'rsvptoast');?></p>
<form action="/add-details/" method="post">
<input type="hidden" name="event" value="<?php echo $event; ?>" />
		<select class="speaker_details" name="_manual_Speaker_<?php echo $count; ?>">
<option value="Choose Manual / Speech">Choose Manual / Speech</option>
<option value="COMPETENT COMMUNICATION (CC) MANUAL: The Ice Breaker (4 to 6 min)">COMPETENT COMMUNICATION (CC) MANUAL: The Ice Breaker (4 to 6 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Organize Your Speech (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Organize Your Speech (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Get to the Point (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Get to the Point (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: How to Say It (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: How to Say It (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Your Body Speaks (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Your Body Speaks (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Vocal Variety (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Vocal Variety (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Research Your Topic (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Research Your Topic (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Get Comfortable with Visual Aids (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Get Comfortable with Visual Aids (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Persuade with Power (5 to 7 min)">COMPETENT COMMUNICATION (CC) MANUAL: Persuade with Power (5 to 7 min)
</option><option value="COMPETENT COMMUNICATION (CC) MANUAL: Inspire Your Audience (8 to 10 min)">COMPETENT COMMUNICATION (CC) MANUAL: Inspire Your Audience (8 to 10 min)
</option><option value="COMMUNICATING ON TELEVISION: Straight Talk (3 min)">COMMUNICATING ON TELEVISION: Straight Talk (3 min)
</option><option value="COMMUNICATING ON TELEVISION: The Talk Show (10 min)">COMMUNICATING ON TELEVISION: The Talk Show (10 min)
</option><option value="COMMUNICATING ON TELEVISION: When You&#39;re the Host (10 min)">COMMUNICATING ON TELEVISION: When You Are the Host (10 min)
</option><option value="COMMUNICATING ON TELEVISION: The Press Conference (4 to 6 min presentation; 8 to 10 min with Q&amp;A)">COMMUNICATING ON TELEVISION: The Press Conference (4 to 6 min presentation; 8 to 10 min with Q&amp;A)
</option><option value="COMMUNICATING ON TELEVISION: Training On Television (5 to 7 min; 5 to 7 min video tape playback)">COMMUNICATING ON TELEVISION: Training On Television (5 to 7 min; 5 to 7 min video tape playback)
</option><option value="FACILITATING DISCUSSION: The Panel Moderator (20 to 30 min)">FACILITATING DISCUSSION: The Panel Moderator (20 to 30 min)
</option><option value="FACILITATING DISCUSSION: The Brainstorming Session (20 to 30 min)">FACILITATING DISCUSSION: The Brainstorming Session (20 to 30 min)
</option><option value="FACILITATING DISCUSSION: The Problem-Solving Session (30 to 40 min)">FACILITATING DISCUSSION: The Problem-Solving Session (30 to 40 min)
</option><option value="FACILITATING DISCUSSION: Handling Challenging Situations (Role Playing) (20 to 30 min)">FACILITATING DISCUSSION: Handling Challenging Situations (Role Playing) (20 to 30 min)
</option><option value="FACILITATING DISCUSSION: Reaching A Consensus (30 to 40 min)">FACILITATING DISCUSSION: Reaching A Consensus (30 to 40 min)
</option><option value="HIGH PERFORMANCE LEADERSHIP: Vision (5 to 7 min)">HIGH PERFORMANCE LEADERSHIP: Vision (5 to 7 min)
</option><option value="HIGH PERFORMANCE LEADERSHIP: Learning (5 to 7 min)">HIGH PERFORMANCE LEADERSHIP: Learning (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Warm Up Your Audience (5 to 7 min)">HUMOROUSLY SPEAKING: Warm Up Your Audience (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Leave Them With A Smile (5 to 7 min)">HUMOROUSLY SPEAKING: Leave Them With A Smile (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Make Them Laugh (5 to 7 min)">HUMOROUSLY SPEAKING: Make Them Laugh (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: Keep Them Laughing (5 to 7 min)">HUMOROUSLY SPEAKING: Keep Them Laughing (5 to 7 min)
</option><option value="HUMOROUSLY SPEAKING: The Humorous Speech (5 to 7 min)">HUMOROUSLY SPEAKING: The Humorous Speech (5 to 7 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: Conversing with Ease (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: Conversing with Ease (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: The Successful Negotiator (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: The Successful Negotiator (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: Diffusing Verbal Criticism (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: Diffusing Verbal Criticism (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: The Coach (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: The Coach (10 to 14 min)
</option><option value="INTERPERSONAL COMMUNICATIONS: Asserting Yourself Effectively (10 to 14 min)">INTERPERSONAL COMMUNICATIONS: Asserting Yourself Effectively (10 to 14 min)
</option><option value="INTERPRETIVE READING: Read A Story (8 to 10 min)">INTERPRETIVE READING: Read A Story (8 to 10 min)
</option><option value="INTERPRETIVE READING: Interpreting Poetry (6 to 8 min)">INTERPRETIVE READING: Interpreting Poetry (6 to 8 min)
</option><option value="INTERPRETIVE READING: The Monodrama (5 to 7 min)">INTERPRETIVE READING: The Monodrama (5 to 7 min)
</option><option value="INTERPRETIVE READING: The Play (12 to 15 min)">INTERPRETIVE READING: The Play (12 to 15 min)
</option><option value="INTERPRETIVE READING: The Oratorical Speech (10 to 12 min)">INTERPRETIVE READING: The Oratorical Speech (10 to 12 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (3 to 5 min)">Other Manual or Non Manual Speech: Custom Speech (3 to 5 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (5 to 7 min)">Other Manual or Non Manual Speech: Custom Speech (5 to 7 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (8 to 10 min)">Other Manual or Non Manual Speech: Custom Speech (8 to 10 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (10 to 12 min)">Other Manual or Non Manual Speech: Custom Speech (10 to 12 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (13 to 15 min)">Other Manual or Non Manual Speech: Custom Speech (13 to 15 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (18 to 20 min)">Other Manual or Non Manual Speech: Custom Speech (18 to 20 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (23 to 25 min)">Other Manual or Non Manual Speech: Custom Speech (23 to 25 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (28 to 30 min)">Other Manual or Non Manual Speech: Custom Speech (28 to 30 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (35 to 40 min)">Other Manual or Non Manual Speech: Custom Speech (35 to 40 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (40 to 45 min)">Other Manual or Non Manual Speech: Custom Speech (40 to 45 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (45 to 50 min)">Other Manual or Non Manual Speech: Custom Speech (45 to 50 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (55 to 60 min)">Other Manual or Non Manual Speech: Custom Speech (55 to 60 min)
</option><option value="Other Manual or Non Manual Speech: Custom Speech (more than an hour)">Other Manual or Non Manual Speech: Custom Speech (more than an hour)
</option><option value="PERSUASIVE SPEAKING: The Effective Salesperson (3 to 4 min speech; 2 min intro; 3 to 5 min role play)">PERSUASIVE SPEAKING: The Effective Salesperson (3 to 4 min speech; 2 min intro; 3 to 5 min role play)
</option><option value="PERSUASIVE SPEAKING: Conquering the " cold="" call"="" (3="" to="" 4="" min="" speech;2="" intro,="" 5="" 7="" role="" play;="" 2="" 3="" discussion)"="">PERSUASIVE SPEAKING: Conquering the "Cold Call" (3 to 4 min speech;2 min intro, 5 to 7 min role play; 2 to 3 min discussion)
</option><option value="PERSUASIVE SPEAKING: The Winning Proposal (5 to 7 min)">PERSUASIVE SPEAKING: The Winning Proposal (5 to 7 min)
</option><option value="PERSUASIVE SPEAKING: Addressing the Opposition (7 to 9 min speech; 2 to 3 min Q&amp;A)">PERSUASIVE SPEAKING: Addressing the Opposition (7 to 9 min speech; 2 to 3 min Q&amp;A)
</option><option value="PERSUASIVE SPEAKING: The Persuasive Leader (6 to 8 min)">PERSUASIVE SPEAKING: The Persuasive Leader (6 to 8 min)
</option><option value="PUBLIC RELATIONS: The Persuasive Approach (8 to 10 min)">PUBLIC RELATIONS: The Persuasive Approach (8 to 10 min)
</option><option value="PUBLIC RELATIONS: Speaking Under Fire (6 to 8 min, 8 to 10 min with Q&amp;A)">PUBLIC RELATIONS: Speaking Under Fire (6 to 8 min, 8 to 10 min with Q&amp;A)
</option><option value="PUBLIC RELATIONS: The Goodwill Speech (5 to 7 min)">PUBLIC RELATIONS: The Goodwill Speech (5 to 7 min)
</option><option value="PUBLIC RELATIONS: The Radio Talk Show (8 to 10 min)">PUBLIC RELATIONS: The Radio Talk Show (8 to 10 min)
</option><option value="PUBLIC RELATIONS: The Crisis Management Speech (8 to 10 min, plus 30 seconds wth Q&amp;A)">PUBLIC RELATIONS: The Crisis Management Speech (8 to 10 min, plus 30 seconds wth Q&amp;A)
</option><option value="SPEAKING TO INFORM: The Speech to Inform (5 to 7 min)">SPEAKING TO INFORM: The Speech to Inform (5 to 7 min)
</option><option value="SPEAKING TO INFORM: Resources for Informing (8 to 10 min)">SPEAKING TO INFORM: Resources for Informing (8 to 10 min)
</option><option value="SPEAKING TO INFORM: The Demonstration Talk (10 to 12 min)">SPEAKING TO INFORM: The Demonstration Talk (10 to 12 min)
</option><option value="SPEAKING TO INFORM: A Fact-Finding Report (10 to 12 min)">SPEAKING TO INFORM: A Fact-Finding Report (10 to 12 min)
</option><option value="SPEAKING TO INFORM: The Abstract Concept (10 to 12 min)">SPEAKING TO INFORM: The Abstract Concept (10 to 12 min)
</option><option value="SPECIAL OCCASION SPEECHES: Mastering the Toast (2 to 3 min)">SPECIAL OCCASION SPEECHES: Mastering the Toast (2 to 3 min)
</option><option value="SPECIAL OCCASION SPEECHES: Speaking in Praise (5 to 7 min)">SPECIAL OCCASION SPEECHES: Speaking in Praise (5 to 7 min)
</option><option value="SPECIAL OCCASION SPEECHES: The Roast (3 to 5 min)">SPECIAL OCCASION SPEECHES: The Roast (3 to 5 min)
</option><option value="SPECIAL OCCASION SPEECHES: Presenting an Award (3 to 4 min)">SPECIAL OCCASION SPEECHES: Presenting an Award (3 to 4 min)
</option><option value="SPECIAL OCCASION SPEECHES: Accepting an Award (5 to 7 min)">SPECIAL OCCASION SPEECHES: Accepting an Award (5 to 7 min)
</option><option value="SPECIALTY SPEECHES: Speak Off The Cuff (5 to 7 min)">SPECIALTY SPEECHES: Speak Off The Cuff (5 to 7 min)
</option><option value="SPECIALTY SPEECHES: Uplift the Spirit (8 to 10 min)">SPECIALTY SPEECHES: Uplift the Spirit (8 to 10 min)
</option><option value="SPECIALTY SPEECHES: Sell a Product (10 to 12 min)">SPECIALTY SPEECHES: Sell a Product (10 to 12 min)
</option><option value="SPECIALTY SPEECHES: Read Out Loud (12 to 15 min)">SPECIALTY SPEECHES: Read Out Loud (12 to 15 min)
</option><option value="SPECIALTY SPEECHES: Introduce the Speaker (duration of a club meeting)">SPECIALTY SPEECHES: Introduce the Speaker (duration of a club meeting)
</option><option value="SPEECHES BY MANAGEMENT: The Briefing (8 to 10 min; plus 5 min with Q&amp;A)">SPEECHES BY MANAGEMENT: The Briefing (8 to 10 min; plus 5 min with Q&amp;A)
</option><option value="SPEECHES BY MANAGEMENT: The Technical Speech (8 to 10 min)">SPEECHES BY MANAGEMENT: The Technical Speech (8 to 10 min)
</option><option value="SPEECHES BY MANAGEMENT: Manage And Motivate (10 to 12 min)">SPEECHES BY MANAGEMENT: Manage And Motivate (10 to 12 min)
</option><option value="SPEECHES BY MANAGEMENT: The Status Report (10 to 12 min)">SPEECHES BY MANAGEMENT: The Status Report (10 to 12 min)
</option><option value="SPEECHES BY MANAGEMENT: Confrontation: The Adversary Relationship (5 min speech; plus 10 min with Q&amp;A)">SPEECHES BY MANAGEMENT: Confrontation: The Adversary Relationship (5 min speech; plus 10 min with Q&amp;A)
</option><option value="STORYTELLING: The Folk Tale (7 to 9 min)">STORYTELLING: The Folk Tale (7 to 9 min)
</option><option value="STORYTELLING: Let&#39;s Get Personal (6 to 8 min)">STORYTELLING: Let&rsquo;s Get Personal (6 to 8 min)
</option><option value="STORYTELLING: The Moral of the Story (4 to 6 min)">STORYTELLING: The Moral of the Story (4 to 6 min)
</option><option value="STORYTELLING: The Touching Story (6 to 8 min)">STORYTELLING: The Touching Story (6 to 8 min)
</option><option value="STORYTELLING: Bringing History to Life (7 to 9 min)">STORYTELLING: Bringing History to Life (7 to 9 min)
</option><option value="TECHNICAL PRESENTATIONS: The Technical Briefing (8 to 10 min)">TECHNICAL PRESENTATIONS: The Technical Briefing (8 to 10 min)
</option><option value="TECHNICAL PRESENTATIONS: The Proposal (8 to 10 min; 3 to 5 min with Q&amp;A)">TECHNICAL PRESENTATIONS: The Proposal (8 to 10 min; 3 to 5 min with Q&amp;A)
</option><option value="TECHNICAL PRESENTATIONS: The Nontechnical Audience (10 to 12 min)">TECHNICAL PRESENTATIONS: The Nontechnical Audience (10 to 12 min)
</option><option value="TECHNICAL PRESENTATIONS: Presenting a Technical Paper (10 to 12 min)">TECHNICAL PRESENTATIONS: Presenting a Technical Paper (10 to 12 min)
</option><option value="TECHNICAL PRESENTATIONS: Enhancing A Technical Talk With The Internet (12 to 15 min)">TECHNICAL PRESENTATIONS: Enhancing A Technical Talk With The Internet (12 to 15 min)
</option><option value="THE DISCUSSION LEADER: The Seminar Solution (20 to 30 min)">THE DISCUSSION LEADER: The Seminar Solution (20 to 30 min)
</option><option value="THE DISCUSSION LEADER: The Round Robin (20 to 30 min)">THE DISCUSSION LEADER: The Round Robin (20 to 30 min)
</option><option value="THE DISCUSSION LEADER: Pilot a Panel (30 to 40 min)">THE DISCUSSION LEADER: Pilot a Panel (30 to 40 min)
</option><option value="THE DISCUSSION LEADER: Make Believe (Role Playing) (20 to 30 min)">THE DISCUSSION LEADER: Make Believe (Role Playing) (20 to 30 min)
</option><option value="THE DISCUSSION LEADER: The Workshop Leader (30 to 40 min)">THE DISCUSSION LEADER: The Workshop Leader (30 to 40 min)
</option><option value="THE ENTERTAINING SPEAKER: The Entertaining Speech (5 to 7 min)">THE ENTERTAINING SPEAKER: The Entertaining Speech (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: Resources for Entertainment (5 to 7 min)">THE ENTERTAINING SPEAKER: Resources for Entertainment (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: Make Them Laugh (5 to 7 min)">THE ENTERTAINING SPEAKER: Make Them Laugh (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: A Dramatic Talk (5 to 7 min)">THE ENTERTAINING SPEAKER: A Dramatic Talk (5 to 7 min)
</option><option value="THE ENTERTAINING SPEAKER: Speaking After Dinner (8 to 10 min)">THE ENTERTAINING SPEAKER: Speaking After Dinner (8 to 10 min)
</option><option value="THE PROFESSIONAL SALESPERSON: The Winning Attitude (8 to 10 min)">THE PROFESSIONAL SALESPERSON: The Winning Attitude (8 to 10 min)
</option><option value="THE PROFESSIONAL SALESPERSON: Closing The Sale (10 to 12 min)">THE PROFESSIONAL SALESPERSON: Closing The Sale (10 to 12 min)
</option><option value="THE PROFESSIONAL SALESPERSON: Training The Sales Force (6 to 8 min speech; 8 to 10 min role play; 2 to 5 min discussion)">THE PROFESSIONAL SALESPERSON: Training The Sales Force (6 to 8 min speech; 8 to 10 min role play; 2 to 5 min discussion)
</option><option value="THE PROFESSIONAL SALESPERSON: The Sales Meeting (15 to 20 min)">THE PROFESSIONAL SALESPERSON: The Sales Meeting (15 to 20 min)
</option><option value="THE PROFESSIONAL SALESPERSON: The Team Sales Presentation (15 to 20 min plus 5 to 7 min per person for manual credit)">THE PROFESSIONAL SALESPERSON: The Team Sales Presentation (15 to 20 min plus 5 to 7 min per person for manual credit)
</option><option value="THE PROFESSIONAL SPEAKER: The Keynote Address (15 to 20 min)">THE PROFESSIONAL SPEAKER: The Keynote Address (15 to 20 min)
</option><option value="THE PROFESSIONAL SPEAKER: Speaking to Entertain (15 to 20 min)">THE PROFESSIONAL SPEAKER: Speaking to Entertain (15 to 20 min)
</option><option value="THE PROFESSIONAL SPEAKER: The Sales Training Speech (15 to 20 min)">THE PROFESSIONAL SPEAKER: The Sales Training Speech (15 to 20 min)
</option><option value="THE PROFESSIONAL SPEAKER: The Professional Seminar (20 to 40 min)">THE PROFESSIONAL SPEAKER: The Professional Seminar (20 to 40 min)
</option><option value="THE PROFESSIONAL SPEAKER: The Motivational Speech (15 to 20 min)">THE PROFESSIONAL SPEAKER: The Motivational Speech (15 to 20 min)
</option></select><br>
		<div>Choose a speech from the current list of Toastmasters International manuals.</div>

		<label for="speechtitle"><?php _e("Your Speech Title",'rsvptoast');?></label>
		<input type="text" name="_title_Speaker_<?php echo $count; ?>" class="title_field" value="">
<br />
		<label for="introduction"><?php _e("Your Speech Introduction",'rsvptoast');?></label>
<br />
		<textarea name="_intro_Speaker_<?php echo $count; ?>" rows="5" style="width: 95%"></textarea>
		<div>
		<?php _e("Enter a complete introduction for the Toastmaster.",'rsvptoast');?>
		</div>
        
        <input type="submit" name="update" value="Update">
</form>
<?php
return ob_get_clean();
			}
		else
			return __("Speaker does not match",'rsvptoast')."<br />";
	}
}

add_shortcode('awesome_add_details','awesome_add_details');


function speech_intros() {

if(!$_GET["intros"])
	return;

?>
<html>
<head>
<style>
h1 {
font-size: 24px;
}
p 
{
	font-size: 18px;
}
</style>
</head>
<body>
<?php
	
	$event = (int) $_GET["intros"];
	
	global $wpdb;
	
	$signup = get_post_custom($event);
	//print_r($signup);
	
	for($i = 1; $i < 6; $i++)
		{
			if($speaker = $signup["_Speaker_".$i][0])
				{
				$userdata = get_userdata($speaker);
				echo "<h1>".__("Speaker",'rsvptoast')." ".$userdata->first_name.' '.$userdata->last_name."</h1>";
				$title = $signup["_title_Speaker_".$i][0];
				$intro = $signup["_intro_Speaker_".$i][0];
				$manual = $signup["_manual_Speaker_".$i][0];
				if($manual || $title || $intro)
					printf('<p><strong>'.__("Manual",'rsvptoast').':</strong> %s</p><p><strong>'.__("Title",'rsvptoast').':</strong> %s</p>%s',$manual,$title,wpautop($intro) );
				else
					echo "<p>".__("Details not provided.",'rsvptoast')."</p>";
				}
			
		}
?>
</body>
</html>
<?php	
	exit();

}

add_action('init','speech_intros');

function profile_prompt() {

		echo nl2br(__('Can we get a photo of you for the members listing on our Toastmasters website? With our club growing, we would like to have a member roster with photos to help everyone get to know each other.','rsvptoast')."\n".__('
You can also log into the website and upload a photo. Take a few minutes to review your profile, making any needed additions or corrections to the contact info we have for you.','rsvptoast'));

$contactmethods['home_phone'] = "Home Phone";
$contactmethods['work_phone'] = "Work Phone";
$contactmethods['mobile_phone'] = "Mobile Phone";

$blogusers = get_users('blog_id=1&orderby=nicename');
    foreach ($blogusers as $user) {		
	$userdata = get_userdata($user->ID);
	echo $userdata->first_name .' '.$userdata->last_name;
	if(isset($userdata->fbuid))
		{
		$fbset = true;
		}
	else
		$fbset = false;
	
	if(userphoto_exists($userdata))
		$photo = true;
	else
		$photo = false;
	$phone = false;
	foreach($contactmethods as $name => $value)
		{
			if(isset($userdata->$name) )
				$phone = true;
		}
	if(!$phone)
		echo " <strong>".__("No Phone Number",'rsvptoast')."</strong>";
	
	if($fbset)
		echo " <strong>".__("Facebook Connection Set",'rsvptoast')."</strong>";
	elseif($photo)
		echo " <strong>".__("Photo Provided",'rsvptoast')."</strong>";
	else
		{
		echo " <strong>".__("Please provide a photo.",'rsvptoast')."</strong>";
		
		printf('<br /><a target="_blank" href="mailto:%s?subject='.__("Please add your photo to your member listiong on the Toastmasters website",'rsvptoast').'">send note</a><br />',$userdata->user_email);
		
		}
	echo "<br />";
	}
}


function awesome_rating () {
global $wpdb;
$sql = "SELECT * FROM `".$wpdb->prefix."postmeta` JOIN ".$wpdb->prefix."rsvp_dates ON ".$wpdb->prefix."rsvp_dates.postID = ".$wpdb->prefix."postmeta.post_id WHERE `meta_key` LIKE '%1' OR  `meta_key` LIKE '%2' OR  `meta_key` LIKE '%3' AND ( (meta_key IS NOT NULL) AND (meta_value IS NOT NULL) AND (datetime > DATE_SUB(NOW(), INTERVAL 3 MONTH)) AND (datetime < NOW()) )";
$r = $wpdb->get_results($sql);
foreach($r as $row)
	{
	//print_r($row);
	$id = (int) $row->meta_value;
	if($id && !empty($row->meta_key) && !strpos($row->meta_key,'Backup') )
		{
		$rate[$id]++;
		$tags[$id] .= $row->meta_key.', ';
		}
	}
arsort($rate);

foreach($rate as $id => $value)
	{
		$userdata = get_userdata($id);
		if(!isset($top))
			$top = $value;
		$score = round(($value / $top) * 5);
		if($score < 1)
			$score = 1;
		printf('<p>%s : %s %s</p>',$score,$userdata->first_name,$userdata->last_name);
		echo $tags[$id]."<br />";
	}
}

function pack_speakers($count)
{
global $post;
$scount = 1;
$fullorder =array();
$currentorder =array();

	for($i = 1; $i <= $count; $i++)
		{
		
		$field = '_Speaker_' . $i;
		$assigned = get_post_meta($post->ID, $field, true);
		if($assigned)
			{
				$currentorder[] = $i;
				$fullorder[] = $scount;
				$speaker[$scount]["assigned"] = $assigned;
				$speaker[$scount]["details"] = get_post_meta($post->ID, '_manual'.$field, true);
				$speaker[$scount]["title"] = get_post_meta($post->ID, '_title'.$field, true);
				$scount++;
			}
		}

		if(sizeof($currentorder) < $count)
			{
				$assigned = get_post_meta($post->ID, '_Backup_Speaker_1', true);
				if($assigned)
					{
					$speaker[$scount]["assigned"] = $assigned;
					$speaker[$scount]["details"] = get_post_meta($post->ID, '_manual_Backup_Speaker_1', true);
					$speaker[$scount]["title"] = get_post_meta($post->ID, '_title_Backup_Speaker_1', true);
					$fullorder[] = $scount;
					delete_post_meta($post->ID,'_Backup_Speaker_1');
					delete_post_meta($post->ID,'_manual_Backup_Speaker_1');
					delete_post_meta($post->ID,'_title_Backup_Speaker_1');
					}
			}
		if( !sizeof($fullorder) )
			return;
		$diff = array_diff($fullorder,$currentorder);
		if(sizeof($diff))
		{
			for($i = 1; $i <= $count; $i++)
				{
				update_post_meta($post->ID,'_Speaker_' . $i,$speaker[$i]["assigned"]);
				update_post_meta($post->ID,'_manual_Speaker_' . $i,$speaker[$i]["details"]);
				update_post_meta($post->ID,'_title_Speaker_' . $i,$speaker[$i]["title"]);
				}
		}

}//end pack speakers

function awemailer($mail) {
	
	global $rsvp_options;
	
	if(get_option('wp4toastmasters_disable_email'))
		{
			echo '<p><b><em>'.__('Email sending functions disabled on this site.','rsvptoast')."</em></b></p>";
			return;
		}
	
	$rsvp_options = apply_filters('rsvp_email_options',$rsvp_options);
	
	if(!$rsvp_options["smtp"])
		{
		echo "<div>".__("Email not set up",'rsvptoast')."</div>";
		return;
		$headers = 'From: '.$mail["from"] . "\r\n" .
    'Reply-To: '. $mail["replyto"] . "\r\n" .
    'X-Mailer: PHP/' . phpversion(). "\r\n" .
	'Content-Type: text/html';

		mail($mail["to"],$mail["subject"],$mail["html"],$headers);
		return;
		}
	
	require_once ABSPATH . WPINC . '/class-phpmailer.php';
	require_once ABSPATH . WPINC . '/class-smtp.php';
	$rsvpmail = new PHPMailer();
	
	$rsvpmail->IsSMTP(); // telling the class to use SMTP

	if($rsvp_options["smtp"] == "gmail") {
		$rsvpmail->SMTPAuth   = true;                  // enable SMTP authentication
		$rsvpmail->SMTPSecure = "tls";                 // sets the prefix to the servier
		$rsvpmail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
		$rsvpmail->Port       = 587;                   // set the SMTP port for the GMAIL server
	}
	elseif($rsvp_options["smtp"] == "sendgrid") {
	$rsvpmail->SMTPAuth   = true;                  // enable SMTP authentication
	$rsvpmail->Host = 'smtp.sendgrid.net';
	$rsvpmail->Port = 587; 
	}
	else {
	$rsvpmail->Host = $rsvp_options["smtp_server"]; // SMTP server
	$rsvpmail->SMTPAuth=true;
	if(isset($rsvp_options["smtp_prefix"]) && $rsvp_options["smtp_prefix"] )
		$rsvpmail->SMTPSecure = $rsvp_options["smtp_prefix"];                 // sets the prefix to the servier
	$rsvpmail->Port=$rsvp_options["smtp_port"];
	}
 
 $rsvpmail->Username=$rsvp_options["smtp_username"];
 $rsvpmail->Password=$rsvp_options["smtp_password"];
 $rsvpmail->AddAddress($mail["to"]);
 if(isset($mail["cc"]) )
 	$rsvpmail->AddCC($mail["cc"]);
$via = (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) ? ' (via '.$_SERVER['SERVER_NAME'].')' : '';
if(is_admin() && isset($_GET["debug"]))
	$rsvpmail->SMTPDebug = 2;
 $rsvpmail->SetFrom($rsvp_options["smtp_useremail"], $mail["fromname"]. $via);
 $rsvpmail->ClearReplyTos();
 $rsvpmail->AddReplyTo($mail["from"], $mail["fromname"]);
if($mail["replyto"])
 $rsvpmail->AddReplyTo($mail["replyto"]);

 $rsvpmail->Subject = $mail["subject"];
if($mail["html"])
	{
	if($mail["text"])
		$rsvpmail->AltBody = $mail["text"];
	else
		$rsvpmail->AltBody = trim(strip_tags($mail["html"]) );
	$rsvpmail->MsgHTML($mail["html"]);
	}
	else
		{
			$rsvpmail->Body = $mail["text"];
			$rsvpmail->WordWrap = 50;
		}
	
	try {
		$rsvpmail->Send();
	} catch (phpmailerException $e) {
		echo $e->errorMessage();
	} catch (Exception $e) {
		echo $e->getMessage(); //Boring error messages from anything else!
	}
	return $rsvpmail->ErrorInfo;
}

if(!function_exists('rsvpmaker_print_redirect'))
{
add_action("template_redirect", 'rsvpmaker_print_redirect');

function rsvpmaker_print_redirect()
{
global $post;

		if ($_GET["tm_reports"])
		{
			include(WP_PLUGIN_DIR . '/rsvpmaker-for-toastmasters/reports-fullscreen.php');
			die();
		}

if($post->post_type != 'rsvpmaker')
	return;	
	
		if ($_GET["print_agenda"])
		{
			$format = get_option('wp4toastmasters_agenda_layout');
			if($format == 'sidebar')
				include(WP_PLUGIN_DIR . '/rsvpmaker-for-toastmasters/agenda-with-sidebar.php');
			else
				include(WP_PLUGIN_DIR . '/rsvpmaker-for-toastmasters/agenda.php');
			die();
		}
		elseif (isset($_GET["email_agenda"]))
		{
			include(WP_PLUGIN_DIR . '/rsvpmaker-for-toastmasters/email_agenda.php');
			die();
		}
}
}


function themewords ($atts) {
global $post;

if($_POST["themewords"])
	update_post_meta($post->ID,'_themewords',simplify_html($_POST["themewords"]));

ob_start();

if(is_user_member_of_blog() && $_GET["edit_roles"])
{
?>
<script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script> 
<script>
        tinymce.init({selector:'textarea.themewords',plugins: "code"});		
</script>
                    <div id="themewords">
                    <h3><?php _e("Theme/Words",'rsvptoast');?></h3>
                    <textarea name="themewords" rows="5" cols="80" class="themewords"><?php global $post; 
										
					echo wpautop(get_post_meta($post->ID,'_themewords',true)); ?> </textarea>
                    </div>
<?php
}
elseif($_GET["print_agenda"])
	{
	$th = get_post_meta($post->ID,'_themewords',true);
	if(!empty($th))
		{
        return '<div class="agenda_note">'.wpautop($th).'</div>';
     	}
	}
else
{
	$th = get_post_meta($post->ID,'_themewords',true);
	if(!empty($th))
		{
?>
                    <div id="themewords">
                    <h3 style="font-weight: bold; margin-top: 20px;"><?php _e("Theme/Words",'rsvptoast');?></h3>
                    <?php echo wpautop($th); ?>
                    </div>
<?php			
		}
}
return ob_get_clean();
}

add_shortcode('themewords','themewords');

function simplify_html($text, $allowable_tags="<p><br><div><b><strong><em><i><h1><h2><h3><h4><h5><h6><ol><ul><li>") {
	$text = strip_tags($text, $allowable_tags);
	$text = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $text);
	return preg_replace("|</{0,1}p>|i","\n", $text);
}

function user_archive () {
global $wpdb;
$wpdb->show_errors();
$blogusers = get_users('blog_id='.get_current_blog_id());
    foreach ($blogusers as $user) {	
	$userdata = get_userdata($user->ID);
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);
	$sql = $wpdb->prepare("REPLACE INTO ".$wpdb->prefix."users_archive SET data=%s, sort=%s", serialize($userdata),$index);
	$wpdb->query($sql);
	}
}

function archive_users_init () {
// if a logged in user access the users list, back up users
if(!strpos($_SERVER['REQUEST_URI'],'users.php') )
	return;

$set = get_option('archive_users_setup');
if(!$set)
	{
	global $wpdb;
	$wpdb->show_errors();
	$sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."users_archive` (
	  `sort` varchar(255) NOT NULL,
	  `data` text NOT NULL,
	  PRIMARY KEY (`sort`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	$wpdb->query($sql);
	add_option('archive_users_setup',1);
	}

	user_archive();
}

add_action('admin_init','archive_users_init');

function toast_admin_notice () {
global $post;
$role_editor = admin_url('edit.php?post_type=rsvpmaker&page=agenda_setup&post_id='.$post->ID);

if( ($_GET["action"] == 'edit') && strpos($post->post_content,'role=') )//isset($post->post_content) && 
	echo '<div style="padding: 5px; margin:5px; border: thick solid red; font-size: 20px;"><span style="color:red;">'.__('Switch to','rsvptoast').':</span> <a href="'.$role_editor.'">Toastmasters '.__('Agenda Setup','rsvptoast').'</a> '.__('for easier drag-and-drop editing','rsvptoast').'</div>';

$public = get_option('blog_public');

if(!$public)
	printf('<div style="padding: 5px; margin:5px; border: thin solid red;">'.__('This site is not being indexed by search engines. To make it public, visit the <a href="%s">Toastmasters Settings</a> screen.','rsvptoast').'</div>',admin_url('options-general.php?page=wp4toastmasters_settings'));

$tz = get_option('timezone_string');
if(empty($tz) )
	printf('<div style="padding: 5px; margin:5px; border: thin solid red;">'.__('Make sure to set the correct timezone for your site so scheduling functions will work properly. Visit the <a href="%s">Toastmasters Settings</a> screen.','rsvptoast').'</div>',admin_url('options-general.php?page=wp4toastmasters_settings'));

}

add_action('admin_notices', 'toast_admin_notice');

if(!function_exists('add_implicit_links') ) { function add_implicit_links($text) {
	$text = preg_replace('! ([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{3})!i', ' <a href="mailto:$1">$1</a>', $text);
	$text = preg_replace('! (www.[a-z0-9_./?=&-;]+)!i', ' <a href="http://$1">$1</a>', $text);
	$text = preg_replace('! (https{0,1}://[a-z0-9_./?=&-;]+)!i', ' <a href="$1">$1</a>', $text);
	return $text;
} }

// shortcode editor functions 

function shortcode_eventdates($post_id) {

global $wpdb;
global $rsvp_options;
global $custom_fields;
$custom_fields = get_post_custom($post_id);

if(isset($custom_fields["_sked"][0]))
	{
	$template = unserialize($custom_fields["_sked"][0]);
	template_schedule($template);

/*
$week = (int) $template["week"];
$dow = (int) $template["dayofweek"];
$weekarray = Array(__("Varies",'rsvpmaker'),__("First",'rsvpmaker'),__("Second",'rsvpmaker'),__("Third",'rsvpmaker'),__("Fourth",'rsvpmaker'),__("Last",'rsvpmaker'),__("Every",'rsvpmaker'));
$dayarray = Array(__("Sunday",'rsvpmaker'),__("Monday",'rsvpmaker'),__("Tuesday",'rsvpmaker'),__("Wednesday",'rsvpmaker'),__("Thursday",'rsvpmaker'),__("Friday",'rsvpmaker'),__("Saturday",'rsvpmaker'));

$weekarr[0] = "<option value=\"0\">".__("Varies",'rsvpmaker')."</option>\n";
$weekarr[1] = "<option value=\"1\">".__("First",'rsvpmaker')."</option>\n";
$weekarr[2] = "<option value=\"2\">".__("Second",'rsvpmaker')."</option>\n";
$weekarr[3] = "<option value=\"3\">".__("Third",'rsvpmaker')."</option>\n";
$weekarr[4] = "<option value=\"4\">".__("Fourth",'rsvpmaker')."</option>\n";
$weekarr[5] = "<option value=\"5\">".__("Last",'rsvpmaker')."</option>\n";
$weekarr[6] = "<option value=\"6\">".__("Every",'rsvpmaker')."</option>\n";

$dayarr[0] = "<option value=\"0\">".__("Sunday",'rsvpmaker')."</option>\n";
$dayarr[1] = "<option value=\"1\">".__("Monday",'rsvpmaker')."</option>\n";
$dayarr[2] = "<option value=\"2\">".__("Tuesday",'rsvpmaker')."</option>\n";
$dayarr[3] = "<option value=\"3\">".__("Wednesday",'rsvpmaker')."</option>\n";
$dayarr[4] = "<option value=\"4\">".__("Thursday",'rsvpmaker')."</option>\n";
$dayarr[5] = "<option value=\"5\">".__("Friday",'rsvpmaker')."</option>\n";
$dayarr[6] = "<option value=\"6\">".__("Saturday",'rsvpmaker')."</option>\n";

$weekselect = $weekarr[(int) $template["week"]];
$weekselect .= implode("",$weekarr); 
$dayselect = $dayarr[(int) $template["dayofweek"]];
$dayselect .= implode("",$dayarr);

$h = (int) $template["hour"];
$minutes = $template["minutes"];
?>
<p><?php _e("Regular Schedule",'rsvpmaker'); ?>: 
<select name="sked[week]" id="week">
<?=$weekselect?>
</select>
<select name="sked[dayofweek]" id="dayofweek">
<?=$dayselect?>
</select>
</p>
        <table border="0">
<tr><td><?php _e("Time",'rsvpmaker'); ?>:</td>
<td><?php _e("Hour",'rsvpmaker'); ?>: <select name="sked[hour]" id="hour">
<?php
for($hour = 0; $hour < 24; $hour++)
{

if($hour == $h)
	$selected = ' selected = "selected" ';
else
	$selected = '';

	if($hour > 12)
		$displayhour .= "\n<option $selected " . 'value="' . $hour . '">' . ($hour - 12) . ' p.m.</option>';
	elseif($hour == 12)
		$displayhour .= "\n<option $selected " . 'value="' . $hour . '">12 p.m.</option>';
	elseif($hour == 0)
		$displayhour .= "\n<option $selected " . 'value="00">12 a.m.</option>';
	else
		$displayhour .= "\n<option $selected " . 'value="' . $hour . '">' . $hour . ' a.m.</option>';
}
echo $displayhour;
?>
</select>

<?php _e("Minutes",'rsvpmaker'); ?>: <select id="minutes" name="sked[minutes]">
<?php
$displayminutes = '
<option value="'.$minutes.'">'.$minutes.'</option>
<option value="00">00</option>
<option value="15">15</option>
<option value="30">30</option>
<option value="45">45</option>
</select>';
echo $displayminutes;
?>
<em><?php _e("For an event starting at 12:30 p.m., you would select 12 p.m. and 30 minutes",'rsvpmaker'); ?>.</em>
</td></tr></table>
<?php
*/
		return;
	}


if(isset($custom_fields["_meet_recur"][0]))
	{
		$t = (int) $custom_fields["_meet_recur"][0];
	}
	
if(isset($post_id) )
	{
	$sql = "SELECT * FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post_id.' ORDER BY datetime';
	$results = $wpdb->get_results($sql,ARRAY_A);
	}
else
	$results = false;

if($results)
{
$start = 2;
foreach($results as $row)
	{
	echo "\n<div class=\"event_dates\"> \n";
	$t = strtotime($row["datetime"]);
	if($rsvp_options["long_date"]) echo date($rsvp_options["long_date"],$t);
	$dur = $row["duration"];
	if($dur != 'allday')
		echo date(' '.$rsvp_options["time_format"],$t);
	if(is_numeric($dur) )
		echo " to ".date ($rsvp_options["time_format"],$dur);
	echo sprintf(' <input type="checkbox" name="delete_date[]" value="%d" /> %s<br />',$row["id"],__('Delete','rsvpmaker'));
	rsvpmaker_date_option($row);
	echo "</div>\n";
	}
}
}

function member_not_user() {
echo '<p style="color: red;"><b>For Toastmasters members, please use the <a href="'.admin_url('users.php?page=add_awesome_member').'">Add Member</a> form instead.</b></p>';
}

add_action('user_new_form','member_not_user');

function add_awesome_roles() {
       add_role( 'officer', 'Officer', array( 'delete_others_pages' => true,
'delete_others_posts' => true,
'delete_pages' => true,
'delete_posts' => true,
'delete_private_pages' => true,
'delete_private_posts' => true,
'delete_published_pages' => true,
'delete_published_posts' => true,
'edit_others_pages' => true,
'edit_others_posts' => true,
'edit_pages' => true,
'edit_posts' => true,
'edit_private_pages' => true,
'edit_private_posts' => true,
'edit_published_pages' => true,
'edit_published_posts' => true,
'manage_categories' => true,
'manage_links' => true,
'moderate_comments' => true,
'publish_pages' => true,
'publish_posts' => true,
'read' => true,
'read_private_pages' => true,
'read_private_posts' => true,
'upload_files' => true,
'delete_others_rsvpmakers' => true,
'delete_rsvpmakers' => true,
'delete_private_rsvpmakers' => true,
'delete_published_rsvpmakers' => true,
'edit_others_rsvpmakers' => true,
'edit_rsvpmakers' => true,
'edit_private_rsvpmakers' => true,
'edit_published_rsvpmakers' => true,
'publish_rsvpmakers' => true,
'read_private_rsvpmakers' => true,
'promote_users' => true,
'remove_users' => true,
'delete_users' => true,
'list_users' => true,
'edit_users' => true
 ) );
   }

function awesome_role_activation_wrapper() {
	global $current_user;
	
   register_activation_hook( __FILE__, 'add_awesome_roles' );
   if($_GET["add_awesome_roles"])
   	add_awesome_roles();

if($_POST['wp4toastmasters_officer_ids'] && current_user_can('manage_options') )
	{
		foreach($_POST['wp4toastmasters_officer_ids'] as $id)
			{
				$id = (int) $id;
				if(($id == 0) || ($id == $current_user->ID) )
					continue;
				elseif( user_can($id, 'manage_options') )
					continue; // don't mess with the admin
				else
					{
						$officer = get_userdata($id);
						$user = array('ID' => $id, 'role' => 'officer', 'user_email' => $officer->user_email);
						wp_update_user($user);
					}
			}
	}
}

add_action('admin_init','awesome_role_activation_wrapper');


function toastmasters_css_js() {
	global $post;
	if(!strpos($post->post_content,'[toast') && !strpos($post->post_content,'[rsvp') )
	
	wp_enqueue_style( 'jquery' );
	wp_enqueue_style( 'style-toastmasters', plugins_url('rsvpmaker-for-toastmasters/toastmasters.css') );
	wp_enqueue_script( 'script-toastmasters', plugins_url('rsvpmaker-for-toastmasters/toastmasters.js'), array(), '1.0.0', true );
}

add_action( 'wp_enqueue_scripts', 'toastmasters_css_js' );

function wp4_speech_prompt($event_post, $datetime) {
	
	global $wpdb;
		$signup = get_post_custom($event_post->ID);
		$prettydate = date('l F jS',$datetime);
		$printlink = rsvpmaker_permalink_query($event_post->ID,'print_agenda=1');
		
		$toastmaster = $signup["_Toastmaster_of_the_Day_1"][0];
		if($toastmaster)
			{
			$userdata = get_userdata($toastmaster);
			$toastmaster_email = $userdata->user_email;

			$phone = '';
			if($userdata->mobile_phone)
				$phone .= ' M: ' . $userdata->mobile_phone;
			if($userdata->home_phone)
				$phone .= ' H: ' . $userdata->home_phone;
			if($userdata->work_phone)
				$phone .= ' W: ' . $userdata->work_phone;
			if(!empty($phone))
				$phone = "\n<br />".$phone;
		$tofday = sprintf("<strong>".__("Toastmaster of the Day",'rsvptoast').":</strong>\n<br >%s %s<br /><a href=\"mailto:%s\">%s</a>",$userdata->display_name, $phone,$userdata->user_email ,$userdata->user_email);	
			}
		else
			{
				$tofday = "<strong>".__("Toastmaster of the Day not assigned",'rsvptoast')."</strong>";
				$toastmaster_email = get_bloginfo('admin_email');
			}
			
$lines = explode("\n",$event_post->post_content);
foreach($lines as $line)
	{
		if(strpos($line,'role') )
		{
		$cells = explode('"',$line);
		$role = $cells[1];
		$count = (isset($cells[3])) ? $cells[3] : 1;
		for($i = 1; $i <= $count; $i++)
			{
				$field = '_'.str_replace(' ','_',$role).'_'.$i;
				$roles[$field] = $role;
			}
		}
	}

	 foreach($roles as $field => $role)
		{
			$assigned = $signup[$field][0];
			if(is_numeric($assigned))
				{
					$userdata = get_userdata($assigned);
					$status = $userdata->display_name;
					$phone = '';
					if($userdata->mobile_phone)
						$phone .= ' M: ' . $userdata->mobile_phone;
					if($userdata->home_phone)
						$phone .= ' H: ' . $userdata->home_phone;
					if($userdata->work_phone)
						$phone .= ' W: ' . $userdata->work_phone;
					if(!empty($phone))
						$phone = "\n".$phone;
					if($role == 'Speaker')
						{
						$manual = $signup['_manual'.$field][0];
						if( empty($manual) || strpos($manual,'Manual / Speech') )
							$manual = __("PLEASE ENTER MANUAL / SPEECH TIMING REQUIREMENT ON WEBSITE",'rsvptoast');
						$manual = "\n<br />$manual\n<br />".__("Remember to supply the Toastmaster of the Day with the title of your speech and an introduction.",'rsvptoast');
						}
					else
						$manual = '';
					if(($assigned != $toastmaster) && isset($userdata->user_email) )
						{
						$yourassign[$userdata->user_email] .= '<p><strong>'.str_replace('_',' ',$role)." $manual </strong></p>\n";
						$yourassign_subj[$userdata->user_email] .= ' - '.str_replace('_',' ',$role);
						}
				}
			else
				$status = $assigned;
			
			if(empty($assigned) || ($assigned == 0) )
				{
					$open[$role]++;
					$openings++;
				}
			
			$roster .= sprintf("<strong>%s:</strong>\n%s %s"."\n",str_replace('_',' ',$role), $status, $phone);
			
		}
				
			$message = sprintf( __("You are scheduled to serve as Toastmaster of the Day this coming Friday. If, for any reason, you will not be able to fulfill this duty, please let the club officers know as soon as possible.

Through the website, you can print the agenda and look up contact information for club members. 

Print the agenda by clicking this link: <a href=\"%s\">%s</a>

IMPORTANT: Don't print the agenda too far in advance because people may sign up for (or withdraw from) roles online during the week. 
The website resources are explained in more detail here:

http://wp4toastmasters.com/

Part of the job of Toastmaster of the Day is to call other members who have taken on a role to make sure they are aware of these duties. Ideally, you also want to fill any open spots on the roster prior to the day of the meeting.

Here is the roster so far:

%s

Here is the current contacts list:
","rsvptoast"), $printlink, $printlink, $roster );

$message .= wp4_email_contacts();

echo "<h2>$toastmaster_email</h2>".nl2br($message);

	$mail["subject"] = "You are the Toastmaster for $prettydate";
	$mail["html"] = "<html>\n<body>\n".wpautop($message)."\n</body></html>";
	$mail["to"] = $toastmaster_email;
	$mail["from"] = ($rsvp_options["smtp_useremail"]) ? $rsvp_options["smtp_useremail"] : get_bloginfo('admin_email');
	$mail["fromname"] = get_bloginfo('name');
	echo awemailer($mail);

foreach($yourassign as $email => $duty)
	{
		$message = sprintf("<html><body><p>".__("Your assigned duty for our meeting on",'rsvptoast')." %s</p>
		
%s

<p>".__("If for any reason you cannot attend, please notify the Toastmaster of the Day",'rsvptoast').":</p>

<p>%s</p></body></html>",$pretydate,$duty,$tofday);

	global $rsvp_options;
	$mail["subject"] = __("Toastmasters duty for",'rsvptoast'). " $prettydate ".$yourassign_subj[$email];
	$mail["html"] = $message;
	$mail["from"] = ($rsvp_options["smtp_useremail"]) ? $rsvp_options["smtp_useremail"] : get_bloginfo('admin_email');
	$mail["fromname"] = get_bloginfo('name');
	$mail["to"] = $email;
	if($toastmaster_email)
		$mail["replyto"] = $toastmaster_email;
	awemailer($mail);
	echo "<br />";
	print_r($mail);
	
	}		

}

function wp4_email_contacts(  ) {

$blogusers = get_users('blog_id=1&orderby=nicename');
    foreach ($blogusers as $user) {
	$userdata = get_userdata($user->ID);
	//print_r($userdata);
	//echo " $userdata->first_name test<br />";
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);
	$members[$index] = $userdata;
	}
	
	ksort($members);
	foreach($members as $userdata) {
		
		if($userdata->last_name == "AVAILABLE")
			continue;
		
		$output .= "\n\n".$userdata->first_name.' '.$userdata->last_name."\n".$userdata->status."\n";

$contactmethods['home_phone'] = __("Home Phone",'rsvptoast');
$contactmethods['work_phone'] = __("Work Phone",'rsvptoast');
$contactmethods['mobile_phone'] = __("Mobile Phone",'rsvptoast');
$contactmethods['user_email'] = __("Email",'rsvptoast');
$contactmethods['status'] = __("Status",'rsvptoast');

	foreach($contactmethods as $name => $value)
		{
		$trimmed = trim($userdata->$name);
		if(empty($trimmed))
			continue;
		$output .= sprintf("%s: %s\n",$value,$trimmed);
		}
	}
return $output;
}

add_action( 'admin_bar_menu', 'toolbar_add_member', 999 );

function toolbar_add_member( $wp_admin_bar ) {

if( !current_user_can('list_users') )
	return $wp_admin_bar;
	$args = array(
		'id'    => 'add_member',
		'title' => __('Member','rsvptoast'),
		'href'  => admin_url('users.php?page=add_awesome_member'),
		'parent' => 'new-content',
		'meta'  => array( 'class' => 'add_member' )
	);
	$wp_admin_bar->add_node( $args );
}

if(!function_exists('rsvpmaker_permalink_query') )
{
function rsvpmaker_permalink_query ($id, $query = '') {

$key = "pquery_".$id;
$p = wp_cache_get($key);
if(!$p)
	{
		$p = get_post_permalink($id);
		$p .= strpos($p,'?') ? '&' : '?';
		wp_cache_set($key,$p);
	}

if(is_array($query) )
	{
		foreach($query as $name => $value)
			$qstring .= $name.'='.$value.'&';
	}
else
	{
		$qstring = $query;
	}
	
	return $p.$qstring;
	
}
} // end function exists

function toastmasters_datebox_message () {
echo '<div style="padding: 5px; margin: 5px; backround-color: #eee; border: thin dotted black;">'.__('For a regular Toastmasters meeting, do not worry about the parameters below. You may use this RSVP functionality to schedule other sorts of events (for example, training or open house events.)','rsvptoast').'</div>';
}

add_action ('rsvpmaker_datebox_message','toastmasters_datebox_message');

function wp4toast_template() {

global $wpdb;
$sql = "SELECT ID FROM `$wpdb->posts` WHERE `post_content` LIKE '%[toastmasters%' AND post_status='publish' ORDER BY `ID` DESC ";
if($wpdb->get_var($sql))
	return;

$default = __('[agenda_note comment="text between here and /agenda_note will be shown on the agenda only"]

<strong>Club Mission:</strong> We provide a supportive and positive learning experience in which members are empowered to develop communication and leadership skills, resulting in greater self-confidence and personal growth.

<strong>Sgt. at Arms</strong> <em>calls the meeting to the order,</em>

[/agenda_note]

[toastmaster role="Invocation" count="1" agenda_note="" ]

[agenda_note comment="text between here and /agenda_note will be shown on the agenda only"]

<strong>President </strong>or<strong> Presiding Officer</strong> <em>leads the self-introductions</em>

Introduces the <strong>Toastmaster of the Day</strong>

[/agenda_note]

[toastmaster role="Toastmaster of the Day" count="1" agenda_note="Introduces supporting roles. Leads the meeting." ]

[toastmaster role="Ah Counter" count="1" agenda_note="" indent="1" ]

[toastmaster role="Timer" count="1" agenda_note="" indent="1" ]

[toastmaster role="Vote Counter" count="1" agenda_note="" indent="1" ]

[toastmaster role="Body Language Monitor" count="1" agenda_note="" indent="1" ]


[toastmaster role="Grammarian" count="1" agenda_note="Leads word of the day contest." indent="1" ]

[toastmaster role="Topics Master" count="1" agenda_note="" ]


[toastmaster role="Humorist" count="1" agenda_note="" ]

[toastmaster role="Speaker" count="3" agenda_note="" ]

[toastmaster role="Backup Speaker" count="1" agenda_note="" ]

[toastmaster role="General Evaluator" count="1" agenda_note="Explains the importance of evaluations. Introduces Evaluators. Asks for Grammarian report. Asks for Body Language Monitor report (and awarding of Best Gestures Ribbon). Gives overall evaluation of the meeting." ]

[toastmaster role="Evaluator" count="3" agenda_note="" ]

[toastmaster themewords="1" ]

[toastmaster officers="1" label="Officers" ]','rsvptoast');

	$post = array(
	  'post_content'   => $default,
	  'post_name'      => 'toastmasters-meeting',
	  'post_title'     => 'Toastmasters Meeting',
	  'post_status'    => 'publish',
	  'post_type'      => 'rsvpmaker',
	  'post_author'    => $user_id,
	  'ping_status'    => 'closed'
	);
	$templateID = wp_insert_post($post);

	if($parent_id = wp_is_post_revision($templateID))
		{
		$templateID = $parent_id;
		}
	$template["hour"]= 19;
	$template["minutes"] = '00';
	$template["week"] = 6;
	$template["dayofweek"] = 1;

	update_post_meta($templateID, '_sked', $template);

$default = __('[agenda_note label="" sep="" comment="block of text continues until /agenda_note"]

<strong>Club Mission:</strong> We provide a supportive and positive learning experience in which members are empowered to develop communication and leadership skills, resulting in greater self-confidence and personal growth.

<strong>Sgt. at Arms</strong> <em>calls the meeting to the order</em>

<strong>President </strong>or<strong> Presiding Officer</strong> <em>leads the self-introductions</em>

Introduces the <strong>Contest Master</strong>

[/agenda_note]

[toastmaster role="Contest Master" count="1" agenda_note="Introduces supporting roles. Leads the meeting." ]

[toastmaster role="Chief Judge" count="1" agenda_note="" ]

[toastmaster role="Timer" count="1" agenda_note="" ]

[toastmaster role="Vote Counter" count="1" agenda_note="" ]

[toastmaster role="Videographer" count="1" agenda_note="" ]

[toastmaster role="International Speech Contestant" count="6" agenda_note="" ]

[toastmaster role="Table Topics Contestant" count="6" agenda_note="" ]

[toastmaster role="Humorous Speech Contestant" count="6" agenda_note="" ]

[toastmaster role="Evaluation Contest Contestant" count="6" agenda_note="" ]

[agenda_note label="" sep="" comment="block of text continues until /agenda_note"]

<strong>Club Dues:</strong> Please pay your club dues of $5 per week. See Treasurer Bruce Goldfarb if you need to get caught up. Prepayment discount: $104 for 26 weeks

[/agenda_note]

[toastmaster officers="1" label="Officers" ]','rsvptoast');

	$post = array(
	  'post_content'   => $default,
	  'post_name'      => 'contest',
	  'post_title'     => 'Contest',
	  'post_status'    => 'publish',
	  'post_type'      => 'rsvpmaker',
	  'post_author'    => $user_id,
	  'ping_status'    => 'closed'
	);
	$templateID = wp_insert_post($post);

	if($parent_id = wp_is_post_revision($templateID))
		{
		$templateID = $parent_id;
		}
	$template["hour"]= 19;
	$template["minutes"] = '00';
	$template["week"] = 6;
	$template["dayofweek"] = 1;

	update_post_meta($templateID, '_sked', $template);

}

register_activation_hook( __FILE__, 'wp4toast_template' );

if($_GET["page"] == 'agenda_setup')
	add_action('admin_head', 'agenda_setup_js');

function agenda_setup_js () {
	wp_enqueue_script( 'jquery-ui-sortable' );
}

add_action( 'wp_ajax_rsvptoast_save', 'rsvptoast_save_callback' );

function rsvptoast_save_callback() {
	global $wpdb; // this is how you get access to the database

	print_r($_POST);

	die(); // this is required to terminate immediately and return a proper response
}

function agenda_setup () {
global $wpdb;

if($_POST)
{
		
	$post_id = (int) $_POST["post_id"];
	$permalink = get_permalink($post_id);
	$agenda_link = rsvpmaker_permalink_query($post_id, 'print_agenda=1');

printf('<div id="message" class="updated">
		<p><strong>'.__("Meeting Agenda updated",'rsvptoast').'.</strong> <a href="%s">'.__("View Form",'rsvptoast').'</a> | <a href="%s">'.__("View Agenda",'rsvptoast').'</a></p>
	</div>',$permalink, $agenda_link);
	if($_POST["sked"] )
		{
				echo rsvp_template_update_checkboxes($post_id);
		}

	$item_array = explode(",",$_POST["order"]);
	foreach($item_array as $item)
		{
			if($_POST["remove"][$item])
				echo '<p style="color: red;">'.__("Remove",'rsvptoast').': '.$item.'</p>';
			elseif($_POST["agenda_note"][$item])
				{
				$output .= '[agenda_note ';
				$atts = $_POST["atts"][$item];
				foreach($atts as $name => $value)
					$output .= $name .'="'.$value.'" ';
				$output .= ']'.$_POST["agenda_note"][$item].'[/agenda_note]'."\n\n";
				//echo $output . '<br />';
				}
			elseif($_POST["agenda_layout"][$item])
				{
				$output .= '[agenda_layout ';
				if($_POST["agenda_sidebar"] == $item)
					$output .= ' sidebar="1" ';
				$output .= ']'."\n\n";
				}
			else
				{
				$output .= '[toastmaster ';
				$atts = $_POST["atts"][$item];
				foreach($atts as $name => $value)
					$output .= $name .'="'.$value.'" ';
				$output .= ']'."\n\n";
				}
		}
	
	$my_post = array(
      'ID'           => $post_id,
      'post_title' => $_POST["post_title"],
      'post_content' => $output
  );
   wp_update_post( $my_post );
if($_POST["sidebar"])
	{
		update_post_meta($post_id,'_tm_sidebar', $_POST["sidebar"]);
		update_post_meta($post_id,'_sidebar_officers', $_POST["sidebar_officers"]);
		if($_POST["template_sidebar"])
			{
				$id = $_POST["template_sidebar"];
				update_post_meta($id,'_tm_sidebar', $_POST["sidebar"]);
				update_post_meta($id,'_sidebar_officers', $_POST["sidebar_officers"]);
			}
		if($_POST["option_sidebar"])
			{
				update_option('_tm_sidebar', $_POST["sidebar"]);
				update_option('_sidebar_officers', $_POST["sidebar_officers"]);
			}
	}
if($_POST["enable_sidebar_layout"])
	update_option("wp4toastmasters_agenda_layout",'sidebar');

}

if($_GET["post_id"])
{
$post_id = (int) $_GET["post_id"];
global $post;
$post = get_post($post_id);
global $agenda_setup_item;
?>
<style>
ul#sortable li {
width: 90%;
min-height: 100px;
padding: 10px;
margin-bottom: 10px;
border: thick dotted #8CF;
cursor:move;
}
h1, h1 input[type="text"] {
font-size: 30px;
}
a.removex, a.show
{
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    border: solid 1px #888;
	padding: 3px;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.4);
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 1px 1px rgba(0, 0, 0, 0.2);
	cursor: pointer;
}
</style>
<form id="agenda_form" method="post" action = "<?php echo admin_url('edit.php?post_type=rsvpmaker&page=agenda_setup&post_id='.$post_id); ?>">
<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
<div style="float: right; width: 250px; text-align: center; margin-right: 10px;"><a href="<?php echo edit_template_url($post_id); ?>"><?php _e("Switch to standard WordPress editor<br />(shortcode view)",'rsvptoast');?></a></div>

<h1><?php _e("Title",'rsvptoast');?>: <input type="text" name="post_title" value="<?php echo $post->post_title; ?>" size="30" /></h1>
<p><em><?php _e("You can add and drop roles, change the number of openings for speakers and other roles, and specify other formatting parameters. To reorder items for the agenda and signup form, position your mouse over any of the blocks outlined in blue to drag-and-drop them into another position.",'rsvptoast');?></em></p>
<?php shortcode_eventdates($post->ID); ?>
<ul id="sortable">
<?php
echo do_shortcode($post->post_content);
//the_content();
?>
</ul>

<?php

$layout = get_option("wp4toastmasters_agenda_layout");
if($layout == 'sidebar')
{
$sidebar = '';
$post_sidebar = get_post_meta($post_id,'_tm_sidebar', true);
$post_sidebar_officers = get_post_meta($post_id,'_sidebar_officers', true);
$template_id = get_post_meta($post_id,'_meet_recur', true);
if($template_id)
	{
	$template_sidebar = get_post_meta($template_id,'_tm_sidebar', true);
	$template_checked = (empty($template_sidebar)) ? ' checked="checked" ' : '';
	}
$option_sidebar = get_option('_tm_sidebar');

$option_checked = (empty($option_sidebar)) ? ' checked="checked" ' : '';

if(!empty($post_sidebar)){
$sidebar = $post_sidebar;
$sidebar_officers = $post_sidebar_officers;
	}
	else {
	if(!empty($template_sidebar))
		{
		$sidebar = $template_sidebar;
		$sidebar_officers= get_post_meta($template_id,'_sidebar_officers', true);
		}
	elseif(!empty($option_sidebar))
		{
		$sidebar = $option_sidebar;
		$sidebar_officers = get_option('_sidebar_officers');
		}
	}
agenda_sidebar_editor ($sidebar, $sidebar_officers);
if($template_id)
	printf('<p><input type="checkbox" name="template_sidebar" value="%d" %s /> '.__('Default sidebar for this template','rsvptoast'),$template_id, $template_checked);
printf('<p><input type="checkbox" name="option_sidebar" value="1" %s /> '.__('Default sidebar (if not set in template)','rsvptoast'),$option_checked);
}
else
	echo '<p><input type="checkbox" name="enable_sidebar_layout" value="1" /> '.__('Enable agenda layout with sidebar','rsvptoast').'</p>';

?>
<input type="hidden" id="order" name="order" value="<?php for($i = 0; $i <= $agenda_setup_item; $i++) { if($i > 0) echo ","; echo "item_".$i; } ?>">
<?php submit_button(); ?>
</form>

<p><button id="add_role"><?php _e("Add Role",'rsvptoast');?></button> <button id="add_note"><?php _e("Add Agenda Note",'rsvptoast');?></button> <button id="add_officers"><?php _e("Add Officers",'rsvptoast');?></button> <button id="add_themewords"><?php _e("Add Theme/Words of the Day",'rsvptoast');?></button> <!--button id="two_column">Two-Column Agenda</button--></p>

<script>
jQuery(function($){
	
	window.agenda_setup_item = <?php echo $agenda_setup_item;?>;

        $( "#sortable" ).sortable({
            placeholder: "ui-state-highlight",
            cursor: 'crosshair',
            update: function(event, ui) {
                var order = $("#sortable").sortable("toArray");
                $('#order').val(order.join(","));
            }
    });

	$(".advanced").hide();

	$(".show").click(function(e) {
	e.preventDefault();
	$(".advanced").show();
	$(".show").hide();
    });

	$(".removex").click(function(e) {
	e.preventDefault();
	this.closest("li").remove();
    });

	$("#add_role").click(function() {
	agenda_setup_item++;
	var item_index = 'item_' + agenda_setup_item;
	var neworder = $('#order').val() + ',' + item_index; 
	$('#order').val( neworder );
  $('#sortable').append('<li id="' + item_index + '"><div class="rolefield"><input type="text" name="atts[' + item_index + '][role]" size="60" id="field_' + item_index + '" placeholder="<?php _e("Role",'rsvptoast');?>" value="" /><select name="atts[' + item_index + '][count]"><option value="1">1</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select> <input type="checkbox" name="atts[' + item_index + '][indent]" value="1"  /> Indent<br /><input type="text" name="atts[' + item_index + '][agenda_note]"  size="60" id="rolenotefield_' + item_index + '" placeholder="<?php _e("Role Note",'rsvptoast');?>" value="" /> (<?php _e("note displayed on agenda only",'rsvptoast');?>)<br /><input type="text" name="atts[' + item_index + '][time]" size="60" id="rolenotefield_' + item_index + '" placeholder="<?php _e("Time (optional): 7:15 pm OR 7:15 pm, 7:30 pm, 7:45 pm",'rsvptoast');?>" value="" /> <br /><br /><input type="checkbox" name="remove[' + item_index + ']" value="1" /> <?php _e("Remove",'rsvptoast');?><br /></div></li>');
});

	$("#add_note").click(function() {
	agenda_setup_item++;
	var item_index = 'item_' + agenda_setup_item;
	var neworder = $('#order').val() + ',' + item_index; 
	$('#order').val( neworder );
  $('#sortable').append('<li id="' + item_index + '"><div class="note"><textarea name="agenda_note[' + item_index + ']" cols="80" rows="3" id="agenda_note_' + item_index + '" placeholder="<?php _e("Agenda Note",'rsvptoast');?>"/></textarea> <br /><?php _e("Display on",'rsvptoast');?>: <select name="atts[' + item_index + '][agenda_display]" ><option value="agenda"><?php _e("agenda",'rsvptoast');?></option><option value="web"><?php _e("web",'rsvptoast');?></option><option value="both"><?php _e("both",'rsvptoast');?></option></select><input type="checkbox" name="atts[' + item_index + '][officers]" value="1"  /> <?php _e("List Officers",'rsvptoast');?> <input type="text" name="atts[' + item_index + '][label]" size="60" id="field_item_' + item_index + '" placeholder="<?php _e("Label for Officers (default: Officers)",'rsvptoast');?>" value="" /><input type="hidden" name="atts[' + item_index + '][sep]" value="" ><br /><input type="checkbox" name="remove[' + item_index + ']" value="1" /> <?php _e("Remove",'rsvptoast');?> </div></li>');
});

	$("#add_officers").click(function() {
	agenda_setup_item++;
	var item_index = 'item_' + agenda_setup_item;
	var neworder = $('#order').val() + ',' + item_index; 
	$('#order').val( neworder );
  $('#sortable').append('<li id="' + item_index + '"><div class="officers" ><input type="hidden" name="atts[' + item_index + '][officers]" value="1" /><input type="text" name="atts[' + item_index + '][label]" size="60" id="field_' + item_index + '" placeholder="<?php _e("<strong>tk</strong>",'rsvptoast');?>" value="<?php _e("Displays listing of officers on agenda",'rsvptoast');?>Officers" /> <br /><input type="checkbox" name="remove[' + item_index + ']" value="1" /> Remove </div></li>');
});

	$("#add_themewords").click(function() {
	agenda_setup_item++;
	var item_index = 'item_' + agenda_setup_item;
	var neworder = $('#order').val() + ',' + item_index; 
	$('#order').val( neworder );
  $('#sortable').append('<li id="' + item_index + '"><div class="themewords"><input type="hidden" name="atts[item_15][themewords]" value="1" /><?php _e("Block of text for meeting theme, words of the day, or other notes (can be edited along with role assignments).",'rsvptoast');?><br /><input type="checkbox" name="remove[' + item_index + ']" value="1" /> <?php _e("Remove",'rsvptoast');?> </div></li>');
});

	$("#two_column").click(function() {
	for(i = 0; i < 3; i++)
	{
	agenda_setup_item++;
	var item_index = 'item_' + agenda_setup_item;
	var neworder = $('#order').val() + ',' + item_index; 
	$('#order').val( neworder );
	if(i == 2)
  		$('#sortable').append('<li id="' + item_index + '"><em><?php _e("Agenda Layout: to divide the agenda into 2 columns, use 3 of these blocks, one at the beginning of the first column, one at the beginning of the second column, and a third at the end of the second column. One column may be designated the sidebar (skinnier column).",'rsvptoast');?></em><input type="hidden" name="agenda_layout[' + item_index + ']" value="1"><br /><input type="checkbox" name="remove[' + item_index + ']" value="1" /> <?php _e("Remove",'rsvptoast');?> </div></li>');
	else
  		$('#sortable').append('<li id="' + item_index + '"><em><?php _e("Agenda Layout: to divide the agenda into 2 columns, use 3 of these blocks, one at the beginning of the first column, one at the beginning of the second column, and a third at the end of the second column. One column may be designated the sidebar (skinnier column).",'rsvptoast');?></em><input type="hidden" name="agenda_layout[' + item_index + ']" value="1"><br /><input type="radio" name="agenda_sidebar" value="' + item_index + '" /> Sidebar<br /><input type="checkbox" name="remove[' + item_index + ']" value="1" /> <?php _e("Remove",'rsvptoast');?> </div></li>');	
	}
});

})
</script>
<?php
}
else
{
		$dayarray = Array(__("Sunday",'rsvpmaker'),__("Monday",'rsvpmaker'),__("Tuesday",'rsvpmaker'),__("Wednesday",'rsvpmaker'),__("Thursday",'rsvpmaker'),__("Friday",'rsvpmaker'),__("Saturday",'rsvpmaker'));
		$weekarray = Array(__("Varies",'rsvpmaker'),__("First",'rsvpmaker'),__("Second",'rsvpmaker'),__("Third",'rsvpmaker'),__("Fourth",'rsvpmaker'),__("Last",'rsvpmaker'),__("Every",'rsvpmaker'));
	
			$sql = "SELECT *, $wpdb->posts.ID as postID
FROM $wpdb->postmeta
JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
WHERE meta_key='_sked' AND post_content LIKE '%[toastmaster%'";
			
		$results = $wpdb->get_results($sql);
		if($results)
		foreach ($results as $r)
			{
			$sked = unserialize($r->meta_value);

		//backward compatability
		if(is_array($sked["week"]))
			{
				$weeks = $sked["week"];
				$dows = $sked["dayofweek"];
			}
		else
			{
				$weeks = array();
				$dows = array();
				$weeks[0] = $sked["week"];
				$dows[0] = $sked["dayofweek"];
			}

		$dayarray = Array(__("Sunday",'rsvpmaker'),__("Monday",'rsvpmaker'),__("Tuesday",'rsvpmaker'),__("Wednesday",'rsvpmaker'),__("Thursday",'rsvpmaker'),__("Friday",'rsvpmaker'),__("Saturday",'rsvpmaker'));
		$weekarray = Array(__("Varies",'rsvpmaker'),__("First",'rsvpmaker'),__("Second",'rsvpmaker'),__("Third",'rsvpmaker'),__("Fourth",'rsvpmaker'),__("Last",'rsvpmaker'),__("Every",'rsvpmaker'));
		$s = '';
		if((int)$weeks[0] == 0)
			$s = __('Schedule Varies','rsvpmaker');
		else
			{
			foreach($weeks as $week)
				{
				if(!empty($s))
					$s .= '/ ';
				$s .= $weekarray[(int) $week].' ';
				}
			foreach($dows as $dow)
				$s .= $dayarray[(int) $dow] . ' ';	
			}

			$template_options .= sprintf('<option value="%d">%s (%s)</option>',$r->postID,$r->post_title,$s);
			}

			$sql = "SELECT *, $wpdb->posts.ID as postID, datetime > CURDATE( ) as current
FROM `".$wpdb->prefix."rsvp_dates`
JOIN $wpdb->posts ON ".$wpdb->prefix."rsvp_dates.postID = $wpdb->posts.ID
WHERE datetime >= '".date('Y-m')."-1' AND $wpdb->posts.post_content LIKE '%[toastmaster%' AND $wpdb->posts.post_status = 'publish'
ORDER BY datetime LIMIT 0,100"; 
		$results = $wpdb->get_results($sql);
		if($results)
		foreach ($results as $r)
			{
			$event_options .= sprintf('<option value="%d">%s %s</option>',$r->postID,$r->post_title,$r->datetime);
			}
			
		$action = admin_url('edit.php');
		
		printf('<form method="get" action="%s"><p>'.__("Get Agenda For",'rsvptoast').' <select name="post_id"><optgroup label="'.__("Templates",'rsvptoast').'">%s</optgroup><optgroup label="'.__("Events",'rsvptoast').'">%s</optgroup></select>
<input type="hidden" name="post_type" value="rsvpmaker" /><input type="hidden" name="page" value="agenda_setup" />		
		</p>',$action, $template_options, $event_options);
		submit_button(__('Get Agenda','rsvptoast'));
		echo '</form>';

		printf('<form method="put" action="%s">',$action);
		submit_button(__('Make New Agenda Template','rsvptoast'));
		echo '</form>';

}

}

function new_agenda_template() {
global $current_user;
if($_REQUEST["submit"] != 'Make New Agenda Template')
	return;
$default = '[toastmaster role="Speaker" count="1" ]';

	$post = array(
	  'post_content'   => $default,
	  'post_title'     => 'Title Goes Here',
	  'post_status'    => 'publish',
	  'post_type'      => 'rsvpmaker',
	  'post_author'    => $current_user->ID,
	  'ping_status'    => 'closed'
	);
	$templateID = wp_insert_post($post);

	if($parent_id = wp_is_post_revision($templateID))
		{
		$templateID = $parent_id;
		}
	$template["hour"]= 19;
	$template["minutes"] = '00';
	$template["week"] = 6;
	$template["dayofweek"] = 1;

	update_post_meta($templateID, '_sked', $template);
	header('Location: '.admin_url('edit.php?post_type=rsvpmaker&page=agenda_setup&post_id='.$templateID));
	exit();
}

add_action('admin_init','new_agenda_template');

function toast_activate() {
global $wpdb;

$wpdb->show_errors();

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

$sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."toastmasters_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `datetime` date NOT NULL,
  `role` varchar(255) CHARACTER SET utf8 NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
dbDelta($sql);
}

register_activation_hook( __FILE__, 'toast_activate' );

function toolbar_link_to_agenda( $wp_admin_bar ) {
if(!current_user_can('edit_others_rsvpmakers') )
	return;
global $post;
if(!strpos($post->post_content,'toastmaster') )
	return;
	$role_editor = admin_url('edit.php?post_type=rsvpmaker&page=agenda_setup&post_id='.$post->ID);
	$args = array(
		'id'    => 'agenda_setup',
		'title' => 'Agenda Setup',
		'href'  => $role_editor,
		'meta'  => array( 'class' => 'agenda-setup-page')
	);
	$wp_admin_bar->add_node( $args );
}

if(strpos($_SERVER['REQUEST_URI'],'rsvpmaker=') || strpos($_SERVER['REQUEST_URI'],'rsvpmaker/'))
	add_action( 'admin_bar_menu', 'toolbar_link_to_agenda', 999 );

function edit_template_url($post_id) {
return admin_url('post.php?action=edit&post='.$post_id);
}

function add_from_template_url($post_id) {
return admin_url('edit.php?post_type=rsvpmaker&page=rsvpmaker_template_list&t='.$post_id);
}

function agenda_setup_url($post_id) {
return admin_url('edit.php?post_type=rsvpmaker&page=agenda_setup&post_id='.$post_id);
}

function member_only_content($content) {

if( !in_category('members-only') ) 
	return $content;

if(!is_user_member_of_blog() )
return '<div style="width: 100%; background-color: #ddd;">'.__('You must be logged in and a member of this blog to view this content','rsvptoast').'</div>'. sprintf('<div id="member_only_login"><a href="%s">'.__('Login to View','rsvptoast').'</a></div>',site_url('/wp-login.php?redirect_to='.urlencode(get_permalink()) ) );
else
return $content.'<div style="width: 100%; background-color: #ddd;">'.__('Note: This is member-only content (login required)','rsvptoast').'</div>';

}

add_filter('the_content','member_only_content');

// widget for members only posts
class WP_Widget_Members_Posts extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_members_entries', 'description' => __( "Your site&#8217;s most recent members-only posts.",'rsvptoast') );
		parent::__construct('members-posts', __('Members Posts','rsvptoast'), $widget_ops);
		$this->alt_option_name = 'widget_members_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );
	}

	public function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'widget_members_posts', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Members Only Posts','rsvptoast' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		/**
		 * Filter the arguments for the members Posts widget.
		 *
		 * @since 3.4.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the members posts.
		 */
		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'category_name' => 'members-only',
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true
		) ) );

		if ($r->have_posts()) :
?>
		<?php echo $args['before_widget']; ?>
		<?php if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
		<ul>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><?php echo get_the_date(); ?></span>
			<?php endif; ?>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $args['after_widget']; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'widget_members_posts', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_members_entries']) )
			delete_option('widget_members_entries');

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete('widget_members_posts', 'widget');
	}

	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','rsvptoast' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:','rsvptoast' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?','rsvptoast' ); ?></label></p>
<?php
	}
}

//widget for posts excluding members only
class WP_Widget_Club_News_Posts extends WP_Widget {

	public function __construct() {
		$widget_ops = array('classname' => 'widget_club_news_entries', 'description' => __( "Your site&#8217;s most recent public blog posts.",'rsvptoast') );
		parent::__construct('club-news-posts', __('Club News Posts','rsvptoast'), $widget_ops);
		$this->alt_option_name = 'widget_club_news_entries';

		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );
	}

	public function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'widget_club_news_posts', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Club News','rsvptoast' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number )
			$number = 5;
		$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;

		/**
		 * Filter the arguments for the club_news Posts widget.
		 *
		 * @since 3.4.0
		 *
		 * @see WP_Query::get_posts()
		 *
		 * @param array $args An array of arguments used to retrieve the club_news posts.
		 */
		$category = get_category_by_slug('members-only');
		if($category)
			$qargs =  array(
			'posts_per_page'      => $number,
			'cat' => '-'.$category->term_id,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true);
		else
			$qargs =  array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true);
						
		$r = new WP_Query( apply_filters( 'widget_posts_args', $qargs ) );

		if ($r->have_posts()) :
?>
		<?php echo $args['before_widget']; ?>
		<?php if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		 ?>
		<ul>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<li>
				<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><?php echo get_the_date(); ?></span>
			<?php endif; ?>
			</li>
		<?php endwhile; ?>
		</ul>
		<?php echo $args['after_widget']; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'widget_club_news_posts', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_club_news_entries']) )
			delete_option('widget_club_news_entries');

		return $instance;
	}

	public function flush_widget_cache() {
		wp_cache_delete('widget_club_news_posts', 'widget');
	}

	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label></p>
<?php
	}
}

function wptoast_widgets () {
	register_widget("AwesomeWidget");
	register_widget( 'WP_Widget_Members_Posts' );
	register_widget( 'WP_Widget_Club_News_Posts' );
}
add_action( 'widgets_init', 'wptoast_widgets');

function club_news($args) {
ob_start();		
		$title = (!empty($args["title"]) ) ? $args["title"] : __('Club News','rsvptoast');
		$show_date = (!empty($args["show_date"])) ? 1 : 0;
		$show_excerpt = (!empty($args["show_excerpt"])) ? 1 : 0;
		$show_thumbnail = (!empty($args["show_thumbnail"])) ? 1 : 0;
		echo '<h2 class="club_news">'.$title."</h2>\n";
		$category = get_category_by_slug('members-only');
		if($category)
			$qargs =  array(
			'posts_per_page'      => $number,
			'cat' => '-'.$category->term_id,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true);
		else
			$qargs =  array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true);
						
		$r = new WP_Query( apply_filters( 'widget_posts_args', $qargs ) );

		if ($r->have_posts()) :
		 ?>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<h3>
				<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><?php echo get_the_date(); ?></span>
			<?php endif; ?>
			</h3>
			<?php
			
			if ( $show_thumbnail && has_post_thumbnail() ) : ?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php the_post_thumbnail('thumbnail'); ?>
				</a>
			<?php endif;			
			
			 if ( $show_excerpt ) : ?>
				<div class="post-excerpt"><?php the_excerpt(); ?></div>
			<?php endif; ?>
		<?php endwhile; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();
		endif;
return ob_get_clean();
}

add_action( 'pre_get_posts', 'toast_modify_query_exclude_category' );
function toast_modify_query_exclude_category( $query ) {
    if ( ! is_admin() && $query->is_main_query() && ! is_user_member_of_blog() )
		{
		$category = get_category_by_slug('members-only');
		if($category)
			$query->set( 'cat', '-'.$category->term_id );
		}
}

function members_only($args) {
ob_start();		
		$title = (!empty($args["title"]) ) ? $args["title"] : 'Members Only';
		$show_date = (!empty($args["show_date"])) ? 1 : 0;
		$show_excerpt = (!empty($args["show_excerpt"])) ? 1 : 0;
		$show_thumbnail = (!empty($args["show_thumbnail"])) ? 1 : 0;
		echo '<h2 class="club_news">'.$title."</h2>\n";
		$qargs =  array(
		'posts_per_page'      => $number,
		'category_name' => 'members-only',
		'no_found_rows'       => true,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true);

		$r = new WP_Query( apply_filters( 'widget_posts_args', $qargs ) );

		if ($r->have_posts()) :
		 ?>
		<?php while ( $r->have_posts() ) : $r->the_post(); ?>
			<h3>
				<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
			<?php if ( $show_date ) : ?>
				<span class="post-date"><?php echo get_the_date(); ?></span>
			<?php endif; ?>
			</h3>
			<?php
			
			if ( $show_thumbnail && has_post_thumbnail() ) : ?>
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
				<?php the_post_thumbnail('thumbnail'); ?>
				</a>
			<?php endif;			
			
			 if ( $show_excerpt ) : ?>
				<div class="post-excerpt"><?php the_excerpt(); ?></div>
			<?php endif; ?>
		<?php endwhile; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();
		endif;
return ob_get_clean();
}

add_shortcode('club_news','club_news');
add_shortcode('members_only','members_only');

function toast_excerpt_more( $more ) {
	return ' <a class="read-more" href="'. get_permalink( get_the_ID() ) . '">[' . __('Read More', 'your-text-domain') . ']</a>';
}
add_filter( 'excerpt_more', 'toast_excerpt_more' );


function toastmasters_sidebar_mce_css( $mce_css ) {
	if ( ! empty( $mce_css ) )
		$mce_css .= ',';

	$mce_css .= plugins_url( '/rsvpmaker-for-toastmasters/sidebar.css');

	return $mce_css;
}
if(strpos($_SERVER['REQUEST_URI'],'agenda_sidebar') )
	add_filter( 'mce_css', 'toastmasters_sidebar_mce_css' );

function agenda_sidebar () {

print_r($_REQUEST);

if($_POST["post_id"])
{
	$post_id = (int) $_POST["post_id"];
	update_post_meta($post_id,'_tm_sidebar',$_POST["sidebar"]);
	$o = ($_POST["sidebar_officers"] == '1') ? 1 : 0;
	update_post_meta($post_id,'_sidebar_officers',$o);
}
if($_POST["template_sidebar"])
{
	$post_id = (int) $_POST["template_sidebar"];
	update_post_meta($post_id,'_tm_sidebar',$_POST["sidebar"]);
	$o = ($_POST["sidebar_officers"] == '1') ? 1 : 0;
	update_post_meta($post_id,'_sidebar_officers',$o);
}
if($_POST["option_sidebar"])
{
	update_option('_tm_sidebar',$_POST["sidebar"]);
	$o = ($_POST["sidebar_officers"] == '1') ? 1 : 0;
	update_option('_sidebar_officers',$o);
}

$post_id= $_REQUEST["post_id"];

global $wpdb;
global $rsvp_options;

if($post_id)
{

$custom = get_post_custom($post->ID);
echo "<pre>".__("Custom",'rsvptoast')."\n";
print_r($custom);
echo "<pre>";

if(!$custom["_sked"][0])
	{
	$sql = "SELECT * FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post_id.' ORDER BY datetime';
	$row = $wpdb->get_row($sql);
	$date = date($rsvp_options["long_date"], strtotime($row->datetime) );
	}
else
	$date = ' ('.__('Template','rsvptoast').')';

if(!empty($custom["_tm_sidebar"][0])){
$sidebar = !empty($custom["_tm_sidebar"][0]);
$sidebar = !empty($custom["_sidebar_officers"][0]);
	}
	else {
	$template_sidebar = get_post_meta($custom["_meet_recur"][0],'_tm_sidebar', true);
	$option_sidebar = get_option('_tm_sidebar');
	if(!empty($template_sidebar))
		{
		$sidebar = $template_sidebar;
		$sidebar_officers= get_post_meta($custom["_meet_recur"][0],'_sidebar_officers', true);
		}
	elseif(!empty($option_sidebar))
		{
		$sidebar = $option_sidebar;
		$sidebar_officers = get_option('_sidebar_officers');
		}
	}

?>
<h3><?php _e('Sidebar for','rsvptoast'); echo " "; the_title(); echo " ".$date; ?></h3>
<form action="<?php echo admin_url('edit.php?post_type=rsvpmaker&page=agenda_sidebar&post_id='.$post_id); ?>" method="post">
<?php
agenda_sidebar_editor($sidebar, $sidebar_officers);
submit_button();
if($custom["_meet_recur"][0])
	sprintf('<p><input type="checkbox" name="template_sidebar" value="%s" /> %s</p>',$custom["_meet_recur"][0],__('Apply to meeting template','rsvptoast'));
?>
<p><input type="checkbox" name="option_sidebar" value="1" /> Default if not set for meeting or in template</p>
<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
</form>
<?php
}// end display form if post_id is set
else
{
?>
<h3><?php _e('Default Sidebar');?></h3>
<form action="<?php echo admin_url('edit.php?post_type=rsvpmaker&page=agenda_sidebar&post_id='.$post_id); ?>" method="post">
<?php
$sidebar = get_option('_tm_sidebar');
$sidebar_officers = get_option('_sidebar_officers');
agenda_sidebar_editor($sidebar, $sidebar_officers);
submit_button();
?>
<input type="hidden" name="option_sidebar" value="1" />
</form>
<?php
}// end display form if post_id is set

global $wpdb;
		$dayarray = Array(__("Sunday",'rsvpmaker'),__("Monday",'rsvpmaker'),__("Tuesday",'rsvpmaker'),__("Wednesday",'rsvpmaker'),__("Thursday",'rsvpmaker'),__("Friday",'rsvpmaker'),__("Saturday",'rsvpmaker'));
		$weekarray = Array(__("Varies",'rsvpmaker'),__("First",'rsvpmaker'),__("Second",'rsvpmaker'),__("Third",'rsvpmaker'),__("Fourth",'rsvpmaker'),__("Last",'rsvpmaker'),__("Every",'rsvpmaker'));
	
			$sql = "SELECT *, $wpdb->posts.ID as postID
FROM $wpdb->postmeta
JOIN $wpdb->posts ON $wpdb->postmeta.post_id = $wpdb->posts.ID
WHERE meta_key='_sked' AND post_content LIKE '%[toastmaster%'";
			
		$results = $wpdb->get_results($sql);
		if($results)
		foreach ($results as $r)
			{
			$sked = unserialize($r->meta_value);
			$day = $dayarray[$sked["dayofweek"]];
			if($sked["week"])
				$day = $weekarray[$sked["week"]] ." ".$day;
			$template_options .= sprintf('<option value="%d">%s (%s)</option>',$r->postID,$r->post_title,$day);
			}

			$sql = "SELECT *, $wpdb->posts.ID as postID, datetime > CURDATE( ) as current
FROM `".$wpdb->prefix."rsvp_dates`
JOIN $wpdb->posts ON ".$wpdb->prefix."rsvp_dates.postID = $wpdb->posts.ID
WHERE datetime >= '".date('Y-m')."-1' AND $wpdb->posts.post_content LIKE '%[toastmaster%' AND $wpdb->posts.post_status = 'publish'
ORDER BY datetime LIMIT 0,100"; 
		$results = $wpdb->get_results($sql);
		if($results)
		foreach ($results as $r)
			{
			$event_options .= sprintf('<option value="%d">%s %s</option>',$r->postID,$r->post_title,$r->datetime);
			}
			
		$action = admin_url('edit.php');
		
		printf('<form method="get" action="%s"><p>'.__("Get Sidebar For",'rsvptoast').' <select name="post_id"><optgroup label="'.__("Templates",'rsvptoast').'">%s</optgroup><optgroup label="'.__("Events",'rsvptoast').'">%s</optgroup></select>
<input type="hidden" name="post_type" value="rsvpmaker" /><input type="hidden" name="page" value="agenda_sidebar" />		
		</p>',$action, $template_options, $event_options);
		submit_button(__('Get','rsvptoast'));
		echo '</form>';

}

function agenda_sidebar_editor ($sidebar, $sidebar_officers) {

?>
<h2><?php _e("Sidebar Content",'rsvptoast');?></h2>
<p><?php _e("Use this space for dues reminders, previews of upcoming events and other supporting information. You can include images as long as they are no more than 175 pixels wide.",'rsvptoast');?></p>
<div style="width: 500px;">
<?php
$editor_id = "sidebar";

$settings = array();

wp_editor( $sidebar, $editor_id, $settings );
?>
</div>
<p><input type="checkbox" name="sidebar_officers" value="1" <?php if($sidebar_officers) echo ' checked="checked" ' ?> > <?php _e("Include officer listing",'rsvptoast');?></p>
<?php
}

//boost random password complexity
function password_hurdle ($pass) {
$upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$lower = 'abcdefghijklmnopqrstuvwxyz';
$symbols = '!@#$%^&*()';
if(!preg_match('/[!@#$%^&*()]/',$pass) )
	$pass .= $symbols[ rand(0,9) ];
if(!preg_match('/[0-9]/',$pass) )
	$pass .= rand(0,9);
if(!preg_match('/[a-z]/',$pass) )
	$pass .= $lower[rand(0,25)];
if(!preg_match('/[A-Z]/',$pass) )
	$pass .= $upper[rand(0,25)];
return $pass;
}

?>