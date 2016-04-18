/**
 * Layout Javascript actions
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright  2004-2015 Jaws Development Group
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
            var dItem = $('<div>').attr({
                'id': 'item_' + response['id'],
                'class': 'item',
                'title': response['tactiondesc']
            });
            dItem.append(
                $('<div>').attr('class', 'item_icon').append(
                    $('<img>').attr({'alt': 'icon', 'src': response['icon']})
                )
            );
            dItem.append(
                $('<div>').attr('class', 'item-delete').append(
                    $('<a>').attr('href', 'javascript:void(0);').on('click', response['delete']).append(
                        $('<img>').attr('src', response['deleteimg'])
                    )
                )
            );
            dItem.append(
                $('<div>').attr('class', 'item-gadget').text(response['tname'])
            );
            dItem.append(
                $('<div>').attr('class', 'item-action').append(
                    $('<a>').attr({
                        'id': response['eaid'],
                        'name': response['eaid'],
                        'href': 'javascript:void(0);',
                        'title': response['tactiondesc']
                    }).on('click', response['eaonclick'])
                )
            );
            dItem.append(
                $('<div>').attr('class', 'item-dw').text(response['dwalways']).append(
                    $('<a>').attr({
                        'id': response['dwid'],
                        'name': response['dwid'],
                        'href': 'javascript:void(0);',
                        'title': response['dwtitle']
                    }).on('click', response['dwonclick'])
                )
            );

            $('#layout_main').append(dItem);
            items['main']['item_' + response['id']] = true;
            $(".layout-section").sortable('refresh');
        }
        LayoutAjax.showResponse(response, false);
    }
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
                $('#layout').val(),
                section,
                position + 1
            ]
        );
        $(".layout-section").sortable('refresh');
    }
}

/**
 * Initializes some variables
 */
function initUI()
{
    $.loadScript('libraries/jquery/jquery-ui.js', function() {
        $(".layout-section").sortable({
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
                            $('#layout').val(),                           // layout name
                            $.data(ui.item[0], 'old_section'),            // old section name
                            parseInt($.data(ui.item[0], 'old_position')), // position in old section
                            new_section,                                  // new section name
                            new_position                                  // position in new section
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
        [gadget, action, params, $('#layout').val(), $('#user').val()]
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
    $('#ea' + lid).parent().parent().attr('title', desc);
    LayoutAjax.callAsync(
        'UpdateElementAction',
        [lid, $('#layout').val(), gadget, action, params]
    );
}

/**
 * Update display when 
 */
function saveChangeDW(itemId, dw) {
    LayoutAjax.callAsync('UpdateDisplayWhen', [itemId, $('#layout').val(), dw]);
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
