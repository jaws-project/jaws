/**
 * Components Javascript actions
 *
 * @category   Ajax
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var ComponentsCallback = {
    InstallGadget2: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state =
                components[selectedComponent].core_gadget ? 'core' : 'installed';
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    UpgradeGadget2: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state =
                components[selectedComponent].core_gadget ? 'core' : 'installed';
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    UninstallGadget2: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    EnableGadget: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state = 'installed';
            components[selectedComponent].disabled = false;
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    DisableGadget: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state = 'installed';
            components[selectedComponent].disabled = true;
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    InstallPlugin: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state = 'installed';
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    UninstallPlugin: function (response) {
        if (response[0]['type'] == 'response_notice') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            closeUI();
        }
        ComponentsAjax.showResponse(response);
    },

    UpdatePluginUsage: function (response) {
        if (response[0]['type'] == 'response_notice') {
            usageCache = $('plugin_usage').clone(true, true);
            if (regCache) {
                regCache = null;
                $('component_registry').remove();
            }
        }
        ComponentsAjax.showResponse(response);
    },

    UpdateRegistry: function (response) {
        if (response[0]['type'] == 'response_notice') {
            regChanges = {};
            regCache = $('component_registry').clone(true, true);
            if (usageCache) {
                usageCache = null;
                $('plugin_usage').remove();
            }
        }
        ComponentsAjax.showResponse(response);
    },

    UpdateACL: function (response) {
        if (response[0]['type'] == 'response_notice') {
            aclChanges = {};
            aclCache = $('component_acl').clone(true, true);
        }
        ComponentsAjax.showResponse(response);
    }
};

/**
 * Initiates gadgets/plugins
 */
function init()
{
    components = pluginsMode?
        ComponentsAjax.callSync('GetPlugins'):
        ComponentsAjax.callSync('GetGadgets');
    buildComponentList();
    $('tabs').getElements('li').addEvent('click', switchTab);
    $('components').getElements('h3').each(function(el, i) {
        el.addEvent('click', toggleCollapse);
        if (ComponentsStorage[Number(pluginsMode)].fetch(i)) {
            el.fireEvent('click');
        }
    });
    updateSummary();
}

/**
 * Builds gadgets/plugins listbox
 */
function buildComponentList()
{
    var sections = {},
        filter = $('#filter').val();
    sections.outdated = $('#outdated').html('');
    sections.notinstalled = $('#notinstalled').html('');
    sections.installed = $('#installed').html('');
    sections.core = $('#core').html('');
    Object.keys(components).sort().each(function(name) {
        if (components[name]['title'].test(filter, 'i')) {
            sections[components[name].state].grab(getComponentElement(components[name]));
        }
    });
    $('#components h3').show();
    $('#components ul:empty').prev('h3').hide();
}

/**
 * Builds and returns a gadget/plugin element
 */
