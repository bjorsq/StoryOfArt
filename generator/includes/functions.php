<?php
/* functions.php */
// include library files
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/DB.php");

// instantiate a new database object
$dsn = $config["db_type"] . "://" . $config["db_user"] . ":" . $config["db_password"] . "@" . $config["db_host"] . "/" . $config["db_name"];
$db = DB::connect($dsn, true);
$db->setFetchMode(DB_FETCHMODE_ASSOC);
/////////////////////////////////////////////////////////////////////////
//                   TEXT EDITING FUNCTIONS                            //
/////////////////////////////////////////////////////////////////////////

/*
 * get_text_details
 */
function get_text_details($text_id = false)
{
    global $config, $db;
    $query = "SELECT t.*, b.behaviour_name FROM `text` t, behaviours b WHERE ";
    if ($text_id) {
        $query .= sprintf("text_id = %d AND ", $text_id);
    }
    $query .= "t.behaviour_id = b.behaviour_id ORDER BY listorder;";
    //print $query;
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
    $count = 0;
    $ret = array();
    if ($result->numRows()) {
        while ($row = $result->fetchRow()) {
            $ret[$count] = array();
            $ret[$count]["text_id"] = $row["text_id"];
            $ret[$count]["content"] = stripslashes($row["content"]);
            $ret[$count]["fontsize"] = $row["fontsize"];
            $ret[$count]["listorder"] = $row["listorder"];
            $ret[$count]["behaviour_id"] = $row["behaviour_id"];
            $count++;
        }
    }
    return $ret;
}
/*
 * add_text
 */
function insert_text($details)
{
    global $config, $db;
    $id = $db->quote($db->nextId("text"));
    $content = $db->quote($details["content"]);
    $fontsize = $db->quote((float) $details["fontsize"]);
    $behaviour_id = $db->quote((int) $details["behaviour_id"]);
    $queries = array();
    if ($details["after"] == "end") {
        $listorder = get_next_listorder('text');
    } else {
        $listorder = ((int) $details["after"]) + 1;
        array_push($queries, sprintf("UPDATE `text` SET listorder = listorder + 1 WHERE listorder > %d", (int) $details["after"])); 
    }
    array_push($queries, sprintf("INSERT INTO `text` (text_id, content, listorder, behaviour_id, fontsize) VALUES ('%d', %s, '%d', '%d', '%d')", $id, $content, $listorder, $behaviour_id, $fontsize));
    foreach ($queries as $query) {
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(),$query);
            return false;
        }
    }
    return true;
}
/*
 * delete_text
 */
function delete_text_behaviour($text_id)
{
    global $config, $db;
    $query = sprintf("DELETE FROM `values` WHERE text_id = %d;", $text_id);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
}
/*
 * update_text
 */
function update_text($details)
{
    global $config, $db;
    $text_id = $db->quote($details["text_id"]);
    if ($details["text_type"] != $details["current_type"]) {
        delete_text_behaviour($text_id);
    }
    $content = $db->quote($details["content"]);
    $fontsize = $db->quote((float) $details["fontsize"]);
    $behaviour_id = $db->quote((int) $details["behaviour_id"]);
    $query = sprintf("UPDATE `text` SET content = %s, behaviour_id = %s, fontsize = %s WHERE text_id = %s;", $content, $behaviour_id, $fontsize, $text_id);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    } else {
        return true;
    }
}
/*
 * delete_text
 */
function delete_text($text_id = false)
{
    global $config, $db;
    if (!$text_id) {
        return false;
    }
    if ($details = get_text_details($text_id)) {
        $query = sprintf("UPDATE `text` SET listorder = listorder - 1 WHERE listorder > %s;", $details[0]["listorder"]); 
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
            return false;
        }
        delete_text_behaviour($text_id);
        $query = sprintf("DELETE FROM `text` WHERE text_id = %d;", $text_id);
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
            return false;
        }
    }
    return true;
}
////////////////////////////////////////////////////////////////////////////////
//                    SECTION EDITING FUNCTIONS                               //
////////////////////////////////////////////////////////////////////////////////

