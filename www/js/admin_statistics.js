// JavaScript Document

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *    Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

function quotabars() {
    var q = $('#page.admin_page .statistics_section .host_quota');
    if(!q.length) return;

    var quota = {
        total: parseInt(q.attr('data-total')),
        used: parseInt(q.attr('data-used')),
        available: parseInt(q.attr('data-available'))
    };

    var bar = $('<div class="progressbar quota" />').insertAfter(q);
    $('<div class="progress-label" />').appendTo(bar);
    bar.progressbar({
        value: false,
        max: 1000,
        change: function() {
            var bar = $(this);
            var v = bar.progressbar('value');

            var classes = [];

            var pct = parseInt(v / 10);

            var tens = parseInt(pct / 10);
            if(tens) classes.push('quota_' + tens + '0');

            if(pct % 10 >= 5) classes.push('quota_plus_5');

            bar.find('.progress-label').text((v / 10).toFixed(1) + '%');
            bar.addClass(classes.join(' '));
        },
        complete: function() {
            var bar = $(this);
            bar.find('.progress-label').text(lang.tr('full'));
        }
    });

    bar.progressbar('value', Math.floor(1000 * quota.used / quota.total));

    var info = lang.tr('quota_usage').r(quota);
    bar.find('.progress-label').text(info);

    q.remove();
}

function graph(g) {
    if (!$("#graph_"+g).length) return;
    $("#graph_"+g).html('<tr><td class="text-center"><strong>Loading...</strong><br><div class="spinner-grow m-5" role="status"></div></td></tr>');
    $.ajax({
        url: "js/graph/statistics_"+g+"_graph.php"+$(location).attr('search')
    }).done(function(json) {
        $("#graph_"+g).html('<canvas id="graph_canvas_'+g+'" height="200"></canvas>');
        var graph = new Chart($("#graph_canvas_"+g),$.parseJSON(json));
    });
}

function table(t,start=0,sort='',sortdirection=0) {
    if (!$("#"+t).length) return;
    $("#nav_"+t).remove();

    spinner_width = Math.max(100,$("#"+t)[0].clientWidth/3);
    spinner_height = Math.max(spinner_width,$("#"+t)[0].clientHeight);
    $("#"+t).html('<tr><td class="text-center"><strong>Loading...</strong><br><div id="spinner_'+t+'" class="spinner-grow" role="status"></div></td></tr>');
    $("#spinner_"+t).width(spinner_width).height(spinner_width);
    if (spinner_height>spinner_width) {
        var m = Number((spinner_height-spinner_width)/2).toString()+"px";
        $("#spinner_"+t).css("margin-top", m).css("margin-bottom", m);
    }

    $.ajax({
        url: "lib/tables/statistics_page.php"+$(location).attr('search')+"&t="+t+"&start="+start+"&sort="+sort+"&sortdirection="+sortdirection
    }).done(function(rows) {
        $("#"+t).html(rows);

        var attr = $("#"+t+" tr:first").attr('sort');
        if (typeof attr !== 'undefined' && attr !== false) {
            var sort=$("#"+t+" tr:first")[0].attributes['sort'].value;
            $("#"+t+" tr:first th").each(function(){
                var key = $(this)[0].attributes['sort'].value;
                var text = $(this).text();
                if (sort == key) {
                    if (sortdirection) {
                        text='<u>'+text+'</u> <i class="fa fa-sort-desc" aria-hidden="true"></i>';
                    } else {
                        text='<u>'+text+'</u> <i class="fa fa-sort-asc" aria-hidden="true"></i>';
                    }
                }
                var span = $("<span>", {
                    html: text,
                    id: t+"_"+key
                });
                span.on("click",function() {
                    if (sort==key && sortdirection==0) {
                        sortdirection=1;
                    } else {
                        sortdirection=0;
                    }
                    table(t,start,key,sortdirection);
                });
                $(this).html('');
                $(this).append(span);
            });
        }

        $("#"+t).after('<div id="nav_'+t+'" class="table-nav"></div>');
        var trs=$("#"+t+" tr");
        if (parseInt(trs[1].attributes['data-row'].value)>0) {
            $("#nav_"+t).append('<span id="nav_'+t+'_back" class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-angle-left fa-stack-1x fa-inverse"></i></span>');
            $("#nav_"+t+"_back").click(function(){
                table(t,2*parseInt(trs[1].attributes['data-row'].value)-parseInt(trs[trs.length-1].attributes['data-row'].value)-1);
            });
        }
        if (!trs[trs.length-1].attributes['data-row-blank']) {
            $("#nav_"+t).append('<span id="nav_'+t+'_forward" class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-angle-right fa-stack-1x fa-inverse"></i></span>');
            $("#nav_"+t+"_forward").click(function(){
                table(t,parseInt(trs[trs.length-1].attributes['data-row'].value)+1);
            });
        }
    });
}

$(function() {
    quotabars();

    $("#idpselect").select2();
    $("#idpbutton").click(function(){
        $(location).prop('href', '?s=statistics&idp='+$("#idpselect").val());
    });

    graph("transfers_vouchers");
    graph("transfers_speeds");
    graph("data_per_day");
    graph("encryption_split");
    $(".graph").delay(800).animate({height:400}, 1000, "easeOutSine")

    table("top_users");
    table("transfer_per_user");
    table("mime_types",0,'',1);
    table("users_with_api_keys",0,'',1);
    table("browser_stats");
});
