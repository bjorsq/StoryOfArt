<?php
$msg = '';
include("includes/functions.php");
if (isset($HTTP_POST_VARS["text_id"]) && $HTTP_POST_VARS["text_id"] != "") {
    if (isset($HTTP_POST_VARS["form_action"])) {
        switch ($HTTP_POST_VARS["form_action"]) {
        case "delete":
            if (delete_text($HTTP_POST_VARS["text_id"])) {
                $msg .= '<p>text deleted successfully</p>';
            } else {
                $msg .= '<p>text could not be deleted</p>';
            }
            break;
        case "up":
        case "down":
            $action = $HTTP_POST_VARS["form_action"];
            if (isset($HTTP_POST_VARS["text_id"]) && $HTTP_POST_VARS["text_id"] != "" && isset($HTTP_POST_VARS["current_listorder"]) && $HTTP_POST_VARS["current_listorder"] != "") {
                if (!move_text($HTTP_POST_VARS["text_id"], $HTTP_POST_VARS["current_listorder"], $action)) {
                    $msg .= '<p>field could not be moved</p>';
                }
            }
            break;
        }
    }
}
$page = $pp = $section_id = false;
if (isset($HTTP_GET_VARS["p"]) && $HTTP_GET_VARS["p"] != "") {
    $page = $HTTP_GET_VARS["p"];
} else {
    $page = 1;
}
if (isset($HTTP_GET_VARS["pp"]) && $HTTP_GET_VARS["pp"] != "") {
    $pp = $HTTP_GET_VARS["pp"];
} else {
    $pp = 15;
}
if (isset($HTTP_GET_VARS["section_id"]) && $HTTP_GET_VARS["section_id"] != "") {
    $section_id = $HTTP_GET_VARS["section_id"];
}
$section_name = "all sections";
$remove_filter = "<td>&nbsp;</td>";
if ($section_id) {
    $section_details = get_section_details($section_id);
    $section_name = stripslashes($section_details[0]["section_name"]);
    $section_start = (int) stripslashes($section_details[0]["section_start"]);
    $section_finish = (int) stripslashes($section_details[0]["section_finish"]);
    $remove_filter = sprintf('<td valign="top"><a href="javascript:remove_filter(%d);" class="button">remove filter</a></td>', $pp);
}
$sections_select = "";
if ($all_sections = get_section_details()) {
    $sections_select .= sprintf('<form name="section_jump" id="section_jump" action="index.php" method="get"><input type="hidden" name="pp" value="%s" />', $pp);
    $sections_select .= get_section_select($section_id, "id");
    $sections_select .= '</form>';
}
$controls = '<table summary="controls" cellpadding="0" cellspacing="0" border="0"><tr>';
$controls .= sprintf('<td valign="top" colspan="5">Currently showing %s</td></tr><tr>', $section_name);
if ($sections_select != "") {
    $controls .= sprintf('<td valign="top">%s</td>', $sections_select);
    $controls .= '<td valign="top"><a href="javascript:edit_section()" class="button">edit</a></td>';
    $controls .= '<td valign="top"><a href="javascript:filter_section()" class="button">filter</a></td>';
} else {
    $controls .= '<td colspan="3"></td>';
}
$controls .= '<td valign="top"><a href="javascript:add_section()" class="button">add new section</a></td>';
$controls .= $remove_filter . '</tr><tr>';
$controls .= '<td valign="top"><form method="get" name="perpage">Show:&nbsp;<select name="pp" onchange="document.forms[\'perpage\'].submit();">';
for ($i = 2; $i < 21; $i++) {
    $sel = (($i * 5) == $pp)? ' selected="selected"': '';
    $controls .= sprintf('<option value="%d"%s>%d</option>', ($i * 5), $sel, ($i * 5));
}
$controls .= '</select>&nbsp;sentences&nbsp;per&nbsp;page</form></td>';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Story of Art: SVG generator</title>
<script language="JavaScript" type="text/javascript" src="scripts.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php
print_nav("home");
printf('<form method="get" name="reloadPage" id="reloadPage" action="index.php"><input type="hidden" name="p" value="%d" /><input type="hidden" name="pp" value="%d" /><input type="hidden" name="section_id" value="%d" /></form>', $page, $pp, $section_id);
if ($text_details = get_text_details()) {
    if ($section_id) {
        $section_text = array();
        $start_adding = false;
        $stop_adding = false;
        for ($i = 0; $i < count($text_details); $i++) {
            if (!$start_adding && $section_start == $text_details[$i]["text_id"]) {
                $start_adding = true;
            }
            if ($section_finish == $text_details[$i]["text_id"]) {
                $stop_adding = true;
            }
            if ($start_adding && !$stop_adding) {
                array_push($section_text, $text_details[$i]);
            }
            if ($stop_adding) {
                break;
            }
        }
        $text_details = $section_text;          
    }
    if (count($text_details) > $pp) {
        $controls .= sprintf('<td valign="top" colspan="3"><form name="navbar" method="get"><input type="hidden" name="pp" value="%s" /><input type="hidden" name="p" value="" />', $pp);
        if ($page != 1) {
            $controls .= sprintf('<input type="button" value="&lt;&lt;" onclick="go_to_page(1)" /><input type="button" value="&lt;" onclick="go_to_page(%d)" />', ($page - 1));
        }
        $no_of_pages = ceil(count($text_details)/$pp);
        if ($no_of_pages > 1) {
            $controls .= "&nbsp;Page&nbsp;" . $page . "&nbsp;";
        }
        if ($page < $no_of_pages) {
            $controls .= sprintf('<input type="button" value="&gt;" onclick="go_to_page(%d)" /><input type="button" value="&gt;&gt;" onclick="go_to_page(%d)" />', ($page + 1), $no_of_pages);
        }
        $controls .= '</form></td>';
        $controls .= sprintf('<td valign="top"><form method="get" name="gotopage"><input type="hidden" name="pp" value="%d" />', $pp);
        $controls .= ($section_id)? '<input type="hidden" name="section_id" value="' . $section_id . '" />': '';
        $controls .= 'Go&nbsp;to&nbsp;page:&nbsp;<select name="p" onchange="document.forms[\'gotopage\'].submit();">';
        $pageNo = "";
        for ($i = 0; $i < $no_of_pages; $i++) {
            $pageNo = $i + 1;
            $sel = ($pageNo == $page)? ' selected="selected"': '';
            $controls .= sprintf('<option value="%s"%s>%s</option>', $pageNo, $sel, $pageNo);
        }
        $controls .= '</select></form></td>';
    } else {
        $controls .= '<td colspan="4">&nbsp;</td>';
    }
    $controls .= '</tr></table>';
    echo $controls;
    printf('%s<table summary="main table" cellpadding="3" cellspacing="1" border="1" bordercolor="#cccccc"><tr><td>ID</td><td colspan="2">SVG functions</td><td>text<img src="images/space.gif" alt="" width="540" height="1" border="0" /></td><td colspan="5">actions</td></tr>', $msg);
    for ($i = 0; $i < count($text_details); $i++) {
        if ((($i + 1) > (($page - 1) * $pp)) && (($i + 1) < ($page * $pp))) {
            $top = $up = $down = $bottom = "&nbsp;";
            if (count($text_details) > 1) {
                if ($i > 0) {
                    $up = sprintf('<a href="javascript:move_up(%d);" class="button"><img src="images/up.gif" alt="move this field up" width="13" height="13" border="0" /></a>', $text_details[$i]["text_id"]);
                }
                if ($i < count($text_details) - 1) {
                    $down = sprintf('<a href="javascript:move_down(%d);" class="button"><img src="images/down.gif" alt="move this field down" width="13" height="13" border="0" /></a>', $text_details[$i]["text_id"]);
                }
            }
            $text = $text_details[$i]["content"];
            $to_js = ($section_id)? ',' . $section_finish: '';
            printf('<tr><td valign="top">%s<form action="index.php" name="update_text_%d" method="post"><input type="hidden" name="text_id" value="%d" /><input type="hidden" name="form_action" value="" /><input type="hidden" name="current_listorder" value="%d" /></form></td><td valign="top"><a href="javascript:viewSVG(%d%s);" class="button">view</a></td><td valign="top"><a href="javascript:saveSVG(%d%s);" class="button">save</a></td><td valign="top" width="540" style="text-align:justify"><a href="javascript:update_text(%d);">%s</a></td><td valign="top"><a href="javascript:edit_behaviour(%d);" class="button">behaviour</a></td><td valign="top"><a href="javascript:insert_text(%d);" class="button">insert</a></td><td valign="top"><a href="javascript:delete_text(%d);" class="button">delete</a></td><td valign="top">%s</td><td valign="top">%s</td></tr>', $text_details[$i]["text_id"], $text_details[$i]["text_id"], $text_details[$i]["text_id"], $text_details[$i]["listorder"], $text_details[$i]["listorder"], $to_js, $text_details[$i]["listorder"], $to_js, $text_details[$i]["text_id"], $text, $text_details[$i]["text_id"], $text_details[$i]["listorder"], $text_details[$i]["text_id"], $up, $down);
        }
    }
} else {
   echo("<p>There is no text in the database.</p><p>Please go to the <a href=\"import.php\">import page</a> to import some text.</p>");
}
?>
</body>
</html>