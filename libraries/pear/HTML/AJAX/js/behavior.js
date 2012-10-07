/**

ModifiedBehavior v1.0 by Ron Lancaster based on Ben Nolan's Behaviour, June 2005 implementation.
Modified to use Dean Edward's CSS Query.

Description
----------

Uses css selectors  to apply javascript Behaviors to enable unobtrusive javascript in html documents.

Dependencies
------------

Requires [Dean Edwards CSSQuery](http://dean.edwards.name/my/cssQuery/ "CSSQuery").

Usage
------

        Behavior.register(
            "b.someclass",
            function(element) {
                element.onclick = function(){
                    alert(this.innerHTML);
                }
            }
        );

        Behavior.register(
            "#someid u",
            function(element) {
                element.onmouseover = function(){
                    this.innerHTML = "BLAH!";
                }
            },
            getElementByID("parent")
        );

Call `Behavior.apply()` to re-apply the rules (if you update the dom, etc).

License
------

Reproduced under BSD licensed. Same license as Ben Nolan's implementation.

More information for Ben Nolan's implementation: <http://ripcord.co.nz/behaviour/>

*/

var Behavior = {

    // private data member
    list : new Array(),

    // private method
    addLoadEvent : function(func) {
        var oldonload = window.onload;

        if (typeof window.onload != 'function') {
            window.onload = func;
        } else {
            window.onload = function() {
                oldonload();
                func();
            }
        }
    },

    // void apply() : Applies the registered ruleset.
    apply : function() {
        for (i = 0; i < Behavior.list.length; i++) {
            var rule = Behavior.list[i];
            var tags = cssQuery(rule.selector, rule.from);
            if (tags) {
                for (j = 0; j < tags.length; j++) {
                    rule.action(tags[j]);
                }
            }
        }
    },

    // void register() : register a css selector, and the action (function) to take,
    // from (optional) is a document, element or array of elements which is filtered by selector.
    register : function(selector, action, from) {
        Behavior.list.push(new BehaviorRule(selector, from, action));
    },

    // void start() : initial application of ruleset at document load.
    start : function() {
        Behavior.addLoadEvent(function() {
            Behavior.apply();
        });
    }
}

function BehaviorRule(selector, from, action) {
    this.selector = selector;
    this.from = from;
    this.action = action;
}

Behavior.start();