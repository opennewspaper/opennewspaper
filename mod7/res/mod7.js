// get absolute path
var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"
if (path.indexOf('typo3conf/ext/newspaper') == -1) {
	path += 'typo3conf/ext/newspaper'; // modify path if called from within a module
}

var refreshCheck;

//hide tablecells containing only hidden elements
function hideEmptyTablecells() {
    $('#placement > #hide-empty tr').children().each(function() {
            var visibleKids = $(this).children().is(':visible');
                if(!visibleKids && $(this).context.tagName == 'TD') {
                    $(this).hide();
                }
            });
}

// show spinner
function showProgress() {
	$("#progress").css("display", "inline");
}


// hide spinner
function hideProgress() {
	$("#progress").css("display", "none");
}

// open preview window
function showArticlePreview() {
	window.open(
		path + "/mod7/index.php?tx_newspaper_mod7[controller]=preview&tx_newspaper_mod7[articleid]=" + $("#placearticleuid").val(),
		"preview",
		"width=800,height=500,left=100,top=100,resizable=yes,toolbar=no,location=no,scrollbars=yes"
	);
}


function filterAvailableSections () {
	filter = $("#filter").val();
	$("select#sections_available option").each(function(index, item){
		if ($(item).text() != "" && $(item).text().toLowerCase().indexOf(filter.toLowerCase()) == -1) {
			$(item).css("display", "none");
		} else {
			$(item).css("display", "");
		}
	});
}

// return uid of article to be placed
function getArticleToBePlacedUid() {
	return ($("#placearticleuid").length? $("#placearticleuid")[0].defaultValue : 0);
}

// insert article in manual article list
function insertArticle(elementId) {
	$("#" + elementId).addOption(
		$("#placearticleuid").val(),
		$("#placearticletitle").val()
	);
	$("#" + elementId).moveOptionsUp(true); // move article to top
	$("#" + elementId).selectOptions($("#placearticleuid").val(), true); // select added article
}

// remove article to be placed from list (no matter what articles are selected)
function removeArticleToBePlaced(elementId) {
	if ($("#" + elementId).containsOption(getArticleToBePlacedUid())) {
		selection = $(elementId.selector).selectedValues; // store old selection
		$(elementId.selector); // remove article to be placed
		$("#" + elementId).removeOption(getArticleToBePlacedUid());
		$("#" + elementId).selectedOptions = selection; // restore old selection
	}
}

function displayInsertOrDelButton(elementId){
	if (typeof elementId == "string") {
		buttonListId = elementId.substring(8);
	} else {
		buttonListId = elementId.selector.substring(8);
	}
	if ($(elementId.selector).containsOption(getArticleToBePlacedUid())) {
		// article to be placed is PLACED in list
		$("#addbutton_" + buttonListId).hide();
		$("#delbutton_" + buttonListId).show();
	} else {
		// article to be placed is MISSING in list
		$("#addbutton_" + buttonListId).show();
		$("#delbutton_" + buttonListId).hide();
	}
	hideProgress(); // displayInsertOrDelButton() might be called when refreshing a list with AJAX
}

function isDirty() {
    var saveIt = false;
    $("select.placement-select").each(function(index, item) {
        $("input[title='" + item.id + "']").each(function(index, item) {
            if ($(item).hasClass("unsaved")) {
                saveIt = true;
			}
  		});
	});
    return saveIt;
}

// \todo: can be optimized by saving all in a single request
function saveAllSections () {
	$("select.placement-select").each(function(index, item) {
		var saveIt = false;
		$("input[title='" + item.id + "']").each(function(index, item) {
			if ($(item).hasClass("unsaved")) {
				saveIt = true;
			}
  		});
		if (saveIt) {
			saveArticleList(item.id, false);
		}
	});
}


function saveArticleList(elementId, async) {
//console.log(stacktrace());
	if (async == undefined) {
		async = true;
	}
    var selectedOption = $("#" + elementId).getSelectedOption();
    $('#' + elementId).attr('multiple', 'multiple');
	$("#" + elementId).selectAllOptions();
	showProgress();
	jQuery.ajax({
		url: path + "/mod7/index.php?tx_newspaper_mod7[ajaxcontroller]=savearticlelist&tx_newspaper_mod7[element]=" + elementId + "&tx_newspaper_mod7[articleids]=" + $("#" + elementId).selectedValues().join("|"),
		success: function (data) {
			if (!data) {
				alert(langSavedidnotwork);
			}
			$("#" + elementId).unselectAllOptions();
            $("#" + elementId).removeAttr('multiple');
            reselectLastOption(elementId, selectedOption.value);
			hideProgress();
		},
		async: async
	});
}

