$(document).ready(function(){YUI({}).use("node","io","io-form","querystring-stringify-simple","anim",function(Y){var interceptLink=function(e){if(e.target.hasAttribute("href")&&e.target.hasClass("new-window")){window.open(e.target.get("href"));
e.preventDefault()
}};Y.delegate("click",interceptLink,document,"a");
var externalLinks=function(where){if(!where){where=document
}if(!where.getElementsByTagName){return
}var anchors=where.getElementsByTagName("a");
for(var i=0;i<anchors.length;
i++){var anchor=anchors[i];
var node=Y.one(anchor);
if(node.hasAttribute("href")&&node.hasClass("new-window")){anchor.target="_blank"
}}};Y.on("domready",externalLinks,Y);
$(function(){if(document.getElementById("filename")&&document.getElementById("title")){document.getElementById("title").focus()
}});var loadLoginFocus=function(e){if(document.getElementById("login")){document.getElementById("username").focus();
if(e.type!=="load"){e.preventDefault()
}}};Y.delegate("click",loadLoginFocus,document,"#login-button");
var addTags=function(e){e.preventDefault();
function successHandler(id,o){Y.one("#thetags").set("innerHTML",o.responseText);
Y.one("#tags").set("value","")
}var cfg={method:"POST",form:{id:"tags-form"},on:{success:successHandler}};
var request=Y.io("?addtags",cfg)
};Y.delegate("click",addTags,"#TagList","#tagsSubmit");
var deleteTag=function(e){e.preventDefault();
function successHandler(id,o){Y.one("#thetags").set("innerHTML",o.responseText)
}var cfg={method:"POST",data:"hash="+Y.QueryString.escape(global_auth_hash),on:{success:successHandler}};
var request=Y.io(e.target.get("href"),cfg)
};Y.delegate("click",deleteTag,"#thetags","a.delete");
var tweetwriteEvent=function(e){var tweet=Y.one("#tweet");
var tweet_value=tweet.get("value");
var tweetel=document.getElementById("tweet-element").getElementsByTagName("p")[0];
if(!tweetel.getElementsByTagName("span")[0]){Y.one(tweetel).appendChild(document.createElement("span"));
Y.one(tweetel.getElementsByTagName("span")[0]).addClass("tweetchar")
}var tweetelspan=Y.one(tweetel.getElementsByTagName("span")[0]);
if(tweet_value.length>=110){tweetelspan.removeClass("tweetcharok").addClass("tweetchartoomuch")
}else{if(tweet_value.length>=100){tweetelspan.addClass("tweetcharok").removeClass("tweetchartoomuch")
}else{tweetelspan.removeClass("tweetcharok").removeClass("tweetchartoomuch")
}}var printtweetlength;
if(tweet_value.length>140){printtweetlength=" −"+(tweet_value.length-140)
}else{printtweetlength=" "+(140-tweet_value.length)
}tweetelspan.set("innerHTML",printtweetlength);
if(tweet_value.length>140){document.getElementById("tweetSubmit").disabled=true
}else{document.getElementById("tweetSubmit").disabled=false
}};Y.on(["click","keyup"],tweetwriteEvent,"#tweet");
var tweetEvent=function(e){e.preventDefault();
function successHandler(id,o){var ani=new Y.Anim({node:"#twitter-servicemsg",from:{opacity:0},to:{opacity:1}});
ani.set("duration",2);
ani.set("easing",Y.Easing.backIn);
Y.one("#twitter-servicemsg").set("innerHTML",o.responseText).setStyle("opacity","0");
ani.run();setTimeout(function(){Y.one("#twitter-servicemsg").set("innerHTML","")
},7000)}var cfg={method:"POST",form:{id:"tweet-form"},on:{success:successHandler}};
var request=Y.io("?tweet",cfg)
};Y.on("click",tweetEvent,"#tweetSubmit");
var favoriteEvent=function(e){e.preventDefault();
function successHandler(id,o){Y.one("#shareFavorite").set("innerHTML",o.responseText)
}var cfg={method:"POST",data:"hash="+Y.QueryString.escape(global_auth_hash),on:{success:successHandler}};
var request=Y.io(e.target.get("href"),cfg)
};Y.delegate("click",favoriteEvent,document,"#shareFavorite a");
var logoutEvent=function(e){e.preventDefault();
var params={hash:global_auth_hash};
if(e.target.get("id")=="remote_signout_link"){params.remote_signout="true"
}else{params.signout="true"
}var form=document.createElement("form");
document.body.appendChild(form);
form.setAttribute("method","POST");
form.setAttribute("action","/logout");
for(var key in params){if(params.hasOwnProperty(key)){var hiddenField=document.createElement("input");
hiddenField.setAttribute("type","hidden");
hiddenField.setAttribute("name",key);
hiddenField.setAttribute("value",params[key]);
form.appendChild(hiddenField)
}}form.submit()};
Y.on("click",logoutEvent,["#logout-link"]);
function validatePassword(pw,options){var o={lower:0,upper:0,alpha:0,numeric:0,special:0,length:[0,Infinity],custom:[],badWords:[],badSequenceLength:0,noQwertySequences:false,noSequential:false};
for(var property in options){if(options.hasOwnProperty(property)){o[property]=options[property]
}}var re={lower:/[a-z]/g,upper:/[A-Z]/g,alpha:/[A-Z]/gi,numeric:/[0-9]/g,special:/[\W_]/g},rule,i;
if(pw.length<o.length[0]||pw.length>o.length[1]){return false
}for(rule in re){if((pw.match(re[rule])||[]).length<o[rule]){return false
}}for(i=0;i<o.badWords.length;
i++){if(pw.toLowerCase().indexOf(o.badWords[i].toLowerCase())>-1){return false
}}if(o.noSequential&&/([\S\s])\1/.test(pw)){return false
}if(o.badSequenceLength){var lower="abcdefghijklmnopqrstuvwxyz",upper=lower.toUpperCase(),numbers="0123456789",qwerty="qwertyuiopasdfghjklzxcvbnm",start=o.badSequenceLength-1,seq="_"+pw.slice(0,start);
for(i=start;i<pw.length;
i++){seq=seq.slice(1)+pw.charAt(i);
if(lower.indexOf(seq)>-1||upper.indexOf(seq)>-1||numbers.indexOf(seq)>-1||(o.noQwertySequences&&qwerty.indexOf(seq)>-1)){return false
}}}for(i=0;i<o.custom.length;
i++){rule=o.custom[i];
if(rule instanceof RegExp){if(!rule.test(pw)){return false
}}else{if(rule instanceof Function){if(!rule(pw)){return false
}}}}return true}var passwordstrenghtEvent=function(e){var password=Y.one("#password");
var passwordhelper=Y.one("#password-element p.description");
if(password.get("value")){if(password.get("value").length<6){document.getElementById("submit").disabled=true;
passwordhelper.removeClass("ok").set("innerHTML","Password too short!");
return true}if(password.get("value").length>20){document.getElementById("submit").disabled=true;
passwordhelper.removeClass("ok").set("innerHTML","Password too long!");
return true}var passed1=validatePassword(password.get("value"),{badWords:["password","p4ssw0rd"],badSequenceLength:6,noQwertySequences:true,noSequential:true});
document.getElementById("submit").disabled=false;
if(!passed1){passwordhelper.removeClass("ok").set("innerHTML","Weak password!");
return true}var passed2=validatePassword(password.get("value"),{special:1});
var passed3=validatePassword(password.get("value"),{numeric:1});
var passed4=validatePassword(password.get("value"),{upper:1});
var passed5=validatePassword(password.get("value"),{alpha:1});
if((passed2&&(passed3||passed4))||(passed3&&(passed2||passed4))){passwordhelper.addClass("ok").set("innerHTML","<b>Very strong password!</b>");
return true}if(passed2||(passed3&&passed5)||passed4||password.get("value").length>10){passwordhelper.addClass("ok").set("innerHTML","<b>Good password!</b>");
return true}passwordhelper.removeClass("ok").set("innerHTML","Try a better password?")
}else{passwordhelper.removeClass("ok").set("innerHTML","Six or more characters required; case-sensitive");
document.getElementById("submit").disabled=true
}};Y.on(["keyup","click"],passwordstrenghtEvent,"#password");
var clear_username_link_cache=function(){Y.one("#usernamelinkpreview").set("innerHTML","Your new URL: http://www.plifk.com/<b>&lt;username&gt;</b>");
document.getElementById("submit").disabled=false
};var check_username_scheme=function(username){username=username.toLowerCase();
var is_ok=false;
if(username.length>15){Y.one("#usernamelinkpreview").set("innerHTML",'Keep your username less than <span class="warning"><b>15</b> characters.</span>');
document.getElementById("submit").disabled=true
}else{var regex=/^[0-9A-Za-z_\-]+$/;
if(!regex.test(username)){Y.one("#usernamelinkpreview").set("innerHTML",'<span class="warning">This username is invalid.</span> You can only use <b>a-z</b>, <b>0-9</b>, <b>_</b> and <b>-</b> for your username.');
document.getElementById("submit").disabled=true
}else{Y.one("#usernamelinkpreview").set("innerHTML","Your new URL: http://www.plifk.com/<b>"+encodeURIComponent(username)+"</b>");
document.getElementById("submit").disabled=false;
is_ok=true}}return is_ok
};var check_new_username_scheme=function(){return check_username_scheme(document.getElementById("newusername").value)
};var availusernameEvent=function(e){function successHandler(id,o){var root=o.responseXML.documentElement;
if(root.getElementsByTagName("username")[0]){Y.one("#usernamelinkpreview").set("innerHTML",'Username <b class="warning">'+Y.QueryString.escape(new_username.get("value"))+"</b> is already taken. Try another one.");
document.getElementById("submit").disabled=true
}else{Y.one("#usernamelinkpreview").set("innerHTML",'Your new URL: http://www.plifk.com/<b class="ok">'+Y.QueryString.escape(new_username.get("value"))+"</b> (available!)");
document.getElementById("submit").disabled=false
}}function failureHandler(id,o){clear_username_link_cache()
}if(document.getElementById("newusername")){var new_username=Y.one("#newusername");
if(!check_username_scheme(new_username.get("value"))){if(!new_username.get("value").length){clear_username_link_cache()
}}else{var cfg={method:"GET",on:{success:successHandler,failure:failureHandler}};
var request=Y.io("/proxy/api?method=/people/info&username="+Y.QueryString.escape(new_username.get("value")),cfg)
}}};Y.on("keyup",check_new_username_scheme,"#newusername");
Y.on("change",availusernameEvent,"#newusername");
var can_finish_progressbar=false;
function Zend_ProgressBar_Update(data){Y.one("#uploadprogress div.percentage").setStyle("width",parseInt(data.percent,10)+"%");
if(data.percent>15){Y.one("#uploadprogress div.percentage").set("innerHTML",parseInt(data.percent,10)+"%")
}else{Y.one("#uploadprogress div.percentage").set("innerHTML","")
}document.getElementById("uploadprogressinfo").innerHTML='<acronym title="Estimated Time of Arrival">ETA</acronym>: '+data.timeRemaining+" seconds; "+data.text;
if(data.percent>80&&!can_finish_progressbar){can_finish_progressbar=true
}}function Zend_ProgressBar_Finish(){if(can_finish_progressbar){Y.one("#uploadprogress div.percentage").set("innerHTML","Finished").setStyle("width","100%");
Y.one("uploadprogressinfo").set("innerHTML","Upload complete. Processing.")
}}
/*@end of workaround*/
function uploadProgressRequest(){function successHandler(id,o){eval("var data = "+o.responseText);
if(data.finished){Zend_ProgressBar_Finish()
}else{Zend_ProgressBar_Update(data);
return setTimeout(uploadProgressRequest,1000)
}}function failureHandler(id,o){Y.one("#uploadprogressinfo").set("innerHTML","There was a problem with the progress request.")
}var cfg={method:"GET",on:{success:successHandler,failure:failureHandler}};
var request=Y.io("/progress/upload?progress_key="+document.getElementById("progress_key").value,cfg)
}var uploadProgressEvent=function(e){Y.one("#bandwidth").setStyle("opacity","0.5");
Y.one("#bandwidthinfo").setStyle("opacity","0.2");
setTimeout(uploadProgressRequest,600)
};Y.on("click",uploadProgressEvent,"#submitupload")
});$("#commentMsg, textarea#description").autoResize({minHeight:50,maxHeight:200,extraSpace:15});
$("#last-activity-table").tablesorter({sortList:[[3,1]]})
});