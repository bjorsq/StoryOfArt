<?php
set_time_limit(0);
include("includes/functions.php");
$start_time = get_microtime();
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
print_nav("controls");
?>
<table summary="" cellpadding="8" cellspacing="0" border="0">
  <tr>
    <td valign="top" width="25" rowspan="11">&nbsp;</td>
    <td valign="top" colspan="4"><h3>Global speed changes</h3></td>
  </tr>
  <tr>
    <td rowspan="2" width="25">&nbsp;</td>
    <td valign="top" colspan="3">
      <form method="get">
        <select name="speed">
          <option value="crawling">Crawling</option>
          <option value="pedestrian">Pedestrian</option>
          <option value="medium">Medium</option>
          <option value="fast">fast</option>
          <option value="silly">silly</option>
          <option value="bonkers">bonkers</option>
          <option value="insane">insane</option>
          <option value="psychotic">psychotic</option>
        </select>
        <input type="submit" name="change" value="go" />
      </form>
    </td>
  </tr>
  <tr>
    <td valign="top" colspan="3">
      <form method="get">
        Sentences per minute:&nbsp;<input type="text" name="spm_g" size="3" /><input type="submit" name="change" value="go" />
      </form>
    </td>
  </tr>
  <tr>
    <td valign="top" colspan="4"><h3>Global acceleration changes</h3></td>
  </tr>
  <tr>
    <td width="25">&nbsp;</td>
    <td valign="top" colspan="2">
      <form method="get">
        from&nbsp;<input type="text" name="acc_g_start" size="3" />&nbsp;to&nbsp;<input type="text" name="acc_g_finish" size="3" />&nbsp;sentences&nbsp;per&nbsp;minute
    </td>
    <td valign="top">
      <input type="submit" name="change" value="go" /> 
      </form>
    </td>
  </tr>
  <tr>
    <td valign="top" colspan="4"><h3>Speed changes by section</h3><form method="get"></td>
  </tr>
  <tr>
    <td width="25" rowspan="5">&nbsp;</td>
    <td valign="top" colspan="3">Section:&nbsp;<?php echo get_section_select(1, "id"); ?></td>
  </tr>
  <tr>
    <td valign="top" colspan="3"><h3>Flat rate</h3></td>
  </tr>
  <tr>
    <td width="25">&nbsp;</td>
    <td valign="top">
      Zoom&nbsp;in:&nbsp;<input type="text" name="sps_in" size="2" value="" />s&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Zoom&nbsp;out:&nbsp;<input type="text" name="sps_out" size="2" value="" />s
    </td>
    <td valign="top">
      <input type="submit" name="flat" value="go" />
    </td>
  </tr>
  <tr>
    <td valign="top" colspan="3"><h3>Acceleration</h3></td>
  </tr>
  <tr>
    <td width="25">&nbsp;</td>
    <td valign="top">
      from&nbsp;<input type="text" name="acc_s_start" size="3" />&nbsp;to&nbsp;<input type="text" name="acc_s_finish" size="3" />&nbsp;sentences&nbsp;per&nbsp;minute
    </td>
    <td valign="top">
      <input type="submit" name="acc" value="go" /> 
      </form>
    </td>
  </tr>
</table>
<?php
$speed_sql = array(
    "crawling"=>array("UPDATE attributes SET default_value = 25 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 1 WHERE attribute_id IN (2,10);"),
    /*"pedestrian"=>array("UPDATE attributes SET default_value = 8 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 1 WHERE attribute_id IN (2,10);"),*/
    "pedestrian"=>array("UPDATE attributes SET default_value = 7 WHERE attribute_id IN (1,9);",
                      "UPDATE attributes SET default_value = 13 WHERE attribute_id IN (3,11);",
                      "UPDATE attributes SET default_value = 0 WHERE attribute_id IN (2,10);"),
    "medium"=>array("UPDATE attributes SET default_value = 5 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 1 WHERE attribute_id IN (2,10);"),
    "fast"=>array("UPDATE attributes SET default_value = 1 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 0 WHERE attribute_id IN (2,10);"),
    "silly"=>array("UPDATE attributes SET default_value = 0.5 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 0 WHERE attribute_id IN (2,10);"),
    "bonkers"=>array("UPDATE attributes SET default_value = 0.25 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 0 WHERE attribute_id IN (2,10);"),
    "insane"=>array("UPDATE attributes SET default_value = 0.125 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 0 WHERE attribute_id IN (2,10);"),
    "psychotic"=>array("UPDATE attributes SET default_value = 0.05 WHERE attribute_id IN (1,3,9,11);",
                      "UPDATE attributes SET default_value = 0 WHERE attribute_id IN (2,10);"));
