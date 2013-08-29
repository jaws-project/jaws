/**
 * Components Javascript actions
 *
 * @category   Ajax
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var ComponentsCallback = {
    installgadget: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state =
                components[selectedComponent].core_gadget ? 'core' : 'installed';
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    upgradegadget: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state =
                components[selectedComponent].core_gadget ? 'core' : 'installed';
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    uninstallgadget: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    enablegadget: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state = 'installed';
            components[selectedComponent].disabled = false;
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    disablegadget: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state = 'installed';
            components[selectedComponent].disabled = true;
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    installplugin: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state = 'installed';
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    uninstallplugin: function (response) {
        if (response[0].css === 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            closeUI();
        }
        showResponse(response);
    },

    updatepluginusage: function (response) {
        if (response[0].css === 'notice-message') {
            usageCache = _('plugin_usage').clone(true, true);
            if (regCache) {
                regCache = null;
                _('component_registry').remove();
            }
        }
        showResponse(response);
    },

    updateregistry: function (response) {
        if (response[0].css === 'notice-message') {
            regChanges = {};
            regCache = _('component_registry').clone(true, true);
            if (usageCache) {
                usageCache = null;
                _('plugin_usage').remove();
            }
        }
        showResponse(response);
    },

    updateacl: function (response) {
        if (response[0].css === 'notice-message') {
            aclChanges = {};
            aclCache = _('component_acl').clone(true, true);
        }
        showResponse(response);
    }
};

/**
 * Initiates gadgets/plugins
 */
