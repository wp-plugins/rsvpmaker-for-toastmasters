<?php

add_action( 'admin_menu', 'toastmasters_reports_menu' );

function toastmasters_reports_menu() {
add_menu_page(__('Toastmasters Reports','rsvptoast'), __('Toastmasters Reports','rsvptoast'), 'read', 'toastmasters_reports', 'toastmasters_reports',plugins_url('rsvpmaker-for-toastmasters/toastmasters-20.png'),'2.01');
$reconcilepg = add_submenu_page( 'toastmasters_reports', __('Reconcile','rsvptoast'), __('Reconcile','rsvptoast'), 'edit_others_rsvpmakers', 'toastmasters_reconcile', 'toastmasters_reconcile');
add_submenu_page( 'toastmasters_reports', __('Edit Stats','rsvptoast'), __('Edit Member Stats','rsvptoast'), 'edit_others_rsvpmakers', 'toastmasters_edit_stats', 'toastmasters_edit_stats');
add_submenu_page( 'toastmasters_reports', __('Record Attendance','rsvptoast'), __('Record Attendance','rsvptoast'), 'edit_others_rsvpmakers', 'toastmasters_attendance', 'toastmasters_attendance');
add_submenu_page( 'toastmasters_reports', __('Attendance Report','rsvptoast'), __('Attendance Report','rsvptoast'), 'read', 'toastmasters_attendance_report', 'toastmasters_attendance_report');
add_submenu_page( 'toastmasters_reports', __('Competent Communicator Progress Report','rsvptoast'), __('CC Progress','rsvptoast'), 'read', 'toastmasters_cc', 'toastmasters_cc');
add_submenu_page( 'toastmasters_reports', __('Competent Leader Progress Report','rsvptoast'), __('CL Progress','rsvptoast'), 'read', 'cl_report', 'cl_report');
add_submenu_page( 'toastmasters_reports', __('Mentors','rsvptoast'), __('Mentors','rsvptoast'), 'edit_others_rsvpmakers', 'toastmasters_mentors', 'toastmasters_mentors');
add_menu_page( __('Import Free Toast Host Data','rsvptoast'), __('Import Free Toast Host Data','rsvptoast'), 'edit_others_rsvpmakers', 'import_fth', 'import_fth');

add_action( 'admin_print_styles-'.$reconcilepg, 'toastmasters_css_js' );

}

$toast_roles = array(
'Ah Counter',
'Body Language Monitor',
'Evaluator',
'General Evaluator',
'Grammarian',
'Humorist',
'Speaker',
'Topics Master',
'Table Topics',
'Timer',
'Toastmaster of the Day',
'Vote Counter');

$competent_leader = array(
"Help Organize a Club Speech Contest",
"Help Organize a Club Special Event",
"Help Organize a Club Membership Campaign or Contest",
"Help Organize a Club PR Campaign",
"Help Produce a Club Newsletter",
"Assist the Club’s Webmaster",
"Befriend a Guest",
"PR Campaign Chair",
"Mentor for a New Member",
"Mentor for an Existing Member",
"HPL Guidance Committee Member",
"Membership Campaign Chair",
"Club PR Campaign Chair",
"Club Speech Contest Chair",
"Club Special Event Chair",
"Club Newsletter Editor",
"Club Webmaster");

