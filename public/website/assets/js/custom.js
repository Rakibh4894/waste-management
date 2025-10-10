$(document).ready(function() {

    $('.richText').richText();

    $(".time").timepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "h:i",
    });

    $(".date").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "yy-mm-dd",
    });

    $('.month').MonthPicker({ Button: false });
    $('.year').yearpicker(
        {
            onShow:null,
            onHide:null,
            onChange:function(value){}

        }
    )
});

