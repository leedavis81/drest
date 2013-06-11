
$('document').ready(function() {
	
	$("h5 a.show_hide").each(function(){
		$(this).click(function(){
			$(this).parent().next('pre').slideToggle('slow');
			return false;
		})
		$(this).parent().next('pre').css('display', 'none');
	});	
});
