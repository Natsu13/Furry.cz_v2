var WindowTitle = document.title;

var openWindow;
		tinymce.init({
			setup: function(editor) {
				editor.addButton('furrafinityLinker', {
					text: false,
					tooltip: "Furraffinity linker",
					image: basepath+'\/images\/fur-affinity_icon_sm_zps6825a89d.png',
					onclick: function() {
						//editor.insertContent('Main button');
						openWindow = editor.windowManager.open({
							title: "Furrafinity linker",
							url: furralinkerurl,
							width: 600,
							height: 150,
							buttons: [{
										text: 'Vložit',
										onclick: function(){ 
												$.ajax({
													url: furrafinityget,
													data: {
														url: jQuery(jQuery("#"+openWindow._id+"-body").find('iframe').contents()).contents().find('#urlFurrafinity')[0].value
													},
													success: function( data ) {
														if(data.Error!=0)
															tinyMCE.activeEditor.windowManager.alert(data.Error);
														else{
															editor.insertContent('<a href="'+data.urlDefault+'" target="_blank"><img src="'+data.urlImage+'" style="max-width:400px;max-height:400px;" border=3></a>');	
															top.tinymce.activeEditor.windowManager.close();
														}	
													}
												});
										}
									}]
						});
					}
				});
			},
			selector: "textarea.tinimce",
			plugins: [
				"advlist autolink lists link image charmap print preview anchor spellchecker",
				"searchreplace visualblocks code fullscreen preview",
				"insertdatetime media table contextmenu paste textcolor mention link"
			],
			toolbar: "insertfile undo redo | styleselect | furrafinityLinker | removeformat | bold italic | forecolor backcolor | bullist numlist outdent indent | link image charmap | preview fullscreen ",
			menubar: false,
			toolbar_items_size: 'small',
			//statusbar: false,
			convert_urls: false,
			mentions: {
				delimiter: "#",
				source: function(query, process) {					
					$.getJSON(autocompleteur, function(data) {
						process(data);
					});
				},
				insert: function(item) {
					for (editorId in tinyMCE.editors) {
					  var orig_element = $(tinyMCE.get(editorId).getElement());
					  var name = orig_element.attr('name');
					  if (name == 'text') {
						if($("#taged_user_"+name).length == 0){
							orig_element.after("<div style='background-color: #5C5C5C;padding: 4px 7px;' id='taged_user_"+name+"_div'><div style='float:left;padding: 1px;' id='taged_user_"+name+"'>Označení tvorové:</div><div style='clear:both;'></div></div>");
							$("#taged_user_"+name+"_div").animate({height: "toggle", opacity:0}, 0, function() {  });
							$("#taged_user_"+name+"_div").animate({height: "toggle", opacity:1}, 300, function() {  });
						}
					  }
					}
					
					$.ajax({
						url: autocompleteur+"/taged_user_"+name,
						data: { user_: item.id },
						success: function( data ) {	
							$("#"+data["object"]).after("<div id=\""+data["object"]+"_id_"+data["Id"]+"\" style=\"overflow: hidden; word-wrap: inherit;height: 23px;background-image:url("+basepath+"/images/avatars/"+data["Avatar"]+");background-repeat:no-repeat;float: left;padding-left: 26px;background-size: 23px;margin-left: 10px;border: 1px solid #313131;background-color: #494949;padding-right: 3px;\">"+data["Name"]+"</div>");
							$("#"+data["object"]+"_id_"+data["Id"]).animate({width: "toggle", opacity:0}, 0, function() {  });
							$("#"+data["object"]+"_id_"+data["Id"]).animate({width: "toggle", opacity:1}, 600, function() {  });
						}
					});
					
					return '<a href="http://' + windowhosturlu + linktoprofileu + '/' + item.id + '">' + item.name.trim() + '</a>';
				}	
			}
		});

