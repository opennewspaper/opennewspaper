
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

	var id1 = window.location.search.toQueryParams(); // read querystring from iframe
	var id2 = id1.returnUrl.toQueryParams(); // read returnUrl param from querystring from iframe

//TODO: change name in url to [tx_newspaper_...][uid][tt_content][uid], it'll be easier here ... (no error checking will be needed here then)
	var iframe_field_name = id2.param.substring(0, id2.param.indexOf("]") + 1);
	p = iframe_field_name.indexOf("[");
	iframe_field = 'input[name="data[' + iframe_field_name.substring(0, p) + ']' + iframe_field_name.substring(p);

	iframe_title = iframe_field + '[title]_hr"]';
	iframe_visibility = iframe_field + '[hidden]_0"]';

	var title = $$(iframe_title)[0].value;
	parent.$("title_" + id2.param).update(title);

//TODO check if this is working with ie, opera and safari
	var visible = $$(iframe_visibility)[0].checked;
//TODO title getLLL???
//TODO T3 skinning???
	if (visible) {
		parent.$("visibility_" + id2.param).update('<img src="sysext/t3skin/icons/gfx/button_unhide.gif" width="16" height="16" title="..." alt="" />');
	} else {
		parent.$("visibility_" + id2.param).update('<img src="sysext/t3skin/icons/gfx/button_hide.gif" width="16" height="16" title="..." alt="" />');
	}

	return false;
}

