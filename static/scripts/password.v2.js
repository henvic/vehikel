/*
 * This file contains a 3rd party function
	Password Validator 0.1
	(c) 2007 Steven Levithan <stevenlevithan.com>
	MIT License
	
	http://blog.stevenlevithan.com/archives/javascript-password-validator
	
	to get this file without being minimized, crop the -min of its link
*/

function validatePassword (pw, options) {
	// default options (allows any password)
	var o = {
		lower:    0,
		upper:    0,
		alpha:    0, /* lower + upper */
		numeric:  0,
		special:  0,
		length:   [0, Infinity],
		custom:   [ /* regexes and/or functions */ ],
		badWords: [],
		badSequenceLength: 0,
		noQwertySequences: false,
		noSequential:      false
	};

	for (var property in options)
		o[property] = options[property];

	var	re = {
			lower:   /[a-z]/g,
			upper:   /[A-Z]/g,
			alpha:   /[A-Z]/gi,
			numeric: /[0-9]/g,
			special: /[\W_]/g
		},
		rule, i;

	// enforce min/max length
	if (pw.length < o.length[0] || pw.length > o.length[1])
		return false;

	// enforce lower/upper/alpha/numeric/special rules
	for (rule in re) {
		if ((pw.match(re[rule]) || []).length < o[rule])
			return false;
	}

	// enforce word ban (case insensitive)
	for (i = 0; i < o.badWords.length; i++) {
		if (pw.toLowerCase().indexOf(o.badWords[i].toLowerCase()) > -1)
			return false;
	}

	// enforce the no sequential, identical characters rule
	if (o.noSequential && /([\S\s])\1/.test(pw))
		return false;

	// enforce alphanumeric/qwerty sequence ban rules
	if (o.badSequenceLength) {
		var	lower   = "abcdefghijklmnopqrstuvwxyz",
			upper   = lower.toUpperCase(),
			numbers = "0123456789",
			qwerty  = "qwertyuiopasdfghjklzxcvbnm",
			start   = o.badSequenceLength - 1,
			seq     = "_" + pw.slice(0, start);
		for (i = start; i < pw.length; i++) {
			seq = seq.slice(1) + pw.charAt(i);
			if (
				lower.indexOf(seq)   > -1 ||
				upper.indexOf(seq)   > -1 ||
				numbers.indexOf(seq) > -1 ||
				(o.noQwertySequences && qwerty.indexOf(seq) > -1)
			) {
				return false;
			}
		}
	}

	// enforce custom regex/function rules
	for (i = 0; i < o.custom.length; i++) {
		rule = o.custom[i];
		if (rule instanceof RegExp) {
			if (!rule.test(pw))
				return false;
		} else if (rule instanceof Function) {
			if (!rule(pw))
				return false;
		}
	}

	// great success!
	return true;
}

/* end of validation function */

var passwordstrenghtEvent = function (e) {
    var password = document.getElementById("password");
    
    var passwordhelper = YAHOO.util.Dom.getElementsByClassName('description', 'p', document.getElementById('password-element'))[0];
    
    if (password.value)
    {
		if (password.value.length < 6)
		{
			document.getElementById('submit').disabled = true;
			YAHOO.util.Dom.removeClass(passwordhelper, 'ok');
			passwordhelper.innerHTML = 'Password too short!';
			return true;
		}
		
		if (password.value.length > 20)
		{
			document.getElementById('submit').disabled = true;
			YAHOO.util.Dom.removeClass(passwordhelper, 'ok');
			passwordhelper.innerHTML = 'Password too long!';
			return true;
		}
		
		var passed1 = validatePassword(password.value, {
			badWords: ["password", "p4ssw0rd"],
			badSequenceLength: 6,
			noQwertySequences: true,
			noSequential:      true
		});
		
		document.getElementById('submit').disabled = false;
		
		if (!passed1) {
			YAHOO.util.Dom.removeClass(passwordhelper, 'ok');
			passwordhelper.innerHTML = 'Password too easy!';
			return true;
		}
		
		var passed2 = validatePassword(password.value, {
			special:  1
		});
		
		var passed3 = validatePassword(password.value, {
			numeric:  1
		});
		
		var passed4 = validatePassword(password.value, {
			upper:    1
		});
		
		var passed5 = validatePassword(password.value, {
			alpha:    1
		});
		
		if ((passed2 && (passed3 || passed4)) || (passed3 && (passed2 || passed4))) {
			passwordhelper.innerHTML = '<b>Very strong password!</b>';
			YAHOO.util.Dom.addClass(passwordhelper, 'ok');
			return true;
		}
		
		if (passed2 || (passed3 && passed5) || passed4 || password.value.length > 10) {
			passwordhelper.innerHTML = '<b>Good password!</b>';
			YAHOO.util.Dom.addClass(passwordhelper, 'ok');
			return true;
		}
		
		YAHOO.util.Dom.removeClass(passwordhelper, 'ok');
		passwordhelper.innerHTML = 'Try a better password?';
	} else {
		YAHOO.util.Dom.removeClass(passwordhelper, 'ok');
		passwordhelper.innerHTML = 'Six or more characters required; case-sensitive';
		document.getElementById('submit').disabled = true;
	}
};

YAHOO.util.Event.addListener('password', 'keyup', passwordstrenghtEvent);
YAHOO.util.Event.addListener('password', 'keydown', passwordstrenghtEvent);