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

function addGroup()
{
	var groups = document.fields._groups;
	var groupslist = document.fields.groupslist;
	if(groups.value != ''){
		groups.value += ',';
	}
	groups.value += groupslist.options[groupslist.selectedIndex].value;
	

}
