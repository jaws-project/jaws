/**
 * Emblems JS actions
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var EmblemsCallback = {
    UpdateEmblem: function(response) {
        EmblemsAjax.showResponse(response);
    },

    DeleteEmblem: function(response) {
        EmblemsAjax.showResponse(response);
        if (response[0]['type'] == 'response_notice') {
            $('emblems_datagrid')[0].deleteItem();                                  
            getDG();
        }
    }
}

/**
 * Updates the emblem
 */
function updateEmblem(id, el)
{
    var row = el.getParent('tr'),
        select = row.getElement('select'),
        inputs = row.getElements('input'),
        data = {
            type: select.value,
            title: inputs[0].value,
            url: inputs[1].value,
            published: inputs[2].checked
        };
    EmblemsAjax.callAsync('UpdateEmblem', [id, data]);
}

/**
 * Deletes the emblem
 */
function deleteEmblem(id)
{
    if (confirm(confirmDelete)) {
        EmblemsAjax.callAsync('DeleteEmblem', id);
    }
}

var EmblemsAjax = new JawsAjax('Emblems', EmblemsCallback);