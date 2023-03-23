(function() {
    var utilities_app = angular.module("utilitiesApp", []);

    utilities_app.filter("htmlToPlainText", function() {
        return function(text) {
            return text ? String(text).replace(/<[^>]+>/gm, '') : '';
        };
    });

    utilities_app.filter("limitToTail", function() {
        return function(text, max, tail) {
            return text.length > max ? text.substr(0, max) + tail : text;
        };
    });

    utilities_app.filter("epochToDateTime", function() {
        return function(seconds) {
            return new Date(0).setUTCSeconds(seconds);
        };
    });

    utilities_app.filter("parseHtml", function() {
        return function(text) {
            if (!text) return text;
            var s2 = text.replace(/&#(\d+);/g, function(x, m1) {
                if(isNaN(parseInt(m1))) return "";
                return String.fromCharCode(parseInt(m1));
            });
            return s2;
        };
    });
}());
