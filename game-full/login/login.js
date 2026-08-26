var myimages=new Array()
function preloadimages(){
	for (i=0;i<preloadimages.arguments.length;i++){
	myimages[i]=new Image()
	myimages[i].src=preloadimages.arguments[i]
	}
}
$('#mainContainer').hide();
preloadimages(	"/BWClient2018/login/loginBtn-hover.png",
				"/BWClient2018/login/newPlayer-hover.png");

$(window).load(function() {
	//Things to do when page loads
	$('.loadingdv').hide()
	$('#mainContainer').show();
	$('.loginLoader').hide();	
	$('#userID').focus();
	
	$("img").mousedown(function(){
	    return false;
	});
	
	$('.userInput').bind('keypress blur', function (event) {
	    var keyCode = (!event.keyCode ? event.which : event.keyCode);
	    var charCode = (!event.charCode ? event.which : event.charCode);
	    if(charCode === 0){
	    	if(keyCode === 8 || keyCode === 9 || keyCode === 13 || keyCode === 95 || keyCode === 45 || keyCode === 32 || keyCode === 46 ||
					(keyCode >= 37 && keyCode <= 40)){
	    		return true;
	    	}
	    }else{
	    	if( 
	    			(charCode >= 97 && charCode <= 122) || 
	    			(charCode >= 65 && charCode <= 90) || 
	    			(charCode >= 48 && charCode <= 57) ||
	    			 charCode === 32 || charCode === 8 || charCode === 45 || charCode === 95){
	    		return true;
	    	}
	    }
		event.preventDefault();
		return false;	    
	});
	$('.userInput').bind('paste', function () {
		  var element = this;
		  setTimeout(function () {
		    var text = $(element).val();
		    var regex = new RegExp("^[ _a-zA-Z0-9.\-]+$");
		    var finalText = "";
		    for(var i =0; i < text.length; i++){
			    if (regex.test(text[i]) === true) {
			    	finalText += text[i];
			    }
		    }
		    $(element).val(finalText);
		  }, 6);
		});
	
	$('.passInput').bind('keypress blur', function (event) {
	    var keyCode = (!event.keyCode ? event.which : event.keyCode);
	    var charCode = (!event.charCode ? event.which : event.charCode);
	    if(charCode === 0){
	    	if(keyCode === 8 || keyCode === 9 || keyCode === 13 || keyCode === 95 || keyCode === 45 || keyCode === 32 || keyCode === 46 ||
					(keyCode >= 37 && keyCode <= 40)){
	    		return true;
	    	}
	    }else{
	    	if( (charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90) || (charCode >= 48 && charCode <= 57) 
	    			|| charCode === 32 || charCode === 8 || charCode === 46 || charCode === 64 || charCode === 45 || charCode === 95 || charCode === 13){
	    		return true;
	    	}
	    }
		event.preventDefault();
		return false;	    
	});	
	
	$('img.newPlayer').mouseover(function(){
		  var src = $(this).attr("src").match(/[^\-]+/) + "-hover.png";
		  $(this).attr("src", src);
	}).mouseout(function(){
		  var src = $(this).attr("src").match(/[^\-]+/) + "-up.png";
		  $(this).attr("src", src);
	});
	
	$('input.loginbtn').mouseover(function(){
		  var src = $(this).attr("src").match(/[^\-]+/) + "-hover.png";
		  $(this).attr("src", src);
	}).mouseout(function(){
		  var src = $(this).attr("src").match(/[^\-]+/) + "-up.png";
		  $(this).attr("src", src);
	}).click(function(){
		$('.loginBtn').hide();
		$('.loginLoader').show();		
		if(validate()){
			return true;
		}
		$('.loginBtn').show();
		$('.loginLoader').hide();	
		return false;
	});
	
	$('.rememberMeImg').click(function(){
		 if($("#rememberMe").val() == 0){
			 
			 $(".rememberMeImg").attr('src',"/BWClient2018/login/tick_box_tick.png");	
			 $("#rememberMe").attr('value', 1);
		 }else{
			 $(".rememberMeImg").attr('src',"/BWClient2018/login/tick_box_empty.png");
			 $("#rememberMe").attr('value', 0);
		 }
	});	
	
	$('img.loginImgs').mouseover(function(){
		  var src = $(this).attr("src").match(/[^\-]+/) + "-hover.png";
		  $(this).attr("src", src);
	}).mouseout(function(){
		  var src = $(this).attr("src").match(/[^\-]+/) + "-up.png";
		  $(this).attr("src", src);
	});	
});


function validate(){
	var msg = "";
	if($('#userID').val() == ""){
		msg += "Invalid Bin Weevil Name";
		$(".alertMsg").html(msg);
		return false;		
	}
	
	if($('#password').val() == ""){
		msg += "Invalid Password";
		$(".alertMsg").html(msg);
		return false;				
	}
	return true;
}