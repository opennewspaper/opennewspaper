
var el_previous = null; // reference to currently open messages
/// Toggle message display for article in production list
function toggleCommentProdList(el) {
	// toggle comments
	if (document.getElementById(el).style.display == 'block') {
		// hide comments
 		document.getElementById(el).style.display = 'none';
 		document.getElementById('b_' + el).src = '../res/be/css/arrow-270.png';
 		el_previous = null;
 	} else {
 		document.getElementById(el).style.display = 'block';
 		document.getElementById('b_' + el).src = '../res/be/css/arrow-90.png';
	 	// hide old comments (if any)
	 	if (el_previous != null) {
	 		document.getElementById(el_previous).style.display = 'none';
	 		document.getElementById('b_' + el_previous).src = '../res/be/css/arrow-270.png';
	 	}
	 	el_previous = el; // store, so this message can be closen when other messages are to be displayed
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
