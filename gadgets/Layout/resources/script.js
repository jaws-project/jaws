/**
 * Layout Javascript actions
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Use async mode, create Callback
 */
var LayoutCallback = {
    editelementaction: function(response) {
        showResponse(response, false);
    },

    changedisplaywhen: function(response) {
        showResponse(response, false);
    },

    moveelement: function(response) {
        showResponse(response, false);
    },

    deleteelement: function(response) {
        showResponse(response, false);
    },

    addgadget: function(response) {
        if (response['success']) {
            //$('layout_main').appendChild(document.createTextNode(response['elementbox']));
            // Fragile!, it must be equal to LayoutItem.html template
            var dItem = document.createElement('div');
            dItem.setAttribute('class', 'item');
            dItem.setAttribute('className', 'item');
            dItem.setAttribute('id', 'item_' + response['id']);
            dItem.setAttribute('title', response['tactiondesc']);

            var dItemIcon = dItem.appendChild(document.createElement('div'));
            dItemIcon.setAttribute('class', 'item-icon');
            dItemIcon.setAttribute('className', 'item-icon');
            var imgIcon = document.createElement('img');
            imgIcon.setAttribute('alt', 'icon');
            imgIcon.setAttribute('src', response['icon']);
            dItemIcon.appendChild(imgIcon);

            var dItemDelete = dItem.appendChild(document.createElement('div'));
            dItemDelete.setAttribute('class', 'item-delete');
            dItemDelete.setAttribute('className', 'item-delete');
            var adel = document.createElement('a');
            adel.setAttribute('href', 'javascript:void(0);');
            adel.setAttribute('onclick', response['delete']);
            imgdel = document.createElement('img');
            imgdel.setAttribute('src', response['deleteimg']);
            adel.appendChild(imgdel);
            dItemDelete.appendChild(adel);

            var dItemGadget = dItem.appendChild(document.createElement('div'));
            dItemGadget.setAttribute('class', 'item-gadget');
            dItemGadget.setAttribute('className', 'item-gadget');
            dItemGadget.appendChild(document.createTextNode(response['tname']));

            var dItemAction = dItem.appendChild(document.createElement('div'));
            dItemAction.setAttribute('class', 'item-action');
            dItemAction.setAttribute('className', 'item-action');
            aea = document.createElement('a');
            aea.setAttribute('href', 'javascript:void(0);');
            aea.setAttribute('onclick', response['eaonclick']);
            aea.setAttribute('id', response['eaid']);
            aea.setAttribute('name', response['eaid']);
            aea.setAttribute('title', response['tactiondesc']);
            aea.appendChild(document.createTextNode(response['taction']));
            dItemAction.appendChild(aea);

            var dItemDw = dItem.appendChild(document.createElement('div'));
            dItemDw.setAttribute('class', 'item-dw');
            dItemDw.setAttribute('className', 'item-dw');
            adw = document.createElement('a');
            adw.setAttribute('href', 'javascript:void(0);');
            adw.setAttribute('onclick', response['dwonclick']);
            adw.setAttribute('id', response['dwid']);
            adw.setAttribute('name', response['dwid']);
            adw.setAttribute('title', response['dwtitle']);
            adw.appendChild(document.createTextNode(response['dwalways']));
            dItemDw.appendChild(document.createTextNode(response['dwdisplay']));
            dItemDw.appendChild(adw);

            $('layout_main').appendChild(dItem);

            Effect.Appear(dItem.id, {duration:1});
            items['main']['item_' + response['id']] = true; 
            newdrags[response['id']] = new Draggable('item_' + response['id'], {revert:true,constraint:true});
            
        }
        showResponse(response['message'], false);
    }
}

/**
 * Move items from one side to other
 */
function moveItems(fromSection, toSection)
{
    var fromSectionName = 'layout_' + fromSection;
    var toSectionName   = 'layout_' + toSection;

    var fromSectionObj   = document.getElementById(fromSectionName);
    var toSectionObj     = document.getElementById(toSectionName);

    var itemsFromSection = $$('#'+fromSectionName + ' .item');

    items[fromSection] = new Array();
    items[toSection]   = new Array();
    for(var i=0; i<itemsFromSection.length; i++) {
        var item = itemsFromSection[i];

        fromSectionObj.removeChild(item);
        toSectionObj.appendChild(item);

        items[toSection][item.id] = true;
    }
}

/**
 * Returns the position on the current item and section
 */
