function validate() {
    console.log("[login.js] - valdate");
    var form = document.getElementsByTagName("input");

    var email = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
    var pass = /(.*[A-Z0-9]+.*[a-z]+.*)|(.*[a-z]+.*[A-Z0-9]+)/;

    if (!form[1].value.replace(/\s/g, '').length && !form[0].value.replace(/\s/g, '').length) {
        form[1].style.borderColor = "red";
        form[1].style.borderRadius = "12px";
        form[0].style.borderColor = "red";
        form[0].style.borderRadius = "12px";
        return false;
    }

    if (!form[0].value.replace(/\s/g, '').length) {
        form[0].style.borderColor = "red";
        form[0].style.borderRadius = "12px";
        return false;
    }

    if (!form[1].value.replace(/\s/g, '').length) {
        console.log("cazzo32");
        form[1].style.borderColor = "red";
        form[1].style.borderRadius = "12px";
        return false;
    }

    console.log("[login-js] " + form[0].value + form[1].value);

    if (document.getElementById("notvalid") != null) //validate format error
        document.getElementById("notvalid").remove();
    if (document.getElementById("p-error") != null) // server error
        document.getElementById("p-error").remove();

    if (!email.test(form[0].value) || !pass.test(form[1].value)) {
        var node = document.createElement("p");
        node.setAttribute("id", "notvalid");
        node.innerHTML = "Invalid Email or Password formats. Password must contain at least a lower case letter and a digit or uppercase letter.";
        node.style.color = "red";
        document.getElementById("formid").appendChild(node);
        return false;
    }
    return true;
}
