/**
 * Poll JS actions
 *
 * @category   Ajax
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PollCallback = {
    InsertPoll: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('#polls_datagrid')[0].addItem();
            $('#polls_datagrid')[0].setCurrentPage(0);
            getDG();
        }
        PollAjax.showResponse(response);
    },

    UpdatePoll: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG();
        }
        PollAjax.showResponse(response);
    },

    DeletePoll: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('#polls_datagrid')[0].deleteItem();
            getDG();
        }
        PollAjax.showResponse(response);
    },

    UpdatePollAnswers: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
        }
        PollAjax.showResponse(response);
    },

    InsertPollGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('#pollgroups_datagrid')[0].addItem();
            $('#pollgroups_datagrid')[0].setCurrentPage(0);
            getDG();
        }
        PollAjax.showResponse(response);
    },

    UpdatePollGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG();
        }
        PollAjax.showResponse(response);
    },

    DeletePollGroup: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('#pollgroups_datagrid')[0].deleteItem();
            getDG();
        }
        PollAjax.showResponse(response);
    },

    AddPollsToPollGroup: function(response) {
        stopAction();
        PollAjax.showResponse(response);
    }
};

/**
 * Select DataGrid row
 *
 */
function selectDataGridRow(rowElement)
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRowColor = rowElement.style.backgroundColor;
    rowElement.style.backgroundColor = '#ffffcc';
    selectedRow = rowElement;
}

/**
 * Deselect DataGrid row
 *
 */
function deselectDataGridRow()
{
    if (selectedRow) {
        selectedRow.style.backgroundColor = selectedRowColor;
    }
    selectedRow = null;
    selectedRowColor = null;
}

/**
 * Stops doing a certain action
 */
function stopAction()
{
    switch(currentAction) {
    case 'Polls':
        selectedPoll = null;
        $('#legend_title').html(addPoll_title);
        $('#title').val('');
        $('#gid').prop('selectedIndex', 0);
        $('#start_time').val('');
        $('#stop_time').val('');
        $('#type').val(0);
        $('#restriction').val(0);
        $('#result_view').val(1);
        $('#published').val(1);
        deselectDataGridRow();
        $('#title')[0].focus();
        break;
    case 'PollAnswers':
        selectedPoll = null;
        currentAction = 'Polls';
        $('#legend_title').html(addPoll_title);
        $('#p_work_area')[0].innerHTML = cachePollForm;
        initDatePicker('start_time');
        initDatePicker('stop_time');
        deselectDataGridRow();
        break;
    case 'PollGroups':
        selectedPollGroup = null;
        $('#legend_title').html(addPollGroup_title);
        $('#title').val('');
        $('#published').val(1);
        deselectDataGridRow();
        $('#title')[0].focus();
        break;
    case 'PollGroupPolls':
        selectedPollGroup = null;
        currentAction = 'PollGroups';
        $('#legend_title').html(addPollGroup_title);
        $('#pg_work_area').html(cachePollGroupsForm);
        deselectDataGridRow();
        break;
    }
}

/**
 * Edit Poll
 */
function editPoll(element, pid)
{
    currentAction = 'Polls';
    selectedPoll = pid;
    $('#legend_title').html(editPoll_title);
    if (cachePollForm != null) {
        $('#p_work_area')[0].innerHTML = cachePollForm;
        initDatePicker('start_time');
        initDatePicker('stop_time');
    }

    selectDataGridRow(element.parentNode.parentNode);

    var pollInfo = PollAjax.callSync('GetPoll', selectedPoll);

    $('#title').val(pollInfo['title'].defilter());
    $('#gid').val(pollInfo['group']);
    if (pollInfo['start_time'] == null) pollInfo['start_time'] = '';
    if (pollInfo['stop_time']  == null) pollInfo['stop_time']  = '';
    $('#start_time').val(pollInfo['start_time']);
    $('#stop_time').val(pollInfo['stop_time']);
    $('#type').val(pollInfo['type']);
    $('#restriction').val(pollInfo['restriction']);
    $('#result_view').val(pollInfo['result_view'] ? 1 : 0);
    $('#published').val(pollInfo['published'] ? 1 : 0);
    console.log(pollInfo);
}

/**
 * Edit Poll Answers
 */
