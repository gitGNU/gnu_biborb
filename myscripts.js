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
