/**
 * Components Javascript actions
 *
 * @category   Ajax
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2004-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var ComponentsCallback = {
    InstallGadget2: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state =
                components[selectedComponent].core_gadget ? 'core' : 'installed';
            buildComponentList();
            closeUI();
        }
    },

    UpgradeGadget2: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state =
                components[selectedComponent].core_gadget ? 'core' : 'installed';
            buildComponentList();
            closeUI();
        }
    },

    UninstallGadget2: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            closeUI();
        }
    },

    EnableGadget: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state = 'installed';
            components[selectedComponent].disabled = false;
            buildComponentList();
            closeUI();
        }
    },

    DisableGadget: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state = 'installed';
            components[selectedComponent].disabled = true;
            buildComponentList();
            closeUI();
        }
    },

    InstallPlugin: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state = 'installed';
            buildComponentList();
            closeUI();
        }
    },

    UninstallPlugin: function (response) {
        if (response['type'] == 'alert-success') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            closeUI();
        }
    },

    UpdatePluginUsage: function (response) {
        if (response['type'] == 'alert-success') {
            usageCache = $('#plugin_usage').clone(true, true);
            if (regCache) {
                regCache = null;
                $('#component_registry').remove();
            }
        }
    },

    UpdateRegistry: function (response) {
        if (response['type'] == 'alert-success') {
            regChanges = {};
            regCache = $('#component_registry').clone(true, true);
            if (usageCache) {
                usageCache = null;
                $('#plugin_usage').remove();
            }
        }
    },

    UpdateACL: function (response) {
        if (response['type'] == 'alert-success') {
            aclChanges = {};
            aclCache = $('#component_acl').clone(true, true);
        }
    }
};

/**
 * Initiates gadgets/plugins
 */
function init()
{
    components = pluginsMode?
        ComponentsAjax.call('GetPlugins', {}, false, {'async': false}):
        ComponentsAjax.call('GetGadgets', {}, false, {'async': false});
    buildComponentList();
    $('#tabs').find('li').on('click', switchTab);
    $('#components').find('h3').each(function(i) {
        $(this).on('click', toggleCollapse);
        if (ComponentsStorage[Number(pluginsMode)].fetch(i)) {
            $(this).trigger('click');
        }
    });
    updateSummary();
}

/**
 * Builds gadgets/plugins list box
 */
function buildComponentList()
{
    var sections = {},
        filter = $('#filter').val();
    sections.outdated = $('#outdated').empty();
    sections.notinstalled = $('#notinstalled').empty();
    sections.installed = $('#installed').empty();
    sections.core = $('#core').empty();
    $.each(Object.keys(components).sort(), function() {
        if (components[this].title.toLowerCase().indexOf(filter.toLowerCase()) >= 0) {
            sections[components[this].state].append(getComponentElement(components[this]));
        }
    });
    var componentsEl = $('#components');
    componentsEl.find('h3').show();
    componentsEl.find('ul:empty').prev('h3').hide();
}

/**
 * Builds and returns a gadget/plugin element
 */
function getComponentElement(comp)
{
    var li = $('<li>').attr('id', comp.name),
        span = $('<span>').html(comp.title),
        img = $('<img>').attr('alt', comp.name),
        a = $('<a>').html(actions[comp.state]);
    if (comp.disabled) {
        a.html(actions.disabled);
    }
    li.append(img, span);
    img.attr('src', pluginsMode?
        'gadgets/Components/Resources/images/plugin.png' :
        'gadgets/' + comp.name + '/Resources/images/logo.png');
    if (comp.state !== 'core') {
        a.attr('href', 'javascript:void(0);');
        a.on('click', function(e) {
            // TODO: check it again
            //e.stop();
            selectedComponent = comp.name;
            setupComponent();
        });
        li.append(a);
    }
    if (comp.disabled) {
        li.addClass('disabled');
    }
    li.on('click', selectComponent);
    return li;
}

/**
 * Updates gadgets/plugins summary
 */
