function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 

function initializeLinks(){	
     
     $("#reviews_td_tab").click(function() {
             $("#desc_a_tab a, #desc_a_tab").removeClass("tabActive");
             $("#reviews_td_tab, #reviews_td_tab a").addClass('tabActive');
             $("#faqData").hide();
             $("#faqData2").fadeIn("1000");
             return false;
      });
     
     $("#desc_a_tab").click(function() {
             $("#reviews_td_tab a, #reviews_td_tab").removeClass("tabActive");
             $("#desc_a_tab, #desc_a_tab a").addClass('tabActive');
             $("#faqData2").hide();
             $("#faqData").fadeIn("1000");
             return false;
      });
}

function validateForm(){
     $("form#form_id input[name='email'], form#form_id input[name='name'], form#form_id textarea[name='message'], form#form_id select[name='subject']").css({"background" : "white"});
     if ($("form#form_id input[name='name']").val().length < 2 ){
          showDialog('300', '180', '<b>Please enter your name</b>',
		       function(){$("form#form_id input[name='name']").css({"background" : "pink"}); closeInMs(3000, "#background, #dialogWindow"); return false;}
            );
          return false;
     } else if( !validateEmail( $("form#form_id input[name='email']").val() ) ){
                    showDialog('320', '180', '<b>Please enter correct email address</b>',
		       function(){$("form#form_id input[name='email']").css({"background" : "pink"}); closeInMs(3000, "#background, #dialogWindow"); return false;}
            );
          return false;
     } else if($("form#form_id select[name='subject'] option:selected").val() == ""){
                    showDialog('300', '180', '<b>Please select subject</b>',
		       function(){$("form#form_id select[name='subject']").css({"background" : "pink"}); closeInMs(3000, "#background, #dialogWindow"); return false;}
            );
          return false;
     } else if($("form#form_id textarea[name='message']").val().length < 5){
                    showDialog('300', '180', '<b>Please complete your message</b>',
		       function(){$("form#form_id textarea[name='message']").css({"background" : "pink"}); closeInMs(3000, "#background, #dialogWindow"); return false;}
            );
          return false;
     }
     return true;
}
   
$(document).ready(function() {
     $("option[value='']").attr({'disabled': 'true'});
     $("div.fieldDiv button.submit").click(validateForm);
	
	$.ajax({
            type: "POST",
            url: "/blocks_custom/contact-categories.php",
            success: function(data) {
                jQuery('#mainContactContainer').html(data);
		//alert("Data Loaded");
		initializeLinks();
		doAccordion();
		$("#reviews_td_tab a, #reviews_td_tab").removeClass("tabActive");
		$("#desc_a_tab, #desc_a_tab a").addClass('tabActive');
		$("#faqData2").hide();
                $("#faqData").fadeIn("1000");
            },
	    dataType:"html",
	    contentType: "text/html; charset=iso-8859-1"
        });
	
		 
	$("#formLink").click(function() {
		$("#leftContactMenu a").not("#formLink").removeClass("active");
		$(this).addClass('active');
	 	$("#mainContactContainer, .call, .write").hide();
		$(".contact-form").fadeIn("1000");
		return false;
	 });

	$("#faqLink").click(function() {
		$("#leftContactMenu a").not("#faqLink").removeClass("active");
		$(this).addClass('active');
		$(".call, .contact-form, .write").hide();
		$("#mainContactContainer").fadeIn("1000");
		return false;
	 });
	
	$("#phoneLink").click(function() {
		$("#leftContactMenu a").not("#phoneLink").removeClass("active");
		$(this).addClass('active');
		$(".contact-form, #mainContactContainer, .write").hide();
		$(".call").fadeIn("1000");
		return false;
	 });
        
	$("#writeLink").click(function() {
		$("#leftContactMenu a").not("#writeLink").removeClass("active");
		$(this).addClass('active');
		$(".contact-form, #mainContactContainer, .call").hide();
		$(".write").fadeIn("1000");
		return false;
	 });
});
