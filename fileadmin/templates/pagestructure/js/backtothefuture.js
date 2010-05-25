/******* nothing above this line *******/ try{ filOtaz_de; } catch(e){ //don't interfere!

/***** my own namespace *****/
filOtaz_de = new function() {
	my=this;

/***** wrappers for cross browser functionality *****/
this.xbrowser = new function() {
	xb=this;

  // xbrowser stuff
  var Engine = function(k,n) {
    this.key=k; this.name=n;
    this.score=0; act=false;
    this.ver={};
    };
  var engines = new function() {
    this.gecko	= new Engine( 'gecko' ,'Mozilla Gecko'     );
    this.ie		= new Engine( 'ie'    ,'Internet Explorer' );
    this.webkit	= new Engine( 'webkit','Apple Safari'      );
    this.opera	= new Engine( 'opera' ,'Opera'             );
    };
  this.is=null;
  {
    var el=document;
    if( el.addEventListener ) { engines.gecko.score +=1; }
    if( el.attachEvent )      { engines.ie.score    +=1; }
    var i,e, s0=0, max=null; for( i in engines ){ e=engines[i]; if( e.score > s0 ){ max=e; s0=e.score;} } // see who wins
    engines[max.key].act=true; this.is=max;	// tag winner
    } 


  // xbrowser properties

  this.page = new function() {
    this.url   = encodeURIComponent( window.location );
	this.title = encodeURIComponent( document.title  );
  };//xb.page


  // xbrowser functions
  
	this.addHandler = function( el, evtype, handler, flag ) {
	  if( el.addEventListener )   { el.addEventListener( evtype, handler, flag ); } // W3C event registration model, supported by gecko
	  else if( el.attachEvent ) { // try to cope w/ MSs model, see http://quirksmode.org/js/events_advanced.html#link6
		var hashname = 'handler'+Math.floor(Math.random()*1000000000000);
		el[hashname] = handler; // put handler into element to set "this"
        var iehandler = function() { el[hashname](window.event); };
        el.attachEvent( 'on'+evtype, iehandler ); 
	    }
      else { el['on'+evtype] = handler; } // legacy DOM 0
    };

	
	// add browser bookmark (inspired by http://www.hostingfanatic.com/webdev/javascript/bookmark-script.html)
	this.addBookmark = function() {
	  if( window.external && window.external.AddFavorite )
	    window.external.AddFavorite( 'http://www.taz.de/', 'TAZ' );
	    //window.external.AddFavorite( window.location.href, document.title );
	  else if( window.sidebar && window.sidebar.addPanel )
	    window.sidebar.addPanel( document.title, location.href,'' );
	};


	// cookie handling (inspired by http://www.quirksmode.org/js/cookies.html#doccookie)
	this.cookie = new function() {
		
		// config defaults
		this.domain = null;
		this.path	= null;
		this.seconds = null;
		this.minutes = null;
		this.hours   = null;
		this.days    = null;
		this.weeks   = null;
		this.months  = null;
		this.years   = null;
		
		var keepalive;
		this.init =function() {
			this.years   && ( keepalive = this.years *365*24*3600 );
			this.months  && ( keepalive = this.months *30*24*3600 );
			this.weeks   && ( keepalive = this.weeks   *7*24*3600 );
			this.days    && ( keepalive = this.days      *24*3600 );
			this.days    && ( keepalive = this.hours        *3600 );
			this.minutes && ( keepalive = this.minutes        *60 );
			this.seconds && ( keepalive = this.seconds );
		}
		
		this.create = function(name,value) {
			var mag = keepalive   ? ( "; max-age=" + keepalive   ) : "";
			var dom = this.domain ? ( "; domain="  + this.domain ) : "";
			var pat = "; path=" + ( this.path || "/" ); 
			document.cookie = name +"="+ value + mag + dom + pat;
		}

		this.read = function(name) {
			var nameEQ = name + "=";
			var i,c; var ca = document.cookie.split(';');
			for( i=0; i<ca.length; i++ ) {
				c = ca[i];
				while( c.charAt(0)==' ' ) c = c.substring(1,c.length);
				if( c.indexOf(nameEQ) == 0 ) return c.substring(nameEQ.length,c.length);
			}
			return null;
		}
		
		this.erase = function(name) {
			var k=keepalive; keepalive=0;
			this.create(name,"");
			keepalive=k;
		}

	};//cookie
	
};//filOtaz_de.xbrowser


/***** taz.de GUI behaviour *****/
this.taz_de = new function() {
	var taz=this;
	var xbrowser=my.xbrowser;
  
this.mainNaviSuchfeld = new function() {

  this.id = null;
  this.initialStyleClass = null;

  this.init = function() {
    if( !this.id || !this.initialStyleClass ) return 1;
    var el = document.getElementById(this.id);

    el.xmlClassName = el.className;
    el.className = el.xmlClassName+' '+this.initialStyleClass;

    var onfocus = function() {
      this.className = this.xmlClassName;
      this.value = '';
      };
    xbrowser.addHandler( el, 'focus', onfocus, false );

  };//init()

};//...mainNaviSuchfeld

this.socialBookmarks = function() {
  var menu = this;
  
  this.buttonId = null;
  this.menuId   = null;
  var menuNode,buttonNode;

  var shown=false;
  this.pop  = function() { shown=true;  menuNode.style.display = 'block'; }
  this.hide = function() { shown=false; menuNode.style.display = 'none';  }
  this.toggle = function() { if(shown) this.hide(); else this.pop(); }

  this.init = function() {
    
    if( !this.menuId || !this.buttonId ) return 1;
    menuNode   = document.getElementById( this.menuId   );
    buttonNode = document.getElementById( this.buttonId );

	buttonNode.style.cursor = 'pointer';

    xbrowser.addHandler( buttonNode, 'focus', function(){ this.blur(); }, false );
    xbrowser.addHandler( buttonNode, 'click', function(){ menu.toggle(); }, false );
    xbrowser.addHandler( menuNode, 'click', function(){ menu.hide(); }, false );

  };//init()

};//...socialBookmarks


};//filOtaz_de.taz_de

};//filOtaz_de

}

// to be shifted to html
/*
tm=filOtaz_de.taz_de.timeMachine
tm.domain = 'taz.de';	// cookie domain  
tm.path   = '/';		// cookie path
tm.h_tolive = 6;		// cookie keepalive, hours 
tm.url_prefix='/tm/';	// where is the archive?
tm.tazrot='#C61940';
tm.init();
tm=null;
*/


/*
function init() {
}
filOtaz_de.xbrowser.addHandler( document.body, 'load', init, false );
*/


/*
// cookie handling (inspired by http://www.quirksmode.org/js/cookies.html#doccookie)
function createCookie(name,value,max-hours,domain,path) {
//	if (days) {
//		var date = new Date();
//		date.setTime(date.getTime()+(days*24*60*60*1000));
//		var expires = "; expires="+date.toGMTString();
//	}
//	else var expires = "";
//
	var mag = max-hours ? ( "; max-age=" + 3600*max-hours ) : "";
	var dom = domain    ? ( "; domain="  + domain         ) : "";
	var pat = "; path=" + ( path || "/" ); 
	document.cookie = name +"="+ value + mag + dom + pat;
}

function readCookie(name) {
	var nameEQ = name + "=";
	var i,c; var ca = document.cookie.split(';');
	for( i=0; i<ca.length; i++ ) {
		c = ca[i];
		while( c.charAt(0)==' ' ) c = c.substring(1,c.length);
		if( c.indexOf(nameEQ) == 0 ) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",0);
}
*/






