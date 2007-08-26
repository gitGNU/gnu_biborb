/**
    Display hide an HTML element.
*/
function toggle_element(id){
    if(document.getElementById){
        document.getElementById(id).style.display = (document.getElementById(id).style.display == 'none') ? 'block' : 'none';
    }
}

// Add a group into the groups input field.
// Added if not already present.
function addGroup()
{
    var groups = document.forms['formAddReference'].elements['groups'];
    var groupslist = document.forms['formAddReference'].elements['listOfGroups'];
    var groupArray = groups.value.split(",");
    var addGroup = trim(groupslist.options[groupslist.selectedIndex].value);

    // exit if there is no group
    if(addGroup == '')
        return;

    // look if group already present
    // if not, add it
    found = false;    
    for(i=0;i<groupArray.length && !found; i++)
    {
        found = (groupArray[i] == addGroup);
    }
    if(!found)
    {
        if(groups.value != "")
        {
            groups.value += ",";
        }
        groups.value += addGroup;
    }
}

// Change the database
function change_db(name){
    window.location="./bibindex.php?bibname="+name;
}

function changeLangForBibIndex(name){
    window.location="./bibindex.php?language="+name;
}
function changeLangForIndex(name){
    window.location="./index.php?action=select_lang&lang="+name;
}

////////////////////////////////////////////////////////////////////////////////
// check forms


/**
 * Validate the form adding bibliographies
 */
function checkAddBib(bibs, lang)
{
    var aElement = document.forms['formAddBib'].elements['database_name'];
    var aValue = trim(aElement.value);
    
    
	if( aValue == "" )
    {
		alert("Bibliography name is not set!");
        aElement.focus();
		return false;
	}
    else
    {
        for (i in bibs)
        {
            if (bibs[i] == aValue)
            {
                aElement.focus();
                alert("This bilbiography already exists!");
                return false;
            }
        }
        document.forms['formAddBib'].submit();        
        return true;
    }
}

/**
 * Validate the form deleting bibliographies
 */
function checkDeleteBibs(lang)
{
    var aElements = document.forms['formDeleteBibs'].elements;
    var aBibSelected = false;
    
    for (var i=0;i<aElements.length && !aBibSelected;i++)
    {
        if (aElements[i].name == "bibs[]")
        {
            aBibSelected = aElements[i].checked;
        }
    }
    if (aBibSelected)
    {
        document.forms['formDeleteBibs'].submit();
        return true;
    }
    else
    {
        alert("No bibliographies selected!");
        return false;
    }
    

}


/**
 * Validate the form deleting bibliographies
 */
function checkAddRefForm(lang,requiredElements)
{
    var aElements = document.forms['f_bibtex_entry'].elements;
    var aMissingData = false;
    
    for (var i=0;i<aElements.length && !aMissingData;i++)
    {
        if ( requiredElements.find(aElements[i].name) )
        {
            aMissingData = trim(aElements[i].value) == "";            
        }
    }
    if (aMissingData)
    {
        alert("Missing value for" + aElements[i-1].name);
    }
    return false;
    
}

            

// check if group is not empty
function validate_add_group(lang){
    var msg;
    var group = document.forms['add_new_group'].elements['newgroupvalue'].value;

    if(lang == 'fr_FR'){
		msg = "Nom de groupe vide!";
	}
	else if(lang == 'en_US'){
		msg = "Empty group name!";
	}

	if(trim(group) == ""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
}

// check if id is not empty
function validate_new_entry_form(lang){
    var msg;
    var id = document.forms['f_bibtex_entry'].elements['id'].value;

    if(lang == 'fr_FR'){
		msg = "Clé BibTeX vide! Vous devez définir une clé BibTeX!";
	}
	else if(lang == 'en_US'){
		msg = "Empty ID! You must define a BibTeX ID.";
	}

	if(trim(id) == ""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
}

function validate_new_bibtex_key(lang){
    var msg;
    var group = document.forms['new_bibtex_key'].elements['bibtex_key'].value;

    if(lang == 'fr_FR'){
		msg = "Clé BibTeX vide! Vous devez définir une clé BibTeX!";
	}
	else if(lang == 'en_US'){
		msg = "Empty ID! You must define a BibTeX ID.";
	}

	if(trim(group) == ""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
}


function validate_bibtex2aux_form(lang){
    var msg;
    var group = document.forms['bibtex2aux_form'].elements['aux_file'].value;

    if(lang == 'fr_FR'){
		msg = "Aucun fichier sélectionné!";
	}
	else if(lang == 'en_US'){
		msg = "No file selected!";
	}

	if(trim(group) == ""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
}

function validate_xpath_form(lang){
    var msg;
    var group = document.forms['xpath_form'].elements['xpath_query'].value;

    if(lang == 'fr_FR'){
		msg = "Requète XPath vide!";
	}
	else if(lang == 'en_US'){
		msg = "Empty XPath query!";
	}

	if(trim(group) == ""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
}

function validate_login_form(lang){
    var msg;
    var username = document.forms['login_form'].elements['login'].value;
    var pass = document.forms['login_form'].elements['mdp'].value;

    if(lang == 'fr_FR'){
		msg = "Utilisateur ou mot de passe vide!";
	}
	else if(lang == 'en_US'){
		msg = "Empty username or password!";
	}

	if(trim(username) == "" || trim(pass)==""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
}

// remove spaces at the beginnig and the end of a string
function trim(str)
{
    return str.replace(/^\s*|\s*$/g,"");
}


function display_browse(str)
{
    document.getElementById('years').style.display = (str != 'years' ? 'none' : 'block');
    document.getElementById('tab_years').className = (str != 'years' ? '' : 'active');
    document.getElementById('authors').style.display = (str != 'authors' ? 'none' : 'block');
    document.getElementById('tab_authors').className = (str != 'authors' ? '' : 'active');
    document.getElementById('series').style.display = (str != 'series' ? 'none' : 'block');
    document.getElementById('tab_series').className = (str != 'series' ? '' : 'active');
    document.getElementById('journals').style.display = (str != 'journals' ? 'none' : 'block');
    document.getElementById('tab_journals').className = (str != 'journals' ? '' : 'active');
    document.getElementById('groups').style.display = (str != 'groups' ? 'none' : 'block');
    document.getElementById('tab_groups').className = (str != 'groups' ? '' : 'active');
}

function toggleTabEdit(str)
{
    document.getElementById('required_ref').style.display = (str != 'required_ref' ? 'none' : 'block');
    document.getElementById('tab_required_ref').className = (str != 'required_ref' ? '' : 'active');
    document.getElementById('optional_ref').style.display = (str != 'optional_ref' ? 'none' : 'block');
    document.getElementById('tab_optional_ref').className = (str != 'optional_ref' ? '' : 'active');
    document.getElementById('additional_ref').style.display = (str != 'additional_ref' ? 'none' : 'block');
    document.getElementById('tab_additional_ref').className = (str != 'additional_ref' ? '' : 'active');
}



/**
 * Function to highlight a row of a table containing a checkbox
 */
function setRowColor(checkBox, styleClass)
{
    var theRow = document.getElementById(checkBox.value);
    if(theRow != null)
        theRow.className = checkBox.checked? styleClass:"none";
}
/*
Array.find = function(ary, element)
{
	for(var i=0; i<ary.length; i++)
    {
		if(ary[i] == element)
        {
			return i;
		}
	}
	return -1;
    }*/