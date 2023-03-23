/**
 * Created by Davis on 8/4/14.
 */
$(document).ready(function () {
    window.globals = window.globals || {};

    communityJS();
    componentJS();
    profileJS();
    collectionJS();
    handleSearchV1();
    resourceJS();
    tagAutocomplete();

    Math.fmod = function (a,b) {
        return Number((a - (Math.floor(a / b) * b)).toPrecision(8));
    };

    globals.showLogin = function(){
        $('.login-backing').show();
        $('.login-backing input[name="email"]').focus();
    };

    $('.back-hide').on('click', '.close', function () {
        $('.background').hide();
        $('.back-hide').hide();
    })
    $('.background').click(function () {
        $('.background').hide();
        $('.back-hide').hide();
    });
    $('.close-btn').click(function () {
        $('.background').hide();
        $('.back-hide').hide();
    });
    $('.clickable').click(function () {
        var _this = $(this);
        $(_this).parent().parent().find('.panel-body:first').toggle();
        $(_this).find('.clickable-icon').toggleClass('fa-plus fa-minus');
    });
    $('.non-click').click(function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    // $('.btn-login').click(globals.showLogin); Commneted by Steven, removes login modal 
    $('.icon-dropdown li').click(function () {
        var icon = $(this).attr('icon');
        var name = $(this).parent().attr('name');
        $('.' + name + '-btn').html('<i class="' + icon + '"></i> ' + icon);
        $('.' + name).val(icon);
    });
    $('.invis-hide').click(function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('body').click(function () {
        $('.invis-hide').hide();
        $('.body-hide').removeClass('active');
    });
    $('.login-backing').click(function () {
        $('.login-backing').hide();
    });
    $('.login-box button').click(function (e) {

        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('.login-box').click(function (e) {

        //e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('.form-submit-button').click(function () {
        $('.submit-class').submit();
    });
    $('.form-submit-button1').click(function () {
        $('.submit-class1').submit();
    });
    $('.collapse').click(function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('.forgot-pw-form').on('submit', function () {
        var email = $(this).find('.forgot-email').val();
        $.post("/forms/forgotPassword.php", {
            email: email
        }).done(function (data) {
            var obj = JSON.parse(data);
            var html;
            if (obj.status == 1) {
                html = '<div class="alert alert-success">';
                html += '<p>' + obj.message + '</p><br/>';
                html += '<form method="post" action="/forms/login.php">';
                html += '<input type="hidden" name="email" value="' + email + '"/>';
                html += '<div class="input-group">';
                html += '<input type="password" class="form-control" name="password" placeholder="Password from Email"/>';
                html += '<span class="input-group-btn">';
                html += '<button class="btn-u" type="submit">Login</button>';
                html += '</span>';
                html += '</div>';
                html += '</form>';
                html += '</div>';
            } else {
                html = '<div class="alert alert-danger">';
                html += '<p>' + obj.message + '</p><br/>';
                html += '<form class="forgot-pw-form">';
                html += '<div class="input-group">';
                html += '<input type="text" class="form-control forgot-email" name="query" placeholder="Account Email"/>';
                html += '<span class="input-group-btn">';
                html += '<button class="btn-u" type="submit">Reset</button>';
                html += '</span>';
                html += '</div>';
                html += '</form>';
                html += '</div>';
            }
            $('.forgot-pw-container').html(html);
        }, "json");
        return false;
    });

    $(".dropdown-toggle").dropdown();

    $(".resource-image-submit").click(function(){
        angular.element("#fields-panel").scope().save();
        $("#resource-image").submit();
    });

    $(".Resource_URL").blur(function(){
        var val = $(this).val();
        if(val.length > 0 && val.substr(0,4) != "http"){
            val = "http://" + val;
        }
        $(this).val(val);
        $(this).valid();
    });

    $(".nav-tabs-js li a").click(function(e){
        e.preventDefault();
        $(this).tab("show");
    });

    $(".toggle-slide-button").click(function(e){
        $(".toggle-slide").slideToggle();
        $(".toggle-slide-hidden").slideToggle();
    });

    $('[data-toggle="popover"]').popover();

    (function() {
        $(".referer-link").each(function(i, obj) {
            var old_href = $(this).attr("href");
            if(window.location.pathname === old_href) return;
            var operator = (old_href.indexOf("?") === -1 ? "?" : "&"); // is this the first get argument?
            var referer_part = operator + 'referer="' + encodeURIComponent(window.location.pathname + window.location.search) + '"';
            var href = old_href + referer_part;
            $(this).attr("href", href);
        });
    }());


    (function() {
        $(".help-tooltip").addClass("fa fa-question-circle help-tooltip-btn");
        $(".help-tooltip").css("cursor", "pointer");
        $(".help-tooltip-btn").click(function() {
            var name = $(this).data("name");
            var title = $(this).data("title");
            if(!title) title = "Help";
            var dialog_body = $("#help-modal-body");
            if(dialog_body.length == 0) {
                $("body").append('<div class="modal fade" id="help-modal" tabindex="-1"><div class="modal-dialog role="document"><div class="modal-content"><div class="modal-body" id="help-modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">close</button></div></div></div></div>');
                dialog_body = $("#help-modal-body");
            }
            dialog_body.attr("title", title);
            $.get("/templates/help-tooltips/" + name).then(function(response) {
                dialog_body.html(response);
                $("#help-modal").modal("show");
            });
        });
    }());

    // jQuery for dropdown toggle with triangle icon
    $('.togglewrapper h3').click(function() {
        $(this).next().toggle();
        $(this).find('i').toggleClass('fa fa-caret-right fa fa-caret-down')
    });

    // jQuery for the collapsable categories in the sidebar
    $('#sidebar-categories-list .glyphicon.glyphicon-plus').each(function() {
        $(this).click(function () {
            $(this).toggleClass('glyphicon-plus glyphicon-minus');
            $(this).siblings('ul').collapse('toggle');
        });
    });

    /* for adding addition resource funding informations on the resource creation page */
    $(".add-resource-funding-info-field").on("click", function(e) {
        var copy = $(this).parent().find(".funding-fields .row").first().clone();
        copy.find("input").val("");
        $(this).parent().find(".funding-fields").append(copy);
    });

    /* for handling topbar alerts */
    var alert_cookies = [];
    var alert_cookies_raw = readCookie("alert-dismiss");
    if(alert_cookies_raw) {
        alert_cookies = JSON.parse(alert_cookies_raw);
    }
    $(".alert-dismissible-hidden").each(function(el) {
        var element_id = $(this).data("id");
        if(alert_cookies.indexOf(element_id) === -1) {
            $(this).removeClass("alert-dismissible-hidden");
        }
    });
    $(".js-alert-dismiss").on("click", function(el) {
        var element_id = $(this).data("id");
        var alert_cookies = [];
        var alert_cookies_raw = readCookie("alert-dismiss");
        if(alert_cookies_raw) {
            alert_cookies = JSON.parse(alert_cookies_raw);
        }
        alert_cookies.push(element_id);
        createCookie("alert-dismiss", JSON.stringify(alert_cookies));
        $(".alert-dismissible[data-id='" + element_id + "']").addClass("alert-dismissible-hidden");
    });

    /* for rrid report items */
    (function() {
        var updateVisibility = function() {
            var key = $(this).data("name");
            var val = $(this).val();
            var type = $(this).data("type");
            $(".js-rrid-report-item-group-item [data-group='" + key + "'][data-type='" + type + "']").parent(".js-rrid-report-item-group-item").hide();
            $(".js-rrid-report-item-group-item [data-group='" + key + "'][data-group-choice='" + val + "'][data-type='" + type + "']").parent(".js-rrid-report-item-group-item").show();
        };

        $(".js-rrid-report-item-group-select").each(updateVisibility);
        $(".js-rrid-report-item-group-select").on("change", updateVisibility);

        /* delete subtype item button */
        $(".js-rrid-report-item-delete-subtype").on("click", function() {
            var report_id = $(this).data("report-id");
            var type = $(this).data("type");
            var subtype = $(this).data("subtype");
            var uuid = $(this).data("uuid");
            var post_data = {
                "rrid-report-id": report_id,
                "type": type,
                "subtype": subtype,
                "uuid": uuid
            };
            $.post("/forms/rrid-report-forms/delete-report-item-subtype.php", post_data)
                .then(function() {
                    location.reload();
                });
        });
    }());
});

function resourceJS() {
    $('.resource-find-form').submit(function (e) {
        e.preventDefault();
        var portalName = $("#community-portal-name").val();
        if(!portalName) portalName = "scicrunch";
        $('.resource-load').html('<i class="fa fa-spin fa-spinner" style="font-size:26px;margin:0 auto"></i>');
        var search = $('.type-find').val().replace(" ", "+");
        $('.resource-load').load('/php/resource-checker.php?name=' + search + '&portalname=' + portalName);
    })
    $('.captcha-form').submit(function (e) {
        //console.log($('#recaptcha-accessible-status').text());
        var text = $('#g-recaptcha-response').val();
        if (text != '')
            return true;
        else
            return false;
    });
}

function profileJS() {
    $('.file-form').change(function () {
        var value = $(this).val();
        var splits = value.split('\\');
        var splits2 = splits[splits.length - 1].split('/');

        $(this).parent().parent().find('.file-placeholder').val(splits2[splits2.length - 1]);
    });
    $('body').on('blur', '.color-input', function () {
        $(this).parent().children('.fa-circle').css('color', '#' + $(this).val());
    });
    $('.edit-popup').click(function () {
        var id = $(this).attr('field');
        $('.field-edit').empty();
        $('.field-edit').load('/php/field-edit.php?id=' + id, function () {
            $('.background').show();
            $('.field-edit').show();
        });
    });
    $('.field-add-btn').click(function () {
        $('.background').show();
        $('.field-add').show();
    });
    $('.category-load-btn').click(function () {
        var cid = $(this).attr('cid');
        var type = $(this).attr('control');
        var category = $(this).attr('category');
        var subcategory = $(this).attr('subcategory');
        var x = $(this).attr('x');
        var y = $(this).attr('y');
        var id = $(this).attr('source');
        $('.category-form-load').empty();
        $('.category-form-load').load('/php/category-form.php?category=' + encodeURIComponent(category) + '&type=' + type + '&subcategory=' + encodeURIComponent(subcategory) + '&id=' + id + '&cid=' + cid + '&x=' + x + '&y=' + y, function () {
            $('.background').show();
            $('.category-form-load').show();
        });
    });
    $('.category-edit-btn').click(function () {
        var id = $(this).attr('data');
        $('.category-form-load').empty();
        $('.category-form-load').load('/php/category-form.php?type=edit&id=' + id, function () {
            $('.background').show();
            $('.category-form-load').show();
        });
    });
    $('.category-name-btn').click(function () {

        var type = $(this).attr('control');
        var category = encodeURIComponent($(this).attr('category'));
        var subcategory = encodeURIComponent($(this).attr('subcategory'));
        var cid = $(this).attr('cid');
        $('.category-form-load').empty();
        $('.category-form-load').load('/php/category-name.php?type=' + type + '&cid=' + cid + '&category=' + category + '&subcategory=' + subcategory, function () {
            $('.background').show();
            $('.category-form-load').show();
        });
    });
    $('.data-add').click(function () {
        var comp = $(this).attr('component');
        var cid = $(this).attr('community');
        $('.data-add-load').empty();
        $('.data-add-load').load('/php/data-load.php?comp=' + comp + '&cid=' + cid, function () {
            $('.background').show();
            $('.data-add-load').show();
        });
    });
    $('.data-edit').click(function () {
        var id = $(this).attr('data');
        $('.data-add-load').empty();
        $('.data-add-load').load('/php/data-edit-load.php?id=' + id, function () {
            $('.background').show();
            $('.data-add-load').show();
        });
    });
    $('.component-select-image').click(function () {
        var comp = $(this).attr('component');
        var cid = $(this).attr('community');
        $('.component-add-load').empty();
        $('.component-add-load').load('/php/component-load.php?comp=' + comp + '&cid=' + cid, function () {
            $('.component-select-container').hide();
            $('.component-add-load').show();
        });
    });
    $('.container-select-image').click(function () {
        var type = $(this).attr('type');
        var cid = $(this).attr('community');
        $('.container-add-load').empty();
        $('.container-add-load').load('/php/component-load-type.php?type=' + type + '&cid=' + cid, function () {
            $('.cont-select-container').hide();
            $('.container-add-load').show();
        });
    });
    $('.edit-container').click(function () {
        var id = $(this).attr('container');
        var cid = $(this).attr('community');
        $('.container-add-load').empty();
        $('.container-add-load').load('/php/component-load-type.php?id=' + id + '&cid=' + cid, function () {
            $('.background').show();
            $('.container-add-load').show();
        });
    });
    $('.data-add-load').on('click', '.icon-dropdown li', function () {
        var icon = $(this).attr('icon');
        var name = $(this).parent().attr('name');
        $('.' + name + '-btn').html('<i class="' + icon + '"></i> ' + icon);
        $('.' + name).val(icon);
    });
    $('.component-add-load').on('click', '.icon-dropdown li', function () {
        var icon = $(this).attr('icon');
        var name = $(this).parent().attr('name');
        $('.' + name + '-btn').html('<i class="' + icon + '"></i> ' + icon);
        $('.' + name).val(icon);
    });
    $('.component-add').click(function () {
        $('.background').show();
        $('.component-select-container').show();
    });
    $('.container-add').click(function () {
        $('.background').show();
        $('.cont-select-container').show();
    });
}

function componentJS() {
    var works_max = 200;
    //$('.works-img').each(function(){
    //    if($(this).height() > works_max)
    //        $(this).height(works_max);
    //    if($(this).width() > works_max)
    //        $(this).width(works_max);
    //});

    var maxHeight = 0;
    $('.easy-block-v3').each(function () {
        if ($(this).height() > maxHeight) {
            maxHeight = $(this).height();
        }
    });
    if (maxHeight < 30)
        maxHeight = 90;
    $('.easy-block-v3').height(maxHeight);
    //maxHeight = 0;
    //$('.works-img').each(function () {
    //    if ($(this).height() > maxHeight) {
    //        maxHeight = $(this).height();
    //    }
    //});
    //if (maxHeight < 100)
    //    maxHeight = 190;
    //$('.works-img').height(maxHeight);
    maxHeight = 0;
    $('.news-img').each(function () {
        if ($(this).height() > maxHeight) {
            maxHeight = $(this).height();
        }
    });
    if (maxHeight < 100)
        maxHeight = 190;
    $('.news-img').height(maxHeight);
    $('.data-link').click(function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        var id = $(this).attr('data');
        $.get("/php/track-click.php", { id: id});
        window.location = href;
        return false;
    });
}

jQuery.fn.sortElements = (function () {

    var sort = [].sort;

    return function (comparator, getSortable) {

        getSortable = getSortable || function () {
            return this;
        };

        var placements = this.map(function () {

            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,

            // Since the element itself will change position, we have
            // to have some way of storing its original position in
            // the DOM. The easiest way is to have a 'flag' node:
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );

            return function () {

                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }

                // Insert before flag:
                parentNode.insertBefore(this, nextSibling);
                // Remove flag:
                parentNode.removeChild(nextSibling);

            };

        });

        return sort.call(this, comparator).each(function (i) {
            placements[i].call(getSortable.call(this));
        });

    };

})();

function collectionJS() {
    $('.ajax-new-collection').click(function () {
        var _this = $(this);
        $('#new-collection-ajax').attr('community', $(_this).attr('community'));
        $('#new-collection-ajax').attr('cid', $(_this).attr('cid'));
        $('#new-collection-ajax').attr('view', $(_this).attr('view'));
        $('#new-collection-ajax').attr('uuid', $(_this).attr('uuid'));
        $('.background').show();
        $('.new-collection-ajax').show();
    });
    $('#new-collection-ajax').submit(function () {
        var _this = $(this);
        var comm = $(_this).attr('community');
        var cid = $(_this).attr('cid');
        var nif = $(_this).attr('view');
        var uuid = $(_this).attr('uuid');
        $.post("/forms/collection-forms/create-collection.php", {
            name: $('.ajax-name').val(),
            transfer: $('.ajax-tranfer').val(),
            redirect: 'off'
        }).done(function (data) {
            var obj = JSON.parse(data);
            var elem;
            var html = '<tr><td><a href="/' + comm + '/account/collections/' + obj.id + '">' + obj.name + '</a></td><td class="' + obj.id + '-count">0</td>';
            $('.collection-tables').each(function(){
                elem = html + '<td><a href="javascript:void(0)" class="add-item" collection="' + obj.id + '" community="' + cid + '" view="' + nif + '" uuid="' + $(this).attr('uuid') + '"><i style="font-size: 16px;color:#00bb00" class="fa fa-plus-circle"></i></a></td></tr>';
                $(this).append(elem);
            });
            $('.background').hide();
            $('.new-collection-ajax').hide();
        }, "json");
        return false;
    });
    $('.collection-box table').on('click', '.add-item', function () {
        var _this = $(this);
        $('.update-' + $(_this).attr('uuid')).show();
        $.get("/forms/collection-forms/add-item.php", {
            community: $(_this).attr('community'),
            uuid: $(_this).attr('uuid'),
            view: $(_this).attr('view'),
            collection: $(_this).attr('collection')
        }).done(function (data) {
            $(_this).removeClass('add-item');
            $(_this).addClass('remove-item');
            $(_this).children('i').toggleClass('fa-times-circle fa-plus-circle');
            $(_this).children('i').css('color', '#bb0000');
            var obj = JSON.parse(data);
            $('.' + $(_this).attr('collection') + '-count').text(obj.num);
            $('.' + $(_this).attr('uuid') + '-image').show();
            $('.update-' + $(_this).attr('uuid')).hide();
            var parent = $(_this).closest('.coll-li').children('.collection-icon');
            $(parent).attr('title', 'In a Collection');
            $(parent).removeClass('fa-square-o');
            $(parent).addClass('fa-check-square-o');
            $(parent).addClass('in-collection');
        }, "json");
    });
    $('.collection-box table').on('click', '.remove-item', function () {
        var _this = $(this);
        $.get("/forms/collection-forms/remove-item.php", {
            community: $(_this).attr('community'),
            uuid: $(_this).attr('uuid'),
            view: $(_this).attr('view'),
            collection: $(_this).attr('collection')
        }).done(function (data) {
            $(_this).removeClass('remove-item');
            $(_this).addClass('add-item');
            $(_this).children('i').toggleClass('fa-times-circle fa-plus-circle');
            $(_this).children('i').css('color', '#00bb00');
            var obj = JSON.parse(data);
            $('.' + $(_this).attr('collection') + '-count').text(obj.num);
            if (obj.inColl == 'true') {
                $('.' + $(_this).attr('uuid') + '-image').attr('title', 'In a Collection');
                $('.' + $(_this).attr('uuid') + '-image').removeClass('fa-square-o');
                $('.' + $(_this).attr('uuid') + '-image').addClass('fa-check-square-o');
                $('.' + $(_this).attr('uuid') + '-image').addClass('in-collection');
            } else {
                $('.' + $(_this).attr('uuid') + '-image').attr('title', 'Not in a Collection');
                $('.' + $(_this).attr('uuid') + '-image').removeClass('in-collection');
                $('.' + $(_this).attr('uuid') + '-image').removeClass('fa-check-square-o');
                $('.' + $(_this).attr('uuid') + '-image').addClass('fa-square-o');
            }
        }, "json");
    });
    $('.manage-coll').click(function (e) {
        if (!$(this).parent().parent().parent().hasClass('active'))
            $('.btn-group').removeClass('active');
        $(this).parent().parent().parent().toggleClass('active');
        $(this).parent().parent().parent().toggleClass('open');
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('.collection-icon').click(function (e) {
        if (!$(this).parent().hasClass('active')) {
            $('.coll-li').removeClass('active');
            $(this).parent().addClass('active');
        } else {
            $(this).parent().removeClass('active');
        }
        e.stopPropagation();
        e.stopImmediatePropagation();
    });

    $(window).click(function (e) {
        $(".coll-li.active").parent().addClass("active");
        $(".coll-li.active").removeClass("active");
    });
}

function communityJS() {
    $('.showMoreColumns').click(function(){
        if($(this).hasClass('active')){
            $('.hidden-column.showing').hide();
            $(this).html('<i class="fa fa-plus"></i> Show More Columns</a>');
            $(this).removeClass('active');
            deleteCookie("show-more-cols");
        } else {
            $('.hidden-column.showing').show();
            $(this).html('<i class="fa fa-times"></i> Show Fewer Columns</a>');
            $(this).addClass('active');
            createCookie("show-more-cols", "", 60);
        }
    });
    if(readCookie("show-more-cols") !== null) {
        $(".showMoreColumns").trigger("click");
    }

    $('.showMoreColumnsInterlexMappings').click(function(){
        if($(this).hasClass('active')){
            $('.hidden-column.showing').hide();
            $(this).html('<i class="fa fa-plus"></i> Show More Columns</a>');
            $(this).removeClass('active');
            deleteCookie("show-more-cols-inter-mappings");
        } else {
            $('.hidden-column.showing').show();
            $(this).html('<i class="fa fa-times"></i> Show Fewer Columns</a>');
            $(this).addClass('active');
            createCookie("show-more-cols-inter-mappings", "", 60);
        }
    });
    if(readCookie("show-more-cols-inter-mappings") !== null) {
        $(".showMoreColumnsInterlexMappings").trigger("click");
    }

    $('.showMoreColumnsInterlexHistory').click(function(){
        if($(this).hasClass('active')){
            $('.hidden-column.showing').hide();
            $(this).html('<i class="fa fa-plus"></i> Show More Columns</a>');
            $(this).removeClass('active');
            deleteCookie("show-more-cols-inter-history");
        } else {
            $('.hidden-column.showing').show();
            $(this).html('<i class="fa fa-times"></i> Show Fewer Columns</a>');
            $(this).addClass('active');
            createCookie("show-more-cols-inter-history", "", 60);
        }
    });
    if(readCookie("show-more-cols-inter-history") !== null) {
        $(".showMoreColumnsInterlexHistory").trigger("click");
    }

    $('.multi-facets').submit(function(e){
        e.preventDefault();
        var url = $(this).attr('url');
        var facets = '';
        $('.facet-checkbox').each(function(){
            if($(this).is(':checked')){
                facets += '&facet[]='+$(this).attr('column')+':'+$(this).attr('facet');
            }
        });
        window.location = url+facets;
        return false;
    });
    $('.multi-indices').submit(function(e){
        e.preventDefault();
        var url = $(this).attr('url');
        var data_sources = $('.data-source').val();
        var sources_count = $('.sources-count').val();
        var indices = [];
        var n = 0;
        $('.indices-checkbox').each(function(){
            if($(this).is(':checked')){
                indices.push($(this).attr('source'));
                n++;
            }
        });
        var indices_s = indices.join(',');
        if(indices_s == '' || n == sources_count) indices_s = data_sources;
        window.location = url.replace('/*/', '/'+indices_s+'/');
        return false;
    });
    $('.multi-types').submit(function(e){
        e.preventDefault();
        var url = $(this).attr('url');
        var types = [];
        $('.types-checkbox').each(function(){
            if($(this).is(':checked')){
                types.push($(this).attr('results_type'));
            }
        });
        if(types.length > 0) types = types.join(',');
        else types = "term";
        window.location = url.replace('%*%', types);
        return false;
    });
    $('.cite-this-btn').click(function (e) {
        $(this).parent().toggleClass('active');
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('.type-find-form').submit(function (e) {
        e.preventDefault();
        var query = $('.type-find').val();
        $('.type-table tr').hide();
        $('.first').show();
        if (query == '')
            $('.type-table tr').show();
        else
            $('.type-table tr[values*="' + query.toLowerCase() + '"]').show();
        $('.last').show();
    });
    $('.simple-toggle').click(function () {
        $('.background').show();
        $($(this).attr('modal')).show();
    });
    $('.sort-popular').click(function () {
        $('.inner-results').sortElements(function (a, b) {
            var contentA = parseInt($(a).attr('popularity'));
            var contentB = parseInt($(b).attr('popularity'));
            return (contentA < contentB) ? 1 : (contentA > contentB) ? -1 : 0;
        })
    });
    $('.circle-container').click(function (e) {
        if (!$(this).hasClass('active'))
            $('.circle-container').removeClass('active');
        $(this).toggleClass('active');
        e.stopPropagation();
        e.stopImmediatePropagation();
    });

    $('.circle-container').blur(function () {
        $('.circle-container').removeClass('active');
    });
    $('.no-propagation').click(function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
    })
    $('.save-search').click(function () {
        $('.background').show();
        $('.saved-this-search').show();
    });

    $('.snippet-edit').click(function () {
        var view = $(this).attr('view');
        var cid = $(this).attr('cid');
        $('.snippet-load').load('/php/snippet-load.php?view=' + view + '&cid=' + cid, function () {
            $('.background').show();
            $('.snippet-load').show();
        });
    });
    $('.fullrecord').click(function () {
        var view = $(this).attr('view');
        var uuid = $(this).attr('uuid');
        $('.record-load').load('/php/full-record.php?view=' + view + '&uuid=' + uuid, function () {
            $('.background').show();
            $('.record-load').show();
        });
    });
    $('.category-choose').click(function (e) {
        e.preventDefault();
        var parent = $(this).attr('parent');
        var child = $(this).attr('child');
        var new_url = window.location.href;
        if(new_url.indexOf("category-filter") !== -1) {
            new_url = new_url.replace(/&category-filter=([^&])*/, "");
        }
        if ($(this).parent().hasClass('active')) {
            $('.inner-results').show();
            $('.inner-hidden-results').hide();
            $(this).parent().removeClass('active');
            $('.data-number').text($('.data-number').attr('data'));
            $('.source-number').text($('.source-number').attr('data'));
        } else {
            $('.inner-results').hide();
            $('.inner-results[parent="' + parent + '"][child="' + child + '"]').show();
            $('.category-li').removeClass('active');
            $('.data-number').text($(this).children('.category-number').text());
            $('.source-number').text($('.inner-results[parent="' + parent + '"][child="' + child + '"]').length);
            $(this).parent().addClass('active');

            new_url += "&category-filter=" + parent + ":" + child;
        }
        window.history.pushState("", "", new_url);
    });
    $('.category-choose-comm').click(function (e) {
        e.preventDefault();
        var comm = $(this).attr('community');
        if ($(this).parent().hasClass('active')) {
            $('.inner-results').show();
            $('.inner-hidden-results').hide();
            $(this).parent().removeClass('active');
            $('.category-li-comm').removeClass('active');
            $('.data-number').text($('.data-number').attr('data'));
            $('.source-number').text($('.source-number').attr('data'));
        } else {
            $('.inner-results').hide();
            $('.inner-results[comms~="' + comm + '"]').show();
            $('.inner-hidden-results').hide();
            $('.category-li').removeClass('active');
            $('.category-li-comm').removeClass('active');
            $('.data-number').text($('.data-number').attr('data'));
            $('.source-number').text($('.source-number').attr('data'));
            $(this).parent().addClass('active');
        }
    });
    $('.column-search-form').submit(function () {
        var _this = $(this);
        var column = $(_this).attr('column');
        var value = $(_this).find('.form-control').val();
        if(!value) return false;
        if(value[0] != "\"" && value[value.length - 1] != "\"" && value.split(" ").length > 1) {
            value = '\"' + $(_this).find('.form-control').val() + '\"';
        }
        var url = window.location.href.toString();
        url = url.replace(/\/page\/\d+/g, "");
        var get_sep = (url.indexOf("?") == -1) ? "?" : "&";
        window.location = url + get_sep + 'filter[]=' + column + ':' + value;
        return false;
    });
    $('.column-search').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
    });
    $('.sortin-column').click(function (e){
        window.location = $(this)[0].href;
    });
    $('.search-filter-btn').click(function () {
        $(this).parent().parent().parent().submit();
    });
    $('.search-header').click(function (e) {
        var _this = $(this);
        var box = $(_this).find('.column-search');
        if ($(box).is(':visible')) {
            $('.column-search').hide();
        } else {
            $('.column-search').hide();
            $(box).toggle();
        }
        e.stopPropagation();
        e.stopImmediatePropagation();
    });

    $('.page-search-form2').submit(function () {
        var community = $('.search-community').val();
        var category = $('.category-input').val();
        var subcategory = $('.subcategory-input').val();
        var source = $('.source-input').val();

        var stripped = $('.stripped-community').val();
        var url;

        var cat = '';
        if(category && category != '')
            cat = category;
        else
            cat = 'Any';

        if(stripped && stripped=='true')
            url = '/' + community + '/stripped/' + cat;
        else
            url = '/' + community + '/' + cat;
        if (subcategory && subcategory != '')
            url += '/' + subcategory;
        if (source && source != '')
            url += '/source/' + source;

        var display = $('#small-search-auto').val();
        var params = '';
        var autocomplete_query = $('#autoValues').val();
        var query = getAutocompleteQuery(autocomplete_query, display);
        if (!query || query == '') query = display;
        query = formatQuery(query);
        //params += 'q=' + query + '&l=' + encodeQuery(display);
        params += 'q=' + query + '&l=' + query;

        window.location = url + '/search?' + params;
        return false;
    });
    $('.page-search-form1').submit(function () {
        var community = $('.search-community').val();
        var category = $('.category-input').val();
        var subcategory = $('.subcategory-input').val();
        var source = $('.source-input').val();
        var type = $('.type-input').val();
        var title = $('.title-input').val();
        var data_sources = $('.data-sources').val();

        var stripped = $('.stripped-community').val();
        var results_types = $('.results-types').val();

        var url;

        if(stripped && stripped=='true')
            url = '/' + community + '/stripped/' + category;
        else
            url = '/' + community + '/' + category;
        if (subcategory && subcategory != '')
            url += '/' + subcategory;
        if (source && source != '')
            url += '/source/' + source;
        if (type == 'interlex')
            url += type;
        if (title == "table")
            url += "/" + title;

        var filters = [];
        $(".search-block-filter").each(function(i, filter) {
            if($(filter).val()) {
                filters.push("filter[]=" + $(filter).data("filtername") + ":" + $(filter).val());
            }
        });

        var display = $('#search-banner-input').val();
        if(display.length > 2 && display[0] == "*") {
            display = display.slice(1);
        }
        var params = '';
        //var autocomplete_query = $('#autoValues').val();
        var autocomplete_query = window.location.href.split("#")[1];  // get categoriy-filter from the current url -- Vicky-2019-7-10
        var query = getAutocompleteQuery(autocomplete_query, display);
        if (!query || query == '') query = display;
        query = formatQuery(query);
        //params += 'q=' + query + '&l=' + encodeQuery(display);
        params += 'q=' + query + '&l=' + query;

        if(filters.length > 0) {
            params += "&" + filters.join("&");
        }
        if(data_sources != "") params += "&sources=" + data_sources;
        if(results_types != "" && results_types != undefined) params += "&types=" + results_types;
        if(autocomplete_query) params += "#" + autocomplete_query;  // add categoriy-filter to the searching url -- Vicky-2019-7-10

        window.location = url + '/search?' + params;
        return false;
    });
    $(".search-banner-category").on("click", function() {
        var href = $(this).data("href");
        var search = $("#search-banner-input").val();
        if(!search) {
            href += "%2A";
        } else {
            href += encodeURIComponent(search);
        }
        window.location = href;
        return false;
    });
    $('.page-search-form').submit(function () {
        var search_type = $('.search-banner-type').val();
        var community = $('.search-community').val();
        if(search_type === undefined){
            if(community === "scicrunch") search_type = "mainpage";
            else if(community === "neuinfo") search_type = "neuinfo";
            else if(community === "SPARC") search_type = "sparc";
            else if(community === "sawg") search_type = "sawg";
        }
        var query_param = search_type === "mainpage" ? "query=" : "q=";
        var category = $('input:radio[name=checkbox-inline]:checked').val();
        if(category === undefined) category = "Any";
        var display = $('#search-banner-input').val();
        var params = '';
        var autocomplete_query = $('#autoValues').val();
        var query = getAutocompleteQuery(autocomplete_query, display);
        if (!query || query == '') query = display;
        query = formatQuery(query);
        //params += query_param + query + '&l=' + encodeQuery(display);
        params += query_param + query + '&l=' + query;
        var stripped = $('.stripped-community').val();
        var url;

        if(stripped && stripped=='true')
            url = '/' + community + '/stripped/' + category;
        else if(!community)
            url = '';
        else
            url = '/' + community + '/' + category;
        if(search_type === "mainpage") window.location = '/browse/search?' + params;
        else if(search_type === "neuinfo") window.location = '/neuinfo/data/search?' + params;
        //else if(search_type === "resources-dashboard") window.location = "/scicrunch/Resources/source/nlx_144509-1/search?" + params;
        else if(search_type === "resources-dashboard") window.location = "/scicrunch/Resources/search?" + params
        else if(search_type === "data-dashboard") window.location = "/scicrunch/data/search?" + params;
        else if(search_type === "sawg") window.location = "/sawg/interlex/search?" + params;
        else window.location = url + '/search?' + params;
        return false;
    });
    $('.page-search-3').submit(function () {
        var category = $('select[name="category"]').val();
        var display = $('#search-block-input').val();
        var params = '';
        var autocomplete_query = $('#autoValues').val();
        var query = getAutocompleteQuery(autocomplete_query, display);
        if (!query || query == '') query = display;
        query = formatQuery(query);
        //params += 'q=' + query + '&l=' + encodeQuery(display);
        params += 'q=' + query + '&l=' + query;
        var community = $('.search-community').val();
        var stripped = $('.stripped-community').val();
        var url;
        console.log(community);
        // Manu added the below line
	community = 'Software-Discovery-Portal';
        if(stripped && stripped=='true')
            url = '/' + community + '/stripped/' + category;
        else
            url = '/' + community + '/' + category;
        window.location = url + '/search?' + params;
        return false;
    });
    function getAutocompleteQuery(auto, display){
        var inside_entity = false;
        var inside_identifier = false;
        var query = "";
        for(var i = 0; i < display.length; i++) {
            if(!inside_entity && display[i] == "[") {
                inside_entity = true;
                continue;
            }
            if(inside_entity && !inside_identifier && display[i] == "{") {
                inside_identifier = true;
                query += " ";
                continue;
            }
            if(inside_entity && inside_identifier && display[i] == "}") {
                inside_identifier = false;
                query += " ";
                continue;
            }
            if(inside_entity && !inside_identifier && display[i] == "]") {
                inside_entity = false;
                continue;
            }
            if((inside_entity && inside_identifier) || (!inside_entity && !inside_identifier)) {
                query += display[i];
            }
        }

        // make sure there are not empty qoutes
        query = query.replace(/""/g, '"');

        // make sure there is not an odd number of quotes
        var quote_count = (query.match(/"/g) || []).length;
        if(quote_count % 2 != 0) {
            query = query.replace(/"/, "");
        }

        // make sure single words are not quoted
        query = query.replace(/"([\w]+)"/g, "$1");

        // trim the final query of white space
        query = query.replace(/^\s+|\s+$/g, "");


        return query;
    }
    function encodeQuery(query) {
        return encodeURIComponent(query).replace(/\&/g, "%26");
    }
    function formatQuery(query) {
        //var new_query = query.replace(/"(\w+)"/g, "$1").replace(/\+/g, "%2b").replace(/\&/g, "%26").replace(/</g, "%3c").replace(/>/g, "%3e");

        // search for PMID strings with and without PMID prefix
        var new_query = query.replace(/PMID:(\d+)/g, "PMID:$1 OR $1");
        new_query = new_query.replace(/PMID:\s(\d+)/g, "PMID:$1 OR $1");

        new_query = new_query.replace(/:\B|\B:/g, "");

        // search for RRID strings without RRID prefix
        new_query = new_query.replace(/RRID:([a-zA-Z0-9_\-]+)/g, "$1");

        new_query = encodeQuery(new_query);

        // remove stop words
        var stop_words = ["of", "in", "the"];
        var nonQuotePositions = getNonQuotePositions(new_query);    // get all positions not quoted
        var removePositions = [];
        for(var i = 0; i < stop_words.length; i++) {
            var sw = stop_words[i];
            var re = new RegExp("\\b" + sw + "\\b", "g");
            var match;
            while(match = re.exec(new_query)) {
                if(nonQuotePositions.indexOf(match.index) != -1) removePositions.push({pos: match.index, sw: sw});  // add the stop word position to removePositions with the stop word
            }
        }
        removePositions = removePositions.sort(function(a,b) { return b.pos - a.pos; });    // sort in reverse order
        for(var i = 0; i < removePositions.length; i++) {
            var rp = removePositions[i];
            new_query = new_query.substr(0, rp.pos) + new_query.substr(rp.pos + rp.sw.length);  // remove the stop word
        }

        // convert out of quote ands and ors to upper case
        var nonQuotePositions = getNonQuotePositions(new_query);
        var operators = ["AND", "OR"];
        for(var i = 0; i < operators.length; i++) {
            var op = operators[i];
            var re = new RegExp("\\b" + op + "\\b", "gi");  // find all operators even if already upper case
            var match;
            while(match = re.exec(new_query)) {
                if(nonQuotePositions.indexOf(match.index) != -1) new_query = new_query.substr(0, match.index) + op + new_query.substr(match.index + op.length); // remove operator and replace with upper case version
            }
        }

        return new_query;

        function getNonQuotePositions(query) {
            var positions = [];
            var in_quote = false;
            for(var i = 0; i < query.length; i++) {
                if(query[i] == '"') {
                    in_quote = !in_quote;
                    continue;
                }
                if(!in_quote) positions.push(i);
            }
            return positions;
        }
    }
    $('.edit-body-btn').click(function () {
        var id = $(this).attr('componentID');
        var type = $(this).attr('componentType')
        $('.component-add-load').empty();
        if (type == 'body') {
            $('.component-add-load').load('/php/single-body-component-load.php?id=' + id, function () {
                $('.background').show();
                $('.component-add-load').show();
            });
        } else if (type == 'other') {
            $('.component-add-load').load('/php/other-components-load.php?id=' + id, function () {
                $('.background').show();
                $('.component-add-load').show();
            });
        } else if (type == 'data') {
            $('.background').show();
            $('.custom-form').show();
        }
    });
    $('.add-data-btn').click(function () {
        var id = $(this).attr('componentID');
        var cid = $(this).attr('cid')
        $('.component-add-load').empty();
        $('.component-add-load').load('/php/data-load.php?comp=' + id + '&cid=' + cid, function () {
            $('.background').show();
            $('.component-add-load').show();
        });
    });
    $('.article-delete-btn').click(function () {
        $('.background').show();
        $('.article-delete').show();
    })
    $('.component-delete-btn').click(function () {
        var id = $(this).attr('componentID');
        var comm = $(this).attr('community');
        $('#component-delete-form').attr('action', '/forms/component-forms/body-component-delete.php?component=' + id + '&cid=' + comm);
        $('.background').show();
        $('.component-delete').show();
    })

   // Javascript to enable link to tab
	var hash = document.location.hash;
	var prefix = "tab_";
	if (hash) {
		try {
			$('a[href='+hash.replace(prefix,"")+']').tab('show');
		} catch(e) {

		}
	}

	// Change hash for page-reload
	$('a').on('shown', function (e) {
		window.location.hash = e.target.hash.replace("#", "#" + prefix);
	});
}

$(function () {
    /* two different types of autocomplete boxes */
    var autocomplete_types = [
        {
            "selector": ".searchbar",
            "termselector": ".searchbar",
            "autoselector": "#autoValues",
            "appendselector": ".autocomplete_append",
        },
        {
            "selector": ".small-search",
            "termselector": "#small-search-auto",
            "autoselector": "#autoValues1",
            "appendselector": ".autocomplete_append1",
        },
    ];
    /* set up both of the autocomplete_types for autocomplete */
    $.each(autocomplete_types ,function(idx, ac) {
        if ($(ac.selector).length) {
            $(ac.selector).autocomplete({
                source: function (request, response) {
                    $.getJSON("/php/autocomplete.php", { term: $(ac.termselector).val(), display: $(ac.autoselector).val() }, function(results){
                        if(results) {
                            results.sort(function(a,b){
                                if(a[1] === b[1]) return 0;
                                if(a[1] === "Resource") return 1;
                                if(b[1] === "Resource") return -1;
                                return 0;
                            });
                        }
                        response(results);
                    });
                },
                appendTo: ac.appendselector,
                focus: function (event, ui) {
                    event.preventDefault();
                    return false;
                },
                messages: {
                    noResults: '',
                    results: function () {
                    }
                },
                select: function (event, ui) {
                    event.preventDefault();
                    var value = $(this).val();
                    var stripped_value = value.substr(0, value.length - ui.item[10].length);
                    var identifier = ui.item[2];
                    /* temporary for broken literature service */
                    //var autocomplete_component = "[" + ui.item[0] + " {" + identifier + "}]";
                    var autocomplete_component = ui.item[0];
                    if(autocomplete_component.indexOf(" ") != -1) autocomplete_component = '"' + autocomplete_component + '"';
                    /* /temporary */
                    $(this).val(stripped_value + autocomplete_component + " ");
                    $(this).data("old-val", $(this).val());
                    //$(ac.autoselector).val(ui.item[8]);
                    return false;
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                //alert(item);
                return $("<li></li>").data("ui-autocomplete-item", item).append("<a><b>" + decodeURIComponent(item[0]) + "</b><div class='float'>" + item[1] + " " + "<a href='http://neurolex.com/wiki/" + item[2] + "' target='_blank'>" + item[2] + "&nbsp;&nbsp;<span style='color:#7207c0'>" + item[9] + "</span></div></a>").appendTo(ul);
            };
            $(ac.selector).on("input", function() {
                /* make sure that if a user edits an entity, the entire entity is deleted */
                /* compares the old value with the new one, if the change is between square brackets [], then the entity is deleted */
                var old_value = $(this).data("old-val");
                var new_value = $(this).val();
                var inside_entity = false;
                var entity_start = 0;
                var entity_end = 0;
                var bad_change = false;
                for(var i = 0; i < old_value.length; i++) {
                    if(new_value.length < 3) break; // if user completely deletes text
                    if((old_value[i] == " " && new_value[i] == "[") || (old_value[i] == "[" && new_value[i] == " ")) break;   // deal with case when deleting space before entity
                    if(!inside_entity && old_value[i] == "[") {
                        inside_entity = true;
                        entity_start = i;
                    }
                    if(inside_entity && old_value[i] != new_value[i]) {
                        bad_change = true;
                        for(var j = i; j < old_value.length; j++) {
                            if(old_value[j] == "]") {
                                entity_end = j;
                                break;
                            }
                        }
                    }
                    if(bad_change) break;
                    if(inside_entity && old_value[i] == "]") {
                        inside_entity = false;
                    }
                }
                if(bad_change && entity_start < entity_end) {
                    var edited_value = old_value.slice(0, entity_start);
                    if(entity_end < old_value.length) edited_value += old_value.slice(entity_end + 1);
                    $(this).val(edited_value);
                    $(this).data("old-val", edited_value);
                } else {
                    $(this).data("old-val", new_value);
                }
            });
            $(ac.selector).on("focus", function() {
                /* set the initial old value for comparisons when user edits input field */
                $(this).data("old-val", $(this).val());
            });
        }
    });
});

$(function () {
    if ($('.field-autocomplete').length) {
	var tags = ["AFL-2.0", "AFL-2.1", "AGPL-3.0-only", "AGPL-3.0-or-later", "Apache-1.0", "Apache-1.1", "Apache-2.0", "Artistic-1.0-Perl", "BSD-2-Clause", "BSD-2-Clause-Patent", "BSD-3-Clause", "BSD-4-Clause", "BSD-4-Clause-UC", "BSL-1.0", "bzip2-1.0.5", "bzip2-1.0.6", "CC0-1.0", "CDDL-1.0", "CPL-1.0", "curl", "EFL-2.0", "EPL-1.0", "EPL-2.0", "EUPL-1.1", "FTL", "GPL-2.0-only", "GPL-2.0-only WITH Classpath-exception-2.0", "GPL-2.0-or-later", "GPL-3.0-only", "GPL-3.0-or-later", "HPND", "IBM-pibs", "ICU", "IJG", "IPL-1.0", "ISC", "LGPL-2.1-only", "LGPL-2.1-or-later", "LGPL-3.0-only", "LGPL-3.0-or-later", "Libpng", "libtiff", "MirOS", "MIT", "MIT-CMU", "MPL-1.1", "MPL-2.0", "MPL-2.0-no-copyleft-exception", "MS-PL", "MS-RL", "NBPL-1.0", "NTP", "OpenSSL", "OSL-3.0", "Python-2.0", "Qhull", "RPL-1.5", "SunPro", "Unicode-DFS-2015", "Unicode-DFS-2016", "UPL-1.0", "WTFPL", "X11", "XFree86-1.1", "Zlib", "zlib-acknowledgement", "AFL-2.0", "AFL-2.1", "AGPL-3.0-only", "AGPL-3.0-or-later", "Apache-1.0", "Apache-1.1", "Apache-2.0", "Artistic-1.0-Perl", "BSD-2-Clause", "BSD-2-Clause-Patent", "BSD-3-Clause", "BSD-4-Clause", "BSD-4-Clause-UC", "BSL-1.0", "bzip2-1.0.5", "bzip2-1.0.6", "CC0-1.0", "CDDL-1.0", "CPL-1.0", "curl", "EFL-2.0", "EPL-1.0", "EPL-2.0", "EUPL-1.1", "FTL", "GPL-2.0-only", "GPL-2.0-only WITH Classpath-exception-2.0", "GPL-2.0-or-later", "GPL-3.0-only", "GPL-3.0-or-later", "HPND", "IBM-pibs", "ICU", "IJG", "IPL-1.0", "ISC", "LGPL-2.1-only", "LGPL-2.1-or-later", "LGPL-3.0-only", "LGPL-3.0-or-later", "Libpng", "libtiff", "MirOS", "MIT", "MIT-CMU", "MPL-1.1", "MPL-2.0", "MPL-2.0-no-copyleft-exception", "MS-PL", "MS-RL", "NBPL-1.0", "NTP", "OpenSSL", "OSL-3.0", "Python-2.0", "Qhull", "RPL-1.5", "SunPro", "Unicode-DFS-2015", "Unicode-DFS-2016", "UPL-1.0", "WTFPL", "X11", "XFree86-1.1", "Zlib", "zlib-acknowledgement"];
        $(".field-autocomplete").autocomplete({
            source: function (request, response) {
		    var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( request.term ), "i" );
		    response( $.grep( tags, function( item ){
			return matcher.test( item );
		    }) );    
            },
            delay: 100,  // Manu
            appendTo: '.autocomplete_append',
            focus: function (event, ui) {
                event.preventDefault();
                var prev_val = $(this).val();
                prev_val = prev_val.substr(0, prev_val.lastIndexOf(","));
                var delim = ", ";
                if(prev_val.length === 0) delim = "";
                jQuery(this).val(prev_val + delim + ui.item.value);
                $(this).parent().children('.autoValues').val(ui.value);
                return false;
            },
            messages: {
                noResults: '',
                results: function () {
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                var prev_val = $(this).val();
                prev_val = prev_val.substr(0, prev_val.lastIndexOf(","));
                var delim = ", ";
                if(prev_val.length === 0) delim = "";
                jQuery(this).val(prev_val + delim + ui.item.value);
                $(this).parent().children('.autoValues').val(ui.item.value);
                return false;
            }
        }).data("ui-autocomplete")._renderItem = function (ul, item) {
         //   alert(item.value);
            return $("<li></li>").data("ui-autocomplete-item", item).append(item.value).appendTo(ul); // Manu -- commented below
            //return $("<li></li>").data("ui-autocomplete-item", item).append("<a><b>" + decodeURIComponent(item[0]) + "</b><div class='float'>" + item[1] + " " + "<a href='http://neurolex.com/wiki/" + item[2] + "' target='_blank'>" + item[2] + "&nbsp;&nbsp;<span style='color:#7207c0'>" + item[9] + "</span></div></a>").appendTo(ul);
        };
    }

    /* google analytics - track outbound clicks */
    (function() {
        var el = null;
        $("a, ga-download").each(function() {
            var hasDownloadClass = $(this).hasClass("ga-download");
            if(hasDownloadClass || ($(this)[0].host && $(this)[0].host !== window.location.host)) {

                var eventAction = "outbound";
                if(hasDownloadClass) {
                    eventAction = "download";
                }
                /* handler for normal left click */
                $(this).on("click", function() {
                    var url = $(this).prop("href");
                    var callback = null;
                    if($(this).prop("target") != "_blank") {
                        callback = function() { document.location = url; };
                    }
                    ga("send", "event", eventAction, "click", url, {
                        "transport": "beacon",
                        "hitCallback": callback
                    });
                    if(callback) {
                        return false;
                    }
                });

                /* handler for middle click */
                $(this).on("mousedown", function(e) {
                    if(e.which != 2) return;
                    el = $(this);
                });
                $(this).on("mouseup", function(e) {
                    if(e.which != 2) return;
                    if(el[0] === $(this)[0]) {
                        var url = $(this).prop("href");
                        ga("send", "event", eventAction, "click", url, {
                            "transport": "beacon"
                        });
                    }
                    el = null;
                });
            }
        });
    }());
});

var Validation = function () {

    return {

        //Validation
        initValidation: function () {
            $(".user-information-form").validate({
                // Rules for form validation
                rules: {
                    required: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    date: {
                        required: true,
                        date: true
                    },
                    min: {
                        required: true,
                        minlength: 5
                    },
                    range: {
                        required: true,
                        rangelength: [5, 10]
                    },
                    digits: {
                        required: true,
                        digits: true
                    },
                    number: {
                        required: true,
                        number: true
                    },
                    minVal: {
                        required: true,
                        min: 5
                    },
                    maxVal: {
                        required: true,
                        max: 100
                    },
                    rangeVal: {
                        required: true,
                        range: [5, 100]
                    },
                    url: {
                        url: true
                    }
                },

                // Messages for form validation
                messages: {
                    required: {
                        required: 'Please enter something'
                    },
                    email: {
                        required: 'Please enter your email address'
                    },
                    date: {
                        required: 'Please enter some date'
                    },
                    min: {
                        required: 'Please enter some text'
                    },
                    max: {
                        required: 'Please enter some text'
                    },
                    range: {
                        required: 'Please enter some text'
                    },
                    digits: {
                        required: 'Please enter some digits'
                    },
                    number: {
                        required: 'Please enter some number'
                    },
                    minVal: {
                        required: 'Please enter some value'
                    },
                    maxVal: {
                        required: 'Please enter some value'
                    },
                    rangeVal: {
                        required: 'Please enter some value'
                    },
                    url: {
                        url: 'Please enter a valid URL'
                    }
                },

                // Do not change code below
                errorPlacement: function (error, element) {
                    error.insertAfter(element.parent());
                }
            });
            $(".reg-page:not(.reg-page-style)").validate({
                // Rules for form validation
                rules: {
                    required: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    date: {
                        required: true,
                        date: true
                    },
                    min: {
                        required: true,
                        minlength: 5
                    },
                    range: {
                        required: true,
                        rangelength: [5, 10]
                    },
                    digits: {
                        required: true,
                        digits: true
                    },
                    number: {
                        required: true,
                        number: true
                    },
                    minVal: {
                        required: true,
                        min: 5
                    },
                    maxVal: {
                        required: true,
                        max: 100
                    },
                    rangeVal: {
                        required: true,
                        range: [5, 100]
                    },
                    url: {
                        url: true
                    }
                },

                // Messages for form validation
                messages: {
                    required: {
                        required: 'Please enter something'
                    },
                    email: {
                        required: 'Please enter your email address'
                    },
                    date: {
                        required: 'Please enter some date'
                    },
                    min: {
                        required: 'Please enter some text'
                    },
                    max: {
                        required: 'Please enter some text'
                    },
                    range: {
                        required: 'Please enter some text'
                    },
                    digits: {
                        required: 'Please enter some digits'
                    },
                    number: {
                        required: 'Please enter some number'
                    },
                    minVal: {
                        required: 'Please enter some value'
                    },
                    maxVal: {
                        required: 'Please enter some value'
                    },
                    rangeVal: {
                        required: 'Please enter some value'
                    },
                    url: {
                        url: 'Please enter a valid URL'
                    }
                },

                // Do not change code below
                errorPlacement: function (error, element) {
                    error.insertAfter(element.parent());
                }
            });
            $(".create-form").validate({
                // Rules for form validation
                rules: {
                    required: {
                        required: true
                    },
                    email: {
                        email: true
                    },
                    date: {
                        required: true,
                        date: true
                    },
                    min: {
                        required: true,
                        minlength: 5
                    },
                    range: {
                        required: true,
                        rangelength: [5, 10]
                    },
                    digits: {
                        required: true,
                        digits: true
                    },
                    number: {
                        required: true,
                        number: true
                    },
                    minVal: {
                        required: true,
                        min: 5
                    },
                    maxVal: {
                        required: true,
                        max: 100
                    },
                    rangeVal: {
                        required: true,
                        range: [5, 100]
                    },
                    url: {
                        url: true
                    }
                },

                // Messages for form validation
                messages: {
                    required: {
                        required: 'Please enter something'
                    },
                    email: {
                        required: 'Please enter your email address'
                    },
                    date: {
                        required: 'Please enter some date'
                    },
                    min: {
                        required: 'Please enter some text'
                    },
                    max: {
                        required: 'Please enter some text'
                    },
                    range: {
                        required: 'Please enter some text'
                    },
                    digits: {
                        required: 'Please enter some digits'
                    },
                    number: {
                        required: 'Please enter some number'
                    },
                    minVal: {
                        required: 'Please enter some value'
                    },
                    maxVal: {
                        required: 'Please enter some value'
                    },
                    rangeVal: {
                        required: 'Please enter some value'
                    },
                    url: {
                        url: 'Please enter a valid URL'
                    }
                },

                // Do not change code below
                errorPlacement: function (error, element) {
                    error.insertAfter(element.parent());
                }
            });
            $("#header-component-form").validate({
                // Rules for form validation
                rules: {
                    required: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    date: {
                        required: true,
                        date: true
                    },
                    min: {
                        required: true,
                        minlength: 5
                    },
                    range: {
                        required: true,
                        rangelength: [5, 10]
                    },
                    digits: {
                        required: true,
                        digits: true
                    },
                    number: {
                        required: true,
                        number: true
                    },
                    minVal: {
                        required: true,
                        min: 5
                    },
                    maxVal: {
                        required: true,
                        max: 100
                    },
                    rangeVal: {
                        required: true,
                        range: [5, 100]
                    },
                    url: {
                        url: true
                    }
                },

                // Messages for form validation
                messages: {
                    required: {
                        required: 'Please enter something'
                    },
                    email: {
                        required: 'Please enter your email address'
                    },
                    date: {
                        required: 'Please enter some date'
                    },
                    min: {
                        required: 'Please enter some text'
                    },
                    max: {
                        required: 'Please enter some text'
                    },
                    range: {
                        required: 'Please enter some text'
                    },
                    digits: {
                        required: 'Please enter some digits'
                    },
                    number: {
                        required: 'Please enter some number'
                    },
                    minVal: {
                        required: 'Please enter some value'
                    },
                    maxVal: {
                        required: 'Please enter some value'
                    },
                    rangeVal: {
                        required: 'Please enter some value'
                    },
                    url: {
                        url: 'Please enter a valid URL'
                    }
                },

                // Do not change code below
                errorPlacement: function (error, element) {
                    error.insertAfter(element.parent());
                }
            });
            $(".resource-form-validate").validate({
                // Rules for form validation
                rules: {
                    required: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    date: {
                        required: true,
                        date: true
                    },
                    min: {
                        required: true,
                        minlength: 5
                    },
                    range: {
                        required: true,
                        rangelength: [5, 10]
                    },
                    digits: {
                        required: true,
                        digits: true
                    },
                    number: {
                        required: true,
                        number: true
                    },
                    minVal: {
                        required: true,
                        min: 5
                    },
                    maxVal: {
                        required: true,
                        max: 100
                    },
                    rangeVal: {
                        required: true,
                        range: [5, 100]
                    },
                    Resource_URL: {
                        url: true
                    }
                },

                // Messages for form validation
                messages: {
                    required: {
                        required: 'Field cannot be empty'
                    },
                    email: {
                        required: 'Please enter your email address'
                    },
                    date: {
                        required: 'Please enter some date'
                    },
                    min: {
                        required: 'Please enter some text'
                    },
                    max: {
                        required: 'Please enter some text'
                    },
                    range: {
                        required: 'Please enter some text'
                    },
                    digits: {
                        required: 'Please enter some digits'
                    },
                    number: {
                        required: 'Please enter some number'
                    },
                    minVal: {
                        required: 'Please enter some value'
                    },
                    maxVal: {
                        required: 'Please enter some value'
                    },
                    rangeVal: {
                        required: 'Please enter some value'
                    },
                    url: {
                        url: 'Please enter a valid URL (ex. http://scicrunch.org)'
                    },
                    Resource_URL: {
                        url: 'Please enter a valid URL (ex. http://scicrunch.org)'
                    }
                },

                // Do not change code below
                errorPlacement: function (error, element) {
                    error.insertAfter(element.parent());
                }
            });
        }

    };
}();

function updateReview() {
    $('.resource-field').each(function () {
        var _this = $(this);
        var name = $(_this).attr('name');
        $('.review-' + name).val($(_this).val());
    });
    $('.form_class').submit(function (event) {
        event.preventDefault();
        if ($(".input_first").val() != "") {
            window.location = window.location.href.toString() + '&query=' + encodeURIComponent($(".input_first").val());
        }
    });
}

function updateLogin() {
    $('.note-load').load('/forms/updateLogin.php', function () {
        $('.notifications').html($('.note-load').html());
        setTimeout(function () {
            $('.notification-alert').fadeOut("slow");
        }, 3000);
    });
    setTimeout(updateLogin, 150000);
}

function handleSearchV1() {
    jQuery('.search-button').click(function () {
        jQuery('.search-open').slideDown();
    });

    jQuery('.search-close').click(function () {
        jQuery('.search-open').slideUp();
    });


}

function tagAutocomplete() {

}

function createCookie(name, val, minutes) {
    var expires = "";
    if(minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    document.cookie = name + "=" + val + expires + "; path=/";
}

function readCookie(name) {
    var name_eq = name + "=";
    var cookie_array = document.cookie.split(";");
    for(var i = 0; i < cookie_array.length; i++) {
        var cookie = cookie_array[i];
        while(cookie.charAt(0) == ' ') cookie = cookie.substring(1, cookie.length);
        if(cookie.indexOf(name_eq) === 0) return cookie.substring(name_eq.length, cookie.length);
    }
    return null;
}

function deleteCookie(name) {
    createCookie(name, "", -1);
}

function CSVtoArray(csv_data, delim) {
    var delimiter = delim || ",";
    var objPattern = new RegExp(
        (
        // Delimiters
        "(\\" + delimiter + "|\\r?\\n|\\r|^)" +
        // Quoted fields
        "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
        // Standard fields
        "([^\"\\" + delimiter + "\\r\\n]*))"
        ), "gi"
    );
    var arrData = [[]];
    var arrMatches = null;
    while(arrMatches = objPattern.exec(csv_data)) {
        var strMatchedDelimiter = arrMatches[1];
        if(strMatchedDelimiter.length && strMatchedDelimiter !== delimiter) {
            arrData.push([]);
        }
        var strMatchedValue;
        if(arrMatches[2]) {
            strMatchedValue = arrMatches[2].replace(new RegExp("\"\"", "g"), "\"");
        } else {
            strMatchedValue = arrMatches[3];
        }
        arrData[arrData.length - 1].push(strMatchedValue);
    }
    return arrData;
}

function mentionIDFormat(val) {
    return /(^PMID:[0-9]+|DOI:.+|PMC:.+)$/.test(val);
}

function copyToClipboard(text) {
    if(window.clipboardData && window.clipBoardData.setData) {
        return clipboardData.setData("Text", text); // for IE
    } else if(document.queryCommandSupported && document.queryCommandSupported("copy")) {
        var textarea_element = document.createElement("textarea");
        textarea_element.textContent = text;
        textarea_element.style.position = "fixed";
        document.body.appendChild(textarea_element);
        textarea_element.select();
        try {
            return document.execCommand("copy");
        } catch(e) {
            return false;
        } finally {
            document.body.removeChild(textarea_element);
        }
    }
}

function pageWorkflowNextStep(name, text, clear) {
    if(clear) {
        pageWorkflowClearAll();
    }
    $(".page-workflow-step[data-page-workflow-step='" + name + "']").each(function() {
        var width = $(this).outerWidth();
        var height = $(this).outerHeight();
        var element = '<div data-name="' + name + '" class="page-workflow-step-tooltip">' + text + '</div>';
        $(this).append(element);
    })
}

function pageWorkflowClear(name) {
    $('.page-workflow-step-tooltip[data-name="' + name + '"]').remove();
}

function pageWorkflowClearAll() {
    $(".page-workflow-step-tooltip").remove();
}
