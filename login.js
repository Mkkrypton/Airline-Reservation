$(document).ready(function() {
	$("#old").hide();
    $("#cart").hide();
    $("#signin").show();
    $("#register").show();

    xmlhttp = new XMLHttpRequest();	
	xmlhttp.onreadystatechange = function() {
               if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var content = xmlhttp.responseText;
                console.log("Login check response:", content);
                
                if(content != "0")
                {
             	   $("#old").show();
                   $("#cart").show();
             	   $("#signin").hide();
                   $("#register").hide();
             	   content = "Welcome " + content + "!";
             	   $("#wuser").text(content);
             	   // Explicitly hide buttons after login
             	   $("#signin").css("display", "none");
             	   $("#register").css("display", "none");
             	   $("#old").css("display", "block");
             	   $("#cart").css("display", "block");
             	  }
             	   else
             	   {	
             	   $("#old").hide();
                   $("#cart").hide();
             	   $("#signin").show();
                   $("#register").show();
             	                	   }
            }
        }
        
        xmlhttp.open("GET","home.php",true);
        xmlhttp.send();      
        
        $("#logout").click(function(){
        	location.href = "logout.php";
        });
        });
