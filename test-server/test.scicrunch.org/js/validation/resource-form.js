Validation.initValidation();
jQuery.validator.addMethod("exists", function (value, element, param) {
    var status;
    $.ajax({
        url: param,
        data: 'name='+value,
        async: false,
        dataType: 'json',
        success: function (j) {
            if (j != '0') {
                status = true;
            } else
            status = false;
        }
    });
    return status;
}, $.format("The string you have entered is invalid or not available."));
jQuery.validator.addMethod("badname", function(value, element, param){
    var status = true;
    if(param.test(value)) status = false;
    return status;
}, $.format("The string you have entered contains invalid characters"));
jQuery.validator.addMethod("accept", function (value, element, param) {
    return value.match(new RegExp(param));
}, $.format("You have used an invalid character for this type."));
jQuery.validator.addMethod("comma_regex", function (value, element, param) {
    var sep_vals = value.split(",");
    var regex = new RegExp(param);
    for(var i=0; i < sep_vals.length; i++){
        if(regex.test(sep_vals[i]) === false) return false;
    }
    return true;
}, $.format("Must be comma separated list of PMIDs or DOI records (eg PMID:1234, DOI:10.1371/journal.pone.0146300)"));

jQuery.validator.addClassRules('portal', {
    required: false,
    //accept: "[0-9a-fA-F\-\.]*",
    exists: "/validation/community-name.php",
    badname: /[^0-9a-zA-Z\-]/
});
jQuery.validator.addClassRules('required', {
    required: true
});
jQuery.validator.addClassRules('Resource_Name', {
    required: false
    //accept: "[0-9a-fA-F\-\.]*"
    //exists: "/validation/resource-name.php"
});
jQuery.validator.addClassRules('color-input', {
    maxlength: 6,
    minlength: 6,
    required: false,
    accept: "[0-9a-fA-F]*"
});
jQuery.validator.addClassRules("Defining_Citation", {
    required: false,
    comma_regex: "(^[ ]?PMID:[0-9]+[ ]?$|^[ ]?DOI:[^ ]+[ ]?$|^$)"
});