function get_section_details($section_id = false)
{
    global $config, $db;
    $query = "SELECT * FROM sections ";
    if ($section_id) {
        $query .= sprintf("WHERE section_id = %d ", $section_id);
    }
    $query .= " ORDER BY listorder;";
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
    $count = 0;
    $ret = array();
    if ($result->numRows()) {
        while ($row = $result->fetchRow()) {
            $ret[$count] = array();
            $ret[$count]["section_id"] = $row["section_id"];
            $ret[$count]["section_name"] = stripslashes($row["section_name"]);
            $ret[$count]["section_start"] = $row["section_start"];
            $ret[$count]["section_finish"] = $row["section_finish"];
            $ret[$count]["section_type"] = $row["section_type"];
            $ret[$count]["listorder"] = $row["listorder"];
            $count++;
        }
    }
    return $ret;
}

function add_section($details)
{
    global $config, $db;
    $id = $db->quote($db->nextId("section"));
    $name = $db->quote(trim(stripslashes($details["section_name"])));
    $section_start = $db->quote($details["section_start"]);
    $section_finish = $db->quote($details["section_finish"]);
    $listorder = get_next_listorder('sections');
    $query = sprintf("INSERT INTO sections (section_id, section_name, section_start, section_finish, listorder) VALUES ('%d', %s, '%s', '%s', '%d')", $id, $name, $section_start, $section_finish, $listorder);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(),$query);
        return false;
    } else {
        return $id;
    }
}
function update_section($details)
{
    global $config, $db;
    $section_id = $db->quote($details["section_id"]);
    $section_name = $db->quote($details["section_name"]);
    $section_start = $db->quote($details["section_start"]);
    $section_finish = $db->quote($details["section_finish"]);
    $query = sprintf("UPDATE sections SET section_name = %s, section_start = %s, section_finish = %s WHERE section_id = %s;", $section_name, $section_start, $section_finish, $section_id);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    } else {
        return true;
    }
}
/*
 * delete_section
 */
function delete_section($section_id = false)
{
    global $config, $db;
    if (!$section_id) {
        return false;
    }
    if ($details = get_section_details($section_id)) {
        $query = sprintf("UPDATE sections SET listorder = listorder - 1 WHERE listorder > %s;", $details[0]["listorder"]); 
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
            return false;
        }
        $query = sprintf("DELETE FROM sections WHERE section_id = %d;", $section_id);
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
            return false;
        }
    }
    return true;
}
////////////////////////////////////////////////////////////////////////////////
//                           ANIMATION ATTRIBUTE FUNCTIONS                    //
////////////////////////////////////////////////////////////////////////////////

function get_attribute_values($text_id)
{
    global $config, $db;
    if (!$text_id) {
        return false;
    }
    $query = sprintf("SELECT attributes.* FROM attributes, `text` WHERE attributes.behaviour_id=text.behaviour_id AND text.text_id=%d ORDER BY attributes.listorder;", $text_id);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
    $count = 0;
    $ret = array();
    if ($result->numRows()) {
        while ($row = $result->fetchRow()) {
            $ret[$count]["id"] = $row["attribute_id"];
            $ret[$count]["name"] = $row["attribute_name"];
            $ret[$count]["type"] = $row["edit_type"];
            $ret[$count]["default"] = $row["default_value"];
            $ret[$count]["value"] = get_attribute_value($row["attribute_id"], $text_id);
            $count++;
        }
    }
    return $ret;
}
function get_attribute_value($attribute_id = false, $text_id = false)
{
    global $config, $db;
    if (!$text_id || !$attribute_id) {
        return false;
    }
    $query = sprintf("SELECT * FROM `values` WHERE attribute_id = %d AND text_id = %d;", $attribute_id, $text_id);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
    if ($result->numRows()) {
        $row = $result->fetchRow();
        return $row["attribute_value"];
    } else {
        return "";
    }
}

