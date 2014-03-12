<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Demonstrative Adjectives Quiz</title>
<style type="text/css">
	
	body {
		background-color: white;
		
	}
	
	button {
		font-size: 18pt;	
	}
	
	input {
		border: 1px solid black;
		font-size: 18pt;
		font-weight: bold;
		/*height: 90%;*/
		margin-left: 5px;
		margin-right: 5px;
		padding: 2%;
		vertical-align: baseline;
		width:90%;
	}
	
	table {
		border-spacing:0;
 		border-collapse:collapse;
		
		width: 80%;
	}
	
	td {
		vertical-align: baseline;
	}
	
	td.entry-1 {
		background-color: #A0A0A0;
		
	}
	
	td.entry-2 {
		background-color: #606060;
		
	}
	
	td.row-label {
		font-size: 16pt;
		padding-right: .5em;
		width: 12%;
	}
	
	tr.column-heading-gender td.column-heading-1,tr.column-heading-gender td.column-heading-2 {
		background-color: #F0F0F0;
		color: black;
		font-size: 16pt;
		font-weight: bold;
		padding-bottom: .25em;
		padding-top: .3em;
		text-align: center;	
		vertical-align: baseline;
	}
	
	tr.column-heading-number td.column-heading-1, tr.column-heading-number td.column-heading-2 {
		font-size: 12pt;
		font-weight: normal;
		padding-bottom: .4em;
		padding-top:.5em;
		text-align: center;
		text-transform: uppercase;
	}	
	
	td.column-heading-1 {
		background-color: #A0A0A0;
		color: white;
	}
	
	td.column-heading-2 {
		background-color: #606060;
		color: white;
	}
	
	td.header-overlap {
		background-color: #E0E0E0;
	}
	
	.row-label {
		background-color: #F0F0F0;
		font-weight: bold;
		text-align: right;
		vertical-align: baseline;
	}
	
	.section-heading {
		background-color: #D0D0D0;
		padding: .25em 0 .18em 0;
		font-size: 18pt;
		text-align: center;	
	}
	
</style>
</head><body>
<!-- Written by Michael Baker (twitter.com/devnull17), 2012 -->
<?php

$columns = array("singmasc","singfem","singneut","plurmasc","plurfem","plurneut");
$adjs = array("this","that","unemphatic");
$cases = array("nom","gen","dat","acc","abl");
$genders = array("masculine", "feminine", "neuter");
$numbers = array("singular", "plural");
$rows = array();
$cells = array();
$answers = array();
$script_decls = "";
$headings = array(
"<b>hic, haec, hoc</b> (this)</b>",
"<b>ille, illa, illud</b> (that)</b>",
"<b>eo, ea, id</b> (this or that, unemphatic)</b>"
);
$case_labels = array("nominative", "genitive", "dative", "accusative", "ablative");

// load answers into numbered array
$fh = fopen("/demoadj.txt", 'r');
$index = 0;
while (!feof($fh)) {
	$line = fgets($fh);
	$answers[$index] = trim($line);
	$index++;
}

// build row names
$index = 0;
for ($i = 0; $i < 3; $i++) {
	for ($j = 0; $j < 5; $j++) {
		$rows[$index] = "$adjs[$i]$cases[$j]";
		$index++;
	}
}

// build cell names
$index = 0;
for ($i = 0; $i < 15; $i++) {
	for ($j = 0; $j < 6; $j++) {
		$name = "$rows[$i]_$columns[$j]";
		$cells[$index] = $name;
		$answers[$name] = $answers[$index];
		$index++;
	}
}

// builds js answer declaration from $cells and $answers
function buildFieldLists() {
	
	global $cells, $answers;
	
	$out = "";
	foreach ($cells as $c) {
		$line = "answers['$c'] = '$answers[$c]';\n";
		$line .= "fields.push('$c');\n";
		$line .= "openFields.push('$c');\n";
		$line .= "lockStatus['$c'] = false;\n";
		$out .= $line;
	}
	
	return $out;
}

function writeColumnHeadings() {
	// TODO: this may have to be changed for each implementation
?>
	<tr class="column-heading-gender">
    	<td></td>
<?php
	
	global $genders;
	
	for ($i = 0; $i < 6; $i++) {
		echo "<td class=\"column-heading-" . ($i < 3 ? "1" : "2") . "\">";
		echo $genders[$i % 3];
		echo "</td>\n";
	}
?>
        
    </tr>
   	<tr class="column-heading-number">
    	<td></td>
        <td class="column-heading-1" colspan="3">singluar</td>
        <td class="column-heading-2" colspan="3">plural</td>
    </tr>
<?php	
}

function writeField($c, $index="") {
	global $answers;

	$tabindex = strlen($index) > 0 ? "tabindex=\"$index\" " : "";
	?>
<input type="text" class="entry" name="<?php echo $c; ?>" id="<?php echo $c; ?>" value="<?php //echo $answers[$c]; ?>" <?php echo $tabindex; ?> onchange='update(this);' onblur='update(this);' onfocus='markActive(this);' oncontextmenu='handleClick(this); return false;' />
<?php 

}

