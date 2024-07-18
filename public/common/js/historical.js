var Historical={
	hist_table:"",
	hist_field:"",
	hist_rowid:0,
	hist_obj_id:0,
	hist_obj:null,
	hist_url:"",
	obj_type:"",
	setType:function(type){Historical.obj_type=type;},
	removeNumber:function(s){
		j=s.length;
		while(j>0){
			j--;
			var n=Number(s.substr(j,1));
			if(!(n>=0 && n<=9))
				break;
		}
		return s.substr(0,j+1);
	},
	init:function(type,x,y){
		Historical.obj_type=type;
		var box='<div id="boxHistory" style="display:none"><iframe id="frameChart" style="width:100%;border:none;height: 300px; margin-top: 0"></iframe></div>';
		$('body').append(box);
		if(x && y){
			var button='<div id="buttonHist" onClick="Historical.loadHistoryData(\'\',Historical.hist_field,Historical.hist_rowid,Historical.hist_obj_id)" style="display:none;cursor:pointer;z-index:100;background:#378de5;position:absolute;width: 26px; height: 26px;top:'+y+'px;left:'+x+'px"><img class="center_content" src="../img/hist.png" height=16 /></div>';
			$('body').append(button);
			document.onmouseup=function (event)
			{
			  //if(event.button==2)
			   {
				   var $focused = $(':focus');
				   var sclass=$focused.attr("class");
				   if(sclass.indexOf("_numeric")!= -1){
					   $("#buttonHist").show();
				   }
				   else
					   $("#buttonHist").hide();
			   }
			};
		}
	},
	update:function(e){
		$("._numeric").click(function(){
			Historical.hist_obj=$(this);
			var s=$(this).attr("name");
			Historical.hist_rowid=$(this).parent().parent().attr('rid');
			if(Historical.obj_type=="ENERGY_UNIT"){
				i1=s.indexOf("_")+1;
				i2=s.lastIndexOf("_")-1;
				i2=s.lastIndexOf("_",i2)-1;
				Historical.hist_field=Historical.removeNumber(s.substr(i1,i2-i1+1));
			}
			else if(Historical.obj_type=="FLOW"){
				i1=s.indexOf("_")+1;
				i2=s.lastIndexOf("_")-1;
				Historical.hist_field=Historical.removeNumber(s.substr(i1,i2-i1+1));
			}
			else if(Historical.obj_type=="TANKSTORAGE"){
				i1=s.indexOf("_")+1;
				i1=s.indexOf("_",i1)+1;
				i2=s.lastIndexOf("_")-1;
				Historical.hist_field=Historical.removeNumber(s.substr(i1,i2-i1+1));
			}
			else if(Historical.obj_type=="QUALITY"){
				i1=0;
				i2=s.length-Historical.hist_rowid.length-1;
				Historical.hist_field=s.substr(i1,i2-i1+1);
			}
			else if(Historical.obj_type=="DEFERMENT" || Historical.obj_type=="WELLTEST" || Historical.obj_type=="TICKET"){
				i1=s.indexOf("_")+1;
				i2=s.length-Historical.hist_rowid.length-1;
				Historical.hist_field=s.substr(i1,i2-i1+1);
			}
			//alert("rid="+hist_rowid+", obj_id="+hist_obj_id);
		});
	},
	loadHistoryData:function(table,field,row_id,obj_id)
	{
		//$("#boxHistory").html("table:"+table+",field:"+field+",row_id:"+row_id+",obj_id:"+obj_id);
		document.getElementById("frameChart").contentWindow.document.write("<font family='Open Sans'>Loading...</font>");
		if(table=="reload" && Historical.hist_url!=""){
			$("#frameChart").attr("src",Historical.hist_url+"&limit="+field);
		}
		else{
			$("#boxHistory").dialog({
					height: 350,
					width: 900,
					position: { my: 'top', at: 'top+150' },
					modal: true,
					title: "History data",
				});
			var sub="";
			if(table=="")
				sub=$("#tabs").tabs("option", "active");
			Historical.hist_url="../common/get_hist_data.php?type="+Historical.obj_type+"&sub="+sub+"&table="+table+"&field="+field+"&row_id="+row_id+"&obj_id="+obj_id;
			$("#frameChart").attr("src",Historical.hist_url);
		}
	},
	selectHistoryValue:function (v){
		if(Historical.hist_obj){
			$(Historical.hist_obj).val(v);
			$(Historical.hist_obj).effect("highlight", {}, 3000);
			$(Historical.hist_obj).css("color", "red");
			$("#boxHistory").dialog("close");
			$("#buttonHist").show();
		}
	}
}