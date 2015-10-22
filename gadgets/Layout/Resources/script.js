/**
 * Layout Javascript actions
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Use async mode, create Callback
 */
var LayoutCallback = {
    UpdateElementAction: function(response) {
        LayoutAjax.showResponse(response, false);
    },

    UpdateDisplayWhen: function(response) {
        LayoutAjax.showResponse(response, false);
    },

    MoveElement: function(response) {
        LayoutAjax.showResponse(response, false);
    },

    DeleteElement: function(response) {
        LayoutAjax.showResponse(response, false);
    },

    AddGadget: function(response) {
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
            items['main']['item_' + response['id']] = true;
            layoutSortable.addItems(dItem);
        }
        LayoutAjax.showResponse(response, false);
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
        var divsOfSection = $('#layout_' + section+ ' .item');

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
function deleteElement(itemId)
{
    var itemDiv  = $('#item_' + itemId),
        section  = itemDiv.parent().attr('id').replace('layout_', ''),
        position = itemDiv.parent().children('div').index(itemDiv);

    var answer = confirm(confirmDelete);
    if (answer) {
        itemDiv.fadeOut(500, function() {$(this).remove();})
        LayoutAjax.callAsync(
            'DeleteElement', [
                itemId,
                $('#index_layout').val(),
                section,
                position + 1,
                $('#user').val()
            ]
        );
    }
}

/**
 * Initializes some variables
 */
function initUI()
{
    $.loadScript('libraries/js/jquery-ui.js', function() {
        layoutSortable = $(".layout-section").sortable({
            revert: true,
            opacity: 0.75,
            helper: "original",
            connectWith: ".layout-section",

            start: function(event, ui) {
                $.data(ui.item[0], 'old_section',  ui.item.parent().attr('id').replace('layout_', ''));
                $.data(ui.item[0], 'old_position', ui.item.parent().children('div').index(ui.item) + 1);
            },

            stop: function(event, ui) {
                var new_section  = ui.item.parent().attr('id').replace('layout_', '');
                    new_position = ui.item.parent().children('div').index(ui.item) + 1;
                if ((new_section  != $.data(ui.item[0], 'old_section')) ||
                    (new_position != $.data(ui.item[0], 'old_position'))
                ) {
                    LayoutAjax.callAsync(
                        'MoveElement', [
                            ui.item.attr('id').replace('item_', ''),      // item id
                            $('#index_layout').val(),                     // index or default layout
                            $.data(ui.item[0], 'old_section'),            // old section name
                            parseInt($.data(ui.item[0], 'old_position')), // position in old section
                            new_section,                                  // new section name
                            new_position,                                 // position in new section
                            $('#user').val()                              // dashboard of user or global layout
                        ]
                    );
                }
                $.removeData(ui.item[0]);
            }
        });
    });
}

function addGadget(url, title)
{
    showDialogBox('gadgets_dialog', title, url, 400, 800);
}

function elementAction(url)
{
    showDialogBox('actions_dialog', actionsTitle, url, 435, 555);
}

function displayWhen(url)
{
    showDialogBox('dw_dialog', displayWhenTitle, url, 300, 250);
}

var prevGadget = '';
function selectGadget(g)
{
    $('#gadget').val(g);

    // Remove all actions 
    $('#actions-list').empty();
    if ($('#' + prevGadget).length) {
        $('#' + prevGadget).attr('class', 'gadget-item');
    }
    $('#' + g).attr('class', 'gadget-item gadget-selected');
    var actions = LayoutAjax.callSync('GetGadgetActions', g);
    if (actions.length > 0) {
        $.each(actions, function(actionIndex, item) {
            var li = $('<li>').attr('id', 'action_' + item['action']);
            li.append($('<input>').attr({
                'id':'action_'+actionIndex,
                'name':'action',
                'type':'radio',
                'value':item['action'],
                'checked': actionIndex == 0
            }));
            // action label
            li.append($('<label>').attr('for', 'action_' + actionIndex).html(item['name']));
            // action description
            li.append($('<span>').html(item['desc']));
            // action params
            if (typeof(item['params']) === 'object') {
                $.each(item['params'], function(paramIndex, param) {
                    var paramID = 'action_' + actionIndex+'_param_'+paramIndex;
                    var divElement = $('<div>').attr('class', 'action_param');
                    var lblElement = $('<label>').attr('for', paramID).html(param['title']+':');
                    divElement.append(lblElement);
                    switch (typeof param['value']) {
                        case 'string':
                        case 'number':
                            paramElement = $('<input>').attr({
                                'type': 'text',
                                'name':paramID,
                                'id': paramID,
                                'value': param['value']
                            });
                            break;

                        case 'boolean':
                            paramElement = $('<input>').attr({
                                'type': 'checkbox',
                                'name': paramID,
                                'id':paramID,
                                'checked': param['value']
                            });
                            break;

                        default:
                            paramElement = $('<select>').attr({'name': paramID, 'id': paramID});
                            $.each(param.value, function(key, value) {
                                paramElement.append($('<option>').val(key).html(value));
                            });
                            break;
                    }
                    divElement.append(paramElement);
                    li.append(divElement);
                });
            }
            $('#actions-list').append(li);
        });
    } else {
        $('<li>').attr('class', 'action-msg').html(noActionsMsg).appendTo($('#actions-list'));
    }
    prevGadget = g;
}

function addGadgetToLayout(gadget, action, params)
{
    hideDialogBox('gadgets_dialog');
    params = (params == null)? null : params.split(',');
    LayoutAjax.callAsync(
        'AddGadget',
        [gadget, action, params, $('#index_layout').val(), $('#user').val()]
    );
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

function saveElementAction(lid, gadget, action, params, title, desc)
{
    hideDialogBox('actions_dialog');
    params = (params == null)? null : params.split(',');
    $('#ea' + lid).html(title);
    $('#ea' + lid).parentNode.parentNode.title = desc;
    LayoutAjax.callAsync(
        'UpdateElementAction',
        [lid, gadget, action, params, $('#user').val()]
    );
}

/**
 * Update display when 
 */
function saveChangeDW(itemId, dw) {
    LayoutAjax.callAsync('UpdateDisplayWhen', [itemId, dw, $('#user').val()]);
    if (dw == '*') {
        $('#dw' + itemId).html(displayAlways);
    } else if (dw.blank()) {
        $('#dw' + itemId).html(displayNever);
    } else {
        $('#dw' + itemId).html(dw.replace(/,/g, ', '));
    }
    hideDialogBox('dw_dialog');
}

var LayoutAjax = new JawsAjax('Layout', LayoutCallback);

var layoutSortable = null;

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
