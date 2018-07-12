$(document).ready(function(){
	$('.disk-state').on('mouseenter', function(){
            $(this).stop().addClass('act');
            $(this).find('div').stop().fadeIn();
		}).on('mouseleave', function(){
            $(this).stop().removeClass('act');
            $(this).find('div').stop().fadeOut();
	})
})