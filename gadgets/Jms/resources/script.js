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
        }
        showResponse(response);
    },

    uninstallgadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
        }
        showResponse(response);
    },

    disablegadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
        }
        showResponse(response);
    },

    updategadget: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'installed';
            buildComponentList();
        }
        showResponse(response);
    },

    enableplugin: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'installed';
            buildComponentList();
        }
        showResponse(response);
    },

    disableplugin: function(response) {
        if (response[0]['css'] == 'notice-message') {
            components[selectedComponent].state = 'notinstalled';
            buildComponentList();
        }
        showResponse(response);
    },

    updatepluginusage: function(response) {
        stopPluginUsage();
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
    sections.outdated = $('outdated_gadgets').set('html', '');
    sections.notinstalled = $('notinstalled_gadgets').set('html', '');
    sections.installed = $('installed_gadgets').set('html', '');
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
        'gadgets/Jms/images/plugins.png' :
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
    //console.log(li);
}

/**
 * Highlights clicked item in the component list
 */
function selectComponent()
{
    selectedComponent = this.id;
    //console.log(selectedComponent);
    $$('#components li.selected').each(function(li) {
        li.removeClass('selected');
    });
    this.addClass('selected');
    componentInfo()
    //showButtons();
}

/**
 * Displays useful information about gadget/plugin
 */
function componentInfo()
{
    var compInfo = pluginsMode?
        JmsAjax.callSync('getplugininfo', selectedComponent):
        JmsAjax.callSync('getgadgetinfo', selectedComponent);
    $('component_info').show().set('html', compInfo);
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
                JmsAjax.callAsync('enableplugin', selectedComponent);
            } else {
                JmsAjax.callAsync('installgadget', selectedComponent);
            }
            break;
        case 'installed':
            if (pluginsMode) {
                JmsAjax.callAsync('disableplugin', selectedComponent);
            } else {
                if (confirm(confirmUninstallComponent)) {
                    JmsAjax.callAsync('uninstallgadget', selectedComponent);
                }
            }
            break;
    }
}

/**
 * Show the buttons depending on the current tab and
 * the items to show
 */
function showButtons()
{
    if (pluginsMode == false) {
        switch($('only_show').value) {
        case 'notinstalled':
            $('install_button').style.display   = 'block';
            break;
        case 'installed':
            $('uninstall_button').style.display = 'block';
            $('purge_button').style.display     = 'block';
            break;
        case 'outdated':
            $('update_button').style.display    = 'block';
            break;
        }
    } else {
        switch($('only_show').value) {
        case 'notinstalled':
            $('install_button').style.display   = 'block';
            break;
        case 'installed':
            if (editingPlugins == true) {
                $('plugin_saveusage').style.display = 'block';
                $('plugin_stopusage').style.display = 'block';
            } else {
                $('uninstall_button').style.display = 'block';
                $('plugin_usage').style.display = 'block';
            }
            break;
        }
    }
}

/**
 * Edits a gadget showing basic info about it
 */
function editGadget(gadget)
{
    if (gadget.blank()) {
        return false;
    }

    if (gadget == '-') {
        return false;
    }

    cleanWorkingArea(true);

    var gadgetInfo = JmsAjax.callSync('getgadgetinfo', gadget);
    if (gadgetInfo == null) {
        return false; //Check
    }
    selectedGadget = gadget;
    $('work_area').innerHTML = gadgetInfo;
    showButtons();
}

/**
 * Shows basic plugin info
 */
function editPlugin(plugin)
{
    if (plugin.blank()) {
        return false;
    }

    editingPlugins = false;
    cleanWorkingArea(true);
    if (plugin == '-') {
        return false;
    }

    var pluginInfo = JmsAjax.callSync('getplugininfo', plugin);
    if (pluginInfo == null) {
        return false; //Check
    }

    selectedPlugin = plugin;
    $('work_area').innerHTML = pluginInfo;
    showButtons();
}

/**
 * Clean the working area
 */
function cleanWorkingArea(hideButtons)
{
    $('work_area').innerHTML = '';
    if (hideButtons != undefined) {
        if (hideButtons == true) {
            var buttons = new Array('purge_button', 'uninstall_button',
                                    'install_button', 'update_button',
                                    'plugin_usage', 'plugin_saveusage',
                                    'plugin_stopusage'
                                    );
            for(var i=0; i<buttons.length; i++) {
                if ($(buttons[i]) != undefined) {
                    $(buttons[i]).style.display = 'none';
                }
            }
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
    if (checkbox.checked == true) {
        //inputs should be located in second container (0 => first, 1 => second)
        var gadgetsCont = $$('.webfx-tree-container')[1];
        if (gadgetsCont != undefined) {
            var inputs  = gadgetsCont.getElementsByTagName('input');
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
 * show the plugin usage UI
 */
function pluginUsage()
{
    cleanWorkingArea(true);

    tree = new WebFXTree(gadgetsMsg);

    tree.openIcon = 'gadgets/Jms/images/gadgets.png';
    tree.icon = 'gadgets/Jms/images/gadgets.png';

    var gadgets = JmsAjax.callSync('getgadgetsofplugin', $('plugins_combo').value);

    var useAllTime = JmsAjax.callSync('usealways', $('plugins_combo').value);

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
    label.appendChild(document.createTextNode(useAlways));

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

    $('work_area').innerHTML = tree.toString();
    editingPlugins = true;
    showButtons();
}

/**
 * Stops editing the plugin
 */
function stopPluginUsage()
{
    var plugin = $('plugins_combo').value;
    editPlugin(plugin)
}

/**
 * Saves the plugin usage properties
 */
function savePluginUsage()
{
    var plugin  = $('plugins_combo').value;

    if ($('use_always').checked == true) {
        JmsAjax.callAsync('updatepluginusage', plugin, '*');
        return true;
    }

    var inputs = $('work_area').getElementsByTagName('input');
    var gadgets   = new Array();
    var counter = 0;
    for(var i=0; i<inputs.length; i++) {
        if (inputs[i].checked) {
            gadgets[counter] = inputs[i].value;
            counter++;
        }
    }

    JmsAjax.callAsync('updatepluginusage', plugin, gadgets);
}

/**
 * Updates the gadget/plugins view
 */
function updateView()
{
    cleanWorkingArea(true);
    if (pluginsMode == false) {
        getGadgets();
    } else {
        editingPlugins = false;
        getPlugins();
    }
}

var JmsAjax = new JawsAjax('Jms', JmsCallback),
    components = {};

var selectedComponent = null;
var selectedGadget = null;
var selectedPlugin = null;

var editingPlugins = false;

var tree = null;
var aItem = null;
