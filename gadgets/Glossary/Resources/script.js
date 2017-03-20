/**
 * Glossary Javascript actions
 *
 * @category   Ajax
 * @package    Glossary
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
/**
 * Glossary callbacks
 */
var GlossaryCallback = {

    NewTerm: function(response) {
        if (response[0]['type'] == 'alert-success') {
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
            if (response[0]['type'] == 'alert-success') {
                edit(combo.options[selIndex].value);
            }
        } else {
            createNewTerm();
        }
        GlossaryAjax.showResponse(response);
    },

    ParseText: function(response) {
        $('#preview_contents').html(response);
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
    $('#term_contents').val(term_data['description']);
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
        id       = $('#hidden_id').val();
        term     = $('#term_title').val();
        fast_url = $('#fast_url').val();
        contents = getEditorValue('#term_contents');
        if (term.blank() || contents.blank())
        {
            alert(jaws.Glossary.Defines.incompleteGlossaryFields);
            return false;
        }

        // Update Combo
        var combo = document.getElementById('term_id');
        combo.options[combo.selectedIndex].text = term;
        // Call function
        loading_message = jaws.Glossary.Defines.updatingMessage;
        GlossaryAjax.callAsync('UpdateTerm', [id, term, fast_url, contents]);
    }
}

/**
 * Delete a term
 */
function deleteTerm()
{
    loading_message = jaws.Glossary.Defines.deletingMessage;
    GlossaryAjax.callAsync('DeleteTerm', $('#term_id').val());
}

/**
 * Switch to a given tab (edit or preview)
 */
function switchTab(c, title)
{
    var editDiv    = $('#edit');
    var previewDiv = $('#preview');
    var editTab    = $('#editTab');
    if (title) {
        editTab.html(title);
    } else {
        var editTitle = editTab.html();
    }
    var previewTab    = $('#previewTab');
    var previewButton = $('#previewButton');
    var saveButton    = $('#saveButton');
    var cancelButton  = $('#cancelButton');
    var delButton     = $('#delButton');

    if (c == 'edit') {
        if (currentMode == 'new') {
            if (jaws.Glossary.Defines.aclAddTerm) {
                saveButton.onclick = function() {
                    newTerm();
                }
            } else {
                if (saveButton) {
                    saveButton.hide();
                }
            }

            if (jaws.Glossary.Defines.aclDeleteTerm) {
                delButton.hide();
            }

            cancelButton.show();
        } else {
            if (jaws.Glossary.Defines.aclEditTerm) {
                saveButton.onclick = function() {
                    updateTerm();
                }
            } else {
                saveButton.hide();
            }

            if (jaws.Glossary.Defines.aclDeleteTerm) {
                delButton.show();
            }

            cancelButton.hide();
        }
        editTab.className        = 'current';
        previewTab.className     = '';
        editDiv.show();
        previewDiv.hide();
    } else if (c == 'preview') {
        editTab.className = '';
        previewTab.className     = 'current';
        editDiv.hide();
        previewDiv.show();
    }
}

/**
 * Switch to edit mode
 */
function edit(id)
{
    previousID  = id;
    currentMode = 'edit';
    loading_message = jaws.Glossary.Defines.retrievingMessage;
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
    var term_contents = getEditorValue('#term_contents');
    $('#preview_title').html($('#term_title').val());

    // Use this if you want to use plugins
    GlossaryAjax.callAsync('ParseText', term_contents);
    //$('#preview_contents').html(term_contents);
}

/**
 * Switch to NEW mode
 */
function createNewTerm(title)
{
    currentMode = 'new';
    switchTab('edit', title);
    $('#term_id').prop('disabled', true);
    $('#term_title').val('');
    $('#term_title').focus();
    $('#fast_url').val('');
    $('#term_contents').val('');   
}

/**
 * Insert new term
 */
function newTerm()
{
    term     = $('#term_title').val();
    fast_url = $('#fast_url').val();
    contents = getEditorValue('#term_contents');
    if (term.blank() || contents.blank())
    {
        alert(jaws.Glossary.Defines.incompleteGlossaryFields);
        return false;
    }

    loading_message = jaws.Glossary.Defines.savingMessage;
    GlossaryAjax.callAsync('NewTerm', [term, fast_url, contents]);
}

/**
 * Add to combo after insert a new term
 */
function afterNewTerm(id)
{
    combo = document.getElementById('term_id');
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
            termTitle.val('');
            termTitle.focus();
            $('#term_contents').val('');
            b.disabled = true;
            combo.disabled = true;
        } else {
            loading_message = jaws.Glossary.Defines.retrievingMessage;
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
    combo = $('#term_id');
    if (combo.length > 0) {
        combo.val($("#term_id option:first").val());
        edit($("#term_id option:first").val());
    } else {
        createNewTerm('');
    }
}

$(document).ready(function() {
    getFirst();
});

var GlossaryAjax = new JawsAjax('Glossary', GlossaryCallback);

var currentMode = 'edit';
var previousID  = 'NEW';
var editTitle   = '';