function toastmasters_reports () {
global $wpdb;
global $toast_all_roles;
$toast_all_roles = array();
global $toast_roles;

?>
<style>
td,th {
border: thin solid #000;
text-align:center;	
	}
th.role {
	min-width: 90px;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

}
</style>
<div class="wrap">
<?php
if(is_admin())
{
fullscreen_link();

if($_POST)
	printf('<h2><a href="%s"  class="add-new-h2">'.__('Return to Report','rsvptoast').'</a> <a href="%s"  class="add-new-h2">'.__('Edit Member Stats','rsvptoast').'</a></h2>',admin_url('admin.php?page=toastmasters_reports'),admin_url('admin.php?page=toastmasters_edit_stats') );
else
	printf('<h2><a href="%s" class="add-new-h2">'.__('Edit Member Stats','rsvptoast').'</a></h2>',admin_url('admin.php?page=toastmasters_edit_stats') );
}

if($_POST["adj"])
	{
		$wpdb->show_errors();
		foreach($_POST["adj"] as $user_id => $newroles)
			{
				foreach($newroles as $role => $count)
					{
						if(!is_numeric($count))// || ($count != 0) || ($_POST["was"][$user_id][$role]) )
						{

						update_user_meta($user_id, $role, $count);
						}
						elseif($count != 0)
						{
							$sqldate = ($_POST["date_it"]) ? 'CURDATE() ' : " '0000-00-00' ";
							$sql = sprintf( "INSERT into %s SET user_id=%d, role='%s', quantity=%d, datetime=%s ",$wpdb->prefix.'toastmasters_history',$user_id, $role, $count, $sqldate );
							$wpdb->show_errors();
							echo $sql . " line 122<br />";

							$wpdb->query($sql);
						}
					}

printf('<div id="message" class="updated">
		<p><strong>%s</strong></p>
	</div>',__('Member stats updated','rsvptoast'));
					
			}
	if($_POST["addcl"])
		{
			$user_id = $_REQUEST["toastmaster"];
			$count = 1;
			foreach($_POST["addcl"] as $role)
				{
					if(!empty($role) )
					{
						$sql = sprintf( "INSERT into %s SET user_id=%d, role='%s', quantity=%d, datetime=CURDATE() ",$wpdb->prefix.'toastmasters_history',$user_id, $role, $count);
						$wpdb->query($sql);
						printf('<p>user: %s '.__('role','rsvptoast').': %s '.__('count','rsvptoast').': %s</p>',$user_id,$role,$count);
					}

				}
		}
printf('<div id="message" class="updated">
		<p><strong>%s.</strong></p>
	</div>',__('Competent leader credits updated','rsvptoast') );

	}
// display routines
if($_GET["toastmaster"])
	{
	global $competent_leader;
	if($_GET["edit"])
		printf('<form action="%s" method="post">',admin_url('admin.php?page=toastmasters_reports&adjust=1') );
	
		$id = $_GET["toastmaster"];
		$userdata = get_userdata($id);
		printf('<h2>%s %s</h2>',$userdata->first_name, $userdata->last_name);
		if($_GET["edit"])
		echo "<p><em>".__('Use this form to give credit for activities that were not tracked through the agenda.','rsvptoast')."</em></p>";
		$myroles = awesome_get_stats($userdata);
		foreach($toast_roles as $role)
			{
				if($_GET["edit"])
					{
					$count = 0;
					$in = sprintf('<input type="text" name="adj[%d][%s]" value="0" size="4" /> ',$userdata->ID,$role);
					}
				else
					$in = '';

				printf('<p>%s%d %s</p>',$in,$myroles[$role],$role); 
			}

		foreach($competent_leader as $role)
			{
				if(isset($myroles[$role]) )
				{
				if($_GET["edit"])
					{
					$count = 0;
					$in = sprintf('<input type="text" name="adj[%d][%s]" value="0" size="4" /> ',$userdata->ID,$role);
					}
				else
					$in = '';
				printf('<p>%s%d %s</p>',$in,$myroles[$role],$role); 
				}
			}

		
		if($_GET["edit"])
			{
				$tasks = '<option value="">Choose a Project</option>';
				foreach($competent_leader as $task)
					{
						$tasks .= sprintf('<option value="%s">%s</option>',$task,$task);
					}
				printf('<h3>'.__('Additonal Competent Leader Credits','rsvptoast').'</h3>
				<p><select name="addcl[]">%s</select></p>
				<p><select name="addcl[]">%s</select></p>
				<p><select name="addcl[]">%s</select></p>
				',$tasks,$tasks,$tasks);
			printf('<p><input type="text" name="adj[%d][%s]" value="0" size="4" /> '.__('Additional Comptent Communicator Speeches','rsvptoast').'</p>',$userdata->ID,"CC Speeches");
			}
		
		if(isset($userdata->toastuser_note) )
			{
				if($_GET["edit"])
					printf('<p><textarea name="adj[%s][toastuser_note]" rows="5" cols="80">%s</textarea>',$userdata->ID,$userdata->toastuser_note);
				else
					echo wpautop($userdata->toastuser_note);
			}
		elseif($_GET["edit"])
			printf('<p><textarea name="adj[%s][toastuser_note]" rows="5" cols="80"></textarea>',$userdata->ID);
		
		if($_GET["edit"])
			echo '<p><input type="checkbox" name="date_it" value="1" checked="checked" /> '.__("Include today's date (clear checkbox if you do NOT want these adjustments included in reports on the current period).",'rsvptoast').'</p>
<input type="hidden" name="toastmaster" value="'.$id.'" />
<p><button>Save</button></p></form>';
		else
	printf('<h2><a href="%s" class="add-new-h2">'.__('Edit Stats For This Member','rsvptoast').'</a></h2>',admin_url('admin.php?page=toastmasters_edit_stats#'.$userdata->ID) );
			
		echo get_latest_speeches($id, $myroles);
	}
elseif($_GET["edit_adjustments"])
	{
	if($_POST["delete_adjustment"])
		{
			foreach($_POST["delete_adjustment"] as $delete_id)
				{
				$delete_id = (int) $delete_id;
				$wpdb->query("DELETE FROM ".$wpdb->prefix."toastmasters_history WHERE id=".$delete_id);
				}
printf('<div id="message" class="updated">
		<p><strong>%s.</strong></p>
	</div>',__('Member stats updated','rsvptoast') );

		}
	printf('<form method="post" action="%s">',admin_url('admin.php?page=toastmasters_reports&edit_adjustments=1'));
	$sql = "SELECT * FROM ".$wpdb->prefix."toastmasters_history ORDER BY id DESC";
	$results = $wpdb->get_results($sql);
	if($results)
	foreach($results as $row)
		{
			$userdata = get_userdata($row->user_id);
			printf('<p><input type="checkbox" name="delete_adjustment[]" value="%d"> %s %s %s %s</p>',$row->ID,$userdata->first_name, $userdata->last_name, $row->role, $row->quantity);
		}
	echo '<p><button>'.__('Delete Checked','rsvptoast').'</button></p>';
	}
else
{

if($_GET["edit"])
{
printf('<form action="%s" method="post">',admin_url('admin.php?page=toastmasters_reports') );
echo "<p><em>".__("Use this form to give credit for activities that were not tracked through the agenda.",'rsvptoast')."</em></p>";
}
else
{
if($_GET["start_month"])
	{
		$year = (int) $_GET["start_year"];
		$month = (int) $_GET["start_month"];
		$start = sprintf('&amp;start_year=%d&amp;start_month=%d',$year,$month);
		$startmsg = '';
	}
else
	{
		$month = 7;
		$year = (date('n') > 6) ? date('Y') : date('Y') - 1;
		$start = '';
		$startmsg = ' <b>(not set)</b>';
	}
if(is_admin())
{
?>
<form action="admin.php" method="get">
<input type="hidden" name="page" value="toastmasters_reports" />
<?php _e("Start Month",'rsvptoast'); ?>: <input name="start_month" size="6" value="<?php echo $month; ?>">
<?php _e("Start Year",'rsvptoast'); ?>: <input name="start_year"  size="6" value="<?php echo $year; ?>">
<button><?php _e("Set",'rsvptoast'); ?></button> <?php echo $startmsg; ?>
</form>
<?php
}// end is admin

}

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
	$members[$index] = $userdata;
	$achievements[$index] = awesome_get_stats($userdata);
	}

// TODO add routine to edit list of roles for table
//echo implode("<br />",$toast_all_roles);

echo '<table   class="wp-list-table widefat fixed posts" >';
$l = '<tr><th>'.__('Name','rsvptoast').'</th><th  class="role">'.__('CC Speeches','rsvptoast').'</th>';
foreach ($toast_roles as $role)
	$l .= '<th class="role">'.$role."</th>";
$l .= "</tr>";
echo $l;
$count =0;

ksort($members);

$ed = '';
foreach ($members as $index => $userdata)
	{
		$count++;
		if(($count % 10) == 0)
			echo $l;


		printf('<tr><td>%s %s<br /><a href="%s%s">View</a>%s</td>',$userdata->first_name, $userdata->last_name,admin_url('admin.php?page=toastmasters_reports&toastmaster=').$userdata->ID,$start,$ed);
		$myroles = $achievements[$index];
		echo "<td>".$myroles["CC Speeches"];
		if($_GET["edit"] && current_user_can('edit_others_rsvpmakers'))
			printf('<br />'.__('Add','rsvptoast').':<br /><input type="text" name="adj[%d][%s]" value="0" size="2" />',$userdata->ID,"CC Speeches");
		echo "</td>";
		foreach($toast_roles as $role)
			{
				echo "<td>".$myroles[$role];
				if($_GET["edit"] && current_user_can('edit_others_rsvpmakers'))
					printf('<br />'.__('Add','rsvptoast').':<br /><input type="text" name="adj[%d][%s]" value="0" size="2" />',$userdata->ID,$role);				
				echo "</td>";
			}
		echo "</tr>";
	}
echo "</table>";


if($_GET["edit"])
{
echo '<p><button>'.__('Save','rsvptoast').'</button></p>
</form>
';
}

} // end not adjust

?>

<!-- iframe width="100%" height="600" src="http://www.clubawesome.org/tr.php" / -->
</div>	
<?php
}

function awesome_get_stats ($userdata) {
global $wpdb;
global $toast_all_roles;
$user_id = $userdata->ID;

//$sql = "SELECT meta_key, count(*) as ct FROM `wpcc_postmeta` where meta_value=".$user_id." AND BINARY meta_key RLIKE '^_[A-Z].+[0-9]$' GROUP BY meta_key";

//  JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->posts.ID = postID 

if($_GET["start_month"])
	{
		$start = " AND datetime > '";
		$start .= (int) $_GET["start_year"];
		$start .= '-';
		$start .= (int) $_GET["start_month"];		
		$start .= '-1';
		$start .= "'";
	}
else
	$start = '';

$sql = "SELECT meta_key, count(*) as ct FROM `$wpdb->postmeta`  JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->postmeta.post_id = postID where meta_value=".$user_id." AND BINARY meta_key RLIKE '^_[A-Z].+[0-9]$' $start  AND datetime < NOW()  GROUP BY meta_key";

$results = $wpdb->get_results($sql);
foreach ($results as $row) 
	{
	$key = trim(preg_replace('/[^A-Za-z]/',' ',$row->meta_key));
	$stats[$key] += $row->ct;
	if(!isset($toast_all_roles) || !in_array($key, $toast_all_roles) )
		$toast_all_roles[] = $key;
	}

global $toast_roles;

$wpdb->show_errors();
$sql = "SELECT SUM(quantity) as total,role FROM `".$wpdb->prefix."toastmasters_history` where user_id=".$userdata->ID." $start group by role";
$results = $wpdb->get_results($sql);

foreach ($results as $row)
	{
		$stats[$row->role] += $row->total;
	}

$stats["CC Speeches"] = (int) get_cc_speech_count($userdata->ID);

return $stats;
}

function get_latest_speeches ($user_id, $myroles = array()) {
global $wpdb;
$wpdb->show_errors();
$sql = "SELECT meta_key, post_id, date_format(datetime,'%M %e, %Y') as date FROM `$wpdb->postmeta` JOIN ".$wpdb->prefix."rsvp_dates ON post_id = postID where meta_key LIKE '%Speaker%' AND meta_value=$user_id AND datetime < NOW() ORDER BY datetime DESC";

//  JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->posts.ID = postID 

$speeches = $wpdb->get_results($sql);

if(sizeof($speeches))
{
foreach($speeches as $s)
	{
		$buff .= sprintf("<h3>%s</h3>",$s->date);
		$project = get_post_meta($s->post_id,'_manual'.$s->meta_key,true);
		if(strpos($project,' Manual') || empty($project) )
			$project = "Project not recorded";
		$parts = explode(":",$project);
		$manual = array_shift($parts);
		$progress[$manual]++;
		$buff .= sprintf('<p>%s</p>',$project);
	}
foreach($progress as $index => $score)
	{
		if(($index == 'COMPETENT COMMUNICATION (CC) MANUAL') && isset($myroles["CC Speeches"]) )
			$score += (int) $myroles["CC Speeches"];
		$scorelist .= sprintf('<div>%s : %d</div>',$index,$score);
	}
$buff = "<h2>Speech Summary</h2>".$scorelist . "<h2>Speech List</h2>".$buff;

if($_GET["start_year"])
	$buff = "<p><em>Speech list not filtered by date</em></p>".$buff;

}

return $buff;
}

function toastmasters_reconcile () {
global $wpdb;
global $post;
	echo '<div class="wrap">';

if($_POST)
	{
	update_post_meta($_POST["post_id"],'_reconciled',date('F j, Y') );
printf('<div id="message" class="updated">
		<p><strong>%s.</strong></p>
	</div>',__('Reconciliation report updated','rsvptoast'));
	echo '<div style="margin-bottom: 20px;"><h2>'.__('Would You Like To Record Attendance?','rsvptoast').'</h2>';
	toastmasters_attendance();
	echo "</div>";
	}
$sql = "SELECT *,date_format(datetime,'%M %e, %Y') as date  FROM $wpdb->posts JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->posts.ID = postID 
WHERE datetime < NOW() AND post_content LIKE '%[toast%' ORDER BY datetime DESC";

$results = $wpdb->get_results($sql);

foreach($results as $row)
	{
		$rdate = get_post_meta($row->ID,'_reconciled', true);
		if($rdate)
			$r = " (reconciled $rdate)";
		else
			$r = "";
		$options .= sprintf('<option value="%d">%s %s</option>',$row->ID,$row->date, $r);
	}

?>
<h1><?php echo __('Reconcile Meeting Roles','rsvptoast').'</h1><p><em>'.__('Use this form to reconcile and add to your record of roles filled at past meetings.','rsvptoast'); ?></em></p>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
<input type="hidden" name="page" value="toastmasters_reconcile" />
<select name="pick">
<?php echo $options; ?>
</select>
<button><?php _e('Get Event','rsvptoast'); ?></button>
</form>
<?php

$sql = "SELECT *,date_format(datetime,'%M %e, %Y') as date FROM $wpdb->posts JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->posts.ID = postID ";
if($_GET["pick"])
	$sql .= " WHERE $wpdb->posts.ID =". (int) $_GET["pick"];
else
	$sql .= " WHERE datetime < NOW() AND post_content LIKE '%[toast%' ORDER BY datetime DESC";

$r_post = $wpdb->get_row($sql);

	printf("<h2>%s</h2>",$r_post->date);
	printf('<form action="%s" method="post">',admin_url('admin.php?page=toastmasters_reconcile') );
	
$post = get_post($r_post->ID);

$content = $r_post->post_content;

$content .= '

[toastmaster role="Table Topics" count="10"]

[toastmaster role="Best Table Topics" count="1"]

[toastmaster role="Best Speech" count="1"]

[toastmaster role="Best Evaluation" count="1"]

';

echo do_shortcode($content);

submit_button('Save Changes','primary','edit_roles');
printf('<input type="hidden" name="post_id" id="post_id" value="%d"></form>',$post->ID);

	echo '</div>';
}


function toastmasters_attendance () {
global $wpdb;

if($_POST["attended"])
	{
		foreach($_POST["attended"] as $meta_key)
			{
				$parts = explode("_",$meta_key);
				$meta_value = array_pop($parts);
				$event = (int) $_POST["event"];
				update_post_meta($event, $meta_key, $meta_value);

			}
printf('<div id="message" class="updated">
		<p><strong>%s.</strong></p>
	</div>',__('Attendance updated','rsvptoast'));

	}

/*
if($_POST["table_topics"])
	{
		foreach($_POST["table_topics"] as $meta_key)
			{
				$parts = explode("_",$meta_key);
				$meta_value = array_pop($parts);
				$event = (int) $_POST["event"];
				update_post_meta($event, $meta_key, $meta_value);
				printf('<p>%s : %s %s</p>',$event, $meta_key,$meta_value);
			}
printf('<div id="message" class="updated">
		<p><strong>%s updated.</strong></p>
	</div>','Table topics');
	}
*/

$sql = "SELECT *,date_format(datetime,'%M %e, %Y') as date  FROM $wpdb->posts JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->posts.ID = postID WHERE datetime < NOW() AND post_content LIKE '%[toast%' ORDER BY datetime DESC";

$results = $wpdb->get_results($sql);

foreach($results as $row)
	{
		$options .= sprintf('<option value="%d">%s</option>',$row->ID,$row->date);
	}

?>
<div class="wrap"><h2><?php _e('Record Attendance','rsvptoast'); ?></h2>
<form method="get" action="<?php echo admin_url('admin.php'); ?>">
<input type="hidden" name="page" value="toastmasters_attendance" />
<select name="pick">
<?php echo $options; ?>
</select>
<button>Get Event</button>
</form>

<?php

$sql = "SELECT *,date_format(datetime,'%M %e, %Y') as date,$wpdb->posts.ID as event_id FROM $wpdb->posts JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->posts.ID = postID ";
if($_GET["pick"])
	$sql .= " WHERE $wpdb->posts.ID =". (int) $_GET["pick"];
else
	$sql .= " WHERE datetime < NOW() AND post_content LIKE '%[toast%' ORDER BY datetime DESC";

$r_post = $wpdb->get_row($sql);

$sql = "SELECT meta_key, meta_value FROM `$wpdb->postmeta` where post_id=".$r_post->event_id." AND BINARY meta_key RLIKE '^_[A-Z].+[0-9]$' $start GROUP BY meta_key";

$present = array();
$meeting_roles = array();
$results = $wpdb->get_results($sql);
foreach ($results as $row) 
	{
		$present[] = $row->meta_value; // all the people who filled any role
		$meeting_roles[] = $row->meta_key;
	}

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
	$members[$index] = $userdata;
	}

// TODO add routine to edit list of roles for table

printf("<h2>%s</h2>",$r_post->date);
printf('<form action="%s" method="post"><input type="hidden" name="event" value="%d">',admin_url('admin.php?page=toastmasters_attendance'), $r_post->event_id);

echo '<table   class="wp-list-table" >'; //  widefat fixed posts
$l = '<tr><th>Name</th><th>Attended</th></tr>';
echo $l;
$count =0;

ksort($members);
foreach ($members as $index => $userdata)
	{
		$count++;
		if(($count % 10) == 0)
			echo $l;
		if(in_array($userdata->ID,$present)) // || in_array('_Attended_'.$userdata->ID,$meeting_roles))
			$att = ' <strong>'.__('YES','rsvptoast').'</strong> ';
		else
			$att = sprintf('<input type="checkbox" name="attended[]" value="_Attended_%d" />',$userdata->ID);
/*
		if(in_array('_Table_Topics_'.$userdata->ID,$meeting_roles))
			$tt = ' <strong>YES</strong> ';
		else
			$tt =  sprintf('<input type="checkbox" name="table_topics[]" value="_Table_Topics_%d" />',$userdata->ID);
*/
		//printf('<tr><td>%s %s</td><td>%s</td><td>%s</td></tr>',$userdata->first_name, $userdata->last_name,$att,$tt);
		printf('<tr><td>%s %s</td><td>%s</td></tr>',$userdata->first_name, $userdata->last_name,$att);
	}
echo "</table>";

submit_button();
?>
</form>
</div>
<?php
} // end attendance


function toastmasters_attendance_report() {
echo '<div class="wrap"><h2>'.__('Attendance Report','rsvptoast').'</h2>';

if(is_admin())
{
fullscreen_link();

if($_GET["start_month"])
	{
		$year = (int) $_GET["start_year"];
		$month = (int) $_GET["start_month"];
		$start = sprintf('&amp;start_year=%d&amp;start_month=%d',$year,$month);
		$startmsg = '';
	}
else
	{
		$month = 7;
		$year = (date('n') > 6) ? date('Y') : date('Y') - 1;
		$start = '';
		$startmsg = ' <b>(not set)</b>';
	}
?>
<form action="admin.php" method="get">
<input type="hidden" name="page" value="toastmasters_attendance_report" />
<?php _e('Start Month','rsvptoast'); ?>: <input name="start_month" size="6" value="<?php echo $month; ?>">
<?php _e('Start Year','rsvptoast'); ?>: <input name="start_year"  size="6" value="<?php echo $year; ?>">
<button><?php _e('Set','rsvptoast'); ?></button> <?php echo $startmsg; ?>
</form>
<?php
}

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {			
	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}
	$members[$user->ID] = $userdata;
	$attendance[$user->ID] = awesome_get_attendance($user->ID);
	}

arsort($attendance);

//print_r($attendance);

echo "<table>";

foreach($attendance as $user_id => $count)
	{
		if(empty ($user_id) || ($user_id == 0))
			continue;
		if(!isset($d))
			$d = $count;
		if($count > 0)
			{
			$bar = 500 * ($count / $d);
			$bar = round($bar);
			}
		else
			$bar = 0;
		if($bar > 20)	
			$barhtml = '<div style="background-color: red; padding: 3px; width: '.$bar.'px"><span style="background-color: #fff;font-weight: bold;">'.$count.'</span></div>';
		else
			$barhtml = '<div>'.$count.'</div>';
		
		$userdata = get_userdata($user_id);

		echo '<tr><td>';
		echo $userdata->first_name;
		echo ' ';
		echo $userdata->last_name;
		echo '</td><td>';
		echo $barhtml;
		echo '</td></tr>';
	}
echo "</table>";
echo '</div>';
} // attendance report

function awesome_get_attendance($user_id) {
global $wpdb;

if($_GET["start_year"])
	{
		$year = (int) $_GET["start_year"];
		$month = (int) $_GET["start_month"];
		$start_date = " AND datetime > '".$year.'-'.$month."-01' ";
	}

$sql = "SELECT meta_value, meta_key FROM `$wpdb->postmeta` join ".$wpdb->prefix."rsvp_dates ON  ".$wpdb->prefix."postmeta.post_id = ".$wpdb->prefix."rsvp_dates.postID  where BINARY meta_key RLIKE '^_[A-Z].+[0-9]$' $start_date AND datetime < NOW() AND meta_value = $user_id group by post_id";
$results = $wpdb->get_results($sql);
return sizeof($results);
}

function toastmasters_cc() {

$ccs = array();

echo '<div class="wrap">
<h2>'.__('Competent Communicator Progress Report','rsvptoast').'</h2>';

global $wpdb;
$wpdb->show_errors();
$sql = "SELECT meta_key, post_id from $wpdb->postmeta WHERE meta_key LIKE '_manual%' AND meta_value LIKE '%COMPETENT%' ";
$results = $wpdb->get_results($sql);
//print_r($results);
foreach($results as $row)
	{
		$sql = sprintf("SELECT meta_value FROM $wpdb->postmeta WHERE post_id=%d AND meta_key='%s'",$row->post_id,str_replace('_manual','',$row->meta_key));
		$member = $wpdb->get_var($sql);
		if($member)
			{
			$ccs[$member]++;
			}
	}

$sql = "SELECT * FROM ".$wpdb->prefix."toastmasters_history WHERE role = 'CC Speeches'";
$results = $wpdb->get_results($sql);
foreach($results as $row)
	{
		$ccs[$row->user_id] += $row->quantity;
	}

	arsort($ccs);

if(is_admin())
{
fullscreen_link();
}

if(!$_GET["all"])
{
$datefilter = strtotime('3 months ago');
printf('<p><em>'.__('Filtered by default to show members active within the last 3 months (since %s) <a href="%s">(show all)','rsvptoast').'</a></em></p>',date('m/d/Y',$datefilter),admin_url('admin.php?page=toastmasters_cc&all=1'));
}

echo "<table>";

	foreach($ccs as $member => $count)
	{	
	$userdata = get_userdata($member);
	if(!$userdata)
		continue;

	$ts = get_latest_visit ($userdata->ID);
	if($ts)
		$d = sprintf('<br />'.__('Last attended','rsvptoast').': %s',date("m/d/Y",$ts));
	else
		$d = '';

	if(!$_GET["all"])
		{
			if( !$ts || ($datefilter > $ts) )
				continue;
		}

	if(!empty($userdata->education_awards)  )
		{
		$advanced .= sprintf("<p><strong>%s %s, %s</strong>: %d</p>",$userdata->first_name, $userdata->last_name, $userdata->education_awards, $count);
		continue;
		}
	$bar = 500 * ($count / 10);
			$barhtml = '<div style="background-color: red; padding: 5px; font-size: large; width: '.$bar.'px"><span style="background-color: #fff;font-weight: bold; margin: 5px;">'.$count.'</span></div>';


		echo '<tr><td><strong>';
		echo $userdata->first_name;
		echo ' ';
		echo $userdata->last_name;
		echo '</strong>'.$d.'</td><td>';
		echo $barhtml;
		echo '</td></tr>';
	}
echo "<table>";

if(isset($advanced))
echo "<h3>".__("Advanced Members</h3><p>Potential additional CC?",'rsvptoast')."</p>".$advanced;

echo "</div>";

}

function is_requirement_met($choices, $goal, $echo = true) {
global $myroles;
$score = 0;

foreach($choices as $choice)
	{
		if(($score < $goal) && isset($myroles[$choice]) && ($myroles[$choice] > 0) )
			{
			$myroles[$choice]--;
			$score++;
			if($echo)
			echo '<div><span style="color: green; font-weight: bold">(x)</span> '.$choice."</div>\n";
			}
		elseif($echo)
			echo '<div>'.$choice."</div>\n";		
	}
	if($score >= $goal)
		{
		if($echo)
			echo '<div><span style="color: green; font-weight: bold">'.__('Goal Met!','rsvptoast').'</span>'."</div>\n";
		return true;
		}
	else
		return false;
}

function cl_report () {
global $project_gaps;

$cl_leaders = array();
$nocl = array();
$text2 = array();

echo '
<style>
td {vertical-align: text-top;}
td.project, th.project, td.name, th.name {
width: 150px;
}
</style>

<div class="wrap">
<h2>Competent Leader Progress Report</h2>';

if(is_admin())
{
fullscreen_link();
}

if(!$_GET["all"])
{
$datefilter = strtotime('3 months ago');
printf(__('<p><em>Filtered by default to show members active within the last 3 months (since %s) <a href="%s">(show all)</a></em></p>','rsvptoast'),date('m/d/Y',$datefilter),admin_url('admin.php?page=cl_report&all=1'));
}

$text = '';

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {		
	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}

	$userdata = get_userdata($user->ID);
	if(preg_match('/[LD]/',$userdata->education_awards))
		continue;

	$ts = get_latest_visit ($userdata->ID);
	if($ts)
		$d = sprintf('<br />'.__('Last attended','rsvptoast').': %s',date("m/d/Y",$ts));
	else
		$d = '';

	if(!$_GET["all"])
		{
			if( !$ts || ($datefilter > $ts) )
				continue;
		}

	ob_start();
	$completed = cl_progress($userdata);
	if($completed)
		{
		$text[$user->ID] = ob_get_clean();	
		$cl_leaders[$completed][] = $userdata;
		}
	else
		{
		$text2[$userdata->last_name.$userdata->first_name] = ob_get_clean();
		$nocl[$userdata->last_name.$userdata->first_name] = '<a href="#'.$userdata->ID.'">'.$userdata->first_name. " ". $userdata->last_name."</a>";
		}
	}

krsort($cl_leaders);
ksort($nocl);
ksort($text2);


foreach($cl_leaders as $count => $users)
	{

	foreach($users as $user)
		{
		$output .= $text[$user->ID];

		$table2 .= '<tr><td>'.$count.'</td>'.$project_gaps[$user->ID].'</tr>';
		}
	}

echo "<table><tr><th>#</th><th class=\"name\">Name</th><th class=\"project\">".__('Project','rsvptoast')." 1</th>
<th class=\"project\">".__('Project','rsvptoast')." 2</th>
<th class=\"project\">".__('Project','rsvptoast')." 3</th>
<th class=\"project\">".__('Project','rsvptoast')." 4</th>
<th class=\"project\">".__('Project','rsvptoast')." 5</th>
<th class=\"project\">".__('Project','rsvptoast')." 6</th>
<th class=\"project\">".__('Project','rsvptoast')." 7</th>
<th class=\"project\">".__('Project','rsvptoast')." 8</th>
<th class=\"project\">".__('Project','rsvptoast')." 9</th>
<th class=\"project\">".__('Project','rsvptoast')." 10</th>
</tr>
$table2
</table>";

echo "<h3>None</h3>";
echo "<div>".implode("<br />",$nocl)."</div>";

echo $output . implode($text2);


echo "</div>";

}

function cl_progress ($userdata) {

global $myroles;
global $project_gaps;

printf('<h2 id="%d">%s %s</h2>',$userdata->ID, $userdata->first_name,$userdata->last_name);

$project_gaps[$userdata->ID] .= sprintf('<td class="name"><a href="#%s">%s %s</a></td>',$userdata->ID, $userdata->first_name,$userdata->last_name);

$myroles = awesome_get_stats($userdata);

$completed = 0;

echo '<h3>'.__('PROJECT 1: Listening','rsvptoast').'<br />
'.__('COMPLETE 3 OF 4','rsvptoast').'</h3>';

$choices = array(
'Ah Counter',
'Evaluator',
'Grammarian',
'Table Topics');
$goal = 3;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'/div>'/__('Listening: 3 OF 4','rsvptoast').'</td>';
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').sprintf('</div><a href="#%s">%s</a></td>',$userdata->ID,__('Listening: 3 OF 4','rsvptoast'));

echo "<h3>".__('PROJECT 2: Critical Thinking','rsvptoast')."<br />
 ".__("COMPLETE 2 OF 3",'rsvptoast')."</h3>";

$choices = array(
'Evaluator',
'Grammarian',
'General Evaluator');

$goal = 2;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>'.sprintf('%s</td>', __('Critical Thinking: 2 OF 3','rsvptoast') );
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Critical Thinking: 2 OF 3','rsvptoast'));

echo "<h3>".__("PROJECT 3: Giving Feedback",'rsvptoast')."<br />
".__("COMPLETE 3 OF 3",'rsvptoast')."</h3>";

$choices = array(
'Evaluator',
'Grammarian',
'General Evaluator');

$goal = 3;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>'.sprintf('%s</td>'.__('Feedback: 3 OF 3','rsvptoast') );
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Feedback: 3 OF 3','rsvptoast'));

echo "<h3>".__("PROJECT 4: Time Management",'rsvptoast')."<br />
  ".__("COMPLETE TIMER",'rsvptoast')."</h3>";
$choices = array(
'Timer');

$goal = 1;
$met = is_requirement_met($choices, $goal);

echo "<h3>+1 ".__("Other",'rsvptoast')."</h3>\n";

$choices = array(
'Toastmaster of the Day',
'Speaker',
'Topics Master',
'Grammarian',
);

$goal = 1;
$met2 = is_requirement_met($choices, $goal);

if($met && $met2)
	{
	$completed++;
	echo '<h4 style="color: green;">Project Complete</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>'.sprintf('%s</td>',__('Timer + One Other','rsvptoast'));
	}
elseif($met)
	$project_gaps[$userdata->ID] .= '<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red; color: #fff;">'.__('Goal 1','rsvptoast').'</div></div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('DONE Timer TODO + One Other','rsvptoast'));
elseif($met2)
	$project_gaps[$userdata->ID] .= '<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red; color: #fff;">'.__('Goal 2','rsvptoast').'</div></div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('TODO Timer DONE + One Other','rsvptoast') );
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Timer + One Other','rsvptoast'));

echo "<h3>".__('PROJECT 5: Planning and Implementation','rsvptoast').'<br />
  '.__('COMPLETE 3 OF 4','rsvptoast')."</h3>\n";

$choices = array(
'Toastmaster of the Day',
'Speaker',
'Topics Master',
'General Evaluator',
);

$goal = 3;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>'.sprintf('%s</td>',__('Planning & Implementation: 3 OF 4','rsvptoast'));
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Planning & Implementation: 3 OF 4','rsvptoast'));

echo "<h3>".__("PROJECT 6: Organization and Delegation",'rsvptoast')."<br />
  ".__("COMPLETE 1 OF 6",'rsvptoast')."</h3>\n";

$choices = array(
"Help Organize a Club Speech Contest",
"Help Organize a Club Special Event",
"Help Organize a Club Membership Campaign or Contest",
"Help Organize a Club PR Campaign",
"Help Produce a Club Newsletter",
"Assist the Club’s Webmaster"
);

$goal = 1;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__("Project Complete",'rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>'.sprintf('%s</td>',__('Organization & Delegation: 1 of 6','rsvptoast') );
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">TODO</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID, __('Organization & Delegation: 1 of 6','rsvptoast') );

echo "<h3>".__("PROJECT 7: Facilitation",'rsvptoast')."<br />
  ".__("COMPLETE 2 OF 4",'rsvptoast')."</h3>\n";

$choices = array(
'Toastmaster of the Day',
'General Evaluator',
"Topics Master",
"Befriend a Guest"
);

$goal = 2;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>'.sprintf('%s</td>',__('Facilitation: 2 OF 4','rsvptoast') );
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">TODO</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Facilitation: 2 of 4','rsvptoast') );

echo "<h3>".__("PROJECT 8: Motivation",'rsvptoast')."<br />
  ".__("COMPLETE 1 CHAIR","rsvptoast")."</h3>\n";

$choices = array("Membership Campaign Chair",
"Club Speech Contest Chair"
);

$goal = 1;
$met = is_requirement_met($choices, $goal);

echo "<h3> +2 ".__("OTHERS","rsvptoast")."</h3>\n";

$choices = array(
"PR Campaign Chair",
'Toastmaster of the Day',
'Evaluator',
'General Evaluator'
);

$goal = 2;
$met2 = is_requirement_met($choices, $goal);

if($met && $met2)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>'.sprintf('%s</td>',__('Motivation: Chair + 1 Other','rsvptoast') );
	}
elseif($met)
	$project_gaps[$userdata->ID] .= '<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red; color: #fff;">'.__('Goal 1','rsvptoast').'</div></div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('DONE Chair TODO + One Other','rsvptoast') );
elseif($met2)
	$project_gaps[$userdata->ID] .= '<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red; color: #fff;">Goal 2</div></div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('TODO Chair DONE + One Other','rsvptoast') );
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Motivation: Chair + 1 Other','rsvptoast') );

echo "<h3>".__("PROJECT 9: Mentoring",'rsvptoast')."<br />
  ".__("COMPLETE 1 OF 3",'rsvptoast')."</h3>\n";

$choices = array(
"Mentor for a New Member",
"Mentor for an Existing Member",
"HPL Guidance Committee Member"
);

$goal = 1;
$met = is_requirement_met($choices, $goal);

if($met)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>'.sprintf('%s</td>',__('Mentoring: 1 of 3','rsvptoast') );
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Mentoring: 1 of 3','rsvptoast') );

echo "<h3>".__("PROJECT 10: Team Building",'rsvptoast')."<br />
  ".__("COMPLETE TOASTMASTER + GENERAL EVALUATOR","rsvptoast")."</h3>";
  
$choices = array(
'Toastmaster of the Day',
'General Evaluator'
);

$goal = 2;
$met = is_requirement_met($choices, $goal);

echo "<h3>".__("OR 1 OF THE FOLLOWING",'rsvptoast')."</h3>";

$choices = array(
"Membership Campaign Chair",
"Club PR Campaign Chair",
"Club Speech Contest Chair",
"Club Special Event Chair",
"Club Newsletter Editor",
"Club Webmaster");

$goal = 1;
$met2 = is_requirement_met($choices, $goal);

if($met || $met2)
	{
	$completed++;
	echo '<h4 style="color: green;">'.__('Project Complete','rsvptoast').'</h4>';
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>'.sprintf('%s</td>',__('Team Building','rsvptoast'));
	}
else
	$project_gaps[$userdata->ID] .= '<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div>'.sprintf('<a href="#%s">%s</a></td>',$userdata->ID,__('Team Building','rsvptoast') );

return $completed;
}

function cl_project_gaps ($userdata) {

printf('<tr><td class="name">%s %s</td>',$userdata->first_name,$userdata->last_name);

$myroles = awesome_get_stats($userdata);

$completed = 0;

$choices = array(
'Ah Counter',
'Evaluator',
'Grammarian',
'Table Topics');
$goal = 3;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	echo '<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>'.__('Listening: 3 OF 4','rsvptoast').'</td>';
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID,__('Listening: 3 OF 4','rsvptoast') );

$choices = array(
'Evaluator',
'Grammarian',
'General Evaluator');

$goal = 2;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>%s</td>',__('Critical Thinking: 2 OF 3','rsvptoast') );
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">TODO</div><a href="#%s">%s</a></td>',$userdata->ID,__('Critical Thinking: 2 OF 3','rsvptoast') );

$choices = array(
'Evaluator',
'Grammarian',
'General Evaluator');

$goal = 3;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>%s</td>',__('Feedback: 3 OF 3','rsvptoast') );
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID,__('Feedback: 3 OF 3','rsvptoast') );

$choices = array(
'Timer');

$goal = 1;
$met = is_requirement_met($choices, $goal, false);

$choices = array(
'Toastmaster of the Day',
'Speaker',
'Topics Master',
'Grammarian',
);

$goal = 1;
$met2 = is_requirement_met($choices, $goal, false);

if($met && $met2)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">Done!</div>%s</td>',__('Timer + One Other','rsvptoast') );
	}
