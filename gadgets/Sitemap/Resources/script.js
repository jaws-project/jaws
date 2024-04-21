/**
 * Sitemap JS actions
 *
 * @category   Ajax
 * @package    Sitemap
 * @author     Jonathan Hernandez <ion@gluch.org.mx>
 * @author     ZehneZiba <zzb@zehneziba.ir>
 * @copyright   2006-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_Sitemap() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
var SitemapCallback = {
    UpdateCategory: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
        }
    },

    UpdateGadgetProperties: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
        }
    },

    SyncSitemapXML: function(response) {
        if (response['type'] == 'alert-success') {
            syncSitemapDataFile(selectedGadget);
        }
    },

    SyncSitemapData: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
        }
    },

    PingSearchEngines: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
        }
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
    gFlagimage = $('#gadget_' + gadget + ' img')[0];
    divSubList = $('#sitemap_gadget_' + gadget);
    if (divSubList.html() == '') {
        var category_list = SitemapAjax.call('GetCategoriesList', gadget, false, {'async': false});
        if (!category_list.blank()) {
            divSubList.html(category_list);
        } else {
            divSubList.html(Jaws.gadgets.Sitemap.defines.noCategoryExists);
        }
        $(gFlagimage).attr('src', Jaws.gadgets.Sitemap.defines.sitemapListCloseImageSrc);
    } else {
        if (force_open == null) {
            divSubList.html('');
            $(gFlagimage).attr('src', Jaws.gadgets.Sitemap.defines.sitemapListOpenImageSrc);
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
        cacheGadgetForm = SitemapAjax.call('GetGadgetUI', {}, false, {'async': false});
    }
    currentAction = 'Gadget';
    selectedGadget = gadget;

    $('#edit_area span').first().html(Jaws.gadgets.Sitemap.defines.editGadgetTitle + ' - ' + selectedGadget);
    $('#btn_cancel').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#category_edit').html(cacheGadgetForm);

    var gadgetInfo = SitemapAjax.call('GetGadget', {'gname':gadget}, false, {'async': false});

    if (gadgetInfo != null) {
        $('#priority').val(gadgetInfo['priority']);
        $('#frequency').val(gadgetInfo['frequency']);
        $('#status').val(gadgetInfo['status']);
        $('#last_update').html(gadgetInfo['update_time']);
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
        cacheCategoryForm = SitemapAjax.call('GetCategoryUI', {}, false, {'async': false});
    }
    currentAction = 'Category';
    selectedCategory = cid;
    selectedGadget = gadget;

    $('#edit_area span').first().html(
        Jaws.gadgets.Sitemap.defines.editCategoryTitle + ' - ' + $('#category_'+cid+' a').first().html()
    );
    $('#btn_cancel').css('display', 'inline');
    $('#btn_save').css('display', 'inline');
    $('#category_edit').html(cacheCategoryForm);

    var categoryInfo = SitemapAjax.call('GetCategory', {'gname':gadget, 'cid':cid}, false, {'async': false});

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
    SitemapAjax.call('SyncSitemapXML', {'gname':gadget});
}

/**
 * Sync sitemap data(user side HTML sitemap) files
 */
function syncSitemapDataFile(gadget)
{
    SitemapAjax.call('SyncSitemapData', {'gname':gadget});
}

/**
 * Ping Search Engines
 */
function pingSearchEngines()
{
    SitemapAjax.call('PingSearchEngines');
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
        SitemapAjax.call('UpdateCategory',
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
        SitemapAjax.call('UpdateGadgetProperties',
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
    SitemapAjax.call('UpdateRobots', {'robots': $('#robots').val() });
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
