// Funktion fuer Zustand-Wechseln der Aufklapp-Boxen, hardcoded

function aufklapp_zustand_wechseln(id){
	pfeilname = "pfeil_"+id;
	shortversion = id.split("_");
	shortversion = shortversion[0]+"_short";	
	if (document.getElementById(id).style.display != 'block'){
		document.getElementById(id).style.display = 'block';
		document.getElementById(shortversion).style.display = 'none';
		document.getElementById(pfeilname).src = 'fileadmin/templates/neu/images/pfeil_oben.gif';
	}
	else{
		document.getElementById(id).style.display = 'none';
		document.getElementById(shortversion).style.display = 'block';
		document.getElementById(pfeilname).src = 'fileadmin/templates/neu/images/pfeil_unten.gif';
	}
}
	