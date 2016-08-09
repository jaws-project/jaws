/**
 * Weather Javascript actions
 *
 * @category    Ajax
 * @package     Logs
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Use async mode, create Callback
 */
var WeatherCallback = {
    InsertMailbox: function (response) {
        if (response['type'] == 'response_notice') {
            w2popup.close();
            w2ui['regions-grid'].reload();
        }
        WeatherAjax.showResponse(response);
    },

    UpdateMailbox: function (response) {
        if (response['type'] == 'response_notice') {
            w2popup.close();
            w2ui['regions-grid'].reload();
        }
        WeatherAjax.showResponse(response);
    },

    DomainSendMail: function (response) {
        if (response['type'] == 'response_notice') {
            w2popup.close();
        }
        WeatherAjax.showResponse(response);
    },

}

/**
 * Deletes a domain
 */
function deleteDomain()
{
    WeatherAjax.callAsync(
        'DeleteDomain', {
            'did': $('#did').val(),
        }
    );
}

/**
 * Mailboxes
 */
function mailboxes()
{
    w2ui['regions-grid'].request('get');
}

/**
 * Edit a region
 */
function editRegion(mbox)
{
    $('#w2ui-popup #mid').val(0);
    $('#region_workarea').w2popup({
        title: lbl_edit,
        modal: true,
        width: 420,
        height: 400,
        onOpen: function(event) {
            event.onComplete = function() {
                if (mbox) {
                    $('#w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').each(
                        function() {
                            $(this).val(mbox[$(this).attr('name')]);
                        }
                    );
                }
            };
        },
    });
}

/**
 * Mailboxes mail form
 */
function mailer()
{
    $('#mbox_mail_form').w2popup({
        title: mailboxes_mailer_title,
        modal: true,
        width: 620,
        height: 450,
        onOpen: function(event) {
            event.onComplete = function() {
            };
        },
    });
}

/**
 * Update a mailbox
 */
function updateMailbox()
{
    if ($('#w2ui-popup #mid').val() != 0) {
        WeatherAjax.callAsync(
            'UpdateMailbox',
            $.unserialize(
                $('#w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').serialize()
            )
        );
    } else {
        WeatherAjax.callAsync(
            'InsertMailbox', 
            $.unserialize(
                $('#did, #w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').serialize()
            )
        );
    }
}


/**
 * stop Action
 */
function stopAction()
{
    $('#deleteGroup').hide();
    $('#deleteDomain').hide();
    $('form[name="groupData"]')[0].reset();
    $('form[name="domainData"]')[0].reset();
    w2ui['mm_sidebar'].unselect('group_' + $('#gid').val());
    w2ui['mm_sidebar'].unselect('domain_' + $('#did').val());
    w2ui['mm_tabs'].enable('tabGroup');
    w2ui['mm_tabs'].click('tabGroup');
    w2ui['mm_tabs'].disable('tabDomain');
    w2ui['mm_tabs'].disable('tabMailboxes');
    // form.reset() method doesn't reset hidden inputs so we must manually reset them
    $('#did').val(0);
}

/**
 * Initiates gadget
 */
$(document).ready(function() {
    // set w2ui default configuration
    w2utils.settings.dataType = 'JSON';
    // load Persian translation
    w2utils.locale('libraries/w2ui/fa-pe.json');

    // initial regions datagrid
    $('#regions-grid').w2grid({
        name: 'regions-grid',
        method: 'POST',
        url: {
            get    : WeatherAjax.baseURL + 'GetUserRegions',
            remove : WeatherAjax.baseURL + 'DeleteUserRegions'
        },
        show: {
            toolbar: true,
            footer: true,
            selectColumn: true,
            toolbarAdd: true,
            toolbarDelete: true,
            toolbarSave: true,
            toolbarEdit: true
        },
        recid: 'id',
        columns: [
            { field: 'title',     caption: lbl_title,  size: '30%' },
        ],
        records: [],
        onRequest: function(event) {
            switch (event.postData.cmd) {
                case 'get':
                    break;

                case 'delete':
                    event.postData = {
                        'ids':  event.postData.selected,
                    };
                    break;

                case 'save':
                    break;

            }

        },
        onLoad: function(event) {
            event.xhr.responseText = eval('(' + event.xhr.responseText + ')');
            if (event.xhr.responseText.type != 'response_notice') {
                event.xhr.responseText.message = event.xhr.responseText.text;
                event.xhr.responseText.status = "error";
            } else {
                event.xhr.responseText = event.xhr.responseText.data;
            }
        },
        onDelete: function(event) {
            if (event.xhr) {
                event.xhr.responseText = eval('(' + event.xhr.responseText + ')');
                if (event.xhr.responseText.type != 'response_notice') {
                    event.xhr.responseText.message = event.xhr.responseText.text;
                    event.xhr.responseText.status = "error";
                } else {
                    event.xhr.responseText = event.xhr.responseText.data;
                }
            }
        },
        onDblClick: function(event) {
            editRegion(this.get(event.recid))
        },
        onSelect: function(event) {
            $('#deleteMailbox').show();
            $('#domainSendMail').show();
        },
        onUnselect: function(event) {
            event.onComplete = function() {
                if (!this.getSelection().length) {
                    $('#deleteMailbox').hide();
                    $('#domainSendMail').hide();
                }
            }
        },
    });
});

var domain_owners,
    MailboxForm = null,
    selectedAction = null,
    WeatherAjax = new JawsAjax('Weather', WeatherCallback);
