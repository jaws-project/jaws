/**
 * UrlMapper Javascript actions
 *
 * @category   Ajax
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * UrlMapper CallBack
 */
var UrlMapperCallback = {
    /**
     * Updates a map
     */
    UpdateMap: function(response) {
        if (response[0]['type'] == 'alert-success') {
            enableMapEditingArea(false);
            showActionMaps();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Update settings
     */
    UpdateSettings: function(response) {
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Adds a new alias
     */
    AddAlias: function(response) {
        if (response[0]['type'] == 'alert-success') {
            rebuildAliasCombo();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Updates a new alias
     */
    UpdateAlias: function(response) {
        if (response[0]['type'] == 'alert-success') {
            rebuildAliasCombo();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Deletes a new alias
     */
    DeleteAlias: function(response) {
        if (response[0]['type'] == 'alert-success') {
            rebuildAliasCombo();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Add a new error map
     */
    AddErrorMap: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopErrorMapAction();
            $('#errormaps_datagrid')[0].addItem();
            $('#errormaps_datagrid')[0].lastPage();
            getDG('errormaps_datagrid');
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * delete an  error map
     */
    DeleteErrorMaps: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopErrorMapAction();
            getDG('errormaps_datagrid', $('#errormaps_datagrid')[0].getCurrentPage(), true);
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * delete error maps using filters
     */
    DeleteErrorMapsFilters: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopErrorMapAction();
            getDG('errormaps_datagrid', $('#errormaps_datagrid')[0].getCurrentPage(), true);
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * update an  error map
     */
    UpdateErrorMap: function(response) {
        if (response[0]['type'] == 'alert-success') {
            stopErrorMapAction();
            getDG('errormaps_datagrid');
        }
        UrlMapperAjax.showResponse(response);
    }
};

/**
 * Build the 'big' alias combo
 */
function rebuildAliasCombo()
{
    var combo = $('#alias-combo')[0];
    while(combo.options.length != 0) {
        combo.options[0] = null;
    }
    var aliases = UrlMapperAjax.callSync('GetAliases');
    if (aliases != false) {
        var i =0;
        $.each(aliases, function(index, value) {
            var op = new Option(' + ' + value['alias_url'], value['id']);
            if (i % 2 == 0) {
                op.style.backgroundColor = evenColor;
            } else {
                op.style.backgroundColor = oddColor;
            }
            combo.options[combo.options.length] = op;
            i++;
        });
        stopAction();
    }
}

/**
 * Edits an alias
 */
function editAlias(id)
{
    var alias = UrlMapperAjax.callSync('GetAlias', id);
    $('#alias_id').val(id);
    $('#custom_url').val(alias['real_url']);
    $('#alias').val(alias['alias_url']);
    $('#delete_button').css('visibility', 'visible');
}

/**
 * Saves an alias
 */
function saveAlias()
{
    if ($('#alias_id').val() == '-') {
        UrlMapperAjax.callAsync(
            'AddAlias', [
                $('#alias').val(),
                $('#custom_url').val()
            ]
        );
    } else {
        UrlMapperAjax.callAsync(
            'UpdateAlias', [
                $('#alias_id').val(),
                $('#alias').val(),
                $('#custom_url').val()
            ]
        );
    }
}

/**
 * Deletes an alias
 */
function deleteCurrentAlias()
{
    if ($('#alias-combo').prop('selectedIndex') != -1) {
        UrlMapperAjax.callAsync('DeleteAlias', $('#alias-combo').val());
    }
    stopAction();
}

/**
 * Update UrlMapper settings
 */
function updateProperties(form)
{
    UrlMapperAjax.callAsync(
        'UpdateSettings', [
            form['enabled'].value,
            form['use_aliases'].value,
            form['custom_precedence'].value,
            form['extension'].value
        ]
    );
}

/**
 * Add/Edit a map
 */
function saveMap()
{
    UrlMapperAjax.callAsync(
        'UpdateMap', [
            selectedMap,
            $('#custom_map_route').val(),
            $('#map_order').val()
        ]
    );
}

/**
 * Prepares the UI to edit an error map
 */
function editErrorMap(element, emid)
{
    selectedErrorMap = emid;
    $('#legend_title').html(jaws.gadgets.UrlMapper.editErrorMap_title);
    selectDataGridRow($(element).parent().parent()[0]);

    var errorMapInfo = UrlMapperAjax.callSync('GetErrorMap', selectedErrorMap);
    $('#url').val(errorMapInfo['url']);
    $('#code').val(errorMapInfo['code']);
    $('#new_url').val(errorMapInfo['new_url']);
    $('#new_code').val(errorMapInfo['new_code']);
    $('#insert_time').val(errorMapInfo['insert_time']);
    $('#btn_cancel').css('visibility', 'visible');
}

/**
 * Prepares the UI to edit a map
 */
function editMap(element, mid)
{
    enableMapEditingArea(true);

    selectedMap = mid;
    $('#legend_title').html(jaws.gadgets.UrlMapper.editMap_title);
    selectDataGridRow(element.parentNode.parentNode);

    var mapInfo = UrlMapperAjax.callSync('GetMap', selectedMap);
    $('#map_route').val(mapInfo['map']);
    $('#map_ext').val(mapInfo['extension']);
    $('#map_order').val(mapInfo['order']);

    if (mapInfo['custom_map'] == null || mapInfo['custom_map'] == '') {
        $('#custom_map_route').val(mapInfo['map']);
    } else {
        $('#custom_map_route').val(mapInfo['custom_map']);
    }
}

/**
 * Prepares a datagrid with maps of each action
 */
function showActionMaps()
{
    if (!$('#gadgets_combo').val() ||
        !$('#actions_combo').val())
    {
        return false;
    }

    resetGrid('maps_datagrid', '');
    //Get maps of this action and gadget
    var result = UrlMapperAjax.callSync(
        'GetActionMaps',
        [$('#gadgets_combo').val(), $('#actions_combo').val()]
    );
    resetGrid('maps_datagrid', result);
    enableMapEditingArea(false);
}

/**
 * Cleans the action combo and fill its again
 */
function rebuildActionCombo()
{
    var combo = $('#actions_combo')[0];
    var selectedGadget = $('#gadgets_combo').val();
    var actions = UrlMapperAjax.callSync('GetGadgetActions', selectedGadget);

    combo.options.length = 0;
    if (actions != false) {
        var i =0;
        $.each(actions, function(index, text) {
            var op = new Option(text, text);
            if (i % 2 == 0) {
                op.style.backgroundColor = evenColor;
            } else {
                op.style.backgroundColor = oddColor;
            }
            combo.options[combo.options.length] = op;
            i++;
        });
    }

    enableMapEditingArea(false);
    resetGrid('maps_datagrid', '');
}

/**
 * Enable/Disable Map editing area
 */
function enableMapEditingArea(status)
{
    if (status) {
        $('#custom_map_route').prop('disabled', false);
        $('#btn_save').prop('disabled', false);
        $('#btn_cancel').prop('disabled', false);
    } else {
        selectedMap = null;
        unselectDataGridRow();
        $('#map_order').val('');
        $('#map_route').val('');
        $('#map_ext').val('');
        $('#custom_map_route').val('');
        $('#custom_map_route').prop('disabled', true);
        $('#btn_save').prop('disabled', true);
        $('#btn_cancel').prop('disabled', true);
        $('#legend_title').html(jaws.gadgets.UrlMapper.addMap_title);
    }
}

/**
 * Change new code value
 */
function changeCode()
{
    if ($('#new_code').val() == 410) {
        $('#new_url').prop('disabled', true);
    } else {
        $('#new_url').prop('disabled', false);
    }
}

/**
 * Get error maps list
 */
function getErrorMaps(name, offset, reset)
{
    var filters = {
        'from_date' : $('#filter_from_date').val(),
        'to_date'   : $('#filter_to_date').val(),
        'code'      : $('#filter_code').val(),
        'new_code'  : $('#filter_new_code').val()
    };

    var result = UrlMapperAjax.callSync('GetErrorMaps', {
        'offset': offset,
        'order': $('#order_type').val(),
        'filters': filters
    });

    if (reset) {
        $('#' + name)[0].setCurrentPage(0);
        var total = UrlMapperAjax.callSync('GetErrorMapsCount', {
            'filters': filters
        });

    }
    resetGrid(name, result, total);
}

/**
 * Search logs
 */
function searchErrorMaps()
{
    getErrorMaps('errormaps_datagrid', 0, true);
}

/**
 * Executes an action on error maps
 */
function errorMapsDGAction()
{
    var combo = $('#errormaps_actions_combo')[0],
        rows = $('#errormaps_datagrid')[0].getSelectedRows();

    var filters = {
        'from_date' : $('#filter_from_date').val(),
        'to_date'   : $('#filter_to_date').val(),
        'code'      : $('#filter_code').val(),
        'new_code'  : $('#filter_new_code').val()
    };

    var confirmation = confirm(jaws.gadgets.UrlMapper.confirmErrorMapDelete);

    if (combo.value == 'delete') {
        if (rows.length < 1) {
            return;
        }
        UrlMapperAjax.callAsync('DeleteErrorMaps', rows);
    } else if (combo.value == 'deleteAll') {
        if (confirmation) {
            UrlMapperAjax.callAsync('DeleteErrorMapsFilters', {'filters': null});
        }
    } else if (combo.value == 'deleteFiltered') {
        if (confirmation) {
            UrlMapperAjax.callAsync('DeleteErrorMapsFilters', {'filters': filters});
        }
    }
}

/**
 * Add/Edit an error map
 */
function saveErrorMap()
{
    if (selectedErrorMap != null && selectedErrorMap > 0) {
        UrlMapperAjax.callAsync(
            'UpdateErrorMap', [
                selectedErrorMap,
                $('#url').val(),
                $('#code').val(),
                $('#new_url').val(),
                $('#new_code').val()
            ]
        );
    } else {
        UrlMapperAjax.callAsync(
            'AddErrorMap', [
                $('#url').val(),
                $('#code').val(),
                $('#new_url').val(),
                $('#new_code').val()
            ]
        );
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('#alias_id').val('-');
    $('#alias').val('');
    $('#custom_url').val('');
    $('#delete_button').css('visibility', 'hidden');
    $('#alias-combo').prop('selectedIndex', -1);
}

/**
 * Stops doing error map action
 */
function stopErrorMapAction()
{
    $('#legend_title').html(jaws.gadgets.UrlMapper.addErrorMap_title);
    $('#btn_cancel').css('visibility', 'hidden');
    unselectDataGridRow();
    selectedErrorMap = null;

    $('#url').val('');
    $('#code').prop('selectedIndex', -1);
    $('#new_url').val('');
    $('#new_code').prop('selectedIndex', -1);
    $('#insert_time').val('');
}

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Maps':
            JawsDataGrid.name = 'maps_datagrid';
            $('#legend_title').html(jaws.gadgets.UrlMapper.addMap_title);
            $('#maps_datagrid')[0].objectName = UrlMapperAjax;
            rebuildActionCombo();
            break;

        case 'Aliases':
            $('#alias_id').val('-');
            $('#custom_url').val('');
            $('#alias').val('');
            break;

        case 'ErrorMaps':
            JawsDataGrid.name = 'errormaps_datagrid';
            $('#legend_title').html(jaws.gadgets.UrlMapper.addErrorMap_title);
            $('#errormaps_datagrid')[0].objectName = UrlMapperAjax;
            initDatePicker('filter_from_date');
            initDatePicker('filter_to_date');
            initDataGrid('errormaps_datagrid', UrlMapperAjax, getErrorMaps);
            break;
    }
});

var UrlMapperAjax = new JawsAjax('UrlMapper', UrlMapperCallback);

var evenColor = '#fff';
var oddColor  = '#edf3fe';

//Current map
var selectedMap = null;

//Current error map
var selectedErrorMap = null;

var cacheMapTemplate = null;
var cacheEditorMapTemplate = null;

var aliasesComboDiv = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
