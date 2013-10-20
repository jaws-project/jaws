/**
 * Logs Javascript actions
 *
 * @category   Ajax
 * @package    Logs
 * @author     HamidReza Aboutalebi <hamid@aboutalebi.com>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var LogsCallback = {

}

/**
 * Get logs
 *
 */
function getLogs(name, offset, reset)
{
    var result = LogsAjax.callSync('GetLogs', {'offset':offset});
    if (reset) {
        $(name).setCurrentPage(0);
        var total = LogsAjax.callSync('GetLogsCount');
    }
    resetGrid(name, result, total);
}

/**
 * Get selected log info
 *
 */
function viewLog(logID)
{
    var result = LogsAjax.callSync('GetLogInfo', {'logID':logID});
    $('log_user').innerHTML = result['username'];
    $('log_gadget').innerHTML = result['gadget'];
}

var LogsAjax = new JawsAjax('Logs', LogsCallback);
LogsAjax.backwardSupport();
cacheContactForm = null;