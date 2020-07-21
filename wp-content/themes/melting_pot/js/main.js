(function($) {
	$(document).ready(function() {
		if ($('.course-container').length > 0) {
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
			$('.course-container').css('min-height', $('.section-icons').css('height'));
	
			var scrollDistance = $('.course-item').width() * 1.5;
			$('#scroll-left-btn').click(function () {
				$('.course-container').animate({
				scrollLeft: slider.scrollLeft - scrollDistance
			}, 300);
			});
			$('#scroll-right-btn').click(function () {
				$('.course-container').animate({
				scrollLeft: slider.scrollLeft + scrollDistance
			}, 300);
			});
		}
	
		//TODO: This is a hack - Move user rank to within profile info in community profile sidebar
		$('.yzb-user-status').insertAfter($('.yzb-head-username'));
		if ($('#user-rank').length > 0) {
			$('#user-rank').prependTo($('.yzb-user-status'));
			$('#user-rank').css({'opacity': '1'});
		}
		$('.yzb-user-status').css({'opacity': '1'});
	
		$('.topmemberNav .yz-primary-nav-settings' ).unbind('click');
		$('.topmemberNav .yz-primary-nav-area').click(function(e) {
			e.preventDefault();
			window.location.href = $(this).closest('a').attr('href');
		});

		// Skills Vault
		var skillsVaultTable = $('#skills-vault-table').DataTable({
			paging: false,
			searching: true,
			// responsive: {
			// 	details: {
			// 		display: $.fn.dataTable.Responsive.display.childRowImmediate,
			// 		type: 'column',
			// 		target: 'tr'
			// 	},
			// 	columns: [
			// 		{ responsivePriority: 1 },
			// 		{ responsivePriority: 2 },
			// 		{ responsivePriority: 3 },
			// 		{ responsivePriority: 4 }
			// 	]
			// },
			scrollX: true,
			autoFill: false,
			info: false,
			fixedHeader: false,
			paging: true,
			pageLength: 50,
			lengthChange: false,
			language: {
				searchPlaceholder: "Find a Skill",
				emptyTable: "There are no skills in the skills vault yet.",
				zeroRecords: "No skills were found."
			},
			oLanguage: {
				sSearch: ""
			},
			order: [1, 'desc']
		});

		$('#skills-vault-table_filter input[type="search"]').on('keyup', function() {
			$('#skill-types-filter a.nav-link.active').removeClass('active');
			skillsVaultTable.columns().search('').draw();
			skillsVaultTable.search($(this).val());
		});

		$('#skill-types-filter').on('click', 'li a.nav-link', function(e) {
			e.preventDefault();
			if (!$(this).hasClass('active')) {
				$('#skill-types-filter a.nav-link.active').removeClass('active');
				skillsVaultTable.column(3).search($(this).text()).draw();	
				$(this).addClass('active');
			} else {
				$(this).removeClass('active');
				skillsVaultTable.columns().search('').draw();
			}

			var searchTerm = skillsVaultTable.columns(3).search()[0];
			$('.dataTables_empty').text('No skills were found for "' + searchTerm + '"');
		});

		var $searchButton = $('.skillsVault #search-button');
		$searchButton.appendTo('.dataTables_filter label');
		$searchButton.click(function(e) {
			e.preventDefault();
			skillsVaultTable.search($('#skills-vault-table_filter input[type="search"]').val()).draw();
		});
	});
})(jQuery);
