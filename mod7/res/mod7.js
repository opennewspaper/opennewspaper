// get absolute path
var path = window.location.pathname;
path = path.substring(0, path.lastIndexOf("/") - 5); // -5 -> cut of "typo3"


var refreshCheck;

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
		"width=600,height=400,left=100,top=200,resizable=yes,toolbar=no,location=no"
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
	return $("#placearticleuid")[0].defaultValue;
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
	hideProgress(); // displayInsertOrDelButton() might be called when refrshinh a list with AJAX
}



// \todo: can be optimized by saving all in a single request
function saveAllSections () {
	$("select.placement-select").each(function(index, item) {
		saveIt = false;
		$("input[title='" + item.id + "']").each(function(index, item) {
			if ($(item).hasClass("unsaved")) {
				saveIt = true;		
			}	
  		});
		if (saveIt) {
			saveSection(item.id, false);
		}
	});
}


function saveSection (elementId, async) {
	if (async == undefined) {
		async = true;
	}
	
	$("#" + elementId).selectAllOptions();
	showProgress();
	jQuery.ajax({
		url: "index.php?tx_newspaper_mod7[ajaxcontroller]=savesection&tx_newspaper_mod7[section]=" + elementId + "&tx_newspaper_mod7[articleids]=" + $("#" + elementId).selectedValues().join("|"),
		success: function (data) {
			if (!data) {
				alert(langSavedidnotwork);
			}
			$("#" + elementId).unselectAllOptions();
			hideProgress();
		},
		async: async
	});
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


function checkForRefresh () {
	allSections = collectSections();
	if (allSections.length > 0) {
		
		//we collect the values of the selects manually so that we do not have
		//to select and unselect all options visually when the user is working
		//on them we build them as the following string:
		//placer_x_y:12|23|1212/player_a_b:1234|-2/...
		//and unpack all this in php - there seems no better way to achieve this
		//without real associative arrays in javascript that are serialisable
		var allSelectValues = new Array();
		for (i = 0; i < allSections.length; ++i) {
			selectValues = new Array();
			$("select#" + allSections[i] + " option").each(function(index, item) {
				selectValues.push($(item).val());
			});		
			selectValues = allSections[i] + ":" + selectValues.join("|")	
			allSelectValues.push(selectValues);
		}
		allSelectValues = allSelectValues.join("/");
		
		showProgress();
		$.getJSON(
			"index.php?tx_newspaper_mod7[ajaxcontroller]=checkarticlelistsforupdates&tx_newspaper_mod7[sectionvalues]=" + allSelectValues + "&tx_newspaper_mod7[sections]=" + sections.join("|"), 
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


function executeAJAX (action, close) {
	if (close == undefined) {
		close = true;
	}
	showProgress();
	$.get(
		"index.php?tx_newspaper_mod7[ajaxcontroller]=" + action, 
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

function connectPlacementEvents () {
	
	$("table.articles .moveup, table.articles .movedown, table.articles .delete, table.articles .insertarticle, .removearticletobeplaced, table.articles .movetotop, table.articles .movetobottom").click(function() {
		$("input.save[title=" +  this.rel + "]").addClass("unsaved");
		$("input#saveall").addClass("unsaved");
		startCheckCountdown();
		return false;
  	});
	
	$("table.articles .movetotop").click(function() {
		$("#" + this.rel).moveOptionsUp(true, true);
		return false;
  	});
	
	$("table.articles .movetobottom").click(function() {
		$("#" + this.rel).moveOptionsDown(true, true);
		return false;
  	});
	
	$("table.articles .moveup").click(function() {
		$("#" + this.rel).moveOptionsUp(false, true);
		return false;
  	});
	
	$("table.articles .movedown").click(function() {
		$("#" + this.rel).moveOptionsDown(false, true);
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
		if (confirm(langReallyrefresh)) {
			$("#" + this.title).selectAllOptions();
			$("#" + this.title).removeOption(/./, true);
			showProgress();
			$("#" + this.title).ajaxAddOption(
				"index.php?tx_newspaper_mod7[ajaxcontroller]=updatearticlelist",
				{"tx_newspaper_mod7[section]" : this.title, "tx_newspaper_mod7[placearticleuid]" : $("#placearticleuid").val()}, 
				false,
				displayInsertOrDelButton, ["#" + this.title] //displayInsertOrDelButton, [{"elementId": "#" + this.title}]
			);	
		}
		$("input.refresh[title=" +  this.title + "]").removeClass("unsaved");
		$("input.save[title=" +  this.title + "]").removeClass("unsaved");
		if (everythingSaved()) {
			$("input#saveall").removeClass("unsaved");
		}
		return false;
  	});
	
	
	$(".save").click(function() {
		saveSection(this.title);
		$("input.refresh[title=" +  this.title + "]").removeClass("unsaved");
		$("input.save[title=" +  this.title + "]").removeClass("unsaved");
		if (everythingSaved()) {
			$("input#saveall").removeClass("unsaved");
		}
		return false;
  	});
	
	
}


function startCheckCountdown () {
	window.clearInterval(refreshCheck);
	refreshCheck = window.setInterval("checkForRefresh()", 60000);
}


function saveSections() {
		elementId = $("#savesections").attr("title");
		$("#" + elementId).selectAllOptions();
		showProgress();
		$.get(
			"index.php?tx_newspaper_mod7[ajaxcontroller]=showplacementandsavesections", 
			$("#placementform").serialize(), 
			function (data) {
				$("#placement").html(data);
				$("#" + elementId).unselectAllOptions();
				connectPlacementEvents();
				hideProgress();
			}
		);
		return false;	
}



$(document).ready(function(){
	
	connectSectionEvents();
	
	$("#savesections").click(function() {
		saveSections();
  	});
	saveSections();
	
	startCheckCountdown();

	$("#checkrefresh").click(function() {
		checkForRefresh();
		return false;
	});
	
	
	$(".addresort").click(function() {
		$("#" + this.id).copyOptions("#" + this.title, "selected");
		$("#" + this.id).unselectAllOptions();
		$("#" + this.title).unselectAllOptions();
		return false;
  	});
	
	
	$(".cancel").click(function() {
		closePlacement();
		return false;
	});
	
	
	$(".sendtodutyeditor").click(function() {
		executeAJAX("sendarticletodutyeditor");
		return false;
	});
	
	
	$(".sendtoeditor").click(function () {
		executeAJAX("sendarticletoeditor");
		return false;
	});
	
	
	$(".place").click(function () {
		executeAJAX("placearticle");
		return false;
	});
	
	
	$(".putonline").click(function () {
		executeAJAX("putarticleonline");
		return false;
	});
	
	
	$(".putoffline").click(function () {
		executeAJAX("putarticleoffline");
		return false;
	});	
	
	
	$(".saveall").click(function () {
		executeAJAX("justsave", false);
		return false;
	});
	
	$("#filter").keyup(function () {
		filterAvailableSections();
		return false;
	});
	
	
	$("#preview").click(function () {
		showArticlePreview();
		return false;
	});
	
});