<?php

function cleanString($text) {
    // 1) convert á ô => a o
    $text = preg_replace("/[áàâãªäā]/u","a",$text);
    $text = preg_replace("/[ÁÀÂÃÄ]/u","A",$text);
    $text = preg_replace("/[ÍÌÎÏ]/u","I",$text);
    $text = preg_replace("/[íìîïī]/u","i",$text);
    $text = preg_replace("/[éèêëē]/u","e",$text);
    $text = preg_replace("/[ÉÈÊË]/u","E",$text);
    $text = preg_replace("/[óòôõºöō]/u","o",$text);
    $text = preg_replace("/[ÓÒÔÕÖ]/u","O",$text);
    $text = preg_replace("/[úùûü]/u","u",$text);
    $text = preg_replace("/[ÚÙÛÜ]/u","U",$text);
    $text = preg_replace("/[’‘‹›‚]/u","'",$text);
    $text = preg_replace("/[“”«»„]/u",'"',$text);
    $text = str_replace("–","-",$text);
    $text = str_replace(" "," ",$text);
    $text = str_replace("ç","c",$text);
    $text = str_replace("Ç","C",$text);
    $text = str_replace("ñ","n",$text);
    $text = str_replace("Ñ","N",$text);
 
    //2) Translation CP1252. &ndash; => -
    $trans = get_html_translation_table(HTML_ENTITIES); 
    $trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark 
    $trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook 
    $trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark 
    $trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis 
    $trans[chr(134)] = '&dagger;';    // Dagger 
    $trans[chr(135)] = '&Dagger;';    // Double Dagger 
    $trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent 
    $trans[chr(137)] = '&permil;';    // Per Mille Sign 
    $trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron 
    $trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark 
    $trans[chr(140)] = '&OElig;';    // Latin Capital Ligature OE 
    $trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark 
    $trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark 
    $trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark 
    $trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark 
    $trans[chr(149)] = '&bull;';    // Bullet 
    $trans[chr(150)] = '&ndash;';    // En Dash 
    $trans[chr(151)] = '&mdash;';    // Em Dash 
    $trans[chr(152)] = '&tilde;';    // Small Tilde 
    $trans[chr(153)] = '&trade;';    // Trade Mark Sign 
    $trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron 
    $trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark 
    $trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE 
    $trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis 
    $trans['euro'] = '&euro;';    // euro currency symbol 
    ksort($trans); 
     
    foreach ($trans as $k => $v) {
        $text = str_replace($v, $k, $text);
    }
 
    // 3) remove <p>, <br/> ...
    $text = strip_tags($text); 
     
    // 4) &amp; => & &quot; => '
    $text = html_entity_decode($text);
     
    // 5) remove Windows-1252 symbols like "TradeMark", "Euro"...
    $text = preg_replace('/[^(\x20-\x7F)]*/','', $text); 
     
    $targets=array('\r\n','\n','\r','\t');
    $results=array(" "," "," ","");
    $text = str_replace($targets,$results,$text);
     
    return ($text);
} 

function scrape($firstForm) {
	
	setlocale(LC_ALL, "en_US.utf8");

	$url = 'http://www.verbix.com/webverbix/go.php?T1=' . $firstForm . '&imageField.x=0&imageField.y=0&D1=9&H1=109';
	$output = file_get_contents($url); 
	
	$doc = new DOMDocument();
	@$doc->loadHTML($output);
	
	$warning = preg_match("/does not exist/i", $doc->saveHTML());
	if ($warning) {
		return false;	
	}
	
	$warning = preg_match("/not a verb infinitive/i", $doc->saveHTML());
	if ($warning) {
		return false;	
	}
	
	$tables = $doc->getElementsByTagName("table");
	
	$matches = array();
	
	// check table attributes to make sure it's the one we want
	foreach ($tables as $t) {
		if ($t->getAttribute("cellpadding") != "0") continue;
		if ($t->getAttribute("cellspacing") != "0") continue;
		if ($t->getAttribute("border") != "0") continue;
		if ($t->getAttribute("width") != "100%") continue;
		if ($t->getAttribute("height") == "48") continue;
		$matches[sizeof($matches)] = $t;
	}
	
	// $matches[0] is the one we want, and it contains one table
	$active = $matches[0]->getElementsByTagName("tr");
	$passive = $matches[1]->getElementsByTagName("tr");
	$voices = array($active, $passive);
	
	$extracted = array();
	foreach ($voices as $voice) {
		foreach ($voice as $tr) {
			
			$myTense = array();
			
			foreach ($tr->childNodes as $node) {
				if ($node->nodeType == XML_ELEMENT_NODE && $node->tagName == "td") {
					// we're now within the td's that hold the actual conjugations
					foreach ($node->childNodes as $child) {	
					 	$seenForms = array();
						if ($child->nodeType == XML_ELEMENT_NODE && $child->tagName == "p") {
							foreach ($child->childNodes as $inner) {
								if ($inner->nodeType == XML_ELEMENT_NODE && $inner->tagName == "span") {
									array_push($seenForms, $inner->getAttribute("class") == "notused" ? "" : cleanString($inner->nodeValue));
								}
								if ($inner->nodeType == XML_ELEMENT_NODE && $inner->tagName == "br") {
									// time to flush $seenForms
									if (sizeof($seenForms) > 0) {
										array_push($extracted, cleanString($seenForms[0]));
									}
									$seenForms = array();
								}
							}
						}
					}
				}
			}
		} // ^^^ this is fucking disgusting
	}
	return $extracted;
}
?>