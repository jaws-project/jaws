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
        _('nofm').innerHTML = (currentIndex + 1) + ' / ' + howmany;
        var percent = Math.round(((currentIndex + 1) * 100) / howmany);

        _('percent').innerHTML = percent + '%';
        _('img_percent').setAttribute('style', 'width:' + percent + '%;');
        PhooAjax.callAsync('importimage', items[currentIndex]['image'], items[currentIndex]['name'], album);
    } else {
        if (currentIndex == howmany) {
            _('nofm').innerHTML = finished_message;
            _('indicator').src = ok_image;
            _('warning').fade('out');
        }
    }
}

function updatePhoto()
{
    var id             = _('image').value;
    var title          = _('title').value;
    var allow_comments = _('allow_comments').checked;
    var published      = _('published').value;
    var description    = getEditorValue('description');

    var albumsNode  = _('album-checkboxes').getElementsByTagName('input');
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
    _('phoo_addentry' + id).innerHTML = entry + '<span id="phoo_addentry' + (id + 1) + '">' + _('phoo_addentry' + id).innerHTML + '</span>';
}

var PhooAjax = new JawsAjax('Phoo', PhooCallback);

var num_entries = 5;

var firstFetch = true;
var currentIndex = 0;
