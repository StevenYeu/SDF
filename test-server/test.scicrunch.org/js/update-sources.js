$(document).ready(function(){
    var endpoint = '/forms/edit-sources.php'
    var $toggleButton = $('.update-sources.toggle-description-encoded');
    $toggleButton.click(function(){
        var el = $(this);
        var $encodeStatus = el.siblings('.update-sources.status-description-encoded');
        var data = el.data();
        el.text("[...]");
        data.value = !data.value;
        var serialized = $.param(data);
        $.post(endpoint, data)
            .done(function(data){
                var res = JSON.parse(data).description_encoded;
                el.data("value", res);
                $encodeStatus.text(res ? "Yes" : "No");
            })
            .error(function(){
                el.text("[Retry]");
            })
            .always(function(){
                el.text("[Toggle]");
            });
    })
})