function NotifView(op){
	if(jak==0){
		selectedDIV = "notificatons";
		if(op!=5){ $("#notificatons").toggle(); }
		$("#nottifSpin").css('display',"inline")
		if($("#notificatons").css('display')=="block"){
			if($("#buttonNotificationOther").css('display')=="none"){ $("#buttonNotificationOther").css('display',"inline");posbut = $("#buttonNotificationOther").offset();$("#buttonNotificationOther").css('display',"none");}
			else{ posbut = $("#buttonNotificationOther").offset();}
			$("#notificatons").offset({ top: (posbut.top+24), left: (posbut.left-400+24)});
			if($("#notificationsText").html()==""){ $("#notificationsText").html("<div style='text-align:center;padding:7px;'>Načítám....</div>"); }
			$.ajax({
				url: notificationaj,
				data: { "jak":jak },
				success: function( data ) {	
					$("#nottifSpin").css('display',"none")
					$("#notificationsText").html("");
					for(i=0;i<data.length;i++){
						if(data[i].Url!=""){ divN="a";style=""; }else{ divN="div";style="cursor:default;"; }
						$("<"+divN+" style='"+style+"' class='notifList "+data[i].Class+"' href='"+data[i].Url+"'><img src='"+data[i].Image+"' class='img'><div class=body><div class='text'>"+data[i].Text+"</div><div class='info'>"+data[i].Info+"</div></div><div style='clear:both;'></div><div style='clear:both;'></div></"+divN+">").appendTo("#notificationsText");
					}		
					if(data.length==0){ $("<div style='text-align:center;padding:7px;'>Žádná notifikace k zobrazení!</div>").appendTo("#notificationsText"); }					
				}
			});
		}
	}else{
		selectedDIV = "notificatons_other";
		if(op!=5){ $("#notificatons_other").toggle(); }
		$("#nottifSpin_other").css('display',"inline")
		if($("#notificatons_other").css('display')=="block"){
			if($("#buttonNotificationOther").css('display')=="none"){ $("#buttonNotificationOther").css('display',"inline");posbut = $("#buttonNotificationOther").offset();$("#buttonNotificationOther").css('display',"none");}
			else{ posbut = $("#buttonNotificationOther").offset();}
			$("#notificatons_other").offset({ top: (posbut.top+24), left: (posbut.left-400+24)});
			if($("#notificationsText_other").html()==""){ $("#notificationsText_other").html("<div style='text-align:center;padding:7px;'>Načítám....</div>"); }
			$.ajax({
				url: notificationaj,
				data: { "jak":jak },
				success: function( data ) {	
					$("#nottifSpin_other").css('display',"none")
					$("#notificationsText_other").html("");
					for(i=0;i<data.length;i++){
						if(data[i].Url!=""){ divN="a";style=""; }else{ divN="div";style="cursor:default;"; }
						$("<"+divN+" style='"+style+"' class='notifList "+data[i].Class+"' href='"+data[i].Url+"'><img src='"+data[i].Image+"' class='img'><div class=body><div class='text'>"+data[i].Text+"</div><div class='info'>"+data[i].Info+"</div></div><div style='clear:both;'></div><div style='clear:both;'></div></"+divN+">").appendTo("#notificationsText_other");
					}		
					if(data.length==0){ $("<div style='text-align:center;padding:7px;'>Žádná notifikace k zobrazení!</div>").appendTo("#notificationsText_other"); }					
				}
			});
		}
	}
}

/*Notification create*/
notifID = 0;
function NotificationCreate(text,desc,href,image){
	if(typeof image!="undefined" && image!="")
	{ $("<a class='notifAlert' id='notification_"+notifID+"' style='display:none;' href='"+href+"'><img src='"+image+"' style='float:left;width:50px;height:50px;padding-top: 4px;padding-bottom:4px;padding-right: 6px;'><div class=text>"+text+"</div><div class=info style='padding-left: 50px;'>"+desc+"</div><div style='clear:both;'></div></a>").appendTo("#notificatonsAlert"); }
	else
	{ $("<a class='notifAlert' id='notification_"+notifID+"' style='display:none;' href='"+href+"'><div class=text>"+text+"</div><div class=info>"+desc+"</div></a>").appendTo("#notificatonsAlert"); }
	$("#notification_"+notifID).fadeIn( "slow", function() {} );
	setTimeout(function(bi) { $("#notification_"+bi).fadeOut( "slow", function() {} ); }, 30000, notifID);
	notifID++;
}

function NotificationControl(){
	$.ajax({
			url: notifcountajax,
			data: {},
			success: function( data ) {					
				$("#buttonNotification").html(data.Count);
				$("#buttonNotificationOther").html(data.Notif);
				$("#buttonNotification").css("display","inline");
				if(data.Count>0 || data.Notif>0){ 					
					document.title = "("+(data.Count+data.Notif)+") "+WindowTitle; 
				}else{ 
					document.title = WindowTitle; 
				}
				$("#buttonNotification_count").css("display","block");
				$("#buttonNotification_count").css("top",$("#buttonNotification").offset().top+7);
				$("#buttonNotification_count").css("left",$("#buttonNotification").offset().left+$("#buttonNotification").width()-$("#buttonNotification_count").width()+4);
				
				$("#buttonNotificationOther_count").css("display","block");
				$("#buttonNotificationOther_count").css("top",$("#buttonNotificationOther").offset().top+7);
				$("#buttonNotificationOther_count").css("left",$("#buttonNotificationOther").offset().left+$("#buttonNotificationOther").width()-$("#buttonNotificationOther_count").width()+4);
				
				if($("#buttonNotification_count").attr("jsed")!=1){
					$("#buttonNotification_count").appendTo("body");
					$("#buttonNotification_count").attr("jsed",1)
					$("#buttonNotificationOther_count").appendTo("body");
				}
				if(data.Count>0){
					$("#buttonNotification_count_text").html(data.Count);
				}else{ $("#buttonNotification_count_text").html(data.Count);$("#buttonNotification_count").css("display","none"); }
				if(data.Notif>0){					
					$("#buttonNotificationOther_count_text").html(data.Notif);
				}else{ $("#buttonNotificationOther_count_text").html(data.Notif);$("#buttonNotificationOther_count").css("display","none"); }
			}
		});					
	$.ajax({				
			url: notificationco+"?time="+(timeNot-1000),
			data: {},
			success: function( data ) {		
				for(i=1;i<data.length;i++){
					NotificationCreate(data[i].Text,data[i].Info,data[i].Href,data[i].Image);
				}
				timeNot = data[0].time;
			}
		});	
}