elseif($met)
	printf('<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red;  color: #fff;">%s</div></div><a href="#%s">%s</a></td>', __('Goal 1','rsvptoast'), $userdata->ID,'DONE Timer TODO + One Other');
elseif($met2)
	printf('<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red; color: #fff;">%s</div></div><a href="#%s">%s</a></td>',__('Goal 2','rsvptoast'),$userdata->ID,'TODO Timer DONE + One Other');
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID,__('Timer + One Other','rsvptoast') );

$choices = array(
'Toastmaster of the Day',
'Speaker',
'Topics Master',
'General Evaluator',
);

$goal = 3;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>%s</td>',__('Planning & Implementation: 3 OF 4','rsvptoast') );
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID, __('Planning & Implementation: 3 OF 4','rsvptoast') );

$choices = array(
"Help Organize a Club Speech Contest",
"Help Organize a Club Special Event",
"Help Organize a Club Membership Campaign or Contest",
"Help Organize a Club PR Campaign",
"Help Produce a Club Newsletter",
"Assist the Club’s Webmaster"
);

$goal = 1;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>%s</td>',__('Organization & Delegation: 1 of 6','rsvptoast') );
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'. __('TODO','rsvptoast') .'</div><a href="#%s">%s</a></td>',$userdata->ID,__('Organization & Delegation: 1 of 6','rsvptoast') );

