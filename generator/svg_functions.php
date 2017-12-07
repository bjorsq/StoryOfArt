<?php
function get_svg($listorder = 1)
{
    global $config, $db;
    $start = (int) $listorder;
    $query = "SELECT t.*, b.behaviour_name FROM text t, behaviours b WHERE listorder > %d AND t.behaviour_id = b.behaviour_id ORDER BY listorder;", $start);
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
            $ret[$count]["width"] = $row["width"];
            $ret[$count]["lines"] = $row["lines"];
            $ret[$count]["listorder"] = $row["listorder"];
            $ret[$count]["behaviour_id"] = $row["behaviour_id"];
            $count++;
        }
    }
    return $ret;
}
?>
