// JavaScript Document




$(function($){
	
	//Report page pop up collaps in map
	$('area').mousemove(function(event){
		var areaids=$(this).attr('alt');
		$('#statdatapopup').attr('class','');
		$('#statdatapopup').addClass('statdatapopup '+areaids);
		$('#statdatapopup').show();
		})
		$('area').mouseout(function(){
				$('.statdatapopup').hide();
			});
	
	//add ids run time on graph path
	$( "tspan" ).each(function( index ) {
 		 $(this).attr('id','tspan'+index);
	});
	
	$("#mainCaptchanew").prev('label').css('display','block');
	
	
	
	
	//input type file buttion design
	
	$('input[type="file"]').before("<a class='filebrowse' href='javascript:void(0);'>Choose File</a>"); 
	$('input[type="file"]').after("<span id='filename'>No file chosen</span>"); 	
	$('input[type="file"]').change(function(){
		var  input_id = $(this).attr('id');
		$(this).parent().addClass(input_id);
		$("."+input_id+" #filename").html($(this).val());
	});
	$('.record_box').removeClass('col-xs-6');
	
	
	/* $('input[type="file"]').before("<a class='filebrowse' href='javascript:void(0);'>Choose File</a>"); 
	$('input[type="file"]').after("<span id='filename'>No file chosen</span>"); 	
	$('input[type="file"]').change(function(){$("#filename").html($(this).val())});
	$('.record_box').removeClass('col-xs-6'); */
	
	//table add class
	$('table').removeClass('table');
	$('table').addClass('table');
	$('.cntnt').addClass('table-responsive');
	$('#accordion table').wrap('<div class="table-responsive"></div>');
	$('h2.col-sm-12').removeClass('col-sm-12');
	$('#frmfeedback').parents('.col-sm-12.col-md-9').wrapInner('<div class="row"></div>');
		$(".nav.navbar-nav li a").load(function(){
		$(this).parent().addClass('active');
		
		});
	
	
	//toogle  Consolidated Report  and Month Wise Analysis Report
    $("[name=toggler]").click(function(){
            $('.toHide').hide();
            $("#blk-"+$(this).val()).show('slow');
    });
	
	        //User detail collaps
		$('.uasername').parent('div').hover(function(){
			$('.user-detail').hide();
			});
	$('.uasername li span').mouseenter(function(){
		$('.user-detail').toggle();
		});
		

	//bootstrap hover popup
	//$(document).ready(function(e) {
    		$('ul.nav li.dropdown, .export-button').hover(function() {
  		$(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
		
		 var displayVal=$('.dropdown-menu').css('display');
		if(displayVal=='none')
			{
				//$('.dropdown-menu').parent('li').addClass('open');
				}
				
	}, function() {
 		 $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
		   var displayVal=$('.dropdown-menu').css('display');
if(displayVal=='block')
			{	
				//$('.dropdown-menu').parent('li').removeClass('open');
				}
		});
		
		
	//});


	//color contrast theme color change

	if($('body').hasClass('contrast'))
	{
		$('.department img').attr('src','/images/star-c.png');
		$('.scheme img').attr('src','/images/icon-1-c.png');
		$('.fund_re img').attr('src','/images/rupee_2_c.png');
		$('.rightemblem').attr('src','/images/indian_emblem_c.png');
		/*$('#one').attr('src','/images/banner1_c.jpg');
		$('#two').attr('src','/images/banner3_c.jpg');
		$('#three').attr('src','/images/banner1_c.jpg');*/
		$('.aadharbasepayment img').attr('src','/images/aadhar_base_graph_bg_c.png');
		$('.beneficary img').attr('src','/images/beneficiary_graph_bg_c.png');
		$('.bene_re img').attr('src','/images/icon-2-c.png');
		$('.scheme_re img').attr('src','/images/icon-3-c.png');
	}
		
	
	
	
	
	//Home page beneficiary graph background color change.
	$('.beneficary path').attr('fill','rgba(227,223,211,1)');
	$('.beneficary path+path').attr('fill','rgba(244,240,227,1)');
	$('.beneficary path+path+path').attr('fill','rgba(227,223,211,1)');
	
	//start customize landing page beneficary graph 
	
	
	$('.beneficary-graph .highcharts-container').css({'height':'','width':'','top':'-20px'});
	$('.beneficary-graph .highcharts-container svg').attr('width','265')
	$('.beneficary-graph .highcharts-container svg').attr('height','280')
	
	//end customize landing page beneficary graph 

	//start customize landing page beneficary graph 
	$('.aadhar-payment-bridge-graph .highcharts-container').css({'height':'','width':'','top':'0px','right':'0px','position':'absolute'});
	$('.aadhar-payment-bridge-graph .highcharts-container svg').attr('width','265')
	$('.aadhar-payment-bridge-graph .highcharts-container svg').attr('height','295')
	//end customize landing page beneficary graph 
	
var disclamer=parseInt($('.highchartseeded-axis text, .highchartseededCategory-axis text, .highchartscheme-axis text').attr('y'))+80;

$('.highchartseeded-axis text, .highchartseededCategory-axis text, .highchartscheme-axis text').attr('x','70');

$('.highchartseeded-axis text, .highchartseededCategory-axis text, .highchartscheme-axis text').attr('y',disclamer);
	
	
	
	
	//states district pop ups.
	 $('#map ul.dbtmap li a').attr('title','');
	$('#map ul.dbtmap li a span').each(function(index, element) {
       $(this).mouseenter(function(){
		   var getDistrictPostionTop=parseInt($(this).parents('li').css('top'))-115+'px';
		  var getDistrictPostionLeft=parseInt($(this).parents('li').css('left'))+15-115+'px';
		$('.statdatapopup').css({'display':'block',
			'top': getDistrictPostionTop,
			'left':getDistrictPostionLeft
		});
	});$(this).mouseleave(function(e) {
        $('.statdatapopup').css({'display':'none'});
    });;
	
    });		 
	
/*MPR table fixed on top start*/

	//=========================================================
	// var winheight=$(window).outerHeight();
	// var theadheight=$('#fixedheader thead').outerHeight();
	// var remainheight=(winheight-theadheight);
	// $('#fixedheader th,#fixedheader td, #fixedheaderinkind th, #fixedheaderinkind td, #fixedheaderserviceenablers th, #fixedheaderserviceenablers td').wrapInner('<span></span>');
	// $(window).scroll(function (event) {
   		// var scrolls = $(window).scrollTop();
		// var tableid=$('table').attr('id');
		// if(tableid=='fixedheader'){var outerdiv = $('#offsettop').offset().top;}
			// if(scrolls>=outerdiv)
			// {
				// $('#fixedheader tbody, #fixedheaderinkind tbody').css('height',remainheight-150);
				// $('.fixscroll').addClass('fix fixscroll');
				// $('#offsettop').css('padding-bottom',(remainheight+theadheight));
			// }
			// else if(scrolls<outerdiv){
				// $('#fixedheader tbody, #fixedheaderinkind tbody').css('height','inherit');
				// $('.fixscroll').removeClass('fix');
				// $('#offsettop').css('padding-bottom','inherit');
				// }
			
	// });
	//============================================================
	
	var winheight = $(window).outerHeight();
    var theadheight = $('#fixedheader thead').outerHeight();
	var fixedheaderinkind = $('#fixedheaderinkind thead').outerHeight();
	var fixedheadercashinkind = $('#fixedheadercashinkind thead').outerHeight();
	var serviceenablersdata = $('#fixedheaderserviceenablers thead').outerHeight();
    var remainheight = (winheight - theadheight);
	var inkinddata = (winheight - fixedheaderinkind);
	var cashinkinddata = (winheight - fixedheadercashinkind);
	var serviceenablersdata = (winheight - serviceenablersdata);
	
    $('#fixedheader th,#fixedheader td, #fixedheaderinkind th, #fixedheaderinkind td, #fixedheadercashinkind th, #fixedheadercashinkind td, #fixedheaderserviceenablers th, #fixedheaderserviceenablers td').wrapInner('<span></span>');
	if($('#dvData').hasClass('fixscroll'))
	{
		$('#footer').css({'display':'none'});
		$('.fixscroll tbody #footer').css({'display':'block'});
	}
	//var thCound=$('.fixscroll table thead th').each(function(){$(this).length})
	//console.log(thCound);
	var footerDaata=($('#footer').html());
	var winWidth=$(window).width()-30;
	$('.fixscroll tbody').append('<tr><td colspan="15" style="padding:0px;line-height:normal;border-width:0px;"><footer id="footer" style="width:'+winWidth+'px;margin-left:15px;">'+footerDaata+'</footer></td></tr>');
    $(window).scroll(function(event) {
	var winheight = $(window).outerHeight();
    var theadheight = $('#fixedheader thead').outerHeight()+75;
	var fixedheaderinkind = $('#fixedheaderinkind thead').outerHeight()+75;
	var fixedheadercashinkind = $('#fixedheadercashinkind thead').outerHeight()+75;
	var serviceenablersdata = $('#fixedheaderserviceenablers thead').outerHeight()+75;
    var remainheight = (winheight - theadheight);
	var inkinddata = (winheight - fixedheaderinkind);
	var cashinkinddata = (winheight - fixedheadercashinkind);
	var serviceenablersdata1 = (winheight - serviceenablersdata);
        var scrolls = $(window).scrollTop();
        var tableid = $('table').attr('id');
        if (tableid == 'fixedheader') {
            var outerdiv = $('#offsettop').offset().top;
        }
        if (scrolls >= outerdiv) {
            //$('#fixedheader tbody, #fixedheaderinkind tbody').css('height', remainheight - 150);
            $('#fixedheader tbody').css('height', remainheight);
			$('#fixedheaderinkind tbody').css('height', inkinddata);
			$('#fixedheadercashinkind tbody').css('height', cashinkinddata);
			$('#fixedheaderserviceenablers tbody').css('height', serviceenablersdata);
            $('.fixscroll').addClass('fix fixscroll');
            $('#offsettop').css('padding-bottom', (remainheight + theadheight));
            $('#offsettop').css('padding-bottom', (inkinddata + fixedheaderinkind));
            $('#offsettop').css('padding-bottom', (cashinkinddata + fixedheadercashinkind));
            $('#offsettop').css('padding-bottom', (serviceenablersdata1 + serviceenablersdata));
        } else if (scrolls < outerdiv) {
            //$('#fixedheader tbody, #fixedheaderinkind tbody').css('height', 'inherit');
			 $('#fixedheader tbody').css('height', 'inherit');
			$('#fixedheaderinkind tbody').css('height', 'inherit');
			$('#fixedheadercashinkind tbody').css('height', 'inherit');
			$('#fixedheaderserviceenablers tbody').css('height', 'inherit');
            $('.fixscroll').removeClass('fix');
            $('#offsettop').css('padding-bottom', 'inherit');
        }
    });
	//============================================================
	
	
	//Security blocked F12, crtl+shift+i, ctrl U and right click.
	$(document).keydown(function(event){
		if(event.keyCode==123){
			//return false;//Prevent from F12
		}
		else if(event.ctrlKey && event.shiftKey && event.keyCode==73 || event.ctrlKey && event.keyCode==85){        
			//return false;  //Prevent from ctrl+shift+i and Prevent from ctrl U
		}
	});
	
	 $("html").on("contextmenu",function(e){
      // return false; // Prevent from right click
    }); 
	
	/*MPR table fixed on top end*/

	//Total Direct Benefit Transfer (FY 2017-18) Map upper right conner start
	$(".fund-button-collapes").click(function(){
		$(this).toggleClass("white");
		$("#scheme_wise_fund").toggleClass("hide");
		
	});
	
	//Total Direct Benefit Transfer (FY 2017-18) Map upper right conner end

$(window).on('load', function() {
        $('#uidaiModalShow').modal('show');
    });


	})
	
	//Union tritory Tabs for center scheme and state scheme
       $('#statescheme').hide(); 
	   $('#central-tab-transfer-result,#state-tab-transfer-result').hide();
	   //first tab
	   $('.centralAndstatescheme li a').click(function(e) {
         $('#statescheme,#centralscheme').hide();
		 $('.centralAndstatescheme li a').removeClass('active');
		 $(this).addClass('active');
		 var getIds=$(this).attr('title');
		 $(getIds).show();
    });
	
	//Union tritory  Radio button tab. inside center scheme and state scheme tab. 
		$('input').click(function(e) {
			var getInputId=$(this).attr('id');
			//alert(getInputId);
           if(getInputId=="central-tab-beneficiaries")
		   {	
		   
		   $('#central-tab-transfer-result').hide();
			   $('#central-tab-beneficiaries-result').show();
			   }; 
			   
			 if(getInputId=='central-tab-transfer')
			  {
			   $('#central-tab-beneficiaries-result').hide();
			   $('#central-tab-transfer-result').show();
			   }; 
			   
			   if(getInputId=='state-tab-beneficiaries')
			  {
				  $('#state-tab-transfer-result').hide();
			   $('#state-tab-beneficiaries-result').show();
			   
			   }; 
			   
			   
			    if(getInputId=='state-tab-transfer')
			  {
			   $('#state-tab-beneficiaries-result').hide();
			   $('#state-tab-transfer-result').show();
			   };  
			   
        });
		
		
	//sticky footer
	var skipContent=parseInt($('div.skipContent').outerHeight());
	var header=parseInt($('header').outerHeight());
	var skipContent=parseInt($('nav.navbar.navbar-default').outerHeight());
	var footer=parseInt($('footer').outerHeight());
	var getTopHeight=skipContent + header + skipContent + footer;
	var windowHeight=$(window).outerHeight();
	var contentHeight=windowHeight-getTopHeight;
	$('#mainContant, #mainContant_id').css({'min-height':contentHeight})
	
	
	//alert massage for external link
	$('a').bind('click', function(){
	var $str=$(this).attr('href');
	var $value=$str.indexOf("http");
	if($value==0)
	{
		var $con=confirm("This is external link, Are you sure you want to continue?");
		if($con==0)
		{return false;	}
	
	}
	});	
	
	
	
	
	
	
	//Font-size increase and descrease
	 $(function () {
	
		
			/*$('h1').css("font-size","20px");
			$('h2').css("font-size","17px");
			$('h3').css("font-size","15px");
			$('h4').css("font-size","13px");
			$('h5').css("font-size","12px");
			$('h6').css("font-size","11px");
			$('body,a,input,textarea,select,td,th,p,span,div,ul,li').css("font-size","13px");*/
			var size = parseInt($('a,input,textarea,select,td,th,p,span,div,ul,li').css("font-size"));
			var h1size = parseInt($('h1').css("font-size"));
			var h2size = parseInt($('h2').css("font-size"));
			var h3size = parseInt($('h3').css("font-size"));
			var h4size = parseInt($('h4').css("font-size"));
			var h5size = parseInt($('h5').css("font-size"));
			var h6size = parseInt($('h6').css("font-size"));
		   //alert(size);
			
			 $("ul.font-zoom li.round-icons a").bind("click", function () {
			 //Manipulating font size of following HTML tag body,a,input,textarea,select,td,th,p,span,div
			
			 if ($(this).hasClass("a-plus")) {
				// alert(size);
                size = size + 2;
				//alert(size);
				 if (size >= 20) {
                    size = 20;
                }
                $('a,input,textarea,select,td,th,p,span,div,ul,li').css("font-size", size);
            } else if ($(this).hasClass("a-minus")) {
                size = size - 2;
                if (size <= 8) {
                    size = 8;
                }
                $('a,input,textarea,select,td,th,p,span,div,ul,li').css("font-size", size);
            } else if ($(this).hasClass("clsBold")) {
               // $('body').css("font-weight", "Bold");
            } else if ($(this).hasClass("clsItalic")) {
               // $('body').css("font-style", "italic");
            }
            else if ($(this).hasClass("a-normal")) {
                location.reload();
				//$('a,input,textarea,select,td,th,p,span,div,ul,li').css("font-size","");
                //$('body,a,input,textarea,select,td,th,p,span,div').css("font-style", "normal");
                //$('body,a,input,textarea,select,td,th,p,span,div').css("font-weight", "normal");
            }
			
			
			
			//Manipulating font size of H1 tag
			if($(this).hasClass('a-plus')){
				 h1size = h1size+2;
				if(h1size>=24){
				h1size = 24;
				}
			$('h1').css('font-size',h1size);
			}
			
			else if($(this).hasClass('a-minus')){
				 h1size = h1size-2;
				if(h1size<=14){
				 h1size = 14;
				}
				$('h1').css("font-size",h1size);
			}
			
			else if($(this).hasClass('a-normal')){
				 location.reload();
			}
		 	
			
			//Manipulating font size of H2 tag
			  if($(this).hasClass('a-plus')){
			  h2size = h2size + 2;
			
			  var h2maxsize=h2size+'4';
				  if (h2size >=h2maxsize){
				  	
					//h2size = 22;
				  }
				  var h2sizes=h2size+" "+"!important";
				
			  $('h2').css({"font-size":h2sizes});
			  }
		  else if ($(this).hasClass('a-minus')){
		  		 h2size = h2size - 2;
			  if (h2size <=12){
				  	h2size = 12;
				  } 
				   var h2sizes=h2size+" !important";
		  	$('h2').css("font-size",h2sizes);
		  }
		  
		  else if($(this).hasClass('a-normal')){
				
		  	 location.reload();
				}
			
			
			//Manipulating font size of H3 tag
			if($(this).hasClass('a-plus')){
				h3size	= 	h3size+2;
				if(h3size>=20){
					h3size	= 	20;
				}
				$('h3').css('font-size',h3size);
			}
			
			else if($(this).hasClass('a-minus')){
				h3size = h3size-2;
				if(h3size<=10){
					h3size = 10;	
				}
				$('h3').css('font-size',h3size);
			}
			
			else if($(this).hasClass('a-normal')){
				 location.reload();
			}
			
			//Manipulating font size of H4 tag
			if($(this).hasClass('a-plus')){
				h4size	= 	h4size+2;
				if(h4size>=16){
					h4size	= 	16;
				}
				$('h4').css('font-size',h4size);
			}
			
			else if($(this).hasClass('a-minus')){
				h4size = h4size-2;
				if(h4size<=10){
					h4size = 10;	
				}
				$('h4').css('font-size',h4size);
			}
			
			else if($(this).hasClass('a-normal')){
				 location.reload();
			}
			
			//Manipulating font size of H5 tag
			if($(this).hasClass('a-plus')){
				h5size	= 	h5size+2;
				if(h5size>=15){
					h5size	= 	15;
				}
				$('h5').css('font-size',h5size);
			}
			
			else if($(this).hasClass('a-minus')){
				h5size = h5size-2;
				if(h5size<=10){
					h5size = 10;	
				}
				$('h5').css('font-size',h5size);
			}
			
			else if($(this).hasClass('a-normal')){
				 location.reload();
			}
			
			//Manipulating font size of H6 tag
			if($(this).hasClass('a-plus')){
				h6size	= 	h6size+2;
				if(h6size>=14){	
					h5size	= 	14;
				}
				$('h6').css('font-size',h6size);
			}
			
			else if($(this).hasClass('a-minus')){
				h6size = h6size-2;
				if(h6size<=9){
					h5size = 9;	
				}
				$('h6').css('font-size',h6size);
			}
			
			else if($(this).hasClass('a-normal')){
				 location.reload();
			}
			
			
			 $('ul.font-zoom ,div.fund-cumulative,div.fund-cumulative span, ul.font-zoom li ,.skipContent ,ul.font-zoom li a, ul.font-zoom li select,.fundTransfer span, .fundTransfer h2,.fundTransferspan span, .fundTransferspan h2, .aadharbasepayment h2, .aadharbasepayment h2 span, .saving, .saving span, .department, .department span, .scheme, .scheme span,.navbar-brand span,.navbar-brand small').css("font-size","");
        });
	
    });

 
