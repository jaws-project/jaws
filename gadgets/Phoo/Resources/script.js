/**
 * Phoo Javascript actions
 *
 * @category   Ajax
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PhooCallback = {

    ImportImage: function(response) {
        currentIndex++;
        ImportImages();
    },

    UpdatePhoto: function(response) {
        PhooAjax.showResponse(response);
    }
}

function gotoLocation(album)
{
    window.location= jaws.gadgets.Phoo.base_script + '?gadget=Phoo&album=' + album;
}

/**
 * Function to import images from data/phoo/import
 */
function ImportImages()
{
    if (((currentIndex + 1) <= howmany) && (items[currentIndex]['image'])) {
        $('#nofm').html((currentIndex + 1) + ' / ' + howmany);
        var percent = Math.round(((currentIndex + 1) * 100) / howmany);

        $('#percent').html(percent + '%');
        $('#img_percent').css('width', percent + '%');
        PhooAjax.callAsync(
            'ImportImage', [
                items[currentIndex]['image'],
                items[currentIndex]['name'], album
            ]
        );
    } else {
        if (currentIndex == howmany) {
            $('#nofm').html(finished_message);
            $('#indicator').attr('src', ok_image);
            $('#warning').hide('slow');
        }
    }
}

function updatePhoto()
{
    var id             = $('#image').val();
    var title          = $('#title').val();
    var allow_comments = $('#allow_comments').prop('checked');
    var published      = $('#published').val();
    var description    = getEditorValue('#description');

    var albumsNode  = $('#album-checkboxes input');
    var albums      = new Array();
    var albmCounter = 0;
    for(var i = 0; i < albumsNode.length; i++) {
        if (albumsNode[i].checked) {
            albums[albmCounter] = albumsNode[i].value;
            albmCounter++;
        }
    }

    PhooAjax.callAsync(
        'UpdatePhoto',
        [id, title, description, allow_comments, published, albums]
    );
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
    $('#phoo_addentry' + id).html(entry + '<span id="phoo_addentry' + (id + 1) + '">' + $('#phoo_addentry' + id).html() + '</span>');
}

/**
 * add a group entry
 */
function saveGroup()
{
    if (!$('#name').val()) {
        alert(jaws.gadgets.Phoo.incompleteGroupFields);
        return false;
    }
    var groupData = {
        'name': $('#name').val(),
        'fast_url': $('#fast_url').val(),
        'meta_keywords': $('#meta_keywords').val(),
        'meta_description': $('#meta_description').val(),
        'description': $('#description').val()
    };

    if ($('#gid').val() == 0) {
        var response = PhooAjax.callSync('AddGroup', groupData);
        if (response[0]['type'] == 'alert-success') {
            $('#groups_combo')
                .append($("<option></option>")
                    .attr("value",response[0]['text']['id'])
                    .text($('#name').val()));
            response[0]['text'] = response[0]['text']['message'];
            stopAction();
        }
        PhooAjax.showResponse(response);
    } else {
        var response = PhooAjax.callSync('UpdateGroup',
                            {'id': $('#gid').val(), data: groupData});
        if (response[0]['type'] == 'alert-success') {
            $('#groups_combo').find('option:selected').text($('#name').val());
            stopAction();
        }
        PhooAjax.showResponse(response);
    }
}

/**
 * Fill form with selected group data
 */
function editGroup(id) 
{
    if (id == 0) return;
    var groupInfo = PhooAjax.callSync('GetGroup', {'gid': id});
    $('#gid').val(groupInfo['id']);
    $('#name').val(groupInfo['name'].defilter());
    $('#fast_url').val(groupInfo['fast_url']);
    $('#meta_keywords').val(groupInfo['meta_keywords'].defilter());
    $('#meta_description').val(groupInfo['meta_description'].defilter());
    $('#description').val(groupInfo['description'].defilter());
    $('#btn_delete').css('display', 'inline');
    $('#legend_title').html(jaws.gadgets.Phoo.editGroupTitle);
}

/**
 * Delete group
 */
function deleteGroup()
{
    var answer = confirm(jaws.gadgets.Phoo.confirmGroupDelete);
    if (answer) {
        var box = $('#groups_combo');
        var quoteIndex = box.selectedIndex;
        var response = PhooAjax.callSync('DeleteGroup', {'id': $('#gid').val()});
        if (response[0]['type'] == 'alert-success') {
            $('#groups_combo').find('option:selected').remove();
            stopAction();
        }
        PhooAjax.showResponse(response);
    }
}

/**
 * Clean the form
 */
function stopAction() 
{
    $('#gid').val(0);
    $('#name').val('');
    $('#fast_url').val('');
    $('#meta_keywords').val('');
    $('#meta_description').val('');
    $('#description').val('');
    $('#groups_combo').prop('selectedIndex', -1);
    $('#btn_delete').css('display', 'none');
    $('#legend_title').html(jaws.gadgets.Phoo.addGroupTitle);
}

/**
 * Filter albums combo with selected group
 */
function filterAlbums(gid)
{
    var response = PhooAjax.callSync('GetAlbums', {'gid': gid});
    var select = $('#albums_list');
    select.options.length = 0;
    for (var i=0; i<response.length; i++) {
        select.options[select.options.length] = new Option(response[i]['name'], response[i]['id']);
    }
}

function selectAllAlbums()
{
    c = document.getElementById('albums_list');
    for (i = c.options.length - 1; i >= 0; i--) {
        c.options[i].selected = true;
    }
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'Groups':
            $('#legend_title').html(jaws.gadgets.Phoo.addGroupTitle);
            break;

        case 'Properties':
            break;
    }
});

function toggleChk(el, id) {
    if (el.checked) {
        document.getElementById('entry_' + id).disabled = false;
        document.getElementById('img_' + id).setAttribute('style', 'filter:alpha(opacity=100);-moz-opacity:1;opacity:1;');
    } else {
        document.getElementById('entry_' + id).disabled = true;
        document.getElementById('img_' + id).setAttribute('style', 'filter:alpha(opacity=50);-moz-opacity:.50;opacity:.50;');
    }
}

var PhooAjax = new JawsAjax('Phoo', PhooCallback);

var num_entries = 5;

var firstFetch = true;
var currentIndex = 0;
