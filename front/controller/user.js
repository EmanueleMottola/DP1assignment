$(document).ready(function (){

    var dep = document.getElementById("bookeddeperture").textContent;
    var arr = document.getElementById("bookedarrival").textContent;

    if (dep == null || arr == null) {
        return;
    }
    console.log("dep: " + dep);
    console.log("arr: " + arr);
    var depcol = document.getElementsByClassName("depredcolor");
    var arrcol = document.getElementsByClassName("arrredcolor");

    for (var i = 0; i < depcol.length; i++) {
        if (depcol[i].textContent == dep) {
            depcol[i].style.background = "red";
            break;
        }
    }

    for (var i = 0; i < arrcol.length; i++) {
        if (arrcol[i].textContent == arr) {
            arrcol[i].style.background = "red";
            break;
        }
    }
    return;
});