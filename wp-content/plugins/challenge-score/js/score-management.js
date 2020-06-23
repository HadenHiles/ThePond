(function($) {

    $(document).ready(function() {
        var $addButton = $('#add-score');
        $addButton.click(function(e) {
            e.preventDefault();
            var challengeId = $('input#challenge-id').val();
            var userId = $('input#user-id').val();
            var score = parseFloat($('input#challenge-score').val());
            
            if(challengeId != null && userId != null && score != null) {
                var data = {
                    action: 'add_challenge_score',
                    challenge_id: challengeId,
                    user_id: userId,
                    score: score
                };
        
                $.ajax({
                    url: ajaxurl, // this will point to admin-ajax.php
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        console.log('Ajax done:', response);
                    }
                });
            }
        });
    });

})(jQuery);

// the promise object also has other methods:
// `done()` / `fail()` / `progress()` / `state()` / `abort()`