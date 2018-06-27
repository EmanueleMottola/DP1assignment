
function highlight_error() {
    var input = document.getElementById("error");
    if (input == null)
        console.log("error è null");
    input.style.borderColor = "red";
    console.log("error è red");
}

function highlight_error_p() {
    var p = document.getElementById("p-error");
    if (p == null)
        console.log("p-error è null");
    p.style.color = 'red';
    console.log("p-error è red");
}

if (document.getElementById("error") != null)
    highlight_error();

if (document.getElementById("p-error") != null)
    highlight_error_p();