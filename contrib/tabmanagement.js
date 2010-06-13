/**
 * Created by IntelliJ IDEA.
 * User: Ramon
 * Date: 08.06.2010
 * Time: 15:32:51
 * To change this template use File | Settings | File Templates.
 */

//////////////////// Functions for tabs
var TabManagement =  Class.create({

    initialize: function() {
        this.tabIds = [];
        this.activeTabClass = 'extra_tab_act';
        this.confirmMessage = confirmationMessage;
        this._hideAllTabs();
    },

    show: function(tabId, params) {
        var tableName = tabId.split('_');
        var id = tableName.pop();
        var isExtraTab = !isNaN(id); //last part of string is concrete id when its an extra tab

        tableName = tableName.join('_');
        var tab_id = isExtraTab ? tableName + '_' + id : tabId; //if it's no extra the passed in tabid was a real div#id

        this._hideAllTabs();

        //after an ajax reload the iframe must be loaded again but tabIds already contains the current tab_id
        //therefore check for empty div.
        // isExtraTab is true when the current tab is an extra and therefore the iframe must be loaded.
        if( ($(tab_id).innerHTML == "") && isExtraTab) {
            $(tab_id).innerHTML='<iframe height="840px" width="100%" name="'+tab_id+'" id="'+tab_id+'" src="alt_doc.php?returnUrl='+t3BackendObject.path+'typo3conf/ext/newspaper/mod3/res/closeTab.html&edit['+tableName+']['+id+']=edit""></iframe>';

            //after an ajax reload the tab_id is already inside the list
            if(!this.tabIds.include(tab_id)) {
                this.tabIds.push(tab_id);
            }
        }

        this.markActiveTab(tab_id);
        $('lastTab').value = tab_id;
        $(tab_id).show();
    },

    markActiveTab: function(tab_id) {
        $('extras').select('.' + this.activeTabClass).each(function(anchor) {
            anchor.removeClassName(this.activeTabClass);
        }, this);
        $('tab_'+ tab_id).select('a').each(function(a) {a.addClassName(this.activeTabClass)}, this);
    },

    /**
     *
     * @param saveInput savedok or saveandclosedok
     */
    submitTabs: function(saveInput) {
        for(var i = 0; i < this.tabIds.length; i++) {
            var frame = $(this.tabIds[i]);
            if(!frame || !frame.id) {
                alert("missing " + this.tabIds[i]);
                return false;
            }
            var iframeDok = top.window.frames[frame.id].document;

            //typo3 needs these coordinates somehow to properly save the article.
//                saveInput.name = '_saveandclosedok';
            ['.x', '.y'].each(function(suffix) {
                var saveDokInput = new Element('input', {type: 'hidden', name: '_saveandclosedok' + suffix, value: 1});
                iframeDok.forms[0].appendChild(saveDokInput);
            });
            $A(iframeDok.getElementsByName('doSave')).each(function(elem) { elem.value = 1 })
            iframeDok.forms[0].submit();
            //                var frameForm = iframeDok.forms[0];
////                alert(frameForm.action);
////                alert(Form.serialize(frameForm));
//                new Ajax.Request(frameForm.action, {
//                    parameters: Form.serialize(frameForm)
//                });
        }
        var tabsAreSaving = true;
        var count = 0;
        var keepAsking = false;
//            while(tabsAreSaving) {
//                //var openTabs = this.tabIds.findAll(function(it) { return top.window.frames[$(it).id].document.forms.length > 0}).size();
//                var openTabs = true;
//                for(var j = 0; j < this.tabIds.size(); j++) {
//                    try {
//                        if(keepAsking)
//                            alert(top.window.frames[this.tabIds[j]].document.body.id);
//                        tabsAreSaving &= top.window.frames[this.tabIds[j]].document.body.id == "";
//                    } catch(e) {
//                        openTabs = false;
//                        if(keepAsking)
//                            alert(this.tabIds[j] + " " + e);
//                    }
//                }
//                if(keepAsking)
//                    keepAsking = confirm("open tabs " + openTabs + " still saving..." + tabsAreSaving);
////                tabsAreSaving = !openTabs;
//                if(count > 10000) {
//                    alert('breaking out');
//                    break;
//                }
//                count++;
//            }
        //alert("open tabs " + openTabs);
        return false;
    },

    /**
     * hide all tabs, they must have a css-class called .extra_tab
     */
    _hideAllTabs: function() {
        $$('.extra_tab').each(function(div){ div.hide();});
    },

    askUserContinueIfDirty: function() {
        var allowSubmit = true;
        if(this.tabIds.size() > 0) {
            allowSubmit = confirm(this.confirmMessage);
        }
        return allowSubmit;
    },

    removeTab: function(extra_class, extra_uid) {
        this.tabIds = this.tabIds.without(extra_class + '_' + extra_uid);
    },

    clearTabCache: function() {
        this.tabIds = [];
    }

});

