<?php
set_time_limit(0);
include("includes/functions.php");
include("includes/svg_functions.php");
require_once("includes/File.php");
if (isset($HTTP_GET_VARS["download"]) && $HTTP_GET_VARS["download"] != "") {
    require_once("includes/pclzip-2-0/pclzip.lib.php");
    $zipname = $HTTP_GET_VARS["download"] . ".zip";
    if(file_exists($config["svg_store_dir"] . $zipname)) {
        @unlink($config["svg_store_dir"] . $zipname);
    }
    $zipfile = new PclZip($config["svg_store_dir"] . $zipname);
    $zipfile->create($config["svg_store_dir"] . $zipname, PCLZIP_OPT_REMOVE_PATH, "story_of_art");
    $zipfile->add($config["svg_store_dir"] . $HTTP_GET_VARS["download"], PCLZIP_OPT_REMOVE_PATH, "story_of_art");
    $zipfile->add($config["svg_store_dir"] . "soa.es", PCLZIP_OPT_REMOVE_PATH, "story_of_art");
    printf("<script>window.location.href='/soa/SVG/%s';</script>", $zipname);
    exit;
}
$filename = "temp.svg";
if (isset($HTTP_GET_VARS["filename"]) && $HTTP_GET_VARS["filename"] != "") {
    $filename = $HTTP_GET_VARS["filename"];
}
if(file_exists($config["svg_store_dir"] . $filename)) {
    @unlink($config["svg_store_dir"] . $filename);
}
$from = 1;
if (isset($HTTP_GET_VARS["from_id"]) && $HTTP_GET_VARS["from_id"] != "") {
    $from = (int) $HTTP_GET_VARS["from_id"];
}
$to = false;
if (isset($HTTP_GET_VARS["to_id"]) && $HTTP_GET_VARS["to_id"] != "") {
    $to = (int) $HTTP_GET_VARS["to_id"];
}
$file_contents = '';
$smil = '';
$duration = 0;
$s = "<!--LINE-->";
$report_text = array();
$report_line = array("section heading","current speed","elapsed time");
array_push($report_text, $report_line);
$text = "";
if ($svg_text = get_svg_text($from, $to)) {
    $file_contents .= get_svg_head($s);
    for ($i = 0; $i < count($svg_text); $i++) {
        $file_contents .= get_text($svg_text[$i], $s);
        $islast = ($i == (count($svg_text) - 1))? true: false;
        $smil = get_animation_tags($svg_text[$i], $i, $s, $islast);
        $report_line = array($svg_text[$i]["content"], (60 / $smil["duration"]), $duration);
        $duration += $smil["duration"];
        if ($smil["isheader"]) {
            array_push($report_text, $report_line);
        }
        $file_contents .= $smil["text"];
    }
    $file_contents .= get_svg_foot($s);
}
if(file_exists($config["svg_store_dir"] . "report.csv")) {
    @unlink($config["svg_store_dir"] . "report.csv");
}
$fr = new File();
for ($i = 0; $i < count($report_text); $i++) {
    $line = implode(",", $report_text[$i]);
    $fr->writeLine($config["svg_store_dir"] . "report.csv", $line, FILE_MODE_APPEND, "\n");
}
$fr->close($config["svg_store_dir"] . "report.csv", FILE_MODE_READ);
$runtime_hours = floor($duration/3600);
$runtime_minutes = floor(($duration - ($runtime_hours * 3600))/60);
$runtime_seconds = $duration - ($runtime_hours * 3600) - ($runtime_minutes * 60);
$runtime = sprintf("%s:%s:%s", $runtime_hours, $runtime_minutes, $runtime_seconds);
$lines = explode($s, $file_contents);
$fp = new File();
for ($i = 0; $i < count($lines); $i++) {
    $fp->writeLine($config["svg_store_dir"] . $filename, $lines[$i], FILE_MODE_APPEND, "\n");
}
$fp->close($config["svg_store_dir"] . $filename, FILE_MODE_READ);

if (isset($HTTP_GET_VARS["filename"]) && $HTTP_GET_VARS["filename"] != "") {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title>Story of Art: SVG generator</title>
<script language="JavaScript" type="text/javascript" src="scripts.js"></script>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div style="float:right"><a href="javascript:self.close();" class="button">close</a></div>
<h3>[Story of Art]</h3>
<p>The file <b><?php echo $filename; ?></b> has been saved successfully.</p>
<p>Total running time is <?php echo $runtime; ?>.</p>
<p>If you would like to view this file now, <a href="javascript:openSVG('<?php echo $filename; ?>')">click here</a></p>
<p>If you would like to download this file as a zip archive, <a href="soaSVG.php?download=<?php echo $filename; ?>">click here</a></p>
</body>
</html>
<?php
} else {
  print("<script>window.location.href='/soa/SVG/temp.svg';</script>");
}
?>