$choices = array(
'Toastmaster of the Day',
'General Evaluator',
"Topics Master",
"Befriend a Guest"
);

$goal = 2;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'. __('Done!','rsvptoast') .'</div>%s</td>',__('Facilitation: 2 OF 4','rsvptoast') );
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">' . __('TODO','rsvptoast') . '</div><a href="#%s">%s</a></td>',$userdata->ID, __('Facilitation: 2 of 4','rsvptoast') );

$choices = array("Membership Campaign Chair",
"Club Speech Contest Chair"
);

$goal = 1;
$met = is_requirement_met($choices, $goal, false);

$choices = array(
"PR Campaign Chair",
'Toastmaster of the Day',
'Evaluator',
'General Evaluator'
);

$goal = 2;
$met2 = is_requirement_met($choices, $goal, false);

if($met && $met2)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">' .__('Done!','rsvptoast'). '</div>%s</td>', __('Motivation: Chair + 1 Other','rsvptoast') );
	}
elseif($met)
	printf('<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red;  color: #fff;">' . __('Goal 1','rsvptoast'). '</div></div><a href="#%s">%s</a></td>',$userdata->ID, __('DONE Chair TODO + One Other','rsvptoast') );
elseif($met2)
	printf('<td class="project"><div width="width: 100%; border: thin solid red; font-weight: bold;"><div style="width: 50%; background-color: red;  color: #fff;">'.__('Goal 2','rsvptoast').'</div></div><a href="#%s">%s</a></td>',$userdata->ID, __('TODO Chair DONE + One Other','rsvptoast') );
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID, __('Motivation: Chair + 1 Other','rsvptoast') );

