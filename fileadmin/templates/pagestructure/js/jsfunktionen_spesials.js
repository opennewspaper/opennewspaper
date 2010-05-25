// Funktion fuer Zustand-Wechseln der Aufklapp-Boxen, hardcoded

function aufklapp_zustand_wechseln(id, title){
	pfeilname = "pfeil_"+id;
	kurzversion = id+"_teaser";

	// IVW Counter gif
	var IVW="http://taz.ivwbox.de/cgi-bin/ivw/CP/ausklappbox_"+title;
	ivwzaehlgif = "ivwzaehlgif_"+id;

	// taz counter gif
	rand = (""+Math.random());
	len = rand.length;
	append = rand.substr(len-10,10);
	tazzaehlgif = "tazzaehlgif_"+id;

	if (document.getElementById(id).style.display != 'block'){
		document.getElementById(kurzversion).style.display = 'none';
		document.getElementById(id).style.display = 'block';
		document.getElementById(pfeilname).src = 'fileadmin/templates/neu/images/pfeil_oben.gif';
		document.getElementById(pfeilname).alt = 'Zuklappen';
		document.getElementById(pfeilname).title = 'Zuklappen';
		document.getElementById(ivwzaehlgif).src = IVW+'?r='+escape(document.referrer)+'&amp;d='+(Math.random()*100000);
		document.getElementById(tazzaehlgif).src = '/digitaz/cntres/ausklappbox_'+title+'/ecnt/'+rand+'.taz/countergif';
	}
	else{
		document.getElementById(kurzversion).style.display = 'block';
		document.getElementById(id).style.display = 'none';
		document.getElementById(pfeilname).src = 'fileadmin/templates/neu/images/pfeil_unten.gif';
		document.getElementById(pfeilname).alt = 'Aussklappen';
		document.getElementById(pfeilname).title = 'Aussklappen';
		document.getElementById(ivwzaehlgif).src = 'fileadmin/templates/neu/images/leer.gif';
		document.getElementById(tazzaehlgif).src = 'fileadmin/templates/neu/images/leer.gif';
	}
}
	
// Funktion fuer Zustand-Weschseln der Tabboxen, hardcoded

function tabbox_wechseln(id){
	if (id == 'tabbox_left'){
		document.getElementById('tabbox_right').style.display = 'block';
		document.getElementById('tabbox_left').style.display = 'none';
		document.getElementById('tabbox_right_header').style.backgroundColor = '#efede4';
		document.getElementById('tabbox_left_header').style.backgroundColor = '#dfdbc9';
	}
	if (id == 'tabbox_right'){
		document.getElementById('tabbox_left').style.display = 'block';
		document.getElementById('tabbox_right').style.display = 'none';
		document.getElementById('tabbox_left_header').style.backgroundColor = '#efede4';
		document.getElementById('tabbox_right_header').style.backgroundColor = '#dfdbc9';
	}
}

// Funktionen fuer Schriftvergroesserung, hardcoded

//Intervalle fuer Vergroesserung / Verkleinerung in 2px-Schritten
//Tags muessen leider manuell und absolut festgelegt werden (p-Klassen,Boxen-Header,h1,h2,etc.)...

var min=13;
var max=17;
var heins_min=22;
var heins_max=26;
var hzwei_min=11;
var hzwei_max=15;


//Hilfsfunktion, um Elemente nach Klassen statt nach Tags zu selektieren
function getElementsByClass(searchClass,node,tag) {
  var classElements = new Array();
  if (node == null)
  	//Beschraenkung auf die linke Spalte
    node = document.getElementById('artikel');
  if (tag == null)
    tag = '*';
  var els = node.getElementsByTagName(tag);
  var elsLen = els.length;
  var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
  for (i = 0, j = 0; i < elsLen; i++) {
    if (pattern.test(els[i].className) ) {
      classElements[j] = els[i];
      j++;
    }
  }
  return classElements;
}

