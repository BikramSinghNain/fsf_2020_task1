(function ($) {

    $(function () {
        $.fn.displayMarks = function (data) {
            document.getElementById("display_marks").innerHTML = data;
        }

        $.fn.diplaySave = function (data) {
        
            document.getElementById('display_files').innerHTML = data;
        }
        
    });
    

})(jQuery);
