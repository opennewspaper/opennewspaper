/**
 *
 * @param className Extra class
 * @param uid       uid of concrete Extra in given Extra class
 * @param close     1 to close popup window
 * @return
 */
function choseRecord(className, uid, close) {
	if (close == 1) {
		close = true;
	} else {
		close = false;
	}

	// get target element to receive the reocrd
	var form_table = extractQuerystringDirect('tx_newspaper_mod1[table]');
	var form_field = extractQuerystringDirect('tx_newspaper_mod1[field]');
	var form_uid = extractQuerystringDirect('tx_newspaper_mod1[uid]');

// \todo: replace "New entry" with correct record title
	insertElement(className, uid, 'db', '[New entry]', '', '', '../typo3conf/ext/newspaper/res/icons/icon_tx_newspaper_extra.gif', '', close, form_table, form_field, form_uid);
}





// for article select boxes rendered by typo3

var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"



/// extracted from typo3/class.browse_links.php
/// in order to get the extra browser working like the typo3 EB

var BrowseLinks = {
elements: {},
addElements: function(elements) {
	BrowseLinks.elements = $H(BrowseLinks.elements).merge(elements).toObject();
},
focusOpenerAndClose: function(close) {
	if (close) {
		parent.window.opener.focus();
		parent.close();
	}
}
}


function $H(object) {
return new Hash(object);
};

//new params: form_table, form_field, form_uid
function insertElement(table, uid, type, filename, fp, filetype, imagefile, action, close, form_table, form_field, form_uid) {
var performAction = true;
// Call performing function and finish this action:
if (performAction) {
addElement(filename, table+"_"+uid, fp, close, table, uid, form_table, form_field, form_uid);
}
}

//new params: table, uid, form_table, form_field, form_uid
function addElement(elName, elValue, altElValue, close, table, uid, form_table, form_field, form_uid) {
//alert("data[" + table + "][" + uid + "][" + form_field + "]");
//alert("data[" + form_table + "][" + form_uid + "][" + form_field + "]");
if (parent.window.opener && parent.window.opener.setFormValueFromBrowseWin) {
parent.window.opener.setFormValueFromBrowseWin("data[" + form_table + "][" + form_uid + "][" + form_field + "]", altElValue? altElValue : elValue, elName);
focusOpenerAndClose(close);
} else {
alert("Error - reference to main window is not set properly!");
parent.close();
}
}

function focusOpenerAndClose(close)	{
BrowseLinks.focusOpenerAndClose(close);
}

