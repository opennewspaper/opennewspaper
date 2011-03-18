
// Fix publish date for published articles without a publish date set
function fixPublishDate() {

	var request = new Ajax.Request(
		'../typo3conf/ext/newspaper/mod1/index.php',
		{
			method: 'get',
			parameters: 'tx_newspaper_mod1[ajaxController]=fixPubDate',
			onCreate: function() {
				$("pubDateSpinner").innerHTML = '<img src="../typo3conf/ext/newspaper/res/be/css/move-spinner.gif" />';
			},
			onSuccess: function(data) {
				$("pubDateSpinner").innerHTML = data.responseText;

			}
		}
	);

}