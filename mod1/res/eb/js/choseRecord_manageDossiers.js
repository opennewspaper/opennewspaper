/**
 *
 * @param className Extra class
 * @param uid       uid of concrete Extra in given Extra class
 * @param close     1 to close popup window
 * @param indexphp  Path to mod6/index.php, defaults to "../mod6/index.php"
 * @return void
 */
function choseRecord(className, uid, close, indexphp) {
// \todo : on error
	if (close == 1) {
		close = true;
	} else {
		close = false;
	}

	if (typeof(indexphp) == 'undefined') {
		indexphp = "../mod6/index.php";
	}

	var tz_uid = opener.NpManageDossier.getTagzoneUid();
	var request = new Ajax.Request(
		indexphp, {
			method: 'get',
			parameters: 'tx_newspaper_mod6[ajaxController]=manageDossiers&tx_newspaper_mod6[className]=' + className + '&tx_newspaper_mod6[uid]=' + uid + '&tx_newspaper_mod6[tag_uid]=' + opener.document.getElementById("tx_newspaper_mod6[tag]").value + '&tx_newspaper_mod6[tz_uid]=' + tz_uid,
			onCreate: function() {
				opener.document.getElementById("tz").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
			},
			onSuccess: function() {
				opener.NpManageDossier.changeTag(opener.document.getElementById("tx_newspaper_mod6[tag]").value);
				if (close) {
					closeElementBrowser();
				}
			}
		}
	);

}