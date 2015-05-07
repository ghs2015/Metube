$(document).ready(function(){
        $('#loading_spinner').show();
        var name = 'name';
        var type = 'type';
        var ext = 'ext';
        var post_data = 'filename='+name+'&filetype='+type+'&fileExt='+ext;
        $.ajax({url: "convert.php", type: 'POST', data: post_data, success: function(result){
            $("#div1").html(result);
        }});
});
