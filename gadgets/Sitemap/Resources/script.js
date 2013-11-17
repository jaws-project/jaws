/**
 * Sitemap JS actions
 *
 * @category   Ajax
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright  2006-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
var ssitems = new Array();
var ssitems1d = new Array();
var references = new Array();
var currentType = '';
var currentID = '';
var firstElement = '';
var editLegend = '';
var currentAction = 'EDIT';

var SitemapCallback = {
    GetReferences: function(response) {
        references[currentType] = response;
        populateReferences(response);
    },

    PingSitemap: function(response) {
        showResponse(response);
    },
    
    NewItem: function(response) {
        if (response[0]['type'] == 'response_notice') {
            currentID = response['id'];
            getItems();
        }
        showResponse(response);
    },

    UpdateItem: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getItems();
        }
        showResponse(response);
    },

    DeleteItem: function(response) {
        if (response[0]['type'] == 'response_notice') {
            currentID = '';
            getItems();
        }
        showResponse(response);
    },

    MoveItem: function(response) {
        if (response[0]['type'] == 'response_notice') {
            getItems();
        }
        showResponse(response);
    }
}

function createTree(data, depth) {
    strHTML = '';
    empty = true;
    for (i in data) {
        if (data[i]['id']) {
            empty = false;
            if (firstElement == '')
                firstElement = data[i]['id'];
            ssitems1d[data[i]['id']] = new Array();
            ssitems1d[data[i]['id']]['id']          = data[i]['id'];
            ssitems1d[data[i]['id']]['parent_id']   = data[i]['parent_id'];
            ssitems1d[data[i]['id']]['title']       = data[i]['title'].defilter();
            ssitems1d[data[i]['id']]['shortname']   = data[i]['shortname'];
            ssitems1d[data[i]['id']]['type']        = data[i]['rfc_type'];
            ssitems1d[data[i]['id']]['reference']   = data[i]['reference'];
            ssitems1d[data[i]['id']]['path']        = data[i]['path'];
            ssitems1d[data[i]['id']]['position']    = data[i]['rank'];
            ssitems1d[data[i]['id']]['url']         = data[i]['url'];
            ssitems1d[data[i]['id']]['priority']    = data[i]['priority'];
            ssitems1d[data[i]['id']]['changefreq']  = data[i]['changefreq'];
            
            // Parents combo
            space = '';
            for (j=1; j<= depth; j++) {
                space = space + ' → '
            }
            $('ssparent').options[$('ssparent').options.length] = new Option(space + data[i]['title'], data[i]['id']);
            // Tree
            strHTML = strHTML + '<span id="ssf' + data[i]['id'] + '" class="folder"><a id="ssa' + data[i]['id'] + '" href="javascript:void(0);" onclick="selectItem(' + data[i]['id'] + ');">' + data[i]['title'] + '</a></span>'
            if (data[i]['childs'].length > 0) {
                strHTML = strHTML + '<div id="ssd' + data[i]['id'] + 'childs" class="itemchilds">';
                strHTML = strHTML + createTree(data[i]['childs'], depth + 1);
                strHTML = strHTML + '</div>';
            }
        } else {
            continue;
        }
    }
    if (empty) {
        newItem();
        return '<div style="text-align: center; padding-top: 50px; color: #666;">' + emptyMessage + '</div>';
    } else {
        return strHTML;
    }
}

function getItems() {
    var ssitems = SitemapAjax.callSync('GetItems');
    // Empty parent combo
    if ($('ssparent').length>0) {
        for (i=$('ssparent').options.length-1; i>=1; i--) {
            $('ssparent').options[i] = null;
        }
    }
    $('ssitems').innerHTML = createTree(ssitems, 0);
    if (currentID == '') {
        currentID = firstElement;
    }
    selectItem(currentID);
}

Array.prototype.inArray = function (value)
{
    for (var i=0; i < this.length; i++) {
        // Matches identical (===), not just similar (==).
        if (this[i] === value) {
            return true;
        }
    }
    return false;
};

var selectedItems = new Array();
var previousID = 0;

function selectItem(id) {
    if ((!id) || (!$('ssf' + id))) return;
    // FIXME: We need to find a way to expand tree when
    // given id is not at top level...
    if (currentAction == 'NEW') {
        $('delete_button').style.display = 'inline';
    }
    currentAction = 'EDIT';
    currentID = id;
    if (previousID != currentID) {
        // populate form
        if (editLegend != '')
            $('ssfieldset_legend').innerHTML = editLegend;
        $('sstitle').focus();
        $('ssid').value = ssitems1d[id]['id'];
        $('sstitle').value = ssitems1d[id]['title'];
        $('sstype').value = ssitems1d[id]['type'];
        createReference(ssitems1d[id]['type']);
        $('ssshortname').value = ssitems1d[id]['shortname'];
    }
    for (i=$('sspriority').options.length-1; i>=1; i--) {
        if ($('sspriority').options[i].value == ssitems1d[id]['priority']) {
            $('sspriority').options[i].selected = true;
        }
    }
    for (i=$('sschangefreq').options.length-1; i>=1; i--) {
        if ($('sschangefreq').options[i].value == ssitems1d[id]['changefreq']) {
            $('sschangefreq').options[i].selected = true;
        }
    }
    
    $('ssreference').value = ssitems1d[id]['reference'];
    $('ssparent').value = ssitems1d[id]['parent_id'];
    $('ssf' + id).style.backgroundColor = '#fafafa';
    $('ssf' + id).style.borderColor = '#ddd';
    if ((id != previousID) && ($('ssf' + previousID))) {
        $('ssf' + previousID).style.backgroundColor = '#fff';
        $('ssf' + previousID).style.borderColor = '#fff';
    }
    if (selectedItems.inArray(id)) {
        // CLOSE
        selectedItems.splice(id,1);
        $('ssa' + id).style.backgroundImage = 'url(\'gadgets/Sitemap/Resources/images/folder.png\')';
        if ($('ssd' + id + 'childs')) {
            $('ssd' + id + 'childs').style.display = 'none';
        }
    } else {
        // OPEN
        selectedItems[id] = id;
        if ($('ssd' + id + 'childs')) {
            $('ssa' + id).style.backgroundImage = 'url(\'gadgets/Sitemap/Resources/images/folder-open.png\')';
            $('ssd' + id + 'childs').style.display = 'block';
        }
    }
    previousID = id;
}

/**
 * Creates or 'copies' an existent Title combo
 */
