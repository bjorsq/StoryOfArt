<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>Story of Art: text editor</title>
<script language="JavaScript" type="text/javascript" src="scripts.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php
function get_types_select($fontsize = "1.1")
{
    global $types, $hidden_fields;
    $text_type_select = '<select name="text_type">';
    $typenames = array_keys($types);
    for ($i = 0; $i < count($types); $i++) {
        $sel = ($fontsize == $types[$i]["fontsize"])? ' selected="selected"':'';
        if ($fontsize == $types[$i]["fontsize"]) {
            $hidden_fields .= sprintf('<input type="hidden" name="current_type" value="%d" />', $types[$i]["name"]);
        }
        $text_type_select .= sprintf('<option value="%s"%s>%s</option>', $types[$i]["name"], $sel, $types[$i]["label"]);
    }
    $text_type_select .= '</select>';
    return $text_type_select;
}                  
$msg = '';
$text_id = false;
include("includes/functions.php");
$types = array(array("name"=>"heading",
                     "label"=>"Heading",
                     "fontsize"=>"1.3",
                     "behaviour_id"=>"2"),
               array("name"=>"subheading",
                     "label"=>"Sub-heading",
                     "fontsize"=>"1.2",
                     "behaviour_id"=>"1"),
               array("name"=>"normal",
                     "label"=>"Normal",
                     "fontsize"=>"1.1",
                     "behaviour_id"=>"1"));

if (isset($HTTP_POST_VARS["form_action"])) {
    switch ($HTTP_POST_VARS["form_action"]) {
    case "edit":
        $details["text_id"] = $HTTP_POST_VARS["text_id"];
        $text_id = $HTTP_POST_VARS["text_id"];
        $details["content"] = trim(stripslashes($HTTP_POST_VARS["content"]));
        if ($details["content"] != "") {
            $details["current_type"] = $HTTP_POST_VARS["current_type"];
            $details["text_type"] = $HTTP_POST_VARS["text_type"];
            for ($i = 0; $i < count($types); $i++) {
                if ($types[$i]["name"] == $HTTP_POST_VARS["text_type"]) {
                    $details["fontsize"] = $types[$i]["fontsize"];
                    $details["behaviour_id"] = $types[$i]["behaviour_id"];
                    break;
                }
            }
            if (update_text($details)) {
                $msg .= '<script language="JavaScript" type="text/javascript">finish_editing();</script>';
            } else {
                $msg .= '<p>text could not be updated</p>';
            }
        }
        break;
    case "insert":
        $details["content"] = trim(stripslashes($HTTP_POST_VARS["content"]));
        if ($details["content"] != "") {
            for ($i = 0; $i < count($types); $i++) {
                if ($types[$i]["name"] == $HTTP_POST_VARS["text_type"]) {
                    $details["fontsize"] = $types[$i]["fontsize"];
                    $details["behaviour_id"] = $types[$i]["behaviour_id"];
                    break;
                }
            }
            $details["after"] = $HTTP_POST_VARS["after"];
            if (insert_text($details)) {
                $msg .= '<script language="JavaScript" type="text/javascript">finish_editing();</script>';
            } else {
                $msg .= '<p>text could not be inserted</p>';
            }
        } else {
            $msg .= '<p><span class="error">ERROR:</span> text must have a content.</p>';
        }
        break;
    }
}
$hidden_fields = '';
$after = "end";
$text_details = array("text_id"=>"", "content"=>"", "behaviour_id"=>"1", "fontsize"=>"1.1");
if (isset($HTTP_GET_VARS["text_id"])) {
    $text_id = $HTTP_GET_VARS["text_id"];
}
if (isset($HTTP_GET_VARS["after"])) {
    $after = $HTTP_GET_VARS["after"];
}
if ($text_id) {
    $header = "";
    $button_text = "update";
    if ($details = get_text_details($text_id)) {
        $text_details = $details[0];
    }
    $hidden_fields = sprintf('<input type="hidden" name="text_id" value="%d" /><input type="hidden" name="form_action" value="edit" />', $text_details["text_id"]);
    printf('<p style="text-align:right;"><a href="javascript:self.close();">[close]</a></p><h3>[editing text]</h3>%s<form name="edit_text" method="post" actiion="edit_text.php">%s<table summary="main table" cellpadding="3" cellspacing="0" border="0">', $msg, $hidden_fields);
    printf('<tr><td valign="top" align="right">text:</td><td valign="top" align="left"><textarea name="content" cols="40" rows="8">%s</textarea></td></tr>', $text_details["content"]);
    printf('<tr><td valign="top" align="right">type:</td><td valign="top" align="left">%s</td></tr>', get_types_select($text_details["fontsize"]));
    print('<tr><td valign="top" align="right">&nbsp;</td><td valign="top" align="left"><a href="javascript:submit_text();">[update]</a></td></tr></table></form>');
} else {
    $hidden_fields .= sprintf('<input type="hidden" name="form_action" value="insert" /><input type="hidden" name="after" value="%s" />', $after);
    printf('<p style="text-align:right;"><a href="javascript:self.close();">[close]</a></p><h3>[inserting text]</h3>%s<form name="edit_text" method="post" actiion="edit_text.php">%s<table summary="main table" cellpadding="3" cellspacing="0" border="0">', $msg, $hidden_fields);
    printf('<tr><td valign="top" align="right">text:</td><td valign="top" align="left"><textarea name="content" cols="40" rows="8">%s</textarea></td></tr>', $text_details["content"]);
    printf('<tr><td valign="top" align="right">type:</td><td valign="top" align="left">%s</td></tr>', get_types_select($text_details["fontsize"]));
    print('<tr><td valign="top" align="right">&nbsp;</td><td valign="top" align="left"><a href="javascript:submit_text();">[insert]</a></td></tr></table></form>');
}

?>
</body>
</html>