function getPositionOfItem(item, section)
{
    item = item.replace('item_', '');
    
    var pos = 1;
    for(key in items[section]) {
        if (typeof(items[section][key]) == 'function') {
            continue;
        }
        var itemName = key.replace('item_', '');
        if (itemName == item) {
            return pos;
        }
        pos++;
    }
    return pos;
}

/**
 * Move an element to another section
 */
function moveElement(element, movedTo)
{
    var comesFrom = element.parentNode;
    var goesTo    = $(movedTo.id.replace('_drop', ''));
    var clone     = element;

    goesTo.appendChild(element);

    var destiny   = document.getElementById(movedTo.id.replace('_drop', ''));
    var emptyDivs = $$('#'+movedTo.id.replace('_drop', '') + ' .layout-message');

    if (emptyDivs.length == 1) {
        destiny.removeChild(emptyDivs[0]);
    }

    movedNewElement = true;
    newEmptyRegion  = goesTo.id;
    // $('log').innerHTML += 'Moved ' + element.id + ' from ' + comesFrom.id + ' to ' + goesTo.id + '<br />';
}

/**
 * Returns true if the total items of a section has changed
 */
function itemMovedOnSameSection(section, serialized)
{
    var sectionName  = section.replace('layout_', '');
    var newItemsSize = serialized.split('&').length;

    var totalItems = 0;
    for(key in items[sectionName]) {
        if (typeof(items[sectionName][key]) == 'function') {
            continue;
        }
        totalItems++;
    }

    if (serialized.blank()) {
        newItemsSize = 0;
    }
    // $('log').innerHTML += ' + ' + section + ' antes tenía: ' + totalItems + ' ahora tiene ' + newItemsSize + '<br />';
    //$('log').innerHTML += ' + serial data: ' + serialized + '<br />';
    return totalItems == newItemsSize;
}

/**
 * Copies the items of a div (all those who have item as classname) to the
 * items section array (items[section])
 */
function rebuildItemsOfSection(section)
{
    var itemsOfSection = $$('#layout_'+section + ' .item');
    items[section] = new Array();
    for(var i=0; i<itemsOfSection.length; i++) {
        var item = itemsOfSection[i].id;
        items[section][item] = true;
    }
}

/**
 * Returns in an array the item that has been changed, the section (where it is now)
 * and the position that it use
 */
function getAddedChanges()
{
    for(var i=0; i<sections.length; i++) {
        var section       = sections[i];
        var divsOfSection = $$('#layout_' + section+ ' .item');

        if (divsOfSection.length > items[section].length) {
            for(var j=0; j<divsOfSection.length; j++) {
                var item = divsOfSection[j].id;
                if (items[section][item] == undefined) {
                    return new Array(item, section, j+1);
                }
            }
        }
    }
    return null;
}

/**
 * Returns in an array the item that has changed, the item where the item is and
 * the new position
 */
function getSectionChanges(section)
{
    var divsOfSection = $$('#layout_' + section+ ' .item');

    var itemPos       = 1;
    for(var j=0; j<divsOfSection.length; j++) {
        var item    = divsOfSection[j].id;
        var origPos = getPositionOfItem(item, section);
        if (origPos != itemPos) {
            return new Array(item, section, itemPos);
        }
        itemPos++;
    }
    return null;
}

/**
 * Returns in an array the item that has been deleted and the section where it was
 */
function getDeletedChanges(item_added)
{
    for(var i=0; i<sections.length; i++) {
        var section = sections[i];
        if (items[section][item_added] == true) {
            return new Array(item_added, section);
        }
    }
    return null;
}

/**
 * Checks a section, if no items are found then a msg should be displayed
 * in the section
 */
function checkDeletedSection(section)
{
    var divsOfSection = $$('#layout_'+section + ' .item');
    if (divsOfSection.length == 0) {
        var emptyDiv = document.createElement('div');
        emptyDiv.className = 'layout-message';
        $('layout_' + section).appendChild(emptyDiv);
        emptyDiv.innerHTML = noItemsMsg;
    }
}

/**
 * Deletes an element
 */
function deleteElement(itemId, confirmMsg)
{
    var itemDiv     = $('item_' + itemId);
    var parentDiv   = itemDiv.parentNode;
    var comesFrom   = parentDiv.id.replace('layout_', '');

    var answer = confirm(confirmMsg);
    if (answer) {
        Effect.Fade(itemDiv.id, {duration:1});
        window.setTimeout('\'parentDiv.removeChild(itemDiv);\'', 800);

        items[comesFrom][itemDiv.id] = null;
        rebuildItemsOfSection(comesFrom);
        checkDeletedSection(comesFrom);
        layoutAsync.deleteelement(itemId);
    }
}