function resortArticleList(elementId, action, async) {
//console.log(stacktrace());
    if($('#' + elementId).hasClass('manual-list')) {
        switch(action) {
        case  'top' :
            $('#' + elementId).moveOptionsUp(true, true);
            break;
        case 'bottom' :
            $('#' + elementId).moveOptionsDown(true, true);
            break;
        case 'moveup' :
            $('#' + elementId).moveOptionsUp(false, true);
            break;
        case 'movedown' :
            $('#' + elementId).moveOptionsDown(false, true);
            break;
        default:
            console.log('action "' + action +"' not found");
            break;
        }
    } else {
        if (async == undefined) {
            async = true;
        }
        var selectedOption = $("#" + elementId).getSelectedOption();
        if(selectedOption) {
            var articleids = $("#" + elementId).getOptionValues().join('|');
            showProgress();
            jQuery.ajax({
                url: path + "/mod7/index.php?tx_newspaper_mod7[ajaxcontroller]="+action+"&tx_newspaper_mod7[sel_article_id]="+selectedOption.value+"&tx_newspaper_mod7[element]=" + elementId + "&tx_newspaper_mod7[articleids]=" + articleids,
                dataType: 'json',
                success: function (data) {
                    if (data) {
                        updateArticleList(elementId, data, selectedOption.value);
                    } else {
                        alert(langSavedidnotwork);
                    }
                    hideProgress();
                },
                async: async
            });
        }
    }
}

function updateArticleList(listId, offsetAndId, lastSelectedOption) {
    var newOptions = {};
    $.each(offsetAndId, function(index, item){
        $('#' +  listId + ' > option ').each(function() {
            var artId = item[1] ;
            //search all option values and compare by article id because offset has changed
            if($(this).val().lastIndexOf(artId) > -1 ) {
                var newText = $(this).text().split('(');
                if(item[0] > 0) {
                    newOptions[item.join('_')] = newText[0] + '(+' + item[0] + ')';
                } else {
                    newOptions[item.join('_')] = newText[0] + '(' + item[0] + ')';
                }
                return;
            }
        });
    });

    $('#' + listId).removeOption(/./); //removes all
    $('#' + listId).addOption(newOptions, false); //add but don't select

    //re-select last selected option
    reselectLastOption(listId, lastSelectedOption);
}

function reselectLastOption(listId, lastSelectedOption) {
    var regExp = lastSelectedOption;
	if (lastSelectedOption) {
		if (lastSelectedOption.indexOf('_') > -1) {
			var artId = lastSelectedOption.split('_').pop();
			regExp = new RegExp('_' + artId + '$');
		}
		$('#' + listId).selectOptions(regExp);
	}
}


function collectSections () {
	sections = new Array ();
	$(".refresh").each(function(index, item) {
		sections.push(item.title);
  	});
	return sections;
}


function closePlacement() {
	top.goToModule('txnewspaperMmain_txnewspaperM2');
	return false;
}


/*
 * re-activate once the check is fixed ...
function checkForRefresh () {
	var allSections = collectSections();
	if (allSections.length > 0) {

		//we collect the values of the selects manually so that we do not have
		//to select and unselect all options visually when the user is working
		//on them we build them as the following string:
		//placer_x_y:12|23|1212/player_a_b:1234|-2/...
		//and unpack all this in php - there seems no better way to achieve this
		//without real associative arrays in javascript that are serialisable
		var allSelectValues = new Array();
		for (var i = 0; i < allSections.length; ++i) {
			var selectValues = new Array();
			$("select#" + allSections[i] + " option").each(function(index, item) {
				selectValues.push($(item).val());
			});
			selectValues = allSections[i] + ":" + selectValues.join("|")
			allSelectValues.push(selectValues);
		}
		allSelectValues = allSelectValues.join("/");

		showProgress();
		$.getJSON(
			path + "/mod7/index.php?tx_newspaper_mod7[ajaxcontroller]=checkarticlelistsforupdates&tx_newspaper_mod7[sectionvalues]=" + allSelectValues + "&tx_newspaper_mod7[sections]=" + sections.join("|"),
			$("#placementform").serialize(),
			function(data) {
				$.each(data, function(index, item){
					if (!item) {
						$("input.refresh[title=" + index + "]").addClass("unsaved");
					} else {
						$("input.refresh[title=" + index + "]").removeClass("unsaved");
					}
				});
				hideProgress();
			}
		);
	}
}
*/


