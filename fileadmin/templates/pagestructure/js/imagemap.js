
function newImage(arg) {
	if (document.images) {
		rslt = new Image();
		rslt.src = arg;
		return rslt;
	}
}

function changeImages() {
	if (document.images && (preloadFlag == true)) {
		for (var i=0; i<changeImages.arguments.length; i+=2) {
			document[changeImages.arguments[i]].src = changeImages.arguments[i+1];
		}
	}
}

var preloadFlag = false;
function preloadImages() {
	if (document.images) {
		afrika_01_Aegypten_over = newImage("/fileadmin/templates/neu/Bilder/afrika_01-Aegypten_over.gif");
		afrika_01_Marokko_over = newImage("/fileadmin/templates/neu/Bilder/afrika_01-Marokko_over.gif");
		preloadFlag = true;
	}
}