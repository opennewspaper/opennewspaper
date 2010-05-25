/* 
Derived from a script by Alejandro Gervasio. 
Modified to work in FireFox by Stefan Mischook for Killersites.com

How it works: just apply the CSS class of 'column' to your pages' main columns.
*/
matchColumns=function(){ 

     var divs,contDivs,maxHeight,divHeight,d; 
	
     // get all <div> elements in the document 

     divs=document.getElementsByTagName('div'); 

     contDivs=[]; 

     // initialize maximum height value 

     maxHeight=0; 

     // iterate over all <div> elements in the document 

     for(var i=0;i<divs.length;i++){ 

          // make collection with <div> elements with class attribute 'container' 

          if(/\bcolumn\b/.test(divs[i].className)){ 

                d=divs[i]; 

                contDivs[contDivs.length]=d; 

                // determine height for <div> element 

                if(d.offsetHeight){ 

                     divHeight=d.offsetHeight; 					

                } 

                else if(d.style.pixelHeight){ 

                     divHeight=d.style.pixelHeight;					 

                } 

                // calculate maximum height 

                maxHeight=Math.max(maxHeight,divHeight); 

          } 

     } 

     // assign maximum height value to all of container <div> elements 

     for(var i=0;i<contDivs.length;i++){ 

          contDivs[i].style.height=maxHeight + "px"; 

     } 

} 

// Runs the script when page loads 

window.onload=function(){ 

     if(document.getElementsByTagName){ 

          matchColumns();			 

     } 

} 


