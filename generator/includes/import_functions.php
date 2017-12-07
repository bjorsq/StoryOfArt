<?php
/* text import functions for soa */
function get_name()
{
    list($usec, $sec) = explode(' ', microtime());
    $seed = (float) $sec + ((float) $usec * 100000);
    mt_srand($seed);
    $id=md5(uniqid(mt_rand()));
	  return $id;
}
function cleanse($text)
{
    $allowed_characters = "0-9A-Za-z!#$%()*+,:;=?@_`{|}~&<>'¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿŒœŠšŸƒ–—‘’‚“”„†‡•…‰€™";
    $escaped_characters = array("\\", "/", "^", "\"", "[", "]");
    $escaped_regexes = array("'\\\'", "'\/'", "'\^'", "'\"'", "'\['", "'\]'");
    $escaped_replacements = array("###BACKSLASH###", "###FORWARDSLASH###", "###CARET###", "###DOUBLEQUOTE###", "###OPENSQUAREBRACKET###", "###CLOSESQUAREBRACKET###");
    $replacement_regexes = array("/###BACKSLASH###/", "/###FORWARDSLASH###/", "/###CARET###/", "/###DOUBLEQUOTE###/", "/###OPENSQUAREBRACKET###/", "/###CLOSESQUAREBRACKET###/");
    $escaped = preg_replace($escaped_regexes, $escaped_replacements, $text);
    $temp = preg_replace("/([^" . $allowed_characters . "])+/", " ", $escaped);
    $stripped = trim(preg_replace($replacement_regexes, $escaped_characters, $temp));    
    $replaced = replace_characters($stripped);
    $excess_stripped = strip_excess_whitespace($replaced);
    $result = trim($excess_stripped);
    return $result;
}
function strip_excess_whitespace($text)
{
    if (preg_match("/\s{2,}/", $text)) {
        preg_replace("/\s{2,}/", " ", $text);
    }
    return $text;
}
$chrs = array("&",
              "<",
              ">",
              "'",                   
              "¡",
              "¢",
              "£",
              "¤",
              "¥",
              "¦",
              "§",
              "¨",
              "©",
              "ª",
              "«",
              "¬",
              "­",
              "®",
              "¯",
              "°",
              "±",
              "²",
              "³",
              "´",
              "µ",
              "¶",
              "·",
              "¸",
              "¹",
              "º",
              "»",
              "¼",
              "½",
              "¾",
              "¿",
              "À",
              "Á",
              "Â",
              "Ã",
              "Ä",
              "Å",
              "Æ",
              "Ç",
              "È",
              "É",
              "Ê",
              "Ë",
              "Ì",
              "Í",
              "Î",
              "Ï",
              "Ð",
              "Ñ",
              "Ò",
              "Ó",
              "Ô",
              "Õ",
              "Ö",
              "×",
              "Ø",
              "Ù",
              "Ú",
              "Û",
              "Ü",
              "Ý",
              "Þ",
              "ß",
              "à",
              "á",
              "â",
              "ã",
              "ä",
              "å",
              "æ",
              "ç",
              "è",
              "é",
              "ê",
              "ë",
              "ì",
              "í",
              "î",
              "ï",
              "ð",
              "ñ",
              "ò",
              "ó",
              "ô",
              "õ",
              "ö",
              "÷",
              "ø",
              "ù",
              "ú",
              "û",
              "ü",
              "ý",
              "þ",
              "ÿ",
              "Œ",
              "œ",
              "Š",
              "š",
              "Ÿ",
              "ƒ",
              "–",
              "—",
              "‘",
              "’",
              "‚",
              "“",
              "”",
              "„",
              "†",
              "‡",
              "•",
              "…",
              "‰",
              "€",
              "™");
