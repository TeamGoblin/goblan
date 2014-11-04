/* Create a case-insensitive contain search for the quick filter */
jQuery.expr[":"].icontains = jQuery.expr.createPseudo(function (arg){
	return function (elem) {
		return jQuery(elem).text().toUpperCase().indexOf(arg.toUpperCase()) >= 0;
    };
});

/* Only allow numeric input on fields */
jQuery.fn.ForceNumericOnly =
function()
{
    return this.each(function()
    {
        $(this).keydown(function(e)
        {
            var key = e.charCode || e.keyCode || 0;
            // allow backspace, tab, delete, arrows, numbers and keypad numbers ONLY
            // home, end, period, and numpad decimal
            return (
                key == 8 ||
                key == 9 ||
                key == 46 ||
                key == 110 ||
                key == 190 ||
                (key >= 35 && key <= 40) ||
                (key >= 48 && key <= 57) ||
                (key >= 96 && key <= 105));
        });
    });
};

/* Create a quick filter box for searching courses when selling */
function setupQuickFilter()
{
	var $containers = $('#course-box');
	$containers.each(function(i) {

		var $filterField = $($(this).find('.filterContent')[0]);
		var element = $filterField.attr('data-element');
		var $filterContent = $('#' + $filterField.attr('name')).find(element); // the name attribute of the filter field is the div we filter
		$filterField.unbind('keyup');
		$filterField.keyup( function() {
			var srchform = $filterField.val();
			if (srchform === '') {
				$filterContent.show();
				return;
			}
			$filterContent.hide();
			$filterContent.find(':icontains("' + srchform + '")').each(function() {
				$(this).parent(element).show();
			});
		});
	});
}

/* Create a custom combo box for searching a select menu */
$.widget( "custom.combobox", {
	_create: function() {
		this.wrapper = $( "<span>" )
			.addClass( "" )
			.insertAfter( this.element );

		this.element.hide();
		this._createAutocomplete();
	},

	_createAutocomplete: function() {
		var selected = this.element.children( ":selected" ),
		value = selected.val() ? selected.text() : "";

		this.input = $( "<input>" )
			.appendTo( this.wrapper )
			.val( value )
			.attr( "title", "")
			.attr( "placeholder", "school")
			.addClass( "width textbox" )
			.autocomplete({
				delay: 0,
				minLength: 0,
				source: $.proxy( this, "_source" )
			});

		this._on( this.input, {
			autocompleteselect: function( event, ui ) {
				ui.item.option.selected = true;
				this._trigger( "select", event, {
					item: ui.item.option
				});
				// Set hidden id to chosen school's ID
				$('#schoolID').val(ui.item.id);
			},

			autocompletechange: "_removeIfInvalid"
		});
	},

	_source: function( request, response ) {
		var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
		response( this.element.children( "option" ).map(function() {
			var text = $( this ).text();
			var val = $(this).val();
			if ( this.value && ( !request.term || matcher.test(text) ) )
				return {
					label: text,
					value: text,
					id: val,
					option: this
				};
			})
		);
	},

	_removeIfInvalid: function( event, ui ) {

		// Selected an item, nothing to do
		if ( ui.item ) {
			return;
		}

		// Search for a match (case-insensitive)
		var value = this.input.val(),
		valueLowerCase = value.toLowerCase(),
		valid = false;
		this.element.children( "option" ).each(function() {
			if ( $( this ).text().toLowerCase() === valueLowerCase ) {
				this.selected = valid = true;
				return false;
			}
		});

		// Found a match, nothing to do
		if ( valid ) {
			return;
		}

		// Remove invalid value
		this.input.val( "" );
		this.element.val( "" );
		this.input.data( "ui-autocomplete" ).term = "";

		// Show no results span

	},

	_destroy: function() {
		this.wrapper.remove();
		this.element.show();
	}
});

function showError($error) {
	$('#errors').html('<div id="pageAlert" class="alert alert-block alert-error fade in">'
	+'<a class="close" href="#" data-dismiss="alert" style="text-decoration: none;">&times;</a>'
	+'<h4 class="alert-heading">Oh no!  Something went wrong:</h4>'
	+$error+'</div>');
}

