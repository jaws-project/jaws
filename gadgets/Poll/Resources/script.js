/**
 * Poll JS actions
 *
 * @category   Ajax
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var PollCallback = {
    InsertPoll: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('polls_datagrid')[0].addItem();
            $('polls_datagrid')[0].setCurrentPage(0);
            getDG();
        }
        PollAjax.showResponse(response);
    },

    UpdatePoll: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            getDG();
        }
        showResponse(response);
    },

    DeletePoll: function(response) {
        if (response[0]['type'] == 'response_notice') {
            stopAction();
            $('polls_datagrid')[0].deleteItem();
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
            $('pollgroups_datagrid')[0].addItem();
            $('pollgroups_datagrid')[0].setCurrentPage(0);
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
            $('pollgroups_datagrid')[0].deleteItem();
            getDG();
        }
        PollAjax.showResponse(response);
    },

    AddPollsToPollGroup: function(response) {
        stopAction();
        PollAjax.showResponse(response);
    }
}

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
 * Unselect DataGrid row
 *
 */
function unselectDataGridRow()
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
        $('legend_title').html(addPoll_title);
        $('#question').val('');
        $('gid').selectedIndex = 0;
        $('#start_time').val('');
        $('#stop_time').val('');
        $('#select_type').val(0);
        $('#poll_type').val(0);
        $('#result_view').val(1);
        $('#visible').val(1);
        unselectDataGridRow();
        $('question').focus();
        break;
    case 'PollAnswers':
        selectedPoll = null;
        currentAction = 'Polls';
        $('legend_title').html(addPoll_title);
        $('p_work_area').html(cachePollForm);
        initDatePicker('start_time');
        initDatePicker('stop_time');
        unselectDataGridRow();
        break;
    case 'PollGroups':
        selectedPollGroup = null;
        $('legend_title').html(addPollGroup_title);
        $('#title').val('');
        $('#visible').val(1);
        unselectDataGridRow();
        $('title').focus();
        break;
    case 'PollGroupPolls':
        selectedPollGroup = null;
        currentAction = 'PollGroups';
        $('legend_title').html(addPollGroup_title);
        $('pg_work_area').html(cachePollGroupsForm);
        unselectDataGridRow();
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
    $('legend_title').html(editPoll_title);
    if (cachePollForm != null) {
        $('p_work_area').html(cachePollForm);
        initDatePicker('start_time');
        initDatePicker('stop_time');
    }

    selectDataGridRow(element.parentNode.parentNode);

    var pollInfo = PollAjax.callSync('GetPoll', selectedPoll);

    $('#question').val(pollInfo['question'].defilter());
    $('#gid').val(pollInfo['gid']);
    if (pollInfo['start_time'] == null) pollInfo['start_time'] = '';
    if (pollInfo['stop_time']  == null) pollInfo['stop_time']  = '';
    $('#start_time').val(pollInfo['start_time']);
    $('#stop_time').val(pollInfo['stop_time']);
    $('#select_type').val(pollInfo['select_type']);
    $('#poll_type').val(pollInfo['poll_type']);
    $('#result_view').val(pollInfo['result_view']);
    $('#visible').val(pollInfo['visible']);
}

/**
 * Edit Poll Answers
 */
function editPollAnswers(element, pid)
{
    if (cachePollForm == null) {
        cachePollForm = $('p_work_area').html();
    }

    selectDataGridRow(element.parentNode.parentNode);

    if (cachePollAnswersForm == null) {
        cachePollAnswersForm = PollAjax.callSync('PollAnswersUI');
    }
    currentAction = 'PollAnswers';

    selectedPoll = pid;
    $('legend_title').html(editAnswers_title);
    $('p_work_area').html(cachePollAnswersForm);
    var answersData = PollAjax.callSync('GetPollAnswers', selectedPoll);
    var answers  = answersData['Answers'];
    $('#question').val(answersData['question'].defilter());

    var box = $('answers_combo');
    box.length = 0;
    for(var i = 0; i < answers.length; i++) {
        box.options[i] = new Option(answers[i]['answer'].defilter(), answers[i]['id']);
    }
    $('answer').focus();
}

/**
 * Saves data / changes on the poll group's form
 */