function updateSummary()
{
    var count = {
        outdated: 0,
        disabled: 0,
        installed: 0,
        notinstalled: 0,
        core: 0,
        total: 0
    };
    $.each(Object.keys(components), function(i, comp) {
        switch (components[comp].state) {
            case 'outdated':
                count.outdated++;
                break;
            case 'notinstalled':
                count.notinstalled++;
                break;
            case 'installed':
                count.installed++;
                if (components[comp].disabled) {
                    count.disabled++;
                }
                break;
            case 'core':
                count.core++;
                break;
        }
        count.total++;
    });
    $('#sum_installed').html(count.installed);
    $('#sum_notinstalled').html(count.notinstalled);

    $('#sum_total').html(count.total);
    if (!pluginsMode) {
        $('#sum_disabled').html(count.disabled);
        $('#sum_outdated').html(count.outdated);
        $('#sum_core').html(count.core);
    }
}

/**
 * Expands/collapses gadget/plugin section
 */
function toggleCollapse()
{
    $(this).toggleClass('collapsed');
    ComponentsStorage[Number(pluginsMode)].update(
        $('#components').find('h3').index($(this)),
        $(this).attr('class')
    );
    $(this).next('ul').toggle();
}

/**
 * Shows/hides tabs upon selected component
 */
function showHideTabs()
{
    var comp = components[selectedComponent];
    $('#tabs').find('li').hide();
    $('#tab_info').show();
    if (comp.state === 'core' || (comp.state === 'installed' && !comp.disabled)) {
        if (comp.manage_reg) {
            $('#tab_registry').show();
        }
        if (comp.manage_acl) {
            $('#tab_acl').show();
        }
        if (pluginsMode) {
            $('#tab_usage').show();
        }
    }
}

/**
 * Shows/hides buttons depending on the current tab and selected component
 */
function showHideButtons()
{
    var comp = components[selectedComponent];
    $('#component_info').find('button').hide();
    if (pluginsMode) {
        switch(comp.state) {
        case 'notinstalled':
            $('#btn_install').css('display', 'inline');
            break;
        case 'installed':
            $('#btn_uninstall').css('display', 'inline');
            break;
        }
    } else {
        switch(comp.state) {
        case 'outdated':
            $('#btn_update').css('display', 'inline');
            break;
        case 'notinstalled':
            $('#btn_install').css('display', 'inline');
            break;
        case 'installed':
            $('#btn_uninstall').css('display', 'inline');
            if (comp.disabled) {
                $('#btn_enable').css('display', 'inline');
            } else {
                $('#btn_disable').css('display', 'inline');
            }
            break;
        }
    }
}

/**
 * Switches between Info/Registry/ACL UIs
 */
function switchTab(tab)
{
    tab = (typeof tab === 'string')? $('#' + tab) : $('#' + this.id);
    if (!tab.is(':visible')) {
        tab = $('#tab_info');
    }
    $('#tabs').find('li.active').removeClass('active');
    tab.addClass('active');
    $('#component_form').find('> div').hide();

    switch (tab.attr('id')) {
        case 'tab_info':
            componentInfo();
            break;
        case 'tab_registry':
            componentRegistry();
            break;
        case 'tab_acl':
            componentACL();
            break;
        case 'tab_usage':
            pluginUsage();
            break;
    }
}

/**
 * Highlights clicked item in the components list
 */
function selectComponent()
{
    var comp = this.id,
        img = $('<img>'),
        h1 = $('<h1>');
    img.attr('src', pluginsMode ?
        'gadgets/Components/Resources/images/plugin.png':
        'gadgets/' + components[comp].name + '/Resources/images/logo.png');
    img.attr('alt', components[comp].title);
    h1.html(components[comp].title + ': ' + components[comp].description);
    $('#component_head').empty().append(img, h1);
    $('#component_form').empty();
    $('#components').find('li.selected').removeClass('selected');
    $(this).addClass('selected');

    selectedComponent = comp;
    regCache = null;
    aclCache = null;
    usageCache = null;
    showHideTabs();
    switchTab($('#tabs').find('li.active').attr('id'));
}

/**
 * Closes component UI and clears selection in the list
 */
function closeUI()
{
    selectedComponent = null;
    $('#components').find('li.selected').removeClass('selected');
    $('#summary').show();
    $('#component').hide();
    updateSummary();
}

/**
 * Displays useful information about gadget/plugin
 */
function componentInfo()
{
    var infoEl = $('#component_info').show();
    if (!infoEl.length) {
        $('#component_form').html(pluginsMode ?
            ComponentsAjax.call('GetPluginInfo', selectedComponent, false, {'async': false}):
            ComponentsAjax.call('GetGadgetInfo', selectedComponent, false, {'async': false})
        );
    }
    $('#summary').hide();
    $('#component').show();

    showHideButtons();
}

