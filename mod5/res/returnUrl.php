<html>
<head>
<script src="/taz426/typo3conf/ext/newspaper/res/be/newspaper.js" type="text/javascript"></script>
<script>
	// extract module number to redirect to
	var mod_num = extractQuerystringDirect("tx_newspaper_mod5[calling_module]", true);
	if (!mod_num) {
		mod_num = 5; // default module (dash board)
	}
	if (mod_num == 2) {
		// production list
//alert('filter ...');
//		var filter = extract_querystring(window.location.search, "tx_newspaper_mod5[mod2Filter]", false, true);
//alert('filter: ' + filter);
//		self.location.href = "../mod2/index.php?tx_newspaper_mod2[type]=filter&" + filter;
	}

	var mod = 'txnewspaperMmain_txnewspaperM' + mod_num;
	top.goToModule(mod);
</script>
</head>
<body></body>
</html>