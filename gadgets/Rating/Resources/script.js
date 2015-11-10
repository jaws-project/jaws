/**
 * Rating Javascript actions
 *
 * @category    Ajax
 * @package     Rating
 */

/**
 *
 */
function postRating(gadget, action, reference, item, rate)
{
    RatingAjax.callAsync('PostRating', {
        'requested_gadget': gadget,
        'requested_action': action,
        'reference'       : reference,
        'item'            : item,
        'rate'            : rate
    });
}

var RatingAjax = new JawsAjax('Rating', false, 'index.php');