function editPollAnswers(element, pid)
{
    if (cachePollForm == null) {
        cachePollForm = $('#p_work_area').html();
    }

    selectDataGridRow(element.parentNode.parentNode);

    if (cachePollAnswersForm == null) {
        cachePollAnswersForm = PollAjax.callSync('PollAnswersUI');
    }
    currentAction = 'PollAnswers';

    selectedPoll = pid;
    $('#legend_title').html(editAnswers_title);
    $('#p_work_area').html(cachePollAnswersForm);
    var answersData = PollAjax.callSync('GetPollAnswers', selectedPoll);
    var answers  = answersData['Answers'];
    $('#title').val(answersData['title'].defilter());

    var box = $('#answers_combo')[0];
    box.length = 0;
    for(var i = 0; i < answers.length; i++) {
        box.options[i] = new Option(answers[i]['title'].defilter(), answers[i]['id']);
    }
    $('#answer')[0].focus();
}

/**
 * Saves data / changes on the poll group's form
 */
function savePoll()
{
    if (currentAction == 'PollAnswers') {
        var box = $('#answers_combo')[0];
        var answers = [];
        if (box.length < 2) {
            alert(requiresTwoAnswers);
            return false;
        }
        for(var i = 0; i < box.length; i++) {
            answers[i] = [];
            answers[i]['id']     = box.options[i].value;
            answers[i]['title'] = box.options[i].text;
        }
        PollAjax.callAsync('UpdatePollAnswers', [selectedPoll, answers]);
    } else {
        if (!$('#title').val()) {
            alert(incompletePollsFields);
            return false;
        }

        if (selectedPoll == null) {
            PollAjax.callAsync(
                'InsertPoll', [
                    $('#title').val(),
                    $('#gid').val(),
                    $('#start_time').val(),
                    $('#stop_time').val(),
                    $('#type').val(),
                    $('#restriction').val(),
                    $('#result_view').val(),
                    $('#published').val()
                ]
            );
        } else {
            PollAjax.callAsync(
                'UpdatePoll', [
                    selectedPoll,
                    $('#title').val(),
                    $('#gid').val(),
                    $('#start_time').val(),
                    $('#stop_time').val(),
                    $('#type').val(),
                    $('#restriction').val(),
                    $('#result_view').val(),
                    $('#published').val()
                ]
            );
        }
    }
}

/**
 * Delete a poll
 */
function deletePoll(element, pid)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    if (confirm(confirmPollDelete)) {
        PollAjax.callAsync('DeletePoll', pid);
    }
    deselectDataGridRow();
}

/**
 *
 */
function keypressOnAnswer(event)
{
    var keyCode = (window.event)? event.keyCode : event.which;
    if (keyCode == '13') {
        addAnswer();
    }
}

/**
 *
 */
function addAnswer()
{
    var answer = $('#answer').val();
    $('#answer').val('')[0].focus();
    if (answer == '') return false;

    var box = $('#answers_combo')[0];
    if (box.selectedIndex != -1) {
        box.options[box.selectedIndex].text = answer;
        box.selectedIndex = -1;
    } else {
        box.options[box.options.length] = new Option(answer, 0);
    }
}

/**
 *
 */
function delAnswer()
{
    var box = $('#answers_combo')[0];
    if (box.selectedIndex != -1) {
        box.options[box.selectedIndex] = null;
    }
    stopAnswer();
}

/**
 *
 */
function editAnswer()
{
    var box = $('#answers_combo')[0];
    if (box.selectedIndex == -1) return;
    var answer = box.options[box.selectedIndex].text;
    if (answer.blank()) return false;

    $('#answer').val(answer)[0].focus();
}

/**
 *
 */
function stopAnswer()
{
    var box = $('#answers_combo')[0];
    $('#answer').val('')[0].focus();
    box.selectedIndex = -1;
}

/**
 *
 */
function upAnswer()
{
    var box = $('#answers_combo')[0];
    if (box.selectedIndex < 1) return;
    var tmpText  = box.options[box.selectedIndex - 1].text;
    var tmpValue = box.options[box.selectedIndex - 1].value;
    box.options[box.selectedIndex - 1].text  = box.options[box.selectedIndex].text;
    box.options[box.selectedIndex - 1].value = box.options[box.selectedIndex].value;
    box.options[box.selectedIndex].text  = tmpText;
    box.options[box.selectedIndex].value = tmpValue;
    box.selectedIndex = box.selectedIndex - 1;
}

/**
 *
 */
function downAnswer()
{
    var box = $('#answers_combo')[0];
    if (box.selectedIndex == -1) return;
    if (box.selectedIndex > box.length-2) return;
    var tmpText  = box.options[box.selectedIndex + 1].text;
    var tmpValue = box.options[box.selectedIndex + 1].value;
    box.options[box.selectedIndex + 1].text  = box.options[box.selectedIndex].text;
    box.options[box.selectedIndex + 1].value = box.options[box.selectedIndex].value;
    box.options[box.selectedIndex].text  = tmpText;
    box.options[box.selectedIndex].value = tmpValue;
    box.selectedIndex  = box.selectedIndex + 1;
}

