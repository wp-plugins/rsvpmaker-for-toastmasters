<?php 
if($_GET["word_agenda"])
{
global $post;
header('Content-Type: application/msword');
header('Content-disposition: attachment; filename='.$post->post_name.'.doc');
}
the_post();
global $post;
global $wpdb;
global $rsvp_options;
$custom = get_post_custom($post->ID);
if(!$custom["_sked"][0])
	{
	$sql = "SELECT * FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post->ID.' ORDER BY datetime';
	$row = $wpdb->get_row($sql);
	$date = date($rsvp_options["long_date"], strtotime($row->datetime) );
	}
else
	$date = ' ('.__('Template','rsvptoast').')';

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
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=Windows-1252">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<!--[if gte mso 9]>
<xml>
<w:WordDocument>
<w:View>Print</w:View>
<w:Zoom>90</w:Zoom>
<w:DoNotOptimizeForBrowser/>
</w:WordDocument>
</xml>
<![endif]-->
<style>
<!-- /* Style Definitions */

html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, font, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend,
table, caption, tbody, tfoot, thead, tr, th, td {
	border: 0;
	font-family: inherit;
	font-size: 100%;
	font-style: inherit;
	font-weight: inherit;
	margin: 0;
	outline: 0;
	padding: 0;
	vertical-align: baseline;
}
body, p, div, td, th {
font-size: 14px;
line-height: 1.3;
font-family:"Times New Roman", Times, serif;
}
div#manual {
font-size: 10px;
}
p, td, th {
margin-top: 5px;
margin-bottom: 5px;
}
.agenda_note p, #theme p {
	margin-top: 5px;
	margin-bottom: 10px;
}
div.agenda_note, div#theme {
margin-top: 10px;
}

blockquote {
margin-left: 10px;
}
h1 {font-size: 24px;  font-weight: bold;  margin-bottom: 5px;}
h2 {font-size: 18px; font-weight: bold; margin-bottom: 5px;}
td {vertical-align: top;}
strong { font-weight: bold; }
em {font-style: italic; }

@page Section1
   {size:8.5in 11.0in; 
   margin: 0.5in; 
   mso-header-margin:.5in;
   mso-footer-margin:.5in; mso-paper-source:0;}
 div.Section1
 {
page:Section1;
width: 700px;
 }
div, p, table, blockquote {
max-width: 700px;
}
#banner {
height: 80px;
}
#td# sidebar {
padding-right: 10px;
width: 25%;
}
td#agenda {
padding: 0 10px 0 10px;
border: medium solid #666;
}
</style>
</head>

<body lang=EN-US style='tab-interval:.5in'>
<div class="Section1">
<div id="banner">
<img src="<?php echo plugins_url('rsvpmaker-for-toastmasters/agenda-rays.png'); ?>" width="700" height="79">
</div>
<h2><?php echo get_bloginfo('name'); ?><?php echo ' - ' . $date; ?></h2>
<table id="main" width="700">
<tr>
<td id="sidebar" width="175"><?php

// display sidebar specific to post, sidebar specific to template, or sidebar set in options - or default message

if(!empty($custom["_tm_sidebar"][0])){
$sidebar = $custom["_tm_sidebar"][0];
$officers = $custom["_sidebar_officers"][0];
	}
	else {
	$template_sidebar = get_post_meta($custom["_meet_recur"][0],'_tm_sidebar', true);
	$option_sidebar = get_option('_tm_sidebar');
	if(!empty($template_sidebar))
		{
		$sidebar = $template_sidebar;
		$officers = get_post_meta($custom["_meet_recur"][0],'_sidebar_officers', true);
		}
	elseif(!empty($option_sidebar))	
		{
		$sidebar = $option_sidebar;
		$officers = get_option('_sidebar_officers');
		}
	else
		$sidebar = sprintf( __('<p>Set sidebar text using the <a href="%s">Agenda Sidebar editor</a></p>','rsvptoast') ,admin_url('edit.php?post_type=rsvpmaker&page=agenda_sidebar&post_id='.$post->ID) );

	}

echo wpautop($sidebar);

$atts["sep"] = 'br';
if($officers)
{
	echo "<p>";
	echo toastmaster_officers($atts);
	echo "</p>";
}
?>
</td>
<td id="agenda" width="*">
<?php
the_content();
?>
</td>
</tr>
</table>
</div>
<?php 
if(!$_GET["word_agenda"])
{
?>
<script type="text/javascript">
<!--
window.print();
//-->
</script>
<?php
}
?>
</body>
</html>