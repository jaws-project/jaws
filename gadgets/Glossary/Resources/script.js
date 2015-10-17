/**
 * Glossary Javascript actions
 *
 * @category   Ajax
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Glossary callbacks
 */
var GlossaryCallback = {

    NewTerm: function(response) {
        if (response[0]['type'] == 'response_notice') {
            afterNewTerm(response['id']);
        }
        GlossaryAjax.showResponse(response);
    },

    UpdateTerm: function(response) {
        GlossaryAjax.showResponse(response);
    },

    DeleteTerm: function(response) {
        var combo = document.getElementById('term_id');
        var auxStyle = combo.options[combo.selectedIndex].style.backgroundColor;
        selIndex = combo.selectedIndex;
        combo.remove(selIndex);
        // Set option styles...
        for (i = selIndex; i <= combo.length - 1; i++) {
            aux = combo.options[i].style.backgroundColor;
            combo.options[i].style.backgroundColor = auxStyle;
            auxStyle = aux;
        }
        // Select next
        if (combo.length > 0) {
            if (selIndex <= combo.length - 1) {
                combo.options[selIndex].selected = true;
            } else {
                selIndex = 0;
                combo.options[selIndex].selected = true;
            }
            if (response[0]['type'] == 'response_notice') {
                edit(combo.options[selIndex].value);
            }
        } else {
            createNewTerm();
        }
        GlossaryAjax.showResponse(response);
    },

    ParseText: function(response) {
        $('preview_contents').html(response);
    }
}

/**
 * Fill editor entries
 */
function fillEditorEntries(term_data)
{
    $('#hidden_id').val(term_data['id']);
    $('#term_title').val(term_data['term'].defilter());
    $('#fast_url').val(term_data['fast_url']);
    changeEditorValue('term_contents', term_data['description']);
    currentMode = 'edit';
}

/**
 * Update a term
 */
function updateTerm()
{
    if (currentMode == 'new') {
        newTerm();
    } else {
        id       = $('hidden_id').value;
        term     = $('term_title').value;
        fast_url = $('fast_url').value;
        contents = getEditorValue('term_contents');
        if (term.blank() || contents.blank())
        {
            alert(incompleteGlossaryFields);
            return false;
        }

        // Update Combo
        var combo = document.getElementById('term_id');
        combo.options[combo.selectedIndex].text = term;
        // Call function
        loading_message = updatingMessage;
        GlossaryAjax.callAsync('UpdateTerm', [id, term, fast_url, contents]);
    }
}

/**
 * Delete a term
 */
function deleteTerm()
{
    id = document.getElementById('term_id').value;
    loading_message = deletingMessage;
    GlossaryAjax.callAsync('DeleteTerm', id);
}

/**
 * Switch to a given tab (edit or preview)
 */
function switchTab(c, title)
{
    var editDiv    = document.getElementById('edit');
    var previewDiv = document.getElementById('preview');
    var editTab    = document.getElementById('editTab');
    if (title) {
        editTab.innerHTML = title;
    } else {
        var editTitle = editTab.innerHTML;
    }
    var previewTab    = $('previewTab');
    var previewButton = $('previewButton');
    var saveButton    = $('saveButton');
    var cancelButton  = $('cancelButton');
    var delButton     = $('delButton');

    if (c == 'edit') {
        if (currentMode == 'new') {
            if (aclAddTerm) {
                saveButton.onclick = function() {
                    newTerm();
                }
            } else {
                if (saveButton) {
                    saveButton.style.display = 'none';
                }
            }

            if (aclDeleteTerm) {
                delButton.style.display = 'none';
            }

            cancelButton.style.display = 'inline';
        } else {
            if (aclEditTerm) {
                saveButton.onclick = function() {
                    updateTerm();
                }
            } else {
                saveButton.style.display = 'none';
            }

            if (aclDeleteTerm) {
                delButton.style.display = 'inline';
            }

            cancelButton.style.display = 'none';
        }
        editTab.className        = 'current';
        previewTab.className     = '';
        editDiv.style.display    = 'block';
        previewDiv.style.display = 'none';
    } else if (c == 'preview') {
        editTab.className = '';
        previewTab.className     = 'current';
        editDiv.style.display    = 'none';
        previewDiv.style.display = 'block';
    }
}

/**
 * Switch to edit mode
 */
function edit(id)
{
    previousID  = id;
    currentMode = 'edit';
    loading_message = retrievingMessage;
    var termData = GlossaryAjax.callSync('GetTerm', id);
    fillEditorEntries(termData);
    editTitle  = termData['term'];
    switchTab('edit', termData['term']);
}

/**
 * Switch to preview mode
 */
function preview()
{
    switchTab('preview');
    var term_contents = getEditorValue('term_contents');
    $('preview_title').html($('term_title').value);

    // Use this if you want to use plugins
    GlossaryAjax.callAsync('ParseText', term_contents);
    //$('preview_contents').html(term_contents);
}

/**
 * Switch to NEW mode
 */
function createNewTerm(title)
{
    currentMode = 'new';
    switchTab('edit', title);
    $('term_id').disabled = true;
    $('#term_title').val('');
    $('term_title').focus();
    $('#fast_url').val('');
    changeEditorValue('term_contents', '');   
}

/**
 * Insert new term
 */
function newTerm()
{
    term     = $('term_title').value;
    fast_url = $('fast_url').value;
    contents = getEditorValue('term_contents');
    if (term.blank() || contents.blank())
    {
        alert(incompleteGlossaryFields);
        return false;
    }

    loading_message = savingMessage;
    GlossaryAjax.callAsync('NewTerm', [term, fast_url, contents]);
}

/**
 * Add to combo after insert a new term
 */
function afterNewTerm(id)
{
    combo = $('term_id');
    combo.disabled = false;
    combo.options[combo.length] = new Option($('#term_title').val(), id);
    combo.options[combo.length - 1].selected = true;
    edit(id);
}

/**
 * Return to edit mode
 */
function returnToEdit()
{
    combo     = document.getElementById('term_id');
    b         = document.getElementById('newButton');
    termTitle = document.getElementById('term_title');

    if (combo.length > 0) {
        if (previousID == 'NEW') {
            termTitle.value = '';
            termTitle.focus();
            changeEditorValue('term_contents', '');
            b.disabled = true;
            combo.disabled = true;
        } else {
            loading_message = retrievingMessage;
            var termData = GlossaryAjax.callSync('GetTerm', previousID);
            fillEditorEntries(termData);
            b.disabled = false;
            combo.disabled = false;
        }
        currentMode = 'edit';
        switchTab('edit', editTitle);
    } else {
        currentMode = 'new';
        createNewTerm();
    }
}

/**
 * Get first term, if not exists then NEW.
 */
function getFirst()
{
    combo = $('term_id');
    if (combo.length > 0) {
        combo.options[0].selected = true;
        edit(combo.options[0].value);
    } else {
        createNewTerm('');
    }
}

var GlossaryAjax = new JawsAjax('Glossary', GlossaryCallback);

var currentMode = 'edit';
var previousID  = 'NEW';
var editTitle   = '';
