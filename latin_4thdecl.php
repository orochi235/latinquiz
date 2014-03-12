<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Fourth Declension Quiz</title>
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
	width: 90%;
}
table {
	border-spacing: 0;
	border-collapse: collapse;
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
tr.column-heading-gender td.column-heading-1, tr.column-heading-gender td.column-heading-2 {
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
	padding-top: .5em;
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
</head>
<body>
<!-- Written by Michael Baker (twitter.com/devnull17), 2012 -->
<?php

$columns = array("singmasc","singfem","singneut","plurmasc","plurfem","plurneut");
$adjs = array("this","that","unemphatic","relative","interrogative");
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
"<b>is, ea, id</b> (this or that, unemphatic)</b>",
"relative pronouns",
"interrogative pronouns"
);
$case_labels = array("nominative", "genitive", "dative", "accusative", "ablative");

// load answers into numbered array
$fh = fopen("demoadj.txt", 'r');
$index = 0;
while (!feof($fh)) {
	$line = fgets($fh);
	$answers[$index] = trim($line);
	$index++;
}

// build row names
$index = 0;
for ($i = 0; $i < sizeof($adjs); $i++) {
	for ($j = 0; $j < sizeof($cases); $j++) {
		$rows[$index] = "$adjs[$i]$cases[$j]";
		$index++;
	}
}

// build cell names
$index = 0;
for ($i = 0; $i < sizeof($rows); $i++) {
	for ($j = 0; $j < sizeof($columns); $j++) {
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
	
	global $genders, $columns;
	
	for ($i = 0; $i < sizeof($columns); $i++) {
		echo "<td class=\"column-heading-" . (($i < sizeof($columns) / 2) ? "1" : "2") . "\">";
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
<input type="text" class="entry" name="<?php echo $c; ?>" id="<?php echo $c; ?>" value="<?php //echo $answers[$c]; ?>" <?php echo $tabindex; ?> onchange='//update(this);' onblur='//update(this);' onfocus='markActive(this);' onclick='//markActive(this);' />
<?php 

}

?>
<script language="javascript">

function checkAnswer(field) {
	if (field.value == "" || field.value == null) {
		markClear(field);
		return false;
	} else {
		if (isCorrect(field.name)) {
			field.value = field.value.toLowerCase();
			markRight(field);
			lockField(field);
			return true;
		} else {
			markWrong(field);
			return false;
		}
	}
}

function chooseNextField(currentField) {
	// move cursor to the next open field
	var closestOffset = 0;
	var chosenName = "";
	for (fieldName in lockStatus) {
		if (lockStatus[fieldName]) continue; // ignore locked fields
		var tabIndex = document.getElementById(fieldName).tabIndex;
		var distance = tabIndex - currentField.tabIndex;
		if (closestOffset == 0) {
			// the first value is always the best option so far
			console.log("Found better match for next field via rule 1 - current tab index is " + currentField.tabIndex + "; old closest offset was " + closestOffset + "; new one is " + distance + "(" + fieldName + ")");
			closestOffset = distance;
			chosenName = fieldName;
			continue;
		}
		
		if (closestOffset > 0) {
			// there's something after this in the list
			if (distance < closestOffset && distance > 0) {
				console.log("Found better match for next field via rule 2 - current tab index is " + currentField.tabIndex + "; old closest offset was " + closestOffset + "; new one is " + distance + "(" + fieldName + ")");
				closestOffset = distance;
				chosenName = fieldName;
			}
			continue;
		}
		
		if (closestOffset < 0) { // statement added for clarity
			// if we're wrapping around, we want to end up as low as possible on the list
			// we also want to automatically save if the current one is ahead in the list, and the previous best match was behind
			if (tabIndex < distance + closestOffset || distance > 0) {
				console.log("Found better match for next field via rule 3 - current tab index is " + currentField.tabIndex + "; old closest offset was " + closestOffset + "; new one is " + distance + "(" + fieldName + ")");
				closestOffset = distance;
				chosenName = fieldName;
			}
			continue;
		}
	}
	
	if (closestOffset != 0) {
		document.getElementById(chosenName).focus();
	}
}

function chooseRandomField() {
	var index = Math.floor(Math.random() * (openFields.length));
	var name = openFields[index];
	var field = document.getElementById(name);
	field.focus();
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

function getCookie(c_name) {
var i,x,y,ARRcookies=document.cookie.split(";");
for (i=0;i<ARRcookies.length;i++)
  {
  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  x=x.replace(/^\s+|\s+$/g,"");
  if (x==c_name)
    {
    return unescape(y);
    }
  }
}

function giveAnswer(field) {
	if (field == null) return;
	if (isLocked(field)) return;
	// field.style.fontWeight = isCorrect(field.name) ? "bold" : "normal";
	if (!isCorrect(field.name)) markSolved(field);
	field.value = answers[field.name];
	lockField(field);	
}

function handleBlur() {
	var result = checkAnswer(this);
}

function handleChange() {
	var result = checkAnswer(this);
}

function handleClick(e) {
	
	if (!e) var e = window.event;
	// e.cancelBubble = true;
	// TODO: go back to previous field if a non-active field was selected
	
	giveAnswer(this);

	if (isShuffleModeOn()) {
		//chooseRandomField();
	} else {
		chooseNextField(this);
	}
	
	return false;

}

function handleKeystroke(e) {
	var key = (window.event) ? event.keyCode : e.keyCode;
	switch (key) { 
	case 9:// tab
		if (isShuffleModeOn()) {
			e.cancelBubble = true;
			chooseRandomField();
		}
		break;
	case 13: // enter
		if (document.activeElement.tagName.toLowerCase() == "input" && document.activeElement.value.length == 0) {
			// pressing enter on a blank field should yield the answer
			var myField = document.activeElement;
			giveAnswer(myField);
			if (!isShuffleModeOn()) chooseNextField(myField);
			return;
		}
		checkAnswer(document.activeElement);
		markActive(document.activeElement);
		if (!isShuffleModeOn()) chooseNextField(document.activeElement);
		break;
	}
}

function isCorrect(fieldName) {
	field = document.getElementById(fieldName);
	return field.value.toLowerCase().replace(/(^\s+|\s+$)/g,' ') == answers[fieldName].toLowerCase();
}	

function isLocked(field) {
	return lockStatus[field.name];
}

function isShuffleModeOn() {
	return document.getElementById("shuffle").checked;	
}

function loadSettings() {
	var shuffle = document.getElementById("shuffle");
	var shuffleFlag = getCookie("latin_shuffle") == 1;	
	shuffle.checked = shuffleFlag;
}

function lockField(field) {
	field.disabled = true;
	lockStatus[field.name] = true;
	var index = openFields.indexOf(field.name);
	if (index < 0) {
		console.log("WARNING: failed to lock field '" + field.name + "', as it was not present in the collection.");
		return false; 	
	}
	openFields.splice(index, 1);
	console.log("Locked field '" + field.name + "'. " + openFields.length + " open field(s) remain.");
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
	field.style.backgroundColor = "slateblue";
	field.style.color = "white";
	field.style.fontWeight = "normal";
}

function markWrong(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "red";
	field.style.color = "white";
	field.style.fontWeight = "bold";
}

function saveSettings() {
	var shuffle = document.getElementById("shuffle");
	var shuffleFlag = shuffle.checked ? 1 : 0;
	setCookie("latin_shuffle", shuffleFlag, 1000);	
}

function setCookie(c_name,value,exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}

function unlockField(field) {
	field.disabled = false;
	lockStatus[field.name] = false;
	var index = openFields.indexOf(field.name);
	if (index < 0) openFields.push(field.name);
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
    <tr class="buttons">
        <td></td>
        <td colspan = "3" class="buttons" style="text-align: left; vertical-align: middle;">
            <input type="checkbox" name="shuffle" id="shuffle" style="display:inline; width: auto;" tabindex="-1"/>
    		<span>Shuffle (press tab to change fields)</span>
            <input type="checkbox" name="blind" id="blind" style="display:inline; width: auto;" tabindex="-1"/>
            <span>Hide answers (not yet implemented)</span>
        </td>
        <td colspan = "3" class="buttons" style="text-align: right;">
            <button onclick="fillAnswers();">Show Answers</button>
    		<button onclick="clearAll();">Clear All</button>
        </td>
    </tr>
  <?php

writeColumnHeadings();

$fieldCount = 0;
// iteration by section (adjs), then row name, then column name
for ($section = 0; $section < sizeof($headings); $section++) {
	$class = $section == 0 ? "" : "row-label";
	$class = "header-overlap";
	echo "<tr><td class=\"$class\"></td><td colspan=\"6\" class=\"section-heading\">$headings[$section]</td></tr>\n";
	for ($row = 0; $row < sizeof($case_labels); $row++) {
		echo "<tr>";
		for ($col = 0; $col < sizeof($columns) + 1; $col++) {
			$style = "";
			
			// TODO: replace these numeric constants
			
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
	window.oncontextmenu = null;
	var inputs = document.getElementsByTagName("input");
	for (elem in inputs) {
		inputs[elem].oncontextmenu = handleClick;
		inputs[elem].onchange = handleChange;
		inputs[elem].onblur = handleBlur;
	}
	
	// set up settings ui elements (currently just the shuffle checkbox)
	document.body.onload = loadSettings;
	document.body.onkeydown = handleKeystroke;
	document.getElementById("shuffle").onchange = saveSettings;
	
</script>
</body>
</html>