SelectTotal = 1;
function ReplaceSelect(){
	$("select").each(function(i){	
		if($(this).attr("id")!="" && typeof $(this).attr("id")!="undefined" && $(this).css("display")!="none"){
			if($(this).hasClass("input")){classAdd=" input";_type=2;}else{classAdd="";_type=1;}
			if(this.style.width!=""){au=0;css='width:'+$(this).css("width")+'';width=$(this).css("width");}else{au=1;css="";width="";}
			$(this).after('<a href=# class="JS ContextMenu '+classAdd+'" autosize="'+au+'" width="'+width+'" dropdown="select__'+SelectTotal+'_list" dropdown-open="left" selectType="'+_type+'" onChange="var gu=value_;$(\'#'+$(this).attr("id")+'\').val(gu);$(\'#'+$(this).attr("id")+'\').change();" dropdown-absolute="true" id="select__'+SelectTotal+'" style="display:inline-block;'+css+';">'+$($(this).find("option")[0]).html()+'</a>');
			var selectedval = $(this).val();
			var html = "<div class='listDiv' id='select__"+SelectTotal+"_list'><div class='listBox' style='"+css+"'><ul>";
				$(this).find("option").each(function(i){
					if($(this).attr("data-no")!="1"){
						if(selectedval == $(this).attr("value")){ sel=" sel='1'"; }else{ sel=""; }
						html+="<li value_='"+$(this).attr("value")+"'"+sel+"><a>"+$(this).html()+"</a></li>";
					}
				});
			html+= "</ul></div></div>";
			$(this).after(html);
			SelectTotal++;
			$(this).hide();
		}	
	});
}

