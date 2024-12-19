<?php
	
	/* Note ::  
		Investor as Buyer
	 	Borrower as Seller
	*/

return [
	'PER_PAGE' => 20,
	'PER_PAGE_ADMIN' => 15,
	'PREFERRED_CONTACT_METHOD' => ['Email', 'Phone'],
	'PREFERRED_DASHBOARD' => ['Borrower', 'Investor'],
	'PREFERRED_DASHBOARD_Arr' => [ 'Borrower' => 'Seller', 'Investor' => 'Buyer'],
	'ACCOUNT_TYPE' => ['Individual', 'Enterprise'], 
	'ACCOUNT_TYPE_ENTERPRISE' => 'Enterprise', 
	// 'TYPE_OF_DOCUMENT' => ['Cheque', 'Invoice', 'Contract', 'Other'],
	'TYPE_OF_DOCUMENT' => ['Check' => 'Cheque', 'Invoice' => 'Invoice', 'Contract' => 'Contract', 'Other' => 'Other'],
	// 'PREFERRED_MODE' => ['Cash', 'eWallet', 'Bank Tran.'],
	'PREFERRED_MODE' => ['Cash' => 'Cash', 'eWallet' => 'eWallet', 'Bank Transfer' => 'Bank Tran.'],
	'IS_GOVERMENT_DOCUMENT' => ['Yes', 'No'],
	'RESPONSIBILITY' => ['With', 'Without'],
	'TYPE_OF_COMPANY' => ['SA', 'LLP', 'PVT LTD'],
	'CHEQUE_STATUS' => ['Postponed', 'Todate'],
	'CHEQUE_TYPE' => ['Crossed', 'Open'],
	'CHEQUE_PAYEE_TYPE' => ['Anyone', 'Named'],
	'CURRENCY_TYPE' => ['USD', 'Gs.'],
	'CURRENCY_SYMBOLS' => ['USD' => '$', 'Gs.' => '₲'],
	'LANGUAGE_TYPE' => ['English', 'German'],
	'USER_LEVEL' => ['Noobie', 'Bronze', 'Silver', 'Gold', 'Platinum'],
	'OPERATION_STATUS' => ['Draft', 'Rejected', 'Approved', 'Pending'],
	'SECURITY_LEVEL' => ['Secure', 'Medium', 'Risky'],
	'INVOICE_TYPE' => ['Service', 'Product'],
	'DEFAULT_FEEDBACK_RATE' => '',
	'DEFAULT_ISSUERS_RATE' => '',
	'MIPO_COMMISSION' => 20,
	'MIPO_ADD_COMMISSION' => 2,
	'OPERATION_EXTRA_EXPIRE_DAYS' => ['30', '60', '120', '240'],
	'DURATION_MONTHS' => [ 0 => 'current month', 1 => '1 months', 3 => '3 months', 6 => '6 months',  12 => '12 months'],
	'languages' => [
		'es' => 'Español',
		'en' => 'English',
	],
	'NOTIFICATIONS_TYPES' => [
		'OPERATIONS' => [
			'Create' => 'N/A', 'Update' => 'N/A', 'Delete' => 'N/A',
			'Draft' => 'N/A', 'Rejected' => true, 'Approved' => true, 'Pending' => 'N/A',
		],
		'OFFERS' => [
			'Create' => true, 'Update' => true, 'Delete' => true,
			'Pending' => true, 'Rejected' => true, 'Counter' => true, 'Approved' => true, 'Received' => true, 'Revert' => true
		],
		'PROMOTIONAL' => [

		],
	],
	'DEFAULT_OFFER_TIME' => 72,
	'Offer_STATUS' => ['Pending', 'Approved', 'Rejected', 'Counter', 'Completed'],
	'DEALS_STATUS' => ['Pending', 'Approved', 'Rejected', 'Completed'],
	'USER_ALERT' => [ 1 => 'Info', 2 => 'Warning', 3 => 'Block'],
	'IS_USER_LOGIN' => 1,
	'PAYMENT_OPTIONS' => ['Cash' => 'Cash', 'eWallet' => 'eWallet', 'Bank Transactions' => 'Bank', 'Other' => 'Other'],
	'CC' => [
		'SEND' => false,
		'EMAILS' => [
			['send' => false, 'email' => 'abcvv@gmail.com'],
			['send' => false, 'email' => 'abcvv@gmail.com']
		],
	],
	'BCC' => [
		'SEND' => false,
		'EMAILS' => [
			['send' => false, 'email' => 'abcvv@gmail.com'],
			['send' => false, 'email' => 'abcvv@gmail.com']
		],
	],

	'SEND_MAIL_NOTIFICATION' => true,
	'SEND_MAIL' => true,
	'SEND_NOTIFICATION' => true,
	'SEND_NOTIFICATION_ADMIN' => true,
	// 'MARITAL_STATUS' => ['Soltero/a','Casado/a','Divorciado/a', 'Viudo/a', 'Separado/a', 'Conviviente', 'Uniones civiles'],
	'MARITAL_STATUS' => ['Single','Married','Divorced', 'Widower', 'Separated', 'Cohabitant', 'Civil unions'],
	'GENDER' => ['Male','Female','Other'],
	'REGISTRATION_STEPS' => ['REGISTER'=>'register','OPT_VERIFY'=>'verify.otp','USER_DETAILS'=>'details.user','IPV_SCREEN'=>'verify.in-person'],
	//'DEFAULT_PLAN_NAME' => ['free','enterprise-1','enterprise-2','enterprise-3','Platinum','Enterprise'],
	'DEFAULT_ROLES_FOR_USER_LEVELS' => ['Noobie','Bronze','Silver','Gold','Platinum','Enterprise'],
	"SELLER_SIGN_CONTRACT_FORWARD_DEAL_TITLE" => "Send document to MIPO",
	"BUYER_SIGN_CONTRACT_FORWARD_DEAL_TITLE" => "Awaiting Seller Documents",
	'ALL_OTP_VERIFY' => date('Y'),
	'SUBSCRIPTION_DEFAULT_NO' => 'MIPO000001',
	'SUBSCRIPTION_DEFAULT_PREFIX' => 'MIPO',
	'MIPO_BANK_DETAILS' => '<p>Titular: Blufish S.A.</p><p>RUC: 80073934-5</p><p>Nro de Cuenta: 14559514</p><p>Banco: Visión Banco</p>',
	'PROFILE_IMAGE_MIMES' => "|image|mimes:png,jpg,jpeg,heif",
	'PROFILE_IMAGE_MAX_MB' => "|max:1024",
	'PROFILE_IMAGE_DIMENSIONS' => "|dimensions:max_width=800,max_height=600",
	'LOGO_IMAGE_MIMES' => "|image|mimes:png,jpg,jpeg,heif",
	'LOGO_IMAGE_MAX_MB' => "|max:1024",
	'LOGO_IMAGE_DIMENSIONS' => "|dimensions:max_width=800,max_height=600",
	'LOGO_IMAGE_MIMES' => "|image|mimes:png,jpg,jpeg,heif",
	'LOGO_IMAGE_MAX_MB' => "|max:1024",
	'LOGO_IMAGE_DIMENSIONS' => "|dimensions:max_width=800,max_height=600",
	// 'COM_DOC_MIMES' => "|file|mimes:png,jpg,heif,pdf",
	'COM_DOC_MIMES' => "|file",
	'COM_DOC_MAX_MB' => "|max:1024",
	'COM_DOC_DIMENSIONS' => "|dimensions:max_width=800,max_height=600",
	'HEIC_TO_OTHER_FORMAT' => "png",
	'HEIC_TO_OTHER_FORMAT_QUALITY' => "60",
	'COPY_RIGHT' => "© 2015 - 2023 MIPO S.A.  All Rights Reserved.",
	'MONTHS_NAME' => [
		'01' => 'enero',
		"02" => "febrero",
		"03" => "marzo",
		"04" => "abril",
		"05" => "mayo",
		"06" => "junio",
		"07" => "julio",
		"08" => "agosto",
		"09" => "septiembre",
		"10" => "octubre",
		"11" => "noviembre",
		"12" => "diciembre",
	]
];
