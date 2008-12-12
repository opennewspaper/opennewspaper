
// check if handle is still unused
if (window.onunload) {
	alert("onunload handle in use. Please inform  developer of extension 'newspaper'.");
}

// add function
window.onunload = postProcess;

// TODO: check if opened iframes need to be saved before closing this form
function postProcess() {
//	var iframe = $$('iframe.extra_iframe'); // get a list of all open extra iframes
}


var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


// AJAX STUFF //

function getExtra(extra, extra_uid, content, content_uid) {
// TODO path is currently set constantly
  var request = new Ajax.Request(
    path + "typo3conf/ext/newspaper/mod1/index.php",
    {
      method: 'get',
      parameters: "extra_iframe&param=" + extra + "|" + extra_uid + "|" + content + "|" + content_uid + "&no_cache=" + new Date().getTime(),
      onSuccess: updatePageJsonIframe
    }
  );
}

function updatePageJsonIframe(request) {
  var json = request.responseText.evalJSON(true);
  var param = escape("?param=" + json.extra_close_param);

//TODO colspan 7 -> make configurable
  $(json.id).update("<td colspan=\"7\"><iframe class=\"extra_iframe\" width=\"600\" height=\"549\" src=\"" + path + "typo3/alt_doc.php?returnUrl=" + path + "typo3conf/ext/newspaper/res/close_extra.html" + param + "&" + json.extra_param + "\"></iframe></td>");
}