/**
 * Moves an item on the section or to another section
 */
function moveItemOnSection(element, section, serialized)
{
    if (actionStep > 2) {
        return false;
    }

    var sectionId = element.id.replace('layout_', '');

    if (actionStep == 1) {
        itemActions['to']   = sectionId;
    }

    if (actionStep == 2) {
        itemActions['from'] = sectionId;
    }

    if (itemMovedOnSameSection(section, serialized) && !movedNewElement) {
        var sectionChanges = getSectionChanges(sectionId);
        if (sectionChanges == null) {
            return false;
        }
        actionStep = 4;
        itemActions['from'] = sectionId;
        actionStep = 1;

        //$('log').innerHTML += '*)El item ' + sectionChanges[0] + ' fue cambiado a ' + sectionChanges[1] + ' en la posición: '
        //                   + sectionChanges[2] + ', el serial es: ' + serialized + '<br />';
        //$('log').innerHTML += 'Serial: ' + serialized + '<br />';

        rebuildItemsOfSection(sectionId);
        layoutAsync.moveelement(sectionChanges[0].replace('item_', ''),
                                sectionChanges[1],
                                sectionChanges[2],
                                items[sectionId]);
    } else {
        actionStep++;
        if (actionStep >= 3) {
            var addedChanges   = getAddedChanges();
            var deletedChanges = getDeletedChanges(addedChanges[0]);

            // $('log').innerHTML += 'El item ' + addedChanges[0] + ' fue cambiado a ' + addedChanges[1] + ' en la posición: ' 
            //                    + addedChanges[2] + ', el serial es: ' + serialized + '<br />';

            rebuildItemsOfSection(addedChanges[1]);
            rebuildItemsOfSection(deletedChanges[1]);

            actionStep = 1;
            itemActions = new Array();

            movedNewElement = false;
            checkDeletedSection(deletedChanges[1]);

            layoutAsync.moveelement(addedChanges[0].replace('item_', ''),
                                    addedChanges[1],
                                    addedChanges[2],
                                    items[addedChanges[1]]);
        }
    }
}

/**
 * Creates a random string (for ids)
 */
function randomString()
{
    var chars  = '0123456789abcdefghijklmnopqrstuvwxyz';
    var length = 8;
    var str    = '';
    for (var i=0; i<length; i++) {
        var num = Math.floor(Math.random() * chars.length);
        str += chars.substring(num, num+1);
    }
    return str;
}

/**
 * Initializes some variables
 */
function initUI()
{
    for(var i=0; i<sections.length; i++) {
        var layoutSection = 'layout_' + sections[i];
        var layoutDrop    = 'layout_' + sections[i] + '_drop';

        objects['sort'][layoutSection] = Sortable.create(layoutSection,
        {
            tag:'div',
            only: 'item',
            dropOnEmpty: true,
            revert: true,
            constraint: true,
            onUpdate: function(element) {
                moveItemOnSection(element, layoutSection, Sortable.serialize(layoutSection)); 
            }
        }
        );

        objects['drop'][layoutDrop] = Droppables.add(layoutDrop, {
            accept: 'item',
            hoverclass: 'layout-section-hover',
            overlap: 'horizontal',
            onDrop: function(draggableElement, droppableElement) {
                moveElement(draggableElement, droppableElement);
            }
        });
    }
}

function changeTheme()
{
    $('controls').submit();
}

function changeLayoutMode()
{
    $('controls').submit();
}

function addGadget(url, title)
{
    showDialogBox('gadgets_dialog', title, url, 350, 610);
}

function editElementAction(url)
{
    showDialogBox('actions_dialog', actionsTitle, url, 400, 400);
}

function changeDisplayWhen(url)
{
    showDialogBox('dw_dialog', displayWhenTitle, url, 300, 250);
}

