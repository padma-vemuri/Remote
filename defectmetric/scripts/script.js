<script type="text/javascript">
YUI().use('node', 'event-touch', function(Y) {
    var onClick = function(e) {
        
        e.preventDefault();
        var item = e.currentTarget,
            list2 = Y.one('#domainduplicate');

        if (item.get('parentNode') === list2) { // remove if click is in the cart
            // remove from cart only if it's not the cart header
            if (item.hasClass('domaindup') === false) {
                item.remove(); // sugar for item.get('parentNode').removeChild(item);
            }
        } else { // else add a clone of the clicked item to the cart
            console.log(list2);
            list2.append(item.cloneNode(true));
        }
    };

    Y.one('#domain').delegate('click', onClick, 'option');
    Y.one('#domain').delegate('touchstart', onClick, 'option');
    Y.one('#domainduplicate').delegate('click', onClick, 'option');
    Y.one('#domainduplicate').delegate('touchstart', onClick, 'option');
});
</script>