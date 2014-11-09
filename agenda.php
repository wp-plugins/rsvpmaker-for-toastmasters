<?php 
if($_GET["word_agenda"])
{
global $post;
header('Content-Type: application/msword');
header('Content-disposition: attachment; filename='.$post->post_name.'.doc');
}
?>
<html>
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
</style>
</head>

<body lang=EN-US style='tab-interval:.5in'>
<div class="Section1">
<?php
the_post();
global $post;
global $wpdb;
global $rsvp_options;
$custom = get_post_custom($post->ID);

$sql = "SELECT * FROM ".$wpdb->prefix."rsvp_dates WHERE postID=".$post->ID.' ORDER BY datetime';
$row = $wpdb->get_row($sql);
$date = date($rsvp_options["long_date"], strtotime($row->datetime) );

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
			/*
			if($r)
				echo "removed $name $priority <br />";
			else
				echo "error $name $priority <br />";
			*/
			}
		}
?>
<table width="700">
<tr><td width="*">
<h1><?php echo get_bloginfo('name'); ?></h1>
<h2><?php the_title(); echo ' ' . $date; ?></h2>
</td><td width="80">
<img src="<?php echo plugins_url('rsvpmaker-for-toastmasters/toastmasters-75.png'); ?>" width="75" height="65" />
</td></tr>
</table>
<?php
the_content();
?>
</div>
</body>
</html>