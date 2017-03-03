/**
 * Notification Javascript actions
 *
 * @category    Ajax
 * @package     Notification
 */
/**
 * Use async mode, create Callback
 */
var NotificationCallback = {
    InstallNotificationDriver: function(response) {
        if (response[0]['type'] == 'alert-success') {
            getDG('notification_drivers_datagrid', $('#notification_drivers_datagrid')[0].getCurrentPage(), true);
            stopAction();
        }
        NotificationAjax.showResponse(response);

    },
    UninstallNotificationDriver: function(response) {
        if (response[0]['type'] == 'alert-success') {
            getDG('notification_drivers_datagrid', $('#notification_drivers_datagrid')[0].getCurrentPage(), true);
            stopAction();
        }
        NotificationAjax.showResponse(response);
    },
    UpdateNotificationDriver: function(response) {
        if (response[0]['type'] == 'alert-success') {
            getDG('notification_drivers_datagrid', $('#notification_drivers_datagrid')[0].getCurrentPage(), true);
            stopAction();
        }
        NotificationAjax.showResponse(response);
    },
    SaveSettings: function(response) {
        NotificationAjax.showResponse(response);
    }
};

/**
 * Clears the form
 */
function stopAction()
{
    switch (currentAction) {
        case "NotificationDrivers":
            selectedDriver = null;
            unselectGridRow('notification_drivers_datagrid');
            $('#driver_settings_ui').hide();
            $('#title').val('');
            $('#enabled').val(1);
            $('#title').focus();
            break;
    }
}

/**
 * Get product exports items (invoice items)
 *
 */
function getNotificationDrivers(name, offset, reset) {
    var result = NotificationAjax.callSync('GetNotificationDrivers');
    resetGrid(name, result);
}

/**
 * Install a notification driver
 */
function installDriver(rowElement, dName)
{
    selectGridRow('notification_drivers_datagrid', rowElement.parentNode.parentNode);
    NotificationAjax.callAsync('InstallNotificationDriver', {'driver': dName});
}

/**
 * Uninstall a notification driver
 */
function uninstallDriver(rowElement, dName)
{
    selectGridRow('notification_drivers_datagrid', rowElement.parentNode.parentNode);
    NotificationAjax.callAsync('UninstallNotificationDriver', {'driver': dName});
}

/**
 * Edits a notification driver
 */
function editNotificationDriver(rowElement, id) {
    $('#driver_settings_ui').show();

    selectGridRow('notification_drivers_datagrid', rowElement.parentNode.parentNode);
    selectedDriver = id;

    var driver = NotificationAjax.callSync('GetNotificationDriver', {'id': id});
    $('#title').val(driver['title'].defilter());
    $('#enabled').val(driver['enabled'] ? 1 : 0);

    var settingsUI = NotificationAjax.callSync('GetNotificationDriverSettingsUI', {'id': id});
    $('#driver_settings_area').html(settingsUI);
}


/**
 * Updates the notification driver
 */
function updateNotificationDriver() {
    if ($('#title').val().blank() || selectedDriver == null) {
        alert(jaws.Notification.Defines.incompleteFields);
        return;
    }

    var data = $.unserialize($('#driver_form').serialize());
    var settings = $.unserialize($('#driver_settings_ui').serialize());
    NotificationAjax.callAsync('UpdateNotificationDriver', {'id': selectedDriver, 'data': data, 'settings': settings})
}

/**
 * save gadget settings
 */
function saveSettings(form) {
    NotificationAjax.callAsync(
        'SaveSettings',
        {
            'gadgets_drivers': $.unserialize($('#gadgets_drivers select').serialize())
        }
    );
}

$(document).ready(function() {
    switch (jaws.core.mainAction) {
        case 'NotificationDrivers':
            currentAction = 'NotificationDrivers';
            initDataGrid('notification_drivers_datagrid', NotificationAjax, getNotificationDrivers);
            break;

        case 'Settings':
            break;
    }
});

var NotificationAjax = new JawsAjax('Notification', NotificationCallback);
var selectedDriver = null,
    currentAction;
