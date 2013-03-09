/**
 * Jms Javascript actions
 *
 * @category   Ajax
 * @package    Jms
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var JmsCallback = {
    updategadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 
                components[selectedComponent].core_gadget? 'core' : 'installed';
            buildComponentList();
            cancel();
        }
        showResponse(response);
    },

    installgadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 
                components[selectedComponent].core_gadget? 'core' : 'installed';
            buildComponentList();
            cancel();
        }
        showResponse(response);
    },

    uninstallgadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            cancel();
        }
        showResponse(response);
    },

    disablegadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            cancel();
        }
        showResponse(response);
    },

    installplugin: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'installed';
            buildComponentList();
            cancel();
        }
        showResponse(response);
    },

    uninstallplugin: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
            cancel();
        }
        showResponse(response);
    },

    updatepluginusage: function(response) {
        cancel();
        showResponse(response);
    }
}

/**
 * Initiates JMS gadgets/plugins
 */
function init()
{
    components = pluginsMode?
        JmsAjax.callSync('getplugins'):
        JmsAjax.callSync('getgadgets');
    buildComponentList();
    $('components').getElements('h3').addEvent('click', toggleSection);
}

/**
 * Builds the gadgets/plugins listbox
 */
function buildComponentList()
{
    var sections = {};
    sections.outdated = $('outdated').set('html', '');
    sections.notinstalled = $('notinstalled').set('html', '');
    sections.installed = $('installed').set('html', '');
    sections.core = $('core').set('html', '');
    Object.keys(components).sort().each(function(name) {
        sections[components[name]['state']].grab(getComponentItem(components[name]));
    });
    $('components').getElements('h3').show();
    $('components').getElements('ul:empty').getPrevious('h3').hide();
}

/**
 * Builds and returns a gadget/plugin item
 */
function getComponentItem(comp)
{
    var li = new Element('li', {id:comp.realname}),
        span = new Element('span').set('html', comp.name),
        img = new Element('img', {alt:comp.realname}),
        a = new Element('a').set('html', actions[comp.state]);
    li.adopt(img, span);
    img.src = pluginsMode?
        'gadgets/Jms/images/plugin.png' :
        'gadgets/' + comp.realname + '/images/logo.png';
    if (comp.state !== 'core') {
        a.href = 'javascript:void(0);';
        a.addEvent('click', function(e) {
            e.stop();
            selectedComponent = comp.realname;
            setupComponent();
        });
        li.grab(a);
    }
    li.addEvent('click', selectComponent);
    return li;
}

/**
 * Expands/Collapses gadget/plugin section
 */
function toggleSection()
{
    this.toggleClass('collapsed');
    this.getNext('ul').toggle();
}

/**
 * Highlights clicked item in the component list
 */
function selectComponent()
{
    selectedComponent = this.id;
    editPluginMode = false;
    $$('#components li.selected').removeClass('selected');
    this.addClass('selected');
    componentInfo();
    showButtons();
}

/**
 * Deselects component in the list and hides info page
 */
function cancel()
{
    selectedComponent = null;
    editPluginMode = false;
    $$('#components li.selected').removeClass('selected');
    $('actions').getElements('button').hide();
    $('component_ui').hide();
}

/**
 * Displays useful information about gadget/plugin
 */
function componentInfo()
{
    var compInfo = pluginsMode?
        JmsAjax.callSync('getplugininfo', selectedComponent):
        JmsAjax.callSync('getgadgetinfo', selectedComponent);
    $('component_ui').show().set('html', compInfo);
}

/**
 * Installs, uninstalls or updates the gadget/plugin
 */
function setupComponent()
{
    switch (components[selectedComponent].state) {
        case 'outdated':
            JmsAjax.callAsync('updategadget', selectedComponent);
            break;
        case 'notinstalled':
            if (pluginsMode) {
                JmsAjax.callAsync('installplugin', selectedComponent);
            } else {
                JmsAjax.callAsync('installgadget', selectedComponent);
            }
            break;
        case 'installed':
            if (pluginsMode) {
                if (confirm(confirmUninstallPlugin)) {
                    JmsAjax.callAsync('uninstallplugin', selectedComponent);
                }
            } else {
                if (confirm(confirmUninstallGadget)) {
                    JmsAjax.callAsync('uninstallgadget', selectedComponent);
                }
            }
            break;
    }
}

/**
 * Disables the gadget
 */
function disableGadget()
{
    if (confirm(confirmDisableGadget)) {
        JmsAjax.callAsync('disablegadget', selectedComponent);
    }
}

/**
 * Shows/hides buttons depending on the current tab and selected component
 */
function showButtons()
{
    var state = components[selectedComponent].state;
    $('actions').getElements('button').hide();
    $('btn_cancel').show('inline');
    if (pluginsMode) {
        switch(state) {
        case 'notinstalled':
            $('btn_install').show('inline');
            break;
        case 'installed':
            if (editPluginMode == true) {
                $('btn_save').show('inline');
            } else {
                $('btn_uninstall').show('inline');
                $('btn_usage').show('inline');
            }
            break;
        }
    } else {
        switch(state) {
        case 'outdated':
            $('btn_update').show('inline');
            break;
        case 'notinstalled':
            $('btn_install').show('inline');
            break;
        case 'installed':
            $('btn_uninstall').show('inline');
            $('btn_disable').show('inline');
            break;
        }
    }
}

/**
 * Displays the plugin usage tree
 */
function pluginUsage()
{
    var gadgets = JmsAjax.callSync('getpluginusage', selectedComponent),
        tree = new WebFXTree(pluginUsageDesc),
        div = new Element('div'),
        label = new Element('label', {'for':'use_always'}),
        chkbox = new Element('input', {
            type: 'checkbox',
            id: 'use_always',
            value: 'use_always',
            checked: gadgets['always'].value
        }),
        rootNode;
    label.set('html', gadgets['always'].text);
    tree.openIcon = 'gadgets/Jms/images/gadgets.png';
    tree.icon = 'gadgets/Jms/images/gadgets.png';
    div.adopt(chkbox, label);
    rootNode = new WebFXTreeItem(div.innerHTML);
    tree.add(rootNode);
    // does not work because of webfxTree bug
    // rootNode.expand();

    delete gadgets['always'];
    Object.keys(gadgets).each(function(gadget) {
        var div = new Element('div'),
            label = new Element('label', {'for':gadget}),
            chkbox = new Element('input', {
                type: 'checkbox',
                id: gadget,
                value: gadget,
                checked: gadgets[gadget].value
            });
        label.set('html', gadgets[gadget].text);
        div.adopt(chkbox, label);
        rootNode.add(new WebFXTreeItem(div.innerHTML));
    });

    $('component_ui').show().set('html', tree.toString());
    editPluginMode = true;
    showButtons();
}

/**
 * Saves the plugin usage
 */
function savePluginUsage()
{
    var selection = '*';
    if (!$('use_always').checked) {
        selection = $('bigTree').getElements('input:checked').get('value');
        selection = selection.erase('use_always').join(',');
    }
    JmsAjax.callAsync('updatepluginusage', selectedComponent, selection);
}

/**
 * Variables
 */
var JmsAjax = new JawsAjax('Jms', JmsCallback),
    components = {},
    selectedComponent = null,
    editPluginMode = false;
