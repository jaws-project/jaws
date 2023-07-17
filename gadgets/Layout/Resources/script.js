/**
 * Layout Javascript actions
 *
 * @category   Ajax
 * @package    Layout
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */

/**
 * Use async mode, create Callback
 */
var LayoutCallback = {
    UpdateElementAction: function(response) {
        //
    },

    UpdateDisplayWhen: function(response) {
        //
    },

    MoveElement: function(response) {
        //
    },

    DeleteElement: function(response) {
        //
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

    var answer = confirm(Jaws.gadgets.Layout.defines.confirmDelete);
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
    }
}

/**
 * Initializes some variables
 */
function initUI()
{
    $(".layout-section").sortable({
        connectWith: ".layout-section",
    }).on('start', function(e, item) {
        var item = $( item );
        item.data('old_section',  item.parent().attr('id').replace('layout_', ''));
        item.data('old_position', item.parent().children('div').index(item) + 1);
    }).on('stop', function(e, item) {
        var item = $( item );
        var new_section  = item.parent().attr('id').replace('layout_', '');
            new_position = item.parent().children('div').index(item) + 1;

        if ((new_section  != item.data('old_section')) ||
            (new_position != item.data('old_position'))
        ) {
            LayoutAjax.callAsync(
                'MoveElement', [
                    item.attr('id').replace('item_', ''),      // item id
                    $('#layout').val(),                        // layout name
                    item.data('old_section'),                  // old section name
                    parseInt(item.data('old_position')),       // position in old section
                    new_section,                               // new section name
                    new_position                               // position in new section
                ]
            );
        }

        item.removeData();
    });
}

function addGadget(url, title)
{
    showDialogBox('gadgets_dialog', title, url, 400, 800);
}

function elementAction(url)
{
    showDialogBox('actions_dialog', Jaws.gadgets.Layout.defines.actionsTitle, url, 435, 555);
}

function displayWhen(url)
{
    showDialogBox('dw_dialog', Jaws.gadgets.Layout.defines.displayWhenTitle, url, 300, 250);
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
    var actions = LayoutAjax.callAsync('GetGadgetActions', g, false, {'async': false});
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
            .html(parent.parent.Jaws.gadgets.Layout.defines.noActionsMsg)
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
        $('#dw' + itemId).html(Jaws.gadgets.Layout.defines.displayAlways);
    } else if (dw.blank()) {
        $('#dw' + itemId).html(Jaws.gadgets.Layout.defines.displayNever);
    } else {
        $('#dw' + itemId).html(dw.replace(/,/g, ', '));
    }
    hideDialogBox('dw_dialog');
}

/**
 *
 */
function layoutControlsSubmit(sender) {
    var layout_layout_url = Jaws.gadgets.Layout.defines.layout_layout_url;
    var layout_theme_url  = Jaws.gadgets.Layout.defines.layout_theme_url;
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
