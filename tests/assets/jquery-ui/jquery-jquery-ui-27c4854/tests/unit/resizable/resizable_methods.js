/*
 * resizable_methods.js
 */
(function($) {

module("resizable: methods");

test("init", function() {
	expect(6);

	$("<div></div>").appendTo('body').resizable().remove();
	ok(true, '.resizable() called on element');

	$([]).resizable().remove();
	ok(true, '.resizable() called on empty collection');

	$('<div></div>').resizable().remove();
	ok(true, '.resizable() called on disconnected DOMElement');

	$('<div></div>').resizable().resizable("foo").remove();
	ok(true, 'arbitrary method called after init');

	el = $('<div></div>').resizable()
	var foo = el.resizable("option", "foo");
	el.remove();
	ok(true, 'arbitrary option getter after init');

	$('<div></div>').resizable().resizable("option", "foo", "bar").remove();
	ok(true, 'arbitrary option setter after init');
});

test("destroy", function() {
	$("<div></div>").appendTo('body').resizable().resizable("destroy").remove();
	ok(true, '.resizable("destroy") called on element');

	$([]).resizable().resizable("destroy").remove();
	ok(true, '.resizable("destroy") called on empty collection');

	$('<div></div>').resizable().resizable("destroy").remove();
	ok(true, '.resizable("destroy") called on disconnected DOMElement');

	$('<div></div>').resizable().resizable("destroy").resizable("foo").remove();
	ok(true, 'arbitrary method called after destroy');

	var expected = $('<div></div>').resizable(),
		actual = expected.resizable('destroy');
	equals(actual, expected, 'destroy is chainable');
});

})(jQuery);
