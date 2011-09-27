
/**
 * Tag backend functions
 */
var NpTag = {
	param: [],

	/**
	 * AJAX call: Fetch tags for selected control tag category or content tag and pass the tags to addOptionsToTarget()
	 * @return void
	 */
	fetchTags: function() {

		if ($("tagTypeCtrl").checked) {
			// control tag
			var param = "&tx_newspaper_mod8[tagType]=ctrl";
			param += "&tx_newspaper_mod8[ctrlTagCat]=" + $("ctrltagcat").value;
			$("spinnerCtrlTag").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
		} else {
			// content tag
			var param = "&tx_newspaper_mod8[tagType]=content";
			$("spinnerContentTag").innerHTML = '<img src="../res/be/css/move-spinner.gif" />';
		}

		var request = new Ajax.Request(
			"index.php", {
				method: 'get',
				parameters: 'tx_newspaper_mod8[ajaxController]=changeTagCat' + param,
				onCreate: function() {
					NpTag.clearOptionsInTarget();
				},
				onSuccess: function(data) {
					if (data) {
						NpTag.addOptionsToTarget(data.responseText.evalJSON()); // pass options to current sub form
						$("spinnerCtrlTag").innerHTML = '';
						$("spinnerContentTag").innerHTML = '';
					}
				}
			}
		);
	},

	/**
	 * Clear all tag related select boxes (check availability)
	 * @return void
	 */
	clearOptionsInTarget: function() {
		var obj = ["tag_rename", "tag_merge", "tag_merge_with", "tag_delete"];
		for (var i=0; i < obj.length; i++) {
			var selectbox = document.getElementById(obj[i]);
			if (selectbox) {
				NpBackend.removeAllOptions(selectbox); // clear select box
			}
		}
	},

	/**
	 * Adds the tags to the appropraite tag related select boxes
	 * @param options Tags in a JSON object [tag uid|tag label, ...]
	 * @return void
	 */
	addOptionsToTarget: function(options) {

		// Add tags on "Rename tags" backend
		if (document.getElementById("tag_rename")) {
			$("tag_rename").options[$("tag_rename").options.length] = new Option(this.param["labelChooseTag"], -1);
			for(i = 0; i < options.length; i++) {
				$("tag_rename").options[$("tag_rename").options.length] = new Option(NpTools.splitParamAtPipe(options[i], 0), NpTools.splitParamAtPipe(options[i], 1));
			}
			return;
		}

		// Add tags on "Merge tags" backend
		if (document.getElementById("tag_merge")) {
			for(var i = 0; i < options.length; i++) {
				$("tag_merge").options[$("tag_merge").options.length] = new Option(NpTools.splitParamAtPipe(options[i], 0), NpTools.splitParamAtPipe(options[i], 1));
			}
			$("tag_merge_with").options[$("tag_merge_with").options.length] = new Option(this.param["labelChooseTag"], -1);
			for(var i = 0; i < options.length; i++) {
				$("tag_merge_with").options[$("tag_merge_with").options.length] = new Option(NpTools.splitParamAtPipe(options[i], 0), NpTools.splitParamAtPipe(options[i], 1));
			}
			return;
		}

		// Add tags on "Delete tag" backend
		if (document.getElementById("tag_delete")) {
			$("tag_delete").options[$("tag_delete").options.length] = new Option(this.param["labelChooseTag"], -1);
			for(i = 0; i < options.length; i++) {
				$("tag_delete").options[$("tag_delete").options.length] = new Option(NpTools.splitParamAtPipe(options[i], 0), NpTools.splitParamAtPipe(options[i], 1));
			}
			return;
		}

	},


// Rename tag

	/**
	 * Fills the rename tag input fields (or hides it if no tag is selected)
	 * @return void
	 */
	processRenameInputField: function() {
		var tagUid = $("tag_rename").value;
		if (tagUid < 1) {
			$("renameBackend").style.display = 'none';
			return false; // nothing to do
		}
		this.refreshRenameInputField();
		$("renameBackend").style.display = 'block';
	},

	/**
	 * Copies the tag name to rename input field
	 * @return void
	 */
	refreshRenameInputField: function() {
		$("rename").value = $("tag_rename").options[$("tag_rename").selectedIndex].text;
		$("message").innerHTML = ''; // clear message
	},

	/**
	 * Store new tag name
	 * @return void
	 */
	storeRenamedTag: function() {
		if (confirm(this.param["confirmMessageRename"])) {
			var tagUid = parseInt($("tag_rename").value);
			var newTagName = $("rename").value;
			var request = new Ajax.Request(
				"index.php",
					{
						method: 'get',
						parameters: 'tx_newspaper_mod8[ajaxController]=renameTag&tx_newspaper_mod8[tagUid]=' + tagUid + '&tx_newspaper_mod8[newTagName]=' + NpTools.escapeQuotes(newTagName),
						onCreate: function() {
							NpBackend.showProgress();
						},
						onSuccess: function(data) {
							if (data) {
								var success = data.responseText.evalJSON().success;
								if (success) {
									$("message").innerHTML = NpTag.param["tagRenamedMesssage"];
									NpTag.fetchTags();
									NpTag.processRenameInputField();
								} else {
									alert(NpTag.param["tagNotUniqueMesssage"]);
								}
							}
							NpBackend.hideProgress();
						}
					}
				);
		}
	},


// Merge tags

	/**
	 * Ajax: Merge selected tags into selected target tag
	 */
	merge: function() {
		var targetTagUid = parseInt($("tag_merge_with").value);
		if (targetTagUid < 1) {
			alert(this.param["messagePleaseSelectTargetTag"]);
			return;
		}

		var sourceTags = $F("tag_merge").join("|");
		if (!sourceTags) {
			alert(this.param["messagePleaseSelectSourceTags"]);
			return;
		}

		if (!confirm(this.param["confirmMessageMerge"])) {
			return; // user must confirm that he knows what he's doing
		}

		var request = new Ajax.Request(
			"index.php", {
				method: 'get',
				parameters: 'tx_newspaper_mod8[ajaxController]=mergeTags&tx_newspaper_mod8[sourceTags]=' + sourceTags + '&tx_newspaper_mod8[targetTag]=' + targetTagUid,
				onCreate: function() {
					NpBackend.showProgress();
				},
				onSuccess: function(data) {
					if (data) {
						NpTag.fetchTags(); // re-read tags
						$("message").innerHTML = data.responseText;
					}
					NpBackend.hideProgress();
				}
			}
		);
	},


// Delete tag

	/**
	 * Ajax: Delete selected tag after confirming to delete the tag (and to detach existing tag usage)
	 * @param confirmDetach Boolean States if a confirm message to detach tags should be shown
	 * @return void
	 */
	deleteTag: function(confirmDetach) {
		var tagUid = parseInt($("tag_delete").value);
		if (tagUid < 1) {
			alert(this.param["messagePleaseSelectTag"]);
			return;
		}

		var detachParam = "";
		if (confirmDetach != true) {
			confirmDetach = false; // false is default
		} else {
			detachParam = "&tx_newspaper_mod8[confirmDetachTags]=1";
		}
		if (confirmDetach || confirm(this.param["confirmMessageDelete"])) {
			var request = new Ajax.Request(
				"index.php", {
					method: 'get',
					parameters: 'tx_newspaper_mod8[ajaxController]=deleteTag&tx_newspaper_mod8[tagUid]=' + tagUid + detachParam,
					onCreate: function() {
						NpBackend.showProgress();
					},
					onSuccess: function(data) {
						if (data) {
							var success = data.responseText.evalJSON().success;
							if (success) {
								$("message").innerHTML = NpTag.param["tagDeletedMesssage"];
								NpTag.fetchTags();
								NpBackend.hideProgress();
							} else {
								if (data.responseText.evalJSON().attachedTagsFound) {
									if (confirm(NpTag.param["confirmDetachMessage"])) {
										NpTag.deleteTag(true);
									} else {
										NpBackend.hideProgress();
									}
								} else {
									alert("Internal error: Could not delete tag.");
								}
							}
						}
					}
				}
			);
		}
	},


// Some function to control the backend

	/**
	 * Radio button control tag checked => read control tag categories
	 * @return void
	 */
	selectCtrlTagCat: function() {
		this.setCtrlTagCatDropdownVisibility(1);
		this.fetchTags();
	},

	/**
	 * Radio button content tag checked, read content tag list
	 * @return void
	 */
	selectContentTag: function() {
		this.setCtrlTagCatDropdownVisibility(0);
		this.fetchTags();
	},

	/**
	 * A control tag categories was selected
	 * @return void
	 */
	changeCtrlTagCat: function() {
		this.fetchTags();
	},

	//
	/**
	 * Show or hide control tag categorie dropdown
	 * @param state Boolean stating if the control tag categorie dropdown should be shown
	 * @return void
	 */
	setCtrlTagCatDropdownVisibility: function(state) {
		if (state) {
			$("ctrlTagCat").show();
		} else {
			$("ctrlTagCat").hide();
		}
	}

}