?>
<script language="javascript">

function checkAnswer(field) {
	if (field.value == "") {
		markClear(field);
	} else {
		if (isCorrect(field.name)) {
			field.value = field.value.toLowerCase();
			markRight(field);
			lockField(field);
		} else {
			markWrong(field);
		}
	}
}

function clearAll() {
	for (i = 0; i < fields.length; i++) {
		var f = document.getElementById(fields[i]);
		unlockField(f);
		clearField(f);
		markClear(f);
	}
}

function clearField(field) {
	field.value = "";
	field.style.fontWeight = "bold";
}

function fillAnswers() {
	for (i = 0; i < fields.length; i++) {
		giveAnswer(document.getElementById(fields[i]));
		lockField(document.getElementById(fields[i]));
	}
}

function giveAnswer(field) {
	if (field == null) return;
	if (isLocked(field)) return;
	field.style.fontWeight = isCorrect(field.name) ? "bold" : "normal";
	if (!isCorrect(field.name)) markSolved(field);
	field.value = answers[field.name];
	
}

function handleClick(field) {
	var elem = document.activeElement;
	giveAnswer(field);
	lockField(field);
	///elem.focus();
}

function isCorrect(fieldName) {
	field = document.getElementById(fieldName);
	return field.value.toLowerCase() == answers[fieldName].toLowerCase();
}	

function isLocked(field) {
	return lockStatus[field.name];
}

function lockField(field) {
	field.disabled = true;
	lockStatus[field.name] = true;
	var index = openFields.indexOf(field.name);
	alert(field.name + " / " + index + " / " + openFields.length + " / " + openFields.splice(index, 1).length);
	openFields = openFields.splice(index, 1);
	
}

function markActive(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "#FFFF66";
	field.style.color = "black";
	field.style.fontWeight = "bold";
}

function markClear(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "white";
	field.style.color = "black";
	field.style.fontWeight = "bold";
}

function markRight(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "green";
	field.style.color = "white";
	field.style.fontWeight = "normal";
}

function markSolved(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "lightslateblue";
	field.style.color = "white";
	field.style.fontWeight = "normal";
}

function markWrong(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "red";
	field.style.color = "white";
	field.style.fontWeight = "bold";
}

function unlockField(field) {
	field.disabled = false;
	lockStatus[field.name] = false;
}

function update(field) {
	checkAnswer(field);
}

	var answers = new Array();
	var fields = new Array();
	var openFields = new Array();
	var lockStatus = new Array();
	<?php echo buildFieldLists(); ?>
</script>

<table name="main">

<?php

writeColumnHeadings();

$fieldCount = 0;
// iteration by section (adjs), then row name, then column name
for ($section = 0; $section < 3; $section++) {
	$class = $section == 0 ? "" : "row-label";
	$class = "header-overlap";
	echo "<tr><td class=\"$class\"></td><td colspan=\"6\" class=\"section-heading\">$headings[$section]</td></tr>\n";
	for ($row = 0; $row < 5; $row++) {
		echo "<tr>";
		for ($col = 0; $col < 7; $col++) {
			$style = "";
			
			switch ($col) {
			case 1:	
				$style = "padding-left: .4em; ";
				break;
			case 3:
				$style = "padding-right: .4em; ";
				break;
			case 4:	
				$style = "padding-left: .4em; ";
				break;
			case 6:
				$style = "padding-right: .4em; ";
				break;				
				
			}
			
			switch ($row) {
				case 0:
					$style .= "padding-top: .4em; ";
					break;
				case 4:
					$style .= "padding-bottom: .4em; ";
					break;
			}
			
			if (strlen($style) > 0) $style = "style=\"" . $style . "\" ";
			
			echo "<td class=\"" . ($col == 0 ? "row-label" : "entry-" . ($col < 4 ? "1" : "2")) . "\"" . $style . ">\n";
			if ($col == 0) {
				echo $case_labels[$row];
			} else {
				
				// ugly math to map tab indices
				$base = $fieldCount % 30;
				$sect = (int)($fieldCount / 30);
				

				$subrow = (int)(($base % 15) / 3);
				$wholerow = (int)($base / 6);
				$offset = $base % 6 > 2 ? (4 - $wholerow ) * 3: $wholerow * -3;
				$tabindex = $fieldCount + $offset + 1;
				
				writeField("$adjs[$section]$cases[$row]_". $columns[$col - 1], $tabindex);
				$fieldCount++;
			}
			echo "</td>\n";
		}
		echo "</tr>\n";
	}
}


?>
</table>
<script language="javascript">
	// giveAnswer(document.getElementById("unemphaticnom_singfem"));
</script>
<button onclick="fillAnswers();">Show Answers</button>
<button onclick="clearAll();">Clear All</button>
</body></html>