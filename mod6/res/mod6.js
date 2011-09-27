
/**
 * Add extras to tagzones in order to specify dossier content
 */
var NpManageDossier = {
	param: [],

	tz_uid: null, // stores tag zone uid when opening the element browser popup

	currentDossierTitle: '', // stores dossier title (for undo function)
	currentSectionUid: 0, // stores uid of associated section
	currentSectionTitle: '', // stores name of associated section

	/**
	 * Get tagzone uid
	 * @return uid of currently processed tagzone
	 */
	getTagzoneUid: function() {
		return this.tz_uid
	},


	/**
	 * Shows or hides backend for batch assigning tags to article and list articles with current tag assigned
	 * @param state If 1 the backend is shown, else the backend is hidden
	 * @return void
	 */
	showArticleBackend: function(state) {
		if (state != 1) {
			var display = 'none';
		} else {
			var display = 'block';
		}
		document.getElementById("article_batch").style.display = display;
		document.getElementById("article_list_trigger").style.display = display;
	},


	/**
	 * Reads all tags for given control tag uid and adds them to the tag dropdown (AJAX call)
	 * @param uid Control tag uid
	 * @return void
	 */
	changeCtrlTagCat: function(uid) {
		var request = new Ajax.Request(
			"index.php",
				{
					method: 'get',
					parameters: 'tx_newspaper_mod6[AjaxCtrlTagCat]=' + parseInt(uid),
					onCreate: function() {
						$("tz").innerHTML = '<img src="../res/be/css/move-spinner.gif" />'; // clear tag zone box (and display spinner there)
						$("tx_newspaper_mod6[tag]").descendants().each(Element.remove); // clear tag select box
					},
					onSuccess: function(data) {
						if (data) {
							// insert tags into select box
							options = data.responseText.evalJSON();
							for(i = 0; i < options.length; i++) {
								$("tx_newspaper_mod6[tag]").options[$("tx_newspaper_mod6[tag]").options.length] = new Option(NpTools.splitParamAtPipe(options[i], 0), NpTools.splitParamAtPipe(options[i], 1));
							}
							$("tz").innerHTML = ''; // remove spinner
							$("tag_batch_msg").innerHTML = ''; // clear message
							$("article_list").innerHTML = ''; // clear article list
							$("article_list").style.display = 'none'; // clear article list
							NpManageDossier.hideTagBackend();
						}
					}
				}
			);
		this.showArticleBackend(0);
	},

	/**
	 * Reads and display tagzone setting for current tag (AJAX call)
	 * If uid is empty, just clear tagzone backend (no tag selected)
	 * @param uid Tag uid
	 * @return void
	 */
	changeTag: function(uid) {
		$("article_list").style.display = 'none'; // clear article list
		var uid = parseInt(uid);

		if (!uid) {
			$("tz").innerHTML = ''; // selected empty row in dropdown, so clear tagzone backend
			$("article_batch").style.display = "none"; // hide article batch backend
			$("article_list_trigger").style.display = "none"; // hide list of article assigned to this tag
			$("article_list").innerHTML = ''; // clear article list
			this.hideTagBackend();
			return;
		}

		var request = new Ajax.Request(
			"index.php", {
				method: 'get',
				parameters: 'tx_newspaper_mod6[AjaxTag]=' + parseInt(uid),
				onCreate: function() {
					$("tz").innerHTML = '<img src="../res/be/css/move-spinner.gif" />'; // clear tag zone box (and display spinner there)
				},
				onSuccess: function(data) {
					if (data) {
						var tmp = data.responseText.evalJSON();
						$("tz").innerHTML = tmp.backend; // display tag zone backend, remove spinner

						NpManageDossier.setCurrentDossierTitle(tmp.dossierTitle);
						NpManageDossier.displayDossierTitle();

						NpManageDossier.setCurrentDossierSection(tmp.sectionTitle, tmp.sectionUid);
						NpManageDossier.displayDossierSection();

						$("tag_batch_msg").innerHTML = ''; // clear message
						$("article_list").innerHTML = ''; // clear article list
					}
				}
			}
		);
		this.showArticleBackend(1);
	},

	/**
	 * Renders the tagzone backend for the selected tag
	 * @param uid Tagzone uid
	 * @return void
	 */
	reloadTagzone: function(uid) {
		this.changeTag(uid);
	},

	/**
	 * Hides backend for tag title and tag section editing
	 */
	hideTagBackend: function() {
		this.displayDossierTitle();
		this.setCurrentDossierSection();
		this.displayDossierSection();
	},



	/**
	 * Remove an Extra from current tag/tagzone
	 * @param tz_uid Tagzone uid
	 * @param e_uid Extra uid
	 * @return void
	 */
	removeExtraFromTagzone: function(tz_uid, e_uid) {
		if (confirm(this.param["confirmMessage"])) {
			var request = new Ajax.Request(
				"index.php", {
					method: 'get',
					parameters: 'tx_newspaper_mod6[AjaxRemoveExtraFromTagZone]=1' + "&tx_newspaper_mod6[tz_uid]=" + parseInt(tz_uid) + "&tx_newspaper_mod6[e_uid]=" + parseInt(e_uid) + "&tx_newspaper_mod6[tag_uid]=" + parseInt($("tx_newspaper_mod6[tag]").value),
					onCreate: function() {
						$("tz").innerHTML = '<img src="../res/be/css/move-spinner.gif" />'; // clear tag zone box (and display spinner there)
					},
					onSuccess: function(data) {
						if (data) {
							var tmp = data.responseText.evalJSON();
							$("tz").innerHTML = tmp.backend; // display tag zone backend, remove spinner
						}
					}
				}
			);
		}
	},


// dossier title handling

	/**
	 * Store current section (for displaying and undoing)
	 * @param title Title of dossier
	 * @retrun void
	 */
	setCurrentDossierTitle: function(title) {
		if (title === "undefined") {
			title = "";
		}
		this.currentDossierTitle = title;
	},

	/** Displays given section title (if any)
	 * @param title Well ... the title ...
	 * @return void
	 */
	displayDossierTitle: function() {
		$("dossier_title").innerHTML = this.currentDossierTitle;
		if (this.currentDossierTitle) {
			$("dossier_title_label").innerHTML = this.param["labelTitle"] + ": ";

			// Show edit icon for dossier title, hide other icons
			$("dossier_title_edit").innerHTML = '<a href="#" onclick="NpManageDossier.editDossierTitle(); return false;">' + NpManageDossier.param["iconEdit"] + '</a>';
		} else {
			$("dossier_title_label").innerHTML = '';
			$("dossier_title_edit").innerHTML = '';
		}
	},

	/**
	 * Store new dossier title (AJAX call)
	 * @return void
	 */
	storeDossierTitle: function() {
		var uid = parseInt($("tx_newspaper_mod6[tag]").value); // read uid of current control tag
		var title = $("input_dossier_title").value; // read new title from input field
		if (!title) {
			alert(this.param["messageWizardDossierTitleMissing"]);
			return false;
		}
		// store new title
		var request = new Ajax.Request(
			"index.php", 				{
				method: 'get',
				parameters: 'tx_newspaper_mod6[AjaxStoreDossierTitleUid]=' + parseInt(uid) + '&tx_newspaper_mod6[dossierTitle]=' + NpTools.escapeQuotes(title),
				onCreate: function() {
					$("dossier_title_edit").innerHTML = '<img src="../res/be/css/move-spinner.gif" />'; // remove store icon, render spinner
				},
				onSuccess: function(data) {
					if (data) {
						var success = data.responseText.evalJSON().success;
						if (success) {
							var title = $("input_dossier_title").value;
							NpManageDossier.setCurrentDossierTitle(title);
							NpManageDossier.displayDossierTitle();
						} else {
							alert(titleNotUniqueMesssage);
							NpManageDossier.changeTitleShowStoreCancelButtons(); // show store and cancel buttons again
						}
					}
				}
			}
		);
	},

	/**
	 * Edit dossier title: remove dossier title display, show dossier title input field
	 * @return void
	 */
	editDossierTitle: function() {
		var uid = parseInt($("tx_newspaper_mod6[tag]").value); // read uid of current control tag

		// render iput field
		$("dossier_title").innerHTML = '<input class="inputDossierTitle" id="input_dossier_title" value="' + NpTools.hscQuotes(this.currentDossierTitle) + '" />'; // render input form field

		// Show store and cancel icons, hide other icons
		$("dossier_title_edit").innerHTML = '<a href="#" onclick="NpManageDossier.storeDossierTitle(); return false;">' + NpManageDossier.param["iconSave"] + '</a> <a href="#" onclick="NpManageDossier.cancelDossierTitleEdit(); return false;">' + NpManageDossier.param["iconX"] + '</a> <span style="color:red;">' + NpManageDossier.param["messagePerformanceWarning"] + '</span>';
	},

	/** Cancel dossier title editing, restore title
	 * @return void
	 */
	cancelDossierTitleEdit: function() {
		this.displayDossierTitle();
	},





// Dossier section handling

	/**
	 * Store current section (for displaying and undoing)
	 * @param sectionTitle Name of section
	 * @param uid uid of section
	 * @retrun void
	 */
	setCurrentDossierSection: function(sectionTitle, uid) {
		if (sectionTitle === "undefined" || !sectionTitle || !uid) {
			// clear settings
			sectionTitle = "";
			uid = -1;
		}
		this.currentSectionTitle = sectionTitle;
		this.currentSectionUid = parseInt(uid);
	},

	/**
	 * Display current section in dropdown
	 * @retrun void
	 */
	displayDossierSection: function() {
		$("dossier_section").innerHTML = this.currentSectionTitle;
		if (this.currentSectionTitle && this.currentSectionUid) {
			// show edit icon
			$("dossier_section_label").innerHTML = this.param["labelSection"] + ': ';
			$("dossier_section_edit").innerHTML = '<a href="#" onclick="NpManageDossier.editDossierSection(); return false;">' + this.param["iconEdit"] + '</a>';
		} else {
			// hide edit icon
			$("dossier_section_label").innerHTML = '';
			$("dossier_section_edit").innerHTML = '';
		}
		$("dossier_section").show();
		$("dossier_section_edit").show();
		$("dossier_section_dropdown").style.display = "none";
	},

	//
	/**
	 * Edit section (display dropdown, hide text)
	 * @return void
	 */
	editDossierSection: function() {
		// get seletced section in dropdown
		for (var i=0; i < $("sections").options.length; i++) {
			if ($("sections").options[i].value == this.currentSectionUid) {
				$("sections").options[i].selected = true;
			}
		}
		$("dossier_section").hide();
		$("dossier_section_edit").hide();
		$("dossier_section_dropdown").style.display = "inline";
	},

	/**
	 * Store new dossier section (AJAX call)
	 * @return void
	 */
	storeDossierSection: function() {
		var tagUid = parseInt($("tx_newspaper_mod6[tag]").value); // read uid of current control tag
		var sectionUid = $("sections").value; // read new section from select box
		var request = new Ajax.Request(
			"index.php", 				{
				method: 'get',
				parameters: 'tx_newspaper_mod6[AjaxStoreDossierSectionUid]=' + parseInt(sectionUid) + '&tx_newspaper_mod6[tagUid]=' + parseInt(tagUid),
				onCreate: function() {},
				onSuccess: function() {
					NpManageDossier.setCurrentDossierSection($("sections").options[$("sections").selectedIndex].text, sectionUid);
					NpManageDossier.displayDossierSection();
				}
			}
		);
	},

	/**
	 * Cancel dossier section editing, restore section display
	 * @return void
	 */
	cancelDossierSectionEdit: function() {
		this.displayDossierSection();
	},





// Handle list of article with tag assigned

	// open and get list or close list
	/**
	 * Open and get list of articles or close list (aka toggle)
	 * @return void
	 */
	toggleArticleList: function() {
		if (document.getElementById('article_list').style.display != 'block') {
			// Show article list backend
			document.getElementById('article_list').style.display = 'block';
			// get assigned articles
			var tag = parseInt(document.getElementById("tx_newspaper_mod6[tag]").value);
			var request = new Ajax.Request(
				"index.php", {
					method: 'get',
					parameters: 'tx_newspaper_mod6[AjaxListArticlesForCtrlTag]=' + tag,
					onCreate: function() {
						$("article_list").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
					},
					onSuccess: function(data) {
						$("article_list").innerHTML = NpManageDossier.getDetachTagCheckbox();
						$("article_list").innerHTML += data.responseText;
						$("detachTagStuff").style.display = 'none'; // hide initially
					}
				}
			);
		} else {
			// Hide article list backend
			document.getElementById('article_list').style.display = 'none';
			document.getElementById('article_list').innerHTML = '';
		}
	},

	/**
	 * Renders a checkbox specifying whether or not to show detach tag checkbox for articles assigned to current tag
	 * @return HTML with a checkbox with some javascript call
	 */
	getDetachTagCheckbox: function() {
		var html = '<div><input id="checkboxDetachTags" onchange="NpManageDossier.changeCheckboxDetachTag(); return false;" type="checkbox" />' + NpManageDossier.param["labelCheckboxBatchDetachTag"];
		html += '<span id="detachTagStuff"><input id="checkboxDetachTagsAllArticles" type="checkbox" onchange="NpManageDossier.setCheckboxDetachArticleAll(); return false;" />' + NpManageDossier.param["labelCheckboxBatchDetachTagAllArticles"] + ' <input type="button" onclick="NpManageDossier.detachTagFromArticles(); return false;" value="' + NpManageDossier.param["labelCheckboxBatchDetachTagSubmit"] + '" /></span><span id="processSpinnerDetach"></span>';
		html += '</div>';
		return html;
	},

	/**
	 * If checkbox "checkboxDetachTags" is clicked, a checkbox is prepended to every article listed, the checkbox is hidden otherwise
	 * @return void
	 */
	changeCheckboxDetachTag: function() {
		if ($("checkboxDetachTags").checked) {
			var display = "inline";
		} else {
			var display = "none";
			$("checkboxDetachTagsAllArticles").checked = false; // reset checkbox
		}

		// show/hide detach backend
		$("detachTagStuff").style.display = display;

		// show/hide checkbox for all articles
		var elements = $$("#article_list .detachTag");
		elements.each(function(element) {
			element.style.display = display;
		});
	},

	/**
	 * Checks or unchecks (depending on checkbox "checkboxDetachTagsAllArticles") all detach tag article checkboxes
	 * @return void
	 */
	setCheckboxDetachArticleAll: function() {
		var checked = $("checkboxDetachTagsAllArticles").checked;
		var elements = $$("#article_list .detachTag");
		elements.each(function(element) {
			element.checked = checked;
		});
	},

	/**
	 * Detach current tag from selected articles
	 * @return void
	 */
	detachTagFromArticles: function() {
		var prefixLength = 7; // length of prefix "detach_" vor field name
		var elements = $$("#article_list .detachTag");

		// Collect article uids to detach the tag from
		var ids = Array();
		elements.each(function(element) {
			if (element.checked == true) {
				var id = parseInt(element.id.substring(prefixLength)); // get substring with article uid
				ids.push(id);
			}
		});

		var tagUid = parseInt($("tx_newspaper_mod6[tag]").value); // read uid of current control tag
		var request = new Ajax.Request(
			"index.php", {
				method: 'get',
				parameters: 'tx_newspaper_mod6[AjaxBatchDetachTag]=' + tagUid + '&tx_newspaper_mod6[articleUids]=' + ids.join(","),
				onCreate: function() {
					$("processSpinnerDetach").innerHTML = '<img src="../res/be/css/move-spinner.gif" />'; // show spinner
				},
				onSuccess: function(data) {
					$("article_list").innerHTML = data.responseText;
				}
			}
		);
	},





	/**
	 * Open Extra element browser
	 * @param uid Tagzone uid
	 * @param extraClass Pre-selected Extra class (if any)
	 * @return void
	 * @todo: move window.open to some element browser lib
	 */
	addExtraToTagzone: function(uid, extraClass) {
		this.tz_uid = parseInt(uid); // store tag zone uid so the element browser knows which tag zone the extra should be added to

		var extraPreselect = (typeof(extraClass) == 'undefined')? '' : '&tx_newspaper_mod1[extraClassPreselect]=' + extraClass;
		var newOnly = (extraPreselect)? '&tx_newspaper_mod1[newOnly]=1' : ''; // if an Exra is pre-selected, create it right away

		// open element browser popup
		var w = window.open(
			"../mod1/index.php?tx_newspaper_mod1[controller]=eb&tx_newspaper_mod1[type]=e&tx_newspaper_mod1[allowMultipleSelection]=0&tx_newspaper_mod1[jsType]=manageDossiers" + extraPreselect + newOnly,
			"npeb",
			"width=800,height=500"
		);
		w.focus();
	},

	/**
	 * Toggle batch assign tag to articles backend
	 * @return void
	 */
	toggleBatchForm: function() {
		if (document.getElementById('article_batch_form').style.display != 'block') {
			document.getElementById('article_batch_form').style.display = 'block';
		} else {
			document.getElementById('article_batch_form').style.display = 'none';
			$("tag_batch_msg").innerHTML = "";
		}
	},


	/**
	 * Adds the selected tag to all articles that has been added to select box tx_newspaper_mod6[articles]
	 * @return void
	 */
	batchAttachTag: function() {
		var tag = document.getElementById("tx_newspaper_mod6[tag]").value;

		// collect article uids
		var s = document.getElementById('tx_newspaper_mod6[articles]');
		var aUids = '';
		for (var i = 0; i < s.options.length; i++) {
			aUids += s.options[i].value;
			if (i < s.options.length-1) {
				aUids += ',';
			}
		}

		// assign tag
		var request = new Ajax.Request(
			"index.php", {
				method: 'get',
				parameters: 'tx_newspaper_mod6[AjaxBatchAttachTag]=' + tag + '&tx_newspaper_mod6[articleUids]=' + aUids,
				onCreate: function() {
					$("processSpinner").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
					$("tag_batch_msg").innerHTML = ''; // clear message
				},
				onSuccess: function(data) {
					$("tx_newspaper_mod6[articles]").descendants().each(Element.remove); // clear article select box
					$("processSpinner").innerHTML = ''; // remove spinner
					$("tag_batch_msg").innerHTML = data.responseText; // show message
				}
			}
		);

	},

	/**
	 *
	 * @return void
	 * @todo: remove when dependecy tree performance issues are solved
	 */
	showReloadButton: function() {
		$("tz_reload").style.display = "block";
	},

	/**
	 * Add article to article select box
	 * @param uid Article uid
	 * @param kicker Article kicker
	 * @param title Article title
	 * @return void
	 */
	addOption: function(uid, kicker, title) {
		NpBackend.addOption(document.getElementById('tx_newspaper_mod6[articles]'), uid, NpTools.assembleArticleTitle(kicker, title));
	},

	/**
	 * Removes all selected options from article select box
	 * @return void
	 */
	removeOptions: function() {
		NpBackend.removeSelectedOptions(document.getElementById('tx_newspaper_mod6[articles]'));
	},

	/**
	 * Opens the article browser
	 * @return void
	 * @todo move to newspaper.js?
	 */
	setFormValueOpenBrowser_A: function() {

		var path = NpTools.getPath();
		if (path.indexOf('typo3conf/ext/newspaper') == -1) {
			path += 'typo3conf/ext/newspaper'; // modify path if called from within a module
		}

	    var url = path + '/mod2/index.php?ab4al=1&select_box_id=tx_newspaper_mod6[articles]';
	    browserWin = window.open(url,"Typo3WinBrowser","height=350,width=650,status=0,menubar=0,resizable=1,scrollbars=1");
	    browserWin.focus();
	}

}
