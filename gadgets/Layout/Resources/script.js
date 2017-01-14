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
                    $('<a>').attr('href', 'javascript:void(0);').click(
                        function() {deleteElement(response['id']);}
                    ).append(
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
                        'href': 'javascript:void(0);'
                    }).text(
                        response['taction']
                    ).click(function() {elementAction(response['eaonclick']);})
                )
            );
            dItem.append(
                $('<div>').attr('class', 'item-dw').append(
                    /*$('label').text(response['dwdisplay'])*/
                    response['dwdisplay']
                ).append(
                    $('<a>').attr({
                        'id': response['dwid'],
                        'name': response['dwid'],
                        'href': 'javascript:void(0);',
                        'title': response['dwtitle']
                    }).text(
                        response['dwalways']
                    ).click(function(){displayWhen(response['dwonclick']);})
                )
            );

            $('#layout_main').append(dItem);
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

    var answer = confirm(jaws.gadgets.Layout.confirmDelete);
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
    showDialogBox('actions_dialog', jaws.gadgets.Layout.actionsTitle, url, 435, 555);
}

function displayWhen(url)
{
    showDialogBox('dw_dialog', jaws.gadgets.Layout.displayWhenTitle, url, 300, 250);
}

var prevGadget = '';
function selectGadget(doc, g)
{
    $(doc).find('#gadget').val(g);

    // Remove all actions 
    $(doc).find('#actions-list').empty();
    if (prevGadget) {
        $(doc).find('#' + prevGadget).attr('class', 'gadget-item');
    }
    $(doc).find('#' + g).attr('class', 'gadget-item gadget-selected');
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
            $(doc).find('#actions-list').append(li);
        });
    } else {
        $('<li>').attr('class', 'action-msg')
            .html(parent.parent.jaws.gadgets.Layout.noActionsMsg)
            .appendTo($(doc).find('#actions-list'));
    }
    prevGadget = g;
}

function addGadgetToLayout(gadget, action, params)
{
    hideDialogBox('gadgets_dialog');
    params = (params == null)? null : params.split(',');
    LayoutAjax.callAsync(
        'AddGadget',
        [gadget, action, params, $('#layout').val()]
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
        $('#dw' + itemId).html(jaws.gadgets.Layout.displayAlways);
    } else if (dw.blank()) {
        $('#dw' + itemId).html(jaws.gadgets.Layout.displayNever);
    } else {
        $('#dw' + itemId).html(dw.replace(/,/g, ', '));
    }
    hideDialogBox('dw_dialog');
}

/**
 *
 */
function layoutControlsSubmit(sender) {
    var layout_layout_url = jaws.gadgets.Layout.layout_layout_url;
    var layout_theme_url  = jaws.gadgets.Layout.layout_theme_url;
    if (sender.id != 'theme') {
        window.location = layout_layout_url.replace('~layout~', $('#layout').val());
    } else {
        window.location = layout_theme_url.replace('~theme~', $('#theme').val());
    }
}

/**
 *
 */
function addGetAction(doc) {
    var gadget = $(doc).find('#gadget').val();
    var action = $(doc).find('#form_actions_list input[type="radio"][name="action"]:checked');
    if (action.length == 0) {
        alert('!!!!!!!!!!');
        return;
    }

    var params = null;
    var paramElemets = $(doc).find('#action_'+action.val()).find('select,input:not([type=radio])');
    if (paramElemets.length > 0) {
        params = new Array();
        $.each(paramElemets, function(index, elParam) {
            if (elParam.type == 'checkbox') {
                params[index] = Number(elParam.checked);
            } else {
                params[index] = elParam.value;
            }
        });
        params = params.join();
    }

    parent.parent.addGadgetToLayout(gadget, action.val(), params);
}

/**
 *
 */
function editGetAction(doc, lid, gadget) {
    var action = $(doc).find('#form_actions_list input[type="radio"][name="action"]:checked');
    if (action.length == 0) {
        alert('!!!!!!!!!!');
        return;
    }

    title = $(doc).find('#action_'+action.val()).children('label').first().html();
    desc = $(doc).find('#action_'+action.val()).children('span').first().html();

    var params = null;
    var paramElemets = $(doc).find('#action_'+action.val()).find('select,input:not([type=radio])');
    if (paramElemets.length > 0) {
        params = new Array();
        $.each(paramElemets, function(index, elParam) {
            if (elParam.type == 'checkbox') {
                params[index] = Number(elParam.checked);
            } else {
                params[index] = elParam.value;
            }
        });
        params = params.join();
    }

    parent.parent.saveElementAction(lid, gadget, action.val(), params, title, desc);
}

/**
 *
 */
function showGadgets (doc) {
    $(doc).find('#selected_gadgets').toggle();
}

/**
 *
 */
function getSelectedGadgets(doc) {
    if ($(doc).find('#display_in').val() == 'always') {
        return '*';
    } else {
        var res = '';
        var selectedItems = $(doc).find('#selected_gadgets input[type=checkbox]');
        $.each(selectedItems, function(index, item) {
            if ($(item).prop('checked')) {
                res += $(item).val() + ',';
            }
        });

        if (res.length > 0) {
            res = res.substr(0, res.lastIndexOf(','));
        }
        return res;
    }
}

$(document).ready(function() {
    initUI();
});

var LayoutAjax = new JawsAjax('Layout', LayoutCallback);

var newdrags = new Array();

var previousMode = null;
var itemTmp = null;

var itemActions = new Array();
var actionStep  = 1;

var currentAction = new Array();

var objects = new Array();
objects['sort'] = new Array();
objects['drop'] = new Array();

var newEmptyRegion  = '';

//selectd layout mode
var selectedMode = null;

//Combo colors
var evenColor = '#fff';
var oddColor  = '#edf3fe';
