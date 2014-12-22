<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Edit Roles / Agenda</title>
<?php
if(!current_user_can('edit_rsvpmakers'))
	die("requires editing rights");
the_post();
global $post;

if($_POST)
{
ob_start();
foreach($_POST["toastorder"] as $index)
	{
		$index = (int) $index;
		$atts = $_POST["atts"][$index];
		$agenda_note = $_POST["agenda_note"][$index];
		$attnv = '';
		
		if(is_array($atts))
		{
			foreach($atts as $label => $value)
				$attnv .= $label.'="'.$value.'" ';
		}
		
		if(!empty($agenda_note) )
			{
			$agenda_note = str_replace("<p>&nbsp;</p>","",$agenda_note);
			$agenda_note = trim(simplify_html($agenda_note));
			//$agenda_display = $_POST["agenda_display"][$index];
//			printf('[agenda_note agenda_display="%s" comment="block of text continues until /agenda_note"]'."\n\n%s\n\n[/agenda_note]\n\n",$agenda_display,$agenda_note);
			printf('[agenda_note %s comment="block of text continues until /agenda_note"]'."\n\n%s\n\n[/agenda_note]\n\n",$attnv,$agenda_note);
			}
		elseif(is_array($atts))
		{
		echo '[toastmaster ' . $attnv. "]\n\n";
		}
	}

	$content = ob_get_clean();


	$my_post = array(
      'ID'           => $post->ID,
      'post_title' => $_POST["post_title"],
      'post_content' => $content
  );

// Update the post into the database
   wp_update_post( $my_post );
}
//print_r($_REQUEST);
//echo "</pre>";

