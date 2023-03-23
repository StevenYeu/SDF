QUnit.test("/api/1/resource/mention/view/{rid}", function(assert){
    var base_url = "/api/1/resource/mention/view";
    $.get(base_url + "/SCR_007341").always(function(result){
        var data = result.data;
        assert.ok(data.length > 100, "got all data (should be around 372, but checking if at least 100)");
    });
    $.get(base_url + "/SCR_007341", {count: 10}).always(function(result){
        var data = result.data;
        assert.equal(data.length, 10, "got 10 results back with count option");
    });
    $.get(base_url + "/SCR_xxxxxx").always(function(result){
        var data = result.data;
        assert.equal(data, undefined, "bad results returned no data");
    });
});

QUnit.test("/api/1/resource/mention/view/{rid}/{mentionid}", function(assert){
    var base_url = "/api/1/resource/mention/view/SCR_007341/PMID:21037582";
    $.get(base_url).always(function(result){
        var data = result.data;
        assert.equal(data["mention"], "PMID:21037582", "fetched the right id");
    });
});

QUnit.test("/api/1/resource/mention/mark/{rid}/{mentionid}", function(assert){
    var base_url = "/api/1/resource/mention/mark/SCR_007341/PMID:21037582";
    var get_url = "/api/1/resource/mention/view/SCR_007341/PMID:21037582";
    $.post(base_url, {mark: "bad"}).always(function(result){
        $.get(get_url).always(function(result){
            var data = result.data;
            assert.equal(data['rating'], "bad", "marked bad");
            $.post(base_url, {mark: "good"}).always(function(result){
                $.get(get_url).always(function(result){
                    var data = result.data;
                    assert.equal(data['rating'], "good", "marked good");
                });
            });
        });
    });
});

QUnit.test("/api/1/resource/mention/vote/{rid}/{mentionid}", function(assert){
    var base_url = "/api/1/resource/mention/vote/SCR_007341/PMID:21037582";
    var get_url = "/api/1/resource/mention/view/SCR_007341/PMID:21037582";
    var count = undefined;
    $.post(base_url, {vote: "bad"}).always(function(result){
        $.get(get_url).always(function(result){
            var data = result.data;
            count = data['vote_good'];
            $.post(base_url, {vote: "good"}).always(function(result){
                $.get(get_url).always(function(result){
                    var data = result.data;
                    var new_count = data['vote_good'];
                    assert.equal(count + 1, new_count, "voting bad then good");
                })
            });
        });
    });
});

QUnit.test("/api/1/resource/fields/view/{rid}", function(assert){
    // volatile if database is reset
    var base_url = "/api/1/resource/fields/view/SCR_014027";
    $.get(base_url).always(function(result){
        var data = result.data.fields;
        var resource_name = data.filter(function(x){ if(x['field'] == "Resource Name") return true; })[0]["value"];
        assert.equal(resource_name, "Dodgers", "true for most recent");
    });
    $.get(base_url, {version: 7}).always(function(result){
        var data = result.data.fields; 
        var resource_name = data.filter(function(x){ if(x['field'] == "Resource Name") return true; })[0]["value"];
        assert.equal(resource_name, "Giants", "got previous version");
    });
});

QUnit.test("/api/1/resource/fields/edit/{rid}", function(assert){
    // volatile if database is reset
    var post_url = "/api/1/resource/fields/edit/SCR_014027";
    var get_url = "/api/1/resource/fields/view/SCR_014027";
    $.post(post_url, {"Resource Name": "Dodgers", "Description": "Puig"}).always(function(result){
        $.get(get_url).always(function(result){
            var data = result.data.fields;
            var resource_desc = data.filter(function(x){ if(x['field'] == "Description") return true; })[0]["value"];
            assert.equal(resource_desc, "Puig", "Changed description");
            $.post(post_url, {"Resource Name": "Dodgers", "Description": "Best in NL West"}).always(function(result){
                $.get(get_url).always(function(result){
                    var data = result.data.fields;
                    var resource_desc = data.filter(function(x){ if(x['field'] == "Description") return true; })[0]["value"];
                    assert.equal(resource_desc, "Best in NL West", "Changed description back");
                });
            });
        });
    });
});

QUnit.test("/api/1/resource/rel --normal", function(assert){
    var add_url = "/api/1/resource/rel/add/SCR_007341";
    var delete_url = "/api/1/resource/rel/del/SCR_007341";
    var get_url = "/api/1/resource/rel/view/SCR_007341";
    $.post(add_url, {id1: "SCR_014027", id2: "SCR_007341", type: "res", relationship: "duplicated by"}).always(function(result){
        $.get(get_url).always(function(result){
            var data = result.data;
            var matches_added = data.filter(function(x) { return x["id2"] == "SCR_007341"; });
            assert.equal(matches_added.length, 1, "added a single relationship");
            $.post(delete_url, {id1: "SCR_014027", id2: "SCR_007341", type: "res", relationship: "duplicated by"}).always(function(result){
                $.get(get_url).always(function(result){
                    var data = result.data;
                    var matches_added = data.filter(function(x) { return x["id2"] == "SCR_007341"; });
                    assert.equal(matches_added.length, 0, "deleted a single relationship");
                });
            });
        });
    });
});

QUnit.test("/api/1/resource/owner/{rid}/del", function(assert){
    var add_url = "/api/1/resource/owner/SCR_007341/add";
    var get_all_url = "/api/1/resource/owner/SCR_007341";
    var check_url  = "/api/1/resource/owner/SCR_007341/check";
    var delete_url = "/api/1/resource/owner/SCR_007341/del";

    $.get(check_url).always(function(result){
        var data = result.data;
        assert.ok(data === true, "I am authorized owner (because I'm a curator)");
    });

    $.get(get_all_url).always(function(result){
        var data = result.data;
        assert.equal($.inArray(31559, data), -1, "i'm not a resource owner");
        $.post(add_url, {uid: 31559}).always(function(result){
            $.get(get_all_url).always(function(result){
                var data = result.data;
                assert.notEqual($.inArray(31559, data), -1, "i've been added as a resource owner");
                $.post(delete_url, {uid: 31559}).always(function(result){
                    $.get(get_all_url).always(function(result){
                        var data = result.data; 
                        assert.equal($.inArray(31559, data), -1, "i've been deleted as a resource owner");
                    });
                });
            });
        });
    });
});
