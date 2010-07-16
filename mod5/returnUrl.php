<html>
<head>
<script>
	// extract module number to redirect to
	var mod_num = parseInt(unescape(window.location.search).replace('?', ''));
	if (!mod_num) {
		mod_num = 5; // default
	}

	var mod = 'txnewspaperMmain_txnewspaperM' + mod_num;
	top.goToModule(mod);
</script>
</head>
<body></body>
</html>