function buySearch() {
	var $time = $('#time').val();

	var $course = $('#course').val();
	if ($course === "") {
		$course = $('#course2').val();
	}
	if ($course === "") {
		$course = $('#course3').val();
	}

	var $professor = $('#professor').val();
	if ($professor === "") {
		$professor = $('#professor2').val();
	}
	if ($professor === "") {
		$professor = $('#professor3').val();
	}

	var $school = $('#school').val();
	if ($school === "") {
		$school = $('#school2').val();
	}
	if ($school === "") {
		$school = $('#school3').val();
	}

	var $data = 'course='+$course+'&professor='+$professor+'&time='+$time+'&school='+$school;

	$.ajax({
		type:'POST',
		url: '/api/buy',
		data: $data,
		success: function(response) {
			if (response.pass) {
				/* Use the response data to re-create the output table */
				$output = '';
				$('#buying').empty();
				if (response.notes) {
					$.each(response.notes, function(index, item) {
						$output += "<div class='col-lg-2 col-6'>";
						$output += '<a href="/notes/view/'+item.id+'">';
						$output += "<div class='card-form2'>";
						$output += '<img src="/'+item.thumbnail+'" width="100%"/>';
						$output += '<div class="note-course">'+item.subject+' '+item.number+'</div>';
						$output += '<div class="note-professor">'+item.lname+', '+item.fname+'</div>';
						$output += '<div class="note-year">'+item.term+' '+item.year+'</div>';
						$output += '<div class="note-title">'+item.title+'</div>';
						if (item.currency == 'USD') {
							$output += '<div class="note-cost">$'+item.cost+'</div>';
						} else {
							$output += '<div class="note-cost">'+item.cost+item.currency+'</div>';
						}
						$output += '<div class="note-author">'+item.alias+'</div>';
						$output += "</div>";
						$output += "</a>";
						$output += "</div>";
					});
					if ($output !== '') {
						$('#buying').append($output);
					}
				}
			}
		},
		dataType:'json'
	});
}

function buyFilter($field, $data) {
	$.ajax({
		type:'POST',
		url: '/api/filter',
		data: $data,
		success: function(response) {
			if (response.pass) {
				/* Use the response data to re-create the output table */
				$output = '';
				$($field).empty();
				if (response.data) {
					$output += '<option value=""></option>';
					$.each(response.data, function(index, item) {
						console.log(item);
						$output += '<option value="'+item.id+'">'+item.title+'</option>';
					});
					if ($output !== '') {
						$($field).append($output);
					}
				}
			}
		},
		dataType:'json'
	});
}