function savePoll()
{
    if (currentAction == 'PollAnswers') {
        var box = $('answers_combo');
        var answers = new Array();
        if (box.length < 2) {
            alert(requiresTwoAnswers);
            return false;
        }
        for(var i = 0; i < box.length; i++) {
            answers[i] = new Array();
            answers[i]['id']     = box.options[i].value;
            answers[i]['answer'] = box.options[i].text;
        }
        PollAjax.callAsync('UpdatePollAnswers', [selectedPoll, answers]);
    } else {
        if (!$('question').val()) {
            alert(incompletePollsFields);
            return false;
        }

        if (selectedPoll == null) {
            PollAjax.callAsync(
                'InsertPoll', [
                    $('#question').val(),
                    $('#gid').val(),
                    $('#start_time').val(),
                    $('#stop_time').val(),
                    $('#select_type').val(),
                    $('#poll_type').val(),
                    $('#result_view').val(),
                    $('#visible').val()
                ]
            );
        } else {
            PollAjax.callAsync(
                'UpdatePoll', [
                    selectedPoll,
                    $('#question').val(),
                    $('#gid').val(),
                    $('#start_time').val(),
                    $('#stop_time').val(),
                    $('#select_type').val(),
                    $('#poll_type').val(),
                    $('#result_view').val(),
                    $('#visible').val()
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
    unselectDataGridRow();
}

/**
 *
 */
function keypressOnAnswer(event)
{
    keynum = (window.event)? event.keyCode : event.which;
    if(keynum == '13') {
        addAnswer();
    }
}

/**
 *
 */
function addAnswer()
{
    var answer = $('answer').value;
    $('#answer').val('');
    $('answer').focus();
    if (answer.blank()) return false;

    var box = $('answers_combo');
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
    var box = $('answers_combo');
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
    var box = $('answers_combo');
    if (box.selectedIndex == -1) return;
    var answer = box.options[box.selectedIndex].text;
    if (answer.blank()) return false;

    $('#answer').val(answer);
    $('answer').focus();
}

/**
 *
 */
function stopAnswer()
{
    var box = $('answers_combo');
    $('#answer').val('');
    box.selectedIndex = -1;
    $('answer').focus();
}

/**
 *
 */
function upAnswer()
{
    var box = $('answers_combo');
    if (box.selectedIndex < 1) return;
    var tmpText  = box.options[box.selectedIndex - 1].text;
    var tmpValue = box.options[box.selectedIndex - 1].value;
    box.options[box.selectedIndex - 1].text  = box.options[box.selectedIndex].text;
    box.options[box.selectedIndex - 1].value = box.options[box.selectedIndex].value;
    box.options[box.selectedIndex].text  = tmpText;
    box.options[box.selectedIndex].value = tmpValue;
    box.selectedIndex  = box.selectedIndex - 1;
}

/**
 *
 */
function downAnswer()
{
    var box = $('answers_combo');
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
    $('legend_title').html(editPollGroup_title);
    if (cachePollGroupsForm != null) {
        $('pg_work_area').html(cachePollGroupsForm);
    }

    selectDataGridRow(element.parentNode.parentNode);

    var groupInfo = PollAjax.callSync('GetPollGroup', selectedPollGroup);

    $('#gid').val(groupInfo['id']);
    $('#title').val(groupInfo['title'].defilter());
    $('visible').selectedIndex = groupInfo['visible'];
}

/**
 * Show a simple-form with checkboxes so polls can check their group
 */
function editPollGroupPolls(element, gid)
{
    if (cachePollGroupsForm == null) {
        cachePollGroupsForm = $('pg_work_area').html();
    }

    selectDataGridRow(element.parentNode.parentNode);

    if (cachePollGroupPollsForm == null) {
        cachePollGroupPollsForm = PollAjax.callSync('PollGroupPollsUI');
    }

    currentAction = 'PollGroupPolls';
    selectedPollGroup = gid;
    $('legend_title').html(editPollGroupPolls_title);
    $('pg_work_area').html(cachePollGroupPollsForm);

    var pollsData = PollAjax.callSync('GetPollGroupPolls', selectedPollGroup);
    var pollsList  = pollsData['Polls'];
    $('#title').val(pollsData['title']);
    if ($('pg_polls_combo')) {
        var inputs  = $('pg_polls_combo').getElementsByTagName('input');
        pollsList.each(function(value, index) {
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
        if ($('pg_polls_combo')) {
            var inputs  = $('pg_polls_combo').getElementsByTagName('input');
            var keys    = new Array();
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
        if (!$('title').val()) {
            alert(incompleteGroupsFields);
            return false;
        }

        if (selectedPollGroup == null) {
            PollAjax.callAsync(
                'InsertPollGroup', [
                    $('title').val(),
                    $('visible').val()
                ]
            );
        } else {
            PollAjax.callAsync(
                'UpdatePollGroup', [
                    selectedPollGroup,
                    $('#title').val(),
                    $('#visible').val()
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
    unselectDataGridRow();
}

function getGroupPolls(gid)
{
    var box = $('grouppolls');
    box.length = 0;
    $('result_area').html('');
    $('legend_title').html('');
    if (gid == 0) return;
    var polls = PollAjax.callSync('GetGroupPolls', gid);
    for(var i = 0; i < polls.length; i++) {
        var op = new Option(polls[i]['question'], polls[i]['id']);
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
    var box = $('grouppolls');
    $('legend_title').html(box.options[box.selectedIndex].text);
    $('result_area').html(PollAjax.callSync('PollResultsUI', pid));
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

//Which action are we runing?
var currentAction = null;

//Which row selected in DataGrid
var selectedRow = null;
var selectedRowColor = null;
