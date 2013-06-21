
$('document').ready(function() {
	
	$("a.show_hide").each(function(){
		$(this).click(function(){
			$(this).siblings('pre').slideToggle('slow');
			return false;
		})
        $(this).siblings('pre').css('display', 'none');
	});	
});
