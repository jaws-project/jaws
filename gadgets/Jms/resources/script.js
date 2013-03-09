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
    installgadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'installed';
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

    updategadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'installed';
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
    img.src = pluginsMode?
        'gadgets/Jms/images/plugin.png' :
        'gadgets/' + comp.realname + '/images/logo.png';
    a.href = 'javascript:void(0);';
    a.addEvent('click', function(e) {
        e.stop();
        selectedComponent = comp.realname;
        setupComponent();
    });
    li.addEvent('click', selectComponent);
    li.adopt(img, span, a);
    return li;
}

/**
 * Highlights clicked item in the component list
 */
function selectComponent()
{
    selectedComponent = this.id;
    $$('#components li.selected').removeClass('selected');
    this.addClass('selected');
    componentInfo()
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
 * Check which gadget has been selected, if 'use_always' was checked
 * and user decided to also select a gadget then 'use_always' become off
 */
function selectGadgetPlugin(checkbox)
{
    if (checkbox.checked == true) {
        $('use_always').checked = false;
        return true;
    }
}

/**
 * Check if user has selected all gadgets to be used
 */
function checkAllGadgets(checkbox)
{
    alert(checkbox);
    if (checkbox.checked == true) {
        //inputs should be located in second container (0 => first, 1 => second)
        var gadgetsCont = $$('.webfx-tree-container')[1];
        if (gadgetsCont != undefined) {
            var inputs = gadgetsCont.getElementsByTagName('input');
            for (var i=0; i<inputs.length; i++) {
                inputs[i].checked = false;
            }
        }
        aItem.collapseAll();
    } else {
        aItem.expandAll();
    }
}

/**
 * displays the plugin usage tree
 */
function pluginUsage()
{
    var gadgets = JmsAjax.callSync('getgadgetsofplugin', selectedComponent);
    var useAllTime = JmsAjax.callSync('usealways', selectedComponent);
    var tree = new WebFXTree('gadgetsMsg');

    tree.openIcon = 'gadgets/Jms/images/gadgets.png';
    tree.icon = 'gadgets/Jms/images/gadgets.png';

    var div = document.createElement('div');

    var chkbox = document.createElement('input');
    chkbox.setAttribute('type', 'checkbox');
    chkbox.setAttribute('name', 'use_always');
    chkbox.setAttribute('value', 'use_always');
    chkbox.setAttribute('id', 'use_always');
    if (useAllTime == true) {
        chkbox.defaultChecked = true;
        chkbox.setAttribute('checked', true);
    }
    chkbox.onclick = function() {
        checkAllGadgets(this);
    }
    chkbox.setAttribute('changed', false);

    var label = document.createElement('label');
    label.htmlFor = 'use_always';
    label.appendChild(document.createTextNode('useAlways'));

    div.appendChild(chkbox);
    div.appendChild(label);

    aItem = new WebFXTreeItem(div.innerHTML);
    tree.add(aItem);

    for(gadget in gadgets) {
        if (typeof(gadgets[gadget]) == 'function') {
            continue;
        }

        //Create checkbox with its label and all that sexy stuff
        var div = document.createElement('div');

        var chkbox = document.createElement('input');
        chkbox.setAttribute('type', 'checkbox');
        chkbox.setAttribute('name', 'gadgets[]');
        chkbox.setAttribute('value', gadgets[gadget]['gadget']);
        if (gadgets[gadget]['value'] == true) {
            chkbox.defaultChecked = true;
            chkbox.setAttribute('checked', true);
        }
        //Little trick to know which values have changed their values
        chkbox.onclick = function() {
            selectGadgetPlugin(this);
        }
        chkbox.setAttribute('id', gadgets[gadget]['gadget']);

        var label = document.createElement('label');
        label.htmlFor = gadgets[gadget]['gadget'];
        label.appendChild(document.createTextNode(gadgets[gadget]['gadget_t']));

        div.appendChild(chkbox);
        div.appendChild(label);

        var gadgetItem = new WebFXTreeItem(div.innerHTML);
        aItem.add(gadgetItem);
    }

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
    //console.log(selection);
    JmsAjax.callAsync('updatepluginusage', selectedComponent, selection);
}

/**
 * Variables
 */
var JmsAjax = new JawsAjax('Jms', JmsCallback),
    components = {},
    selectedComponent = null,
    editPluginMode = false,
    aItem = null;
