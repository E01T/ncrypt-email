(function(){
    
	"use strict";
	var EventUtil = (function(){
		 	if(typeof addEventListener !== "undefined"){
		 	return {
					addHandler: (function(){
							return function(element, type, handler, capture){
								element.addEventListener(type, handler, capture || false);
							};
					}()), // self executing function
					removeHandler: (function(){
							return function(element, type, handler, capture){
								element.removeEventListener(type, handler, capture || false);
							};
					}()), // self executing function
					getEvent: (function(){
			      			return function(event){
			      				return event;
			      			};
	    			}()),
					// self executing function
					getTarget: (function(){
							return function(event){
								return this.getEvent(event).target;
							};
					}()), // self executing function
					preventDefault: (function(){
							return function(event){
								this.getEvent(event).preventDefault();
							};
					}())
				}; // object returned
			}else if(typeof attachEvent !== "undefined"){
				return {
					addHandler: (function(){
							return function(element, type, handler){
								element.attachEvent("on" + type, handler);
							};
					}()), // self executing function
					removeHandler: (function(){
							return function(element, type, handler){
								element.detachEvent("on" + type, handler);
							};
					}()), // self executing function
					getEvent: (function(){
			      			return function(event){
			      				return event ? event : window.event;
			      			};
	    			}()), // self executing function
					getTarget: (function(){
							return function(event){ 
								return this.getEvent(event).srcElement;
							};
					}()), // self executing function
					preventDefault: (function(){
							return function(event){
								this.getEvent(event).returnValue = false;
							};
					}())
			}; // object returned
		}else { // for older browsers
			return {
				addHandler: (function(){
					return function(element, type, handler){
						element["on" + type] = handler;
					};
				}()),
				removeHandler: (function(){
					return function(element, type){
						element["on" + type] = null;
					};
				}())
			}; // object returned
		}
			
	}());
	/****************** END Event handler mechanism ******************/

	/****************** isEventSupported function *********************/	

	// A utility function to check if a specific event is supported by the browser
	// Property of the EventUtil object
	// Source: http://perfectionkills.com/detecting-event-support-without-browser-sniffing/
	EventUtil.isEventSupported = (function(){
	    var TAGNAMES = {
	      'select':'input','change':'input',
	      'submit':'form','reset':'form',
	      'error':'img','load':'img','abort':'img'
	    }
	    function isEventSupported(eventName) {
	      var el = document.createElement(TAGNAMES[eventName] || 'div');
	      eventName = 'on' + eventName;
	      var isSupported = (eventName in el);
	      if (!isSupported) {
	        el.setAttribute(eventName, 'return;');
	        isSupported = typeof el[eventName] == 'function';
	      }
	      el = null;
	      return isSupported;
	    }
	    return isEventSupported;
	}());

	/****************** END isEventSupported function ******************/

    // Check which events are supported by the browser
    var EVENT_TYPE = [];
	if (EventUtil.isEventSupported('mouseover') )
		EVENT_TYPE.push('mouseover');

    if (EventUtil.isEventSupported('touchstart'))
        EVENT_TYPE.push('touchstart');
    else if(EventUtil.isEventSupported('pointerdown'))
    	EVENT_TYPE.push('pointerdown');
    else
    	EVENT_TYPE.push('click');
        
    var event_type_length = EVENT_TYPE.length;
    ////////////////////////////////////////////////////////////////////////////////

	var xmlhttp;
	if (window.XMLHttpRequest)
  		xmlhttp=new XMLHttpRequest();
  	else 
  		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");


  	var links_array = [];
	var get_the_nums_array = [];
	var anchor_array = [];

	var url = MyScriptParams.ajaxURL;
	var fn_call_counter = 0;
	
	/**
	 * Get all the hyperlinks in a page and loop through them.
	 * if the match a regex /dcrypt.../ store them into the links_array
	 * and also store the number of the link for later usage. 
	 * @param  {event} standard JavaScript event object 
	 * @return {void} 
	 */
	function foo(event){
		"use strict";
	    
		anchor_array = document.getElementsByTagName('a');

			for(var k=0, i=0; k < anchor_array.length; k++){
				if( anchor_array[k].href.match(/dcrypt-email\.php\?crypt/) ){
					links_array[i] = anchor_array[k];
					get_the_nums_array[i] = k;
					links_array[i] = links_array[i].href.replace(/http:.*(crypt=)/i , "");
					i++;
				}
			} 
	}
	/************* END of foo function *************/

	EventUtil.addHandler(window, 'load', foo, false);
	EventUtil.addHandler(window, 'load', ajaxRequest, false);

	function ajaxRequest(event){
		"use strict";
	    
	    if(links_array.length === 0 ) return; // If there are no emails in the page exit the function
	    
		xmlhttp.open("POST", url , true);

		xmlhttp.onreadystatechange = function(){
			"use strict";
			var response_array, domain, j = 0, evt_count = 0;

	  		if (xmlhttp.readyState==4 && xmlhttp.status==200){
		    	
		    	// Here I am getting a string, which I'll eventually turned into an array
		    	response_array = xmlhttp.responseText;

		    	// if response is the string 0 probably the user is Logged-IN so the
		    	// ajax response did not happend see wp_ajax_nopriv_...
		    	if( response_array === "0" ){
		    		// console.log(response_array);
		    		return; // EXIT function
		    	}

		    	// if the string is the cookie message...
	    		if( response_array === MyScriptParams.cookieString ){
	    			while(fn_call_counter < 4) {
	    				fn_call_counter++;
	    				ajaxRequest(null);
						return; // EXIT function
	    			}
	    			
		    		while(j < get_the_nums_array.length){	
	                    for(; evt_count < event_type_length; evt_count++) {	    			
	                        EventUtil.addHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count], function(){
		    					alert(MyScriptParams.cookieString);
		    				},false);
	                    }
	                    evt_count = 0;
	    			j++;
	    			} // END while
	    			return; // EXIT function

	    		}else{
		    		// Turned the string into an array
					response_array = response_array.split(",");
		    		while(j < get_the_nums_array.length){

				    	// if the response string has subject...
				    	if( (response_array[j].indexOf("?") != -1) ){ 
				    		domain = response_array[j].substr(0, response_array[j].indexOf("?"));
				    		domain = domain.replace(/mailto:/g, "");
	                        for(; evt_count < event_type_length; evt_count++){
	    			    		if(anchor_array[get_the_nums_array[j]].innerHTML === MyScriptParams.linkedText){ 
	        			    		EventUtil.addHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count] ,(function(j,domain,evt_count){
	    				    			function said(evt){
	    				    				// console.log(evt);
	    				    				EventUtil.preventDefault(evt);
	    			    					anchor_array[get_the_nums_array[j]].href = response_array[j];
	    		    						anchor_array[get_the_nums_array[j]].innerHTML = domain;
	    			    					EventUtil.removeHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count], said ,false);
	    			    				}
	    			    				return said;
	    			    				}(j,domain,evt_count)),false);
	    				    	}else{
	    				    		EventUtil.addHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count],(function(j,domain,evt_count){
	    				    			function said(evt){
	    				    				// console.log(evt);
	    				    				EventUtil.preventDefault(evt);
	    			    					anchor_array[get_the_nums_array[j]].href = response_array[j];
	    			    					if( MyScriptParams.hovered )
	    										anchor_array[get_the_nums_array[j]].innerHTML = domain; // added now
	    			    					EventUtil.removeHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count], said ,false);
	    			    				}
	    			    				return said;
	    			    				}(j,domain,evt_count)),false);
	    			    		}
				    	    } // END for loop
	                        evt_count = 0;
			    		}else{ // the response string has NO subject...
			    			domain = response_array[j];
				    		domain = domain.replace(/mailto:/g, "");
	                        for(; evt_count < event_type_length; evt_count++){
	    				    	if(anchor_array[get_the_nums_array[j]].innerHTML === MyScriptParams.linkedText){
	    				    		EventUtil.addHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count],(function(j,domain,evt_count){
	    				    			function said(evt){
	    				    				// console.log(evt);
	    				    				EventUtil.preventDefault(evt);
	    			    					anchor_array[get_the_nums_array[j]].href = response_array[j];
	    			    					anchor_array[get_the_nums_array[j]].innerHTML = domain;
	    			    					EventUtil.removeHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count], said ,false);
	    			    				}
	    			    				return said;
	    			    				}(j,domain,evt_count)),false);
	    				    	}else{
	    				    		EventUtil.addHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count],(function(j,domain,evt_count){
	    				    			function said(evt){
	    				    				// console.log(evt);
	    				    				EventUtil.preventDefault(evt);
	    			    					anchor_array[get_the_nums_array[j]].href =  response_array[j];
	    			    					if( MyScriptParams.hovered )
	    										anchor_array[get_the_nums_array[j]].innerHTML = domain; // added now
	    			    					EventUtil.removeHandler(anchor_array[get_the_nums_array[j]], EVENT_TYPE[evt_count], said ,false);
	    			    				}
	    			    				return said;
	    			    				}(j,domain,evt_count)),false);
	    				    	}
	                        } // END for loop
	                        evt_count = 0;
				    	}
				    	anchor_array[get_the_nums_array[j]].target="_self";

				    	j++; // increment loop
			    	} // END while loop
		    		
		    	} // END else
		    } // END if (xmlhttp.readyState==4 && xmlhttp.status==200){
	  	} // END onreadystatechange function
		
		xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		// you POST and you send the data (decrypt_answer and links_array) with it
		// xmlhttp.send("decrypt_answer="+ MyScriptParams.answer + "&links_array="+ links_array);
		xmlhttp.send("action="+"e01t_ajax_decrypt_request"+"&no_of_houses="+MyScriptParams.no_of_houses+"&links_array="+links_array+"&post_code="+MyScriptParams.post_code);
	} // END ajaxRequest function

}());