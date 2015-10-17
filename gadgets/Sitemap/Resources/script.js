/**
 * Sitemap JS actions
 *
 * @category   Ajax
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2006-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
var SitemapCallback = {
    UpdateCategory: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
        }
        SitemapAjax.showResponse(response);
    },
    UpdateGadgetProperties: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
        }
        SitemapAjax.showResponse(response);
    },
    SyncSitemapXML: function(response) {
        if (response[0]['type'] == 'response_notice') {
            syncSitemapDataFile(selectedGadget);
        }
        SitemapAjax.showResponse(response);
    },
    SyncSitemapData: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
        }
        SitemapAjax.showResponse(response);
    },
    PingSearchEngines: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
        }
        SitemapAjax.showResponse(response);
    },
    UpdateRobots: function(response) {
        SitemapAjax.showResponse(response);
    }
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    $('#btn_cancel').css('display', 'none');
    $('#btn_save').css('display', 'none');
    selectedGadget  = null;
    selectedCategory  = null;
    currentAction = null;
    unselectTreeRow();
    $('#category_edit').html('');
    $('#edit_area span').first().html('');
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
    currentAction = 'Gadget';
    selectedGadget = gadget;

    $('#edit_area span').first().html(editGadgetTitle + ' - ' + selectedGadget);
    $('#btn_cancel').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#category_edit').html(cacheGadgetForm);

    var gadgetInfo = SitemapAjax.callSync('GetGadget', {'gname':gadget});

    if (gadgetInfo != null) {
        $('#priority').val(gadgetInfo['priority']);
        $('#frequency').val(gadgetInfo['frequency']);
        $('#status').val(gadgetInfo['status']);
        $('last_update').html(gadgetInfo['update_time']);
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

    $('#edit_area span').first().html(editCategoryTitle + ' - ' + $('#category_'+cid+' a').first().html());
    $('#btn_cancel').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#category_edit').html(cacheCategoryForm);

    var categoryInfo = SitemapAjax.callSync('GetCategory', {'gname':gadget, 'cid':cid});

    if (categoryInfo != null) {
        $('#cid').val(categoryInfo['id']);
        $('#priority').val(categoryInfo['priority']);
        $('#frequency').val(categoryInfo['frequency']);
        $('#status').val(categoryInfo['status']);
    }
}

/**
 * Sync sitemap data files
 */
function syncSitemap(gadget)
{
    if (gadget == null) {
        return;
    }
    selectedGadget = gadget;
    SitemapAjax.callAsync('SyncSitemapXML', {'gname':gadget});
}

/**
 * Sync sitemap data(user side HTML sitemap) files
 */
function syncSitemapDataFile(gadget)
{
    SitemapAjax.callAsync('SyncSitemapData', {'gname':gadget});
}

/**
 * Ping Search Engines
 */
function pingSearchEngines()
{
    SitemapAjax.callAsync('PingSearchEngines');
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
 * Saves category|gadget changes
 */
function saveProperties() {
    if (currentAction == 'Category') {
        cacheCategoryForm = null;
        SitemapAjax.callAsync('UpdateCategory',
            {
                'gname': selectedGadget,
                'category': selectedCategory,
                data: {
                    'priority': $('#priority').val(),
                    'frequency': $('#frequency').val(),
                    'status': $('#status').val()
                }
            }
        );
    } else if(currentAction == 'Gadget') {
        cacheGadgetForm = null;
        SitemapAjax.callAsync('UpdateGadgetProperties',
            {
                'gname': selectedGadget,
                data: {
                    'priority': $('#priority').val(),
                    'frequency': $('#frequency').val(),
                    'status': $('#status').val()
                }
            }
        );
    }
}

/**
 * Update robots
 */
function updateRobots() {
    SitemapAjax.callAsync('UpdateRobots', {'robots': $('robots').value });
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

var currentAction = null;

//Which row selected in Tree
var selectedRow = null;
var selectedRowColor = null;