/**
 * Displays registry keys/values of the gadget/plugin
 */
function componentRegistry(reset)
{
    if (!regCache) {
        var table = $('<table>'),
            res = ComponentsAjax.call('GetRegistry', [selectedComponent, pluginsMode], false, {'async': false}),
            div = $('<div>').html(res.ui);
        $('#component_form').append(div.find('div'));
        $.each(res.data, function(name, value) {
            var label = $('<label>').html(name).attr('for', name),
                th = $('<th>').append(label),
                input = $('<input>').attr('id', name);
            switch (typeof value) {
                case 'boolean':
                    input.attr('type', 'checkbox').prop('checked', value);
                    break;
                case 'number':
                    input.attr('type', 'number').val(value);
                    break;
                default:
                    input.attr('type', 'text').val(value);
            }
            var td = $('<td>').append(input),
                tr = $('<tr>').append(th, td);
            input.on('change', onValueChange);
            table.append(tr);
        });
        $('#frm_registry').append(table);
        regCache = $('#component_registry').clone(true, true);
    }

    var regEl = $('#component_registry');
    if (reset) {
        regEl.html(regCache.clone(true, true));
        regChanges = {};
    }
    $('#summary').hide();
    $('#component').show();
    regEl.show();
    regEl.next('.actions').show();
}

/**
 * Displays ACL keys/values of the gadget/plugin
 */
function componentACL(reset)
{
    if (!aclCache) {
        var table = $('<table>'),
            res = ComponentsAjax.call('GetACL', [selectedComponent, pluginsMode], false, {'async': false}),
            div = $('<div>').html(res.ui);
        $('#component_form').append(div.find('div'));
        $.each(res.acls, function(i, acl) {
            var key_unique = acl.key_name + ':' + acl.key_subkey,
                label = $('<label>').html(acl.key_desc).attr('for', key_unique),
                th = $('<th>').append(label),
                input = $('<input>').attr({
                    'id': key_unique,
                    'type': 'checkbox',
                    'value': 1,
                }).prop('checked', acl.key_value),
                td = $('<td>').append(input),
                tr = $('<tr>').append(td, th);
            input.on('change', onValueChange);
            table.append(tr);
        });
        $('#frm_acl').append(table);
        aclCache = $('#component_acl').clone(true, true);
    }

    var aclEl = $('#component_acl');
    if (reset) {
        aclEl.replaceWith(aclCache.clone(true, true));
        aclChanges = {};
    }
    $('#summary').hide();
    $('#component').show();
    aclEl.show();
    aclEl.next('.actions').show();
}

/**
 * Installs, uninstalls or updates the gadget/plugin
 */
function setupComponent()
{
    var comp = components[selectedComponent];
    switch (comp.state) {
        case 'outdated':
            ComponentsAjax.call('UpgradeGadget2', selectedComponent);
            break;
        case 'notinstalled':
            if (pluginsMode) {
                ComponentsAjax.call('InstallPlugin', selectedComponent);
            } else {
                ComponentsAjax.call('InstallGadget2', selectedComponent);
            }
            break;
        case 'installed':
            if (pluginsMode) {
                if (confirm(Jaws.gadgets.Components.defines.confirmUninstallPlugin)) {
                    ComponentsAjax.call('UninstallPlugin', selectedComponent);
                }
            } else {
                if (comp.disabled) {
                    ComponentsAjax.call('EnableGadget', selectedComponent);
                } else if (confirm(Jaws.gadgets.Components.defines.confirmUninstallGadget)) {
                    ComponentsAjax.call('UninstallGadget2', selectedComponent);
                }
            }
            break;
    }
}

/**
 * Enables the gadget
 */
function enableGadget()
{
    ComponentsAjax.call('EnableGadget', selectedComponent);
}

/**
 * Disables the gadget
 */
function disableGadget()
{
    if (confirm(Jaws.gadgets.Components.defines.confirmDisableGadget)) {
        ComponentsAjax.call('DisableGadget', selectedComponent);
    }
}

/**
 * Stores changed Registry/ACL value
 */