selectData = new Array();
function ContextMenuList(divi){
				$("#"+divi).find("li").each(function(i){
					if($(this).attr("selx")==1){ 
						$(this).addClass("selx");
						selectData[divi][2].val($(this).find("a")[0].innerHTML);	
						selectData[divi][1].attr("value_",$(this).attr("value_")); 
					}
					$(this).attr("divi",divi);
					$(this).attr("pos",i);
					$(this).click(function(){
						divi = $(this).attr("divi");
						$(this).addClass("selx");
						selectData[divi][0] = $(this).attr("pos");
						selectData[divi][2].val($(this).find("a")[0].innerHTML);					
						selectData[divi][1].attr("value_",$(this).attr("value_"));
						eval("var value_='"+$(this).attr("value_")+"';"+selectData[divi][1].attr("onChange"));
						$("#"+divi).find("li").each(function(i){
							if(selectData[divi][0]!=i){
								$(this).removeClass("selx");
								$(this).removeClass("nos");
								selectData[divi][6] = 1;
								//$(this).addClass("nos");
								//Close!								
							}						
						 });
						 $("#"+selectedDIV).hide();
						 selectedDIV="";
						 selectedBUT.removeClass("selected");
						 selectData[divi][2].keyup();
					});
				});
}
function ContextMenuClickable(){
	$(".ContextMenu").each(function(i)
	{
	if($(this).attr("jsed")==2){
				var divi = $(this).attr("dropdown");
				$("#"+divi).find("li").each(function(i){
					if($(this).attr("selx")==1){ 
						$(this).addClass("selx");
						selectData[divi][2].val($(this).find("a")[0].innerHTML);	
						selectData[divi][1].attr("value_",$(this).attr("value_")); 
					}
					$(this).attr("divi",divi);
					$(this).attr("pos",i);
					$(this).click(function(){
						divi = $(this).attr("divi");
						$(this).addClass("selx");
						selectData[divi][0] = $(this).attr("pos");
						selectData[divi][2].val($(this).find("a")[0].innerHTML);					
						selectData[divi][1].attr("value_",$(this).attr("value_"));
						eval("var value_='"+$(this).attr("value_")+"';"+selectData[divi][1].attr("onChange"));
						$("#"+divi).find("li").each(function(i){
							if(selectData[divi][0]!=i){
								$(this).removeClass("selx");
								$(this).removeClass("nos");
								selectData[divi][6] = 1;
								//$(this).addClass("nos");
								//Close!								
							}						
						 });
						 $("#"+selectedDIV).hide();
						 selectedDIV="";
						 selectedBUT.removeClass("selected");
						 selectData[divi][2].keyup();
					});
				});
				$(this).attr("jsed",1);
	}
	else if($(this).attr("jsed")!=1){
		selectbox_html = $(this).html();
		selectbox = $(this);
		$(this).attr("jsed",1);		
		if(!$(this).hasClass("input")){ $(this).append(" &#x25bc;"); }
		$(this).click(function(){
			var divi = $(this).attr("dropdown");
			var ope_ = $(this).attr("dropdown-open");
			var abs_ = $(this).attr("dropdown-absolute");	
			var sel_ = $(this).attr("selectType");
			$("#"+divi).css("width",$(window).width()-$("#"+divi).offset().left-20);
			if($("#"+divi).css('display')=="none" && (lastDIVsel!=divi || $(this).find("input").length>0 )){
				pass = false;
				if(  $(this).find("input").length == 0 ){ pass = true; }
				else if( $(this).find("input")[0].value!="" || !$(this).hasClass("input") ){ pass = true; }
				if( pass ){				
				if(abs_=="true"){ top_=$(this).offset().top;left_=$(this).offset().left; }else{ top_=0;left_=0; }
					//$($("#"+divi).find(".listBox")[0]).css("border-top-left-radius","3px");
					//$($("#"+divi).find(".listBox")[0]).css("border-top-right-radius","3px");
				if(ope_=="right"){
					$("#"+divi).appendTo("body");
					$("#"+divi).show();
					$("#"+divi).css("top",$(this).offset().top+this.offsetHeight-1);						
					$("#"+divi).css("left",$(this).offset().left-($($("#"+divi).children(".listBox")[0]).outerWidth()-this.offsetWidth));												
					//$($("#"+divi).find(".listBox")[0]).css("border-top-right-radius","0px");					
				}
				if(ope_=="left"){
					$("#"+divi).appendTo("body");
					$("#"+divi).css("top",$(this).offset().top+this.offsetHeight-1);
					$("#"+divi).css("left",$(this).offset().left);
					$("#"+divi).show();
					//$($("#"+divi).find(".listBox")[0]).css("border-top-left-radius","0px");
				}	
				if(ope_=="top"){
					$("#"+divi).appendTo("body");
					$("#"+divi).show();
					$("#"+divi).css("top",$(this).offset().top-($($("#"+divi).children(".listBox")[0]).outerHeight()));
					$("#"+divi).css("left",$(this).offset().left);						
					//$("#"+divi).css("right",$("#"+divi).children(".listBox")[0].offsetWidth);
				}						
				$("#"+divi).css("width",$(window).width()-$("#"+divi).offset().left-20);					
				if($($("#"+divi).find(".listBox")[0]).outerWidth() <= $(this).outerWidth()){
					$($("#"+divi).find(".listBox")[0]).css("width", $(this).outerWidth()); 
					var wigh = ($($("#"+divi).find(".listBox")[0]).outerWidth() - $(this).outerWidth());
					if(wigh<0){wigh=0;}					
					$($("#"+divi).find(".listBox")[0]).css("width", $(this).outerWidth()-wigh); 					
				}
				if($($("#"+divi).find(".listBox")[0]).outerWidth() == $(this).outerWidth()){
					//$($("#"+divi).find(".listBox")[0]).css("border-top-left-radius","0px");
					//$($("#"+divi).find(".listBox")[0]).css("border-top-right-radius","0px");
				}
				$(this).addClass("selected");
				selectedDIV = divi;
				selectedBUT = $(this);
				return false;
				}
			}else{ 
				if(  $(this).find("input").length != 0 ){ 
					if($(this).hasClass("input") && $(this).find("input")[0].value==""){ $("#"+divi).css('display',"none") }
				}else{
					$("#"+divi).css('display',"none");
				}
			}
		});
	
		$(this).each(function(i)
		{
			var sel_ = $(this).attr("selectType");			
			var divi = $(this).attr("dropdown");
			selectData[divi] = new Array();
			selectData[divi][0] = 0;
			selectData[divi][3] = 0;
			selectData[divi][1] = $(this);	
			if(sel_=="2"){
				$(this).html("");
				var wid_ = $(this).attr("width");
				if(typeof wid_ == "undefined"){wid_=100;}
				$(this).width(wid_);
				//$(this).html("&#x25bc;");
				
				inputer = document.createElement('input');
				inputer.setAttribute("parent",i);
				inputer.setAttribute("divi",divi);				
				if(selectbox.hasClass("input"))
				{inputer.setAttribute("style","width:"+wid_+"px;padding:0px;");}
				else
				{inputer.setAttribute("style","width:"+(wid_-15)+"px;padding:0px;margin:0px;");}
				inputer.setAttribute("placeholder",selectbox_html);
				
				$(inputer).keydown(function( event ) {
					if(event.keyCode == 40){
						return false;
					}
					else if(event.keyCode == 13){
						return false;
					}
					else if(event.keyCode == 38){
						return false;
					}
				});
				$(inputer).keyup(function( event ) {
					mam=0;mat="";map=0;maq=0;
					valu_trigged_input  = $(this).val();
					divi = $(this).attr("divi");
					
					if(selectData[divi][6] != 1){ selectData[divi][1].click(); }
					selectData[divi][6]=0;
					
					var callbackfunct = selectData[divi][1].attr("data-callback");
					if(typeof callbackfunct!="undefined" && callbackfunct!=""){
						if(event.keyCode != 40 && event.keyCode != 13 && event.keyCode != 38)
						eval(callbackfunct+"($(this), $(this).val(), '"+divi+"');");
						mam=$("#"+divi).find("li").length;
					}else{
					
					var lijak="";
					if(event.keyCode!=8){lijak=":visible";}
					
					$("#"+divi).find("li").each(function(i){
						value_trigged_list = $(this).find("a")[0].innerHTML;
						if( value_trigged_list.toLowerCase().indexOf( valu_trigged_input.toLowerCase() ) ==-1 ){
							$(this).hide();
						}
						else{ 
							mam++;
							mat = value_trigged_list;
							map = i;
							$(this).show();
							if( value_trigged_list.toLowerCase() ==  valu_trigged_input.toLowerCase()){								
								maq++;
								divi = $(this).attr("divi");
								$(this).addClass("selx");
								selectData[divi][0] = $(this).attr("pos");
								if(event.keyCode>=48 && event.keyCode<=122){ selectData[divi][2].val(value_trigged_list); }
								selectData[divi][1].attr("value_",$(this).attr("value_")); 
								eval("var value_='"+$(this).attr("value_")+"', custom=0;"+selectData[divi][1].attr("onChange"));
								if(typeof $(this).attr("data-image")!="undefined"){ selectData[divi][1].css("background-image","url("+$(this).attr("data-image")+")"); }
								
								$("#"+divi).find("li"+lijak).each(function(i){
									if(selectData[divi][0]!=i){
										$(this).removeClass("selx");
									}						
								 });		
								
							}else{
								$(this).removeClass("selx");
							}
						}													
					});
					
					}
					returnFalse = false;
					if(mam>1){
						var selectDiv = 0;
						okolik=0;
						if(event.keyCode == 40){
							selectData[divi][3]++;
							if(mam<=selectData[divi][3]){ selectData[divi][3]=0; }
						}
						else if(event.keyCode == 38){
							selectData[divi][3]--;
							if(selectData[divi][3]<0){ selectData[divi][3]=mam-1; }
						}
						else if(event.keyCode == 13){
							a=-1;mam____=0;				
							$("#"+divi).find("li"+lijak).each(function(i){								
								divi = $(this).attr("divi");
								if($(this).is(":visible")){
									a++;
								}
								if((selectData[divi][3])==a && mam____==0){
									mam____=1;
									//alert(a);
									$(this).addClass("selx"); 
									selectData[divi][0] = $(this).attr("pos");
									if(typeof $(this).attr("place-text")!="undefined")
										selectData[divi][2].val($(this).attr("place-text"));	
									else
										selectData[divi][2].val($(this).find("a")[0].innerHTML);	
									selectData[divi][1].attr("value_",$(this).attr("value_")); 
									eval("var value_='"+$(this).attr("value_")+"', custom=0;"+selectData[divi][1].attr("onChange"));									
									if(typeof $(this).attr("data-image")!="undefined"){ selectData[divi][1].css("background-image","url("+$(this).attr("data-image")+")"); }
									selectData[divi][3]=0;
									$("#"+selectedDIV).hide();
									selectedDIV="";
									selectedBUT.removeClass("selected");
									selectData[divi][6] = 1;
									selectData[divi][2].keyup();									
									//$("#"+divi).css('display',"none");									
								}
								else{ $(this).removeClass("selx"); }								
							});
						}
						else{
							selectData[divi][3]=0;							
						}
						parentDiv = $($("#"+divi).find("div")[0]);
						//if(selectData[divi][3]!=0){
							a=-1;
							$("#"+divi).find("li"+lijak).each(function(i){
								divi = $(this).attr("divi");
								if($(this).is(":visible")){
									a++;
								}
								if((selectData[divi][3])==a){ 
									$(this).addClass("selx"); 										
									parentDiv = $($("#"+divi).find("div")[0]);
									if($(this).position().top < 0){
										parentDiv.scrollTop(parentDiv.scrollTop() + $(this).position().top - 5);	
									}
									if(($(this).position().top - parentDiv.height() + $(this).height()) > 0){
										parentDiv.scrollTop(parentDiv.scrollTop() + ( $(this).height() + ($(this).position().top - parentDiv.height() )) - 5);	
									}
								}
								else{ $(this).removeClass("selx"); }
							});
						//}
					}
					nom = 1;
					if(mam==1 && event.keyCode!=8 && event.keyCode>=48 && event.keyCode<=122){  						
						$("#"+divi).find("li"+lijak).each(function(i){								
							divi = $(this).attr("divi");
							if(map==i){
								$(this).addClass("selx"); 
								selectData[divi][0] = $(this).attr("pos");
								if(typeof $(this).attr("place-text")!="undefined")
									selectData[divi][2].val($(this).attr("place-text"));	
								else
									selectData[divi][2].val($(this).find("a")[0].innerHTML);	
								selectData[divi][1].attr("value_",$(this).attr("value_")); 
								eval("var value_='"+$(this).attr("value_")+"', custom=0;"+selectData[divi][1].attr("onChange"));
								if(typeof $(this).attr("data-image")!="undefined"){ selectData[divi][1].css("background-image","url("+$(this).attr("data-image")+")"); }	
								nom=0;
							}
							else{ $(this).removeClass("selx"); }								
						});						
					}
					
					if(mam==0){
						if(selectData[divi][1].attr("data-custom") == "true"){
							selectData[divi][1].attr("value_",selectData[divi][2].val()); 
							eval("var value_='"+selectData[divi][2].val()+"', custom=1;"+selectData[divi][1].attr("onChange"));
							$("#"+divi).hide();
						}else{
							selectData[divi][2].animate({backgroundColor:'red'},200);
						}
					}else{ selectData[divi][2].animate({backgroundColor:'transparent'},200); }
					
					if(maq<1){if(selectData[divi][0]!=0){ selectData[divi][0] = 0;selectData[divi][1].attr("value_","");eval("var value_='', custom=0;"+selectData[divi][1].attr("onChange"));} }
					if(selectData[divi][1].attr("value_")=="" && selectData[divi][1].hasClass("withimage") && nom==1){ selectData[divi][1].css("background-image", 'none'); }
				});
				
				selectData[divi][2] = $(inputer);
				$(this).append(inputer);
				
				if(selectData[divi][1].outerWidth() > selectData[divi][1].attr("width")){
					inputer.setAttribute("style","width:"+(selectData[divi][1].attr("width")-(selectData[divi][1].outerWidth() - selectData[divi][1].attr("width")))+"px;padding:0px;");
					divib = selectData[divi][1].attr("dropdown");
					$("#"+divib).css("width",selectData[divi][1].outerWidth()+"px");
				}
				
				span = document.createElement('span');
				if(!$(this).hasClass("input")){ $(span).html("&#x25bc;"); }
				$(this).append(span);
				
				//$(this).html("&#x25bc;");
				$(this).attr("value_","");			
				$("#"+divi).find("li").each(function(i){
					if($(this).attr("selx")==1){ 
						$(this).addClass("selx");
						selectData[divi][2].val($(this).find("a")[0].innerHTML);	
						selectData[divi][1].attr("value_",$(this).attr("value_")); 
					}
					$(this).attr("divi",divi);
					$(this).attr("pos",i);
					$(this).click(function(){
						divi = $(this).attr("divi");
						$(this).addClass("selx");
						selectData[divi][0] = $(this).attr("pos");
						selectData[divi][2].val($(this).find("a")[0].innerHTML);					
						selectData[divi][1].attr("value_",$(this).attr("value_"));
						eval("var value_='"+$(this).attr("value_")+"';"+selectData[divi][1].attr("onChange"));
						$("#"+divi).find("li").each(function(i){
							if(selectData[divi][0]!=i){
								$(this).removeClass("selx");
								$(this).removeClass("nos");
								selectData[divi][6] = 1;
								//$(this).addClass("nos");
								//Close!								
							}						
						 });
						 $("#"+selectedDIV).hide();
						 selectedDIV="";
						 selectedBUT.removeClass("selected");
						 selectData[divi][2].keyup();
					});
				});
			}else if(sel_=="1"){
				$(this).html(" &#x25bc;");
				$(this).attr("value_","");			
				$("#"+divi).find("li").each(function(i){
					if(selectData[divi][1].attr("autosize") == 0){ class_="textin"; }else{ class_=""; }
					if($(this).attr("sel")==1){ $(this).addClass("sel");selectData[divi][1].html("<span class='"+class_+"' style='width:"+(selectData[divi][1].outerWidth()-30)+"px;'>"+$(this).find("a")[0].innerHTML+"</span> &#x25bc;");selectData[divi][1].attr("value_",$(this).attr("value_")); }else{ $(this).addClass("nos"); }
					$(this).attr("divi",divi);
					$(this).attr("pos",i);
					$(this).click(function(){
						divi = $(this).attr("divi");
						$(this).addClass("sel");
						selectData[divi][0] = $(this).attr("pos");
						if(selectData[divi][1].attr("autosize") == 0){ class_="textin"; }else{ class_=""; }
						selectData[divi][1].html("<span class='"+class_+"' style='width:"+(selectData[divi][1].outerWidth()-30)+"px;'>"+$(this).find("a")[0].innerHTML+"</span> &#x25bc;");					
						selectData[divi][1].attr("value_",$(this).attr("value_"));
						eval("var value_='"+$(this).attr("value_")+"';"+selectData[divi][1].attr("onChange"));
						$("#"+divi).find("li").each(function(i){
							if(selectData[divi][0]!=i){
								$(this).removeClass("sel");$(this).removeClass("nos");
								$(this).addClass("nos");
								//Close!								
							}						
						 });
						 $("#"+selectedDIV).hide();
						 selectedDIV="";
						 selectedBUT.removeClass("selected");
					});
				});
			}else{
				$("#"+divi).find("li").each(function(i){
					$(this).click(function(){
						//Close!							
						$("#"+selectedDIV).hide();
						selectedDIV="";
						selectedBUT.removeClass("selected");
						if($($(this).find("a")[0]).attr("href")=="#no"){ return false; }
					});
				});
			}
		});	
		$(this).on('selectstart', false);
		}
	});
}

