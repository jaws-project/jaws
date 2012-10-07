/**
 * FAQ Javascript actions
 *
 * @category   Ajax
 * @package    Faq
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Use async mode, create Callback
 */
var FaqCallback = {
    deletequestion: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            buildCategory(currentCategory);
        }
    },

    deletecategory: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
            deleteCategoryArea(currentCategory);
        }
    },

    fixpositions: function(response) {
        showResponse(response);
    },

    movequestion: function(response) {
        showResponse(response);
        if (response[0]['css'] == 'notice-message') {
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

    var f          = new faqadminajax();
    var parsedText = f.parsetext(answer);

    answerArea.innerHTML   = parsedText;
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

    var f          = new faqadminajax();
    var parsedText = f.parsetext(description);

    descriptionArea.innerHTML = parsedText;
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
    faq.deletequestion(id);
    currentCategory = category;
}

/**
 * Delete a category
 */
function deleteCategory(id)
{
    faq.deletecategory(id);
    currentCategory = id;
}

/**
 * Delete the DIV of a category
 */
function deleteCategoryArea(category)
{
    categoriesInfo['' + category + '']['deleted'] = true;
    var catForm          = document.forms['catForm'];
    var categoryOptions  = catForm.elements['category'].options;
    var categoryPosition = null;
    var lastOption       = null;
    var pattern          = /^(.*?).\s+(.*?)/;

    for(i=0; i<categoryOptions.length; i++) {
        var optionValue = categoryOptions[i].value;
        if (optionValue != '*') {
            var nameSplitted = categoryOptions[i].innerHTML.split(pattern);
            var optionText   = nameSplitted[3];
            if (optionValue == category) {
                categoryOptions[i].style.display = 'none';
                lastOption = i;
            } else {
                if (lastOption != null) {
                    if (!categoriesInfo['' + optionValue + '']['deleted']) {
                        categoryOptions[i].innerHTML = lastOption + '. ' + optionText;
                    }
                    categoryPosition = document.getElementById('FaqCategoryPosition_' + optionValue);
                    categoryPosition.innerHTML = lastOption;
                    lastOption = null;
                } else {
                    if (!categoriesInfo['' + optionValue + '']['deleted']) {
                        categoryOptions[i].innerHTML = i + '. ' + optionText;
                    }
                    categoryPosition = document.getElementById('FaqCategoryPosition_' + optionValue);
                    categoryPosition.innerHTML = i;
                }
            }
        }
    }


    var categoryDiv = document.getElementById('FaqCategory_' + category);
    categoryDiv.style.display = 'none';
    categoryDiv.id = 'FaqDeletedCategory_' + category;
}

/**
 * Move a question
 */
function moveQuestion(id, category, direction)
{
    faq.movequestion(id, direction);
    currentCategory = category;
}

/**
 * Call the datagrid constructor and builds all the Main Area
 */
function buildMainArea()
{
    var f = new faqadminajax();
    var result = f.getmainarea();

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
        var f = new faqadminajax();
        var result = f.getcategorygrid(id);

        var categoryArea = document.getElementById('FaqDataGridOfCategory_' + id);
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
 * Update the category
 *
 * @param  string   element  Element name (div name)
 * @param  string   getList  ordered List in GET format
 */
function moveCategory(element, getList)
{
    var pattern    = /ManageQuestions\[\]=(.*?)/;
    //convert GET list to an array
    var ids          = getList.split(pattern);
    var category     = null;
    var catSpanPos   = null;
    var realIterator = 0;

    for(i=0; i<ids.length; i++) {
        if (ids[i] != '') {
            category = ids[i].replace('&', '');
            if (!categoriesInfo['' + category + '']['deleted']) {
                categoriesInfo['' + category + '']['id']      = category;
                categoriesInfo['' + category + '']['pos']     = (realIterator + 1);
                categoriesInfo['' + category + '']['deleted'] = categoriesInfo['' + category + '']['deleted'];

                catSpanPos = document.getElementById('FaqCategoryPosition_' + category);
                catSpanPos.innerHTML = (realIterator + 1);
                realIterator++;
            }
        }
    }
    faq.fixpositions(categoriesInfo);
    rePopulateCatForm();
}

/**
 * Save the selected ID
 */
function saveSelectedID(element)
{
    selectedID = element.id.replace('FaqCategory_', '');
}

var faq = new faqadminajax(FaqCallback);
faq.serverErrorFunc = Jaws_Ajax_ServerError;
faq.onInit = showWorkingNotification;
faq.onComplete = hideWorkingNotification;

var selectedID      = 0;
var currentCategory = false;