function onValueChange()
{
    switch ($('#tabs').find('li.active').attr('id')) {
        case 'tab_registry':
            switch (this.type) {
                case 'checkbox':
                    regChanges[this.id] = this.checked;
                    break;
                case 'number':
                    regChanges[this.id] = parseFloat(this.value);
                    break;
                default:
                    regChanges[this.id] = this.value;
                    break;
            }
            break;
        case 'tab_acl':
            aclChanges[this.id] = this.checked;
            break;
    }
}

/**
 * Updates gadget registry with changed values
 */
function saveRegistry()
{
    ComponentsAjax.call('UpdateRegistry', [selectedComponent, regChanges]);
}

/**
 * Updates gadget ACLs with changed values
 */
function saveACL()
{
    ComponentsAjax.call('UpdateACL', [selectedComponent, aclChanges]);
}

/**
 * Displays the plugin usage tree
 */
function pluginUsage(reset)
{
    if (!usageCache) {
        var tbody = $('<tbody>'),
            res = ComponentsAjax.call('GetPluginUsage', selectedComponent, false, {'async': false}),
            div = $('<div>').html(res.ui);
        $('#component_form').append(div.first('div'));
        $.each(res.usage.gadgets, function(i, gadget) {
            var label = $('<label>').html(gadget.title),
                th = $('<th>').append(label),
                b_input = $('<input>').attr('type', 'checkbox').attr('name', 'backend').val(gadget.name),
                b_td = $('<td>').append(b_input),
                f_input = $('<input>').attr('type', 'checkbox').attr('name', 'frontend').val(gadget.name),
                f_td = $('<td>').append(f_input),
                tr = $('<tr>').append(th, b_td, f_td);
            if (res.usage.backend === '*' || res.usage.backend.indexOf(gadget.name) !== -1) {
                b_input.attr('checked', true);
            }
            if (res.usage.frontend === '*' || res.usage.frontend.indexOf(gadget.name) !== -1) {
                f_input.attr('checked', true);
            }
            tbody.append(tr);
        });
        $('#plugin_usage').find('table').append(tbody);
        $('#all_backend').attr('checked', (res.usage.backend === '*'));
        $('#all_frontend').attr('checked', (res.usage.frontend === '*'));
        usageCache = $('#plugin_usage').clone(true, true);
    }
    if (reset) {
        $('#plugin_usage').html(usageCache.clone(true, true));
    }
    $('#summary').hide();
    $('#component').show();
    $('#plugin_usage').show();
}

/**
 * Saves the plugin usage
 */
function savePluginUsage()
{
    var usageEl = $('#plugin_usage'),
        backend = usageEl.find('input:checked[name=backend]').map(function() { return this.value; }).toArray(),
        frontend = usageEl.find('input:checked[name=frontend]').map(function() { return this.value; }).toArray(),
        total = usageEl.find('input[name=frontend]').length;
    backend = (backend.length === total) ? '*' : backend.join(',');
    frontend = (frontend.length === total) ? '*' : frontend.join(',');
    ComponentsAjax.call('UpdatePluginUsage', [selectedComponent, backend, frontend]);
}

/**
 * Checks/unchecks all gadgets
 */
function usageCheckAll(el)
{
    var usageEl = $('#plugin_usage');
    switch (el.id) {
        case 'all_backend':
            usageEl.find('input[name=backend]').attr('checked', el.checked);
            break;
        case 'all_frontend':
            usageEl.find('input[name=frontend]').attr('checked', el.checked);
            break;
    }
}

$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'Gadgets':
            actions = {
                outdated: Jaws.gadgets.Components.defines.lbl_update,
                disabled: Jaws.gadgets.Components.defines.lbl_enable,
                installed: Jaws.gadgets.Components.defines.lbl_uninstall,
                notinstalled: Jaws.gadgets.Components.defines.lbl_install
            };
            pluginsMode = false;
            break;

        case 'Plugins':
            actions = {
                installed: Jaws.gadgets.Components.defines.lbl_uninstall,
                notinstalled: Jaws.gadgets.Components.defines.lbl_install
            };
            pluginsMode = true;
            break;
    }

    init();
});

/**
 * Variables
 */
var ComponentsStorage = [new JawsStorage('Gadgets'), new JawsStorage('Plugins')];
var ComponentsAjax = new JawsAjax('Components', ComponentsCallback),
    actions = {},
    pluginsMode = false,
    selectedComponent = null,
    components = {},
    regChanges = {},
    aclChanges = {},
    regCache = null,
    aclCache = null,
    usageCache = null;
