function showProgress () {
	$("#progress").css("display", "inline");
}


function hideProgress () {
	$("#progress").css("display", "none");
}


function insertArticle (elementId) {
	$("#" + elementId).addOption(
		$("#placearticleuid").val(), 
		$("#placearticletitle").val()
	);
	$("#" + elementId).moveOptionsUp(true);
	$("#" + elementId).unselectAllOptions();
}


//@todo: could be optimised by doing it all in a single request
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
				alert("Das Speichern hat nicht funktoniert.");
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
	
	if (noConfirmation || confirm("Wirklich abbrechen und alle Aenderungen verwerfen?")) {	
		$("#placement").html("");
		$("#sections_selected").removeOption(/./);
		$("#sections_available").unselectAllOptions();
	}
}


function checkForRefresh () {
	allSections = collectSections();
	$("select.placement-select").each(function(index, item){
		$(item).selectAllOptions();		
	});
	showProgress();
	$.getJSON(
		"index.php?tx_newspaper_mod7[ajaxcontroller]=checkarticlelistsforupdates&tx_newspaper_mod7[sections]=" + sections.join("|"), 
		$("#placementform").serialize(), 
		function (data) {
			$.each(data, function(index, item) {
				if (!item) {
					$("input.refresh[title=" + index + "]").css("color", "red");
				} else {
					$("input.refresh[title=" + index + "]").css("color", "black");
				}
			});
			$("select.placement-select").each(function(index, item){
				$(item).unselectAllOptions();		
			});
			hideProgress();
		}
	);
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
				alert ("Diese Aktion hat leider nicht funktioniert.");
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
		if (confirm("Wollen Sie wirklich diese Liste aktualisieren?\n Ungespeicherte Aenderungen gehen verloren.")) {
			$("#" + this.title).selectAllOptions();
			$("#" + this.title).removeOption(/./, true);
			showProgress();
			$("#" + this.title).ajaxAddOption(
				"index.php?tx_newspaper_mod7[ajaxcontroller]=updatearticlelist",
				{"tx_newspaper_mod7[section]" : this.title, "tx_newspaper_mod7[articleid]" : $("#placearticleuid").val()}, 
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
	
	
	$("#preview").click(function() {
		
		elementId = this.title;
		$("#" + elementId).selectAllOptions();
		showProgress();
		$.get(
			"index.php?tx_newspaper_mod7[ajaxcontroller]=showplacement", 
			$("#placementform").serialize(), 
			function (data) {
				$("#placement").html(data);
				$("#" + elementId).unselectAllOptions();
				connectPlacementEvents();
				hideProgress();
			}
		);
		
  	});
	
	
	// good idea?
	var refreshCheck = window.setInterval("checkForRefresh()", 15000);
	// window.clearInterval(refreshCheck);
	// alternative:
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
	
	
});