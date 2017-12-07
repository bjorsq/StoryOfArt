<?php
set_time_limit(0);
include("includes/functions.php");
include("includes/import_functions.php");
$msg = "";
$successmsg = "";
$queries = array();
$output = ""; 
if (isset($HTTP_GET_VARS["export"])) {
    if ($text_details = get_text_details()) {
        for ($i = 0; $i < count($text_details); $i++) {
            $text = reinstate_characters($text_details[$i]["content"]);
            $add_linebreak = false;
            switch ($text_details[$i]["fontsize"]) {
            case "1.3":
                $output .= "\n\n!!" . $text . ".\n\n";
                break;
            case "1.2":
                $output .= "\n\n!" . $text . ".\n\n";
                break;
            default:
                $output .= $text . " ";
                break;
            }
        }
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");              // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");                                    // HTTP/1.0 
        header("Content-Type: application/x-msword");
        header("Content-Length: " . strlen($output));
        header("Content-Disposition: attachment; filename=soa.doc;");
        echo $output;
        exit;
    }
}
if (isset($HTTP_POST_VARS["import"])) {
    $filename = $config["imports_store_dir"] . get_name() . ".txt";
    if (move_uploaded_file($HTTP_POST_FILES["raw"]["tmp_name"], $filename)) {
        $fp = fopen($filename, "rb");
        if (!$fp) {
            $msg = "Failed to open uploaded file";
        } else {
            $queries = array("TRUNCATE TABLE `values`;", "TRUNCATE TABLE `text`;", "TRUNCATE TABLE `sections`;");
            $contents = fread ($fp, filesize($filename));
            fclose($fp);
            $lines = explode(".", $contents);
            $count = 1;
            $heading_count = 0;
            $subheading_count = 0;
            $headings = array();
            $subheadings = array();
            $speed_checkpoints = array("THE STORY OF ART", "MEDIEVAL ART", "REALISM", "HISTORY PAINTING", "CUBISM", "THE ST IVES GROUP", "END OF SIXTH PART");
            $big_sections = array("first part"=>array("start"=>"THE STORY OF ART","finish"=>"MEDIEVAL ART"),
                                  "second part"=>array("start"=>"MEDIEVAL ART","finish"=>"REALISM"),
                                  "third part"=>array("start"=>"REALISM","finish"=>"HISTORY PAINTING"),
                                  "fourth part"=>array("start"=>"HISTORY PAINTING","finish"=>"CUBISM"),
                                  "fifth part"=>array("start"=>"CUBISM","finish"=>"THE ST IVES GROUP"),
                                  "sixth part"=>array("start"=>"THE ST IVES GROUP","finish"=>"END OF SIXTH PART"),
                                  "last part"=>array("start"=>"END OF SIXTH PART","finish"=>"")
                            );
            $start_points = array();
            $finish_points = array();
            for ($i = 0; $i < count($lines); $i++) {
                $line = cleanse($lines[$i]);
                if ($line != "" && !empty($line) && $line != "!!" && $line != "!") {
                    $behaviour_id = 1;
                    $fontsize = 1.1;
                    if (preg_match("'^!!(.*)'", $line, $matches)) {
                        //heading
                        $heading_count++;
                        $text = trim(strtoupper($matches[1]));
                        if (in_array($text, $speed_checkpoints)) {
                            foreach ($big_sections as $name=>$section) {
                                if ($section["start"] == $text) {
                                    $start_points[$name] = $count;
                                }
                                if ($section["finish"] == $text) {
                                    $finish_points[$name] = ($count - 1);
                                }
                            }
                        }
                        $behaviour_id = 2;
                        $fontsize = 1.3;
                        array_push($headings, array("section_id"=>$heading_count, "section_name"=>$text, "section_listorder"=>$heading_count, "section_start"=>$count, "section_finish"=>0, "section_type"=>'heading')); 
                    } elseif (preg_match("'^!(.*)'", $line, $matches)) {
                        //sub-heading
                        $heading_count++;
                        $fontsize = 1.2;
                        $text = trim($matches[1]);
                        array_push($subheadings, array("section_id"=>$heading_count, "section_name"=>$text, "section_listorder"=>$heading_count, "section_start"=>$count, "section_finish"=>0, "section_type"=>'subheading')); 
                    } else {
                        $text = $line . ".";
                    }
                    array_push($queries, sprintf("INSERT INTO `text` (text_id, content, behaviour_id, fontsize, listorder, in_section) VALUES (%d, '%s', %d, %.1f, %d, 0);", $count, addslashes($text), $behaviour_id, $fontsize, $count));
                    $count++;
                }
            }
            //process headings
            if (!empty($headings)) {
                for ($h = 0; $h < count($headings); $h++) {
                    if ($h > 0) {
                        $headings[($h - 1)]["section_finish"] = $headings[$h]["section_start"];
                    }
                    if ($h == (count($headings) - 1)) {
                        $headings[$h]["section_finish"] = "end";
                    }
                }
                for ($hs = 0; $hs < count($headings); $hs++) {
                    array_push($queries, sprintf("INSERT INTO `sections` (section_id, section_name, listorder, section_start, section_finish, section_type) VALUES (%d, '%s', %d, %d, %d, 'heading');", $headings[$hs]["section_id"], $headings[$hs]["section_name"], $headings[$hs]["section_listorder"], $headings[$hs]["section_start"], $headings[$hs]["section_finish"]));
                }
            }
            $no_of_lines = count($queries);
            $no_of_headings = (count($headings) + count($subheadings));
            foreach ($big_sections as $name=>$values) {
                $no_of_headings++;
                if ($name == "last part") {
                    $finish_points["last part"] = ($count - 1);
                }
                array_push($queries, sprintf("INSERT INTO `sections` (section_id, section_name, listorder, section_start, section_finish, section_type) VALUES (%d, '%s', %d, %d, %d, 'heading');", $no_of_headings, $name, $no_of_headings, $start_points[$name], $finish_points[$name]));
            }
            array_push($queries, sprintf("UPDATE section_seq SET id = %d", $no_of_headings));
            array_push($queries, sprintf("UPDATE text_seq SET id = %d", $no_of_lines));
            //process subheadings
            if (!empty($subheadings)) {
                for ($s = 0; $s < count($subheadings); $s++) {
                    if ($s > 0) {
                        $subheadings[($s - 1)]["section_finish"] = $subheadings[$s]["section_start"];
                    }
                    if ($s == (count($subheadings) - 1)) {
                        $subheadings[$s]["section_finish"] = "end";
                    }
                }
                for ($sh = 0; $sh < count($subheadings); $sh++) {
                    array_push($queries, sprintf("INSERT INTO `sections` (section_id, section_name, listorder, section_start, section_finish, section_type) VALUES (%d, '%s', %d, %d, %d, 'subheading');", $subheadings[$sh]["section_id"], $subheadings[$sh]["section_name"], $subheadings[$sh]["section_listorder"], $subheadings[$sh]["section_start"], $subheadings[$sh]["section_finish"]));
                }
            }
            $successmsg = $no_of_lines . " lines of text imported successfully";
        }
    } else {
        $msg = "Failed to upload file";
    }
}
if (isset($HTTP_GET_VARS["delete"])) {
    $queries = array("TRUNCATE TABLE `values`;", "TRUNCATE TABLE `text`;", "TRUNCATE TABLE `sections`;");
    $successmsg = "text deleted successfully";
}
if (!empty($queries)) {  
    foreach ($queries as $query) {
        $result = $db->query($query);
        if (DB::isError($result)) {
            sql_error(__FILE__, __LINE__, $result->getMessage(),$query);
        }
    }
    $msg = $successmsg;
}            

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
print_nav("import");
?>
<table summary="" cellpadding="8" cellspacing="0" border="0">
<?php
if ($msg != "") {
    echo("<tr><td colspan=\"3\">$msg</td></tr>\n");
}
?>
<tr>
<td valign="top" width="25">&nbsp;</td>
<td valign="top">Text file to import:</td>
<td valign="top">
<form method="post" action="import.php" enctype="multipart/form-data">
<input type="file" name="raw" /><input type="submit" name="import" value="import" />
</form>
</td>
</tr>
</table>
<h1>[&nbsp;export&nbsp;]</h1>
<table summary="" cellpadding="8" cellspacing="0" border="0">
<tr>
<td valign="top" width="25">&nbsp;</td>
<td valign="top">
<form method="get">
<input type="submit" name="export" value="export all text" />
</form>
</td>
</tr>
</table>

<h1>[&nbsp;delete&nbsp;]</h1>
<table summary="" cellpadding="8" cellspacing="0" border="0">
<tr>
<td valign="top" width="25">&nbsp;</td>
<td valign="top">
<form method="get">
<input type="submit" name="delete" value="delete all text" />
</form>
</td>
</tr>
</table>
</body>
</html>