/**
 * Intercepts original function if there are unsaved iframes and warns user
 * @param func
 */
var interceptIfDirty = function(func) {
    //parameter orginalFunc is passed from wrap function itself
    return func.wrap(function(orginalFunc) {
                if(tabManagement.askUserContinueIfDirty()) {
                    var args = Array.prototype.slice.call(arguments, 1);
                    return orginalFunc.apply(this, args);
                }
            });
}


var tabManagement = null;
/**
 * Stuff that should be executed after the dom is loaded
 *
 */
document.observe('dom:loaded', function() {
    tabManagement = new TabManagement();
    tabManagement.show($('lastTab').value);

    //handling this inside a loop did not work
    extra_insert_after = interceptIfDirty(extra_insert_after);
    extra_move_after = interceptIfDirty(extra_move_after);
    extra_delete = interceptIfDirty(extra_delete);
    extra_shortcut_create = interceptIfDirty(extra_shortcut_create);
});

//////////// copied from mod3_new_extra some slightly modified for usage with tabs others just because of scope
function getChosenExtra() {
    if (document.getElementById('extra_list').selectedIndex < 0) {
        alert(msgNoExtraSelected);
        return false;
    }
    return document.getElementById('extra_list').value;
}

function getParagraph() {
    return parseInt(document.getElementById('paragraph').value);
}

function extra_insert_after_NEW(origin_uid, pz_uid, article_uid, in_article, paragraphUsed) {
    var target_uid = article_uid > 0? article_uid : pz_uid;
    var closehtml = (in_article)? escape("close_reload_in_concrete_article.html?pz_uid=" + target_uid) : "close.html";
//		var new_extra_paragraph_position_data = '';
//		 if(paragraphUsed) {
//		    new_extra_paragraph_position_data = '&paragraph=' + getParagraph();
//		 }
    var extra_class_sysfolder = getChosenExtra();
    var extra_class = top.splitAtPipe(extra_class_sysfolder, 0);
    var extra_sysfolder = top.splitAtPipe(extra_class_sysfolder, 1);
    if (!extra_class || !extra_sysfolder) {
        alert('Fatal error: Value in list of extras has wrong structure! Please contact developers!');
        return false;
    }
    extra_sysfolder = parseInt(extra_sysfolder);
    if (extra_class != false) { //extra is related to pagezone in save hook!
        var loc = top.path + "typo3conf/ext/newspaper/mod3/index.php";
        new Ajax.Request(loc, {
            parameters: {'extra_create' : 1, 'article_uid' : article_uid, 'extra_class' : extra_class, 'origin_uid' : origin_uid, 'pz_uid' : pz_uid, 'paragraph' : getParagraph()},
            onCreate: processing_in_article,
            onSuccess: function(transport) {
                if(transport) {
                    var  data = transport.responseJSON;
                    $('extras').innerHTML = data.content;
                    tabManagement.show(extra_class+'_'+data.extra_uid);
                }
            }
        });
    }
    return false;
}

function extra_insert_after_POOL(origin_uid, pz_uid, in_article, paragraphUsed) {
    if (in_article) alert("pool: in article not yet implemented");
        new_extra_paragraph_position_data = '';
    if(paragraphUsed) {
        new_extra_paragraph_position_data = '&paragraph=' + getParagraph();
    }
    extra_class_sysfolder = getChosenExtra();
    extra_class = top.splitAtPipe(extra_class_sysfolder, 0);
    if (extra_class == false) {
        alert('Fatal error: Value in list of extras has wrong structure! Please contact developers!');
        return false;
    }
    if (extra_class != false) {
//self.location.href = "index.php?chose_extra_from_pool=1&origin_uid=" + origin_uid + "&extra=" + extra_class + "&pool_extra_pz_uid=" + pz_uid + "&pool_extra_after_origin_uid=" + origin_uid + new_extra_paragraph_position_data
        self.location.href = "index.php?chose_extra_from_pool=1&origin_uid=" + origin_uid + "&extra=" + extra_class + "&pz_uid=" + pz_uid + new_extra_paragraph_position_data;
    }
}

