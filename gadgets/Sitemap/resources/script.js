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
    getreferences: function(response) {
        references[currentType] = response;
        populateReferences(response);
    },

    pingsitemap: function(response) {
        showResponse(response);
    },
    
    newitem: function(response) {
        if (response[0]['css'] == 'notice-message') {
            currentID = response['id'];
            getItems();
        }
        showResponse(response);
    },

    updateitem: function(response) {
        if (response[0]['css'] == 'notice-message') {
            getItems();
        }
        showResponse(response);
    },

    deleteitem: function(response) {
        if (response[0]['css'] == 'notice-message') {
            currentID = '';
            getItems();
        }
        showResponse(response);
    },

    moveitem: function(response) {
        if (response[0]['css'] == 'notice-message') {
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
            _('ssparent').options[_('ssparent').options.length] = new Option(space + data[i]['title'], data[i]['id']);
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
    var ssitems = SitemapAjax.callSync('getitems');
    // Empty parent combo
    if (_('ssparent').length>0) {
        for (i=_('ssparent').options.length-1; i>=1; i--) {
            _('ssparent').options[i] = null;
        }
    }
    _('ssitems').innerHTML = createTree(ssitems, 0);
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
    if ((!id) || (!_('ssf' + id))) return;
    // FIXME: We need to find a way to expand tree when
    // given id is not at top level...
    if (currentAction == 'NEW') {
        _('delete_button').style.display = 'inline';
    }
    currentAction = 'EDIT';
    currentID = id;
    if (previousID != currentID) {
        // populate form
        if (editLegend != '')
            _('ssfieldset_legend').innerHTML = editLegend;
        _('sstitle').focus();
        _('ssid').value = ssitems1d[id]['id'];
        _('sstitle').value = ssitems1d[id]['title'];
        _('sstype').value = ssitems1d[id]['type'];
        createReference(ssitems1d[id]['type']);
        _('ssshortname').value = ssitems1d[id]['shortname'];
    }
    for (i=_('sspriority').options.length-1; i>=1; i--) {
        if (_('sspriority').options[i].value == ssitems1d[id]['priority']) {
            _('sspriority').options[i].selected = true;
        }
    }
    for (i=_('sschangefreq').options.length-1; i>=1; i--) {
        if (_('sschangefreq').options[i].value == ssitems1d[id]['changefreq']) {
            _('sschangefreq').options[i].selected = true;
        }
    }
    
    _('ssreference').value = ssitems1d[id]['reference'];
    _('ssparent').value = ssitems1d[id]['parent_id'];
    _('ssf' + id).style.backgroundColor = '#fafafa';
    _('ssf' + id).style.borderColor = '#ddd';
    if ((id != previousID) && (_('ssf' + previousID))) {
        _('ssf' + previousID).style.backgroundColor = '#fff';
        _('ssf' + previousID).style.borderColor = '#fff';
    }
    if (selectedItems.inArray(id)) {
        // CLOSE
        selectedItems.splice(id,1);
        _('ssa' + id).style.backgroundImage = 'url(\'gadgets/Sitemap/images/folder.png\')';
        if (_('ssd' + id + 'childs')) {
            _('ssd' + id + 'childs').style.display = 'none';
        }
    } else {
        // OPEN
        selectedItems[id] = id;
        if (_('ssd' + id + 'childs')) {
            _('ssa' + id).style.backgroundImage = 'url(\'gadgets/Sitemap/images/folder-open.png\')';
            _('ssd' + id + 'childs').style.display = 'block';
        }
    }
    previousID = id;
}

/**
 * Creates or 'copies' an existent Title combo
 */
function createSelectElement()
{
    if (_('ssfieldset').selectReference == undefined) {
        var combo = document.createElement('select');
        combo.setAttribute('id', 'ssreference');
        combo.style.width = '300px';
        _('ssfieldset').selectReference = combo;
        return combo;
    } else {
        var combo = _('ssfieldset').selectReference;
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
    if (_('ssfieldset').inputElement == undefined) {
        var input = document.createElement('input');
        input.setAttribute('id', 'ssreference');
        input.setAttribute('type', 'text');
        input.style.width = '300px';
        _('ssfieldset').inputElement = input;
        return input;
    } else {
        var input = _('ssfieldset').inputElement;
        return input;
    }
}

function createReference(type) {
    currentType = type;
    if (type == 'url') {
        var inputElement  = createInputElement();
        
        var divsOfFieldSet = _('ssfieldset').getElementsByTagName('div');
        divsOfFieldSet[3].replaceChild(inputElement, _('ssreference'));
    } else {
        var selectElement = createSelectElement();
        var divsOfFieldSet = _('ssfieldset').getElementsByTagName('div');
        divsOfFieldSet[3].replaceChild(selectElement, _('ssreference'));

        if(references[type]) {
            // Already
            populateReferences(references[type]);
        } else {
            SitemapAjax.callAsync('getreferences', type);
        }
    }
}

function populateReferences(data) {
    // Empty combo
    for (i=_('ssreference').options.length-1; i>=0; i--) {
        _('ssreference').options[i] = null;
    }
    // Populate combo
    pos = 0;
    for (i in data) {
        if ((typeof data[i] == 'object' && !!data[i]) || (typeof data[i] == 'function')) {
            // nothing
        } else {
            _('ssreference').options[pos] = new Option(data[i],i);
            pos++;
        }
    }
    // Select first
    if (_('ssreference').options[0]) {
        _('ssreference').options[0].selected = true;
    }
}


function saveCurrent() {
    id = _('ssid').value;
    parent_id = _('ssparent').value;
    title = _('sstitle').value;

    shortname = _('ssshortname').value;
    var re = new RegExp ('^[a-zA-Z0-9-]+$','');
    if (!re.test(shortname)) {
        alert(shortnameError);
        _('ssshortname').focus();
        return;
    }

    type = _('sstype').value;
    reference = _('ssreference').value;
    changefreq = _('sschangefreq').value;
    priority   = _('sspriority').value;
    if (currentAction == 'NEW') {
        SitemapAjax.callAsync('newitem', parent_id, title, shortname, 
            type, reference, changefreq, priority);
    } else {
        if (id == parent_id) {
            alert(selfParentError);
            _('ssparent').focus();
            return;
        }
        SitemapAjax.callAsync('updateitem', id, parent_id, title, shortname, 
            type, reference, changefreq, priority);
    }
}

function deleteCurrent() {
    SitemapAjax.callAsync('deleteitem', currentID);
}

function newItem() {
    _('delete_button').style.display = 'none';
    if (_('ssfieldset_legend').innerHTML != newLegend) {
        editLegend = _('ssfieldset_legend').innerHTML;
        _('ssfieldset_legend').innerHTML = newLegend;
    }
    // clean form
    _('ssid').value = 0;
    _('sstitle').value = '';
    _('sstitle').focus();
    _('sstype').value = 'url';
    createReference('url');
    _('ssshortname').value = '';
    _('ssreference').value = '';
    _('ssparent').value = 0;
    currentAction = 'NEW';
}

function moveItem(direction) {
    SitemapAjax.callAsync('moveitem', currentID, direction);
}

function pingSitemap() {
    SitemapAjax.callAsync('pingsitemap');
}

var SitemapAjax = new JawsAjax('Sitemap', SitemapCallback);