$choices = array(
"Mentor for a New Member",
"Mentor for an Existing Member",
"HPL Guidance Committee Member"
);

$goal = 1;
$met = is_requirement_met($choices, $goal, false);

if($met)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>%s</td>',__('Mentoring: 1 of 3','rsvptoast') );
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID,__('Mentoring: 1 of 3','rsvptoast') );

$choices = array(
'Toastmaster of the Day',
'General Evaluator'
);

$goal = 2;
$met = is_requirement_met($choices, $goal, false);

$choices = array(
"Membership Campaign Chair",
"Club PR Campaign Chair",
"Club Speech Contest Chair",
"Club Special Event Chair",
"Club Newsletter Editor",
"Club Webmaster");

$goal = 1;
$met2 = is_requirement_met($choices, $goal, false);

if($met || $met2)
	{
	$completed++;
	printf('<td class="project"><div style="width: 100%; background-color: red; color: #fff; font-weight: bold;">'.__('Done!','rsvptoast').'</div>%s</td>',__('Team Building','rsvptoast'));
	}
else
	printf('<td class="project"><div style="width: 100%; border: thin solid red; font-weight: bold;">'.__('TODO','rsvptoast').'</div><a href="#%s">%s</a></td>',$userdata->ID,__('Team Building','rsvptoast') );

