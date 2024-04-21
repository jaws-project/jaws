/**
 * Poll JS actions
 *
 * @category   Ajax
 * @package    Poll
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
function Jaws_Gadget_Poll() { return {
    // ASync callback method
    AjaxCallback : {
    },
}};
/**
 * Use async mode, create Callback
 */
var PollCallback = {
    InsertPoll: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#polls_datagrid')[0].addItem();
            $('#polls_datagrid')[0].setCurrentPage(0);
            getDG();
        }
    },

    UpdatePoll: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG();
        }
    },

    DeletePoll: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#polls_datagrid')[0].deleteItem();
            getDG();
        }
    },

    UpdatePollAnswers: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
        }
    },

    InsertPollGroup: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#pollgroups_datagrid')[0].addItem();
            $('#pollgroups_datagrid')[0].setCurrentPage(0);
            getDG();
        }
    },

    UpdatePollGroup: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            getDG();
        }
    },

    DeletePollGroup: function(response) {
        if (response['type'] == 'alert-success') {
            stopAction();
            $('#pollgroups_datagrid')[0].deleteItem();
            getDG();
        }
    },

    AddPollsToPollGroup: function(response) {
        stopAction();
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
        $('#legend_title').html(Jaws.gadgets.Poll.defines.addPoll_title);
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
        initDatePicker('start_time');
        initDatePicker('stop_time');
        break;
    case 'PollAnswers':
        selectedPoll = null;
        currentAction = 'Polls';
        $('#legend_title').html(Jaws.gadgets.Poll.defines.addPoll_title);
        $('#p_work_area')[0].innerHTML = cachePollForm;
        initDatePicker('start_time');
        initDatePicker('stop_time');
        deselectDataGridRow();
        break;
    case 'PollGroups':
        selectedPollGroup = null;
        $('#legend_title').html(Jaws.gadgets.Poll.defines.addPollGroup_title);
        $('#title').val('');
        $('#published').val(1);
        deselectDataGridRow();
        $('#title')[0].focus();
        break;
    case 'PollGroupPolls':
        selectedPollGroup = null;
        currentAction = 'PollGroups';
        $('#legend_title').html(Jaws.gadgets.Poll.defines.addPollGroup_title);
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
    $('#legend_title').html(Jaws.gadgets.Poll.defines.editPoll_title);
    if (cachePollForm != null) {
        $('#p_work_area')[0].innerHTML = cachePollForm;
        initDatePicker('start_time');
        initDatePicker('stop_time');
    }

    selectDataGridRow(element.parentNode.parentNode);

    var pollInfo = PollAjax.call('GetPoll', selectedPoll, false, {'async': false});

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
        cachePollAnswersForm = PollAjax.call('PollAnswersUI', {}, false, {'async': false});
    }
    currentAction = 'PollAnswers';

    selectedPoll = pid;
    $('#legend_title').html(Jaws.gadgets.Poll.defines.editAnswers_title);
    $('#p_work_area').html(cachePollAnswersForm);
    var answersData = PollAjax.call('GetPollAnswers', selectedPoll, false, {'async': false});
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
            alert(Jaws.gadgets.Poll.defines.requiresTwoAnswers);
            return false;
        }
        for(var i = 0; i < box.length; i++) {
            answers[i] = {};
            answers[i]['id']    = box.options[i].value;
            answers[i]['title'] = box.options[i].text;
        }
        PollAjax.call('UpdatePollAnswers', [selectedPoll, answers]);
    } else {
        if (!$('#title').val()) {
            alert(Jaws.gadgets.Poll.defines.incompletePollsFields);
            return false;
        }

        if (selectedPoll == null) {
            PollAjax.call(
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
            PollAjax.call(
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
    if (confirm(Jaws.gadgets.Poll.defines.confirmPollDelete)) {
        PollAjax.call('DeletePoll', pid);
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
    $('#legend_title').html(Jaws.gadgets.Poll.defines.editPollGroup_title);
    if (cachePollGroupsForm != null) {
        $('#pg_work_area').html(cachePollGroupsForm);
    }

    selectDataGridRow(element.parentNode.parentNode);

    var groupInfo = PollAjax.call('GetPollGroup', selectedPollGroup, false, {'async': false});

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
        cachePollGroupPollsForm = PollAjax.call('PollGroupPollsUI', {}, false, {'async': false});
    }

    currentAction = 'PollGroupPolls';
    selectedPollGroup = gid;
    $('#legend_title').html(Jaws.gadgets.Poll.defines.editPollGroupPolls_title);
    $('#pg_work_area').html(cachePollGroupPollsForm);

    var pollsData = PollAjax.call('GetPollGroupPolls', selectedPollGroup, false, {'async': false});
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
            PollAjax.call('AddPollsToPollGroup', [selectedPollGroup, keys]);

        }
    } else {
        if (!$('#title').val()) {
            alert(Jaws.gadgets.Poll.defines.incompleteGroupsFields);
            return false;
        }

        if (selectedPollGroup == null) {
            PollAjax.call(
                'InsertPollGroup', [
                    $('#title').val(),
                    $('#published').val()
                ]
            );
        } else {
            PollAjax.call(
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
    if (confirm(Jaws.gadgets.Poll.defines.confirmPollGroupDelete)) {
        PollAjax.call('DeletePollGroup', gid)
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
    var polls = PollAjax.call('GetGroupPolls', gid, false, {'async': false});
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
    $('#result_area').html(PollAjax.call('PollResultsUI', pid, false, {'async': false}));
}

$(document).ready(function() {
    switch (Jaws.defines.mainAction) {
        case 'Polls':
            currentAction = 'Polls';
            initDataGrid('polls_datagrid', PollAjax);
            stopAction();
            break;

        case 'PollGroups':
            currentAction = 'PollGroups';
            initDataGrid('pollgroups_datagrid', PollAjax);
            stopAction();
            break;

        case 'Reports':
            $('#pollgroups').selectedIndex = 0;
            break;
    }
});

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