function update_attribute_value($details)
{
    global $config, $db;
    $text_id = $db->quote($details["text_id"]);
    $attribute_id = $db->quote($details["attribute_id"]);
    $attribute_value = $db->quote($details["attribute_value"]);
    if (_attribute_value_exists($attribute_id, $text_id)) {
        $query = sprintf("UPDATE `values` SET attribute_value = %s WHERE text_id = %s AND attribute_id = %s;", $attribute_value, $text_id, $attribute_id);
    } else {
        $query = sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES(%s, %s, %s);", $text_id, $attribute_id, $attribute_value);
    }
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    } else {
        return true;
    }
}
function _attribute_value_exists($attribute_id, $text_id)
{
    global $config, $db;
    $query = sprintf("SELECT * FROM `values` WHERE attribute_id = %s AND text_id = %s;", $attribute_id, $text_id);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
    if ($result->numRows()) {
        return true;
    } else {
        return false;
    }
}
/**
 * get_next_listorder
 */
function get_next_listorder($table = "text")
{
    global $config, $db;
    $query = sprintf("SELECT MAX(listorder) + 1 AS next FROM %s", $table);
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(),$query);
        return false;
    }
    $row = $result->fetchRow();
    if ($row["next"]) {
        return $row["next"];
    } else {
        return 1;
    }
}
/**
 * move_text
 *
 * moves text up or down in the listing
 *
 * @param int $text_id - id of the text to be moved
 * @param string $action- (up | down | bottom | top)
 * @return boolean (success | failure)
 * @throws sql_error()
 */
function move_text($text_id = false, $current_listorder = false, $action = false)
{
    global $config, $db;
    if (!$text_id || !$current_listorder || !$action || !eregi("(up|down|top|bottom)", $action)) {
        return false;
    }
    $queries = array();
    switch ($action) {
    case "up":
        array_push($queries, sprintf("UPDATE text SET listorder = listorder + 1 WHERE listorder = %d;", $current_listorder - 1));
        array_push($queries, sprintf("UPDATE text SET listorder = listorder - 1 WHERE text_id = %d;", $text_id));
        break;
    case "top":
        array_push($queries, sprintf("UPDATE text SET listorder = 0 WHERE text_id = %d;", $text_id));
        array_push($queries, sprintf("UPDATE text SET listorder = listorder + 1 WHERE listorder < %d;", $current_listorder));
        break;
    case "down":
        array_push($queries, sprintf("UPDATE text SET listorder = listorder - 1 WHERE listorder = %d;", $current_listorder + 1));
        array_push($queries, sprintf("UPDATE text SET listorder = listorder + 1 WHERE text_id = %d;", $text_id));
        break;
    case "bottom":
        array_push($queries, sprintf("UPDATE text SET listorder = %d WHERE text_id = %d;", get_next_listorder(), $text_id));
        array_push($queries, sprintf("UPDATE text SET listorder = listorder - 1 WHERE listorder > %d;", $current_listorder));
        break;
    }
    reset($queries);
    foreach ($queries as $query) {
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
            return false;
        }
    }
    return true;
}
////////////////////////////////////////////////////////////////////////////////
//                             GUI FUNCTIONS                                  //
////////////////////////////////////////////////////////////////////////////////
function get_text_select($selected_id = false, $sfx = "start")
{
    $ret = "";
    if ($all_text = get_text_details()) {
        $ret .= sprintf('<select name="section_%s">', $sfx);
        for ($i = 0; $i < count($all_text); $i++) {
            $txt = (strlen($all_text[$i]["content"]) > 35)? substr($all_text[$i]["content"], 0, 35) . "...": $all_text[$i]["content"];
            $sel = ($selected_id && $all_text[$i]["text_id"] == $selected_id)? ' selected="selected"': '';
            $ret .= sprintf('<option value="%s"%s>%s</option>', $all_text[$i]["text_id"], $sel, $txt);
        }
        $ret .= '</select>';
    }
    return $ret;
}
function get_section_select($selected_id = false, $sfx = "start")
{
    $ret = "";
    if ($all_sections = get_section_details()) {
        $ret .= sprintf('<select name="section_%s">', $sfx);
        for ($i = 0; $i < count($all_sections); $i++) {
            $sel = '';
            if ($selected_id) {
                $sel = ($selected_id == $all_sections[$i]["section_id"])? ' selected="selected"': '';
            } elseif ($i == 0) {
                $sel = ' selected="selected"';
            }
            $indent = ($all_sections[$i]["section_type"] == "subheading")? " - ": "";
            $ret .= sprintf('<option value="%s"%s>%s%s</option>', $all_sections[$i]["section_id"], $sel, $indent, stripslashes($all_sections[$i]["section_name"]));
        }
        $ret .= '</select>';
    }
    return $ret;
}
function get_behaviours_select($selected_id = false)
{
    global $config, $db;
    $query = "SELECT behaviour_id AS id, behaviour_name AS name FROM behaviours;";
    $result = $db->query($query);
    if (DB::isError($result)) {
        sql_error(__FILE__, __LINE__, $result->getMessage(), $query);
        return false;
    }
    $ret = '';
    if ($result->numRows()) {
        $ret = '<select name="behaviour_id">';
        while ($row = $result->fetchRow()) {
            if ($result->numRows() == 1) {
                $ret = sprintf('<input type="hidden" name="behaviour_id" value="%d" />%s', $row["id"], $row["name"]);
                break;
            }
            $sel = ($selected_id && $row["id"] == $selected_id)? ' selected="selected"': '';
            $ret .= sprintf('<option value="%d"%s>%s</option>', $row["id"], $sel, $row["name"]);
        }
        $ret .= '</select>';
    }
    return $ret;
}
////////////////////////////////////////////////////////////////////////////////
//                       UTILITY FUNCTIONS                                    //
////////////////////////////////////////////////////////////////////////////////