function increaseFontSize() {
	/*
   var p = document.getElementById('artikel').getElementsByTagName('p');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 13;
      }
      if(s!=max) {
         s += 2;
      }
      p[i].style.fontSize = s+"px";
	  p[i].style.lineHeight = (s+3)+"px";
   }
	 */
   var heins = document.getElementById('artikel').getElementsByTagName('h1');
   for(i=0;i<heins.length;i++) {
      if(heins[i].style.fontSize) {
         var sheins = parseInt(heins[i].style.fontSize.replace("px",""));
      } else {
         var sheins = 22;
      }
      if(sheins!=heins_max) {
         sheins += 2;
      }
      heins[i].style.fontSize = sheins+"px"
   }
   var hzwei = document.getElementById('artikel').getElementsByTagName('h2');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
   //h6 entspricht von der Fontgroesse her h2
   var hzwei = document.getElementById('artikel').getElementsByTagName('h6');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
   var hzwei = document.getElementById('artikel').getElementsByTagName('h5');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 13;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = document.getElementById('artikel').getElementsByTagName('h4');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('smallblackfont');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var p = getElementsByClass('artikelintro');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 13;
      }
      if(s!=max) {
         s += 2;
      }
      p[i].style.fontSize = s+"px";
	  p[i].style.lineHeight = (s+3)+"px";
   }
	 	 var p = getElementsByClass('artikeltext');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 13;
      }
      if(s!=max) {
         s += 2;
      }
      p[i].style.fontSize = s+"px";
	  p[i].style.lineHeight = (s+3)+"px";
   }
	 var hzwei = getElementsByClass('bildunterschrift');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('bildunterschrift_rechts');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('ressortlink_title');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('ressortlink_text');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_max) {
         shzwei += 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
   if (s==max){
		document.getElementById("schrift_groesser").src = "fileadmin/templates/neu/images/plus_grau.gif";
		
	   }
	else
		document.getElementById("schrift_groesser").src = "fileadmin/templates/neu/images/plus.gif";
		document.getElementById("schrift_kleiner").src = "fileadmin/templates/neu/images/minus.gif";
}
function decreaseFontSize() {
	/*
   var p = document.getElementById('artikel').getElementsByTagName('p');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 13;
      }
      if(s!=min) {
         s -= 2;
      }
      p[i].style.fontSize = s+"px";
	  p[i].style.lineHeight = (s+3)+"px";
   }   
	 */
   var heins = document.getElementById('artikel').getElementsByTagName('h1');
   for(i=0;i<heins.length;i++) {
      if(heins[i].style.fontSize) {
         var sheins = parseInt(heins[i].style.fontSize.replace("px",""));
      } else {
         var sheins = 22;
      }
      if(sheins!=heins_min) {
         sheins -= 2;
      }
      heins[i].style.fontSize = sheins+"px"
   }
   var hzwei = document.getElementById('artikel').getElementsByTagName('h2');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
   //h6 entspricht von der Fontgroesse her h2
   var hzwei = document.getElementById('artikel').getElementsByTagName('h6');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
   var hzwei = document.getElementById('artikel').getElementsByTagName('h5');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 13;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = document.getElementById('artikel').getElementsByTagName('h4');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var p = getElementsByClass('artikelintro');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 13;
      }
      if(s!=min) {
         s -= 2;
      }
      p[i].style.fontSize = s+"px";
	  p[i].style.lineHeight = (s+3)+"px";
   }
	 var p = getElementsByClass('artikeltext');
   for(i=0;i<p.length;i++) {
      if(p[i].style.fontSize) {
         var s = parseInt(p[i].style.fontSize.replace("px",""));
      } else {
         var s = 13;
      }
      if(s!=min) {
         s -= 2;
      }
      p[i].style.fontSize = s+"px";
	  p[i].style.lineHeight = (s+3)+"px";
   }
	 var hzwei = getElementsByClass('smallblackfont');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('bildunterschrift_rechts');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('bildunterschrift');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('ressortlink_text');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
	 var hzwei = getElementsByClass('ressortlink_title');
   for(i=0;i<hzwei.length;i++) {
      if(hzwei[i].style.fontSize) {
         var shzwei = parseInt(hzwei[i].style.fontSize.replace("px",""));
      } else {
         var shzwei = 11;
      }
      if(sheins!=heins_min) {
         shzwei -= 2;
      }
      hzwei[i].style.fontSize = shzwei+"px"
   }
   if (s==min){
		document.getElementById("schrift_kleiner").src = "fileadmin/templates/neu/images/minus_grau.gif";
		
	   }
	else
		document.getElementById("schrift_kleiner").src = "fileadmin/templates/neu/images/minus.gif";
		document.getElementById("schrift_groesser").src = "fileadmin/templates/neu/images/plus.gif";
}

function hilitesearch_on(){
	document.getElementById("searchform1").style.background = '#efede4';
	document.getElementById("searchform2").style.background = '#efede4';
	document.getElementById("searchform3").style.background = '#efede4';
}

function hilitesearch_off(){
	document.getElementById("searchform1").style.background = '#DCD8C4';
	document.getElementById("searchform2").style.background = '#DCD8C4';
	document.getElementById("searchform3").style.background = '#DCD8C4';
}

//lädt Zusatz-Mini-Stylesheet für Mac
if (navigator.userAgent.indexOf('Macintosh') > -1){ 
	var headID = document.getElementsByTagName("head")[0];         
	var cssNode = document.createElement('link');
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = 'css/mac.css';
	cssNode.media = 'screen';
	headID.appendChild(cssNode);

}

