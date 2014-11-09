<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Reports</title>
<?php
do_action( 'admin_print_styles' );
?>

</head>

<body>
<?php
if(!is_user_logged_in())
	die('Login required');
if($_GET["tm_reports"] == 'toastmasters_reports')
	toastmasters_reports();
elseif($_GET["tm_reports"] == 'toastmasters_attendance_report')
	toastmasters_attendance_report();
elseif($_GET["tm_reports"] == 'toastmasters_attendance')
	toastmasters_attendance();
elseif($_GET["tm_reports"] == 'toastmasters_cc')
	toastmasters_cc();
elseif($_GET["tm_reports"] == 'cl_report')
	cl_report();
elseif($_GET["tm_reports"] == 'toastmasters_mentors')
	toastmasters_mentors();
?>
</body>
</html>