function extract_querystring(querystring, param) {
	querystring = unescape(querystring);
	if (querystring.substring(0, 1) == '?' || querystring.substring(0, 1) == '&') {
		querystring = querystring.substring(1);
	}
	var p = querystring.split('&'); // split querystring
	for (var i = 0; i < p.length; i++) {
		var pos = p[i].indexOf('=');
		if (pos > 0) {
			if (p[i].substring(0, pos) == param) {
				return p[i].substring(pos + 1); // return value of param
			}
		}
	}
	return ''; // no hit found
}