/*
son of suckerfish menu script from:
http://www.htmldog.com/articles/suckerfish/dropdowns/
 */

sfHover=function(){
	var sfEls=document.getElementById("nav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
			this.style.zIndex=200; //this line added to force flyout to be above relatively positioned stuff in IE
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}

if (window.attachEvent) window.attachEvent("onload", sfHover);



function applyTheme(){
  // insert font scripts required in the head of the document
	$("head").append('<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu|Merriweather">');
	$("head").append('<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">');
  // add classes for styling
  $(".minorLinks, #wrap, #header, #content-wrap, #footer").addClass("row expanded");
  $("#header, #footer").addClass("column");
  $("#header-logo").addClass("small-12 medium-7 columns");
  // finder classes
  $("#header-finder").addClass("small-12 medium-5 columns");
  $("#header-finder").find("table tr:nth-child(2)").addClass("row");
  $("#header-finder").find("table tr:nth-child(2) td:nth-child(1)").addClass("find-input small-9 columns");
  $("#header-finder").find("table tr:nth-child(2) td:nth-child(2)").addClass("find-button small-3 columns");
  // menu classes
  $("#header-menu").addClass("small-12 medium-4 large-3 columns");
  $("#content").addClass("small-12 large-9 columns");
  $.each($("body").find("table"), function(){
    if($(this).data("responsive") == true) $(this).addClass("dataTable");
    if($(this).data("fold")== true) $(this).addClass("foldTable");
  });
  // new sidebar
  var menu = $("#wrap").find("#header-menu").detach();
  var sidebar = $("#wrap").find("#sidebar").detach();
  menu.append(sidebar);
  $("#content-wrap").prepend(menu);
  $("#content-wrap").addClass("collapse");
  // set height of sidebar same as height of content
  $("#content-wrap").attr("data-equalize-on","large");
  $("#content-wrap").attr("data-equalizer", "");
  menu.attr("data-equalizer-watch", "");
  $("#content").attr("data-equalizer-watch", "");
  //style the menu
  $("#header-menu").prepend('<div class="title-bar" data-responsive-toggle="nav" data-hide-for="medium"><button class="menu-icon" type="button" data-toggle></button><div class="title-bar-title">Menu</div></div>');
  $("#nav").addClass("vertical menu");
  $("#nav").attr("data-responsive-menu", "drilldown");
  $.each($("#nav").find("ul"), function(){
    $(this).addClass("vertical menu");
  });


}

var smallBreak = 640; // Your small screen breakpoint in pixels
var columns = $('.dataTable tr').length;
var rows = $('.dataTable th').length;

function foldTable(){
  // create accodion from table
  $('.foldTable').find('.break').each(function(index){
    $(this).attr('id', 'panel00'+index);
    $(this).addClass('panel-header');
    $(this).children('td').append('<span class="collapse-controls right"><i class="fa fa-angle-down"></i></span>');
    var panelContent = document.createElement('div');
    $(panelContent).attr('id', 'content00'+index);
    $(panelContent).addClass('panel-content');
    $(panelContent).append($(this).nextUntil('.break'));
    $(panelContent).css('display', 'none');
    $(this).after(panelContent);
  });
  $('.panel-header').on('click', function(){
    $(this).next().slideToggle();
    $(this).find('.collapse-controls').children('i').toggleClass('fa-angle-down fa-angle-up');
  });
}

function shapeTable() {
    if ($(window).width() < smallBreak) {
        for (i=0;i < rows; i++) {
            var maxHeight = $('.dataTable th:nth-child(' + i + ')').outerHeight();
            for (j=0; j < columns; j++) {
                if ($('.dataTable tr:nth-child(' + j + ') td:nth-child(' + i + ')').outerHeight() > maxHeight) {
                    maxHeight = $('.dataTable tr:nth-child(' + j + ') td:nth-child(' + i + ')').outerHeight();
                }
                if ($('.dataTable tr:nth-child(' + j + ') td:nth-child(' + i + ')').prop('scrollHeight') > $('.dataTable tr:nth-child(' + j + ') td:nth-child(' + i + ')').outerHeight()) {
                    maxHeight = $('.dataTable tr:nth-child(' + j + ') td:nth-child(' + i + ')').prop('scrollHeight');
                }
            }

            for (j=0; j < columns; j++) {
                $('.dataTable tr:nth-child(' + j + ') td:nth-child(' + i + ')').css('height',maxHeight);
                $('.dataTable th:nth-child(' + i + ')').css('height',maxHeight);
            }
        }
    } else {
        //$('.dataTable td, .dataTable th').removeAttr('style');
    }
}

// after the body has loaded insert required scripts
$(window).bind("load", function() {


  applyTheme();


	$(document).ready(shapeTable());
	$(document).ready(foldTable());
  $(window).resize(function(){
    shapeTable()
  });
  $(document).foundation();
});
