jQuery(function(e){function t(){var t,n=[];location.origin||(location.origin=location.protocol+"//"+location.host),t=location.origin+ajaxurl;var o=function(){var n;return e.ajax({async:!1,type:"POST",url:t,data:{action:"edd_fields_get_posts",current_post_type:typenow},success:function(t){n=e.parseJSON(t)},error:function(e){n=[{text:"Error. See Browser Console.",value:""}],console.error(e)}}),n}();return n=o}function n(t){var n,o=[];if(o.push({text:"Choose a Field Name",value:""}),void 0!==t&&""!==t){location.origin||(location.origin=location.protocol+"//"+location.host),n=location.origin+ajaxurl;var a=function(){var o;return e.ajax({async:!1,type:"POST",url:n,data:{action:"edd_fields_get_names",post_id:t},success:function(t){o=e.parseJSON(t)},error:function(e){o=[{text:"Error. See Browser Console.",value:""}],console.error(e)}}),o}();o=a}else e(".edd-fields-repeater tbody tr .edd-fields-key input").each(function(t,n){o.push({text:e(n).val(),value:e(n).val()})});if(0==e(".edd-fields-names option").length)return o;e(".edd-fields-names").empty();for(var i="",s=0;s<o.length;s++)i+='<option value="'+o[s].value+'">'+o[s].text+"</option>";e(".edd-fields-names").html(i)}e(document).ready(function(){tinymce.PluginManager.add("edd_fields_shortcodes_script",function(o,a){o.addButton("edd_fields_shortcodes",{text:"EDD Fields",icon:!1,type:"menubutton",menu:[{text:"Create Fields Table",onclick:function(){o.windowManager.open({title:"Add Fields Table",body:[{type:"select",name:"id",label:"Using This Post's Data:",values:t()},{type:"textbox",name:"class",label:"Wrapper Class (Optional)"}],onsubmit:function(e){o.insertContent("[edd_fields_table"+(void 0!==e.data.id?' post_id="'+e.data.id+'"':"")+(void 0!==e.data["class"]?' class="'+e.data["class"]+'"':"")+"]")}})}},{text:"Get Field Value",onclick:function(){o.windowManager.open({title:"Retrieve a Field's Value by Name",body:[{type:"select",name:"id",label:"Using This Post's Data:",classes:"edd-fields-get-names",values:t()},{type:"select",name:"name",label:"Field Name",classes:"edd-fields-names",values:n(void 0)}],onPostRender:function(t){e(".edd-fields-get-names").on("change",function(t){n(e(this).val())})},onsubmit:function(e){o.insertContent("[edd_field"+(void 0!==e.data.id?' post_id="'+e.data.id+'"':"")+(void 0!==e.data.name?' name="'+e.data.name+'"':"")+"]")}})}}]})})})});