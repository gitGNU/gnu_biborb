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
