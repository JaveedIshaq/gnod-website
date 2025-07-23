<?php
// SMTP Configuration for GNOD Technologies
return [
    "smtp" => [
        "host" => "smtp.gmail.com",
        "port" => 587,
        "username" => "ishaqjaveed1@gmail.com",
        "password" => "jrpj ojgy djbl jhqp", // Gmail app password
        "encryption" => "tls",
    ],
    "from" => [
        "email" => "gnodtechnologies@gmail.com",
        "name" => "GNOD Technologies Contact Form"
    ],
    "to" => [
        "email" => "gnodtechnologies@gmail.com" // Where to receive contact form emails
    ],
    "recaptcha" => [
        "site_key" => "6Lf8P4wrAAAAAN1EmzldoSaiBET-Dh2UDvf4m154",
        "secret_key" => "6Lf8P4wrAAAAAGMslsPV_sj41Umx-qOnKHCxPw_B"
    ]
];
