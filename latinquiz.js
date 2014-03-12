function checkAnswer(field) {
	if (field.value == "") {
		markClear(field);
	} else {
		if (isCorrect(field.name)) {
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
	field.style.fontWeight = "normal";
}

function fillAnswers() {
	for (i = 0; i < fields.length; i++) {
		giveAnswer(document.getElementById(fields[i]));
	}
}

function giveAnswer(field) {
	if (field == null) return;
	field.style.fontWeight = isCorrect(field.name) ? "normal" : "bold";
	if (!isCorrect(field.name)) markSolved(field);
	field.value = answers[field.name];
	
}

function isCorrect(fieldName) {
	field = document.getElementById(fieldName);
	return field.value.toLowerCase() == answers[fieldName].toLowerCase();
}	

function lockField(field) {
	field.disabled = true;
	lockStatus[field.name] = true;
}

function markActive(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "lightyellow";
	field.style.color = "black";
}

function markClear(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "inherit";
	field.style.color = "inherit";
}

function markRight(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "green";
	field.style.color = "white";
	field.style.fontWeight = "normal";
}

function markSolved(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "red";
	field.style.color = "white";
	field.style.fontWeight = "bold";
}

function markWrong(field) {
	if (lockStatus[field.name]) return; // ignore if locked
	field.style.backgroundColor = "darkred";
	field.style.color = "white";
	field.style.fontWeight = "normal";
}

function unlockField(field) {
	field.disabled = false;
	lockStatus[field.name] = false;
}

function update(field) {
	checkAnswer(field);
}