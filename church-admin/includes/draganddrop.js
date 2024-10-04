jQuery(function( $) {

    // preventing page from redirecting
    $("html").on("dragover", function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $(".drag-message").text("Drag here");
    });

    $("html").on("drop", function(e) { e.preventDefault(); e.stopPropagation(); });

    // Drag enter
    $('.ca-upload-area').on('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var id=$(this).data('id');
        $(".drag-message"+id).text("Drop");
    });

    // Drag over
    $('.ca-upload-area').on('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
        var id=$(this).data('id');
        $(".drag-message"+id).text("Drop");
    });

    // Drop
    $('.ca-upload-area').on('drop', function (e) {
        e.stopPropagation();
        e.preventDefault();
        //for mp3
        console.log('Dropped');
        $(".ca-mp3-box").html('Added');


        var whichImage=$(this).data('which');
        var id=$(this).data('id');
        var nonce=$(this).data('nonce');
        console.log('WHICH '+whichImage);
        console.log("id " + id);
        $("#drag-message"+id).text("Uploading");
        var image = e.originalEvent.dataTransfer.files;
        console.log(image)
        var fd = new FormData();
        fd.append("action", "church_admin");
        fd.append("method", whichImage+'-upload');
        fd.append("nonce", nonce);
        if(id) fd.append("id", id);
        fd.append("nonce",nonce);
        fd.append("userImage", image[0] );
        
        uploadData(fd);
    });

    // Open file selector on div click
    $("#uploadfile").click(function()  {
        $("#file").click();
    });

    // file selected
    $("#file").change(function()  {
        var fd = new FormData();

        var files = $('#file')[0].files[0];

        fd.append('file',files);
        
        uploadData(fd);
    });


// Sending AJAX request and upload file
function uploadData(formData)  {
    console.log(formData)

    $.ajax({
        url: ajaxurl,
        type: 'post',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(response)  {
            console.log(response);
            $("#audio_url").val(response.src);
            $("#"+response.div).attr('src',response.src);
            $("#"+response.div).attr('srcset','');
            $("#"+response.id).val(response.attachment_id);
            $("#drag-message"+response.people_id).text("Uploaded");
            $("#attachment_id"+response.people_id).val(response.attachment_id);
        }
    });
}

// Added thumbnail
function addThumbnail(data)  {
    $(".drag-message").remove(); 
    var len = $("#uploadfile div.thumbnail").length;

    var num = Number(len);
    num = num + 1;

    var name = data.name;
    var size = convertSize(data.size);
    var src = data.src;

    // Creating an thumbnail
    $("#uploadfile").append('<div id="thumbnail_'+num+'" class="thumbnail"></div>');
    $("#thumbnail_"+num).append('<img src="'+src+'" width="100%" height="78%">');
    $("#thumbnail_"+num).append('<span class="size">'+size+'<span>');

}

// Bytes conversion
function convertSize(size) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (size == 0) return '0 Byte';
    var i = parseInt(Math.floor(Math.log(size) / Math.log(1024) ));
    return Math.round(size / Math.pow(1024, i), 2) + ' ' + sizes[i];
}
    
    });