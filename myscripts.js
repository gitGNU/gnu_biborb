function toggle_abstract(id)
{
        if(document.getElementById){
            var x = document.getElementById(id);
            if(x.style.display == 'none'){
                x.style.display = 'block';
            }
            else{
                x.style.display = 'none';
            }
        }
}

function toggle_element(id)
{
    if(document.getElementById){
        var x = document.getElementById(id);
        if(x.style.display == 'none'){
            x.style.display = 'block';
        }
        else{
            x.style.display = 'none';
        }
    }
}

// Add a group into the groups input field.
// Added if not already present.
function addGroup()
{
	var groups = document.fields._groups;
    var groupslist = document.fields.groupslist;
    var groupArray = groups.value.split(",");
    var addGroup = groupslist.options[groupslist.selectedIndex].value;
    
    found = false;
    for(i=0;i<groupArray.length && !found; i++){
        found = (groupArray[i] == addGroup);
    }
    if(!found){
        if(groups.value != ""){
            groups.value += ",";
        }
        groups.value += addGroup;
    }
}

// Change the database
function change_db(name){
    window.location="./bibindex.php?bibname="+name;
}

function change_lang(name){
    window.location="./bibindex.php?language="+name;
}
function change_lang_index(name){
    window.location="./index.php?language="+name;
}

////////////////////////////////////////////////////////////////////////////////
// check forms

// new bibliography creation
// check the name is not empty
function validate_bib_creation(lang){
	var msg;
	var name = document.forms['f_bib_creation'].elements['database_name'].value;

	if(lang == 'fr_FR'){
		msg = "Nom de bibliographie vide!";
	}
	else if(lang == 'en_US'){
		msg = "Empty bibliography name!";
	}

	if(trim(name) == ""){
		alert(msg);
		return false;
	}
    else{
        return true;
    }
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

//
function trim(str)
{
    return str.replace(/^\s*|\s*$/g,"");
}


