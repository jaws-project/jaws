/**
 * UrlMapper Javascript actions
 *
 * @category   Ajax
 * @package    UrlMapper
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2014 Jaws Development Group
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
        if (response[0]['type'] == 'response_notice') {
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
        if (response[0]['type'] == 'response_notice') {
            rebuildAliasCombo();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Updates a new alias
     */
    UpdateAlias: function(response) {
        if (response[0]['type'] == 'response_notice') {
            rebuildAliasCombo();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Deletes a new alias
     */
    DeleteAlias: function(response) {
        if (response[0]['type'] == 'response_notice') {
            rebuildAliasCombo();
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * Add a new error map
     */
    AddErrorMap: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopErrorMapAction();
            $('errormaps_datagrid')[0].addItem();
            $('errormaps_datagrid').lastPage();
            getDG('errormaps_datagrid');
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * delete an  error map
     */
    DeleteErrorMaps: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopErrorMapAction();
            getDG('errormaps_datagrid', $('errormaps_datagrid')[0].getCurrentPage(), true);
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * delete error maps using filters
     */
    DeleteErrorMapsFilters: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopErrorMapAction();
            getDG('errormaps_datagrid', $('errormaps_datagrid')[0].getCurrentPage(), true);
        }
        UrlMapperAjax.showResponse(response);
    },

    /**
     * update an  error map
     */
    UpdateErrorMap: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopErrorMapAction();
            getDG('errormaps_datagrid');
        }
        UrlMapperAjax.showResponse(response);
    }
}

/**
 * Build the 'big' alias combo 
 */
