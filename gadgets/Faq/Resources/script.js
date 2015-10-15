/**
 * FAQ Javascript actions
 *
 * @category   Ajax
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FaqCallback = {
    DeleteQuestion: function(response) {
        FaqAjax.showResponse(response);
        if (response[0]['type'] == 'response_notice') {
            buildCategory(currentCategory);
        }
    },

    DeleteCategory: function(response) {
        if (response[0]['type'] == 'response_notice') {
            $('FaqCategory_'+currentCategory).destroy();
        }
        FaqAjax.showResponse(response);
    },

    MoveCategory: function(response) {
        FaqAjax.showResponse(showResponse(response);
    },

    MoveQuestion: function(response) {
        FaqAjax.showResponse(response);
        if (response[0]['type'] == 'response_notice') {
            buildCategory(currentCategory);
        }
    }
}

/**
 * Prepare the preview
 */
function parseQuestionText(form)
{
    var answer   = getEditorValue('answer');
    var question = form.elements['question'].value;

    var previewArea = document.getElementById('preview');
    previewArea.style.display = 'block';

    var answerArea   = document.getElementById('answerPreview');
    var questionArea = document.getElementById('questionPreview');

    answer = FaqAjax.callSync('ParseText', answer);
    answerArea.innerHTML   = answer;
    questionArea.innerHTML = question;
}

/**
 * Prepare the preview
 */
function parseCategoryText(form)
{
    var category    = form.elements['category'].value;
    var description = getEditorValue('description');

    var previewArea = document.getElementById('preview');
    previewArea.style.display = 'block';

    var descriptionArea = document.getElementById('descriptionPreview');
    var categoryArea    = document.getElementById('categoryPreview');

    description = FaqAjax.callSync('ParseText', description);
    descriptionArea.innerHTML = description;
    categoryArea.innerHTML    = category;
}

/**
 * Show another category (or show all)
 */
function showCategory(form)
{
    var category = form.elements['category'].value;
    var categoryOptions = form.elements['category'].options;
    var optionValue = 0;
    var categoryDiv = '';
    for(i=0; i<categoryOptions.length; i++) {
        optionValue = categoryOptions[i].value;
        if (optionValue != '*') {
            categoryDiv = document.getElementById('FaqCategory_' + optionValue);
            if (!categoriesInfo['' + optionValue + '']['deleted']  && optionValue == category) {
                categoryDiv.style.display = 'block';
            } else if(!categoriesInfo['' + optionValue + '']['deleted']  && category == '*') {
                categoryDiv.style.display = 'block';
            } else {
                categoryDiv.style.display = 'none';
            }
        }
    }
}

/**
 * Delete a question
 */
function deleteQuestion(id, category)
{
    FaqAjax.callAsync('DeleteQuestion', id);
    currentCategory = category;
}

/**
 * Delete a category
 */
function deleteCategory(id)
{
    FaqAjax.callAsync('DeleteCategory', id);
    currentCategory = id;
}

/**
 * Move a question
 */
function moveQuestion(category, id, position, direction)
{
    if (position == 1 && direction < 0) {
        return;
    }

    FaqAjax.callAsync('MoveQuestion', [category, id, position, direction]);
    currentCategory = category;
}

/**
 * Call the datagrid constructor and builds all the Main Area
 */
function buildMainArea()
{
    var result = FaqAjax.callSync('getmainarea');
    var area = document.getElementById('ManageQuestions');
    area.innerHTML = result;
}

/**
 * Only builds the datagrid of a category
 *
 */
function buildCategory(id)
{
    if (id) {
        var result = FaqAjax.callSync('GetCategoryGrid', id);
        var categoryArea = $('FaqDataGridOfCategory_' + id);
        categoryArea.innerHTML = result;
        currentCategory = false;
    }
}

/**
 * Repopulates the catForm
 */
function rePopulateCatForm()
{
    var catForm          = document.forms['catForm'];
    var categoryOptions  = catForm.elements['category'].options;
    var firstOption      = categoryOptions[0];
    var originalNames    = [];
    var options          = null;
    var pattern          = /^(.*?).\s+(.*?)/;

    for (i=0; i< categoryOptions.length; i++) {
        var nameSplitted =  categoryOptions[i].innerHTML.split(pattern);
        originalNames['' + categoryOptions[i].value + ''] = [];
        originalNames['' + categoryOptions[i].value + '']['name']  = nameSplitted[3];
        originalNames['' + categoryOptions[i].value + '']['value'] = categoryOptions[i].value;
    }

    var j = 1;
    var categoryId  = null;
    var categoryPos = null;
    var lastColor   = '#fff';
    for(i=0; i<categoriesInfo.length; i++) {
        if (categoriesInfo[i] != undefined) {
            if (categoriesInfo[i]['id'] != undefined) {
                categoryId  = categoriesInfo[i]['id'];
                categoryPos = categoriesInfo[i]['pos'];
                if (!categoriesInfo['' + categoryId + '']['deleted']) {
                    option = new Option();
                    option.style.backgroundColor = lastColor;
                    option.text                  = categoryPos + '. ' + originalNames['' + categoryId + '']['name'];
                    option.value                 = originalNames['' + categoryId + '']['value'];
                    categoryOptions[categoryPos] = option;
                    lastColor = (lastColor == '#fff') ? '#eee': '#fff';
                    j++;
                } else {
                    categoryOptions[categoryPos].style.display = 'none';
                    categoryOptions[categoryPos] = null;
                }
            }
        }
    }

}

/**
 * Save the selected ID
 */
function saveSelectedID(element)
{
    selectedID = element.id.replace('FaqCategory_', '');
}

/**
 * Initializes some variables
 */
function initUI()
{
    faqSortable = new Sortables( '#ManageQuestions', {
        clone: false,
        revert: true,
        opacity: 0.7,

        onStart: function(el) {
            el.setProperty('old_position', el.getParent().getElements('div.category[id]').indexOf(el) + 1);
        },

        onComplete: function(el) {
            var new_position = el.getParent().getElements('div.category[id]').indexOf(el) + 1;
            if (el.getProperty('old_position') &&
                new_position != el.getProperty('old_position'))
            {
                FaqAjax.callAsync(
                    'MoveCategory', [
                        el.id.replace('FaqCategory_', ''),
                        el.getProperty('old_position'),
                        new_position
                    ]
                );
            }
            el.removeProperty('old_position');
        }
    });
}

var FaqAjax = new JawsAjax('Faq', FaqCallback);

var selectedID      = 0;
var currentCategory = false;

faqSortable = null;
