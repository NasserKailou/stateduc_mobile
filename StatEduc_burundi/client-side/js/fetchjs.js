<!--

var xmlhttp=false;

/*@cc_on @*/

/*@if (@_jscript_version >= 5)
// JScript gives us Conditional compilation, we can cope with old IE versions.
// and security blocked creation of the objects.

try {

    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");

} catch (e) {

    try {

        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");

    } catch (E) {

        xmlhttp = false;

    }

}

@end @*/

if (!xmlhttp && typeof(XMLHttpRequest) != 'undefined') {

    xmlhttp = new XMLHttpRequest();

}

function fetchjs(script) {

    xmlhttp.open("GET", script, true);

    xmlhttp.onreadystatechange = function() {

        if(xmlhttp.readyState==4) {

            eval(xmlhttp.responseText);

        }

    }

    xmlhttp.send(null);

    return false;

}

-->