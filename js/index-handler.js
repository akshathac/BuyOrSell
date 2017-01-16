/**
 * Created by akshatha on 12/11/2016.
 */
    $(document).ready(function(){   <!--Begin of ready-->
        //Onchange of file uploaded in POST modal
        $(document).on("change","#uploadbutton",function(){
            var m_data = new FormData();
            m_data.append("file_upload",$('input[id=uploadbutton]')[0].files[0]);
            $.ajax({
                url: 'php/upload.php',
                data: m_data,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'json',
                complete: function(response) {
                    response = JSON.parse(response.responseText);
                    $("#uploaded_file").attr("src",decodeURIComponent(response.result_link));
                    $("#hidden_uploaded_file").val(decodeURIComponent(response.result_link));
                    $("#uploaded_file").css("display","block");
                }
            });

        }); <!-- End of upload-->

        //onclick of category
        $(document).on("click",".search_by_category",function(){
        var category_choosed = $(this).attr("data-name");
            $.ajax({
                url:'index.php',
                data: {action:"search_by_category",category_name:category_choosed},
                dataType:'json',
                complete:function(response){
                    var output = JSON.parse(response.responseText);
            if(output.success){
                    var item ="";
                    output.result.forEach(function(entry){
                       item += " <div class='item  col-xs-4 col-lg-4'>" +
                           " <div class='thumbnail'> <img class='group list-group-image' src='"+decodeURIComponent(entry.image_path)+"' alt='' />"+
                            "<div class='caption'>"+
                            "<h4 class='group inner list-group-item-heading'>"+
                            entry.title+"</h4>"+
                        "<p class='group inner list-group-item-text'>"+
                           entry.description+"</p>"+
                        "<div class='row'>"+
                           " <div class='col-xs-12 col-md-6'>"+
                            "<p class='lead'>"+
                           "$"+entry.cost+"</p>"+
                        "</div>"+
                        "<div class='col-xs-12 col-md-6'>"+
                            "<a class='btn btn-success' id='add_to_cart' product_id = '"+entry.product_id+"'>Add to cart</a>"+
                        "</div> </div> </div> </div> </div>";
                    });
                if(item == ""){
                    item="<div class='alert alert-info'> <strong>Info!</strong> No posting for this category. </div>";
                }
                    $("#products").html(item);
                    $("#DisplayItem").modal('show');
                    }
            else{
                window.location.replace("login.html");
            }
                }
            });
        });  <!-- End of category search-->
        $('#list').click(function(event){event.preventDefault();$('#products .item').addClass('list-group-item');}); //List view toggle
        $('#grid').click(function(event){event.preventDefault();$('#products .item').removeClass('list-group-item');$('#products .item').addClass('grid-group-item');});//Grid view toggle

        $(document).on("click","#activity",function(){
            $.ajax({
                url: 'index.php',
                data: {action: "user_activity"},
                dataType: 'json',
                complete: function (response) {
                    var output = JSON.parse(response.responseText);
                    if (output.success) {
                        var item = update_activity_view(output);

                        if (item == "") {
                            item = "<div class='alert alert-info'> <strong>Info!</strong> No items to show </div>";
                        }
                        $("#products").html(item);
                    } else{
                        window.location.replace("login.html");
                    }
                    }

            });
        });
        //Add to cart
        $(document).on("click","#add_to_cart",function(){
            var product_id = $(this).attr('product_id');
            $.ajax({
                url: 'index.php',
                data: {action: "add_to_cart", product_id: product_id},
                dataType: 'json',
                complete: function (response) {
                    var output = JSON.parse(response.responseText);
                    if (output.success == 'false')
                    {
                        window.location.replace("login.html");
                    }
                }
            });
        });
        $(document).on("click","#remove_from_cart",function(){
            var product_id = $(this).attr('product_id');
            $.ajax({
                url: 'index.php',
                data: {action: "remove_from_cart", product_id: product_id},
                dataType: 'json',
                complete: function (response) {
                    var output = JSON.parse(response.responseText);
                    if (output.success) {
                    var item = update_cart_view(output);
                    if (item == "") {
                        item = "<div class='alert alert-info'> <strong>Info!</strong> No items to show </div>";
                    }
                    $("#products").html(item);
                }else{
                    window.location.replace("login.html");
        }
                }
            });
        });
        $(document).on("click","#remove_from_posting",function(){
            var product_id = $(this).attr('product_id');
            $.ajax({
                url: 'index.php',
                data: {action: "remove_from_sellinglist", product_id: product_id},
                dataType: 'json',
                complete: function (response) {
                    var output = JSON.parse(response.responseText);
                    if (output.success) {
                    var item = update_activity_view(output);
                    if (item == "") {
                        item = "<div class='alert alert-info'> <strong>Info!</strong> No items to show </div>";
                    }
                    $("#products").html(item);
                    }else{
                        window.location.replace("login.html");
                    }
                }
            });
        });
        //Add to cart
        $(document).on("click","#cart_items",function(){
            $.ajax({
                url: 'index.php',
                data: {action: "get_cart_items"},
                dataType: 'json',
                complete: function (response) {
                    var output = JSON.parse(response.responseText);
                    console.log(output.success);
                    if (output.success) {
                        var item = update_cart_view(output);
                        if (item == "") {
                            item = "<div class='alert alert-info'> <strong>Info!</strong> No items to show </div>";
                        }
                        $("#products").html(item);
                        $("#DisplayItem").modal('show');
                    }else{
                        window.location.replace("login.html");
                    }
                }
            });
        });
        //Logout action
        $(document).on('click',"#log_out_action",function(){
            $.ajax({
                url: 'index.php',
                data: {action: "clear_the_session"}
            });
        });
        $(document).on('focusout',"#search",function(){
                var category_choosed = $(this).val();
                $.ajax({
                    url:'index.php',
                    data: {action:"search_by_value",product_name:category_choosed},
                    dataType:'json',
                    complete:function(response){
                        var output = JSON.parse(response.responseText);
                        if(output.success){
                            var item ="";
                            output.result.forEach(function(entry){
                                item += " <div class='item  col-xs-4 col-lg-4'>" +
                                    " <div class='thumbnail'> <img class='group list-group-image' src='"+decodeURIComponent(entry.image_path)+"' alt='' />"+
                                    "<div class='caption'>"+
                                    "<h4 class='group inner list-group-item-heading'>"+
                                    entry.title+"</h4>"+
                                    "<p class='group inner list-group-item-text'>"+
                                    entry.description+"</p>"+
                                    "<div class='row'>"+
                                    " <div class='col-xs-12 col-md-6'>"+
                                    "<p class='lead'>"+
                                    "$"+entry.cost+"</p>"+
                                    "</div>"+
                                    "<div class='col-xs-12 col-md-6'>"+
                                    "<a class='btn btn-success' id='add_to_cart' product_id = '"+entry.product_id+"'>Add to cart</a>"+
                                    "</div> </div> </div> </div> </div>";
                            });
                            if(item == ""){
                                item="<div class='alert alert-info'> <strong>Info!</strong> No posting for this category. </div>";
                            }
                            $("#products").html(item);
                            $("#DisplayItem").modal('show');
                        }
                        else{
                            window.location.replace("login.html");
                        }
                    }
                });
        });
    }); <!--End of ready-->