?>
<style>
.myButton {
	-moz-box-shadow:inset 0px 1px 0px 0px #cf866c;
	-webkit-box-shadow:inset 0px 1px 0px 0px #cf866c;
	box-shadow:inset 0px 1px 0px 0px #cf866c;
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #d0451b), color-stop(1, #bc3315));
	background:-moz-linear-gradient(top, #d0451b 5%, #bc3315 100%);
	background:-webkit-linear-gradient(top, #d0451b 5%, #bc3315 100%);
	background:-o-linear-gradient(top, #d0451b 5%, #bc3315 100%);
	background:-ms-linear-gradient(top, #d0451b 5%, #bc3315 100%);
	background:linear-gradient(to bottom, #d0451b 5%, #bc3315 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#d0451b', endColorstr='#bc3315',GradientType=0);
	background-color:#d0451b;
	-moz-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;
	border:1px solid #942911;
	display:inline-block;
	cursor:pointer;
	color:#ffffff;
	font-family:arial;
	font-size:13px;
	padding:6px 24px;
	text-decoration:none;
	text-shadow:0px 1px 0px #854629;
	width: 250px;
}
.myButton:hover {
	background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #bc3315), color-stop(1, #d0451b));
	background:-moz-linear-gradient(top, #bc3315 5%, #d0451b 100%);
	background:-webkit-linear-gradient(top, #bc3315 5%, #d0451b 100%);
	background:-o-linear-gradient(top, #bc3315 5%, #d0451b 100%);
	background:-ms-linear-gradient(top, #bc3315 5%, #d0451b 100%);
	background:linear-gradient(to bottom, #bc3315 5%, #d0451b 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#bc3315', endColorstr='#d0451b',GradientType=0);
	background-color:#bc3315;
}
.myButton:active {
	position:relative;
	top:1px;
}
</style>
<?php
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'tiny_mce' );

wp_head();
?>
<?php
if($_REQUEST["edit"])
{
/* ?>
<script src="//tinymce.cachefly.net/4.1/tinymce.min.js"></script>
<script>
        tinymce.init({selector:'textarea',plugins: "code"});		
</script>
<?php
*/
}
else
{
?>
<style>
textarea {display: none;}
div.name, div.note, div.note, div.themewords, div.officers
	{
		cursor:move;
	}
</style>
<?php
}
?>

<!--link rel="stylesheet" href="css/style.css" / -->
<!--script type="text/javascript" src="js/script.js"></script-->
<style>
div.name, div.note, div.note, div.themewords, div.officers
	{
		background: #E1F3FC; 
		margin-bottom: 10px;
		margin-top:15px;
		margin-left:10px;
		height:100px;
		border-radius:5px;
		border:1px solid blue;
		padding:5px;
	}	
div.note {
height: 250px;
}
div.themewords, div.officers
	{
		height:50px;
	}	
input.submit {
margin-top: 20px;
font-size: large;
color: red;
}
div#wpfooter {
-display: none;
}
</style>
</head>
<body>
        <div class="maindiv">
<div style="background-color: #eee; padding: 5px; border: thin solid #000;">
<p><a href="<?php echo rsvpmaker_permalink_query ($post->ID); ?>">View Event</a></p>
<?php

if(!$_REQUEST["edit"])
	{
?>

<p>Add, remove, and rearrange the widgets representing role signups on your agenda, as well as blocks of text. You can drag-and-drop widgets in any order. Click the <strong>edit</strong> button next to agenda notes to edit the text of those notes.</p>
<?php
	}
?>
</div>            
			<div id="header"></div>
            
			<div class="menu">
<!--
                <button id="namebutton"><img src="images/name-img.png"/>Name</button><br>
                <button id="emailbutton"><img src="images/email.png"/>Email</button><br>
                <button id="notebutton"><img src="images/contact-img.png"/>note</button><br>
                <button id="checkboxbutton"><img src="images/check-img.png"/>CheckBox</button><br>
                <button id="radioaddbutton"><img src="images/radio-img.png"/>Radio</button>
				<button id="reset">Reset</button>
-->
<?php
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
            </div>
            
			<div class="InputsWrapper1">
                <!-- div id="yourhead">
					<div id="your">
						<h2 id="yourtitle">Your Form Title<img src="images/edit-form.png"/></h2>
						<h4 id="justclickid">Just Click on Fields on left to start building your form. It's fast, easy & fun.</h4>
					</div>
				</div -->	

<?php
global $post;
if($_POST && !isset($_POST["edit"]))
{
$sked = get_post_meta($post->ID,'_sked',true);
if(!empty($sked) )
	{
			echo rsvp_template_update_checkboxes($post->ID);
	}
}
?>

<form action="<?php if($_GET["new_template"]) echo site_url('/rsvpmaker/?shortcode_editor=1');
else { echo rsvpmaker_permalink_query($post->ID, "shortcode_editor=1"); }  ?>" method="post">
<input type="hidden" name="shortcode_editor" value="1" />
<p>Event Title: <input type="text" name="post_title" value="<?php if( isset($post->post_title) ) echo $post->post_title; else echo "Meeting"; ?>" ></p>
<?php
shortcode_eventdates();
?>
<div id="InputsWrapper">

<?php

if($_GET["contest"])
{
echo agenda_note_edit(array(),'The Sgt. at Arms opens the meeting. Leads the Pledge of Allegience. The President leads the self-introductions, then introduces the Contest Master');
echo toastmasters_short_edit(array('role' => 'Contest Master','leader' => 1,'agenda_note' => 'Welcomes audience and dignitaties'));
echo toastmasters_short_edit(array('role' => 'Chief Judge','indent' => 1,'agenda_note' => 'Provides an overview of the contest rules'));
echo toastmasters_short_edit(array('role' => 'Timers','count' => 2));
echo toastmasters_short_edit(array('role' => 'Ballot Counters','count' => 2));
echo toastmasters_short_edit(array('role' => 'Evaluation Contestant','count' => 6));
echo toastmasters_short_edit(array('role' => 'Humorous Speech Contestant','count' => 6));
echo toastmasters_short_edit(array('role' => 'International Speech Contestant','count' => 6));
echo toastmasters_short_edit(array('role' => 'Table Topics Contestant','count' => 6));
echo agenda_note_edit(array(),'Provide notes here on the balloting procedure and recognition of the winners.');
}
elseif($_GET["new_template"] || ($_GET["page"] == 'role_setup'))
{
echo agenda_note_edit(array(),'The Sgt. at Arms opens the meeting. Leads the Pledge of Allegience. The President leads the self-introductions, then introduces the Toastmaster of the Day');
echo toastmasters_short_edit(array('role' => 'Toastmaster of the Day','leader' => 1));
echo toastmasters_short_edit(array('role' => 'Timer','indent' => 1));
echo toastmasters_short_edit(array('role' => 'Ah Counter','indent' => 1));
echo toastmasters_short_edit(array('role' => 'Body Language Monitor','indent' => 1));
echo toastmasters_short_edit(array('role' => 'Vote Counter','indent' => 1));
echo toastmasters_short_edit(array('role' => 'Grammarian','indent' => 1,'agenda_note' => 'Leads the Word of the Day contest'));
echo toastmasters_short_edit(array('role' => 'Table Topics Master'));
echo toastmasters_short_edit(array('role' => 'Speaker','count' => 3));
echo toastmasters_short_edit(array('role' => 'General Evaluator','agenda_note' => 'Leads the evaluation portion of the meeting. Calls for reports from the Body Language Monitor and Grammarian. Gives an overall evaluation of the meeting.'));
echo toastmasters_short_edit(array('role' => 'Evaluator','count' => 3));
echo agenda_note_edit(array(),"The Toastmaster of the Day recognizes the winners of today's contests, then returns control of the meeting to the President.");
echo toastmasters_short_edit(array('themewords' => 1));
}
else
	{
	$tcount = preg_match_all('/{toastmaster/',$post->post_content,$matches);
	$acount = preg_match_all('/{agenda_note/',$post->post_content,$matches);
	$i = $tcount + $acount;
	//date options go here
	the_content();
	}
?>

</div>

            </div>


            </div>
        </div>

<div id="addbuttons">
                <button id="namebutton" class="myButton">Add Role</button><br />
                <button id="notebutton" class="myButton">Add Agenda Note</button><br>
</div>
<br />
<button class="myButton">Save</button>
</form>

<script>

jQuery(document).ready(function($) {
		  				
			
				var MaxInputs = 100; //Maximum input boxes allowed
                
					
                /*----------------------------------
				to keep track of fields and divs added
				-----------------------------------*/
				var nameFieldCount = <?php echo $i; $i = "' + nameFieldCount + '";?>; 
				
				var InputsWrapper = $("#InputsWrapper"); //Input box wrapper ID
				var x = InputsWrapper.length; //Initial field count
				
				/*------------------
				to get fields button ID
				------------------*/
				var namefield = $("#namebutton"); 
                var notefield = $("#notebutton"); 
<?php
if(!$_REQUEST["edit"])
{
?>            
                $(InputsWrapper).sortable();  		// to make added fields sortable			
<?php
}
?>
				/*------------------------------------------------
				To add Name field 
				-------------------------------------------------*/
				$(namefield).click(function()  		
                {
                    if (x <= MaxInputs) 		
                    {
                        nameFieldCount++; 			
                        
						
                        $(InputsWrapper).append('<?php echo str_replace(">",">'+\n'",preg_replace("/[\n\r]/","",toastmasters_short_edit(array('role' => ''))) ); ?>');
						
                        x++; 
                    }
                    return false;
                });
               
			    $("body").on("click", ".removeclass0", function() {   //to remove name field

                    $(this).parent('div').remove(); 
                    x--; 
                    return false;
                });
                
				
				/*------------------------------------------------
				To add note field 
				-------------------------------------------------*/
				$(notefield).click(function()  
                {
                    if (x <= MaxInputs)
                    {
                        nameFieldCount++; 
                      
                        $(InputsWrapper).append('<?php echo str_replace(">",">'+\n'",preg_replace("/[\n\r]/","",agenda_note_edit(array(),'')) ); ?>');
                        x++; 
                    }
                    return false;
                });
				
});

</script>
</body>
</html>
