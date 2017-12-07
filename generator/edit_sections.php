<?php
$msg = '';
$text_id = false;
include("includes/functions.php");
if (isset($HTTP_POST_VARS["form_action"])) {
    $details = array();
    switch ($HTTP_POST_VARS["form_action"]) {
    case "edit":
        $details["section_id"] = (int) $HTTP_POST_VARS["section_id"];
        $details["section_name"] = trim(stripslashes($HTTP_POST_VARS["section_name"]));
        $details["section_start"] = (int) $HTTP_POST_VARS["section_start"];
        $details["section_finish"] = (int) $HTTP_POST_VARS["section_finish"];
        if ($details["section_name"] != "") {
            if (update_section($details)) {
                $msg .= '<script language="JavaScript" type="text/javascript">finish_editing();</script>';
            } else {
                $msg .= '<p>section could not be updated</p>';
            }
        }
        break;
    case "add":
        $details["section_name"] = trim(stripslashes($HTTP_POST_VARS["section_name"]));
        if ($details["section_name"] != "") {
            $details["section_start"] = (int) $HTTP_POST_VARS["section_start"] or 1;
            $details["section_finish"] = (int) $HTTP_POST_VARS["section_finish"] or 1;
            if (add_section($details)) {
                $msg .= '<script language="JavaScript" type="text/javascript">finish_editing();</script>';
            } else {
                $msg .= '<p>section could not be added</p>';
            }
        } else {
            $msg .= '<p><span class="error">ERROR:</span> text must have a content.</p>';
        }
        break;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>Story of Art: section editor</title>
<script language="JavaScript" type="text/javascript" src="scripts.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php
$hidden_fields = '';
$section_details = array("section_id"=>"", "section_name"=>"", "section_start"=>"", "section_finish"=>"", "listorder"=>"");
$section_id = false;
if (isset($HTTP_GET_VARS["section_id"])) {
    $section_id = $HTTP_GET_VARS["section_id"];
}
if ($section_id) {
    $header = "<h3>[editing section]</h3>";
    $button_text = "update";
    if ($details = get_section_details($section_id)) {
        $section_details = $details[0];
        $hidden_fields .= sprintf('<input type="hidden" name="section_id" value="%d" /><input type="hidden" name="form_action" value="edit" />', $section_details["section_id"]);
    }
} else {
    $header = "<h3>[adding section]</h3>";
    $button_text = "add";
    $hidden_fields .= '<input type="hidden" name="form_action" value="add" />';
    
}
printf('<div style="float:right;"><a href="javascript:self.close();">[close]</a></div>%s%s<form name="edit_section" method="post" actiion="edit_text.php">%s<table summary="main table" cellpadding="3" cellspacing="0" border="0">', $header, $msg, $hidden_fields);
printf('<tr><td valign="top" align="right">name:</td><td valign="top" align="left"><input type="text" size="30" name="section_name" value="%s" /></td></tr>', $section_details["section_name"]);
printf('<tr><td valign="top" align="right">start:</td><td valign="top" align="left">%s</td></tr>', get_text_select($section_details["section_start"], 'start'));
printf('<tr><td valign="top" align="right">finish:</td><td valign="top" align="left">%s</td></tr>', get_text_select($section_details["section_finish"], 'finish'));
printf('<tr><td valign="top" align="right">&nbsp;</td><td valign="top" align="left"><a href="javascript:submit_section();">[%s]</a></td></tr></table></form>', $button_text);
?>
</body>
</html>