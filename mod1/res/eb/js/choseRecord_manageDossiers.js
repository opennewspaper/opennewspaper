function choseRecord(className, uid, close) {
// \todo : on error
	if (close == 1) {
		close = true;
	} else {
		close = false;
	}

	var tz_uid = opener.getTagzoneUid();

	var request = new Ajax.Request(
		"../mod6/index.php",
		{
			method: 'get',
			parameters: 'tx_newspaper_mod6[ajaxController]=manageDossiers&tx_newspaper_mod6[className]=' + className + '&tx_newspaper_mod6[uid]=' + uid + '&tx_newspaper_mod6[tag_uid]=' + opener.document.getElementById("tx_newspaper_mod6[tag]").value + '&tx_newspaper_mod6[tz_uid]=' + tz_uid,
			onCreate: function() {
				opener.document.getElementById("tz").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
			},
			onSuccess: function() {
				opener.changeTag(opener.document.getElementById("tx_newspaper_mod6[tag]").value);
				if (close) {
					closeElementBrowser();
				}
			}
		}
	);

}