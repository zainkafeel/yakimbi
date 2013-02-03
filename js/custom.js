// JavaScript Document
$(document).ready(function() {
  // Handler for .ready() called.
			var dataString = '';
			$.ajax({
			type: "POST",
			url: "api.php?action=fav_list",
			data: dataString,
			dataType: "json",
				success: function(data) {
					$('#fav_image').html(data.display);
        			$(".ajax").colorbox();
				}
			});
});
//Color Box Resize Loader Width 
		function colorbox_resize(){
			$.colorbox.resize({innerWidth:$('.fav-view img').width()});
		}
		function add_desc(unqid){
			$('#'+unqid).html('<textarea name="addcomment" placeholder="write description" raws"5" style="width:99.5%" id="addcomment"></textarea><input type="submit" value="save" onclick="javascript:save_comment(\''+unqid+'\')">');
			$.colorbox.resize();
		}
		function remove_desc(unqid){
			var dataString = 'favid='+$('#'+unqid).attr('rel');
			$.ajax({
			type: "POST",
			url: "api.php?action=removedesc",
			data: dataString,
			dataType: "json",
				success: function(data) {
					$('#'+unqid).html('<a href="javascript:add_desc(\''+unqid+'\');">Add Description</a>');
					$.colorbox.resize();
				}
			});	
			
		}
		function save_comment(unqid){
		var dataString = 'favid='+$('#'+unqid).attr('rel')+'&desc='+$('#addcomment').val();
			$.ajax({
			type: "POST",
			url: "api.php?action=savedesc",
			data: dataString,
			dataType: "json",
				success: function(data) {
						$('#'+unqid).html($('#addcomment').val());
						$('#'+unqid).append('<div id="removedesc"><a href="javascript:remove_desc(\''+unqid+'\');">Remove Description</a></div>');
					    $.colorbox.resize();
				}
			});
			
		}
		function removefav(divid, rawid){
			$('.'+divid).remove();
			var dataString = 'id='+rawid;
			$.ajax({
			type: "POST",
			url: "api.php?action=remove_fav",
			data: dataString,
			dataType: "json",
				success: function(data) {
					$('#total').html(data.count);
				}
			});
		}

		function addtofav(url,tag, unid,farmid,serverid,imgid,secret){
		var $counter = $('#fav_image');
		var posX     = $counter.offset().left;
		var posY     = $counter.offset().top;
		var currentX = $('#'+unid+'_img').offset().left;
		var currentY = $('#'+unid+'_img').offset().top;
		itemObject = $('#'+unid+'_img'); // Code has proper selector to choose appropriate item
		newItemObject = itemObject.clone().appendTo('body');
		var dataString = 'url='+url+'&tag='+tag+'&farmid='+farmid+'&serverid='+serverid+'&imgid='+imgid+'&secret='+secret;		    
    	$(newItemObject).css({position:'absolute',left:currentX,top:currentY}).animate({
        left:posX,
        top:posY,
        width:0,
        height:0
      },1000,function(){
       $(newItemObject).remove();
	   //$('#'+unid+'_img').removeAttr("style");
    }); 
		$.ajax({
        type: "POST",
        url: "api.php?action=add2fav",
        data: dataString,
        dataType: "json",
        	success: function(data) {
			    
				if(data.process == 'sucess'){
					$counter.html(data.display);
				}else{
					$counter.html($counter.html);
					//$('#'+unid).html("This image already added into your favorite");
				}
				$(".ajax").colorbox();
				//$('#'+unid).html(data.process);
				//$('#'+unid).html("This image added sucessfully into your favorite");
        	}
        });
		}