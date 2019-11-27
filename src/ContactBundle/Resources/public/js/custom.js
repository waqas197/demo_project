$(document).ready(function () {





    $(function () {
        $("#contact_dateOfBirth").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd.mm.yy",
            minDate: "-150Y",
            maxDate: 0
        });
    });

    $("#contact_zip").inputFilter(function (value) {
        return /^\d*$/.test(value);
    });

    $("#contactName").keyup( function() {
        searchContact();
    });

    $("#contactAddress").keyup( function() {
        searchContact();
    });

    $("#contactEmail").keyup( function() {
        searchContact();
    });

});


(function ($) {
    $.fn.inputFilter = function (inputFilter) {
        return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function () {
            if (inputFilter(this.value)) {
                this.oldValue = this.value;
                this.oldSelectionStart = this.selectionStart;
                this.oldSelectionEnd = this.selectionEnd;
            } else if (this.hasOwnProperty("oldValue")) {
                this.value = this.oldValue;
                this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
            } else {
                this.value = "";
            }
        });
    };
}(jQuery));

function searchContact() {
    var contactName = $("#contactName").val();
    var contactAddress = $("#contactAddress").val();
    var contactEmail = $("#contactEmail").val();

    $.ajax({
        url: "/api/contact/search",
        type: 'GET',
        data: {
            'contactName' : contactName,
            'contactAddress' : contactAddress,
            'contactEmail' : contactEmail
        },
        success: function (result) {
            $("#searchResults").html(result);
        }
    });
}

function deleteContact(id) {
    var result = confirm("Are you sure you want to delete this record?");
    if (result) {
        $.ajax({
            url: "/api/contact/delete/" + id,
            type: 'DELETE',
            success: function (result) {
                $('#' + id).hide("slow");
                new PNotify({
                    title: 'Success',
                    text: result,
                    type: 'success'
                });
            },
            error:
                function (result) {
                    new PNotify({
                        title: 'Error',
                        text: result.responseJSON,
                        type: 'error'
                    });
                }
        });
    }
}

