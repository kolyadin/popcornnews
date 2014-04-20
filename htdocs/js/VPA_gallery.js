function vpa_gal_remove_file(obj,id)
{
	if(!id)id='gal_images';
    var tb=document.getElementById(id).getElementsByTagName('tbody')[0];
    var tr=obj.parentNode.parentNode;
    if (tb.rows[0]!=tr)
    {
        tb.removeChild(tr);
    }
}

function vpa_gal_add_file(id)
{
	if(!id)id='gal_images';
    var tb=document.getElementById(id);
    if (tb.rows.length>=11) return false;
    var cl=tb.rows[0];
    var ncl=cl.cloneNode(true);
    var lst=tb.rows[tb.rows.length-1];
    //lst.cells[1].className='display_on';
    tb.getElementsByTagName('tbody')[0].appendChild(ncl);
    tb.rows[tb.rows.length-1].getElementsByTagName('input')[0].value='';
}

