/**
 * TMS (Theme Management System) Javascript actions
 *
 * @category   Ajax
 * @package    Tms
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright   2007-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
function Jaws_Gadget_Tms() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var TmsCallback = {
    sharetheme: function(response) {
        var optionSelected = $('#themes_combo').find('option:selected');
        if (response['type'] == 'alert-success') {
            optionSelected.addClass('isshared');
            $('#unshare_button').css('display', 'block');
            $('#share_button').css('display', 'none');
        } else {
            optionSelected.addClass('isnotshared');
            $('#unshare_button').css('display', 'none');
            $('#share_button').css('display', 'block');
        }
    },

    unsharetheme: function(response) {
        var optionSelected = $('#themes_combo').find('option:selected');
        if (response['type'] == 'alert-success') {
            optionSelected.addClass('isnotshared');
            $('#unshare_button').css('display', 'none');
            $('#share_button').css('display', 'block');
        } else {
            optionSelected.addClass('isshared');
            $('#unshare_button').css('display', 'block');
            $('#share_button').css('display', 'none');
        }
    },

    installtheme: function(response) {
        if (response['type'] == 'alert-success') {
            $('#themes_combo').val(selectedTheme);
            editTheme(selectedTheme);
        }
    },

    uninstalltheme: function(response) {
        if (response['type'] == 'alert-success') {
        }
    },

    newrepository: function(response) {
        if (response['type'] == 'alert-success') {
            $('#repositories_datagrid')[0].addItem();
            $('#repositories_datagrid')[0].setCurrentPage(0);
        }
        getDG();
    },

    deleterepository: function(response) {
        if (response['type'] == 'alert-success') {
            $('#repositories_datagrid')[0].deleteItem();
        }
        getDG();
    },

    getrepository: function(response) {
        updateForm(response);
    },

    updaterepository: function(response) {
        getDG();
    },

    DeleteTheme: function(response) {
        if (response['type'] == 'alert-success') {
            $('#themes_combo').find('option:selected').remove();
            cleanWorkingArea(true);
        }
    },

    savesettings: function(response) {
        //
    }
};

/**
 * Show the buttons depending on the current tab and
 * the items to show
 */
function showButtons()
{
    if ($('#download').val() == 'true') {
        $('#download_button').css('display', 'inline');
    } else {
        $('#download_button').css('display', 'none');
    }
    if ($('#delete').val() == 'true') {
        $('#delete_button').css('display', 'inline');
    } else {
        $('#delete_button').css('display', 'none');
    }
}

/**
 * Edits a theme showing basic info about it
 */
function editTheme(theme)
{
    if (theme == '') {
        return false;
    }

    cleanWorkingArea(true);

    var themeInfo = TmsAjax.call('GetThemeInfo', theme, false, {'async': false});
    if (themeInfo == null) {
        return false; //Check
    }
    selectedTheme = theme;
    $('#theme_area').html(themeInfo);
    showButtons();
}

/**
 * Delete selected theme
 */
function deleteTheme()
{
    if (selectedTheme == '') {
        return false;
    }
    if (!confirm(Jaws.gadgets.Tms.defines.confirmDeleteTheme)) {
        return false;
    }

    TmsAjax.call('DeleteTheme', selectedTheme);
}

/**
 * Clean the working area
 */
function cleanWorkingArea(hideButtons)
{
    $('#theme_area').empty();
    if (hideButtons != undefined) {
        if (hideButtons == true) {
            var buttons = ['uninstall_button', 'share_button', 'unshare_button', 'install_button'];
            for (var i=0; i<buttons.length; i++) {
                if ($('#' + buttons[i]) != undefined) {
                    $('#' + buttons[i]).css('display', 'none');
                }
            }
        }
    }
}

/**
 * Download theme
 */
function downloadTheme()
{
    window.location= TmsAjax.baseScript + '?reqGadget=Tms&reqAction=DownloadTheme&theme=' + selectedTheme;
}

function uploadTheme()
{
    document.theme_upload_form.submit();
}

/**
 * Cleans the form
 */
function cleanForm(form)
{
    form['name'].value   = '';
    form['url'].value    = 'http://';
    form['id'].value     = '';
    form['action'].value = 'AddRepository';
}

/**
 * Updates form with new values
 */
function updateForm(repositoryInfo)
{
    var form = $('#repositories_form');
    form['name'].value   = repositoryInfo['name'];
    form['url'].value    = repositoryInfo['url'];
    form['id'].value     = repositoryInfo['id'];
    form['action'].value = 'UpdateRepository';
}

/**
 * Add a repository
 */
function addRepository(form)
{
    var name = form['name'].value,
        url  = form['url'].value;

    TmsAjax.call('newrepository', [name, url]);
    cleanForm(form);
}

/**
 * Updates a repository
 */
function updateRepository(form)
{
    var name = form['name'].value,
        url  = form['url'].value,
        id   = form['id'].value;

    TmsAjax.call('updaterepository', [id, name, url]);
    cleanForm(form);
}

/**
 * Submit the
 */
function submitForm(form)
{
    if (form['action'].value == 'UpdateRepository') {
        updateRepository(form);
    } else {
        addRepository(form);
    }
}

/**
 * Deletes a repository
 */
function deleteRepository(id)
{
    TmsAjax.call('deleterepository', id);
    cleanForm($('#repositories_form')[0]);
}

/**
 * Edits a repository
 */
function editRepository(id)
{
    TmsAjax.call('getrepository', id);
}

/**
 * Saves settings
 */
function saveSettings()
{
    TmsAjax.call('savesettings', $('#share_themes').val());
}

$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'Themes':
            $('#themes_combo').prop('selectedIndex', -1);
            break;

    }
});

var TmsAjax = new JawsAjax('Tms', TmsCallback),
    selectedTheme = null,
    evenColor = '#fff',
    oddColor  = '#edf3fe';
