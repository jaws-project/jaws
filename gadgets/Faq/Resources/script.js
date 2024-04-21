/** * FAQ Javascript actions * * @category   Ajax * @package    Faq * @author     Pablo Fischer <pablo@pablo.com.mx> * @copyright   2005-2022 Jaws Development Group * @license    http://www.gnu.org/copyleft/gpl.html */function Jaws_Gadget_Faq() { return {    // ASync callback method    AjaxCallback : {    },}};/** * Use async mode, create Callback */var FaqCallback = {    InsertQuestion: function (response) {        if (response['type'] == 'alert-success') {            getDG('questions_datagrid', $('#questions_datagrid')[0].getCurrentPage(), true);            stopAction();        }    },    UpdateQuestion: function (response) {        if (response['type'] == 'alert-success') {            getDG('questions_datagrid', $('#questions_datagrid')[0].getCurrentPage(), true);            stopAction();        }    },    DeleteQuestion: function(response) {        if (response['type'] == 'alert-success') {            getDG('questions_datagrid', $('#questions_datagrid')[0].getCurrentPage(), true);            stopAction();        }    },    MoveQuestion: function(response) {        if (response['type'] == 'alert-success') {            getDG('questions_datagrid', $('#questions_datagrid')[0].getCurrentPage(), true);        }    },    InsertCategory: function (response) {        if (response['type'] == 'alert-success') {            getDG('categories_datagrid', $('#categories_datagrid')[0].getCurrentPage(), true);            stopAction();        }    },    UpdateCategory: function (response) {        if (response['type'] == 'alert-success') {            getDG('categories_datagrid', $('#categories_datagrid')[0].getCurrentPage(), true);            stopAction();        }    },    DeleteCategory: function(response) {        if (response['type'] == 'alert-success') {            getDG('categories_datagrid', $('#categories_datagrid')[0].getCurrentPage(), true);            stopAction();        }    },    MoveCategory: function(response) {        if (response['type'] == 'alert-success') {            getDG('categories_datagrid', $('#categories_datagrid')[0].getCurrentPage(), true);        }    }}/** * Stops doing a certain action */function stopAction() {    switch (currentAction) {        case 'Questions' :            $('#legend_title').html(Jaws.gadgets.Faq.defines.addQuestion_title);            selectedQuestion = null;            $('#answer').val('');            $('#question').val('');            $('#category').val(0);            $('#fast_url').val('');            $('#meta_keywords').val('');            $('#meta_description').val('');            $('#published').val(0);            unselectGridRow('questions_datagrid');            break;        case 'Categories' :            $('#legend_title').html(Jaws.gadgets.Faq.defines.addCategory_title);            selectedCategory = null;            $('#title').val('');            $('#fast_url').val('');            $('#meta_keywords').val('');            $('#meta_description').val('');            $('#description').val('');            unselectGridRow('categories_datagrid');            break;    }}/** * Fetches questions data to fills the data grid */function getQuestionsDataGrid(name, offset, reset){    var questions = FaqAjax.call(        'GetQuestions',        {'offset': offset, 'category': $('#category_filter').val()},        false,        {'async': false}    );    if (reset) {        stopAction();        $('#'+name)[0].setCurrentPage(0);        var total = FaqAjax.call('GetQuestionsCount', $('#category_filter').val(), false, {'async': false});    }    resetGrid(name, questions, total);}/** * Fetches categories data to fills the data grid */function getCategories(name, offset, reset){    var categories = FaqAjax.call('GetCategories', {}, false, {'async': false});    resetGrid(name, categories);}/** * Edit a question */function editQuestion(rowElement, id) {    selectGridRow('questions_datagrid', rowElement.parentNode.parentNode);    selectedQuestion = id;    $('#legend_title').html(Jaws.gadgets.Faq.defines.editQuestion_title);    var question = FaqAjax.call('GetQuestion', {'id': id}, false, {'async': false});    $('#question').val(question['question']);    $('#answer').val(question['answer']);    $('#category').val(question['category_id']);    $('#fast_url').val(question['fast_url']);    $('#meta_keywords').val(question['meta_keywords']);    $('#meta_description').val(question['meta_description']);    if(question['published']) {        $('#published').val(1);    } else {        $('#published').val(0);    }}/** * Edit a category */function editCategory(rowElement, id) {    stopAction();    selectGridRow('categories_datagrid', rowElement.parentNode.parentNode);    selectedCategory = id;    $('#legend_title').html(Jaws.gadgets.Faq.defines.editCategory_title);    var category = FaqAjax.call('GetCategory', {'id': id}, false, {'async': false});    $('#title').val(category['category']);    $('#fast_url').val(category['fast_url']);    $('#meta_keywords').val(category['meta_keywords']);    $('#meta_description').val(category['meta_description']);    $('#description').val(category['description']);}/** * Saves data / changes */function saveQuestion(){    var answer = $('#answer').val();    if (!$('#question').val() || answer == '')    {        alert(Jaws.gadgets.Faq.defines.incompleteQuestionFields);        return false;    }    if (selectedQuestion == null) {        FaqAjax.call(            'InsertQuestion', {                'data': {                    'question': $('#question').val(),                    'answer': answer,                    'category': $('#category').val(),                    'fast_url': $('#fast_url').val(),                    'meta_keywords': $('#meta_keywords').val(),                    'meta_description': $('#meta_description').val(),                    'published': $('#published').val()                }            }        );    } else {        FaqAjax.call(            'UpdateQuestion', {                'id': selectedQuestion,                'data': {                    'question': $('#question').val(),                    'answer': answer,                    'category': $('#category').val(),                    'fast_url': $('#fast_url').val(),                    'meta_keywords': $('#meta_keywords').val(),                    'meta_description': $('#meta_description').val(),                    'published': $('#published').val()                }            }        );    }}/** * Saves data / changes */function saveCategory(){    if (!$('#title').val())    {        alert(incompleteCategoryFields);        return false;    }    if (selectedCategory == null) {        FaqAjax.call(            'InsertCategory', {                'data': {                    'category': $('#title').val(),                    'description': $('#description').val(),                    'fast_url': $('#fast_url').val(),                    'meta_keywords': $('#meta_keywords').val(),                    'meta_description': $('#meta_description').val()                }            }        );    } else {        FaqAjax.call(            'UpdateCategory', {                'id': selectedCategory,                'data': {                    'category': $('#title').val(),                    'description': $('#description').val(),                    'fast_url': $('#fast_url').val(),                    'meta_keywords': $('#meta_keywords').val(),                    'meta_description': $('#meta_description').val()                }            }        );    }}/** * Delete a question */function deleteQuestion(rowElement, id){    stopAction();    if (confirm(Jaws.gadgets.Faq.defines.confirmQuestionDelete)) {        selectGridRow('servers_datagrid', rowElement.parentNode.parentNode);        FaqAjax.call('DeleteQuestion', {'id': id});    }}/** * Delete a category */function deleteCategory(rowElement, id){    stopAction();    if (confirm(Jaws.gadgets.Faq.defines.confirmCategoryDelete)) {        selectGridRow('categories_datagrid', rowElement.parentNode.parentNode);        FaqAjax.call('DeleteCategory', {'id': id});    }}/** * Move a question */function moveQuestion(category, id, position, direction){    if (position == 1 && direction < 0) {        return;    }    FaqAjax.call('MoveQuestion', {'category': category, 'id': id, 'position': position, 'direction': direction});}/** * Move a category */function moveCategory(id, position, direction){    if (position == 1 && direction < 0) {        return;    }    FaqAjax.call('MoveCategory', {'id': id, 'old_pos': position, 'new_pos': position + direction});}$(document).ready(function() {    switch (Jaws.defines.mainAction) {        case 'Questions':            $('#bgroup_filter').prop('selectedIndex', 0);            currentAction = 'Questions';            stopAction();            initDataGrid('questions_datagrid', FaqAjax, getQuestionsDataGrid);            break;        case 'Categories':            currentAction = 'Categories';            stopAction();            initDataGrid('categories_datagrid', FaqAjax, 'getCategories');            break;    }});var FaqAjax = new JawsAjax('Faq', FaqCallback);var currentAction = null,    selectedQuestion = null,    selectedCategory = null;faqSortable = null;