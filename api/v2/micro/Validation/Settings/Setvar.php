<?php


namespace Micro\Validation\Settings;

class Setvar {

    /*
    * section preg_match content variable
    * =================================
    */
    static function preg_phonenumber () {
        return '/^\(?\+?([0-9]{1,4})\)?[-\. ]?(\d{3})[-\. ]?([0-9]{7})$/';
    }

    static function preg_url () {
        return "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
    }

    /**
     * @return [type] [description]
     */
    static function preg_vowel()
    {
        return '/^(\s|[aeiouAEIOU])*$/';
    }

    static function preg_consonant()
    {
        return '/^(\s|[b-df-hj-np-tv-zB-DF-HJ-NP-TV-Z])*$/';
    }



    /*
    * section SANITIZE content variable
    * =================================
    */
    static function sanitize_email () {
        return FILTER_SANITIZE_EMAIL;
    }

    static function validate_email () {
        return FILTER_VALIDATE_EMAIL;
    }

    // section char_replace content variable



    /*
    * section set_message content variable
    * =================================
    */
    static function date_not_valid () {
        return "This is not date.";
    }

    static function date_delimiter_notsame () {
        return "Error Delimiter is not the same.";
    }

    static function date_delimiter_notfound () {
        return "Error Delimiter is not found.";
    }

    static function date_is_empty () {
        return "Date is Cannot be empty!";
    }

    static function minlength () {
        return "Minimal character length is ";
    }

    static function setminlength () {
        return "Please set your length with integer. (63)";
    }

    static function maxlength () {
        return "Maximal character length is ";
    }

    static function setmaxlength () {
        return "Please set your length with integer. (81)";
    }

    static function mail_is_empty () {
        return "Email is Cannot be empty!";
    }

    static function number_is_empty () {
        return "Number is Cannot be empty!";
    }

    static function number_is_notnumeric () {
        return "This is not numeric!";
    }

    static function invalid_phone_number () {
        return "Invalid Phone number";
    }

    static function invalid_url () {
        return "Invalid URL";
    }

    static function url_is_empty () {
        return "URL Cannot be empty!";
    }


    static function file_type_notfound () {
        return "File type is not found.";
    }

    static function file_is () {
        return "This is not file.";
    }




}


?>
