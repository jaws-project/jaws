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
function editRegion(region)
{
    var geoPos = WeatherAjax.callSync('GetRegion', {'id': region.id});

    $('#w2ui-popup #mid').val(0);
    $('#region_workarea').w2popup({
        title: lbl_edit + ' ' +lbl_geo_position,
        modal: true,
        width: 400,
        height: 550,
        onOpen: function(event) {
            event.onComplete = function() {
                // $('#id').val(geoPos['id']);
                $('#w2ui-popup #title').val(geoPos['title'].defilter());
                $('#w2ui-popup #fast_url').val(geoPos['fast_url']);
                $('#w2ui-popup #latitude').val(geoPos['latitude']);
                $('#w2ui-popup #longitude').val(geoPos['longitude']);
                $('#w2ui-popup #published').val(geoPos['published']? 1 : 0);
                setGoogleMapImage();

                // if (mbox) {
                //     $('#w2ui-popup form input,#w2ui-popup form select,#w2ui-popup form textarea').each(
                //         function() {
                //             $(this).val(mbox[$(this).attr('name')]);
                //         }
                //     );
                // }
            };
        },
    });
}

/**
 * Calculates position of the element
 */
function findElementPosition(element)
{
    if (typeof(element.offsetParent ) != "undefined") {
        for (var posX = 0, posY = 0; element; element = element.offsetParent) {
            posX += element.offsetLeft;
            posY += element.offsetTop;
        }

        return [posX, posY];
    }

    return [element.x, element.y];
}

/**
 * Gets the geo position
 */
function GetGeoPosition(mx, my, iw, ih, clat, clng)
{
    var lng = iw/ZoomPixelsPerLonDegree[ZoomLevel];
    lng = clng + (mx*lng/iw - 0.5*lng);

    var cosRadLat = Math.cos(clat*Math.PI/180);
    var lat = ih * cosRadLat/ZoomPixelsPerLonDegree[ZoomLevel];
    lat =  clat + (0.5*lat - my*lat/ih);

    return [Math.round(lat*100)/100, Math.round(lng*100)/100];
}

/**
 * Calculates coordinates of the clicked point and returns the appropriate map
 */
function getGoogleMap(ev, element)
{
    var geoPos,
        posX = 0,
        posY = 0,
        imgPos = findElementPosition(element);

    if (!ev) {
        var ev = window.event;
    }
    if (ev.pageX || ev.pageY) {
        posX = ev.pageX;
        posY = ev.pageY;
    } else if (ev.clientX || ev.clientY) {
        posX = ev.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        posY = ev.clientY + document.body.scrollTop  + document.documentElement.scrollTop;
    }
    posX = posX - imgPos[0];
    posY = posY - imgPos[1];

    //---------------------
    geoPos = GetGeoPosition(posX,
        posY,
        element.width,
        element.height,
        parseFloat($('#latitude').val()),
        parseFloat($('#longitude').val()));
    $('#latitude').val(geoPos[0]);
    $('#longitude').val(geoPos[1]);
    setGoogleMapImage();
}

/**
 * Updates the map with new position
 */
function setGoogleMapImage()
{
    $('#gmap').prop('src', base_script + '?gadget=Weather&action=GetGoogleMapImage' +
        '&latitude=' + $('#latitude').val() + '&longitude=' + $('#longitude').val() +
        '&zoom=' + ZoomLevel + '&size='  + ImageSize);
}

/**
 * Zooms in/out on the map
 */
function zoomMap(level)
{
    ZoomLevel = ZoomLevel + level;
    if (ZoomLevel < 1) {
        ZoomLevel = 1;
    }

    if (ZoomLevel > 10) {
        ZoomLevel = 10;
    }

    setGoogleMapImage();
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

/**
 * const list taken from google api
 */
const ImageSize = '352x288',
    ZoomPixelsPerLonDegree = [
        0.7111111111111111,
        1.4222222222222223,
        2.8444444444444446,
        5.688888888888889,
        11.377777777777778,
        22.755555555555556,
        45.51111111111111,
        91.02222222222223,
        182.04444444444445,
        364.0888888888889,
        728.1777777777778,
        1456.3555555555556,
        2912.711111111111,
        5825.422222222222,
        11650.844444444445,
        23301.68888888889,
        46603.37777777778,
        93206.75555555556,
        186413.51111111112];

var ZoomLevel = 1;

var domain_owners,
    MailboxForm = null,
    selectedAction = null,
    WeatherAjax = new JawsAjax('Weather', WeatherCallback);
