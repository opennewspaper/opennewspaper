/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Benjamin Mack <mack@xnos.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Handling of newspaper role menu
 */
var NewspaperRole = Class.create({
    ajaxScript:'ajax.php',
    menu:null,
    toolbarItemIcon:null,

    /**
     * Registers  resize event listener and executes on DOM ready
     */
    initialize:function () {
        Event.observe(window, 'resize', this.positionMenu);

        Event.observe(window, 'load', function () {
            this.positionMenu();
            this.toolbarItemIcon = $$('#tx-newspaper-role-menu .toolbar-item img')[0].src;
            this.ajaxScript = top.TS.PATH_typo3 + this.ajaxScript; // can't be initialized earlier

            Event.observe($$('#tx-newspaper-role-menu .toolbar-item')[0], 'click', this.toggleMenu);
            this.menu = $$('#tx-newspaper-role-menu .toolbar-item-menu')[0];
            this.toolbarItemIcon = $$('#shortcut-menu .toolbar-item img')[0].src;
        }.bindAsEventListener(this));
    },

    /**
     * Positions the menu below the toolbar icon, let's do some math!
     */
    positionMenu:function () {
        var calculatedOffset = 0;
        var parentWidth = $('tx-newspaper-role-menu').getWidth();
        var ownWidth = $$('#tx-newspaper-role-menu .toolbar-item-menu')[0].getWidth();
        var parentSiblings = $('tx-newspaper-role-menu').previousSiblings();

        parentSiblings.each(function (toolbarItem) {
            calculatedOffset += toolbarItem.getWidth() - 1;
            // -1 to compensate for the margin-right -1px of the list items,
            // which itself is necessary for overlaying the separator with the active state background

            if (toolbarItem.down().hasClassName('no-separator')) {
                calculatedOffset -= 1;
            }
        });
        calculatedOffset = calculatedOffset - ownWidth + parentWidth;


        $$('#tx-newspaper-role-menu .toolbar-item-menu')[0].setStyle({
            left:calculatedOffset + 'px'
        });
    },

    /**
     * Toggles the visibility of the menu and places it under the toolbar icon
     */
    toggleMenu:function (event) {
        var toolbarItem = $$('#tx-newspaper-role-menu > a')[0];
        var menu = $$('#tx-newspaper-role-menu .toolbar-item-menu')[0];
        toolbarItem.blur();

        if (!toolbarItem.hasClassName('toolbar-item-active')) {
            toolbarItem.addClassName('toolbar-item-active');
            Effect.Appear(menu, {duration:0.2});
            TYPO3BackendToolbarManager.hideOthers(toolbarItem);
        } else {
            toolbarItem.removeClassName('toolbar-item-active');
            Effect.Fade(menu, {duration:0.1});
        }

        if (event) {
            Event.stop(event);
        }
    },

    /**
     * Displays the menu and does the AJAX call to the TYPO3 backend
     */
    updateMenu:function () {
        var origToolbarItemIcon = this.toolbarItemIcon.src;
        this.toolbarItemIcon.src = 'gfx/spinner.gif';

        new Ajax.Updater(
            this.menu,
            this.ajaxScript, {
                parameters:{
                    ajaxID:'tx_newspaper_role::renderMenu'
                },
                onComplete:function (xhr) {
                    this.toolbarItemIcon.src = origToolbarItemIcon;
                }.bind(this)
            }
        );
    },

    /**
     * Updates newspaper role display
     * @param    String        Localized title of newspaper role
     * @param    boolean        flag to explicitly update the menu
     */
    updateRole:function (role, explicitlyUpdateMenu) {
        if (explicitlyUpdateMenu) {
            // re-render the menu e.g. if a document was closed inside the menu
            this.updateMenu();
        }
        $('tx-newspaper-role-role').writeAttribute('value', role);
    },

    /// Changes role to editorial staff (and update the backend)
    changeRoleToEditorialStaff:function () {
        this.changeRole('tx_newspaper_role::changeRoleToEditorialStaff');
    },
    /// Changes role to duty editor (and update the backend)
    changeRoleToDutyEditor:function () {
        this.changeRole('tx_newspaper_role::changeRoleToDutyEditor');
    },
    /**
     * Change the role, update label in menu and reload production list (if visible)
     * @param newRole
     */
    changeRole:function (newRole) {
        var request = new Ajax.Request(
            this.ajaxScript, {
                method:'get',
                parameters:'ajaxID=' + newRole,
                onSuccess:function (xhr) {
                    data = xhr.responseText.evalJSON(true).newspaperRoleMenu.evalJSON(true);
                    $("tx-newspaper-role-menu").children[1].update();
                    $("tx-newspaper-role-menu").children[1].insert(data.menu); // Show new menu
                    $("tx-newspaper-role-role").value = data.roleLabel; // Update label
                    TYPO3BackendNewspaperRole.updateProductionList();
                }
            }
        );
    },
    /**
     * Reload production list module (if production list is active)
     */
    updateProductionList:function () {
        if (top.TYPO3.ModuleMenu.App.loadedModule == 'txnewspaperMmain_txnewspaperM2') {
            // Reload module, so the new role can be added to the filter settings
            top.TYPO3.ModuleMenu.App.showModule('txnewspaperMmain_txnewspaperM2', '');
        }
    }

});

var TYPO3BackendNewspaperRole = new NewspaperRole();