function loading_show(){ 
	$("#dialog-loading").show("scale",400); 
}
function loading_hide(div){ 
	$('#dialog-loading').css("width","300px");
	$('#dialog-loading').css("height","70px");
	if(typeof div!="undefined"){
		var div=div;
		div.css("opacity",0);
		$("#dialog-loading").hide();
		$('#dialog-loading').animate({height:div.outerHeight(),width:div.outerWidth(),top:div.offset().top,left:div.offset().left}, function(){ 
			div.css("opacity",1);
			$('#dialog-loading').css("width","300px");
			$('#dialog-loading').css("height","70px");
			$("#dialog-loading").center();
		});
		//parents('.ui-dialog:first').
	}else{
		$("#dialog-loading").hide("fade",200);  
	}
}

function UserShowCategory(ids){
	html="";
	if(ids==-1){
		for(i=0;i<allUserCategory.sekcion.length;i++){
			if(typeof allUserCategory.users[i]!="undefined"){
				for(a=0;a<allUserCategory.users[i].length;a++){
					html+="<div class='ratingBoxis'><img src='"+basepath+"/images/avatars/"+allUserCategory.users[i][a]["Avatar"]+"' class='ratingAvatar'><div style='float:left;'><b><a style='bacground:white;' href='"+intercomlinked+"/"+allUserCategory.users[i][a]["Username"]+"/'>"+allUserCategory.users[i][a]["Name"]+"</a></b><br>"+allUserCategory.sekcion[i]+"</div><div style='clear:both;'></div></div>";
				}
			}
		}
	}else{
		if(typeof allUserCategory.users[ids] == "undefined"){
			html = "<div style='font-weight:bold;text-align:center;padding:20px;'>Nebyly nalezeny žádné výsledky.</div>";
		}else{
			for(var i=0;i<allUserCategory.users[ids].length;i++){
				html+="<div class='ratingBoxis'><img src='"+basepath+"/images/avatars/"+allUserCategory.users[ids][i]["Avatar"]+"' class='ratingAvatar'><div style='float:left;'><b>"+allUserCategory.users[ids][i]["Name"]+"</b><br><a href='"+intercomlinked+"/"+allUserCategory.users[ids][i]["Username"]+"/'>Poslat zprávu</a></div><div style='clear:both;'></div></div>";
			}
		}
	}	
	$("#UserListCategory").html(html);
}

