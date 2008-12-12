
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
//TODO: use 80% of width and height
var width = viewportwidth; // viewport measures content frame, form fit content frame, so use full width
var height = viewportheight - 50;
top.showPopWin(path + "typo3/alt_doc.php?returnUrl=" + path + "typo3conf/ext/newspaper/res/close_extra_modalbox.html" + param + "&" + json.extra_param, width, height, null, true)
//TODO: set title for modal window

}