function executeAJAX (action, close) {
	if (close == undefined) {
		close = true;
	}
	showProgress();
	$.get(
		path + "/mod7/index.php?tx_newspaper_mod7[ajaxcontroller]=" + action,
		$("#placementform").serialize(),
		function (data) {
			if (data) {
				saveAllSections();
				close? closePlacement() : '';
				$("input.save").removeClass("unsaved");
				$("input#saveall").removeClass("unsaved");
			} else {
				alert (langActiondidnotwork);
			}
			hideProgress();
		}
	);
}


function everythingSaved () {
	var result = true;
	$("input.save").each(function(index, item) {
		if ($(item).hasClass("unsaved")) {
			result = false;
		}
	});
	return result;
}


function connectSectionEvents(){
	$("table.sections .movetotop").click(function() {
		$("#" + this.rel).moveOptionsUp(true, true);
		return false;
  	});
	$("table.sections .movetobottom").click(function() {
		$("#" + this.rel).moveOptionsDown(true, true);
		return false;
  	});
	$("table.sections .moveup").click(function() {
		$("#" + this.rel).moveOptionsUp(false, true);
		return false;
  	});
	$("table.sections .movedown").click(function() {
		$("#" + this.rel).moveOptionsDown(false, true);
		return false;
  	});
	$("table.sections .delete").click(function() {
		$("#" + this.rel).removeOption(/./, true);
		return false;
  	});
}

function connectPlacementEvents() {
	$("table.articles .moveup, table.articles .movedown, table.articles .delete, table.articles .insertarticle, .removearticletobeplaced, table.articles .movetotop, table.articles .movetobottom").click(function() {
		$("input.save[title=" +  this.rel + "]").addClass("unsaved");
		$("input#saveall").addClass("unsaved");
		startCheckCountdown();
		return false;
  	});
	$("table.articles .movetotop").click(function() {
        resortArticleList(this.rel, 'top', true);
		return false;
  	});
	$("table.articles .movetobottom").click(function() {
        resortArticleList(this.rel, 'bottom', true);
		return false;
  	});
	$("table.articles .moveup").click(function() {
        resortArticleList(this.rel, 'moveup', true);
		return false;
  	});
	$("table.articles .movedown").click(function() {
        resortArticleList(this.rel, 'movedown', true);
		return false;
  	});
	$("table.articles .delete").click(function() {
		$("#" + this.rel).removeOption(/./, true);
		displayInsertOrDelButton($("#" + this.rel));
		return false;
  	});
	$(".insertarticle").click(function() {
		insertArticle(this.rel);
		displayInsertOrDelButton($("#" + this.rel));
		return false;
  	});
	$(".removearticletobeplaced").click(function() {
		removeArticleToBePlaced(this.rel);
		displayInsertOrDelButton($("#" + this.rel));
		return false;
  	});
	$(".refresh").click(function() {
		var doRefresh = true;
        if (isDirty()) {
            doRefresh = confirm(langReallyrefresh)
        }
        if(doRefresh) {
            $('#' + this.title).attr('multiple', 'multiple');
            $("#" + this.title).selectAllOptions();
            $("#" + this.title).removeOption(/./, true);
            showProgress();
            $("#" + this.title).ajaxAddOption(
                path + "/mod7/index.php?tx_newspaper_mod7[ajaxcontroller]=updatearticlelist", {
                    "tx_newspaper_mod7[element]" : this.title,
                    "tx_newspaper_mod7[placearticleuid]" : ($("#placearticleuid").length? $("#placearticleuid").val() : 0)
                },
                false,
                displayInsertOrDelButton, ["#" + this.title] //displayInsertOrDelButton, [{"elementId": "#" + this.title}]
            );
    		$("input.refresh[title=" +  this.title + "]").removeClass("unsaved");
	    	$("input.save[title=" +  this.title + "]").removeClass("unsaved");
            $('#' + this.title).removeAttr('multiple');
        }
		if (everythingSaved()) {
			$("input#saveall").removeClass("unsaved");
		}
		return false;
  	});
	$(".save").click(function() {
		if(isDirty()) {
            saveArticleList(this.title);
            $("input.refresh[title=" +  this.title + "]").removeClass("unsaved");
            $("input.save[title=" +  this.title + "]").removeClass("unsaved");
            if (everythingSaved()) {
                $("input#saveall").removeClass("unsaved");
            }
        }
		return false;
  	});
}


function startCheckCountdown () {
// re-activate once the check is fixed ...
//	window.clearInterval(refreshCheck);
//	refreshCheck = window.setInterval("checkForRefresh()", 60000);
}


