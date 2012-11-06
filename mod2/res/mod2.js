
var elPrevious = null; // Reference to currently open messages
var elDetail = null; // Reference to currently opened details


/**
 * Removes tailing '_basic" and "_all"
 * @param el
 * @return element id without tailiung "_basic" or "_all"
 */
function cleanElementId(el) {
    var p;
    p = el.lastIndexOf('_basic');
    if (p > -1) {
        el = el.substr(0, p);
    }
    p = el.lastIndexOf('_all');
    if (p > -1) {
        el = el.substr(0, p);
    }
    return el;
}

/**
 * Simply adds '_basic" to the given element id
 * @param el
 * @return {String} el appended with "_basic"
 */
function getCommentId(el) {
    el = cleanElementId(el);
    return el + '_basic';
}

/// Toggle message display for article in production list
function toggleCommentProdList(el) {
    var elButtonId, elCommentId;
    elButtonId = cleanElementId(el);
    elCommentId = getCommentId(el);
	// Toggle comments
	if (document.getElementById(elCommentId).style.display == 'block' || elDetail !== null) {
		// Hide comments
 		document.getElementById(elCommentId).style.display = 'none';
 		document.getElementById('b_' + elButtonId).src = '../res/be/css/arrow-270.png';
        hideCommentDetails();
 		elPrevious = null;
 	} else {
 		document.getElementById(elCommentId).style.display = 'block';
 		document.getElementById('b_' + elButtonId).src = '../res/be/css/arrow-90.png';
	 	// Hide old comments (if any)
	 	if (elPrevious != null) {
	 		document.getElementById(elPrevious).style.display = 'none';
	 		document.getElementById('b_' + cleanElementId(elPrevious)).src = '../res/be/css/arrow-270.png';
            hideCommentDetails();
	 	}
	 	elPrevious = el; // Store, so this message can be closed when other messages are to be displayed
 	}
 }


/**
 * Hide currently opened messaging details (set in elDetails)
 */
function hideCommentDetails() {
    if (elDetail !== null) {
        document.getElementById(elDetail).style.display = 'none';
        elDetail = null;
    }
}

/**
 * Toggle messaging details
 * @param el
 */
function toggleCommentDetails(el) {
	// Toggle comments
	if (document.getElementById(el).style.display == 'block') {
        // hide comments
        document.getElementById(el).style.display = 'none';
        elDetail = null; // No details opened
    } else {
        document.getElementById(el).style.display = 'block';
        elDetail = el; // Store currently opened comment id
    }
}

/**
 * Toggle detail information in messaging details (f. ex. details for changes in an Extra)
 * @param el
 */
function toggleCommentDetailsDetails(el) {
	// Toggle comments
	if (document.getElementById(el).style.display == 'block') {
        // @todo change displayed element to "+"
        // hide comments
        document.getElementById(el).style.display = 'none';
    } else {
        // @todo change displayed element to "-"
        document.getElementById(el).style.display = 'block';
    }
}

var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


function showArticlePreview(article_uid) {
	var url = path + "/mod7/index.php?tx_newspaper_mod7[controller]=preview&tx_newspaper_mod7[articleid]=" + article_uid;
	top.NpBackend.showArticlePreview(url);
}

/**
 * Ajax call: Delete article
 * @param article uid
 * @param (localized) confirmation message
 */
function deleteArticle(article_uid, message) {
	if (confirm(message)) {

		var request = new Ajax.Request(
			"index.php", {
				method: 'get',
				parameters: 'tx_newspaper_mod2[ajaxController]=deleteArticle&tx_newspaper_mod2[articleUid]=' + parseInt(article_uid),
				onCreate: function() {
				},
				onSuccess: function() {
					self.location.reload();
				}
			}
		);
	}
}

/**
 * Ajax call: publish or hide article
 * @param article_uid
 * @param status: 1 = hidden, 0 = published
 */
function changeArticleHiddenStatus(article_uid, status) {
	NpBackend.showProgress();
	type = (status != 0)? 'hideArticle' : 'publishArticle';
	var request = new Ajax.Request(
		"index.php", {
			method: 'get',
			parameters: 'tx_newspaper_mod2[ajaxController]=' + type + '&tx_newspaper_mod2[articleUid]=' + parseInt(article_uid),
			onCreate: function() {
			},
			onSuccess: function() {
				self.location.reload();
			}
		}
	);
}


function openPlacementMask(article_uid) {
	article_uid = parseInt(article_uid);
	// call placement mask (and add production list filter setting so thus filter can be active when returning to the production list
	self.location.href = "../mod7/index.php?tx_newspaper_mod7[articleid]=" + article_uid +  "&tx_newspaper_mod7[mod2Filter]=" + escape($("moderation").serialize());
}


function newArticle(path) {
	self.location.href = path + "index.php?tx_newspaper_mod5[controller]=new_article_wizard&tx_newspaper_mod5[calling_module]=2&tx_newspaper_mod5[mod2Filter]=" + escape($("moderation").serialize());
}


function submitFilter(type) {
	// type = reset -> reset filter
	if (type == 'reset') {
		self.location.href = "index.php?tx_newspaper_mod2[type]=reset";
	}

	// type = reset_startpage -> startpage is set to 0, filter setting is used
	// type = filter -> filter settings remain unchanged, page is reloaded
	self.location.href = "index.php?tx_newspaper_mod2[type]=" + type + "&" + $("moderation").serialize();

}

function browse(page) {
	$("tx_newspaper_mod2[startPage]").value = parseInt($("tx_newspaper_mod2[startPage]").value) + parseInt(page); // set new page in browse sequence
	submitFilter('filter'); // execute filter (do not reset startPage counter)
}



function checkEnter(code) {
	if (code != 13) {
		return;
	}
	submitFilter('reset_startpage');
	return false;
}