return $completed;
}



function get_cc_speech_count ($user_id) {
global $wpdb;
$wpdb->show_errors();

$count = 0;

$sql = "SELECT meta_key, post_id FROM `$wpdb->postmeta` where meta_value=".$user_id." AND meta_key LIKE '_Speaker%' ";

$results = $wpdb->get_results($sql);
foreach ($results as $row) 
	{
	$key = '_manual'.$row->meta_key;
	$sql = "SELECT meta_value FROM `$wpdb->postmeta` where post_id=".$row->post_id." AND meta_key LIKE '$key' AND meta_value LIKE '%COMPETENT%' ";
	//echo "$sql <br />";
	if( $wpdb->get_var($sql) )
		$count++; 
	}

$sql = "SELECT SUM(quantity) as total FROM `".$wpdb->prefix."toastmasters_history` where user_id=".$user_id." AND role='CC Speeches'";
$count += $wpdb->get_var($sql);

return $count;
}

function get_latest_visit ($user_id) {
global $wpdb;
$wpdb->show_errors();

$sql = "SELECT datetime FROM `$wpdb->postmeta` JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->postmeta.post_id = postID where meta_value=".$user_id." AND BINARY meta_key RLIKE '^_[A-Z].+[0-9]$' AND datetime < NOW() ORDER BY datetime DESC";
$date = $wpdb->get_var($sql);
if(!$date)
	return;
return strtotime($date);
}

function last_filled_role ($user_id, $role) {
global $wpdb, $rsvp_options;
$wpdb->show_errors();

$role = preg_replace('/[0-9]/','',$role);

$sql = "SELECT DATE_FORMAT(datetime,'%M %d, %Y') FROM `$wpdb->postmeta` JOIN ".$wpdb->prefix."rsvp_dates ON $wpdb->postmeta.post_id = postID where meta_value=".$user_id." AND meta_key LIKE '".$role."%' ORDER BY datetime DESC";
$date = $wpdb->get_var($sql);
if($date)
	return $date;
else
	return 'N/A';
}

function toastmasters_mentors() {
echo '<div class="wrap"><h2>Mentors</h2>';

if($_POST["mentor"])
	{
		foreach($_POST["mentor"] as $user_id => $mentor)
			{
				if(!empty($mentor) )
					{
						update_user_meta($user_id, 'mentor', $mentor);
					}
			}

echo '<div id="message" class="updated">
		<p><strong>'.__('Mentor list updated','rsvptoast').'</strong></p>
	</div>';

	}

if($_GET["edit"])
	printf('<h2><a class="add-new-h2" href="%s">%s</a></h2>',admin_url('admin.php?page=toastmasters_mentors'),__('Return to Report','rsvptoast') );
else
	printf('<h2><a class="add-new-h2" href="%s">%s</a></h2>',admin_url('admin.php?page=toastmasters_mentors&edit=1'), __('Edit','rsvptoast') );

if(!$_GET["all"])
{
$datefilter = strtotime('3 months ago');
printf('<p><em>'.__('Filtered by default to show members active within the last 3 months','rsvptoast').' ('.__('since','rsvptoast').' %s) <strong>'.__('AND','rsvptoast').'</strong> '.__('who have not yet attained an educational award such as CC or CL','rsvptoast').' <a href="%s">('.__('show all','rsvptoast').')</a></em></p>',date('m/d/Y',$datefilter),admin_url('admin.php?page=toastmasters_mentors&all=1'));
}

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {	
	if(function_exists('exclude_network_owner'))
		{
			$exclude = exclude_network_owner($user->ID, $site_id);
			if($exclude)
				continue;
		}
	$userdata = get_userdata($user->ID);
	$ts = get_latest_visit ($user->ID);
	if(!$_GET["all"])
		{
			if( !$ts || ($datefilter > $ts) )
				continue;
			elseif(!empty($userdata->education_awards))
				continue;
		}

	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);
	$members[$index] = $userdata;
	}
ksort($members);

if($_GET["edit"] && current_user_can('edit_others_rsvpmakers') )
	printf('<form action="%s" method="post">',admin_url('admin.php?page=toastmasters_mentors&edit=1') );

