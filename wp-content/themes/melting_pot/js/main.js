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
});
