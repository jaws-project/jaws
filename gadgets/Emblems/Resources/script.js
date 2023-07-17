/**
 * Emblems JS actions
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @author     Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var EmblemsCallback = {
    UpdateEmblem: function(response) {
        //
    },

    DeleteEmblem: function(response) {
        if (response['type'] == 'alert-success') {
            $('#emblems_datagrid')[0].deleteItem();
            getDG();
        }
    }
}

/**
 * Updates the emblem
 */
function updateEmblem(id, el)
{
    var row = $(el).parent().parent(),
        select = $(row).find('select'),
        inputs = $(row).find('input'),
        data = {
            type: select.val(),
            title: inputs[0].value,
            url: inputs[1].value,
            published: inputs[2].checked
        };
    EmblemsAjax.call('UpdateEmblem', [id, data]);
}

/**
 * Deletes the emblem
 */
function deleteEmblem(id)
{
    if (confirm(Jaws.gadgets.Emblems.defines.confirmDelete)) {
        EmblemsAjax.call('DeleteEmblem', id);
    }
}

$(document).ready(function() {
    initDataGrid('emblems_datagrid', EmblemsAjax);

});

var EmblemsAjax = new JawsAjax('Emblems', EmblemsCallback);