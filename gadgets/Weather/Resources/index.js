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
    InsertRegion: function (response) {
        if (response['type'] == 'alert-success') {
            $('#regions-grid').repeater('render');
            stopAction();
        }
        WeatherAjax.showResponse(response);
    },
    UpdateRegion: function (response) {
        if (response['type'] == 'alert-success') {
            $('#regions-grid').repeater('render');
            stopAction();
        }
        WeatherAjax.showResponse(response);
    },
    DeleteUserRegion: function (response) {
        if (response['type'] == 'alert-success') {
            $('#regions-grid').repeater('render');
            stopAction();
        }
        WeatherAjax.showResponse(response);
    }
}

/**
 * Edit a region
 */
function editRegion(id)
{
    selectedRegion = id;
    WeatherAjax.callSync('GetRegion', {'id': selectedRegion}, function (geoPos) {
        if (geoPos) {
            $('form#region #title').val(geoPos['title'].defilter());
            $('form#region #fast_url').val(geoPos['fast_url']);
            $('form#region #latitude').val(geoPos['latitude']);
            $('form#region #longitude').val(geoPos['longitude']);
            $('form#region #published').val(geoPos['published'] ? 1 : 0);
            setGoogleMapImage();

            $('#regionModalLabel').html(jaws.Weather.Defines.lbl_geo_position);
            $('#regionModal').modal('show');
        }
    });
}

/**
 * Delete a region
 */
function deleteRegion(id)
{
    if (confirm(jaws.Weather.Defines.confirmDelete)) {
        WeatherAjax.callAsync('DeleteUserRegion', {'id': id});
    }
}

/**
 * Show user's current location
 */

function showMyLocation() {
    if (!navigator.geolocation) {
        return false;
    }

    function success(position) {
        $('#latitude').val(position.coords.latitude);
        $('#longitude').val(position.coords.longitude);
        ZoomLevel = 10;
        setGoogleMapImage();
    };

    function error() {
        return false;
    };

    navigator.geolocation.getCurrentPosition(success, error);
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
function setGoogleMapImage() {
    $('#gmap').prop('src', jaws.Weather.Defines.base_script + '?gadget=Weather&action=GetGoogleMapImage' +
        '&latitude=' + $('#latitude').val() + '&longitude=' + $('#longitude').val() +
        '&zoom=' + ZoomLevel + '&size=' + ImageSize);
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
 * Update a region
 */
function updateRegion() {
    if (selectedRegion != null) {
        WeatherAjax.callAsync(
            'UpdateRegion', {
                'data': $.unserialize(
                    $('form#region input,form#region select,form#region textarea').serialize()
                ),
                'id': selectedRegion
            }
        );
    } else {
        WeatherAjax.callAsync(
            'InsertRegion', {
                'data': $.unserialize(
                    $('#did, form#region input,form#region select,form#region textarea').serialize()
                )
            }
        );
    }
}


/**
 * stop Action
 */
function stopAction()
{
    selectedRegion = null;
    $('#regionModal').modal('hide');
    $('form#region')[0].reset();
}


// Define the data to be displayed in the repeater.
function regionsDataSource(options, callback) {

    // define the columns for the grid
    var columns = [
        {
            'label': jaws.Weather.Defines.lbl_title,
            'property': 'title',
            'sortable': true
        },
        {
            'label': jaws.Weather.Defines.lbl_published,
            'property': 'published',
            'sortable': true
        }
    ];

    // set options
    var pageIndex = options.pageIndex;
    var pageSize = options.pageSize;
    var options = {
        'pageIndex': pageIndex,
        'pageSize': pageSize,
        'sortDirection': options.sortDirection,
        'sortBy': options.sortProperty,
        'filterBy': options.filter.value || '',
        'searchBy': options.search || ''
    };

    WeatherAjax.callAsync('GetUserRegions', options, function (response) {
        if (response.type == 'alert-success') {
            var items = response.data.records;
            var totalItems = response.data.total;
            var totalPages = Math.ceil(totalItems / pageSize);
            var startIndex = (pageIndex * pageSize) + 1;
            var endIndex = (startIndex + pageSize) - 1;

            if (endIndex > items.length) {
                endIndex = items.length;
            }

            // configure datasource
            var dataSource = {
                'page': pageIndex,
                'pages': totalPages,
                'count': totalItems,
                'start': startIndex,
                'end': endIndex,
                'columns': columns,
                'items': items
            };

            // pass the datasource back to the repeater
            callback(dataSource);
        } else {
            WeatherAjax.showResponse(response);
        }
    });
}

/**
 * initiate regions datagrid
 */
function initiateRegionsDG() {

    var list_actions = {
        width: 50,
        items: [
            {
                name: 'edit',
                html: '<span class="glyphicon glyphicon-pencil"></span> ' + jaws.Weather.Defines.lbl_edit,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    editRegion(helpers.rowData.id);
                    callback();
                }

            },
            {
                name: 'delete',
                html: '<span class="glyphicon glyphicon-trash"></span> ' + jaws.Weather.Defines.lbl_delete ,
                clickAction: function (helpers, callback, e) {
                    e.preventDefault();
                    deleteRegion(helpers.rowData.id);
                    callback();
                }
            }
        ]
    };

    $('#regions-grid').repeater({
        dataSource: regionsDataSource,
        staticHeight: 600,
        list_actions: list_actions,
        list_direction: $('.repeater-canvas').css('direction')
    });

    $('#regionModal').on('show.bs.modal', function (e) {
        showMyLocation();
    });

    $('#regionModal').on('hidden.bs.modal', function (e) {
        stopAction();
    });
}

/**
 * Initiates gadget
 */
$(document).ready(function() {
    switch (jaws.Defines.mainAction) {
        case 'UserRegionsList':
            initiateRegionsDG();
            break;
    }
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

var ZoomLevel = 1,
    selectedRegion = null,
    WeatherAjax = new JawsAjax('Weather', WeatherCallback);