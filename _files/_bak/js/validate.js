// form Validation Code

function validate(form){
	document.getElementById("namewarn").style.visibility = "hidden";
	document.getElementById("mailwarn").style.visibility = "hidden";
	document.getElementById("commentwarn").style.visibility = "hidden";

	
	if(document.getElementById("sname").value == ""){
	document.getElementById("namewarn").style.visibility = "visible";
	return false;
	}
	
	if(document.getElementById("email").value == ""){
	document.getElementById("mailwarn").style.visibility = "visible";
	return false;
	}	
	
	if(document.getElementById("comments").value == ""){
	document.getElementById("commentwarn").style.visibility = "visible";
	return false;
	}		
	
	return true;
}