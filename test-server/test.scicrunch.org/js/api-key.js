$(function(){
    $(".update-text").click(function(){
        $(".key-row").each(function(){
            var key_val = $(this).find(".key-val").html();
            var project_name = $(this).find(".project-name").val();
            var description = $(this).find(".key-description").val();
            $.ajax({
                type: "POST",
                url: "/api/1/key/update",
                data: JSON.stringify({keyval: key_val, project_name: project_name, description: description}),
                headers: {"Content-type": "application/json"}
            });
        });
    });
});
