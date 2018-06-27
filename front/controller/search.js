var focusId = "dep";
$row = $("tr");
var id;

function update_select(pid){
    focusId = pid;
    console.log(focusId);
}

function select(value) {
    id = "#" + focusId;
    $(id).val(value);

    $row.show();
    return;
}