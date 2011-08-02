<html>
<head>
<script src="../../res/be/newspaper.js" type="text/javascript"></script>
<script>
	// extract module number to redirect to
	var mod_num = NpTools.extractQuerystringDirect("tx_newspaper_mod5[calling_module]", true);
	if (!mod_num) {
		mod_num = 5; // default module (dash board)
	}

	var mod = 'txnewspaperMmain_txnewspaperM' + mod_num;
	top.goToModule(mod);
</script>
</head>
<body></body>
</html>