function getComponentElement(comp)
{
    var li = new Element('li', {id:comp.name}),
        span = new Element('span').set('html', comp.title),
        img = new Element('img', {alt:comp.name}),
        a = new Element('a').set('html', actions[comp.state]);
    if (comp.disabled) {
        a.set('html', actions.disabled);
    }
    li.adopt(img, span);
    img.src = pluginsMode?
        'gadgets/Components/Resources/images/plugin.png' :
        'gadgets/' + comp.name + '/Resources/images/logo.png';
    if (comp.state !== 'core') {
        a.set('href', 'javascript:void(0);');
        a.addEvent('click', function(e) {
            e.stop();
            selectedComponent = comp.name;
            setupComponent();
        });
        li.grab(a);
    }
    if (comp.disabled) {
        li.addClass('disabled');
    }
    li.addEvent('click', selectComponent);
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
    Object.keys(components).each(function(comp) {
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
    $('s#um_installed').html(count.installed);
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
    this.toggleClass('collapsed');
    ComponentsStorage[Number(pluginsMode)].update(
        $('components').getElements('h3').indexOf(this),
        this.getProperty('class')
    );
    this.getNext('ul').toggle();
}

/**
 * Shows/hides tabs upon selected component
 */
function showHideTabs()
{
    var comp = components[selectedComponent];
    $('#tabs li').hide();
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
    $('#component_info button').hide();
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
 * Switches between Info/Regsitry/ACL UIs
 */
function switchTab(tab)
{
    tab = (typeof tab === 'string') ? tab : this.id;
    tab = $(tab).isVisible() ? tab : 'tab_info';
    $('#tabs li.active').removeClass('active');
    $(tab).addClass('active');
    $('#component_form').children().hide();
    switch (tab) {
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
        img = new Element('img'),
        h1 = new Element('h1');
    img.src = pluginsMode ? 
        'gadgets/Components/Resources/images/plugin.png': 
        'gadgets/' + components[comp].name + '/Resources/images/logo.png';
    img.alt = components[comp].title;
    h1.innerHTML = components[comp].title + ': ' + components[comp].description;
    $('#component_head').html('');
    $('#component_form').html('');
    $('#component_head').adopt(img, h1);
    $('#components li.selected').removeClass('selected');
    this.addClass('selected');

    selectedComponent = comp;
    regCache = null;
    aclCache = null;
    usageCache = null;
    showHideTabs();
    switchTab($('tabs').getElement('li.active').id);
}

/**
 * Closes component UI and clears selection in the list
 */
function closeUI()
{
    selectedComponent = null;
    $('#components li.selected').removeClass('selected');
    $('#summary').show();
    $('#component').hide();
    updateSummary();
}

/**
 * Displays useful information about gadget/plugin
 */
function componentInfo()
{
    if (!$('#component_info').length) {
        $('#component_form').html(pluginsMode ?
            ComponentsAjax.callSync('GetPluginInfo', selectedComponent):
            ComponentsAjax.callSync('GetGadgetInfo', selectedComponent)
        );
    }

    $('#summary').hide();
    $('#component').show();
    $('#component_info').show();
    showHideButtons();
}

/**
 * Displays registry keys/values of the gadget/plugin
 */
function componentRegistry(reset)
{
    if (!regCache) {
        var table = new Element('table'),
            res = ComponentsAjax.callSync('GetRegistry', [selectedComponent, pluginsMode]),
            div = new Element('div').set('html', res.ui);
        $('component_form').grab(div.getElement('div'));
        Object.each(res.data, function(value, name) {
            var label = new Element('label', {html:name, 'for':name}),
                th = new Element('th').grab(label),
                input = new Element('input', {'id':name, 'value':value}),
                td = new Element('td').grab(input),
                tr = new Element('tr').adopt(th, td);
            input.setProperty('onchange', 'onValueChange(this)');
            table.grab(tr);
        });
        $('frm_registry').grab(table);
        regCache = $('component_registry').clone(true, true);
    }
    if (reset) {
        regCache.clone(true, true).replaces($('component_registry'));
        regChanges = {};
    }
    $('#summary').hide();
    $('#component').show();
    $('#component_registry').show();
}

/**
 * Displays ACL keys/values of the gadget/plugin
 */
function componentACL(reset)
{
    if (!aclCache) {
        var table = new Element('table'),
            res = ComponentsAjax.callSync('GetACL', [selectedComponent, pluginsMode]),
            div = new Element('div').set('html', res.ui);
        aclCache = div.getElement('div');
        $('component_form').grab(div.getElement('div'));
        res.acls.each(function(acl) {
            var key_unique = acl.key_name + ':' + acl.key_subkey;
            var label = new Element('label', {html:acl.key_desc, 'for':key_unique}),
                th = new Element('th').grab(label),
                input = new Element('input', {
                    'id': key_unique,
                    'type': 'checkbox',
                    'value': acl.key_value,
                    'checked': acl.key_value
                }),
                td = new Element('td').grab(input),
                tr = new Element('tr').adopt(td, th);
            input.setProperty('onchange', 'onValueChange(this)');
            table.grab(tr);
        });
        $('frm_acl').grab(table);
        aclCache = $('component_acl').clone(true, true);
    }
    if (reset) {
        aclCache.clone(true, true).replaces($('component_acl'));
        aclChanges = {};
    }
    $('#summary').hide();
    $('#component').show();
    $('#component_acl').show();
}

/**
 * Installs, uninstalls or updates the gadget/plugin
 */
function setupComponent()
{
    var comp = components[selectedComponent];
    switch (comp.state) {
        case 'outdated':
            ComponentsAjax.callAsync('UpgradeGadget2', selectedComponent);
            break;
        case 'notinstalled':
            if (pluginsMode) {
                ComponentsAjax.callAsync('InstallPlugin', selectedComponent);
            } else {
                ComponentsAjax.callAsync('InstallGadget2', selectedComponent);
            }
            break;
        case 'installed':
            if (pluginsMode) {
                if (confirm(confirmUninstallPlugin)) {
                    ComponentsAjax.callAsync('UninstallPlugin', selectedComponent);
                }
            } else {
                if (comp.disabled) {
                    ComponentsAjax.callAsync('EnableGadget', selectedComponent);
                } else if (confirm(confirmUninstallGadget)) {
                    ComponentsAjax.callAsync('UninstallGadget2', selectedComponent);
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
    ComponentsAjax.callAsync('EnableGadget', selectedComponent);
}

/**
 * Disables the gadget
 */
function disableGadget()
{
    if (confirm(confirmDisableGadget)) {
        ComponentsAjax.callAsync('DisableGadget', selectedComponent);
    }
}

/**
 * Stores changed Registry/ACL value
 */
function onValueChange(el)
{
    switch ($('tabs').getElement('li.active').get('id')) {
        case 'tab_registry':
            regChanges[el.id] = el.value;
            break;
        case 'tab_acl':
            aclChanges[el.id] = el.checked;
            break;
    }
}

/**
 * Updates gadget registry with changed values
 */
function saveRegistry()
{
    ComponentsAjax.callAsync('UpdateRegistry', [selectedComponent, regChanges]);
}

/**
 * Updates gadget ACLs with changed values
 */
function saveACL()
{
    ComponentsAjax.callAsync('UpdateACL', [selectedComponent, aclChanges]);
}

/**
 * Displays the plugin usage tree
 */
function pluginUsage(reset)
{
    if (!usageCache) {
        var tbody = new Element('tbody'),
            res = ComponentsAjax.callSync('GetPluginUsage', selectedComponent),
            div = new Element('div').set('html', res.ui);
        $('component_form').grab(div.getElement('div'));
        res.usage.gadgets.each(function(gadget) {
            var label = new Element('label', {html:gadget.title}),
                th = new Element('th').grab(label),
                b_input = new Element('input', {type:'checkbox', name:'backend', value:gadget.name}),
                b_td = new Element('td').grab(b_input),
                f_input = new Element('input', {type:'checkbox', name:'frontend', value:gadget.name}),
                f_td = new Element('td').grab(f_input),
                tr = new Element('tr').adopt(th, b_td, f_td);
            if (res.usage.backend === '*' || res.usage.backend.indexOf(gadget.name) !== -1) {
                b_input.checked = true;
            }
            if (res.usage.frontend === '*' || res.usage.frontend.indexOf(gadget.name) !== -1) {
                f_input.checked = true;
            }
            tbody.grab(tr);
        });
        $('plugin_usage').getElement('table').grab(tbody);
        $('all_backend').checked = (res.usage.backend === '*') ? true : false;
        $('all_frontend').checked = (res.usage.frontend === '*') ? true : false;
        usageCache = $('#plugin_usage').clone(true, true);
    }
    if (reset) {
        usageCache.clone(true, true).replaces($('plugin_usage'));
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
    var backend = $('plugin_usage').getElements('input:checked[name=backend]').get('value'),
        frontend = $('plugin_usage').getElements('input:checked[name=frontend]').get('value'),
        total = $('plugin_usage').getElements('input[name=frontend]').length;
    backend = (backend.length === total) ? '*' : backend.join(',');
    frontend = (frontend.length === total) ? '*' : frontend.join(',');
    ComponentsAjax.callAsync('UpdatePluginUsage', [selectedComponent, backend, frontend]);
}

/**
 * Checks/unchecks all gadgets
 */
function usageCheckAll(el)
{
    switch (el.id) {
        case 'all_backend':
            $('plugin_usage').getElements('input[name=backend]').set('checked', el.checked);
            break;
        case 'all_frontend':
            $('plugin_usage').getElements('input[name=frontend]').set('checked', el.checked);
            break;
    }
}

/**
 * Variables
 */
var ComponentsStorage = [new JawsStorage('Gadgets'), new JawsStorage('Plugins')];
var ComponentsAjax = new JawsAjax('Components', ComponentsCallback),
    selectedComponent = null,
    components = {},
    regChanges = {},
    aclChanges = {},
    regCache = null,
    aclCache = null,
    usageCache = null;
