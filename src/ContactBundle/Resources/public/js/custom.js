$(document).ready(function() {
    $(function(){
        $("#contact_dateOfBirth").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: "dd.mm.yy",
            minDate: "-150Y",
            maxDate: 0
        });
    });

    $(document).ready(function() {
        $("#contact_zip").inputFilter(function(value) {
            return /^\d*$/.test(value);
        });
    });

});


(function($) {
    $.fn.inputFilter = function(inputFilter) {
        return this.on("input keydown keyup mousedown mouseup select contextmenu drop", function() {
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