var prevGadget = '';
function selectGadget(g)
{
    $('gadget').value = g;

    // Remove all actions 
    while ($('actions-list').firstChild)
    {
        $('actions-list').removeChild($('actions-list').firstChild);
    };

    if ($(prevGadget)) {
        $(prevGadget).setAttribute('class', 'gadget-item'); 
        $(prevGadget).setAttribute('className', 'gadget-item'); 
    }
    $(g).setAttribute('class', 'gadget-item gadget-selected'); 
    $(g).setAttribute('className', 'gadget-item gadget-selected'); 
    var actions = layoutSync.getgadgetactions(g);
    if (actions.length > 0) {
        actions.each (function(item, actionIndex) {
            var li = new Element('li', {'id':'action_' + item['action']});
            li.adopt(new Element('input', {'id':'action_'+actionIndex,
                                            'name':'action',
                                            'type':'radio',
                                            'value':item['action'],
                                            'checked': actionIndex == 0}));
            // action label
            li.adopt(new Element('label', {'for':'action_' + actionIndex}).set('html', item['name']));
            // action params
            if (typeof(item['params']) === 'object') {
                item['params'].each(function(param, index) {
                    select = new Element('select', {'name': param['title']});
                    Object.keys(param['value']).each(function(value) {
                        select.adopt(new Element('option', {'value': value}).set('html', param['value'][value]));
                    });
                    li.adopt(select);
                });
            }
            // action description
            li.adopt(new Element('span', {}).set('html', item['desc']));
            $('actions-list').appendChild(li);
        });
    } else {
        var li = new Element('li', {'class':'action-msg'}).set('html', noActionsMsg);
        $('actions-list').appendChild(li);
    }
    prevGadget = g;
}

function addGadgetToLayout(gadget, action, params)
{
    hideDialogBox('gadgets_dialog');
    params = params.split(',');
    layoutAsync.addgadget(gadget, action, params);
}

function getSelectedAction()
{
    var radioObj = document.forms['form1'].elements['action'];
    if(!radioObj)
        return "";
    var radioLength = radioObj.length;
    if(radioLength == undefined)
        if(radioObj.checked)
            return radioObj.value;
        else
            return "";
    for(var i = 0; i < radioLength; i++) {
        if(radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

function addGadgetToLayout(gadget, action, params)
{
    hideDialogBox('gadgets_dialog');
    params = params.split(',');
    layoutAsync.addgadget(gadget, action, params);
}

function saveElementAction(lid, gadget, action, params, title, desc)
{
    hideDialogBox('actions_dialog');
    params = params.split(',');
    $('ea' + lid).innerHTML = title;
    $('ea' + lid).parentNode.parentNode.title = desc;
    layoutAsync.editelementaction(lid, gadget, action, params);
}

function saveChangeDW(itemId, dw) {
    // Ugly hack to update
    fun = 'layoutAsync.changedisplaywhen(' + itemId + ',\'' + dw + '\')';
    setTimeout(fun, 0);
    if (dw == '*') {
        $('dw' + itemId).innerHTML = displayAlways;
    } else if (dw.blank()) {
        $('dw' + itemId).innerHTML = displayNever;
    } else {
        $('dw' + itemId).innerHTML = dw.replace(/,/g, ', ');
    }
    hideDialogBox('dw_dialog');
}

var ver = navigator.appVersion;
if (/MSIE 6/i.test(navigator.userAgent)) {
    window.onload=function() {
        window.onscroll = function() {
            var clientHeight = document.documentElement.clientHeight;
            clientHeight = (clientHeight == 0 )? document.body.offsetHeight : clientHeight;
            var scrollTop = document.documentElement.scrollTop;
            scrollTop = (scrollTop == 0 )? (document.body.scrollTop - 4) : scrollTop;
            $('layout-controls').style.top = clientHeight + scrollTop - 64 + "px";
        }
    }
}
var layoutAsync = new layoutadminajax(LayoutCallback);
layoutAsync.serverErrorFunc = Jaws_Ajax_ServerError;
layoutAsync.onInit = showWorkingNotification;
layoutAsync.onComplete = hideWorkingNotification;

var layoutSync  = new layoutadminajax();
layoutSync.serverErrorFunc = Jaws_Ajax_ServerError;
layoutSync.onInit = showWorkingNotification;
layoutSync.onComplete = hideWorkingNotification;

var items = new Array();
var newdrags = new Array();
var sections = new Array();

var previousMode = null;
var itemTmp = null;

var itemActions = new Array();
var actionStep  = 1;

var currentAction = new Array();

var sections = new Array();

var objects = new Array();
objects['sort'] = new Array();
objects['drop'] = new Array();

var movedNewElement = false;
var newEmptyRegion  = '';

//selectd layout mode
var selectedMode = null;

//Combo colors
var evenColor = '#fff';
var oddColor  = '#edf3fe';
