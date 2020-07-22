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
	
		// Option to allow full screen image on click
		$('img[data-enlargable]').addClass('img-enlargable').click(function(){
			var src = $(this).attr('src');
			var modal;
			
			function removeModal(){ 
				modal.remove(); 
				$('body').off('keyup.modal-close'); 
			}
			
			modal = $('<div class="img-enlargable">').css({
				background: 'RGBA(0,0,0,.75) url('+src+') no-repeat center',
				backgroundSize: 'initial',
				width:'100%', height:'100%',
				position:'fixed',
				zIndex:'10000',
				top:'0', left:'0',
				cursor: 'zoom-out'
			}).click(function(){
				removeModal();
			}).appendTo('body');
			
			//handling ESC
			$('body').on('keyup.modal-close', function(e){
			  if(e.key==='Escape'){ removeModal(); } 
			});
		});

		// Skills Vault
		var frequencyColumn = 2;
		var skillTypeColumn = 4;
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
			order: [frequencyColumn, 'desc']
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
				skillsVaultTable.column(skillTypeColumn).search($(this).text()).draw();	
				$(this).addClass('active');
			} else {
				$(this).removeClass('active');
				skillsVaultTable.columns().search('').draw();
			}

			var searchTerm = skillsVaultTable.columns(skillTypeColumn).search()[0];
			$('.dataTables_empty').text('No skills were found for "' + searchTerm + '"');
		});

		var $searchButton = $('.skillsVault #search-button');
		$searchButton.appendTo('.dataTables_filter label');
		$searchButton.click(function(e) {
			e.preventDefault();
			skillsVaultTable.search($('#skills-vault-table_filter input[type="search"]').val()).draw();
		});

		// Modal for skills vault
		if ($('#skillsVaultModal').length == 1) {
			$('a.action-button').click(function(e) {
				e.preventDefault();
				
				var button = $(this) // Button that triggered the modal
				var skill = button.data('skill'); // Extract info from data-* attributes
				var url = button.data('url');
				var video = button.data('video');
				// If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
				// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
				var modal = $('#skillsVaultModal');
				modal.find('.modal-title').text(skill);
				modal.find('.modal-body .videoWrapper').html(video);
				modal.find('a.action').attr('href', url);

				modal.modal({
					backdrop: true
				});
			});
		}
	});
})(jQuery);