$(document).ready(function() {

	var lastStep = 0;

	/* enable filter on sell courses search */
	setupQuickFilter();

	/* Hide all spinners by default */
	$('#schoolspin').hide();
	$('#aliasok').hide();
	$('#emailok').hide();
	$('#aliasbad').hide();
	$('#emailbad').hide();

	/* Login/Register/Forgot functions */
	var hash = window.location.hash.substring(1);
	if (hash == 'forgot') {
		$('#login').hide();
	} else {
		$('#forgot').hide();
	}

	$('#loginLink, #loginLink2').on('click', null, function(e) {
		var $login = $('#login');
		var $forgot = $('#forgot');

		$forgot.hide();
		$login.show();
	});

	$('#forgotLink').on('click', null, function(e) {
		var $login = $('#login');
		var $forgot = $('#forgot');

		$forgot.show();
		$login.hide();
	});

	$('#registerButton-1').on('click', null, function(e) {
		// Check to see if there are errors on alias or email if there are don't submit
		if ($('#aliasError').val() != 0 || $('#emailError').val() != 0 || !$('#terms').prop('checked')){
			if ($('#aliasError').val() != 0) $error = 'Alias is invalid';
			if ($('#emailError').val() != 0) $error = 'Email is invalid';
			if (!$('#terms').prop('checked')) $error = 'Please agree to terms and conditions!';
			showError($error);
		}
		// Else submit the form
		else {
			$('.formContainer').animate({
				marginLeft: "-100%"
			}, 500 );
		}
	});

	$('#registerButton-2').on('click', null, function(e) {
		if ($('#schoolID').val() != 0) {
			$('#registerForm').submit();
		} else {
			$error = 'No school selected!';
			showError($error);
		}
	});

	$('#registerButton-3').on('click', null, function(e) {
		if ($('#newschool').val() != '') {
			$('#registerForm').submit();
		} else {
			$error = 'No school entered!';
			showError($error);
		}
	});

	$('#register .back').on('click', null, function(e) {
		$('.formContainer').animate({
			marginLeft: "0%"
		}, 500 );
	});

	$('#register .back2').on('click', null, function(e) {
		$('.formContainer').animate({
			marginLeft: "-100%"
		}, 500 );
	});


	/* Sell functions */
	$('#sellButton-1').on('click', null, function(e) {
		// Check to see if there are errors on alias or email if there are don't submit
		if ($('#title').val() == '' || $('#description').val() == '' || $('#cost').val() == '' || $('#filer').val() == '' || (parseFloat($('#cost').val()) < 2.50)) {
			if ($('#title').val() == '') $error = 'Title is invalid';
			if ($('#description').val() == '') $error = 'Description is invalid';
			if ($('#cost').val() == '') $error = 'Cost is invalid';
			if (parseFloat($('#cost').val()) < 2.50) $error = 'Cost must be >= $2.50';
			if ($('#filer').val() == '') $error = 'File is invalid';
			showError($error);
		} else {
			lastStep = 1;
			$('.formContainer').animate({
				marginLeft: "-100%"
			}, 500 );
		}
	});

	$('#sellButton-2').on('click', null, function(e) {
		// Check to see if there are errors on courseID
		if (!$("input[name='courseID']:checked").val()) {
			$error = 'Course is invalid';
			showError($error);
		} else {
			lastStep = 2;
			$('.formContainer').animate({
				marginLeft: '-200%'
			}, 500 );
		}
	});

	$('#sellButton-3').on('click', null, function(e) {
		// Check to see if there are errors on professor
		if ($('#professor').val() == '') {
			$error = 'Professor is invalid';
			showError($error);
		} else {
			$('#sellForm').submit();
		}
	});

	$('#sellButton-4').on('click', null, function(e) {
		// Check to see if there are errors on selections
		if ($('#professor2').val() == '') {
			$error = 'Professor is invalid';
			showError($error);
		} else if ($('#subject').val() == '') {
			$error = 'Subject is invalid';
			showError($error);
		} else if ($('#number').val() == '') {
			$error = 'Number is invalid';
			showError($error);
		} else {
			$('#sellForm').submit();
		}
	});

	$('#sellButton-5').on('click', null, function(e) {
		if ($('#fname').val() == '') {
			$error = 'First Name is invalid';
			showError($error);
		} else if ($('#lname').val() == '') {
			$error = 'Last Name is invalid';
			showError($error);
		} else {
			$('#sellForm').submit();
		}
	});

	$('#notFound').on('click', null, function(e) {
		lastStep = 2;
		$('.formContainer').animate({
				marginLeft: "-300%"
		}, 500 );
	});

	$('#notFound2').on('click', null, function(e) {
		// Check to verify required fields filled in before moving
		if ($('#subject').val() == '') {
			$error = 'Subject is invalid';
			showError($error);
		} else if ($('#number').val() == '') {
			$error = 'Number is invalid';
			showError($error);
		} else {
			lastStep = 4;
			$('.formContainer').animate({
					marginLeft: "-400%"
			}, 500 );
		}
	});

	$('#notFound3').on('click', null, function(e) {
		lastStep = 3;
		$('.formContainer').animate({
				marginLeft: "-400%"
		}, 500 );
	});


	$('#sell .back').on('click', null, function(e) {
		$('.formContainer').animate({
			marginLeft: "0%"
		}, 500 );
	});

	$('#sell .back2').on('click', null, function(e) {
		$('.formContainer').animate({
			marginLeft: "-100%"
		}, 500 );
	});

	$('#sell .back3').on('click', null, function(e) {
		$('.formContainer').animate({
			marginLeft: "-100%"
		}, 500 );
	});

	$('#sell .back4').on('click', null, function(e) {
		if (lastStep == 3) {
			$('.formContainer').animate({
				marginLeft: "-200%"
			}, 500 );
		} else {
			$('.formContainer').animate({
				marginLeft: "-300%"
			}, 500 );
		}
	});


	/* Edit Function */
	$('#editButton').on('click', null, function(e) {
		// Check to see if there are errors on alias or email if there are don't submit
		if ($('#title').val() == '' || $('#description').val() == '' || $('#cost').val() == '' || (parseFloat($('#cost').val()) < 2.50)) {
			if ($('#title').val() == '') $error = 'Title is invalid';
			if ($('#description').val() == '') $error = 'Description is invalid';
			if ($('#cost').val() == '') $error = 'Cost is invalid';
			if (parseFloat($('#cost').val()) < 2.50) $error = 'Cost must be >= $2.50';
			showError($error);
		} else {
			$('#editForm').submit();
		}
	});

	/* Delete Confirmation Function */
	$('#deleteButton').on('click', null, function(e) {
		$('#deleteForm').submit();
	});

	/* Warning Link */
	$('#warning-link').on('click', null, function(e) {
		$('.form_base').height('100%');
		//$('.form_base').css('line-height', '25px');
	});


	/* verify alias while user is typing */
	$('#alias').typing({
		start: function (event, $elem) {
			$('#aliasok').hide();
			$('#aliasbad').hide();
		},
		stop: function (event, $elem) {
			// Post alias to verify its unique
			$.ajax({
				url: "/api/user-unique",
				type:'POST',
				data: {
					name: $elem.val()
				},
				dataType:'json',
				success: function( response ) {
					$alias = /^[A-Z0-9\_]+?$/i;
					if (response[0].pass != 'false' && $alias.test($elem.val())) {
						$elem.css('border', '1px solid green');
						$('#aliasok').show();
						$('#aliasError').val(0);
					} else {
						$elem.css('border', '1px solid red');
						$('#aliasbad').show();
						$('#aliasError').val(1);
					}
				}
			});
		},
		delay: 500
	});

	/* run email verification while user is tying */
	$('#registerEmail').typing({
		start: function (event, $elem) {
			$('#emailok').hide();
			$('#emailbad').hide();
		},
		stop: function (event, $elem) {
			// Post email to verify its unique
			$.ajax({
				url: "/api/email-unique",
				type:'POST',
				data: {
					email: $elem.val()
				},
				dataType:'json',
				success: function( response ) {
					$email = /^[A-Z0-9\.\_\-\+]+?@[A-Z0-9\.\-]+?\.[A-Z0-9\.]+?$/i;
					if (response[0].pass != 'false' && $email.test($elem.val())) {
						$elem.css('border', '1px solid green');
						$('#emailok').show();
						$('#emailError').val(0);
					} else {
						$elem.css('border', '1px solid red');
						$('#emailbad').show();
						$('#emailError').val(1);
					}
				}
			});
		},
		delay: 500
	});

	/* Make the register school dropdown a combo search box */
	$( "#register #school" ).combobox();

	/* Change the way the file input box looks */
	var wrapper = $('<div/>').css({height:0,width:0,'overflow':'hidden'});
	var fileInput = $('#filer').wrap(wrapper);
	var extensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png', 'html', 'ppt'];
	fileInput.change(function(){
		$this = $(this);
		if ($this.val() != '') {
			var fileExt = $this.val().split('.').pop();
			fileExt = fileExt.toLowerCase();
			if ($.inArray(fileExt, extensions) != -1) {
				$('#file').text($this.val());
			}
		}
	});

	$('#file').click(function(){
		fileInput.click();
	}).show();

	/* Change the way the preview input box looks */
	var wrapper2 = $('<div/>').css({height:0,width:0,'overflow':'hidden'});
	var fileInput2 = $('#preview').wrap(wrapper2);
	var extensions2 = ['jpg', 'png'];
	fileInput2.change(function(){
		$this = $(this);
		if ($this.val() != '') {
			var fileExt = $this.val().split('.').pop();
			fileExt = fileExt.toLowerCase();
			if ($.inArray(fileExt, extensions2) != -1) {
				$('#file2').text($this.val());
			}
		}
	});

	$('#file2').click(function(){
		fileInput2.click();
	}).show();

	/* only allow numbers input to cost field */
	$("#cost").ForceNumericOnly();

	/* When changing the school on sell, update courses and professors */
	$('#sell #school').on('change', function() {
		// reset schools shown on next tab
		$('#courseTable').empty();
		var $data = 'school='+$('#sell #school').val();
		$.ajax({
			type:'POST',
			url: '/api/school-courses',
			data: $data,
			success: function(response) {
				if (response.pass) {
					$.each(response.courses, function(index, item) {
						var $append = '<li><table><tr>';
						$append += '<td rowspan=2 class="rb"><input type="radio" name="courseID" value="'+item.id+'"></td>';
						$append += '<td>'+item.subject+' '+item.number+'</td>';
						$append += '</tr>';
						$append += '<tr>';
						$append += '<td>'+item.professor+'</td>';
						$append += '</tr></table></li>';
						$('#courseTable').append($append);
					});
					/* enable filter on sell courses search */
					setupQuickFilter();
				} else {

				}
			},
			dataType:'json'
		});

		// reset professors shown on professors dropdown
		$('#professor').empty();
		var $data = 'school='+$('#sell #school').val();
		$.ajax({
			type:'POST',
			url: '/api/school-professors',
			data: $data,
			success: function(response) {
				if (response.pass) {
					var $append = '<option value="">Professor Name</option>';
					$('#professor').append($append);
					$.each(response.professors, function(index, item) {
						$append = '<option value="'+item.id+'">'+item.lname+', '+item.fname+'</option>';
						$('#professor').append($append);
					});
				}
			},
			dataType:'json'
		});
	});

	// Hide all fields by default
	$('#buy-filter .course, #buy-filter .professor, #buy-filter .school').hide();
	$('#buy-filter .second, #buy-filter .third').hide();

	$('#buy-filter #first').on('change', function(){

		$("#course option:selected").removeAttr("selected");
		$("#professor option:selected").removeAttr("selected");
		$("#school option:selected").removeAttr("selected");

		$("#course2 option:selected").removeAttr("selected");
		$("#professor2 option:selected").removeAttr("selected");
		$("#school2 option:selected").removeAttr("selected");

		$("#course3 option:selected").removeAttr("selected");
		$("#professor3 option:selected").removeAttr("selected");
		$("#school3 option:selected").removeAttr("selected");

		$('#buy-filter .course, #buy-filter .professor, #buy-filter .school').hide();
		$('#second').empty();
		$('#second').append($("<option></option>").attr("value",'').text(''));
		$('#third').empty();
		$('.third').hide();

	 	$first = $('#first').val();
		if ($first !== '') {
			switch ($first) {
				case 'School':
					$('#school').show(); 
					$('#second').append($("<option></option>").attr("value",'Course').text('Course'));
					$('#second').append($("<option></option>").attr("value",'Professor').text('Professor'));
					break;
				case 'Course':
					$('#course').show();
					$('#second').append($("<option></option>").attr("value",'School').text('School'));
					$('#second').append($("<option></option>").attr("value",'Professor').text('Professor'));
					break;
				case 'Professor':
					$('#professor').show();
					$('#second').append($("<option></option>").attr("value",'School').text('School'));
					$('#second').append($("<option></option>").attr("value",'Course').text('Course'));
					break;
			}
			$('.second').show();
		} else {
			$('#buy-filter .course, #buy-filter .professor, #buy-filter .school').hide();
			$('#buy-filter .second, #buy-filter .third').hide();

			// Clear the filters off
			buySearch();
		}
	});

	$('#buy-filter #second').on('change', function(){

		$("#course2 option:selected").removeAttr("selected");
		$("#professor2 option:selected").removeAttr("selected");
		$("#school2 option:selected").removeAttr("selected");

		$("#course3 option:selected").removeAttr("selected");
		$("#professor3 option:selected").removeAttr("selected");
		$("#school3 option:selected").removeAttr("selected");

		$('#buy-filter #course2, #buy-filter #professor2, #buy-filter #school2').hide();
		$('#third').empty();
		$('#third').append($("<option></option>").attr("value",'').text(''));
		
		var $course = '';
		var $prof = '';
		var $school = '';
		var $toshow2 = '';

		$second = $('#second').val();
		if ($second !== '') {
			switch ($second) {
				case 'School':
					if ($first == 'Professor'){
						$('#third').append($("<option></option>").attr("value",'Course').text('Course'));
						$prof = $('#professor').val();
					}
					else {
						$('#third').append($("<option></option>").attr("value",'Professor').text('Professor'));
						$course = $('#course').val();
					}
					$toshow2 = '#school2';
					break;
				case 'Course':
					if ($first == 'Professor'){
						$('#third').append($("<option></option>").attr("value",'School').text('School'));
						$prof = $('#professor').val();
					}
					else {
						$('#third').append($("<option></option>").attr("value",'Professor').text('Professor'));
						$school = $('#school').val();
					}
					$toshow2 = '#course2';
					break;
				case 'Professor':
					if ($first == 'Course'){
						$('#third').append($("<option></option>").attr("value",'School').text('School'));
						$course = $('#course').val();
					}
					else {
						$('#third').append($("<option></option>").attr("value",'Course').text('Course'));
						$school = $('#school').val();
					}
					$toshow2 = '#professor2';
					break;
			}
			// Ping API and pass values from #first to get list for #second
			$data = 'course='+$course+'&professor='+$prof+'&school='+$school+'&field='+$second;
			buyFilter($toshow2, $data);
			$($toshow2).show(); // Show the proper selected filter field
			$('.third').show();
		} else {
			$('#buy-filter #course2, #buy-filter #professor2, #buy-filter #school2').hide();
			$('#buy-filter .third').hide();
			buySearch();
		}
	});

	$('#buy-filter #third').on('change', function(){
		
		$("#course3 option:selected").removeAttr("selected");
		$("#professor3 option:selected").removeAttr("selected");
		$("#school3 option:selected").removeAttr("selected");

		$('#buy-filter #course3, #buy-filter #professor3, #buy-filter #school3').hide();

		var $course = '';
		var $prof = '';
		var $school = '';
		var $toshow3 = '';

		$third = $('#third').val();
		if ($third !== '') {
			switch ($third) {
				case 'School':
					$course = $('#course').val();
					if ($course === "") {
						$course = $('#course2').val();
					}
					
					$professor = $('#professor').val();
					if ($professor === "") {
						$professor = $('#professor2').val();
					}
					$toshow3 = '#school3';
					break;
				case 'Course':
					$school = $('#school').val();
					if ($school === "") {
						$school = $('#school2').val();
					}
					$professor = $('#professor').val();
					if ($professor === "") {
						$professor = $('#professor2').val();
					}
					$toshow3 = '#course3';
					break;
				case 'Professor':
					$school = $('#school').val();
					if ($school === "") {
						$school = $('#school2').val();
					}
					$course = $('#course').val();
					if ($course === "") {
						$course = $('#course2').val();
					}
					$toshow3 = '#professor3';
					break;
			}
			// Ping API and pass values from #first to get list for #second
			$data = 'course='+$course+'&professor='+$prof+'&school='+$school+'&field='+$third;
			buyFilter($toshow3, $data);
			$($toshow3).show(); // Show the proper selected filter field
		} else {
			$('#buy-filter #course3, #buy-filter #professor3, #buy-filter #school3').hide();
			buySearch();
		}
	});

	$('#buy-filter #course, #buy-filter #professor, #buy-filter #school').on('change', function(){
		// Remove the second set of filters
		$("#course2 option:selected").removeAttr("selected");
		$("#professor2 option:selected").removeAttr("selected");
		$("#school2 option:selected").removeAttr("selected");
		$("#second option:selected").removeAttr("selected");

		$("#course3 option:selected").removeAttr("selected");
		$("#professor3 option:selected").removeAttr("selected");
		$("#school3 option:selected").removeAttr("selected");
		$("#third option:selected").removeAttr("selected");
	});

	$('#buy-filter #course2, #buy-filter #professor2, #buy-filter #school2').on('change', function(){
		// Remove the third set of filters
		$("#course3 option:selected").removeAttr("selected");
		$("#professor3 option:selected").removeAttr("selected");
		$("#school3 option:selected").removeAttr("selected");
		$("#third option:selected").removeAttr("selected");
	});

	// On change, run the filter
	$('#buy-filter #course, #buy-filter #professor, #buy-filter #time, #buy-filter #school, #course2, #course3, #professor2, #professor3, #school2, #school3').on('change', buySearch);

	/* Autocomplete course subjects */
	var $subjects = ['Accounting','Agriculture','Anthropology','Architecture','Art','Astronomy','Biology','Business','Business Management','Business Technology','CAD','Chemistry','Computer Information Systems','Computer Science','Computer Engineering','Communications','Creative Writing','Criminal Justice','Digital Media','Economics','Education','Engineering','English','Environmental Science','Film','Finance','Foreign Language','Geography','Geographical Information Systems','Graphic Design','History','Humanities','Journalism','Management','Marketing','Math','Multimedia','Music','Natural Science','Nursing','Occupational Therapy','Oceanography','Optometry','Painting','Philosophy','Photography','Physical Education','Physical Therapy','Physics','Political Science','Project Management','Psychology','Radiology','Religion','Sociology','Software Engineering','Speech','Theatre','Visual Communications','Web Development'];
	$("#subject" ).autocomplete({
		source: $subjects
	});

	var bar = $('.bar');
	var percent = $('.percent');

	$('#sellForm').ajaxForm({
		dataType: 'json',
		beforeSend: function() {
			var percentVal = '0%';
			bar.width(percentVal);
			percent.html(percentVal);
			$('#sell').hide();
			$('.progress').show();
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete + '%';
			bar.width(percentVal);
			percent.html(percentVal);
		},
		success: function() {
			var percentVal = '100%';
			bar.width(percentVal);
			percent.html(percentVal);
		},
		complete: function(response, status, xhr) {
			/* Check to see if the PHP verification failed */
			var x = $.parseJSON(response.responseText);
			/* If PHP failed, show the error and the form */
			if (x.success != 'true') {
				var $error = x.message;
				showError($error);
				var percentVal = '0%';
				bar.width(percentVal);
				percent.html(percentVal);
				$('#sell').show();
				$('.progress').hide();
			} else {
				/* If PHP passed, show the newly created note page */
				window.location = '/notes/view/'+x.noteID;
			}
		}
	});

	$('#editForm').ajaxForm({
		dataType: 'json',
		beforeSend: function() {
			var percentVal = '0%';
			bar.width(percentVal);
			percent.html(percentVal);
			$('#sell').hide();
			$('.progress').show();
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete + '%';
			bar.width(percentVal);
			percent.html(percentVal);
		},
		success: function() {
			var percentVal = '100%';
			bar.width(percentVal);
			percent.html(percentVal);
		},
		complete: function(response, status, xhr) {
			/* Check to see if the PHP verification failed */
			var x = $.parseJSON(response.responseText);
			/* If PHP failed, show the error and the form */
			if (x.success != 'true') {
				var $error = x.message;
				showError($error);
				var percentVal = '0%';
				bar.width(percentVal);
				percent.html(percentVal);
				$('#sell').show();
				$('.progress').hide();
			} else {
				/* If PHP passed, show the newly created note page */
				window.location = '/notes/view/'+x.noteID;
			}
		}
	});

	$('#deleteForm').ajaxForm({
		dataType: 'json',
		beforeSend: function() {
			var percentVal = '0%';
			bar.width(percentVal);
			percent.html(percentVal);
			$('#sell').hide();
			$('.progress').show();
		},
		uploadProgress: function(event, position, total, percentComplete) {
			var percentVal = percentComplete + '%';
			bar.width(percentVal);
			percent.html(percentVal);
		},
		success: function() {
			var percentVal = '100%';
			bar.width(percentVal);
			percent.html(percentVal);
		},
		complete: function(response, status, xhr) {
			/* Check to see if the PHP verification failed */
			var x = $.parseJSON(response.responseText);
			/* If PHP failed, show the error and the form */
			if (x.success != 'true') {
				var $error = x.message;
				showError($error);
				var percentVal = '0%';
				bar.width(percentVal);
				percent.html(percentVal);
				$('#sell').show();
				$('.progress').hide();
			} else {
				/* If PHP passed, show the newly created note page */
				window.location = '/';
			}
		}
	});

	$('.progress').hide();

	$('input, textarea').placeholder();

	// Bind the RATE IT stars to send AJAX to backend
	$('.rateit').bind('rated reset', function (e) {
		var ri = $(this);

		// if the user pressed reset, it will get value: 0 (to be compatible with the HTML range control), 
		// we could check if e.type == 'reset', and then set the value to null
		var value = ri.rateit('value'); // rating value
		var noteID = $('#userrating').attr('data-noteid'); // note id
		var userID = $('#userrating').attr('data-userid'); // user id
		console.log(noteID);

		// disable voting after
		//ri.rateit('readonly', true);

		$.ajax({
			url: '/notes/rate', //your server side script
			data: { id: noteID, value: value }, //our data
			type: 'POST',
			dataType: 'JSON',
			success: function (data) {
				//$('#response').append('<li>' + data + '</li>');

			},
			error: function (jxhr, msg, err) {
				//$('#response').append('<li style="color:red">' + msg + '</li>');
			}
		});
	});

	$("[rel=tooltip]").tooltip();

});
