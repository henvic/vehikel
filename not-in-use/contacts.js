var updateContactCallback =
{
  success: function(o){
		if(o.responseText !== undefined)
		{
			//alert(o.responseText);
			if(o.responseText !== '')
			{
				document.getElementById("contactList").innerHTML = o.responseText;
			}
		}
	},
  failure: function(o){ }
};

var delContactEvent = function(e) {
	
	var elTarget = YAHOO.util.Event.getTarget(e);
    while (elTarget.id != "container") {
    	 if(elTarget.nodeName.toLowerCase() == "a") {
    		 //is it the delete link? If so...
    		 if(elTarget.parentNode.nodeName.toLowerCase() == 'small')
    		 {
    			 YAHOO.util.Event.stopEvent(e);
    			 YAHOO.util.Connect.asyncRequest('GET', elTarget.href + "&ajax", updateContactCallback);
    		 }
    		 break;
    	 } else {
    		 elTarget = elTarget.parentNode;
    	 }
    }
	
	/*var inputTags = encodeURIComponent(document.getElementById("tags").value);
	if(inputTags)
	{
		var addTagData = "tags=" + inputTags + "&uid=" + uid + "&resource=" + resource + "&resourceId=" + resourceId + "&hash=" + global_auth_hash;
	    YAHOO.util.Connect.asyncRequest('POST', '/people/' + resourceAlias + '/?delContact=' + something + '&hash=' + global_auth_hash, delContactCallback);
	}*/
}

YAHOO.util.Event.addListener("contactList", "click", delContactEvent);