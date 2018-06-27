function validate_book(){
    var dep = document.getElementById("dep").value;
    var arr = document.getElementById("arr").value;

    console.log("dep: " + dep + "arr:" + arr)

    if(dep == "" && arr == ""){
        document.getElementById("dep").style.borderColor = "red";
        document.getElementById("arr").style.borderColor = "red";
        document.getElementById("dep").style.borderRadius = "12px";
        document.getElementById("arr").style.borderRadius = "12px";
        return false;
    }

    if(dep == ""){
        document.getElementById("dep").style.borderColor = "red";
        document.getElementById("dep").style.borderRadius = "12px";
        return false;
    }

    if(dep == ""){
        document.getElementById("arr").style.borderColor = "red";
        document.getElementById("arr").style.borderRadius = "12px";
        return false;
    }

    var value =  (dep > arr ? false : (arr > dep ? true : false));

    if(!value){
        console.log("non in ordine");
        alert("Departure and arrival are not in alphabetic order.");
        return false;
    }
    else
        return true;
        
}