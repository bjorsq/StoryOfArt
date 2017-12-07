function go_to_page(no)
{
    document.forms['navbar'].p.value = no;
    document.forms['navbar'].submit();
}
function insert_text(after_listorder)
{
    popup('edit_text.php?after='+after_listorder, 500, 400, 0, 0);
}
function submit_text()
{
  var f = document.forms["edit_text"];
  if (f.content.value == "") {
    alert("please supply some text");
    return;
  }
  f.submit();
}
function delete_text(id)
{
    if (id != "" && id != null && id != "undefined") {
        var formObj = document.forms["update_text_"+id];
        if (confirm("Are you sure you want to delete this text?")) {
            formObj.form_action.value = 'delete';
            formObj.submit();
        }
    }
}
function update_text(id)
{
    if (id != "" && id != null && id != "undefined") {
        popup('edit_text.php?text_id=' + id, 500, 400, 0, 0);
    }
}
function move_to_top(id)
{
    if (id != "" && id != null && id != "undefined") {
        var formObj = document.forms["update_text_"+id];
        formObj.form_action.value = "top";
        formObj.submit();
    }
}
function move_to_bottom(id)
{
    if (id != "" && id != null && id != "undefined") {
        var formObj = document.forms["update_text_"+id];
        formObj.form_action.value = "bottom";
        formObj.submit();
    }
}
function move_up(id)
{
    if (id != "" && id != null && id != "undefined") {
        var formObj = document.forms["update_text_"+id];
        formObj.form_action.value = "up";
        formObj.submit();
    }
}
function move_down(id)
{
    if (id != "" && id != null && id != "undefined") {
        var formObj = document.forms["update_text_"+id];
        formObj.form_action.value = "down";
        formObj.submit();
    }
}
function edit_behaviour(text_id)
{
    popup('edit_behaviour.php?text_id='+text_id, 500, 400, 0, 0);
}
function submit_behaviour()
{
    document.forms["edit_behaviour"].submit();
}
function finish_editing()
{
    if (window.opener) {
        window.opener.document.forms["reloadPage"].submit();
    }
    self.close();
}
function edit_section()
{
    var section_id = document.forms["section_jump"].section_id.options[document.forms["section_jump"].section_id.selectedIndex].value;
    popup('edit_sections.php?section_id='+section_id, 500, 400, 0, 0);
}
function filter_section()
{
    document.forms["section_jump"].submit();
}
function remove_filter(pp)
{
    window.location.href = "index.php?p=1&pp="+pp;
}
function add_section()
{
    popup('edit_sections.php', 500, 400, 0, 0);
}
function submit_section()
{
  var f = document.forms["edit_section"];
  if (f.name.value == "") {
    alert("please supply a name");
    return;
  }
  f.submit();
}
function viewSVG()
{
  var a = arguments;
  if (a.length==2) {
    popup('soaSVG.php?from_id='+a[0]+'&to_id='+a[1], 1024,760,0,0,'status');
  } else {
    popup('soaSVG.php?from_id='+a[0], 1024, 760,0,0,'status');
    //self.close();
  }
}
function openSVG(filename)
{
    popup('SVG/'+filename, 1024,760,0,0,'status');
}
function saveSVG(from_id, to_id)
{
  var filename = prompt("Please enter a filename","here");
  if (filename != "") {
    if (filename.indexOf(".svg") == -1) {
      filename += ".svg";
    }
    popup('soaSVG.php?from_id='+from_id+'&to_id='+to_id+'&filename='+filename, 360,240,0,0,'status','temp');
  } else {
    alert("pretty please?");
    saveSVG(from_id);
  }
}
var _console = null;
function popup() {
  //create a simple popup with no window features
	//usage: popup(url,width,height,x-screen-position,y-screen-position,featurelist,fullscreen);
  if(arguments.length==0) return;
	var a=arguments;
	var popup=new Object;
	popup.url=a[0]||null;
	popup.w=a[1]||300;
	popup.h=a[2]||300;
	popup.x=a[3]||0;
	popup.y=a[4]||0;
	popup.extras=a[5]||"";
  popup.name=a[6]||"console";
  popup.fullscreen=a[7]||false;
	if(document.layers){
    popup.features=popup.fullscreen?"outerWidth="+screen.width+",outerHeight="+screen.height+",screenX=0,screenY=0":"innerWidth="+popup.w+",innerHeight="+popup.h+",screenX="+popup.x+",screenY="+popup.y;
	}else{
    popup.features=popup.fullscreen?"width="+screen.width+",height="+screen.height+",left=0,top=0":"width="+popup.w+",height="+popup.h+",left="+popup.x+",top="+popup.y;
	}
	if(popup.extras!="") popup.features+=","+popup.extras;
  if((_console==null)||(_console.closed)){
	  _console=window.open(popup.url,popup.name,popup.features);
    _console.focus();
	}else{
	  _console.close();
    _console=window.open(popup.url,popup.name,popup.features);
    _console.focus();
    _console.moveTo(popup.x,popup.y);
	}
}