foreach($members as $userdata)
	{
	if($_GET["edit"] && current_user_can('edit_others_rsvpmakers') )
		printf('<p>%s %s: <input type="text" name="mentor[%d]" value="%s" /></p>',$userdata->first_name,$userdata->last_name, $userdata->ID, $userdata->mentor);
	else
		printf('<p>%s %s: %s</p>',$userdata->first_name,$userdata->last_name, $userdata->mentor);
	}

if($_GET["edit"] && current_user_can('edit_others_rsvpmakers') )
	echo '<button>'.__('Save','rsvptoast').'</button></form>';

echo '</div>';
}

function toastmasters_edit_stats() {
global $wpdb;
global $toast_roles;
global $competent_leader;
$tasks = '<option value="">'.__('Choose a Project','rsvptoast').'</option>';
foreach($competent_leader as $task)
	{
		$tasks .= sprintf('<option value="%s">%s</option>',$task,$task);
	}
?>
<style>
td,th {
border: thin solid #000;
text-align:center;	
	}
th.role {
	min-width: 90px;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

}
</style>
<?php
echo '<div class="wrap"><h2>'.__('Edit Member Stats','rsvptoast').'</h2>';
if($_POST["newstat"])
	{
		$wpdb->show_errors();
		foreach($_POST["newstat"] as $user_id => $newroles)
			{
					foreach($newroles as $role => $count)
					{
						$oldcount = (int) $_POST["oldstat"][$user_id][$role];
						if(!is_numeric($count))
						{
						update_user_meta($user_id, $role, $count);
						}
						elseif($count != $oldcount)
						{
							$adjustment = $count - $oldcount;
							$sql = sprintf( "INSERT into %s SET user_id=%d, role='%s', quantity=%d, datetime=CURDATE() ",$wpdb->prefix.'toastmasters_history',$user_id, $role, $adjustment );
					$wpdb->show_errors();
							$wpdb->query($sql);
						}
					}

					
			}

if($_POST["editcl"])
foreach($_POST["editcl"] as $user_id => $cl_updates)
	{
		foreach($cl_updates as $role)
			{
				if(!empty($role) )
					{
					$sql = sprintf( "INSERT into %s SET user_id=%d, role='%s', quantity=1, datetime=CURDATE() ",$wpdb->prefix.'toastmasters_history',$user_id, $role);
					$wpdb->show_errors();
					$wpdb->query($sql);
					}
			}
	}

printf('<div id="message" class="updated">
		<p><strong>%s.</strong></p>
	</div>',__('Member stats updated','rsvptoast') );

	}

printf('<form action="%s" method="post">',admin_url('admin.php?page=toastmasters_edit_stats') );

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
	$members[$index] = $userdata;
	$achievements[$index] = awesome_get_stats($userdata);
	}

$l = '<table class="wp-list-table widefat fixed posts" ><tr><th  class="role">'.__('CC Speeches','rsvptoast').'</th>';
foreach ($toast_roles as $role)
	$l .= '<th class="role">'.$role."</th>";
$l .= "</tr>";

ksort($members);

$ed = '';
foreach ($members as $index => $userdata)
	{
		$myroles = $achievements[$index];
		printf('<h3 id="%d">%s %s - <a href="%s">'.__('View','rsvptoast').'</a></h3>',$userdata->ID, $userdata->first_name, $userdata->last_name,admin_url('admin.php?page=toastmasters_reports&toastmaster=').$userdata->ID);
		echo $l;
		echo "<tr><td>";
			printf('<input type="text" name="newstat[%d][%s]" value="%d" size="2" />',$userdata->ID,"CC Speeches",$myroles["CC Speeches"]);
		if($myroles["CC Speeches"])
			printf('<input type="hidden" name="oldstat[%d][%s]" value="%d" size="2" />',$userdata->ID,"CC Speeches",$myroles["CC Speeches"]);
		echo "</td>";
		foreach($toast_roles as $role)
			{
				echo "<td>";
				printf('<input type="text" name="newstat[%d][%s]" value="%d" size="2" />',$userdata->ID,$role,$myroles[$role]);
			if($myroles[$role])
				printf('<input type="hidden" name="oldstat[%d][%s]" value="%d" size="2" />',$userdata->ID,$role,$myroles[$role]);
				echo "</td>";
			}
		echo "</tr>";
		echo "</table>";
		$cl = array();
		foreach($competent_leader as $role)
			{
				if(isset($myroles[$role]) )
				{
					$cl[] = $role;
				}
			}
		if(sizeof($cl) )
			{
		echo "<p>CL Projects: ";
			echo implode(', ',$cl);
		echo "</p>";
			}

				printf('<p><b>'.__('Additonal Competent Leader Credits for','rsvptoast').' %s %s</b></p>
				<p><select name="editcl[%d][]">%s</select><select name="editcl[%d][]">%s</select></p>
				<p><select name="editcl[%d][]">%s</select><select name="editcl[%d][]">%s</select></p>
				',$userdata->first_name, $userdata->last_name,$userdata->ID,$tasks,$userdata->ID,$tasks,$userdata->ID,$tasks,$userdata->ID,$tasks);

	}

submit_button();
echo '</div>';	
}

function fullscreen_link () {
foreach($_GET as $i => $value)
	{
		if($i == 'page')
			$query = '?tm_reports=';
		else
			$query .= '&'.$i.'=';
		$query .= $value;
	}
printf('<div style="float: right;"><a href="%s">%s</a></div>',site_url($query),__('Full Screen','rsvptoast'));
}

function import_fth () {
?>
<h1><?php _e('Import Free Toast Host Data','rsvptoast'); ?></h1>
<?php
global $wpdb;
global $toast_roles;

$action = admin_url('admin.php?page=import_fth');

if($_POST["speeches"])
{
$fth_roles = array();
?>
<form action="<?php echo $action; ?>" method="post">
<?php

	echo "<h3>".__('Match Users','rsvptoast')."</h3><p>".__("Either match with a WordPress user or leave blank (Match?) if there is no match, as with a former member.",'rsvptoast')."</p>";

$blogusers = get_users('blog_id='.get_current_blog_id() );
    foreach ($blogusers as $user) {	
	$userdata = get_userdata($user->ID);
	$index = preg_replace('/[^A-Za-z]/','',$userdata->last_name.$userdata->first_name.$userdata->user_login);
	$members[$index] = $userdata;
	$users_id[$userdata->ID] = sprintf('<option value="%s">%s %s</option>',$userdata->ID,$userdata->first_name,$userdata->last_name);
	}
ksort($members);

$userlist = '<option value="">Match?</option>';
foreach($members as $userdata)
	{
		$userlist .= sprintf('<option value="%s">%s %s</option>',$userdata->ID,$userdata->first_name,$userdata->last_name);
	}

if($_POST["speeches"])
	{

		$lines = explode("\n",$_POST["speeches"]);
		foreach($lines as $index => $line)
			{
				$cells = explode("\t",$line);
				//echo "<h2>Line: $index </h2>";
				if(sizeof($cells) == 6)
					{
					$name = array_shift($cells);
					//echo "<h1>$name</h1>";
					$name = trim($name);
					$nameindex = preg_replace('/[^A-Za-z]/','',$name);
					$names[$nameindex] = $name;
					}
				//print_r($cells);
				$speech["name"][$nameindex][] = $name;
				$name = preg_replace('/, [A-Z]{2,4}/','',$name);
				$speech["date"][$nameindex][] = trim($cells[0]);
				$speech["project"][$nameindex][] = trim($cells[3]);
				$speech["title"][$nameindex][] = trim($cells[2] . ' '. $cells[4]);
			}
	}
if($_POST["stats"])
	{
		$lines = explode("\n",$_POST["stats"]);
		foreach($lines as $index => $line)
			{
				$cells = explode("\t",$line);
				//echo "<h2>Line: $index </h2>";
				//print_r($cells);
				if(sizeof($cells) == 3)
					{
					$name = array_shift($cells);
					$name = trim($name);
					$nameindex = preg_replace('/[^A-Za-z]/','',$name);
					$names[$nameindex] = $name;
					}
				$stats["date"][$nameindex][] = $cells[0];
				$stats["role"][$nameindex][] = $cells[1];
			}
	ksort($names);
	foreach($names as $nameindex => $name)
		{	
		$p = explode(' ',trim($name));
		$sql = "SELECT user_id from $wpdb->usermeta WHERE meta_key='first_name' AND meta_value LIKE '".$p[0]."%'";
		$results = $wpdb->get_results($sql);
		$matching = '';
		foreach($results as $r)
			$matching .= $users_id[$r->user_id];
		printf('<p><select name="user[%s]">%s</select> = %s</p>',$nameindex,$matching.$userlist,$name);
		}
		
	foreach($stats["date"] as $nameindex => $daterow)
		{
			//print_r($namerow);
			foreach($daterow as $i => $date)
			{
			$t = strtotime($date);
			$dates[$t] = $t;
			$name = $names[$nameindex];
			$role = $stats["role"][$nameindex][$i];
			$role = trim(preg_replace('/ #[0-9]/','',$role));
			if($role == 'Speaker')
				continue; // track speakers through project list instead
			if(!in_array($role,$fth_roles) )
				$fth_roles[] = $role;
			printf('<input type="hidden" name="role[%s][%s]" value="%s" />',$t, $nameindex, $role);
			}
		}
	
	}
	
	echo "<h3>Match Roles</h3>";
	sort($fth_roles);
	foreach($fth_roles as $role)
	{
			$options = '<option value="">Match?</option>';
			
			foreach($toast_roles as $tracked)
				{
					$s = ($role == $tracked) ? ' selected="selected" ' : '';
					if(($role == 'Toastmaster') && ($tracked == 'Toastmaster of the Day'))
						$s = ' selected="selected" ';
					if(($role == 'Topic Master') && ($tracked == 'Topics Master'))
						$s = ' selected="selected" ';
					if(($role == 'Table Topics Contestant') && ($tracked == 'Table Topics'))
						$s = ' selected="selected" ';
					
					$options .= sprintf('<option value="%s" %s> %s</option>',$tracked, $s, $tracked);
				}
	printf('<p><select name="rolelist[%s]">%s</select> = %s</p>',$role, $options, $role);
	}

	echo "<h3>Speech Projects</h3>";
	$project_options = get_toast_speech_options();
	foreach($speech["name"] as $nameindex => $namerow)
		{
			//print_r($namerow);
			foreach($namerow as $i => $name)
			{
			$date = $speech["date"][$nameindex][$i];
			$t = strtotime($date);
			$dates[$t] = $t;
			$project = $speech["project"][$nameindex][$i];
			$title = $speech["title"][$nameindex][$i];
			printf('<p>'.__('Member','rsvptoast').': %s '.__('Date','rsvptoast').': <input type="text" name="speechdate[%s][]" value="%s"> <br />'.__('Project','rsvptoast').': <select name="project[%s][%s]"><option value="%s">%s</option>%s</select>
			<br />Title: <input type="text" name="title[%s][%s]" value="%s"></p>',$name, $nameindex, $date, $t, $nameindex, $project, $project, $project_options, $t, $nameindex, $title);
			}
		}

	printf('<input type="hidden" name="dates" value="%s" />',implode(",",$dates));

submit_button(__('Import Records (step 2)','rsvptoast'),'primary'); ?>
</form>
<?php
}
elseif($_POST["dates"])
{
	
	printf('<h3>'.__('Recording data. Verify by checking','rsvptoast').' <a href="%s">'.__('Toastmaster Reports','rsvptoast').'</a>.</h3>',admin_url('admin.php?page=toastmasters_reports'));
	foreach($_POST["user"] as $nameindex => $id)
		{
			if($id)
				$users[$nameindex] = (int) $id;
		}

	$dates = explode(",",$_POST["dates"]);
	sort($dates);
	foreach($dates as $date)
		{
			$t = (int) $date;
			if($t == 0)
				continue;
			$sqldate = date('Y-m-d',$t);
			
			$p = array('post_title' => __('Historical Data','rsvptoast'),'post_type' => 'historical-toastmsters-data','post_content' => __('used to track events imported from Free Toast Host. Do not delete.','rsvptoast'),'post_status' => 'publish');
			$post_id = wp_insert_post($p);
			$sql = "INSERT INTO ".$wpdb->prefix."rsvp_dates SET datetime='$sqldate', postID=". $post_id; 
			$wpdb->query($sql);
			
			//echo "<h1>$sqldate</h1>";
			if(is_array($_POST["project"][$date]) )
				{
					$count = 1;
					foreach($_POST["project"][$date] as $nameindex => $project)
						{
						if(isset($users[$nameindex]))
							{
							$user_id = $users[$nameindex];
							$meta_key = '_Speaker_'.$count;
							update_post_meta($post_id, $meta_key, $user_id);
							//echo $meta_key.': '.$nameindex.": ".$project."<br />";
							$count++;
							if(empty($project))
								continue;
							update_post_meta($post_id, '_manual'.$meta_key, $project);
							$title = $_POST["title"][$date][$nameindex];
							if(!empty($title))
								{
								//echo "title: $title <br />";
								update_post_meta($post_id, '_title'.$meta_key, $project);
								}
							}
						}
				}
			if(is_array($_POST["role"][$date]) )
				{
					foreach($_POST["role"][$date] as $nameindex => $role)
						{
						if($_POST["rolelist"][$role])
							$role = $_POST["rolelist"][$role];
						else
							continue;
						if(isset($users[$nameindex]))
							{
							$user = $users[$nameindex];
							//echo "user id: $user <br />";					
							//echo $nameindex.": ".$role."<br />";
							update_post_meta($post_id, '_'.$role.'_1', $user_id);
							}
						}
				}
		}
}
else
{ // step 1 form
?>
<form action="<?php echo $action; ?>" method="post">
<h3><?php _e('Paste in the contents of','rsvptoast'); ?> ...</h3>
<?php _e('Member Speech Historical Report','rsvptoast'); ?>:<br />
<textarea name="speeches" cols="100" rows="10"></textarea>
<br />
<?php _e('Member Role Historical Report','rsvptoast'); ?>:<br />
<textarea name="stats" cols="100" rows="10"></textarea>
<?php submit_button(__('Import Records (step 1)','rsvptoast'),'primary'); ?>
</form>
<div style="max-width: 605px;">
<h1>Directions</h1>
<p>This tool allows you to import some of the data collected through your use of Free Toast Host so that it will be reflected in the member performance reports for progress toward CC, CL, etc.</p>
<p>When you are viewing an agenda on Free Toast Host, the reports button is displayed at the top of the screen. Click it.</p>
<p><img src="<?php echo plugins_url('/rsvpmaker-for-toastmasters/fth_agenda_role_rpt.png'); ?>" width="600" height="80" alt="FTH agenda button" /></p>
<p>Free Toast Host displays a dialog box prompting you to choose the report you want to access. We are going to use the Member Speech Report and the Member Role Report (the html version, not the xls download). Under Select Start Date, make sure you select &quot;All.&quot; </p>
<p><img src="<?php echo plugins_url('/rsvpmaker-for-toastmasters/fth-dialog.png'); ?>" width="600" height="392" alt="FTH Dialog box" /></p>
<p>First, select &quot;Member Speech Report (html)&quot; and click <strong>Run/Download</strong>.</p>
<p>If you are prompted to print the document, click <strong>Cancel</strong>. Copy and Paste the all the data (not including the headers at the top) and paste it into the appropriate dialog on this form.</p>
<p><img src="<?php echo plugins_url('/rsvpmaker-for-toastmasters/fth-speech-report.png'); ?>" width="600" height="455" alt="Speech Report" /></p>
<p>Repeat the process for &quot;Member Role Report (html)&quot;.</p>
<p><img src="<?php echo plugins_url('/rsvpmaker-for-toastmasters/fth_member_role_report.png'); ?>" width="600" height="463" alt="Role Report" /></p>
<p>Click <strong>Import Records (step 1)</strong>.</p>
<p>On the next screen, you will be given the opportunity to make some corrections, matching up the names of members with the correct records recorded in WordPress for Toastmasters and matching the names of roles with the standard role names WordPress for Toastmasters uses for reporting.</p>
<p>Some data may not match up perfectly. You may have former members on the historical report for whom there is no matching record in WordPress. You may have roles that don't match up with the standard roles. If you leave those items with no selection, they simply will not be recorded.</p>
<p>If members speech projects were not recorded in Free Toast Host, you can add that information if you have it. Otherwise, leave it blank. The member will still be recorded as having given a speech, even though you haven't specified which one.</p>
<p>Click <strong>Import Records (step 2)</strong> and the data will be recorded.</p>
</div>

<?php
}

}

?>