/**
 * print_nav
 *
 * prints navigation bar
 * @param string $pagename - current page
 */
function print_nav($page = "home")
{
    $pages = array("home" => "index.php",
                   "import" => "import.php",
                   "controls" => "controls.php");
    $pagenames = array("home" => "The&nbsp;Story&nbsp;of&nbsp;Art&nbsp;",
                   "import" => "&nbsp;Import&nbsp;",
                   "controls" => "&nbsp;Control&nbsp;Panel&nbsp;");
    print('<table summary="navigation" cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td width="50%"><h1>[' . $pagenames[$page] . ']</h1></td><td width="20%">&nbsp;</td>');
    foreach ($pages as $name=>$pageURL) {
        $link = ($page == $name)? '<td width="10%" align="center"><span class="current">' . $name . '</span></td>': '<td width="10%" align="center"><a href="' . $pageURL . '" class="nav">' . $name . '</a></td>';
        print($link);
    }
    print('</tr></table>');
}
/**
 * sql_error
 *
 * wrapper for the PEAR DB::Error so the query is shown in the output
 * and database errors can be logged or output to the browser
 *
 * @param $msg message from the getMessage method of the DB_RESULT class
 * @param $query SQL query sent to the database which caused the error
 */
function sql_error($prg = '', $line = 0, $msg = 'An unspecified error has occurred', $query = 'No database query specified')
{
    global $config;
    if ($config["sql_error_log"] == "") {
        // logging is not enabled - output to browser and exit
        printf('<p class="error">An error occurred in the query:<br /><b>%s</b><br />on line <b>%d</b> of the file <b>%s</b><br />The message from the database was:<br /><b>%s</b></p>', $query, $line, $prg, $msg);
        exit;
    } else {
        // logging is enabled - output to log and fail silently
        if ($fh = fopen($config["sql_error_log"], 'rb')) {
            $logStr = date("j/n/Y H:i:s") . " QUERY:: " . $query . " FILE:: " . $prg . " LINE:: " . $line . " MESSAGE:: " . $msg;
            fwrite($fh, $logStr);
        }
    }
}

/**
 * print_error
 *
 * prints an error and optionally logs it
 *
 * @param $msg message from the getMessage method of the DB_RESULT class
 * @param $line
 * @param $query SQL query sent to the database which caused the error
 */
function print_error($prg = '', $line = 0, $message = 'An unspecified error has occurred')
{
    global $config;
    if ($config["error_log"] == "") {
        // logging is not enabled - output to browser and exit
        printf('<p class="error">An error has occurred:<br />In the file <b>%s</b> on line <b>%s</b><br />The message reads:<br />%s</p>', $prg, $line, $message);
   } else {
        // logging is enabled - output to log and fail silently
        if ($fh = fopen($config["error_log"], 'rb')) {
            $logStr = date("j/n/Y H:i:s") . " FILE:: " . $prg . " LINE:: " . $line . " MESSAGE:: " . $msg;
            fwrite($fh, $logStr);
        }
    }
}
function get_microtime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}
?>
