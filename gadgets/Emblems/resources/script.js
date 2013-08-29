/**
 * Emblems JS actions
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var EmblemsCallback = {
    updateemblem: function(response) {
        showResponse(response);
    },

    deleteemblem: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            _('emblems_datagrid').deleteItem();                                  
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
    EmblemsAjax.callAsync('updateemblem', id, data);
}

/**
 * Deletes the emblem
 */
function deleteEmblem(id)
{
    if (confirm(confirmDelete)) {
        EmblemsAjax.callAsync('deleteemblem', id);
    }
}

var EmblemsAjax = new JawsAjax('Emblems', EmblemsCallback);