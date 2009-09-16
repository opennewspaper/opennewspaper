/// \todo: deprecated??? 


// FIXME: IE and Opera not running!!! 



//  TODO: naming modalbox|iframe var name: rename so modalbox/iframe version look more similar (identical???)



// hide path and pic icon (doesn't make sense to be displayed in modalbox/iframe forms)
$$('div.docheader-row2-right')[0].hide();


// check if handle is still unused
if (window.onunload) {
	alert("onunload handle in use. Please inform  developer of extension 'newspaper'.");
}

// add function
window.onunload = postProcess;

// modify title and hidden status in parent form
function postProcess() {

	var id1 = window.location.search.toQueryParams(); // read querystring from iframe (subModal)
	var id2 = id1.returnUrl.toQueryParams(); // read returnUrl param from querystring from iframe

	var field_name = id2.param.substring(0, id2.param.indexOf("]") + 1);
	p = field_name.indexOf("[");
	field = 'input[name="data[' + field_name.substring(0, p) + ']' + field_name.substring(p);

	titleField = field + '[title]_hr"]';
	visibilityField = field + '[hidden]_0"]';


	// search node for frame named "list_frame"
	var n = parent.$("content").contentDocument.getElementById("typo3-content-frameset").childNodes;
	var frameNode = null;
	for (var i = 0; i < n.length; i++) {
	  if (n[i].name == 'list_frame')
	  	frameNode = n[i];
	}
	if (frameNode == 'null') {
	  //TODO: some error handling maybe ...
	  return false;
	}

	// set title in form that called this modal box
	var titleValue = $$(titleField)[0].value;
	replaceText(frameNode.contentDocument.getElementById("title_" + id2.param), titleValue);


//TODO check if this is working with ie, opera and safari
	var hidden = $$(visibilityField)[0].checked;
console.log(hidden);

//TODO title getLLL???
//TODO T3 skinning???
	data = new array();
	if (hidden) {
		var visibilityValue = '<img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="..." alt="" />';
		data["src"] = "sysext/t3skin/icons/gfx/button_unhide.gif";
	} else {
		var visibilityValue = '<img src="sysext/t3skin/icons/gfx/button_hide.gif" width="16" height="16" title="..." alt="" />';
		data["src"] = "sysext/t3skin/icons/gfx/button_hide.gif";
	}
	//replaceText(frameNode.contentDocument.getElementById("visibility_" + id2.param), visibilityValue);
	replaceImg(frameNode.contentDocument.getElementById("visibility_" + id2.param), data);

	return false;
}


// TODO: move function to separate js file (so they can be used by other js scripts too!)

// DOM functions

function replaceImg(el, img_data) {
  if (el != null) {
	if (el.childNodes.length != 1) return false;

	var elTarget = el;
	if (el.firstChild.nodeName == "A") {
	  // image is wrapped in <a> tag, so go one level deeper in dom tree
	  elTarget = el.firstChild;
	}

	if (elTarget.childNodes.length != 1) return false;

	if (elTarget.childNodes[0].nodeName != "IMG") return false;
    elTarget.childNodes[0].src = img_data["src"];

  }
}

// taken from "Head Rush: AJAX (at least I think I got it there ...)
function replaceText(el, text) {
  if (el != null) {
    clearText(el);
    var newNode = document.createTextNode(text);
    el.appendChild(newNode);
  }
}
function clearText(el) {
  if (el != null) {
    if (el.childNodes) {
      for (var i = 0; i < el.childNodes.length; i++) {
        var childNode = el.childNodes[i];
        el.removeChild(childNode);
      }
    }
  }
}