var swpier_toggle_body = new Array();
var swpier_toggle_id = new Array(); 
var resizer_klt = new Array();
var progres_bar = new Array();

$("input[type=\"progressbar\"]").each(function(i)
	{
		var state = $(this).attr("value");
		var width = $(this).outerWidth(true);
		var max = $(this).attr("data-max");
		var proc = Math.round((state/max)*100);
		var wigra = (width/100)*state;
				
		$(this).css("display","none");
		this.setAttribute("parent",i);
		body = document.createElement('div');
		body.setAttribute("parent",i);
		$(body).css("width",width+"px");
		$(body).addClass("progress_bar");
		$(body).html("<span class=pro>"+proc+"%</span>");
		
		gra = document.createElement('div');
		$(gra).css("width",wigra+"px");
		$(gra).addClass("gra");
		$(body).append(gra);
		
		text = document.createElement('div');
		$(text).css("width",width+"px");
		$(text).addClass("text");
		$(text).html(proc+"%");
		$(gra).append(text);		
		
		progres_bar[i] = new Array($(this),body);
		
		$(this).bind("change paste keyup", function(event){
			var state = $(this).val();
			var max = $(this).attr("data-max");
			var proc = Math.round((state/max)*100);
			var wigra = (state/width)*100;
			var parent = $(this).attr("parent");
			var div = progres_bar[parent][1];
			
			$($(div).find(".pro")[0]).html(proc+"%");
			//$($(div).find(".gra")[0]).css("width",wigra+"px");
			$($(div).find(".gra")[0]).animate({ width: wigra+"px",}, 200 );
			$($(div).find(".text")[0]).html(proc+"%");
		});
		
		$(this).after(body);
	}
);	
$("img[type=\"image_resize\"]").load(function(a)
	{
		i=a.timeStamp;
		var image = $(this).attr("src");
		var name = $(this).attr("name");
		var resize = $(this).attr("data-resize").split("x");
		
		$(this).css("display","none");
		
		imre_body = document.createElement('div');
		$(imre_body).css("width",resize[0]);
		$(imre_body).css("height",resize[1]);
		imre_body.setAttribute("parent",i);
		$(imre_body).css("background","url("+image+")");	
		$(imre_body).addClass("image_resize_b");
		
		$(this).after(imre_body);
	}
);	

