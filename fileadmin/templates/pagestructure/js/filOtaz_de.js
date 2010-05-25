/******* nothing above this line *******/ try{ filOtaz_de; } catch(e){ //don't interfere!

/***** my own namespace *****/
filOtaz_de = new function() {
	my=this;

/***** wrappers for cross browser functionality *****/
this.xbrowser = new function() {
	xb=this;

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
    var el=document.getElementsByTagName('body')[0];
    if( el.addEventListener ) { engines.gecko.score +=1; }
    if( el.attachEvent )      { engines.ie.score    +=1; }
    var i,e, s0=0, max=null; for( i in engines ){ e=engines[i]; if( e.score > s0 ){ max=e; s0=e.score;} } // see who wins
    engines[max.key].act=true; this.is=max;	// tag winner
    } 

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

};//mainNaviSuchfeld

};//filOtaz_de.taz_de

};//filOtaz_de

/******* nothing beyond this line *******/ }//catch