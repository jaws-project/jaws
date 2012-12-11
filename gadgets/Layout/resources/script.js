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

    MoveElement: function(response) {
        showResponse(response, false);
    },

    DeleteElement: function(response) {
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
            dItemIcon.setAttribute('class', 'item_icon');
            dItemIcon.setAttribute('className', 'item_icon');
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
 * Deletes an element
 */
function deleteElement(itemId, confirmMsg)
{
    var itemDiv  = $('item_' + itemId),
        section  = itemDiv.getParent().id.replace('layout_', ''),
        position = itemDiv.getParent().getElements('div.item[id]').indexOf(itemDiv);

    var answer = confirm(confirmMsg);
    if (answer) {
        itemDiv.fade('out');
        (function(){this.destroy();}).delay(500, itemDiv);
        LayoutAjax.callAsync('DeleteElement', itemId, section, position + 1);
    }
}

/**
 * Initializes some variables
 */
function initUI()
{
    var sections_selector = '';
    for(var i=0; i<sections.length; i++) {
        sections_selector += '#layout_' + sections[i] + ', ';
    }

    new Sortables(sections_selector, {
        clone: true,
        revert: true,
        opacity: 0.7,
        onStart: function(el) {
            el.setProperties({
                old_section  : el.getParent().id.replace('layout_', ''),
                old_position : el.getParent().getElements('div.item[id]').indexOf(el),
            });
        },

        onComplete: function(el) {
            var new_section  = el.getParent().id.replace('layout_', ''),
                new_position = el.getParent().getElements('div.item[id]').indexOf(el);

            if (el.getProperty('old_section') &&
                (new_section != el.getProperty('old_section') ||
                 new_position != el.getProperty('old_position'))
            ) {
                LayoutAjax.callAsync(
                    'MoveElement',
                    el.id.replace('item_', ''),         /* item id */
                    el.getProperty('old_section'),      /* old section name */
                    el.getProperty('old_position') + 1, /* position in old section */
                    new_section,                        /* new section name */
                    new_position + 1                    /* position in new section */
                );
            }
            el.removeProperties('old_section', 'old_position');
        },
    });
}

function changeTheme()
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
    var actions = LayoutAjax.callSync('getgadgetactions', g);
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
    LayoutAjax.callAsync('addgadget', gadget, action, params);
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
    LayoutAjax.callAsync('addgadget', gadget, action, params);
}

function saveElementAction(lid, gadget, action, params, title, desc)
{
    hideDialogBox('actions_dialog');
    params = params.split(',');
    $('ea' + lid).innerHTML = title;
    $('ea' + lid).parentNode.parentNode.title = desc;
    LayoutAjax.callAsync('editelementaction', lid, gadget, action, params);
}

function saveChangeDW(itemId, dw) {
    // Ugly hack to update
    fun = 'LayoutAjax.callAsync(\'changedisplaywhen\', ' + itemId + ', \'' + dw + '\')';
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

var LayoutAjax = new JawsAjax('Layout', LayoutCallback);

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

var newEmptyRegion  = '';

//selectd layout mode
var selectedMode = null;

//Combo colors
var evenColor = '#fff';
var oddColor  = '#edf3fe';
