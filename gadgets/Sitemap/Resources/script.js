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

    $('edit_area').getElementsByTagName('span')[0].innerHTML =
        editCategoryTitle + ' - ' + $('category_'+cid).getElementsByTagName('a')[0].innerHTML;
    $('btn_cancel').style.display = 'inline';
    $('btn_save').style.display   = 'inline';
    $('category_edit').innerHTML = cacheCategoryForm;

    var categoryInfo = SitemapAjax.callSync('GetCategory', {'gname':gadget, 'cid':cid});

    if (categoryInfo != null) {
        $('cid').value = categoryInfo['id'];
//    $('gid').value         = categoryInfo['gid'];
        $('title').value = categoryInfo['title'].defilter();
        $('url').value = categoryInfo['url'];
        $('fast_url').value = categoryInfo['fast_url'];
        $('description').value = categoryInfo['description'].defilter();
        if ($('tags') != null) {
            $('tags').value = categoryInfo['tags'];
        }
        $('clicks').value = categoryInfo['clicks'];
        setRanksCombo($('gid').value);
        $('rank').value = categoryInfo['rank'];
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

var SitemapAjax = new JawsAjax('Sitemap', SitemapCallback);

//Current gadget
var selectedGadget = null;

//Current category
var selectedCategory = null;

//Cache for saving the category form template
var cacheCategoryForm = null;

//Which row selected in Tree
var selectedRow = null;
var selectedRowColor = null;
