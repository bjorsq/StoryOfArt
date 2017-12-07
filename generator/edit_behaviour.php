<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>Story of Art: behaviour editor</title>
<script language="JavaScript" type="text/javascript" src="scripts.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div style="float:right;"><a href="javascript:self.close();">[close]</a></div>
<?php
$msg = '';
$text_id = false;
include("includes/functions.php");
if (isset($HTTP_POST_VARS["text_id"])) {
    $details["text_id"] = $HTTP_POST_VARS["text_id"];
    for ($i = 0; $i < count($HTTP_POST_VARS["attributes"]); $i++) {
        $details["attribute_id"] = $HTTP_POST_VARS["attributes"][$i];
        $details["attribute_value"] = stripslashes($HTTP_POST_VARS[$HTTP_POST_VARS["attributes"][$i]]);
        if (update_attribute_value($details)) {
            $msg .= '<script language="JavaScript" type="text/javascript">self.close();</script>';
        } else {
            $msg .= '<p>attributes could not be updated</p>';
        }
    }
}
$hidden_fields = '';
if (isset($HTTP_GET_VARS["text_id"])) {
    $text_id = $HTTP_GET_VARS["text_id"];
    $header = "";
    if ($details = get_attribute_values($text_id)) {
        $hidden_fields .= sprintf('<input type="hidden" name="text_id" value="%d" /><input type="hidden" name="form_action" value="edit" />', $text_id);
        printf('<h3>[editing behaviour]</h3>%s%s<form name="edit_behaviour" method="post" actiion="edit_behaviour.php">%s<table summary="main table" cellpadding="3" cellspacing="0" border="0">', $header, $msg, $hidden_fields);
        for ($i = 0; $i < count($details); $i++) {
            $val = ($details[$i]["value"] == "")? $details[$i]["default"]: $details[$i]["value"];
            switch($details[$i]["type"]) {
            case 'textline':
                printf('<tr><td valign="top" align="right">%s</td><td valign="top" align="left"><input type="hidden" name="attributes[]" value="%d" /><input type="text" size="30" name="%d" value="%s" /></td></tr>', $details[$i]["name"], $details[$i]["id"], $details[$i]["id"], $val);
                break;
            case 'number':
                printf('<tr><td valign="top" align="right">%s</td><td valign="top" align="left"><input type="hidden" name="attributes[]" value="%d" /><input type="text" size="3" name="%d" value="%s" /></td></tr>', $details[$i]["name"], $details[$i]["id"], $details[$i]["id"], $val);
                break;
            case 'textbox':
                printf('<tr><td valign="top" align="right">%s</td><td valign="top" align="left"><input type="hidden" name="attributes[]" value="%d" /><textarea cols="30" rows="5" name="%d">%s</textarea></td></tr>', $details[$i]["name"], $details[$i]["id"], $details[$i]["id"], $val);
                break;
            }
        }
        print('<tr><td valign="top" align="right">&nbsp;</td><td valign="top" align="left"><a href="javascript:submit_behaviour();">[update]</a></td></tr></table></form>');
    }
}
?>
</body>
</html>