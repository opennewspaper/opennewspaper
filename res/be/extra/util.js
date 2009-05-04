


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


