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
    UpdateCategory: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
        }
        showResponse(response);
    }

}


/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('btn_cancel').style.display = 'none';
    $('btn_save').style.display   = 'none';
    selectedCategory  = null;
    currentAction = null;
    unselectTreeRow();
    $('category_edit').innerHTML = '';
    $('edit_area').getElementsByTagName('span')[0].innerHTML = '';
}

function listCategories(gadget, force_open)
{
    gNode = $('gadget_' + gadget);
    gFlagimage = gNode.getElementsByTagName('img')[0];
    divSubList = $('sitemap_gadget_' + gadget);
    if (divSubList.innerHTML == '') {
        var category_list = SitemapAjax.callSync('GetCategoriesList', gadget);
        if (!category_list.blank()) {
            divSubList.innerHTML = category_list;
        } else {
            divSubList.innerHTML = noCategoryExists;
        }
        gFlagimage.src = sitemapListCloseImageSrc;
    } else {
        if (force_open == null) {
            divSubList.innerHTML = '';
            gFlagimage.src = sitemapListOpenImageSrc;
        }
    }
    if (force_open == null) {
//        stopAction();
    }
}


/**
 * Edit gadget properties
 */
function editGadget(gadget)
{
    if (gadget == null) return;
    unselectTreeRow();
    if (cacheGadgetForm == null) {
        cacheGadgetForm = SitemapAjax.callSync('GetGadgetUI');
    }
    currentAction = 'Groups';
    selectedGadget = gadget;

    $('edit_area').getElementsByTagName('span')[0].innerHTML =
        editCategoryTitle + ' - ' + $('category_'+cid).getElementsByTagName('a')[0].innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_save').style.display   = 'inline';
    $('gadget_edit').innerHTML = cacheGadgetForm;

    var gadgetInfo = SitemapAjax.callSync('GetGadget', {'gname':gadget});

    if (gadgetInfo != null) {
        $('priority').value = gadgetInfo['priority'];
        $('frequency').value = gadgetInfo['frequency'];
        $('url').value = gadgetInfo['url'];
        $('status').value = gadgetInfo['status'];
    }
}

/**
 * Edit Category sitemap parameters
 */
function editCategory(element, gadget, cid)
{
    if (cid == 0) return;
    selectTreeRow(element.parentNode);
    if (cacheCategoryForm == null) {
        cacheCategoryForm = SitemapAjax.callSync('GetCategoryUI');
    }
    currentAction = 'Category';
    selectedCategory = cid;
    selectedGadget = gadget;

    $('edit_area').getElementsByTagName('span')[0].innerHTML =
        editCategoryTitle + ' - ' + $('category_'+cid).getElementsByTagName('a')[0].innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_save').style.display   = 'inline';
    $('category_edit').innerHTML = cacheCategoryForm;

    var categoryInfo = SitemapAjax.callSync('GetCategory', {'gname':gadget, 'cid':cid});

    if (categoryInfo != null) {
        $('cid').value = categoryInfo['id'];
        $('priority').value = categoryInfo['priority'];
        $('frequency').value = categoryInfo['frequency'];
        $('url').value = categoryInfo['url'];
        $('status').value = categoryInfo['status'];
    }
}

/**
 * Select Tree row
 *
 */
function selectTreeRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#eeeecc';
    selectedRow = rowElement;
}

/**
 * Unselect Tree row
 *
 */
function unselectTreeRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Saves category / changes
 */
function saveCategory()
{
        if ($('url').value.blank()) {
            alert(incompleteFields);
            return false;
        }

        cacheCategoryForm = null;
        SitemapAjax.callAsync('UpdateCategory',
            {
                'gname': selectedGadget,
                'category': selectedCategory,
                data:{
                'priority': $('priority').value,
                'frequency': $('frequency').value,
                'url': $('url').value,
                'status': $('status').value
                }
            }
        );
}


var SitemapAjax = new JawsAjax('Sitemap', SitemapCallback);

//Current gadget
var selectedGadget = null;

//Current category
var selectedCategory = null;

//Cache for saving the gadget form template
var cacheGadgetForm = null;

//Cache for saving the category form template
var cacheCategoryForm = null;

//Which row selected in Tree
var selectedRow = null;
var selectedRowColor = null;