$("input[type=\"toggle_swipe\"]").each(function(i)
	{
		swpier_toggle_id[i] = $(this);
		swpier_toggle_id[i].css("display","none");
		var state = $(this).attr("value"); if(state == undefined){state=0;}
		var stav = $(this).attr("data-state"); if(typeof $(this).attr("data-state") == "undefined" || $(this).attr("data-state")==""){stav=new Array("ON","OFF");}else{stav = stav.split("|");}
		var disab = $(this).attr("disabled");
		swpier_toggle = document.createElement('a');
		swpier_toggle.setAttribute("id","toggle_swipe_"+i);
		swpier_toggle.setAttribute("parent",i);
		swpier_toggle.setAttribute("href","#toggle_swipe_"+i);
		$(swpier_toggle).addClass("toggle_swipe");
		this.setAttribute("value",state);
		$(swpier_toggle).click(function(){
			var parent = $(this).attr("parent");			
			var state = swpier_toggle_id[parent].attr("value");
			var disab = swpier_toggle_id[parent].attr("disabled");
			if(disab=="disabled"){
				if(state==1){
					$($(swpier_toggle_body[i]).find(".mover")[0]).animate({ 
						left: "-=15px",
					}, 200 );
					$($(swpier_toggle_body[i]).find(".mover")[0]).animate({ 
						left: "+=15px",
					}, 200 );
				}else{
					$($(swpier_toggle_body[i]).find(".mover")[0]).animate({ 
						left: "+=15px",
					}, 200 );
					$($(swpier_toggle_body[i]).find(".mover")[0]).animate({ 
						left: "-=15px",
					}, 200 );
				}
			}else{			
				if(state==1){
					$($(swpier_toggle_body[i]).find(".mover")[0]).animate({ 
						left: "-=34px",
					}, 200 );
				}else{
					$($(swpier_toggle_body[i]).find(".mover")[0]).animate({ 
						left: "+=34px",
					}, 200 );
				}
				swpier_toggle_id[parent].attr("value",(state==0?1:0));
			}
			return false;
		});
		
		swpier_toggle_body[i] = document.createElement('span'); // tělo switche
		$(swpier_toggle_body[i]).addClass("body");
		
		swpier_toggle_body_zap = document.createElement('div'); //pozadí zap
		$(swpier_toggle_body_zap).addClass("zap");
		$(swpier_toggle_body_zap).html(stav[0]);
		$(swpier_toggle_body[i]).append(swpier_toggle_body_zap);
		
		swpier_toggle_body_vyp = document.createElement('div'); //pozadí vyp
		$(swpier_toggle_body_vyp).addClass("vyp");
		$(swpier_toggle_body_vyp).html(stav[1]);
		$(swpier_toggle_body[i]).append(swpier_toggle_body_vyp);
		
		swpier_toggle_body_switch = document.createElement('div'); //přepínač
		$(swpier_toggle_body_switch).addClass("mover");
		if(disab=="disabled"){ $(swpier_toggle_body_switch).addClass("disabled"); }
		if(state==1){
			$(swpier_toggle_body_switch).css("left",75-1-39);	
			$(swpier_toggle_body_switch).css("top",1);	
		}else{
			$(swpier_toggle_body_switch).css("left",1);	
			$(swpier_toggle_body_switch).css("top",1);	
		}
		$(swpier_toggle_body[i]).append(swpier_toggle_body_switch);
		
		$(swpier_toggle).append(swpier_toggle_body[i]);
		
		$(this).after(swpier_toggle);
	}
);	