function saveSections() {
		elementId = $("#savesections").attr("title");
		$("#" + elementId).selectAllOptions();
		showProgress();
		$.get(
			path + "/mod7/index.php?tx_newspaper_mod7[ajaxcontroller]=showplacementandsavesections",
			$("#placementform").serialize(),
			function (data) {
				$("#placement").html(data);
				$("#" + elementId).unselectAllOptions();
				connectPlacementEvents();
				hideEmptyTablecells();
                hideProgress();
			}
		);
		return false;
}

function setFormValueOpenBrowser_AL(selectBoxId, section_filter) {
    var url = path + '/mod2/index.php?ab4al=1&select_box_id=' + selectBoxId;
	if (section_filter) {
		url += '&s=' + escape(section_filter);
	}
    browserWin = window.open(url,"Typo3WinBrowser","height=350,width=650,status=0,menubar=0,resizable=1,scrollbars=1");
    browserWin.focus();
}



$(document).ready(function(){

	if ($("#savesections").length) {
		// execute only if full articlelist placement module (don't include if articlelist placement is standalone version)
		connectSectionEvents();
 		$("#savesections").click(function(){
			saveSections();
		});
		saveSections();
	} else {
		// add listener to articlelist selectbox
		connectPlacementEvents();
	}

	startCheckCountdown();

//	$("#checkrefresh").click(function() {
//		checkForRefresh();
//		return false;
//	});


	if ($(".addresort").length) {
		$(".addresort").click(function() {
			$("#" + this.id).copyOptions("#" + this.title, "selected");
			$("#" + this.id).unselectAllOptions();
			$("#" + this.title).unselectAllOptions();
			return false;
	  	});
	}

	if ($(".cancel").length) {
		$(".cancel").click(function() {
			closePlacement();
			return false;
		});
	}

	if ($(".sendtodutyeditor").length) {
		$(".sendtodutyeditor").click(function() {
			executeAJAX("sendarticletodutyeditor");
			return false;
		});
	}

	if ($(".sendtodutyeditorhide").length) {
		$(".sendtodutyeditorhide").click(function() {
			executeAJAX("sendarticletodutyeditorhide");
			return false;
		});
	}

	if ($(".sendtodutyeditorpublish").length) {
		$(".sendtodutyeditorpublish").click(function(){
			executeAJAX("sendarticletodutyeditorpublish");
			return false;
		});
	}

	if ($(".sendtoeditor").length) {
		$(".sendtoeditor").click(function () {
			executeAJAX("sendarticletoeditor");
			return false;
		});
	}

	if ($(".sendtoeditorhide").length) {
		$(".sendtoeditorhide").click(function () {
			executeAJAX("sendarticletoeditorhide");
			return false;
		});
	}

	if ($(".sendtoeditorpublish").length) {
		$(".sendtoeditorpublish").click(function () {
			executeAJAX("sendarticletoeditorpublish");
			return false;
		});
	}

	if ($(".place").length) {
		$(".place").click(function () {
			executeAJAX("placearticle");
			return false;
		});
	}

	if ($(".placehide").length) {
		$(".placehide").click(function () {
			executeAJAX("placearticlehide");
			return false;
		});
	}

	if ($(".placepublish").length) {
		$(".placepublish").click(function(){
			executeAJAX("placearticlepublish");
			return false;
		});
	}

	if ($(".putonline").length) {
		$(".putonline").click(function(){
			executeAJAX("putarticleonline");
			return false;
		});
	}

	if ($(".putoffline").length) {
		$(".putoffline").click(function(){
			executeAJAX("putarticleoffline");
			return false;
		});
	}

	if ($(".saveall").length) {
		$(".saveall").click(function () {
			executeAJAX("justsave", false);
			return false;
		});
	}

	if ($("#filter").length) {
		$("#filter").keyup(function () {
			filterAvailableSections();
			return false;
		});
	}

	if ($("#preview").length) {
		$("#preview").click(function () {
			showArticlePreview();
			return false;
		});
	}

});




// http://www.gettingclever.com/2008/06/javascript-stacktrace.html
function stacktrace() {
 re = /function\W+([\w-]+)/i;
 var f = arguments.callee;
 var s = "";
 while (f) {
  s += (re.exec(f))[1] + '(';
  for (i = 0; i < f.arguments.length - 1; i++) {
   s += "'" + f.arguments[i] + "', ";
  }
  if (f.arguments.length > 0) {
   s += "'" + f.arguments[i] + "'";
  }
  s += ")\n\n";
  f = f.arguments.callee.caller;
 }
 return s;
}
