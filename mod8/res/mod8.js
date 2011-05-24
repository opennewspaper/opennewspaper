
// Radio button control tag checked => read control tag categories
function selectCtrlTagCat() {
	setCtrlTagCatDropdownVisibility(1);
	fetchTags();
}


// Radio button content tag checked, read content tag list
function selectContentTag() {
	setCtrlTagCatDropdownVisibility(0);
	fetchTags();
}


// A control tag categories was selected
function changeCtrlTagCat() {
	fetchTags();
}


function fetchTags() {

	if ($("tagTypeCtrl").checked) {
		// control tag
		var param = "&tx_newspaper_mod8[tagType]=ctrl";
		param += "&tx_newspaper_mod8[ctrlTagCat]=" + $("ctrltagcat").value;
		$("spinnerCtrlTag").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
	} else {
		// content tag
		var param = "&tx_newspaper_mod8[tagType]=content";
		$("spinnerContentTag").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
	}

	var request = new Ajax.Request(
		"index.php", {
			method: 'get',
			parameters: 'tx_newspaper_mod8[ajaxController]=changeTagCat' + param,
			onCreate: function() {
				clearOptionsInTarget();
			},
			onSuccess: function(data) {
				if (data) {
					options = data.responseText.evalJSON();
					addOptionsToTarget(options); // pass option to current sub form
					$("spinnerCtrlTag").innerHTML = '';
					$("spinnerContentTag").innerHTML = '';
				}
			}
		}
	);
}


// show or hide control tag categorie dropdown
function setCtrlTagCatDropdownVisibility(state) {
	if (state) {
		$("ctrlTagCat").show();
	} else {
		$("ctrlTagCat").hide();
	}
}