function rebuildAliasCombo()
{
    var combo = $('alias-combo');
    while(combo.options.length != 0) {
        combo.options[0] = null;
    }
    var aliases = UrlMapperAjax.callSync('GetAliases');
    if (aliases != false) {
        var i =0;
        aliases.each(function(value, index) {
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
    $('alias_id').value   = id;
    $('custom_url').value = alias['real_url'];
    $('alias').value      = alias['alias_url'];
    $('delete_button').style.visibility = 'visible';
}

/**
 * Saves an alias
 */
function saveAlias()
{
    if ($('alias_id').value == '-') {
        UrlMapperAjax.callAsync(
            'AddAlias', [
                $('alias').value,
                $('custom_url').value
            ]
        );
    } else {
        UrlMapperAjax.callAsync(
            'UpdateAlias', [
                $('alias_id').value,
                $('alias').value,
                $('custom_url').value
            ]
        );
    }
}

/**
 * Deletes an alias
 */
function deleteCurrentAlias()
{
    var aliasCombo = $('alias-combo');
    if (aliasCombo.selectedIndex != -1) {
        UrlMapperAjax.callAsync('DeleteAlias', aliasCombo.value);
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
            form.elements['enabled'].value,
            form.elements['use_aliases'].value,
            form.elements['custom_precedence'].value,
            form.elements['extension'].value
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
            $('custom_map_route').value,
            $('map_order').value
        ]
    );
}

/**
 * Prepares the UI to edit an error map
 */
function editErrorMap(element, emid)
{
    selectedErrorMap = emid;
    $('legend_title').innerHTML = editErrorMap_title;
    selectDataGridRow(element.parentNode.parentNode);

    var errorMapInfo = UrlMapperAjax.callSync('GetErrorMap', selectedErrorMap);
    $('url').value = errorMapInfo['url'];
    $('code').value = errorMapInfo['code'];
    $('new_url').value = errorMapInfo['new_url'];
    $('new_code').value = errorMapInfo['new_code'];
    $('insert_time').value = errorMapInfo['insert_time'];

    $('btn_cancel').style.visibility = 'visible';
}

/**
 * Prepares the UI to edit a map
 */
function editMap(element, mid)
{
    enableMapEditingArea(true);

    selectedMap = mid;
    $('legend_title').innerHTML = editMap_title;
    selectDataGridRow(element.parentNode.parentNode);

    var mapInfo = UrlMapperAjax.callSync('GetMap', selectedMap);
    $('map_route').value  = mapInfo['map'];
    $('map_ext').value    = mapInfo['extension'];
    $('map_order').value  = mapInfo['order'];

    if (mapInfo['custom_map'] == null || mapInfo['custom_map'] == '') {
        $('custom_map_route').value  = mapInfo['map'];
    } else {
        $('custom_map_route').value  = mapInfo['custom_map'];
    }
}

/**
 * Prepares a datagrid with maps of each action
 */
function showActionMaps()
{
    if (!$('gadgets_combo').val() ||
        !$('actions_combo').val())
    {
        return false;
    }

    resetGrid('maps_datagrid', '');
    //Get maps of this action and gadget
    var result = UrlMapperAjax.callSync(
        'GetActionMaps',
        [$('gadgets_combo').value, $('actions_combo').value]
    );
    resetGrid('maps_datagrid', result);
    enableMapEditingArea(false);
}

/**
 * Cleans the action combo and fill its again
 */
function rebuildActionCombo()
{
    var combo = $('actions_combo');
    var selectedGadget = $('gadgets_combo').value;
    var actions = UrlMapperAjax.callSync('GetGadgetActions', selectedGadget);

    combo.options.length = 0;
    if (actions != false) {
        var i =0;
        actions.each(function(text, index) {
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
        $('custom_map_route').disabled  = false;
        $('btn_save').disabled   = false;
        $('btn_cancel').disabled = false;
    } else {
        selectedMap = null;
        unselectDataGridRow();
        $('map_order').value  = '';
        $('map_route').value  = '';
        $('map_ext').value    = '';
        $('custom_map_route').value  = '';
        $('custom_map_route').disabled  = true;
        $('btn_save').disabled   = true;
        $('btn_cancel').disabled = true;
    }
}

/**
 * Change new code value
 */
function changeCode()
{
    if ($('new_code').value == 410) {
        $('new_url').disabled = true;
    } else {
        $('new_url').disabled = false;
    }
}

/**
 * Get error maps list
 */
function getErrorMaps(name, offset, reset)
{
    var filters = {
        'from_date' : $('filter_from_date').value,
        'to_date'   : $('filter_to_date').value,
        'code'      : $('filter_code').value,
        'new_code'  : $('filter_new_code').value
    };

    var result = UrlMapperAjax.callSync('GetErrorMaps', {
        'offset': offset,
        'order': $('order_type').value,
        'filters': filters
    });

    if (reset) {
        $(name)[0].setCurrentPage(0);
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
function errorMapsDGAction(combo)
{
    var rows = $('errormaps_datagrid')[0].getSelectedRows();

    var filters = {
        'from_date' : $('filter_from_date').value,
        'to_date'   : $('filter_to_date').value,
        'code'      : $('filter_code').value,
        'new_code'  : $('filter_new_code').value
    };

    var confirmation = confirm(confirmErrorMapDelete);

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
                $('url').value,
                $('code').value,
                $('new_url').value,
                $('new_code').value
            ]
        );
    } else {
        UrlMapperAjax.callAsync(
            'AddErrorMap', [
                $('url').value,
                $('code').value,
                $('new_url').value,
                $('new_code').value
            ]
        );
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('alias_id').value = '-';     
    $('alias').value    = '';
    $('custom_url').value = '';
    $('delete_button').style.visibility = 'hidden';
    $('alias-combo').selectedIndex = -1;
}

/**
 * Stops doing error map action
 */
function stopErrorMapAction()
{
    $('legend_title').innerHTML = addErrorMap_title;
    $('btn_cancel').style.visibility = 'hidden';
    unselectDataGridRow();
    selectedErrorMap = null;

    $('url').value = '';
    $('code').selectedIndex = -1;
    $('new_url').value = '';
    $('new_code').selectedIndex = -1;
    $('insert_time').value = '';
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
