
// hide path and pic icon (doesn't make sense to be displayed in modalbox/iframe forms)
$$('div.docheader-row2-right')[0].hide();


// check if handle is still unused
if (window.onunload) {
	alert("onunload handle in use. Please inform  developer of extension 'extra'.");
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

	title = field + '[title]_hr"]';
	visibility = field + '[hidden]_0"]';

//alert(document.getElementById("title_" + id2.param)); return false;
	parent.$("title_" + id2.param).update(title);

//TODO check if this is working with ie, opera and safari
	var visible = $$(iframe_visibility)[0].checked;
//TODO title getLLL???
//TODO T3 skinning???
	if (visible) {
		document.$("visibility_" + id2.param).update('<img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="..." alt="" />');
	} else {
		document.$("visibility_" + id2.param).update('<img src="sysext/t3skin/icons/gfx/button_hide.gif" width="16" height="16" title="..." alt="" />');
	}

	return false;
}

