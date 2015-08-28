function afficher_cacher(id)
{
    if(document.getElementById(id).style.display=="none")
    {
        document.getElementById(id).style.display="table-footer-group";
    }
    else
    {
        document.getElementById(id).style.display="none";
    }
    return true;
}