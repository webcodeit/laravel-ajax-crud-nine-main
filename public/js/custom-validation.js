valid_file_message = "Please upload valid file. gif / jpeg / jpg / png.";
// valid_file_accept='.gif|.GIF|.jpg|.png|.JPG|.PNG|.JPEG|.jpeg';
valid_file_accept = 'image/*';

$(document).ready(function ($) {
	$.validator.addClassRules({
		validImage: {
			accept: valid_file_accept
		},
	});

	/*$.extend($.validator.messages, {
		required: "This field is required.",
		remote: "Please fix this field.",
		email: "Please enter a valid email address.",
		url: "Please enter a valid URL.",
		date: "Please enter a valid date.",
		dateISO: "Please enter a valid date (ISO).",
		number: "Please enter a valid number.",
		digits: "Please enter only digits.",
		creditcard: "Please enter a valid credit card number.",
		equalTo: "Please enter the same value again.",
		accept: "Please enter a value with a valid file.",
		maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
		minlength: jQuery.validator.format("Please enter at least {0} characters."),
		rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
		range: jQuery.validator.format("Please enter a value between {0} and {1}."),
		max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
		min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
	});*/
});

$(".form-validatin").validate({
	rules: {
		newpassword: {
			// required: true,
			minlength: 5,
		},
		confirmpassword: {
			// required: true,
			minlength: 5,
			equalTo: '#newpassword'
		},
		oldpassword: {
			// required: true,
			minlength: 5,
		},

	}
	,
	messages: {
		oldpassword: {
			equalTo: "Please enter correct old password."
		}, confirmpassword: {
			equalTo: "Password and confirm password should be same."
		}

	}
});

$('.form-validation').each(function () {
	var form_id = $(this).attr('id');
	$(`#${form_id}`).validate({
		ignore: [],
		errorPlacement: function (error, element) {
			return false;
		},
	});
});