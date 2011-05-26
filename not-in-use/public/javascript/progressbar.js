/*@Safari has a bug:
 * http://stackoverflow.com/questions/952267/safari-doesnt-allow-ajax-requests-after-form-submit
 * https://bugs.webkit.org/show%5Fbug.cgi?id=23933
*/
/*beginf of @workaround: only let the changes in Zend_ProgressBar_Finish happen if the user is near the end*/
var can_finish_progressbar = false;

function Zend_ProgressBar_Update(data) {/* @TODO use YUI ProgressBar? */
	YAHOO.util.Dom.getElementsByClassName('percentage', 'div', document
			.getElementById('uploadprogress'))[0].style.width = parseInt(
			data.percent, 10) + '%';

	if (data.percent > 15) {
		YAHOO.util.Dom.getElementsByClassName('percentage', 'div', document
				.getElementById('uploadprogress'))[0].innerHTML = parseInt(
				data.percent, 10) + '%';
	} else {//clears if there is less than 15% because if the user stopped there's garbage there
		YAHOO.util.Dom.getElementsByClassName('percentage', 'div', document
				.getElementById('uploadprogress'))[0].innerHTML = '';
	}

	document.getElementById('uploadprogressinfo').innerHTML = '<acronym title="Estimated Time of Arrival">ETA</acronym>: ' + data.timeRemaining + ' seconds; ' + data.text;

	/*@gambiarra*/
	if (data.percent > 80 && !can_finish_progressbar) {
		can_finish_progressbar = true;
	}
}

function Zend_ProgressBar_Finish() {
	if (can_finish_progressbar) {
		YAHOO.util.Dom.getElementsByClassName('percentage', 'div', document
				.getElementById('uploadprogress'))[0].style.width = '100%';
		YAHOO.util.Dom.getElementsByClassName('percentage', 'div', document
				.getElementById('uploadprogress'))[0].innerHTML = 'Finished';
		document.getElementById('uploadprogressinfo').innerHTML = 'Upload complete. Processing.';
	}
}
/*@end of workaround*/








var progressbarHandleSuccess = function(o){
	if(o.responseText !== undefined){
		eval('var data = ' + o.responseText);
		if (data.finished) {
			Zend_ProgressBar_Finish();
		} else {
			Zend_ProgressBar_Update(data);
			return setTimeout(makeRequest, 1000);
		}
	}
};

var progressbarHandleFailure = function(o){
	if(o.responseText !== undefined){
		document.getElementById('uploadprogressinfo').innerHTML = 'There was a problem with the progress request.';
	}
};

var progressbarCallback =
{
  success:progressbarHandleSuccess,
  failure:progressbarHandleFailure
};

function makeRequest(){
	sUrl = "/progress/upload?progress_key=" + document.getElementById("progress_key").value;
	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, progressbarCallback);
}




var progressEvent = function (e) {
	var elTarget = YAHOO.util.Event.getTarget(e);
	document.getElementById('bandwidth').style.opacity = 0.5;
	document.getElementById('bandwidthinfo').style.opacity = 0.2;
	setTimeout(makeRequest, 600);
};

YAHOO.util.Event.addListener("submitupload", "click", progressEvent);
