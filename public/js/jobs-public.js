(function( $ ) {
	'use strict';

	$(function() {

		const container = $('#jobs-page');
		let team_select = $('#team-select'),
				location_select = $('#location-select'),
				team_wrap = $('.team-wrap'),
				$no_result = $('.no-result'),
				$no_post = $('#post-404');

		// Push URL parameters
		function insertParam(key, value) {
			const urlParams = new URLSearchParams(window.location.search);
			if (value) {
				urlParams.set(key, value);
				window.history.replaceState( {} , document.title, '?' + urlParams );
			} else {
				urlParams.delete(key);
				window.history.replaceState( {} , document.title, '?' + urlParams );
				let url = window.location.href;
				if (url.substring(url.length-1) == "?") {
		        url = url.substring(0, url.length-1);
						window.history.replaceState( {} , document.title, url );
		    }
			}
		}

		// Check if filters activated
		if(team_select.val() !== '0') {
			const team_val = team_select.find('option').filter(':selected').val();
			teamSelect(team_val);
		}
		if(location_select.val() !== '0') {
			const location_val = location_select.find('option').filter(':selected').val();
			locationSelect(location_val);
		}

		// Dependencies filter Location
		function dependenciesLocation($element) {
			let collection_loc_table = [],
					collection_loc_table_full = [],
					collection_loc_select = [],
					get_val_col = $element.find('.location-col span'),
					get_val_select = location_select.find('option');
			if($element.length == 0) {
				get_val_col = team_wrap.find('.location-col span');
			}

			get_val_col.each(function() {
				let id = $(this).data('value');
				if(jQuery.inArray(id, collection_loc_table) == -1) {
					collection_loc_table.push(id)
				}
				collection_loc_table_full.push(id);
			});
			get_val_select.each(function() {
				let id = $(this).val().replace(/"/g, '');
				if(jQuery.inArray(id, collection_loc_select) == -1) {
					collection_loc_select.push(+id);
				}
			});
			let difference = $(collection_loc_select).not(collection_loc_table).get();
			difference = difference.filter(item => item !== 0);
			get_val_select.show();
			$.each( difference, function( key, value ) {
				location_select.find('option[value="'+ value +'"]').hide();
			});

			// Recount Jobs inside the select
			let counts = {}
			for (let i = 0; i < collection_loc_table_full.length; i++) {
				if (counts[collection_loc_table_full[i]]) {
					counts[collection_loc_table_full[i]] += 1
				} else {
					counts[collection_loc_table_full[i]] = 1
				}
			}
			// Rebuild the select
			jQuery.each(collection_loc_table_full, function(index, value){
				let get_temp_select = location_select.find('option[value='+ value +']');
				let get_name_select = get_temp_select.data('name');
				get_temp_select.text(get_name_select + ' ('+ counts[value] +')');
			});

		}

		// Dependencies filter Team
		function dependenciesTeam() {
			let collection_team_table = [],
					collection_team_table_full = [],
					collection_team_select = [],
					get_val_col = $('.job-wrap').not('.hidden').find('.team-col'),
					get_val_select = team_select.find('option');

		  get_val_col.each(function() {
				let id = $(this).data('value');
				if(jQuery.inArray(id, collection_team_table) == -1) {
					collection_team_table.push(id);
				}
				collection_team_table_full.push(id);
		  });
			get_val_select.each(function() {
				let id = $(this).val().replace(/"/g, '');
				if(jQuery.inArray(id, collection_team_select) == -1) {
					collection_team_select.push(+id);
				}
		  });
			let difference = $(collection_team_select).not(collection_team_table).get();
			difference = difference.filter(item => item !== 0);
			get_val_select.show();
			$.each( difference, function( key, value ) {
				team_select.find('option[value="'+ value +'"]').hide();
			});

			// Recount Jobs inside the select
			let counts = {}
			for (let i = 0; i < collection_team_table_full.length; i++) {
				if (counts[collection_team_table_full[i]]) {
					counts[collection_team_table_full[i]] += 1
				} else {
					counts[collection_team_table_full[i]] = 1
				}
			}

			// Rebuild the select
			jQuery.each(collection_team_table_full, function(index, value){
				let get_temp_select = team_select.find('option[value='+ value +']');
				let get_name_select = get_temp_select.data('name');
				get_temp_select.text(get_name_select + ' ('+ counts[value] +')');
			});
		}

		// Team Filter
		function teamSelect(val) {
			let select_val = val;
			let result = container.find('#team-'+ select_val);

			if(select_val == '0') {
				team_wrap.removeClass('hidden team-hidden');
				location_select.find('option').show();
				updateTeamTable();
				insertParam('team', null);
			} else {
				let name_dept = team_select.find('option:selected').data('name').toLowerCase();
				team_wrap.not(result).addClass('hidden team-hidden');
				result.removeClass('hidden team-hidden');
				insertParam('team', name_dept);
			}

			dependenciesLocation(result);
		}
		team_select.on('change', function (el){
			teamSelect(this.value);
			hideErrorMsg();
		});

		// Location Filter
		function locationSelect(el) {
			const select_val = el;
			let wrappers = $('.job-wrap');
			let result = container.find('.location-col [data-value="'+ select_val +'"]');
			let wrap_result = result.parents('.job-wrap');
			wrappers.not(wrap_result).addClass('hidden');
			wrap_result.removeClass('hidden');
			if(select_val == '0') {
				wrappers.removeClass('hidden');
				team_select.find('option').show();
				insertParam('location', null);
			} else {
				let name_loc = location_select.find('option:selected').data('name').toLowerCase();
				insertParam('location', name_loc);
			}
			dependenciesTeam();
			updateTeamTable();
		}
		location_select.on('change', function (){
			locationSelect(this.value);
			hideErrorMsg();
		});

		// Reset button
		$('.filters').on('click', 'a', function(){
			team_select.val('0').change();
			location_select.val('0').change();
			hideErrorMsg();
			($no_post) ? insertParam('404', null) : '';
		});

		// Hide empty result message
		function hideErrorMsg() {
			if($no_result){
				$no_result.fadeOut(500, function() { $(this).remove(); });
			}
		}

		// Check team table after change filters
		function updateTeamTable() {
			team_wrap.each(function(){
				let $this = $(this);
				if($this.find('.job-wrap.hidden').length === $this.find('.job-wrap').length){
					$this.addClass('hidden');
				} else if (!$this.hasClass('team-hidden')) {
					$this.removeClass('hidden');
				}
			});
		}

	});

})( jQuery );
