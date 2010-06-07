
var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


// source: http://andylangton.co.uk/articles/javascript/get-viewport-size-javascript/
var viewportwidth;
var viewportheight;
if (typeof document.innerWidth != 'undefined') {
  // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
  viewportwidth = document.innerWidth,
  viewportheight = document.innerHeight
} else if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0) {
  // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
  viewportwidth = document.documentElement.clientWidth,
  viewportheight = document.documentElement.clientHeight
} else {
  // older versions of IE
  viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
  viewportheight = document.getElementsByTagName('body')[0].clientHeight
}




// AJAX STUFF //

function getExtra(extra, extra_uid, content, content_uid) {
// TODO path is currently set constantly
  var request = new Ajax.Request(
    path + "typo3conf/ext/newspaper/mod1/index.php",
    {
      method: 'get',
      parameters: "extra_modalbox&param=" + extra + "|" + extra_uid + "|" + content + "|" + content_uid + "&no_cache=" + new Date().getTime(),
      onSuccess: updatePageJsonModalbox
    }
  );
}

function updatePageJsonModalbox(request) {
  var json = request.responseText.evalJSON(true);
  var param = escape("?param=" + json.extra_close_param);

  // submodal
  var width = viewportwidth; // viewport measures content frame, form fit content frame, so use full width
  var height = viewportheight - 50;
  top.showPopWin(path + "typo3/alt_doc.php?returnUrl=" + path + "typo3conf/ext/newspaper/res/close_extra_modalbox.html" + param + "&" + json.extra_param, width, height, null, true)
  //TODO: set title for modal window
}



function toggleExtraVisibility(extra, extra_uid, content, content_uid, img_id) {
// TODO path is currently set constantly
  var request = new Ajax.Request(
    path + "typo3conf/ext/newspaper/mod1/index.php",
    {
      method: 'get',
      parameters: "extra_toggle_visibility&param=" + extra + "|" + extra_uid + "|" + content + "|" + content_uid + "|" + img_id + "&no_cache=" + new Date().getTime(),
      onSuccess: updatePageJsonVisibility
    }
  );
}

function updatePageJsonVisibility(request) {
  var json = request.responseText.evalJSON(true);
//TODO: working in ff, what about the other browsers???
  document.getElementById(json.id).src = json.img_src;
}



function deleteExtra(extra, extra_uid, content, content_uid, confirmed) {

	var confirm_cancelled = false;
	if (!confirmed) {
//TODO: LL
		check = confirm("Delete Extra? Can't be undone!");
		if (check == false) 
			confirm_cancelled = true;
		else
			confirmed = true;
	}
	
	if (!confirm_cancelled && confirmed) {
      var request = new Ajax.Request(
        path + "typo3conf/ext/newspaper/mod1/index.php",
        {
          method: 'get',
          parameters: "extra_delete&param=" + extra + "|" + extra_uid + "|" + content + "|" + content_uid + "&no_cache=" + new Date().getTime(),
          onSuccess: updatePageJsonDelete
        }
      );

	}
}

function updatePageJsonDelete(request) {
  var json = request.responseText.evalJSON(true);
//TODO: working in ff, what about the other browsers???
  $(json.id).remove();
}