/**
 * Edit poll group
 */
function editPollGroup(element, gid)
{
    if (gid == 0) return;

    currentAction = 'PollGroups';
    selectedPollGroup = gid;
    $('#legend_title').html(editPollGroup_title);
    if (cachePollGroupsForm != null) {
        $('#pg_work_area').html(cachePollGroupsForm);
    }

    selectDataGridRow(element.parentNode.parentNode);

    var groupInfo = PollAjax.callSync('GetPollGroup', selectedPollGroup);

    $('#gid').val(groupInfo['id']);
    $('#title').val(groupInfo['title'].defilter());
    $('#published').prop('selectedIndex', groupInfo['published']);
}

/**
 * Show a simple-form with checkboxes so polls can check their group
 */
function editPollGroupPolls(element, gid)
{
    if (cachePollGroupsForm == null) {
        cachePollGroupsForm = $('#pg_work_area').html();
    }

    selectDataGridRow(element.parentNode.parentNode);

    if (cachePollGroupPollsForm == null) {
        cachePollGroupPollsForm = PollAjax.callSync('PollGroupPollsUI');
    }

    currentAction = 'PollGroupPolls';
    selectedPollGroup = gid;
    $('#legend_title').html(editPollGroupPolls_title);
    $('#pg_work_area').html(cachePollGroupPollsForm);

    var pollsData = PollAjax.callSync('GetPollGroupPolls', selectedPollGroup);
    var pollsList  = pollsData['Polls'];
    $('#title').val(pollsData['title']);
    if ($('#pg_polls_combo').length) {
        var inputs  = $('#pg_polls_combo input');
        $.each(pollsList, function(index, value) {
            for (var i=0; i<inputs.length; i++) {
                if (value['id'] == inputs[i].value) {
                    inputs[i].checked= true;
                    break
                }
            }
        });
    }
}

/**
 * Saves data / changes on the poll group's form
 */
function savePollGroup()
{
    if (currentAction == 'PollGroupPolls') {
        if ($('#pg_polls_combo').length) {
            var inputs  = $('#pg_polls_combo').find('input');
            var keys    = [];
            var counter = 0;
            for (var i=0; i<inputs.length; i++) {
                if (inputs[i].checked) {
                    keys[counter] = inputs[i].value;
                    counter++;
                }
            }
            PollAjax.callAsync('AddPollsToPollGroup', [selectedPollGroup, keys]);

        }
    } else {
        if (!$('#title').val()) {
            alert(incompleteGroupsFields);
            return false;
        }

        if (selectedPollGroup == null) {
            PollAjax.callAsync(
                'InsertPollGroup', [
                    $('#title').val(),
                    $('#published').val()
                ]
            );
        } else {
            PollAjax.callAsync(
                'UpdatePollGroup', [
                    selectedPollGroup,
                    $('#title').val(),
                    $('#published').val()
                ]
            );
        }
    }
}

/**
 * Delete poll group
 */
function deletePollGroup(element, gid)
{
    stopAction();
    selectDataGridRow(element.parentNode.parentNode);
    if (confirm(confirmPollGroupDelete)) {
        PollAjax.callAsync('DeletePollGroup', gid)
    }
    deselectDataGridRow();
}

function getGroupPolls(gid)
{
    var box = $('#grouppolls')[0];
    box.length = 0;
    $('#result_area').html('');
    $('#legend_title').html('');
    if (gid == 0) return;
    var polls = PollAjax.callSync('GetGroupPolls', gid);
    for(var i = 0; i < polls.length; i++) {
        var op = new Option(polls[i]['title'], polls[i]['id']);
        if (i % 2 == 0) {
            op.style.backgroundColor = evenColor;
        } else {
            op.style.backgroundColor = oddColor;
        }
        box.options[i] = op;
    }
    box.selectedIndex == -1
}

function showResult(pid)
{
    var box = $('#grouppolls')[0];
    $('#legend_title').html(box.options[box.selectedIndex].text);
    $('#result_area').html(PollAjax.callSync('PollResultsUI', pid));
}

var PollAjax = new JawsAjax('Poll', PollCallback);

//Current poll
var selectedPoll = null;
//current poll group
var selectedPollGroup = null;

//Combo colors
var evenColor = '#fff';
var oddColor  = '#edf3fe';

//Cache for Poll template
var cachePollForm = null;
//Cache for Poll Answers template
var cachePollAnswersForm = null;
//Cache for PollGroups template
var cachePollGroupsForm = null;
//Cache for saving the PollGroup-Polls template
var cachePollGroupPollsForm = null;

//Which action are we running?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