$queries = array();
if (isset($HTTP_GET_VARS["speed"])) {
    //global speed change using preset speed dropdown
    array_push($queries, "TRUNCATE TABLE `values`;");
    array_push($queries, $speed_sql[$HTTP_GET_VARS["speed"]][0]);
}
if (isset($HTTP_GET_VARS["spm_g"]) && $HTTP_GET_VARS["spm_g"] != "") {
    //global speed change using sentences per minute form
    array_push($queries, "TRUNCATE TABLE `values`;");
    $spm = (int) $HTTP_GET_VARS["spm_g"];
    $zoom_duration = (30 / $spm);
    array_push($queries, sprintf("UPDATE attributes SET default_value = %.3f WHERE attribute_id IN (1,3,9,11);", $zoom_duration));
}    
if (isset($HTTP_GET_VARS["acc_g_start"]) && $HTTP_GET_VARS["acc_g_start"] != "" && isset($HTTP_GET_VARS["acc_g_finish"]) && $HTTP_GET_VARS["acc_g_finish"] != "") {
    //global acceleration
    array_push($queries, "TRUNCATE TABLE `values`;");
    $s = (int) $HTTP_GET_VARS["acc_g_start"];
    $f = (int) $HTTP_GET_VARS["acc_g_finish"];
    $start_zoom_duration = (30 / $s);
    $finish_zoom_duration = (30 / $f);
    $interval = $start_zoom_duration - $finish_zoom_duration;
    $all_text = get_text_details();
    for ($i = 0 ; $i < count($all_text); $i++) {
        $zoom_duration = $start_zoom_duration - (($interval / count($all_text)) * $i);
        array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 1, $zoom_duration));
        array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 3, $zoom_duration));
        array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 9, $zoom_duration));
        array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 11, $zoom_duration));
    }
}
if (isset($HTTP_GET_VARS["section_id"]) && $HTTP_GET_VARS["section_id"] != "") {
    //section speed change
    $section_id = (int) $HTTP_GET_VARS["section_id"];
    $details = get_section_details($section_id);
    $section_details = $details[0];
    if (isset($HTTP_GET_VARS["flat"]) && isset($HTTP_GET_VARS["sps_in"]) && isset($HTTP_GET_VARS["sps_out"]) && $HTTP_GET_VARS["sps_in"] != "" && $HTTP_GET_VARS["sps_out"] != "") {
        //flat rate
        $zoom_in_duration = (int) $HTTP_GET_VARS["sps_in"];
        $zoom_out_duration = (int) $HTTP_GET_VARS["sps_out"];
        $all_text = get_text_details();
        $start_adding = false;
        $stop_adding = false;
        for ($i = 0; $i < count($all_text); $i++) {
            if ($all_text[$i]["text_id"] == $section_details["section_start"]) {
                $start_adding = true;
            }
            if ($all_text[$i]["text_id"] == $section_details["section_finish"]) {
                $stop_adding = true;
            }
            if ($start_adding && !$stop_adding) {
                array_push($queries, sprintf("DELETE FROM `values` WHERE text_id = %d", $all_text[$i]["text_id"]));
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 1, $zoom_in_duration));
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 3, $zoom_out_duration));
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 9, $zoom_in_duration));
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $all_text[$i]["text_id"], 11, $zoom_out_duration));
            }
        }
    }
    if (isset($HTTP_GET_VARS["acc"]) && isset($HTTP_GET_VARS["acc_s_start"]) && isset($HTTP_GET_VARS["acc_s_finish"]) && $HTTP_GET_VARS["acc_s_start"] != "" && $HTTP_GET_VARS["acc_s_finish"] != "") {
        //acceleration
        $s = (int) $HTTP_GET_VARS["acc_s_start"];
        $f = (int) $HTTP_GET_VARS["acc_s_finish"];
        $start_zoom_duration = (30 / $s);
        $finish_zoom_duration = (30 / $f);
        $interval = $start_zoom_duration - $finish_zoom_duration;
        $all_text = get_text_details();
        $text_to_change = array();
        $start_adding = false;
        $stop_adding = false;
        for ($i = 0 ; $i < count($all_text); $i++) {
            if ($all_text[$i]["text_id"] == $section_details["section_start"]) {
                $start_adding = true;
            }
            if ($start_adding && !$stop_adding) {
                array_push($text_to_change, $all_text[$i]);
            }
            if ($all_text[$i]["text_id"] == $section_details["section_finish"]) {
                $stop_adding = true;
            }
        }
        for ($i = 0 ; $i < count($text_to_change); $i++) {
            $zoom_duration = $start_zoom_duration - (($interval / count($text_to_change)) * $i);
            $in = $zoom_duration;
            $out = $zoom_duration;
            if ($zoom_duration > 5) {
                $in = 5;
                $out = $zoom_duration + ($zoom_duration - 5);
            }
            array_push($queries, sprintf("DELETE FROM `values` WHERE text_id = %d", $text_to_change[$i]["text_id"]));
            if ($text_to_change[$i]["behaviour_id"] == 1) {
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $text_to_change[$i]["text_id"], 1, $in));
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $text_to_change[$i]["text_id"], 3, $out));
            } else {
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $text_to_change[$i]["text_id"], 9, $in));
                array_push($queries, sprintf("INSERT INTO `values` (text_id, attribute_id, attribute_value) VALUES (%s, %s, %.2f);", $text_to_change[$i]["text_id"], 11, $out));
            }            
        }
    }
}
//print_r($queries);
if (!empty($queries)) {  
    foreach ($queries as $query) {
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(),$query);
        }
    }
}
$finish_time = get_microtime();
if (!empty($queries)) {
    printf("<p>script execution took %.6f seconds (%d database queries preformed)</p>", ($finish_time - $start_time), count($queries));
}
?>
</body>
</html>