$ents = array("&amp;",
              "&lt;",
              "&gt;",
              "&apos;",
              "&#xa1;",
              "&#xa2;",
              "&#xa3;",
              "&#xa4;",
              "&#xa5;",
              "&#xa6;",
              "&#xa7;",
              "&#xa8;",
              "&#xa9;",
              "&#xaa;",
              "&#xab;",
              "&#xac;",
              "&#xad;",
              "&#xae;",
              "&#xaf;",
              "&#xb0;",
              "&#xb1;",
              "&#xb2;",
              "&#xb3;",
              "&#xb4;",
              "&#xb5;",
              "&#xb6;",
              "&#xb7;",
              "&#xb8;",
              "&#xb9;",
              "&#xba;",
              "&#xbb;",
              "&#xbc;",
              "&#xbd;",
              "&#xbe;",
              "&#xbf;",
              "&#xc0;",
              "&#xc1;",
              "&#xc2;",
              "&#xc3;",
              "&#xc4;",
              "&#xc5;",
              "&#xc6;",
              "&#xc7;",
              "&#xc8;",
              "&#xc9;",
              "&#xca;",
              "&#xcb;",
              "&#xcc;",
              "&#xcd;",
              "&#xce;",
              "&#xcf;",
              "&#xd0;",
              "&#xd1;",
              "&#xd2;",
              "&#xd3;",
              "&#xd4;",
              "&#xd5;",
              "&#xd6;",
              "&#xd7;",
              "&#xd8;",
              "&#xd9;",
              "&#xda;",
              "&#xdb;",
              "&#xdc;",
              "&#xdd;",
              "&#xde;",
              "&#xdf;",
              "&#xe0;",
              "&#xe1;",
              "&#xe2;",
              "&#xe3;",
              "&#xe4;",
              "&#xe5;",
              "&#xe6;",
              "&#xe7;",
              "&#xe8;",
              "&#xe9;",
              "&#xea;",
              "&#xeb;",
              "&#xec;",
              "&#xed;",
              "&#xee;",
              "&#xef;",
              "&#xf0;",
              "&#xf1;",
              "&#xf2;",
              "&#xf3;",
              "&#xf4;",
              "&#xf5;",
              "&#xf6;",
              "&#xf7;",
              "&#xf8;",
              "&#xf9;",
              "&#xfa;",
              "&#xfb;",
              "&#xfc;",
              "&#xfd;",
              "&#xfe;",
              "&#xff;",
              "&#x152;",
              "&#x153;",
              "&#x160;",
              "&#x161;",
              "&#x178;",
              "&#x192;",
              "&#x2013;",
              "&#x2014;",
              "&#x2018;",
              "&#x2019;",
              "&#x201A;",
              "&#x201C;",
              "&#x201D;",
              "&#x201E;",
              "&#x2020;",
              "&#x2021;",
              "&#x2022;",
              "&#x2026;",
              "&#x2030;",
              "&#x20AC;",
              "&#x2122;");
function replace_characters($text)
{
    global $chrs;
    //make $chrs into regexes
    $regexes = array();
    for ($i = 0; $i < count($chrs); $i++) {
        $regexes[$i] = "/" . $chrs[$i] . "/";
    }
    reset($regexes);
    global $ents;
    $res = preg_replace($regexes, $ents, $text);
    return $res;    
}
function reinstate_characters($text)
{
    global $ents;
    //make $ents into regexes
    $regexes = array();
    for ($i = 0; $i < count($ents); $i++) {
        $regexes[$i] = "/" . $ents[$i] . "/";
    }
    reset($regexes);
    global $chrs;
    $res = preg_replace($regexes, $chrs, $text);
    return $res;    
}
function page_header()
{
    return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title>The Story of Art</title></head><body>';
}
function page_footer()
{
    return '</body></html>';
}
function section_heading($text)
{
    return sprintf('<h1>%s</h1>', strtoupper($text));
}
function section_subheading($text)
{
    return sprintf('<h2>%s</h2>', $text);
}
function section_text($text)
{
    return sprintf('<p>%s</p>', $text);
}
/*
generates ascii list for use in replace_high_ascii()
*/
function generate_ascii_list()
{
    echo "<pre>";
    $over = range('a','h');
    $extended = array("152","153","160","161","178","192","2013","2014","2018","2019","201A","201C","201D","201E","2020","2021","2022","2026","2030","20AC","2122");
    for ($l = 0; $l < 6; $l++) {
        for ($i = 0; $i < 16; $i++) {
            if ($i < 10) {
                echo "                  \"'&#x" . $over[$l] . $i . ";'\",\n";
            } else {
                $idx = $i-10;
                echo "                  \"'&#x" . $over[$l] . $over[$idx] . ";'\",\n";
            }
        }
    }
    foreach ($extended as $x) {
        echo "                  \"'&#x" . $x . ";'\",\n";
    }
    for ($l = 0; $l < 6; $l++) {
        for ($i = 0; $i < 16; $i++) {
            if ($i < 10) {
                echo "                  \"'&amp;#x" . $over[$l] . $i . ";'\",\n";
            } else {
                $idx = $i-10;
                echo "                  \"'&amp;#x" . $over[$l] . $over[$idx] . ";'\",\n";
            }
        }
    }
    foreach ($extended as $x) {
        echo "                  \"'&amp;#x" . $x . ";'\",\n";
    }
    echo "</pre>";
}
?>