function update_cart_view(output){
    var item ="";
    output.result.forEach(function (entry) {
        item += " <div class='item  col-xs-4 col-lg-4'>" +
            " <div class='thumbnail'> <img class='group list-group-image' src='" + decodeURIComponent(entry.image_path) + "' alt='' />" +
            "<div class='caption'>" +
            "<h4 class='group inner list-group-item-heading'>" +
            entry.title + "</h4>" +
            "<p class='group inner list-group-item-text'>" +
            entry.description + "</p>" +
            "<div class='row'>" +
            " <div class='col-xs-12 col-md-6'>" +
            "<p class='lead'>" +
            "$"+entry.cost+"</p>" +
            "</div>" +
            "<div class='col-xs-12 col-md-6'>"+
            "<a class='btn btn-success' id='remove_from_cart' product_id ='"+entry.product_id+"'>Remove</a>"+
            "</div> </div> </div> </div> </div>";
    });
    return item;
}
function update_activity_view(output){
    var item ="";
    output.result.forEach(function (entry) {
        item += " <div class='item  col-xs-4 col-lg-4'>" +
            " <div class='thumbnail'> <img class='group list-group-image' src='" + decodeURIComponent(entry.image_path) + "' alt='' />" +
            "<div class='caption'>" +
            "<h4 class='group inner list-group-item-heading'>" +
            entry.title + "</h4>" +
            "<p class='group inner list-group-item-text'>" +
            entry.description + "</p>" +
            "<div class='row'>" +
            " <div class='col-xs-12 col-md-6'>" +
            "<p class='lead'>" +
            "$"+entry.cost+"</p>" +
            "</div>" +
            "<div class='col-xs-12 col-md-6'>"+
            "<a class='btn btn-success' id='remove_from_posting' product_id = '"+entry.product_id+"'>Remove</a>"+
            "</div> </div> </div> </div> </div>";
    });
    return item;
}