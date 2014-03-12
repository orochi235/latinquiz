<?php

ob_start();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ferre Quiz</title>
<style type="text/css">
<?php include_once("latin.css"); ?>
</style>
</head>
<body>
<!-- Written by Michael Baker (twitter.com/devnull17), 2012 -->
<?php

include_once("scrape.php");

$voices = array("act","pass");
$moods = array("ind","subj");
$tenses = array("pres","imp","fut","perf","plu","futperf");
$fulltenses = array();
$persons = array("1","2","3");
$numbers = array("singular", "plural");
$rows = array();
$columns = array();
$cells = array();
$answers = array();
$script_decls = "";
$headings = array(
"present",
"imperfect",
"future",
"perfect",
"pluperfect",
"future perfect"
);
$case_labels = array("1st person", "2nd person", "3rd person");

// scrape verb forms from verbix.com ;)
// forms are grouped by voice, then tense, then mood, then number, then person
$forms = scrape("fero");

$index = 0;
for ($v = 0; $v < 2; $v++) {
	for ($t = 0; $t < 6; $t++) {
		for ($m = 0; $m < 2; $m++) {
			if ($m == 1 && $t % 3 == 2) continue; // no subjunctive future forms exist
			for ($i = 0; $i < 6; $i++) {
				$myNumber = $i < 3 ? "singular" : "plural";
				$myPerson = $i % 3 + 1;
				$answers["$voices[$v]_$moods[$m]_" . $myNumber . "_$tenses[$t]_" . $myPerson] = $forms[$index + $i];
			}
			
			$index += 6;
		}
	}
}

/*
// load answers into numbered array
$fh = fopen("ferre.txt", 'r');
$index = 0;
while (!feof($fh)) {
	$line = fgets($fh);
	$answers[$index] = trim($line);
	$index++;
}*/

// build row names
$index = 0;
for ($i = 0; $i < sizeof($tenses); $i++) {
	for ($j = 0; $j < sizeof($persons); $j++) {
		$rows[$index] = "$tenses[$i]_$persons[$j]";
		$index++;
	}
}

// build a complete list of tenses
$index = 0;
for ($v = 0; $v < sizeof($voices); $v++) {
	for ($m = 0; $m < sizeof($moods); $m++) {
		for ($t = 0; $t < sizeof($tenses); $t++) {
			$fulltenses[$index] = "$voices[$v]_$moods[$m]_$tenses[$t]";
			$index++;
		}
	}
}

// build column headings
$index = 0;
for ($v = 0; $v < sizeof($voices); $v++) {
	for ($m = 0; $m < sizeof($moods); $m++) {
		for ($n = 0; $n < sizeof($numbers); $n++) {
			$columns[$index] = "$voices[$v]_$moods[$m]_$numbers[$n]";
			$index++;
		}
	}
}
// build cell names
$index = 0;
for ($i = 0; $i < sizeof($columns); $i++) {
	for ($j = 0; $j < sizeof($tenses); $j++) {
		for ($k = 0; $k < sizeof($persons); $k++) {
	
			$name = "$columns[$i]_$tenses[$j]_$persons[$k]";
			if ($i / 2 % 2 == 1 && $j % 3 == 2) continue; // don't make fields for future subjunctive tenses
				$cells[$index] = $name;
	//		$answers[$name] = $answers[$index];
				$index++;
		}
	}
}

// builds js answer declaration from $cells and $answers
function buildFieldLists() {
	
	global $cells, $answers;
	
	$out = "";
	foreach ($cells as $c) {
		if (array_key_exists($c, $answers))
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
	
	global $voices, $moods, $numbers, $columns;
	
	for ($i = 0; $i < sizeof($columns); $i++) {
		echo "<td class=\"column-heading-" . (($i < sizeof($columns) / 2) ? "1" : "2") . "\">";
		echo $numbers[$i % sizeof($voices)];
		echo "</td>\n";
	}
?>
</tr>
<tr class="column-heading-number">
  <td></td>
  <td class="column-heading-1" colspan="4">active</td>
  <td class="column-heading-2" colspan="4">passive</td>
</tr>
<tr class="column-heading-number">
  <td></td>
  <td class="column-heading-1" colspan="2">indicative</td>
  <td class="column-heading-1" colspan="2">subjunctive</td>
  <td class="column-heading-2" colspan="2">indicative</td>
  <td class="column-heading-2" colspan="2">subjunctive</td>
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
        <td colspan="3" class="buttons" style="text-align: left; vertical-align: middle;">
            <input type="checkbox" name="shuffle" id="shuffle" style="display:inline; width: auto;" tabindex="-1"/>
    		<span>Shuffle (press tab to change fields)</span>
            <input type="checkbox" name="blind" id="blind" style="display:inline; width: auto;" tabindex="-1"/>
            <span>Hide answers (not yet implemented)</span>
        </td>
        <td colspan="5" class="buttons" style="text-align: right;">
            <button onclick="fillAnswers();">Show Answers</button>
    		<button onclick="clearAll();">Clear All</button>
        </td>
    </tr>
  <?php

writeColumnHeadings();

$fieldCount = 0;
// iteration by section (adjs), then row name, then column name
for ($section = 0; $section < sizeof($tenses); $section++) {
	

	
	$class = "header-overlap";
	echo "<tr><td class=\"$class\"></td><td colspan=\"8\" class=\"section-heading\">$headings[$section]</td></tr>\n";
	for ($row = 0; $row < sizeof($persons); $row++) {
		echo "<tr>";
		for ($col = 0; $col < sizeof($columns) + 1; $col++) {
			$style = "";
			
			// TODO: replace these numeric constants
					
			if ($col % 2 == 1) { // all odd-numbered columns start a section in this case
				$style .= "padding-left: .4em; ";
			}
			
			if ($col % 2 == 0 && $col != 0) {
				$style .= "padding-right: .4em; ";
			}
			
			switch ($row) {
				case 0:
					$style .= "padding-top: .4em; ";
					break;
				case 2:
					$style .= "padding-bottom: .4em; ";
					break;
			}
			
			if (strlen($style) > 0) $style = "style=\"" . $style . "\" ";
			
			echo "<td class=\"" . ($col == 0 ? "row-label" : "entry-" . ($col < 5 ? "1" : "2")) . "\"" . $style . ">\n";
			if ($col == 0) {
				echo $case_labels[$row];
			} else {
				if ($section % 3 != 2 || (($col - 1) / 2) % 2 != 1) { // don't write fields for subjunctive future tenses
					// ugly math to map tab indices
					$offset = $section * 24 - (int)($section / 3) * 12;
					$base = $fieldCount - $offset;
					
					$subrow = (int)($base / ($section % 3 == 2 ? 4 : 8));

					$effectiveCol = $section % 3 == 2 && $col > 4 ? $col - 2 : $col; // compensate for missing columns in some rows
					$tabindex = $offset + $subrow + ($effectiveCol - 1) * 3 + 1;
					
					// TODO: maybe remove parent loop and just check if the current field exists in the cells collection?
					writeField($columns[$col - 1] . "_$tenses[$section]_$persons[$row]", $tabindex);
					$fieldCount++;
				} 
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
</html><?php

$page = ob_get_contents();
ob_end_flush();
$fp = fopen("latin_cache/$verb.html","w");
fwrite($fp,$page);
fclose($fp);

?>