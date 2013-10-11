/**
 * Phoo Javascript actions
 *
 * @category   Ajax
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PhooCallback = {

    importimage: function(response) {
        currentIndex++;
        ImportImages();
    },

    updatephoto: function(response) {
        showResponse(response);
    }
}

function gotoLocation(album)
{
    window.location= base_script + '&album=' + album;
}

/**
 * Function to import images from data/phoo/import
 */
function ImportImages()
{
    if (((currentIndex + 1) <= howmany) && (items[currentIndex]['image'])) {
        $('nofm').innerHTML = (currentIndex + 1) + ' / ' + howmany;
        var percent = Math.round(((currentIndex + 1) * 100) / howmany);

        $('percent').innerHTML = percent + '%';
        $('img_percent').setAttribute('style', 'width:' + percent + '%;');
        PhooAjax.callAsync('importimage', items[currentIndex]['image'], items[currentIndex]['name'], album);
    } else {
        if (currentIndex == howmany) {
            $('nofm').innerHTML = finished_message;
            $('indicator').src = ok_image;
            $('warning').fade('out');
        }
    }
}

function updatePhoto()
{
    var id             = $('image').value;
    var title          = $('title').value;
    var allow_comments = $('allow_comments').checked;
    var published      = $('published').value;
    var description    = getEditorValue('description');

    var albumsNode  = $('album-checkboxes').getElementsByTagName('input');
    var albums      = new Array();
    var albmCounter = 0;
    for(var i = 0; i < albumsNode.length; i++) {
        if (albumsNode[i].checked) {
            albums[albmCounter] = albumsNode[i].value;
            albmCounter++;
        }
    }

    PhooAjax.callAsync('updatephoto', id, title, description, allow_comments, published, albums);
}

/**
 * add a file entry
 */
function addEntry(title)
{
    num_entries++;
    id = num_entries;
    entry = '<label id="photo' + id + '_label" for="photo' + id + '">' + title + ' ' + id + ':&nbsp;</label>';
    entry += '<input type="file" name="photo' + id + '" id="photo' + id + '" title="Photo ' + id + '" /><br />';
    $('phoo_addentry' + id).innerHTML = entry + '<span id="phoo_addentry' + (id + 1) + '">' + $('phoo_addentry' + id).innerHTML + '</span>';
}

/**
 * add a group entry
 */
function saveGroup()
{
        if($('gid').value==0) {
            var response = PhooAjax.callSync('AddGroup', {'name': $('name').value, 'description': $('description').value});
            if (response[0]['type'] == 'response_notice') {
                var box = $('groups_combo');
                box.options[box.options.length] = new Option($('name').value, response[0]['id']);
                response[0]['message'] = response[0]['message']['message'];
                stopAction();
            }
            showResponse(response);
        } else {
            var box = $('groups_combo');
            var groupIndex = box.selectedIndex;
            var response = PhooAjax.callSync('EditGroup', 
                                {'id': $('gid').value, 'name': $('name').value, 'description': $('description').value});
            if (response[0]['type'] == 'response_notice') {
                box.options[groupIndex].text = $('name').value;
                stopAction();
            }
            showResponse(response);
        }

    PhooAjax.callAsync('AddGroup', {'name': $('name').value, 'description': $('description').value});
}

/**
 * Fill form with selected group data
 */
function editGroup(id) 
{
    if (id == 0) return;
    var groupInfo = PhooAjax.callSync('GetGroup', {'gid': id});
    $('gid').value    = groupInfo['id'];
    $('name').value   = groupInfo['name'].defilter();
    $('description').value = groupInfo['description'].defilter();
    $('btn_delete').style.display = 'inline';
}

/**
 * Clean the form
 */
function stopAction() 
{
    $('gid').value         = 0;
    $('name').value        = '';
    $('description').value = '';
    $('groups_combo').selectedIndex = -1;
    $('btn_delete').style.display = 'none';
}

var PhooAjax = new JawsAjax('Phoo', PhooCallback);

var num_entries = 5;

var firstFetch = true;
var currentIndex = 0;
