function showProgress () {
	$("#progress").css("display", "inline");
}


function hideProgress () {
	$("#progress").css("display", "none");
}


function showArticlePreview () {
	window.open(
		"/typo3conf/ext/newspaper/mod7/index.php?tx_newspaper_mod7[controller]=preview&tx_newspaper_mod7[articleid]=" + $("#placearticleuid").val(), 
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


function insertArticle (elementId) {
	$("#" + elementId).addOption(
		$("#placearticleuid").val(), 
		$("#placearticletitle").val()
	);
	$("#" + elementId).moveOptionsUp(true);
	$("#" + elementId).unselectAllOptions();
}


//could be optimised by doing it all in a single request
function saveAllSections () {
	$("select.placement-select").each(function(index, item){
		saveSection (item.id, false);
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


function closePlacement (noConfirmation) {
	if (noConfirmation == undefined) {
		noConfirmation = false;
	}
	
	if (noConfirmation || confirm(langReallycancel)) {	
		$("#placement").html("");
		$("#sections_selected").removeOption(/./);
		$("#sections_available").unselectAllOptions();
	}
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
						$("input.refresh[title=" + index + "]").css("color", "red");
					} else {
						$("input.refresh[title=" + index + "]").css("color", "black");
					}
				});
				hideProgress();
			});
	}
}


function executeFinalAction (action) {
	showProgress();
	$.get(
		"index.php?tx_newspaper_mod7[ajaxcontroller]=" + action, 
		$("#placementform").serialize(), 
		function (data) {
			if (data) {
				saveAllSections();
				closePlacement(true);
			} else {
				alert (langActiondidnotwork);
			}
			hideProgress();
		}
	);
}


function connectPlacementEvents () {
	
	
	$(".moveup").click(function() {
		$("#" + this.rel).moveOptionsUp(false, true);
  	});
	
	
	$(".movedown").click(function() {
		$("#" + this.rel).moveOptionsDown(false, true);
  	});
	
	
	$(".delete").click(function() {
		$("#" + this.rel).removeOption(/./, true);
  	});
	
	
	$(".insertarticle").click(function() {
		insertArticle(this.rel);
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
				hideProgress
			);	
		}		
  	});
	
	
	$(".save").click(function() {
		saveSection(this.title);
  	});
	
}


$(document).ready(function(){
	
	connectPlacementEvents();
	
	
	$("#save").click(function() {
		
		elementId = this.title;
		$("#" + elementId).selectAllOptions();
		showProgress();
		$.get(
			"index.php?tx_newspaper_mod7[ajaxcontroller]=showplacementandsave", 
			$("#placementform").serialize(), 
			function (data) {
				$("#placement").html(data);
				$("#" + elementId).unselectAllOptions();
				connectPlacementEvents();
				hideProgress();
			}
		);
		
  	});
	
	var refreshCheck = window.setInterval("checkForRefresh()", 15000);
	// window.clearInterval(refreshCheck);

	$("#checkrefresh").click(function() {
		checkForRefresh();
	});
	
	
	$(".addresort").click(function() {
		$("#" + this.id).copyOptions("#" + this.title, "selected");
		$("#" + this.id).unselectAllOptions();
		$("#" + this.title).unselectAllOptions();
  	});
	
	
	$(".cancel").click(function() {
		cancelPlacement();
	});
	
	
	$(".sendtocod").click(function() {
		executeFinalAction("sendarticletocod");
	});
	
	
	$(".sendtoeditor").click(function () {
		executeFinalAction("sendarticletoeditor");
	});
	
	
	$(".place").click(function () {
		executeFinalAction("placearticle");
	});
	
	
	$(".putonline").click(function () {
		executeFinalAction("putarticleonline");
	});
	
	
	$(".putoffline").click(function () {
		executeFinalAction("putarticleoffline");
	});	
	
	
	$("#filter").keyup(function () {
		filterAvailableSections();
	});
	
	
	$("#preview").click(function () {
		showArticlePreview();
	});
	
});