function createSelectElement()
{
    if ($('ssfieldset').selectReference == undefined) {
        var combo = document.createElement('select');
        combo.setAttribute('id', 'ssreference');
        combo.style.width = '300px';
        $('ssfieldset').selectReference = combo;
        return combo;
    } else {
        var combo = $('ssfieldset').selectReference;
        while(combo.options.length != 0) {
            combo.options[0] = null;
        }
        return combo;
    }
}

/**
 * Creates or 'copies' an existent URL combo
 */
function createInputElement()
{
    if ($('ssfieldset').inputElement == undefined) {
        var input = document.createElement('input');
        input.setAttribute('id', 'ssreference');
        input.setAttribute('type', 'text');
        input.style.width = '300px';
        $('ssfieldset').inputElement = input;
        return input;
    } else {
        var input = $('ssfieldset').inputElement;
        return input;
    }
}

function createReference(type) {
    currentType = type;
    if (type == 'url') {
        var inputElement  = createInputElement();
        
        var divsOfFieldSet = $('ssfieldset').getElementsByTagName('div');
        divsOfFieldSet[3].replaceChild(inputElement, $('ssreference'));
    } else {
        var selectElement = createSelectElement();
        var divsOfFieldSet = $('ssfieldset').getElementsByTagName('div');
        divsOfFieldSet[3].replaceChild(selectElement, $('ssreference'));

        if(references[type]) {
            // Already
            populateReferences(references[type]);
        } else {
            SitemapAjax.callAsync('GetReferences', type);
        }
    }
}

function populateReferences(data) {
    // Empty combo
    for (i=$('ssreference').options.length-1; i>=0; i--) {
        $('ssreference').options[i] = null;
    }
    // Populate combo
    pos = 0;
    for (i in data) {
        if ((typeof data[i] == 'object' && !!data[i]) || (typeof data[i] == 'function')) {
            // nothing
        } else {
            $('ssreference').options[pos] = new Option(data[i],i);
            pos++;
        }
    }
    // Select first
    if ($('ssreference').options[0]) {
        $('ssreference').options[0].selected = true;
    }
}


function saveCurrent() {
    id = $('ssid').value;
    parent_id = $('ssparent').value;
    title = $('sstitle').value;

    shortname = $('ssshortname').value;
    var re = new RegExp ('^[a-zA-Z0-9-]+$','');
    if (!re.test(shortname)) {
        alert(shortnameError);
        $('ssshortname').focus();
        return;
    }

    type = $('sstype').value;
    reference = $('ssreference').value;
    changefreq = $('sschangefreq').value;
    priority   = $('sspriority').value;
    if (currentAction == 'NEW') {
        SitemapAjax.callAsync('NewItem', parent_id, title, shortname, 
            type, reference, changefreq, priority);
    } else {
        if (id == parent_id) {
            alert(selfParentError);
            $('ssparent').focus();
            return;
        }
        SitemapAjax.callAsync('UpdateItem', id, parent_id, title, shortname, 
            type, reference, changefreq, priority);
    }
}

function deleteCurrent() {
    SitemapAjax.callAsync('DeleteItem', currentID);
}

function newItem() {
    $('delete_button').style.display = 'none';
    if ($('ssfieldset_legend').innerHTML != newLegend) {
        editLegend = $('ssfieldset_legend').innerHTML;
        $('ssfieldset_legend').innerHTML = newLegend;
    }
    // clean form
    $('ssid').value = 0;
    $('sstitle').value = '';
    $('sstitle').focus();
    $('sstype').value = 'url';
    createReference('url');
    $('ssshortname').value = '';
    $('ssreference').value = '';
    $('ssparent').value = 0;
    currentAction = 'NEW';
}

function moveItem(direction) {
    SitemapAjax.callAsync('MoveItem', currentID, direction);
}

function pingSitemap() {
    SitemapAjax.callAsync('PingSitemap');
}

var SitemapAjax = new JawsAjax('Sitemap', SitemapCallback);
