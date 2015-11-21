/**
 * Rating Javascript actions
 *
 * @category    Ajax
 * @package     Rating
 */

/**
 *
 */
function postRating(data, rate)
{
    RatingAjax.callAsync('PostRating', {
        'requested_gadget': data.gadget,
        'requested_action': data.action,
        'reference'       : data.reference,
        'item'            : (data.item? data.item : 0),
        'rate'            : rate
    });
}

$(document).ready(function() {
    $('[rel="rating-rating"]').children('input[type="radio"]').on('change', function() {
        postRating($(this).parent().data(), $(this).val());
    });

    $('[rel="rating-like"]').children('input[type="checkbox"]').on('change', function() {
        postRating($(this).parent().data(), $(this).prop('checked')? 1 : null);
    });
});

var RatingAjax = new JawsAjax('Rating', false, 'index.php');
