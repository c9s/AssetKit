/**
*
*  AJAX IFRAME METHOD (AIM)
*  http://www.webtoolkit.info/
*
**/

AIM = {

    frame : function(c) {
        var n = 'f' + Math.floor(Math.random() * 99999);
        var d = document.createElement('DIV');
        d.innerHTML = '<iframe style="display:none" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
        document.body.appendChild(d);

        var i = document.getElementById(n);
        if (c && typeof(c.onComplete) == 'function') {
            i.onComplete = c.onComplete;
        }

        return n;
    },

    form : function(f, name) {
        f.setAttribute('target', name);
    },


    /**
     * what onStart returns affect the form submitting,
     * if you returns false, the form won't submit.
     */
    submit : function(f, c) {
        AIM.form(f, AIM.frame(c));
        if (c && typeof(c.onStart) == 'function') {
            if(window.console)
                console.log("Running AIM.onStart");
            return c.onStart();
        } else {
            return true;
        }
    },

    loaded : function(id) {
        var i = document.getElementById(id);
        if (i.contentDocument) {
            var d = i.contentDocument;
        } else if (i.contentWindow) {
            var d = i.contentWindow.document;
        } else {
            var d = window.frames[id].document;
        }

        if (d.location.href == "about:blank") {
            return;
        }
        if (typeof(i.onComplete) == 'function') {
            if( window.console )
                console.warn("Running AIM.onComplete");
            var match = /(\{.+\}).+/.exec(d.body.innerHTML);
            if (match) {
                if ( window.console )
                    console.log(match[1]);
                i.onComplete(match[1]);
            }
        }
    }
};