function init()
{
    components = pluginsMode?
        ComponentsAjax.callSync('getplugins'):
        ComponentsAjax.callSync('getgadgets');
    buildComponentList();
    _('tabs').getElements('li').addEvent('click', switchTab);
    _('components').getElements('h3').each(function(el, i) {
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
    var sections = {};
    sections.outdated = _('outdated').set('html', '');
    sections.notinstalled = _('notinstalled').set('html', '');
    sections.installed = _('installed').set('html', '');
    sections.core = _('core').set('html', '');
    Object.keys(components).sort().each(function(name) {
        sections[components[name].state].grab(getComponentElement(components[name]));
    });
    _('components').getElements('h3').show();
    _('components').getElements('ul:empty').getPrevious('h3').hide();
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
        'gadgets/Components/images/plugin.png' :
        'gadgets/' + comp.name + '/images/logo.png';
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
    _('sum_installed').innerHTML = count.installed;
    _('sum_notinstalled').innerHTML = count.notinstalled;

    _('sum_total').innerHTML = count.total;
    if (!pluginsMode) {
        _('sum_disabled').innerHTML = count.disabled;
        _('sum_outdated').innerHTML = count.outdated;
        _('sum_core').innerHTML = count.core;
    }
}

/**
 * Expands/collapses gadget/plugin section
 */
function toggleCollapse()
{
    this.toggleClass('collapsed');
    ComponentsStorage[Number(pluginsMode)].update(
        _('components').getElements('h3').indexOf(this),
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
    _('tabs').getElements('li').hide();
    _('tab_info').show();
    if (comp.state === 'core' || (comp.state === 'installed' && !comp.disabled)) {
        if (comp.manage_reg) {
            _('tab_registry').show();
        }
        if (comp.manage_acl) {
            _('tab_acl').show();
        }
        if (pluginsMode) {
            _('tab_usage').show();
        }
    }
}

/**
 * Shows/hides buttons depending on the current tab and selected component
 */
function showHideButtons()
{
    var comp = components[selectedComponent];
    _('component_info').getElements('button').hide();
    if (pluginsMode) {
        switch(comp.state) {
        case 'notinstalled':
            _('btn_install').show('inline');
            break;
        case 'installed':
            _('btn_uninstall').show('inline');
            break;
        }
    } else {
        switch(comp.state) {
        case 'outdated':
            _('btn_update').show('inline');
            break;
        case 'notinstalled':
            _('btn_install').show('inline');
            break;
        case 'installed':
            _('btn_uninstall').show('inline');
            if (comp.disabled) {
                _('btn_enable').show('inline');
            } else {
                _('btn_disable').show('inline');
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
    tab = _(tab).isVisible() ? tab : 'tab_info';
    _('tabs').getElement('li.active').removeClass('active');
    _(tab).addClass('active');
    _('component_form').getChildren().hide();
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
        'gadgets/Components/images/plugin.png': 
        'gadgets/' + components[comp].name + '/images/logo.png';
    img.alt = components[comp].title;
    h1.innerHTML = components[comp].title + ': ' + components[comp].description;
    _('component_head').innerHTML = '';
    _('component_form').innerHTML = '';
    _('component_head').adopt(img, h1);
    __('#components li.selected').removeClass('selected');
    this.addClass('selected');

    selectedComponent = comp;
    regCache = null;
    aclCache = null;
    usageCache = null;
    showHideTabs();
    switchTab(_('tabs').getElement('li.active').id);
}

/**
 * Closes component UI and clears selection in the list
 */
function closeUI()
{
    selectedComponent = null;
    __('#components li.selected').removeClass('selected');
    _('summary').show();
    _('component').hide();
    updateSummary();
}

/**
 * Displays useful information about gadget/plugin
 */
function componentInfo()
{
    if (!_('component_info')) {
        _('component_form').set('html', pluginsMode ?
            ComponentsAjax.callSync('getplugininfo', selectedComponent):
            ComponentsAjax.callSync('getgadgetinfo', selectedComponent)
        );
    }

    _('summary').hide();
    _('component').show();
    _('component_info').show();
    showHideButtons();
}

/**
 * Displays registry keys/values of the gadget/plugin
 */
function componentRegistry(reset)
{
    if (!regCache) {
        var table = new Element('table'),
            res = ComponentsAjax.callSync('getregistry', selectedComponent, pluginsMode),
            div = new Element('div').set('html', res.ui);
        _('component_form').grab(div.getElement('div'));
        res.data.each(function(reg) {
            var label = new Element('label', {html:reg.key_name, 'for':reg.key_name}),
                th = new Element('th').grab(label),
                input = new Element('input', {'id':reg.key_name, 'value':reg.key_value}),
                td = new Element('td').grab(input),
                tr = new Element('tr').adopt(th, td);
            input.setProperty('onchange', 'onValueChange(this)');
            table.grab(tr);
        });
        _('frm_registry').grab(table);
        regCache = _('component_registry').clone(true, true);
    }
    if (reset) {
        regCache.clone(true, true).replaces(_('component_registry'));
        regChanges = {};
    }
    _('summary').hide();
    _('component').show();
    _('component_registry').show();
}

/**
 * Displays ACL keys/values of the gadget/plugin
 */
function componentACL(reset)
{
    if (!aclCache) {
        var table = new Element('table'),
            res = ComponentsAjax.callSync('getacl', selectedComponent, pluginsMode),
            div = new Element('div').set('html', res.ui);
        aclCache = div.getElement('div');
        _('component_form').grab(div.getElement('div'));
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
        _('frm_acl').grab(table);
        aclCache = _('component_acl').clone(true, true);
    }
    if (reset) {
        aclCache.clone(true, true).replaces(_('component_acl'));
        aclChanges = {};
    }
    _('summary').hide();
    _('component').show();
    _('component_acl').show();
}

/**
 * Installs, uninstalls or updates the gadget/plugin
 */
function setupComponent()
{
    var comp = components[selectedComponent];
    switch (comp.state) {
        case 'outdated':
            ComponentsAjax.callAsync('upgradegadget', selectedComponent);
            break;
        case 'notinstalled':
            if (pluginsMode) {
                ComponentsAjax.callAsync('installplugin', selectedComponent);
            } else {
                ComponentsAjax.callAsync('installgadget', selectedComponent);
            }
            break;
        case 'installed':
            if (pluginsMode) {
                if (confirm(confirmUninstallPlugin)) {
                    ComponentsAjax.callAsync('uninstallplugin', selectedComponent);
                }
            } else {
                if (comp.disabled) {
                    ComponentsAjax.callAsync('enablegadget', selectedComponent);
                } else if (confirm(confirmUninstallGadget)) {
                    ComponentsAjax.callAsync('uninstallgadget', selectedComponent);
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
    ComponentsAjax.callAsync('enablegadget', selectedComponent);
}

/**
 * Disables the gadget
 */
function disableGadget()
{
    if (confirm(confirmDisableGadget)) {
        ComponentsAjax.callAsync('disablegadget', selectedComponent);
    }
}

/**
 * Stores changed Registry/ACL value
 */
function onValueChange(el)
{
    switch (_('tabs').getElement('li.active').get('id')) {
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
    ComponentsAjax.callAsync('updateregistry', selectedComponent, regChanges);
}

/**
 * Updates gadget ACLs with changed values
 */
function saveACL()
{
    ComponentsAjax.callAsync('updateacl', selectedComponent, aclChanges);
}

/**
 * Displays the plugin usage tree
 */
function pluginUsage(reset)
{
    if (!usageCache) {
        var tbody = new Element('tbody'),
            res = ComponentsAjax.callSync('getpluginusage', selectedComponent),
            div = new Element('div').set('html', res.ui);
        _('component_form').grab(div.getElement('div'));
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
        _('plugin_usage').getElement('table').grab(tbody);
        _('all_backend').checked = (res.usage.backend === '*') ? true : false;
        _('all_frontend').checked = (res.usage.frontend === '*') ? true : false;
        usageCache = _('plugin_usage').clone(true, true);
    }
    if (reset) {
        usageCache.clone(true, true).replaces(_('plugin_usage'));
    }
    _('summary').hide();
    _('component').show();
    _('plugin_usage').show();
}

/**
 * Saves the plugin usage
 */
function savePluginUsage()
{
    var backend = _('plugin_usage').getElements('input:checked[name=backend]').get('value'),
        frontend = _('plugin_usage').getElements('input:checked[name=frontend]').get('value'),
        total = _('plugin_usage').getElements('input[name=frontend]').length;
    backend = (backend.length === total) ? '*' : backend.join(',');
    frontend = (frontend.length === total) ? '*' : frontend.join(',');
    ComponentsAjax.callAsync('updatepluginusage', selectedComponent, backend, frontend);
}

/**
 * Checks/unchecks all gadgets
 */
function usageCheckAll(el)
{
    switch (el.id) {
        case 'all_backend':
            _('plugin_usage').getElements('input[name=backend]').set('checked', el.checked);
            break;
        case 'all_frontend':
            _('plugin_usage').getElements('input[name=frontend]').set('checked', el.checked);
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
