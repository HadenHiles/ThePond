jQuery(document).ready(function () {
	if (jQuery('.course-container').length > 0) {
		const slider = document.querySelector('.course-container');
		let isDown = false;
		let startX;
		let scrollLeft;

		slider.addEventListener('mousedown', (e) => {
		  isDown = true;
		  slider.classList.add('active');
		  startX = e.pageX - slider.offsetLeft;
		  scrollLeft = slider.scrollLeft;
		});
		slider.addEventListener('mouseleave', () => {
		  isDown = false;
		  slider.classList.remove('active');
		});
		slider.addEventListener('mouseup', () => {
		  isDown = false;
		  slider.classList.remove('active');
		});
		slider.addEventListener('mousemove', (e) => {
		  if(!isDown) return;
		  e.preventDefault();
		  const x = e.pageX - slider.offsetLeft;
		  const walk = (x - startX) * 2; //scroll-fast
		  slider.scrollLeft = scrollLeft - walk;
		});

		// Set the minimum hight for the course container
		jQuery('.course-container').css('min-height', jQuery('.section-icons').css('height'));

		var scrollDistance = jQuery('.course-item').width() * 1.5;
		jQuery('#scroll-left-btn').click(function () {
			jQuery('.course-container').animate({
	        scrollLeft: slider.scrollLeft - scrollDistance
	    }, 300);
		});
		jQuery('#scroll-right-btn').click(function () {
			jQuery('.course-container').animate({
	        scrollLeft: slider.scrollLeft + scrollDistance
	    }, 300);
		});
	}

	//TODO: This is a hack - Move user rank to within profile info in community profile sidebar
	jQuery('.yzb-user-status').insertAfter(jQuery('.yzb-head-username'));
	if (jQuery('#user-rank').length > 0) {
		jQuery('#user-rank').prependTo(jQuery('.yzb-user-status'));
		jQuery('#user-rank').css({'opacity': '1'});
	}
	jQuery('.yzb-user-status').css({'opacity': '1'});

	jQuery('.topmemberNav .yz-primary-nav-settings' ).unbind('click');
	jQuery('.topmemberNav .yz-primary-nav-area').click(function(e) {
		e.preventDefault();
		window.location.href = jQuery(this).closest('a').attr('href');
	});


	//If images get 404 try using wp uploads folder instead of ThePondCDN
	jQuery('img').each(function() {
    if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
      // image was broken
			var newImgUrl = this.src;
			newImgUrl = newImgUrl.replace("https://cdn.thepond.howtohockey.com/", "https://thepond.howtohockey.com/wp-content/uploads/");
			this.src = newImgUrl;
    }
  });

	var imageURLs = jQuery('div');
	imageURLs.each(function(index, element) {
	    var imageURL = jQuery(element).css('background-image').replace('url("', '').replace('")', '');
	    if (imageURL != "none") {
	        jQuery.ajax({
	           url: imageURL,
	           type: 'HEAD',
						 beforeSend: function(jqXHR, settings) {
							 jqXHR.url = settings.url;
						 },
	           error: function(jqXHR, exception) {
							 	var imageUrl = jqXHR.url;
	              jQuery(element).css({'background-image': 'url("' + imageUrl.replace("https://cdn.thepond.howtohockey.com/", "https://thepond.howtohockey.com/wp-content/uploads/") + '")'});
	           }
	        });
	    }
	});
});
