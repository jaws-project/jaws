/**
 * Emblems JS actions
 *
 * @category   Ajax
 * @package    Emblems
 * @author     Amir Mohammad Saied <amirsaied@gmail.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var EmblemsCallback = {
    updateproperties: function(response) {
        showResponse(response)
    },

    deleteemblem: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            $('emblems_datagrid').deleteItem();                                  
            getDG();
        }
    },

    updateemblem: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            getDG();
        }
    }
}

/**
 * Update the emblems properties
 */
function updateProperties(form)
{
    var rows = form.elements['rows_combo'].value;
    var allow_url = 'true';
    if (form.elements['allow_url_false'].checked) {
        allow_url = 'false';
    }

    EmblemsAjax.callAsync('updateproperties', rows, allow_url);
}

/**
 * Delete an emblem
 */
function deleteEmblem(id, msg)
{
    if(confirm(msg)){
        EmblemsAjax.callAsync('deleteemblem', id);
    }
}

/**
 * Updates an emblem
 */
function editEmblem(id)
{
    var form   = $('emblemsForm');
    var title  = form.elements['title' + id].value;
    var url    = form.elements['url' + id].value;
    var type   = form.elements['type' + id].value;
    var status = form.elements['status' + id].value;
    
    EmblemsAjax.callAsync('updateemblem', id, title, url, type, status);
}

var EmblemsAjax = new JawsAjax('Emblems', EmblemsCallback);